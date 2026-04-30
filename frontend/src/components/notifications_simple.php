<?php
// Versión simplificada del sistema de notificaciones
// Solo carga si hay sesión activa y los archivos existen

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario'])) {
    return; // No mostrar notificaciones si no hay sesión
}

// Cargar NotificationManager solo si existe
if (file_exists(__DIR__ . '/../lib/NotificationManager.php')) {
    require_once __DIR__ . '/../lib/NotificationManager.php';
    
    try {
        $notificationManager = NotificationManager::getInstance();
        $notificationManager->loadFromSession();
        
        // 🚨 GENERACIÓN AUTOMÁTICA DE NOTIFICACIONES DESACTIVADA TEMPORALMENTE
// Para evitar notificaciones flotantes molestas en todas las vistas
/*
        // Generar notificaciones automáticas al cargar
        if (!isset($_SESSION['last_notification_check']) || 
            (time() - $_SESSION['last_notification_check']) > 300) { // 5 minutos
            $notificationManager->generateSystemNotifications();
            $_SESSION['last_notification_check'] = time();
        }
        */
        
        $notifications = $notificationManager->getAllNotifications();
        $unread_count = $notificationManager->getUnreadCount();
        
        // Solo mostrar el componente si hay notificaciones o para debugging
        if (!empty($notifications) || isset($_GET['debug_notifications'])) {
            include 'notifications_ui.php';
        }
        
    } catch (Exception $e) {
        // Si hay error, no mostrar nada pero registrar en log
        error_log("Error en sistema de notificaciones: " . $e->getMessage());
    }
}
?>
