<?php

namespace App\Services;

use Database\Database;
use App\Services\AppLogger;
use Exception;

class MaintenanceService {
    
    /**
     * Create maintenance request
     */
    public function createRequest(array $data): int {
        $sql = "INSERT INTO mantenimiento_habitaciones 
                (habitacion_id, descripcion, prioridad, estado, reportado_por, tipo_mantenimiento, categoria, costo_estimado, observaciones, hotel_id) 
                VALUES (:habitacion_id, :descripcion, :prioridad, :estado, :reportado_por, :tipo_mantenimiento, :categoria, :costo_estimado, :observaciones, :hotel_id)";
        
        $params = [
            ':habitacion_id' => $data['habitacion_id'],
            ':descripcion' => $data['descripcion'],
            ':prioridad' => $data['prioridad'] ?? 'media',
            ':estado' => 'abierto',
            ':reportado_por' => $data['reportado_por'],
            ':tipo_mantenimiento' => $data['tipo_mantenimiento'] ?? 'correctivo',
            ':categoria' => $data['categoria'] ?? null,
            ':costo_estimado' => $data['costo_estimado'] ?? 0.00,
            ':observaciones' => $data['observaciones'] ?? null,
            ':hotel_id' => $data['hotel_id'] ?? 1
        ];
        
        Database::execute($sql, $params);
        $requestId = Database::lastInsertId();
        
        // Update room status if urgent
        if ($data['prioridad'] === 'urgente') {
            $this->updateRoomStatus($data['habitacion_id'], 'mantenimiento');
        }
        
        AppLogger::business('Maintenance request created', [
            'request_id' => $requestId,
            'habitacion_id' => $data['habitacion_id'],
            'prioridad' => $data['prioridad']
        ]);
        
        return $requestId;
    }
    
    /**
     * Get maintenance requests with filters
     */
    public function getRequests(array $filters = []): array {
        $sql = "SELECT mh.*, h.numero as habitacion_numero, h.tipo as habitacion_tipo,
                       reporter.nombre as reportado_nombre, reporter.apellido as reportado_apellido,
                       assigned.nombre as asignado_nombre, assigned.apellido as asignado_apellido
                FROM mantenimiento_habitaciones mh
                LEFT JOIN habitaciones h ON mh.habitacion_id = h.id
                LEFT JOIN usuarios reporter ON mh.reportado_por = reporter.id
                LEFT JOIN usuarios assigned ON mh.asignado_a = assigned.id
                WHERE mh.deleted_at IS NULL";
        
        $params = [];
        
        if (!empty($filters['estado'])) {
            $sql .= " AND mh.estado = :estado";
            $params[':estado'] = $filters['estado'];
        }
        
        if (!empty($filters['prioridad'])) {
            $sql .= " AND mh.prioridad = :prioridad";
            $params[':prioridad'] = $filters['prioridad'];
        }
        
        if (!empty($filters['habitacion_id'])) {
            $sql .= " AND mh.habitacion_id = :habitacion_id";
            $params[':habitacion_id'] = $filters['habitacion_id'];
        }
        
        if (!empty($filters['asignado_a'])) {
            $sql .= " AND mh.asignado_a = :asignado_a";
            $params[':asignado_a'] = $filters['asignado_a'];
        }
        
        if (!empty($filters['fecha_desde'])) {
            $sql .= " AND mh.fecha_reporte >= :fecha_desde";
            $params[':fecha_desde'] = $filters['fecha_desde'];
        }
        
        if (!empty($filters['fecha_hasta'])) {
            $sql .= " AND mh.fecha_reporte <= :fecha_hasta";
            $params[':fecha_hasta'] = $filters['fecha_hasta'];
        }
        
        $sql .= " ORDER BY mh.prioridad DESC, mh.fecha_reporte DESC";
        
        return Database::fetchAll($sql, $params);
    }
    
