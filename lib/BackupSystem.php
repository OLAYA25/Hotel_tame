<?php
/**
 * BackupSystem - Sistema automatizado de backup y restauración
 * Soporta múltiples estrategias de backup y programación
 */
class BackupSystem {
    private $db;
    private $config;
    private $backup_dir;
    
    public function __construct($database) {
        $this->db = $database->getConnection();
        $this->backup_dir = __DIR__ . '/../backups/';
        
        $this->config = [
            'auto_backup' => true,
            'backup_interval' => 'daily', // daily, weekly, monthly
            'max_backups' => 30,
            'compress_backups' => true,
            'include_files' => true,
            'backup_tables' => [
                'reservas', 'clientes', 'habitaciones', 'usuarios', 
                'transacciones_contables', 'productos', 'pedidos_productos'
            ],
            'exclude_tables' => [
                'sessions', 'cache', 'temp_logs'
            ],
            'notification_email' => 'admin@hotel.com',
            'encryption_key' => 'hotel_backup_key_2025'
        ];
        
        // Crear directorio de backups
        if (!is_dir($this->backup_dir)) {
            mkdir($this->backup_dir, 0755, true);
        }
        
        // Crear subdirectorios
        $subdirs = ['database', 'files', 'logs', 'encrypted'];
        foreach ($subdirs as $subdir) {
            $path = $this->backup_dir . $subdir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }
    
    /**
     * Crear backup completo
     */
    public function createBackup($type = 'full') {
        $timestamp = date('Y-m-d_H-i-s');
        $backup_name = "backup_{$type}_{$timestamp}";
        
        try {
            switch ($type) {
                case 'database':
                    $result = $this->createDatabaseBackup($backup_name);
                    break;
                case 'files':
                    $result = $this->createFilesBackup($backup_name);
                    break;
                case 'full':
                default:
                    $result = $this->createFullBackup($backup_name);
                    break;
            }
            
            if ($result['success']) {
                $this->logBackup($backup_name, $type, $result);
                $this->cleanupOldBackups();
                $this->sendBackupNotification($backup_name, $type, $result);
            }
            
            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'backup_name' => $backup_name
            ];
        }
    }
    
    /**
     * Crear backup de base de datos
     */
    private function createDatabaseBackup($backup_name) {
        $backup_file = $this->backup_dir . 'database/' . $backup_name . '.sql';
        
        // Obtener todas las tablas
        $tables = [];
        $stmt = $this->db->query("SHOW TABLES");
        while ($row = $stmt->fetch()) {
            $table = array_values($row)[0];
            if (in_array($table, $this->config['backup_tables']) && 
                !in_array($table, $this->config['exclude_tables'])) {
                $tables[] = $table;
            }
        }
        
        // Generar SQL dump
        $sql = "-- Backup Database: Hotel Management System\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Type: Database Backup\n\n";
        
        foreach ($tables as $table) {
            $sql .= $this->getTableDump($table);
        }
        
        // Escribir archivo
        if (file_put_contents($backup_file, $sql) === false) {
            throw new Exception("No se pudo crear el archivo de backup");
        }
        
        // Comprimir si está configurado
        if ($this->config['compress_backups']) {
            $this->compressFile($backup_file);
            $backup_file .= '.gz';
        }
        
        return [
            'success' => true,
            'file' => $backup_file,
            'size' => filesize($backup_file),
            'tables' => count($tables),
            'type' => 'database'
        ];
    }
    
    /**
     * Crear backup de archivos
     */
    private function createFilesBackup($backup_name) {
        $backup_file = $this->backup_dir . 'files/' . $backup_name . '.zip';
        
        // Directorios a incluir en backup
        $directories = [
            'uploads/',
            'assets/images/',
            'documents/',
            'config/' // Solo backup, no incluir credenciales sensibles
        ];
        
        // Crear ZIP
        $zip = new ZipArchive();
        if ($zip->open($backup_file, ZipArchive::CREATE) !== TRUE) {
            throw new Exception("No se pudo crear el archivo ZIP");
        }
        
        $total_files = 0;
        $total_size = 0;
        
        foreach ($directories as $dir) {
            $full_path = __DIR__ . '/../' . $dir;
            if (is_dir($full_path)) {
                $files = $this->addDirectoryToZip($zip, $full_path, $dir);
                $total_files += $files['count'];
                $total_size += $files['size'];
            }
        }
        
        // Agregar manifiesto
        $manifest = [
            'backup_name' => $backup_name,
            'created_at' => date('Y-m-d H:i:s'),
            'directories' => $directories,
            'total_files' => $total_files,
            'total_size' => $total_size,
            'version' => '1.0'
        ];
        
        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
        $zip->close();
        
        return [
            'success' => true,
            'file' => $backup_file,
            'size' => filesize($backup_file),
            'files' => $total_files,
            'type' => 'files'
        ];
    }
    
