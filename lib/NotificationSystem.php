<?php
/**
 * NotificationSystem - Sistema de notificaciones en tiempo real
 * Soporta múltiples canales y tipos de notificaciones
 */
class NotificationSystem {
    private $db;
    private $notifications = [];
    
    public function __construct($database) {
        $this->db = $database->getConnection();
        $this->initializeTables();
    }
    
    /**
     * Inicializar tablas de notificaciones
     */
    private function initializeTables() {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS notificaciones (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                tipo VARCHAR(50) NOT NULL,
                titulo VARCHAR(255) NOT NULL,
                mensaje TEXT NOT NULL,
                data JSON,
                leida BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_usuario (usuario_id),
                INDEX idx_tipo (tipo),
                INDEX idx_leida (leida),
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
            )
        ");
        
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS notificaciones_config (
                usuario_id INT PRIMARY KEY,
                email_notificaciones BOOLEAN DEFAULT TRUE,
                browser_notificaciones BOOLEAN DEFAULT TRUE,
                tipos_habilitados JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
            )
        ");
    }
    
    /**
     * Crear nueva notificación
     */
    public function createNotification($usuario_id, $tipo, $titulo, $mensaje, $data = null) {
        $stmt = $this->db->prepare("
            INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, data) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $data_json = $data ? json_encode($data) : null;
        $stmt->execute([$usuario_id, $tipo, $titulo, $mensaje, $data_json]);
        
        $notification_id = $this->db->lastInsertId();
        
        // Enviar notificación en tiempo real si está configurado
        $this->sendRealTimeNotification($usuario_id, $notification_id, $tipo, $titulo, $mensaje, $data);
        
        return $notification_id;
    }
    
    /**
     * Crear notificación masiva
     */
    public function createBulkNotification($usuarios, $tipo, $titulo, $mensaje, $data = null) {
        $notifications = [];
        foreach ($usuarios as $usuario_id) {
            $notifications[] = $this->createNotification($usuario_id, $tipo, $titulo, $mensaje, $data);
        }
        return $notifications;
    }
    
    /**
     * Notificaciones automáticas del sistema
     */
    public function checkSystemNotifications() {
        $this->checkLowOccupancy();
        $this->checkHighDemand();
        $this->checkPendingReservations();
        $this->checkMaintenanceRooms();
        $this->checkRevenueGoals();
    }
    
    /**
     * Verificar baja ocupación
     */
    private function checkLowOccupancy() {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'ocupada' THEN 1 ELSE 0 END) as ocupadas
            FROM habitaciones 
            WHERE deleted_at IS NULL
        ");
        $result = $stmt->fetch();
        
        $ocupacion = $result['total'] > 0 ? ($result['ocupadas'] / $result['total']) * 100 : 0;
        
