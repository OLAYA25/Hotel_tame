<?php

namespace App\Services;

use Database\Database;
use App\Services\AppLogger;
use Exception;

class HousekeepingService {
    
    /**
     * Create housekeeping task
     */
    public function createTask(array $data): int {
        $sql = "INSERT INTO housekeeping_tasks 
                (habitacion_id, tipo_limpieza, estado, asignado_a, fecha_programada, observaciones, tiempo_estimado_minutes, hotel_id) 
                VALUES (:habitacion_id, :tipo_limpieza, :estado, :asignado_a, :fecha_programada, :observaciones, :tiempo_estimado_minutes, :hotel_id)";
        
        $params = [
            ':habitacion_id' => $data['habitacion_id'],
            ':tipo_limpieza' => $data['tipo_limpieza'] ?? 'regular',
            ':estado' => 'pendiente',
            ':asignado_a' => $data['asignado_a'] ?? null,
            ':fecha_programada' => $data['fecha_programada'],
            ':observaciones' => $data['observaciones'] ?? null,
            ':tiempo_estimado_minutes' => $data['tiempo_estimado_minutes'] ?? 30,
            ':hotel_id' => $data['hotel_id'] ?? 1
        ];
        
        Database::execute($sql, $params);
        $taskId = Database::lastInsertId();
        
        AppLogger::business('Housekeeping task created', [
            'task_id' => $taskId,
            'habitacion_id' => $data['habitacion_id'],
            'tipo_limpieza' => $data['tipo_limpieza']
        ]);
        
        return $taskId;
    }
    
    /**
     * Get tasks for specific date and staff
     */
    public function getTasksByDateAndStaff(string $date, ?int $staffId = null): array {
        $sql = "SELECT ht.*, h.numero as habitacion_numero, h.tipo as habitacion_tipo,
                       u.nombre as asignado_nombre, u.apellido as asignado_apellido
                FROM housekeeping_tasks ht
                LEFT JOIN habitaciones h ON ht.habitacion_id = h.id
                LEFT JOIN usuarios u ON ht.asignado_a = u.id
                WHERE ht.fecha_programada = :fecha 
                AND ht.deleted_at IS NULL";
        
        $params = [':fecha' => $date];
        
        if ($staffId) {
            $sql .= " AND ht.asignado_a = :staff_id";
            $params[':staff_id'] = $staffId;
        }
        
        $sql .= " ORDER BY ht.estado, ht.fecha_programada";
        
        return Database::fetchAll($sql, $params);
    }
    
    /**
     * Update task status
     */
    public function updateTaskStatus(int $taskId, string $status, ?int $inspectorId = null, ?string $observaciones = null): bool {
        $sql = "UPDATE housekeeping_tasks 
                SET estado = :estado, updated_at = NOW()";
        
        $params = [':estado' => $status, ':task_id' => $taskId];
        
        if ($status === 'completada') {
            $sql .= ", fecha_realizada = NOW()";
        }
        
        if ($status === 'inspeccion') {
            $sql .= ", fecha_inspeccion = NOW(), inspector_id = :inspector_id";
            $params[':inspector_id'] = $inspectorId;
        }
        
        if ($observaciones) {
            $sql .= ", observaciones = :observaciones";
            $params[':observaciones'] = $observaciones;
        }
        
        $sql .= " WHERE id = :task_id";
        
        $result = Database::execute($sql, $params);
        
        AppLogger::business('Housekeeping task status updated', [
            'task_id' => $taskId,
            'new_status' => $status,
            'inspector_id' => $inspectorId
        ]);
        
        return $result;
    }
    