    /**
     * Crear backup completo
     */
    private function createFullBackup($backup_name) {
        $backup_dir = $this->backup_dir . $backup_name . '/';
        mkdir($backup_dir, 0755, true);
        
        // Crear backup de base de datos
        $db_result = $this->createDatabaseBackup($backup_name);
        if (!$db_result['success']) {
            throw new Exception("Error en backup de base de datos: " . $db_result['error']);
        }
        
        // Crear backup de archivos
        $files_result = $this->createFilesBackup($backup_name);
        if (!$files_result['success']) {
            throw new Exception("Error en backup de archivos: " . $files_result['error']);
        }
        
        // Crear archivo de metadatos
        $metadata = [
            'backup_name' => $backup_name,
            'created_at' => date('Y-m-d H:i:s'),
            'type' => 'full',
            'database' => $db_result,
            'files' => $files_result,
            'system_info' => $this->getSystemInfo(),
            'version' => '1.0'
        ];
        
        file_put_contents($backup_dir . 'metadata.json', json_encode($metadata, JSON_PRETTY_PRINT));
        
        // Comprimir todo
        if ($this->config['compress_backups']) {
            $this->compressDirectory($backup_dir);
            $backup_file = $backup_dir . '.zip';
            $this->removeDirectory($backup_dir);
        } else {
            $backup_file = $backup_dir;
        }
        
        return [
            'success' => true,
            'file' => $backup_file,
            'size' => $this->getDirectorySize($backup_file),
            'database' => $db_result,
            'files' => $files_result,
            'type' => 'full'
        ];
    }
    
    /**
     * Restaurar backup
     */
    public function restoreBackup($backup_file) {
        try {
            // Verificar que el archivo exista
            if (!file_exists($backup_file)) {
                throw new Exception("El archivo de backup no existe");
            }
            
            // Descomprimir si es necesario
            $extract_path = $this->backup_dir . 'restore_' . time() . '/';
            mkdir($extract_path, 0755, true);
            
            if (pathinfo($backup_file, PATHINFO_EXTENSION) === 'zip') {
                $zip = new ZipArchive();
                if ($zip->open($backup_file) === TRUE) {
                    $zip->extractTo($extract_path);
                    $zip->close();
                }
            }
            
            // Leer metadatos
            $metadata_file = $extract_path . 'metadata.json';
            if (file_exists($metadata_file)) {
                $metadata = json_decode(file_get_contents($metadata_file), true);
                
                // Restaurar base de datos
                if (isset($metadata['database'])) {
                    $this->restoreDatabase($extract_path);
                }
                
                // Restaurar archivos
                if (isset($metadata['files'])) {
                    $this->restoreFiles($extract_path);
                }
            } else {
                // Restaurar solo base de datos si no hay metadatos
                $this->restoreDatabase($extract_path);
            }
            
            // Limpiar archivos temporales
            $this->removeDirectory($extract_path);
            
            return [
                'success' => true,
                'message' => 'Backup restaurado exitosamente'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Programar backups automáticos
     */
    public function scheduleAutoBackup() {
        if (!$this->config['auto_backup']) {
            return false;
        }
        
        $last_backup = $this->getLastBackupTime();
        $next_backup = $this->calculateNextBackupTime($last_backup);
        
        if (time() >= $next_backup) {
            return $this->createBackup('full');
        }
        
        return false;
    }
    
    /**
     * Obtener lista de backups
     */
    public function getBackupList() {
        $backups = [];
        
        // Escanear directorio de backups
        $files = glob($this->backup_dir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $backups[] = $this->getBackupInfo($file);
            }
        }
        
        // Ordenar por fecha (más reciente primero)
        usort($backups, function($a, $b) {
            return $b['created_at'] - $a['created_at'];
        });
        
        return $backups;
    }
    
    /**
     * Eliminar backup
     */
    public function deleteBackup($backup_name) {
        $backup_file = $this->backup_dir . $backup_name;
        
        if (file_exists($backup_file)) {
            if (is_dir($backup_file)) {
                return $this->removeDirectory($backup_file);
            } else {
                return unlink($backup_file);
            }
        }
        
        return false;
    }
    
    /**
     * Obtener dump de tabla
     */
    private function getTableDump($table) {
        $sql = "-- Table: {$table}\n";
        $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
        
        // Obtener estructura
        $stmt = $this->db->query("SHOW CREATE TABLE `{$table}`");
        $row = $stmt->fetch();
        $sql .= $row['Create Table'] . ";\n\n";
        
        // Obtener datos
        $stmt = $this->db->query("SELECT * FROM `{$table}`");
        $rows = $stmt->fetchAll();
        
        if (!empty($rows)) {
            $columns = array_keys($rows[0]);
            $sql .= "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES\n";
            
            foreach ($rows as $row) {
                $values = array_map(function($value) {
                    if ($value === null) {
                        return 'NULL';
                    } elseif (is_string($value)) {
                        return "'" . addslashes($value) . "'";
                    } else {
                        return $value;
                    }
                }, $row);
                
                $sql .= "(" . implode(', ', $values) . "),\n";
            }
            
            $sql = rtrim($sql, ",\n") . ";\n\n";
        }
        
        return $sql;
    }
    
    /**
     * Agregar directorio al ZIP
     */
    private function addDirectoryToZip($zip, $dir, $zip_dir) {
        $files = ['count' => 0, 'size' => 0];
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }
            
            $file_path = $file->getPathname();
            $relative_path = $zip_dir . substr($file_path, strlen(__DIR__ . '/../'));
            
            if ($zip->addFile($file_path, $relative_path)) {
                $files['count']++;
                $files['size'] += $file->getSize();
            }
        }
        
        return $files;
    }
    