        if ($ocupacion < 30) {
            $usuarios = $this->getAdminUsers();
            $this->createBulkNotification(
                $usuarios,
                'alerta_ocupacion',
                '⚠️ Baja Ocupación Detectada',
                "La ocupación actual es del " . round($ocupacion, 1) . "%. Considere iniciar campañas promocionales.",
                ['ocupacion' => $ocupacion, 'tipo' => 'baja']
            );
        }
    }
    
    /**
     * Verificar alta demanda
     */
    private function checkHighDemand() {
        $stmt = $this->db->query("
            SELECT COUNT(*) as reservas_hoy
            FROM reservas 
            WHERE DATE(fecha_entrada) = CURRENT_DATE
            AND estado = 'confirmada'
            AND deleted_at IS NULL
        ");
        $result = $stmt->fetch();
        
        if ($result['reservas_hoy'] > 10) { // Umbral configurable
            $usuarios = $this->getAdminUsers();
            $this->createBulkNotification(
                $usuarios,
                'alerta_demanda',
                '🔥 Alta Demanda Detectada',
                "Se han registrado {$result['reservas_hoy']} reservas para hoy. Prepare personal adicional.",
                ['reservas' => $result['reservas_hoy'], 'tipo' => 'alta']
            );
        }
    }
    
    /**
     * Verificar reservas pendientes
     */
    private function checkPendingReservations() {
        $stmt = $this->db->query("
            SELECT COUNT(*) as pendientes
            FROM reservas 
            WHERE estado = 'pendiente'
            AND created_at < DATE_SUB(NOW(), INTERVAL 2 HOUR)
            AND deleted_at IS NULL
        ");
        $result = $stmt->fetch();
        
        if ($result['pendientes'] > 0) {
            $usuarios = $this->getReceptionUsers();
            $this->createBulkNotification(
                $usuarios,
                'reservas_pendientes',
                '⏰ Reservas Pendientes',
                "Hay {$result['pendientes']} reservas pendientes por más de 2 horas.",
                ['cantidad' => $result['pendientes']]
            );
        }
    }
    
    /**
     * Verificar habitaciones en mantenimiento
     */
    private function checkMaintenanceRooms() {
        $stmt = $this->db->query("
            SELECT COUNT(*) as mantenimiento, 
                   GROUP_CONCAT(numero) as habitaciones
            FROM habitaciones 
            WHERE estado = 'mantenimiento'
            AND deleted_at IS NULL
        ");
        $result = $stmt->fetch();
        
        if ($result['mantenimiento'] > 0) {
            $usuarios = $this->getMaintenanceUsers();
            $this->createBulkNotification(
                $usuarios,
                'habitaciones_mantenimiento',
                '🔧 Habitaciones en Mantenimiento',
                "Hay {$result['mantenimiento']} habitaciones en mantenimiento: {$result['habitaciones']}.",
                ['habitaciones' => explode(',', $result['habitaciones'])]
            );
        }
    }
    
    /**
     * Verificar metas de revenue
     */
    private function checkRevenueGoals() {
        $stmt = $this->db->query("
            SELECT 
                SUM(precio_total) as revenue_actual,
                (SELECT meta_revenue FROM metas_hotel WHERE mes = MONTH(CURRENT_DATE) AND anio = YEAR(CURRENT_DATE)) as meta
            FROM reservas 
            WHERE MONTH(fecha_entrada) = MONTH(CURRENT_DATE)
            AND YEAR(fecha_entrada) = YEAR(CURRENT_DATE)
            AND estado = 'confirmada'
            AND deleted_at IS NULL
        ");
        $result = $stmt->fetch();
        
        if ($result['meta'] && $result['revenue_actual'] > 0) {
            $porcentaje = ($result['revenue_actual'] / $result['meta']) * 100;
            
            if ($porcentaje >= 90 && $porcentaje < 100) {
                $usuarios = $this->getAdminUsers();
                $this->createBulkNotification(
                    $usuarios,
                    'meta_cerca',
                    '🎯 Meta Casi Alcanzada',
                    "Llevan el " . round($porcentaje, 1) . "% de la meta de revenue del mes.",
                    ['porcentaje' => $porcentaje, 'actual' => $result['revenue_actual'], 'meta' => $result['meta']]
                );
            } elseif ($porcentaje >= 100) {
                $usuarios = $this->getAllUsers();
                $this->createBulkNotification(
                    $usuarios,
                    'meta_superada',
                    '🏆 Meta de Revenue Superada',
                    "¡Felicidades! Han superado la meta de revenue del mes.",
                    ['porcentaje' => $porcentaje, 'actual' => $result['revenue_actual'], 'meta' => $result['meta']]
                );
            }
        }
    }
    
    /**
     * Obtener notificaciones de usuario
     */
    public function getUserNotifications($usuario_id, $limit = 20, $unread_only = false) {
        $sql = "
            SELECT id, tipo, titulo, mensaje, data, leida, created_at
            FROM notificaciones 
            WHERE usuario_id = ?
        ";
        
        $params = [$usuario_id];
        
        if ($unread_only) {
            $sql .= " AND leida = FALSE";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $notifications = $stmt->fetchAll();
        
        // Decodificar data JSON
        foreach ($notifications as &$notification) {
            $notification['data'] = $notification['data'] ? json_decode($notification['data'], true) : null;
        }
        
        return $notifications;
    }
    
    /**
     * Marcar notificación como leída
     */
    public function markAsRead($notification_id, $usuario_id) {
        $stmt = $this->db->prepare("
            UPDATE notificaciones 
            SET leida = TRUE, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ? AND usuario_id = ?
        ");
        return $stmt->execute([$notification_id, $usuario_id]);
    }
    
    /**
     * Marcar todas como leídas
     */
    public function markAllAsRead($usuario_id) {
        $stmt = $this->db->prepare("
            UPDATE notificaciones 
            SET leida = TRUE, updated_at = CURRENT_TIMESTAMP 
            WHERE usuario_id = ? AND leida = FALSE
        ");
        return $stmt->execute([$usuario_id]);
    }
    
    /**
     * Contar notificaciones no leídas
     */
    public function getUnreadCount($usuario_id) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM notificaciones 
            WHERE usuario_id = ? AND leida = FALSE
        ");
        $stmt->execute([$usuario_id]);
        return $stmt->fetch()['count'];
    }
    
    /**
     * Configurar preferencias de notificaciones
     */
    public function updateNotificationPreferences($usuario_id, $preferences) {
        $stmt = $this->db->prepare("
            INSERT INTO notificaciones_config (usuario_id, email_notificaciones, browser_notificaciones, tipos_habilitados)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            email_notificaciones = VALUES(email_notificaciones),
            browser_notificaciones = VALUES(browser_notificaciones),
            tipos_habilitados = VALUES(tipos_habilitados),
            updated_at = CURRENT_TIMESTAMP
        ");
        
        return $stmt->execute([
            $usuario_id,
            $preferences['email'] ?? true,
            $preferences['browser'] ?? true,
            json_encode($preferences['tipos'] ?? [])
        ]);
    }
    
    /**
     * Enviar notificación en tiempo real
     */
    private function sendRealTimeNotification($usuario_id, $notification_id, $tipo, $titulo, $mensaje, $data) {
        // Aquí se integraría con WebSocket o Server-Sent Events
        // Por ahora, almacenamos para polling
        
        $this->notifications[] = [
            'id' => $notification_id,
            'usuario_id' => $usuario_id,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'data' => $data,
            'timestamp' => time()
        ];
    }
    
    /**
     * Obtener notificaciones para polling
     */
    public function getPollingNotifications($usuario_id, $last_timestamp = 0) {
        $new_notifications = array_filter($this->notifications, function($notif) use ($usuario_id, $last_timestamp) {
            return $notif['usuario_id'] == $usuario_id && $notif['timestamp'] > $last_timestamp;
        });
        
        return array_values($new_notifications);
    }
    
    /**
     * Obtener usuarios administradores
     */
    private function getAdminUsers() {
        $stmt = $this->db->query("
            SELECT u.id 
            FROM usuarios u
            JOIN usuarios_roles ur ON u.id = ur.usuario_id
            JOIN roles r ON ur.rol_id = r.id
            WHERE r.nombre = 'Administrador' 
            AND u.deleted_at IS NULL
        ");
        return array_column($stmt->fetchAll(), 'id');
    }
    
    /**
     * Obtener usuarios de recepción
     */
    private function getReceptionUsers() {
        $stmt = $this->db->query("
            SELECT u.id 
            FROM usuarios u
            JOIN usuarios_roles ur ON u.id = ur.usuario_id
            JOIN roles r ON ur.rol_id = r.id
            WHERE r.nombre IN ('Administrador', 'Recepcionista') 
            AND u.deleted_at IS NULL
        ");
        return array_column($stmt->fetchAll(), 'id');
    }
    
    /**
     * Obtener usuarios de mantenimiento
     */
    private function getMaintenanceUsers() {
        $stmt = $this->db->query("
            SELECT u.id 
            FROM usuarios u
            JOIN usuarios_roles ur ON u.id = ur.usuario_id
            JOIN roles r ON ur.rol_id = r.id
            WHERE r.nombre IN ('Administrador', 'Mantenimiento') 
            AND u.deleted_at IS NULL
        ");
        return array_column($stmt->fetchAll(), 'id');
    }
    
    /**
     * Obtener todos los usuarios activos
     */
    private function getAllUsers() {
        $stmt = $this->db->query("
            SELECT id FROM usuarios WHERE deleted_at IS NULL
        ");
        return array_column($stmt->fetchAll(), 'id');
    }
    
    /**
     * Limpiar notificaciones antiguas
     */
    public function cleanupOldNotifications($days = 30) {
        $stmt = $this->db->prepare("
            DELETE FROM notificaciones 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            AND leida = TRUE
        ");
        return $stmt->execute([$days]);
    }
}
?>