    /**
     * Update maintenance request status
     */
    public function updateRequestStatus(int $requestId, string $status, ?int $asignadoA = null, ?string $resolucion = null, ?float $costoReal = null): bool {
        $sql = "UPDATE mantenimiento_habitaciones 
                SET estado = :estado, updated_at = NOW()";
        
        $params = [':estado' => $status, ':request_id' => $requestId];
        
        if ($asignadoA && $status === 'en_progreso') {
            $sql .= ", asignado_a = :asignado_a, fecha_asignacion = NOW()";
            $params[':asignado_a'] = $asignadoA;
        }
        
        if ($status === 'resuelto') {
            $sql .= ", fecha_resolucion = NOW()";
            
            if ($resolucion) {
                $sql .= ", resolucion = :resolucion";
                $params[':resolucion'] = $resolucion;
            }
            
            if ($costoReal !== null) {
                $sql .= ", costo_real = :costo_real";
                $params[':costo_real'] = $costoReal;
            }
            
            // Update room status back to available
            $request = $this->getRequestById($requestId);
            if ($request) {
                $this->updateRoomStatus($request['habitacion_id'], 'disponible');
            }
        }
        
        $sql .= " WHERE id = :request_id";
        
        $result = Database::execute($sql, $params);
        
        AppLogger::business('Maintenance request status updated', [
            'request_id' => $requestId,
            'new_status' => $status,
            'asignado_a' => $asignadoA
        ]);
        
        return $result;
    }
    
    /**
     * Get maintenance statistics
     */
    public function getStatistics(string $period = 'month'): array {
        $dateCondition = match($period) {
            'day' => 'DATE(fecha_reporte) = CURDATE()',
            'week' => 'WEEK(fecha_reporte) = WEEK(NOW())',
            'month' => 'MONTH(fecha_reporte) = MONTH(NOW()) AND YEAR(fecha_reporte) = YEAR(NOW())',
            'year' => 'YEAR(fecha_reporte) = YEAR(NOW())',
            default => '1=1'
        };
        
        $sql = "SELECT 
                    COUNT(*) as total_requests,
                    COUNT(CASE WHEN prioridad = 'urgente' THEN 1 END) as urgent_requests,
                    COUNT(CASE WHEN estado = 'abierto' THEN 1 END) as open_requests,
                    COUNT(CASE WHEN estado = 'en_progreso' THEN 1 END) as in_progress_requests,
                    COUNT(CASE WHEN estado = 'resuelto' THEN 1 END) as resolved_requests,
                    AVG(costo_real) as avg_cost_real,
                    SUM(costo_real) as total_cost_real,
                    AVG(TIMESTAMPDIFF(HOUR, fecha_reporte, fecha_resolucion)) as avg_resolution_time_hours
                FROM mantenimiento_habitaciones
                WHERE deleted_at IS NULL AND {$dateCondition}";
        
        $stats = Database::fetch($sql);
        
        // Get requests by priority
        $prioritySql = "SELECT prioridad, COUNT(*) as count
                       FROM mantenimiento_habitaciones
                       WHERE deleted_at IS NULL AND {$dateCondition}
                       GROUP BY prioridad
                       ORDER BY FIELD(prioridad, 'urgente', 'alta', 'media', 'baja')";
        
        $stats['by_priority'] = Database::fetchAll($prioritySql);
        
        // Get requests by category
        $categorySql = "SELECT categoria, COUNT(*) as count
                       FROM mantenimiento_habitaciones
                       WHERE deleted_at IS NULL AND {$dateCondition} AND categoria IS NOT NULL
                       GROUP BY categoria
                       ORDER BY count DESC";
        
        $stats['by_category'] = Database::fetchAll($categorySql);
        
        return $stats;
    }
    
    /**
     * Get scheduled maintenance tasks
     */
    public function getScheduledTasks(string $date = null): array {
        $date = $date ?? date('Y-m-d');
        
        $sql = "SELECT mp.*, h.numero as habitacion_numero, h.tipo as habitacion_tipo
                FROM mantenimiento_programado mp
                LEFT JOIN habitaciones h ON mp.habitacion_id = h.id
                WHERE mp.proxima_ejecucion <= :fecha 
                AND mp.activo = 1 
                AND mp.deleted_at IS NULL
                ORDER BY mp.proxima_ejecucion ASC";
        
        return Database::fetchAll($sql, [':fecha' => $date]);
    }
    