    /**
     * Comprimir archivo
     */
    private function compressFile($file) {
        $gz_file = $file . '.gz';
        $fp = fopen($gz_file, 'wb');
        fwrite($fp, gzencode(file_get_contents($file), 9));
        fclose($fp);
        unlink($file);
        return $gz_file;
    }
    
    /**
     * Comprimir directorio
     */
    private function compressDirectory($dir) {
        $zip_file = $dir . '.zip';
        $zip = new ZipArchive();
        
        if ($zip->open($zip_file, ZipArchive::CREATE) === TRUE) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir),
                RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    continue;
                }
                
                $file_path = $file->getPathname();
                $relative_path = substr($file_path, strlen($dir));
                $zip->addFile($file_path, $relative_path);
            }
            
            $zip->close();
        }
        
        return $zip_file;
    }
    
    /**
     * Eliminar directorio recursivamente
     */
    private function removeDirectory($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        
        return rmdir($dir);
    }
    
    /**
     * Obtener tamaño de directorio
     */
    private function getDirectorySize($path) {
        if (is_file($path)) {
            return filesize($path);
        }
        
        $size = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        
        return $size;
    }
    
    /**
     * Obtener información del sistema
     */
    private function getSystemInfo() {
        return [
            'php_version' => PHP_VERSION,
            'mysql_version' => $this->db->query("SELECT VERSION()")->fetchColumn(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? '',
            'backup_version' => '1.0'
        ];
    }
    
    /**
     * Registrar backup
     */
    private function logBackup($backup_name, $type, $result) {
        $log_entry = [
            'backup_name' => $backup_name,
            'type' => $type,
            'success' => $result['success'],
            'size' => $result['size'] ?? 0,
            'created_at' => date('Y-m-d H:i:s'),
            'error' => $result['error'] ?? null
        ];
        
        $log_file = $this->backup_dir . 'logs/backup_log.json';
        $logs = [];
        
        if (file_exists($log_file)) {
            $logs = json_decode(file_get_contents($log_file), true) ?? [];
        }
        
        $logs[] = $log_entry;
        
        // Mantener solo últimos 100 logs
        if (count($logs) > 100) {
            $logs = array_slice($logs, -100);
        }
        
        file_put_contents($log_file, json_encode($logs, JSON_PRETTY_PRINT));
    }
    
    /**
     * Enviar notificación de backup
     */
    private function sendBackupNotification($backup_name, $type, $result) {
        if (!$this->config['notification_email']) {
            return;
        }
        
        $subject = "Backup Hotel - " . ($result['success'] ? 'Éxito' : 'Error');
        $message = "
            <h2>Resultado del Backup</h2>
            <p><strong>Nombre:</strong> {$backup_name}</p>
            <p><strong>Tipo:</strong> {$type}</p>
            <p><strong>Estado:</strong> " . ($result['success'] ? 'Exitoso' : 'Fallido') . "</p>
            <p><strong>Tamaño:</strong> " . $this->formatBytes($result['size'] ?? 0) . "</p>
            <p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>
        ";
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: noreply@hotel.com\r\n";
        
        mail($this->config['notification_email'], $subject, $message, $headers);
    }
    
    /**
     * Limpiar backups antiguos
     */
    private function cleanupOldBackups() {
        $backups = $this->getBackupList();
        
        if (count($backups) > $this->config['max_backups']) {
            $to_delete = array_slice($backups, $this->config['max_backups']);
            
            foreach ($to_delete as $backup) {
                $this->deleteBackup($backup['name']);
            }
        }
    }
    
    /**
     * Obtener información de backup
     */
    private function getBackupInfo($file) {
        return [
            'name' => basename($file),
            'path' => $file,
            'size' => filesize($file),
            'created_at' => filemtime($file),
            'type' => $this->detectBackupType($file)
        ];
    }
    
    /**
     * Detectar tipo de backup
     */
    private function detectBackupType($file) {
        $name = basename($file);
        
        if (strpos($name, 'database') !== false) {
            return 'database';
        } elseif (strpos($name, 'files') !== false) {
            return 'files';
        } else {
            return 'full';
        }
    }
    
    /**
     * Formatear bytes
     */
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Restaurar base de datos desde backup
     */
    private function restoreDatabase($extract_path) {
        $sql_files = glob($extract_path . '*.sql');
        
        foreach ($sql_files as $sql_file) {
            if (basename($sql_file) !== 'metadata.json') {
                $sql = file_get_contents($sql_file);
                
                // Ejecutar SQL en bloques para evitar límites
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                
                foreach ($statements as $statement) {
                    if (!empty($statement)) {
                        try {
                            $this->db->exec($statement);
                        } catch (Exception $e) {
                            error_log("Error restaurando SQL: " . $e->getMessage());
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Restaurar archivos desde backup
     */
    private function restoreFiles($extract_path) {
        // Directorios donde restaurar archivos
        $restore_map = [
            'uploads/' => __DIR__ . '/../uploads/',
            'assets/images/' => __DIR__ . '/../assets/images/',
            'documents/' => __DIR__ . '/../documents/'
        ];
        
        foreach ($restore_map as $backup_dir => $target_dir) {
            $source_dir = $extract_path . $backup_dir;
            
            if (is_dir($source_dir)) {
                // Crear directorio destino si no existe
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }
                
                // Copiar archivos recursivamente
                $this->copyDirectory($source_dir, $target_dir);
            }
        }
    }
    
    /**
     * Copiar directorio recursivamente
     */
    private function copyDirectory($source, $dest) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                $dest_path = $dest . '/' . basename($file->getPathname());
                if (!is_dir($dest_path)) {
                    mkdir($dest_path, 0755, true);
                }
            } else {
                $dest_path = $dest . '/' . basename($file->getPathname());
                copy($file, $dest_path);
            }
        }
    }
    
    /**
     * Obtener último tiempo de backup
     */
    private function getLastBackupTime() {
        $backups = $this->getBackupList();
        return !empty($backups) ? $backups[0]['created_at'] : 0;
    }
    
    /**
     * Calcular próximo tiempo de backup
     */
    private function calculateNextBackupTime($last_backup) {
        $interval = $this->config['backup_interval'];
        
        switch ($interval) {
            case 'daily':
                return $last_backup + (24 * 60 * 60);
            case 'weekly':
                return $last_backup + (7 * 24 * 60 * 60);
            case 'monthly':
                return $last_backup + (30 * 24 * 60 * 60);
            default:
                return $last_backup + (24 * 60 * 60);
        }
    }
}
?>
