<?php
/**
 * NotificationManager - Gestor de notificaciones visuales del sistema
 * Maneja notificaciones en tiempo real para el frontend
 */
require_once __DIR__ . '/../backend/config/database.php';

class NotificationManager {
    private static $instance = null;
    private $notifications = [];
    private $unread_count = 0;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Agregar notificación al sistema
     */
    public function addNotification($type, $title, $message, $data = null, $persistent = false) {
        $notification = [
            'id' => uniqid('notif_', true),
            'type' => $type, // success, error, warning, info
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'persistent' => $persistent,
            'timestamp' => time(),
            'read' => false
        ];
        
        $this->notifications[] = $notification;
        $this->unread_count++;
        
        // Guardar en sesión para persistencia
        $_SESSION['notifications'] = $this->notifications;
        $_SESSION['unread_notifications'] = $this->unread_count;
        
        return $notification['id'];
    }
    
    /**
     * Obtener notificaciones no leídas
     */
    public function getUnreadNotifications() {
        return array_filter($this->notifications, function($notif) {
            return !$notif['read'];
        });
    }
    
    /**
     * Marcar notificación como leída
     */
    public function markAsRead($notification_id) {
        foreach ($this->notifications as &$notification) {
            if ($notification['id'] === $notification_id) {
                $notification['read'] = true;
                $this->unread_count--;
                break;
            }
        }
        
        $_SESSION['notifications'] = $this->notifications;
        $_SESSION['unread_notifications'] = $this->unread_count;
    }
    
    /**
     * Marcar todas como leídas
     */
    public function markAllAsRead() {
        foreach ($this->notifications as &$notification) {
            $notification['read'] = true;
        }
        $this->unread_count = 0;
        
        $_SESSION['notifications'] = $this->notifications;
        $_SESSION['unread_notifications'] = $this->unread_count;
    }
    
    /**
     * Obtener contador de no leídas
     */
    public function getUnreadCount() {
        return $this->unread_count;
    }
    
    /**
     * Obtener todas las notificaciones
     */
    public function getAllNotifications() {
        return $this->notifications;
    }
    
    /**
     * Limpiar notificaciones antiguas
     */
    public function cleanup($max_age = 86400) { // 24 horas por defecto
        $current_time = time();
        $this->notifications = array_filter($this->notifications, function($notif) use ($current_time, $max_age) {
            return $notif['persistent'] || ($current_time - $notif['timestamp']) < $max_age;
        });
        
        $_SESSION['notifications'] = $this->notifications;
    }
    
    /**
     * Cargar notificaciones desde sesión
     */
    public function loadFromSession() {
        $this->notifications = $_SESSION['notifications'] ?? [];
        $this->unread_count = $_SESSION['unread_notifications'] ?? 0;
    }
    
    /**
     * Generar notificaciones automáticas del sistema
     */
    public function generateSystemNotifications() {
        // Verificar reservas pendientes
        $this->checkPendingReservations();
        
        // Verificar habitaciones en mantenimiento
        $this->checkMaintenanceRooms();
        
        // Verificar metas de revenue
        $this->checkRevenueGoals();
        
        // Verificar ocupación baja
        $this->checkLowOccupancy();
    }
    
