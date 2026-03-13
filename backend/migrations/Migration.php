<?php
/**
 * Sistema de Migraciones de Base de Datos
 */

class Migration {
    private $db;
    private $migrationsPath;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->migrationsPath = __DIR__ . '/migrations';
    }
    
    /**
     * Ejecutar todas las migraciones pendientes
     */
    public function migrate() {
        // Crear tabla de migraciones si no existe
        $this->createMigrationsTable();
        
        // Obtener migraciones ya ejecutadas
        $executedMigrations = $this->getExecutedMigrations();
        
        // Obtener todas las migraciones disponibles
        $availableMigrations = $this->getAvailableMigrations();
        
        // Ejecutar migraciones pendientes
        foreach ($availableMigrations as $migration) {
            if (!in_array($migration['file'], $executedMigrations)) {
                $this->executeMigration($migration);
            }
        }
        
        echo "Migraciones completadas.\n";
    }
    
    /**
     * Crear tabla para registrar migraciones
     */
    private function createMigrationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->db->prepare($sql)->execute();
    }
    
    /**
     * Obtener migraciones ya ejecutadas
     */
    private function getExecutedMigrations() {
        $sql = "SELECT migration FROM migrations ORDER BY executed_at DESC";
        $result = $this->db->prepare($sql)->fetchAll();
        
        return array_column($result, 'migration');
    }
    
    /**
     * Obtener migraciones disponibles
     */
    private function getAvailableMigrations() {
        $migrations = [];
        
        if (!is_dir($this->migrationsPath)) {
            return $migrations;
        }
        
        $files = scandir($this->migrationsPath);
        sort($files);
        
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $migrations[] = [
                    'file' => $file,
                    'path' => $this->migrationsPath . '/' . $file,
                    'version' => $this->extractVersion($file)
                ];
            }
        }
        
        return $migrations;
    }
    
    /**
     * Extraer versión del nombre de archivo
     */
    private function extractVersion($filename) {
        // Formato: 001_create_users.sql
        preg_match('/^(\d+)_(.+)\.sql$/', $filename, $matches);
        return $matches[1] ?? $filename;
    }
    
    /**
     * Ejecutar migración específica
     */
    private function executeMigration($migration) {
        echo "Ejecutando migración: {$migration['file']}\n";
        
        try {
            // Leer archivo SQL
            $sql = file_get_contents($migration['path']);
            
            if ($sql === false) {
                throw new Exception("No se pudo leer el archivo de migración: {$migration['file']}");
            }
            
            // Separar consultas (asumiendo que están separadas por ;)
            $queries = array_filter(array_map('trim', explode(';', $sql)));
            
            $this->db->beginTransaction();
            
            foreach ($queries as $query) {
                if (!empty($query)) {
                    $this->db->prepare($query)->execute();
                }
            }
            
            // Registrar migración como ejecutada
            $this->markMigrationAsExecuted($migration['file']);
            
            $this->db->commit();
            
            echo "Migración {$migration['file']} ejecutada exitosamente.\n";
            
        } catch (Exception $e) {
            $this->db->rollBack();
            echo "Error ejecutando migración {$migration['file']}: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    /**
     * Marcar migración como ejecutada
     */
    private function markMigrationAsExecuted($migrationFile) {
        $sql = "INSERT INTO migrations (migration) VALUES (:migration)";
        $this->db->prepare($sql)->bind(':migration', $migrationFile)->execute();
    }
    
    /**
     * Revertir última migración
     */
    public function rollback($steps = 1) {
        $executedMigrations = $this->getExecutedMigrations();
        
        if (empty($executedMigrations)) {
            echo "No hay migraciones para revertir.\n";
            return;
        }
        
        $toRollback = array_slice($executedMigrations, 0, $steps);
        
        foreach ($toRollback as $migration) {
            echo "Revertiendo migración: $migration\n";
            
            try {
                $rollbackFile = $this->migrationsPath . '/' . str_replace('.sql', '_rollback.sql', $migration);
                
                if (file_exists($rollbackFile)) {
                    $sql = file_get_contents($rollbackFile);
                    $queries = array_filter(array_map('trim', explode(';', $sql)));
                    
                    $this->db->beginTransaction();
                    
                    foreach ($queries as $query) {
                        if (!empty($query)) {
                            $this->db->prepare($query)->execute();
                        }
                    }
                    
                    // Eliminar migración de la tabla de migraciones
                    $deleteSql = "DELETE FROM migrations WHERE migration = :migration";
                    $this->db->prepare($deleteSql)->bind(':migration', $migration)->execute();
                    
                    $this->db->commit();
                    
                    echo "Migración $migration revertida exitosamente.\n";
                } else {
                    echo "No se encontró archivo de rollback para $migration\n";
                }
            } catch (Exception $e) {
                $this->db->rollBack();
                echo "Error revertiendo migración $migration: " . $e->getMessage() . "\n";
            }
        }
    }
    
    /**
     * Obtener estado de las migraciones
     */
    public function status() {
        $executedMigrations = $this->getExecutedMigrations();
        $availableMigrations = $this->getAvailableMigrations();
        
        echo "\n=== Estado de Migraciones ===\n";
        echo "Migraciones disponibles: " . count($availableMigrations) . "\n";
        echo "Migraciones ejecutadas: " . count($executedMigrations) . "\n\n";
        
        foreach ($availableMigrations as $migration) {
            $status = in_array($migration['file'], $executedMigrations) ? 'EJECUTADA' : 'PENDIENTE';
            echo "[{$status}] {$migration['file']}\n";
        }
    }
    
    /**
     * Crear nueva migración
     */
    public function create($name, $description = '') {
        $timestamp = date('Y_m_d_His');
        $filename = sprintf('%03d_%s.sql', $this->getNextNumber(), $this->sanitizeName($name));
        
        $template = "-- Migration: $name\n";
        $template .= "-- Description: $description\n";
        $template .= "-- Created: " . date('Y-m-d H:i:s') . "\n\n";
        $template .= "-- Write your migration SQL here\n\n";
        
        $filepath = $this->migrationsPath . '/' . $filename;
        
        if (file_put_contents($filepath, $template)) {
            echo "Migración creada: $filename\n";
            return $filename;
        } else {
            echo "Error creando migración: $filename\n";
            return false;
        }
    }
    
    /**
     * Obtener siguiente número de migración
     */
    private function getNextNumber() {
        $files = glob($this->migrationsPath . '/*.sql');
        $maxNumber = 0;
        
        foreach ($files as $file) {
            $filename = basename($file);
            if (preg_match('/^(\d+)_/', $filename, $matches)) {
                $number = (int)$matches[1];
                if ($number > $maxNumber) {
                    $maxNumber = $number;
                }
            }
        }
        
        return $maxNumber + 1;
    }
    
    /**
     * Sanitizar nombre para archivo
     */
    private function sanitizeName($name) {
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $name));
    }
}