    /**
     * Get housekeeping statistics
     */
    public function getStatistics(string $date = null): array {
        $date = $date ?? date('Y-m-d');
        
        $sql = "SELECT 
                    COUNT(*) as total_tasks,
                    COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pendientes,
                    COUNT(CASE WHEN estado = 'en_progreso' THEN 1 END) as en_progreso,
                    COUNT(CASE WHEN estado = 'completada' THEN 1 END) as completadas,
                    COUNT(CASE WHEN estado = 'inspeccion' THEN 1 END) en_inspeccion,
                    AVG(tiempo_real_minutes) as avg_tiempo_real,
                    AVG(tiempo_estimado_minutes) as avg_tiempo_estimado
                FROM housekeeping_tasks 
                WHERE fecha_programada = :fecha AND deleted_at IS NULL";
        
        $stats = Database::fetch($sql, [':fecha' => $date]);
        
        // Get staff productivity
        $staffSql = "SELECT 
                        u.id, u.nombre, u.apellido,
                        COUNT(ht.id) as tasks_completadas,
                        AVG(ht.tiempo_real_minutes) as avg_time
                     FROM usuarios u
                     LEFT JOIN housekeeping_tasks ht ON u.id = ht.asignado_a 
                         AND ht.fecha_programada = :fecha 
                         AND ht.estado = 'completada'
                     WHERE u.rol = 'limpieza'
                     GROUP BY u.id, u.nombre, u.apellido
                     ORDER BY tasks_completadas DESC";
        
        $stats['staff_productivity'] = Database::fetchAll($staffSql, [':fecha' => $date]);
        
        return $stats;
    }
    
    /**
     * Generate automatic tasks for checkout rooms
     */
    public function generateCheckoutTasks(): int {
        $today = date('Y-m-d');
        
        // Get rooms with checkout today
        $sql = "SELECT r.habitacion_id 
                FROM reservas r
                WHERE r.fecha_salida = CURDATE() 
                AND r.estado IN ('ocupada', 'finalizada')
                AND r.deleted_at IS NULL";
        
        $checkoutRooms = Database::fetchAll($sql);
        
        $tasksCreated = 0;
        
        foreach ($checkoutRooms as $room) {
            // Check if task already exists
            $existingSql = "SELECT id FROM housekeeping_tasks 
                           WHERE habitacion_id = :habitacion_id 
                           AND fecha_programada = :fecha 
                           AND tipo_limpieza = 'checkout'
                           AND deleted_at IS NULL";
            
            $existing = Database::fetch($existingSql, [
                ':habitacion_id' => $room['habitacion_id'],
                ':fecha' => $today
            ]);
            
            if (!$existing) {
                $this->createTask([
                    'habitacion_id' => $room['habitacion_id'],
                    'tipo_limpieza' => 'checkout',
                    'fecha_programada' => $today,
                    'observaciones' => 'Limpieza de checkout automática'
                ]);
                $tasksCreated++;
            }
        }
        
        AppLogger::business('Automatic checkout tasks generated', [
            'tasks_created' => $tasksCreated,
            'date' => $today
        ]);
        
        return $tasksCreated;
    }
    
    /**
     * Get room cleaning history
     */
    public function getRoomCleaningHistory(int $habitacionId, int $limit = 50): array {
        $sql = "SELECT ht.*, u.nombre as asignado_nombre, u.apellido as asignado_apellido
                FROM housekeeping_tasks ht
                LEFT JOIN usuarios u ON ht.asignado_a = u.id
                WHERE ht.habitacion_id = :habitacion_id 
                AND ht.deleted_at IS NULL
                ORDER BY ht.fecha_programada DESC
                LIMIT :limit";
        
        return Database::fetchAll($sql, [
            ':habitacion_id' => $habitacionId,
            ':limit' => $limit
        ]);
    }
    
    /**
     * Get pending tasks count by staff
     */
    public function getPendingTasksCountByStaff(): array {
        $sql = "SELECT u.id, u.nombre, u.apellido,
                       COUNT(ht.id) as pending_count
                FROM usuarios u
                LEFT JOIN housekeeping_tasks ht ON u.id = ht.asignado_a 
                    AND ht.estado IN ('pendiente', 'en_progreso')
                    AND ht.fecha_programada <= CURDATE()
                    AND ht.deleted_at IS NULL
                WHERE u.rol = 'limpieza' AND u.deleted_at IS NULL
                GROUP BY u.id, u.nombre, u.apellido
                ORDER BY pending_count DESC";
        
        return Database::fetchAll($sql);
    }
}
