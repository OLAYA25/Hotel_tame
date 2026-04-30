<?php

namespace App\Services;

use Database\Database;
use App\Services\AppLogger;
use App\Services\EventDispatcher;
use Exception;

class NotificationService {
    
    /**
     * Create and send notification
     */
    public function createNotification(array $notificationData): int {
        $sql = "INSERT INTO notifications 
                (tipo, titulo, mensaje, icono, color, link, datos_adicionales, para_todos, hotel_id) 
                VALUES (:tipo, :titulo, :mensaje, :icono, :color, :link, :datos_adicionales, :para_todos, :hotel_id)";
        
        $params = array_merge([
            ':hotel_id' => 1,
            ':icono' => 'info',
            ':color' => 'blue',
            ':para_todos' => 0
        ], $notificationData);
        
        Database::execute($sql, $params);
        $notificationId = Database::lastInsertId();
        
        // Send to users if not for everyone
        if (!$notificationData['para_todos'] && isset($notificationData['usuarios'])) {
            $this->sendToUsers($notificationId, $notificationData['usuarios']);
        } elseif ($notificationData['para_todos']) {
            $this->sendToAllUsers($notificationId);
        }
        
        AppLogger::business('Notification created', [
            'notification_id' => $notificationId,
            'type' => $notificationData['tipo'],
            'title' => $notificationData['titulo']
        ]);
        
        return $notificationId;
    }
    
    /**
     * Send notification to specific users
     */
    public function sendToUsers(int $notificationId, array $userIds): void {
        foreach ($userIds as $userId) {
            $sql = "INSERT INTO user_notifications (notification_id, usuario_id, hotel_id) 
                    VALUES (:notification_id, :usuario_id, :hotel_id)";
            
            Database::execute($sql, [
                ':notification_id' => $notificationId,
                ':usuario_id' => $userId,
                ':hotel_id' => 1
            ]);
        }
    }
    
    /**
     * Send notification to all users
     */
    public function sendToAllUsers(int $notificationId): void {
        $sql = "INSERT INTO user_notifications (notification_id, usuario_id, hotel_id)
                SELECT :notification_id, id, :hotel_id FROM usuarios WHERE deleted_at IS NULL AND activo = 1";
        
        Database::execute($sql, [
            ':notification_id' => $notificationId,
            ':hotel_id' => 1
        ]);
    }
    
    /**
     * Get user notifications
     */
    public function getUserNotifications(int $userId, bool $unreadOnly = false, int $limit = 50): array {
        $sql = "SELECT n.*, un.leida, un.fecha_leida, un.fecha_envio
                FROM notifications n
                JOIN user_notifications un ON n.id = un.notification_id
                WHERE un.usuario_id = :usuario_id AND un.deleted_at IS NULL";
        
        $params = [':usuario_id' => $userId];
        
        if ($unreadOnly) {
            $sql .= " AND un.leida = 0";
        }
        
        $sql .= " ORDER BY un.fecha_envio DESC LIMIT :limit";
        
        return Database::fetchAll($sql, array_merge($params, [':limit' => $limit]));
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId, int $userId): bool {
        $sql = "UPDATE user_notifications 
                SET leida = 1, fecha_leida = NOW(), updated_at = NOW()
                WHERE notification_id = :notification_id AND usuario_id = :usuario_id";
        
        $result = Database::execute($sql, [
            ':notification_id' => $notificationId,
            ':usuario_id' => $userId
        ]);
        
        AppLogger::business('Notification marked as read', [
            'notification_id' => $notificationId,
            'user_id' => $userId
        ]);
        
        return $result;
    }
    
    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead(int $userId): int {
        $sql = "UPDATE user_notifications 
                SET leida = 1, fecha_leida = NOW(), updated_at = NOW()
                WHERE usuario_id = :usuario_id AND leida = 0";
        
        Database::execute($sql, [':usuario_id' => $userId]);
        
        $count = Database::fetch("SELECT ROW_COUNT() as count")['count'];
        
        AppLogger::business('All notifications marked as read', [
            'user_id' => $userId,
            'count' => $count
        ]);
        
        return $count;
    }
    
    /**
     * Get unread count for user
     */
    public function getUnreadCount(int $userId): int {
        $sql = "SELECT COUNT(*) as count FROM user_notifications 
                WHERE usuario_id = :usuario_id AND leida = 0 AND deleted_at IS NULL";
        
        return Database::fetch($sql, [':usuario_id' => $userId])['count'] ?? 0;
    }
    