    /**
     * Generate scheduled maintenance tasks
     */
    public function generateScheduledTasks(): int {
        $today = date('Y-m-d');
        $tasksGenerated = 0;
        
        $scheduledTasks = $this->getScheduledTasks($today);
        
        foreach ($scheduledTasks as $task) {
            // Check if already created for this date
            $existingSql = "SELECT id FROM mantenimiento_habitaciones 
                           WHERE habitacion_id = :habitacion_id 
                           AND DATE(fecha_reporte) = :fecha
                           AND descripcion LIKE :descripcion
                           AND deleted_at IS NULL";
            
            $existing = Database::fetch($existingSql, [
                ':habitacion_id' => $task['habitacion_id'],
                ':fecha' => $today,
                ':descripcion' => '%' . $task['tipo_mantenimiento'] . '%'
            ]);
            
            if (!$existing) {
                $this->createRequest([
                    'habitacion_id' => $task['habitacion_id'],
                    'descripcion' => 'Mantenimiento programado: ' . $task['tipo_mantenimiento'],
                    'prioridad' => 'media',
                    'reportado_por' => 1, // System user
                    'tipo_mantenimiento' => 'preventivo',
                    'categoria' => 'programado',
                    'observaciones' => $task['observaciones']
                ]);
                
                // Update next execution date
                $this->updateNextExecutionDate($task['id'], $task['frecuencia']);
                
                $tasksGenerated++;
            }
        }
        
        AppLogger::business('Scheduled maintenance tasks generated', [
            'tasks_generated' => $tasksGenerated,
            'date' => $today
        ]);
        
        return $tasksGenerated;
    }
    
    /**
     * Get maintenance materials with low stock
     */
    public function getLowStockMaterials(): array {
        $sql = "SELECT * FROM mantenimiento_materiales 
                WHERE stock_actual <= stock_minimo 
                AND deleted_at IS NULL
                ORDER BY (stock_minimo - stock_actual) DESC";
        
        return Database::fetchAll($sql);
    }
    
    /**
     * Update room status
     */
    private function updateRoomStatus(int $habitacionId, string $status): void {
        $sql = "UPDATE habitaciones SET estado = :estado WHERE id = :id";
        Database::execute($sql, [
            ':estado' => $status,
            ':id' => $habitacionId
        ]);
    }
    
    /**
     * Get request by ID
     */
    private function getRequestById(int $requestId): ?array {
        $sql = "SELECT * FROM mantenimiento_habitaciones WHERE id = :id AND deleted_at IS NULL";
        return Database::fetch($sql, [':id' => $requestId]);
    }
    
    /**
     * Update next execution date for scheduled maintenance
     */
    private function updateNextExecutionDate(int $taskId, string $frecuencia): void {
        $nextDate = match($frecuencia) {
            'diario' => "DATE_ADD(CURDATE(), INTERVAL 1 DAY)",
            'semanal' => "DATE_ADD(CURDATE(), INTERVAL 1 WEEK)",
            'mensual' => "DATE_ADD(CURDATE(), INTERVAL 1 MONTH)",
            'trimestral' => "DATE_ADD(CURDATE(), INTERVAL 3 MONTH)",
            'semestral' => "DATE_ADD(CURDATE(), INTERVAL 6 MONTH)",
            'anual' => "DATE_ADD(CURDATE(), INTERVAL 1 YEAR)",
            default => "CURDATE()"
        };
        
        $sql = "UPDATE mantenimiento_programado 
                SET ultima_ejecucion = NOW(), proxima_ejecucion = {$nextDate}
                WHERE id = :id";
        
        Database::execute($sql, [':id' => $taskId]);
    }
}
