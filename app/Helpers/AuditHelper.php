<?php
/**
 * Helper de Auditoría
 */

class AuditHelper {
    
    /**
     * Registrar acción en el log
     */
    public static function log($usuarioId, $accion, $tabla, $registroId, $datosAnteriores = null, $datosNuevos = null) {
        $sql = "INSERT INTO logs (usuario_id, accion, tabla, registro_id, datos_anteriores, datos_nuevos, fecha, ip) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        
        return $stmt->execute([
            $usuarioId,
            $accion,
            $tabla,
            $registroId,
            $datosAnteriores ? json_encode($datosAnteriores) : null,
            $datosNuevos ? json_encode($datosNuevos) : null,
            $ip
        ]);
    }
    
    /**
     * Obtener logs de un usuario
     */
    public static function getUserLogs($usuarioId, $limit = 100) {
        $sql = "SELECT l.*, u.nombre as usuario_nombre
                FROM logs l
                LEFT JOIN usuarios u ON l.usuario_id = u.id
                WHERE l.usuario_id = :usuario_id
                ORDER BY l.fecha DESC
                LIMIT :limit";
        
        $db = Database::getInstance();
        return $db->prepare($sql)
                   ->bind(':usuario_id', $usuarioId)
                   ->bind(':limit', $limit, PDO::PARAM_INT)
                   ->fetchAll();
    }
    
    /**
     * Obtener logs de una tabla
     */
    public static function getTableLogs($tabla, $registroId = null, $limit = 100) {
        $sql = "SELECT l.*, u.nombre as usuario_nombre
                FROM logs l
                LEFT JOIN usuarios u ON l.usuario_id = u.id
                WHERE l.tabla = :tabla";
        
        $params = [':tabla' => $tabla];
        
        if ($registroId) {
            $sql .= " AND l.registro_id = :registro_id";
            $params[':registro_id'] = $registroId;
        }
        
        $sql .= " ORDER BY l.fecha DESC LIMIT :limit";
        
        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener estadísticas de actividad
     */
    public static function getActivityStats($period = 'day') {
        $dateCondition = match($period) {
            'day' => 'DATE(fecha) = CURDATE()',
            'week' => 'WEEK(fecha) = WEEK(NOW())',
            'month' => 'MONTH(fecha) = MONTH(NOW()) AND YEAR(fecha) = YEAR(NOW())',
            'year' => 'YEAR(fecha) = YEAR(NOW())',
            default => '1=1'
        };
        
        $sql = "SELECT 
                    COUNT(*) as total_acciones,
                    COUNT(DISTINCT usuario_id) as usuarios_activos,
                    COUNT(DISTINCT tabla) as tablas_afectadas
                FROM logs
                WHERE {$dateCondition}";
        
        $db = Database::getInstance();
        return $db->prepare($sql)->fetch();
    }
}
