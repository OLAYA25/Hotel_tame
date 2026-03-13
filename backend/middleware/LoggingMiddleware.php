<?php
/**
 * Middleware de Logging y Auditoría
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/AuthMiddleware.php';

class LoggingMiddleware {
    
    /**
     * Registrar acción en el log
     */
    public static function log($action, $table = null, $recordId = null, $oldData = null, $newData = null) {
        $user = AuthMiddleware::user();
        
        $data = [
            'usuario_id' => $user ? $user['id'] : null,
            'accion' => $action,
            'tabla' => $table,
            'registro_id' => $recordId,
            'datos_anteriores' => $oldData ? json_encode($oldData) : null,
            'datos_nuevos' => $newData ? json_encode($newData) : null,
            'fecha' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        try {
            $db = Database::getInstance();
            $sql = "INSERT INTO logs (usuario_id, accion, tabla, registro_id, datos_anteriores, datos_nuevos, fecha, ip, user_agent) 
                    VALUES (:usuario_id, :accion, :tabla, :registro_id, :datos_anteriores, :datos_nuevos, :fecha, :ip, :user_agent)";
            
            $db->prepare($sql)->execute($data);
        } catch (Exception $e) {
            error_log("Error al registrar log: " . $e->getMessage());
        }
    }
    
    /**
     * Registrar login
     */
    public static function logLogin($userId, $success = true) {
        self::log(
            $success ? 'login_success' : 'login_failed',
            'usuarios',
            $userId,
            null,
            ['success' => $success, 'timestamp' => date('Y-m-d H:i:s')]
        );
    }
    
    /**
     * Registrar logout
     */
    public static function logLogout($userId) {
        self::log('logout', 'usuarios', $userId);
    }
    
    /**
     * Registrar creación de registro
     */
    public static function logCreate($table, $recordId, $data) {
        self::log('crear_' . $table, $table, $recordId, null, $data);
    }
    
    /**
     * Registrar actualización de registro
     */
    public static function logUpdate($table, $recordId, $oldData, $newData) {
        self::log('actualizar_' . $table, $table, $recordId, $oldData, $newData);
    }
    
    /**
     * Registrar eliminación de registro
     */
    public static function logDelete($table, $recordId, $data) {
        self::log('eliminar_' . $table, $table, $recordId, $data, null);
    }
    
    /**
     * Registrar cambio de estado
     */
    public static function logStatusChange($table, $recordId, $oldStatus, $newStatus) {
        self::log(
            'cambiar_estado_' . $table,
            $table,
            $recordId,
            ['estado' => $oldStatus],
            ['estado' => $newStatus]
        );
    }
    
    /**
     * Registrar check-in
     */
    public static function logCheckin($reservaId, $userId) {
        self::log('checkin', 'reservas', $reservaId, null, ['usuario_id' => $userId]);
    }
    
    /**
     * Registrar check-out
     */
    public static function logCheckout($reservaId, $userId) {
        self::log('checkout', 'reservas', $reservaId, null, ['usuario_id' => $userId]);
    }
    
    /**
     * Obtener logs de un usuario
     */
    public static function getUserLogs($userId, $limit = 100, $offset = 0) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT l.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido
                    FROM logs l
                    LEFT JOIN usuarios u ON l.usuario_id = u.id
                    WHERE l.usuario_id = :usuario_id
                    ORDER BY l.fecha DESC
                    LIMIT :limit OFFSET :offset";
            
            return $db->prepare($sql)
                       ->bind(':usuario_id', $userId)
                       ->bind(':limit', $limit, PDO::PARAM_INT)
                       ->bind(':offset', $offset, PDO::PARAM_INT)
                       ->fetchAll();
        } catch (Exception $e) {
            error_log("Error al obtener logs de usuario: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener logs de una tabla
     */
    public static function getTableLogs($table, $recordId = null, $limit = 100, $offset = 0) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT l.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido
                    FROM logs l
                    LEFT JOIN usuarios u ON l.usuario_id = u.id
                    WHERE l.tabla = :tabla";
            
            $params = [':tabla' => $table];
            
            if ($recordId) {
                $sql .= " AND l.registro_id = :registro_id";
                $params[':registro_id'] = $recordId;
            }
            
            $sql .= " ORDER BY l.fecha DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error al obtener logs de tabla: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener estadísticas de actividad
     */
    public static function getActivityStats($period = 'day') {
        try {
            $db = Database::getInstance();
            
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
                        COUNT(DISTINCT tabla) as tablas_afectadas,
                        COUNT(CASE WHEN accion LIKE '%login%' THEN 1 END) as logins,
                        COUNT(CASE WHEN accion LIKE '%crear%' THEN 1 END) as creaciones,
                        COUNT(CASE WHEN accion LIKE '%actualizar%' THEN 1 END) as actualizaciones,
                        COUNT(CASE WHEN accion LIKE '%eliminar%' THEN 1 END) as eliminaciones
                    FROM logs
                    WHERE {$dateCondition}";
            
            return $db->prepare($sql)->fetch();
        } catch (Exception $e) {
            error_log("Error al obtener estadísticas de actividad: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Limpiar logs antiguos
     */
    public static function cleanOldLogs($days = 90) {
        try {
            $db = Database::getInstance();
            $sql = "DELETE FROM logs WHERE fecha < DATE_SUB(NOW(), INTERVAL :days DAY)";
            
            return $db->prepare($sql)
                       ->bind(':days', $days, PDO::PARAM_INT)
                       ->execute();
        } catch (Exception $e) {
            error_log("Error al limpiar logs antiguos: " . $e->getMessage());
            return false;
        }
    }
}