    /**
     * Create notification from template
     */
    public function createFromTemplate(string $templateType, array $variables, array $userIds = null): int {
        $templateSql = "SELECT * FROM notification_templates 
                        WHERE tipo = :tipo AND activo = 1 AND deleted_at IS NULL";
        
        $template = Database::fetch($templateSql, [':tipo' => $templateType]);
        
        if (!$template) {
            throw new Exception("Template not found: $templateType");
        }
        
        // Replace variables in title and message
        $title = $this->replaceVariables($template['titulo'], $variables);
        $message = $this->replaceVariables($template['mensaje'], $variables);
        
        $notificationData = [
            'tipo' => $templateType,
            'titulo' => $title,
            'mensaje' => $message,
            'icono' => $template['icono'],
            'color' => $template['color'],
            'datos_adicionales' => json_encode($variables),
            'para_todos' => $userIds === null ? 1 : 0,
            'usuarios' => $userIds
        ];
        
        return $this->createNotification($notificationData);
    }
    
    /**
     * Replace variables in template
     */
    private function replaceVariables(string $text, array $variables): string {
        foreach ($variables as $key => $value) {
            $text = str_replace('{' . $key . '}', $value, $text);
        }
        return $text;
    }
    
    /**
     * Get notification statistics
     */
    public function getNotificationStatistics(string $period = 'week'): array {
        $dateCondition = $this->getDateCondition($period);
        
        $sql = "SELECT 
                    COUNT(*) as total_notifications,
                    COUNT(CASE WHEN n.para_todos = 1 THEN 1 END) as global_notifications,
                    COUNT(DISTINCT n.tipo) as unique_types,
                    COUNT(DISTINCT un.usuario_id) as unique_users
                FROM notifications n
                LEFT JOIN user_notifications un ON n.id = un.notification_id
                WHERE {$dateCondition} AND n.deleted_at IS NULL";
        
        $stats = Database::fetch($sql);
        
        // Notifications by type
        $typeSql = "SELECT n.tipo, COUNT(*) as count
                   FROM notifications n
                   WHERE {$dateCondition} AND n.deleted_at IS NULL
                   GROUP BY n.tipo
                   ORDER BY count DESC";
        
        $stats['by_type'] = Database::fetchAll($typeSql);
        
        // Read rate
        $readRateSql = "SELECT 
                           COUNT(*) as total_sent,
                           COUNT(CASE WHEN leida = 1 THEN 1 END) as read_count,
                           ROUND(COUNT(CASE WHEN leida = 1 THEN 1 END) * 100.0 / COUNT(*), 2) as read_rate
                        FROM user_notifications un
                        JOIN notifications n ON un.notification_id = n.id
                        WHERE {$dateCondition} AND un.deleted_at IS NULL";
        
        $stats['read_rate'] = Database::fetch($readRateSql);
        
        return $stats;
    }
    
    /**
     * Automatic notifications for daily operations
     */
    public function generateDailyNotifications(): void {
        $today = date('Y-m-d');
        
        // Check-ins today
        $checkinSql = "SELECT COUNT(*) as count FROM reservas 
                      WHERE fecha_entrada = :today AND deleted_at IS NULL";
        $checkinCount = Database::fetch($checkinSql, [':today' => $today])['count'];
        
        if ($checkinCount > 0) {
            $this->createFromTemplate('checkin_hoy', ['cantidad' => $checkinCount]);
        }
        
        // Check-outs today
        $checkoutSql = "SELECT COUNT(*) as count FROM reservas 
                       WHERE fecha_salida = :today AND deleted_at IS NULL";
        $checkoutCount = Database::fetch($checkoutSql, [':today' => $today])['count'];
        
        if ($checkoutCount > 0) {
            $this->createFromTemplate('checkout_hoy', ['cantidad' => $checkoutCount]);
        }
        
        // Urgent maintenance
        $maintenanceSql = "SELECT COUNT(*) as count FROM mantenimiento_habitaciones 
                          WHERE prioridad = 'urgente' AND estado != 'resuelto' AND deleted_at IS NULL";
        $urgentCount = Database::fetch($maintenanceSql)['count'];
        
        if ($urgentCount > 0) {
            $this->createNotification([
                'tipo' => 'mantenimiento_urgente',
                'titulo' => 'Mantenimiento Urgente Pendiente',
                'mensaje' => "Hay $urgentCount solicitudes de mantenimiento urgente sin resolver",
                'icono' => 'alert-triangle',
                'color' => 'red',
                'usuarios' => $this->getMaintenanceStaff(),
                'para_todos' => 0
            ]);
        }
        
        // Low stock supplies
        $stockSql = "SELECT COUNT(*) as count FROM housekeeping_supplies 
                    WHERE stock_actual <= stock_minimo AND deleted_at IS NULL";
        $lowStockCount = Database::fetch($stockSql)['count'];
        
        if ($lowStockCount > 0) {
            $this->createNotification([
                'tipo' => 'stock_bajo',
                'titulo' => 'Stock Bajo en Suministros',
                'mensaje' => "Hay $lowStockCount productos con stock bajo",
                'icono' => 'package',
                'color' => 'orange',
                'usuarios' => $this->getHousekeepingStaff(),
                'para_todos' => 0
            ]);
        }
        
        AppLogger::business('Daily notifications generated', [
            'checkins' => $checkinCount,
            'checkouts' => $checkoutCount,
            'urgent_maintenance' => $urgentCount,
            'low_stock' => $lowStockCount
        ]);
    }
    