    /**
     * Verificar reservas pendientes
     */
    private function checkPendingReservations() {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $stmt = $db->query("
                SELECT COUNT(*) as count 
                FROM reservas 
                WHERE estado = 'pendiente'
                AND created_at < DATE_SUB(NOW(), INTERVAL 2 HOUR)
                AND deleted_at IS NULL
            ");
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                $this->addNotification(
                    'warning',
                    'Reservas Pendientes',
                    "Hay {$result['count']} reservas pendientes por más de 2 horas.",
                    ['count' => $result['count'], 'type' => 'pending_reservations'],
                    true
                );
            }
        } catch (Exception $e) {
            error_log("Error verificando reservas pendientes: " . $e->getMessage());
        }
    }
    
    /**
     * Verificar habitaciones en mantenimiento
     */
    private function checkMaintenanceRooms() {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $stmt = $db->query("
                SELECT COUNT(*) as count, GROUP_CONCAT(numero SEPARATOR ', ') as rooms
                FROM habitaciones 
                WHERE estado = 'mantenimiento'
                AND deleted_at IS NULL
            ");
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                $this->addNotification(
                    'info',
                    'Habitaciones en Mantenimiento',
                    "Hay {$result['count']} habitaciones en mantenimiento: {$result['rooms']}.",
                    ['count' => $result['count'], 'rooms' => explode(', ', $result['rooms'])],
                    true
                );
            }
        } catch (Exception $e) {
            error_log("Error verificando habitaciones en mantenimiento: " . $e->getMessage());
        }
    }
    
    /**
     * Verificar metas de revenue
     */
    private function checkRevenueGoals() {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $stmt = $db->query("
                SELECT 
                    SUM(precio_total) as actual,
                    (SELECT meta_revenue FROM metas_hotel 
                     WHERE mes = MONTH(CURRENT_DATE) AND anio = YEAR(CURRENT_DATE)) as meta
                FROM reservas 
                WHERE MONTH(fecha_entrada) = MONTH(CURRENT_DATE)
                AND YEAR(fecha_entrada) = YEAR(CURRENT_DATE)
                AND estado = 'confirmada'
                AND deleted_at IS NULL
            ");
            $result = $stmt->fetch();
            
            if ($result['meta'] && $result['actual'] > 0) {
                $percentage = ($result['actual'] / $result['meta']) * 100;
                
                if ($percentage >= 90 && $percentage < 100) {
                    $this->addNotification(
                        'success',
                        'Meta Casi Alcanzada',
                        "Llevan el " . round($percentage, 1) . "% de la meta de revenue del mes.",
                        ['percentage' => $percentage, 'actual' => $result['actual'], 'meta' => $result['meta']],
                        true
                    );
                } elseif ($percentage >= 100) {
                    $this->addNotification(
                        'success',
                        '🏆 Meta de Revenue Superada',
                        "¡Felicidades! Han superado la meta de revenue del mes.",
                        ['percentage' => $percentage, 'actual' => $result['actual'], 'meta' => $result['meta']],
                        true
                    );
                }
            }
        } catch (Exception $e) {
            error_log("Error verificando metas de revenue: " . $e->getMessage());
        }
    }
    
    /**
     * Verificar ocupación baja
     */
    private function checkLowOccupancy() {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $stmt = $db->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = 'ocupada' THEN 1 ELSE 0 END) as ocupadas
                FROM habitaciones 
                WHERE deleted_at IS NULL
            ");
            $result = $stmt->fetch();
            
            $occupancy = $result['total'] > 0 ? ($result['ocupadas'] / $result['total']) * 100 : 0;
            
            if ($occupancy < 30) {
                $this->addNotification(
                    'warning',
                    'Baja Ocupación Detectada',
                    "La ocupación actual es del " . round($occupancy, 1) . "%. Considere iniciar campañas promocionales.",
                    ['occupancy' => $occupancy, 'total' => $result['total'], 'occupied' => $result['ocupadas']],
                    true
                );
            }
        } catch (Exception $e) {
            error_log("Error verificando ocupación: " . $e->getMessage());
        }
    }
    
    /**
     * Generar notificación de nueva reserva
     */
    public function notifyNewReservation($reservation_data) {
        $this->addNotification(
            'success',
            'Nueva Reserva',
            "Reserva #{$reservation_data['id']} creada para {$reservation_data['cliente_nombre']}.",
            $reservation_data,
            false
        );
    }
    
    /**
     * Generar notificación de check-in
     */
    public function notifyCheckIn($reservation_data) {
        $this->addNotification(
            'info',
            'Check-In Realizado',
            "Check-in de {$reservation_data['cliente_nombre']} en habitación {$reservation_data['habitacion_numero']}.",
            $reservation_data,
            false
        );
    }
    
    /**
     * Generar notificación de check-out
     */
    public function notifyCheckOut($reservation_data) {
        $this->addNotification(
            'info',
            'Check-Out Realizado',
            "Check-out de {$reservation_data['cliente_nombre']} de habitación {$reservation_data['habitacion_numero']}.",
            $reservation_data,
            false
        );
    }
    
    /**
     * Generar notificación de cancelación
     */
    public function notifyCancellation($reservation_data) {
        $this->addNotification(
            'warning',
            'Reserva Cancelada',
            "Reserva #{$reservation_data['id']} cancelada por {$reservation_data['cliente_nombre']}.",
            $reservation_data,
            false
        );
    }
}
?>