    /**
     * Get maintenance staff IDs
     */
    private function getMaintenanceStaff(): array {
        $sql = "SELECT id FROM usuarios WHERE rol = 'mantenimiento' AND deleted_at IS NULL AND activo = 1";
        $staff = Database::fetchAll($sql);
        return array_column($staff, 'id');
    }
    
    /**
     * Get housekeeping staff IDs
     */
    private function getHousekeepingStaff(): array {
        $sql = "SELECT id FROM usuarios WHERE rol = 'limpieza' AND deleted_at IS NULL AND activo = 1";
        $staff = Database::fetchAll($sql);
        return array_column($staff, 'id');
    }
    
    /**
     * Get date condition for SQL queries
     */
    private function getDateCondition(string $period): string {
        return match($period) {
            'day' => 'DATE(n.created_at) = CURDATE()',
            'week' => 'WEEK(n.created_at) = WEEK(NOW())',
            'month' => 'MONTH(n.created_at) = MONTH(NOW()) AND YEAR(n.created_at) = YEAR(NOW())',
            'year' => 'YEAR(n.created_at) = YEAR(NOW())',
            default => '1=1'
        };
    }
    
    /**
     * Delete old notifications
     */
    public function cleanupOldNotifications(int $daysOld = 30): int {
        $sql = "DELETE FROM notifications 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
        
        Database::execute($sql, [':days' => $daysOld]);
        
        $deletedCount = Database::fetch("SELECT ROW_COUNT() as count")['count'];
        
        AppLogger::business('Old notifications cleaned up', [
            'days_old' => $daysOld,
            'deleted_count' => $deletedCount
        ]);
        
        return $deletedCount;
    }
    
    /**
     * Get user notification preferences
     */
    public function getUserPreferences(int $userId): array {
        $sql = "SELECT * FROM notification_preferences WHERE usuario_id = :usuario_id AND deleted_at IS NULL";
        $preferences = Database::fetch($sql, [':usuario_id' => $userId]);
        
        if (!$preferences) {
            // Create default preferences
            $this->createDefaultPreferences($userId);
            return $this->getUserPreferences($userId);
        }
        
        return $preferences;
    }
    
    /**
     * Create default notification preferences
     */
    private function createDefaultPreferences(int $userId): void {
        $sql = "INSERT INTO notification_preferences 
                (usuario_id, email_notifications, sms_notifications, push_notifications, tipos_notificaciones, hotel_id) 
                VALUES (:usuario_id, 1, 0, 1, :tipos_notificaciones, :hotel_id)";
        
        $defaultTypes = json_encode([
            'reserva_creada' => true,
            'reserva_cancelada' => true,
            'pago_recibido' => true,
            'mantenimiento_urgente' => true
        ]);
        
        Database::execute($sql, [
            ':usuario_id' => $userId,
            ':tipos_notificaciones' => $defaultTypes,
            ':hotel_id' => 1
        ]);
    }
    
    /**
     * Update user notification preferences
     */
    public function updatePreferences(int $userId, array $preferences): bool {
        $sql = "UPDATE notification_preferences 
                SET email_notifications = :email_notifications,
                    sms_notifications = :sms_notifications,
                    push_notifications = :push_notifications,
                    tipos_notificaciones = :tipos_notificaciones,
                    updated_at = NOW()
                WHERE usuario_id = :usuario_id";
        
        $params = array_merge([':usuario_id' => $userId], $preferences);
        
        $result = Database::execute($sql, $params);
        
        AppLogger::business('Notification preferences updated', [
            'user_id' => $userId,
            'preferences' => $preferences
        ]);
        
        return $result;
    }
}
