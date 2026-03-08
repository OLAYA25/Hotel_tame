<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Verificar sesión de usuario
session_start();
// TEMPORAL: Desactivar verificación de sesión para pruebas
// if (!isset($_SESSION['usuario'])) {
//     http_response_code(401);
//     echo json_encode(['error' => 'No autorizado']);
//     exit;
// }

// Logging para depuración
error_log("Notifications API - Session: " . (isset($_SESSION['usuario']) ? 'YES' : 'NO'));
error_log("Notifications API - User ID: " . ($_SESSION['usuario']['id'] ?? 'NOT SET'));
error_log("Notifications API - Request: " . $_SERVER['REQUEST_URI']);

include_once '../../backend/config/database.php';
include_once '../../lib/NotificationManager.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Soportar tanto el formato antiguo como el nuevo del dashboard
        if(isset($_GET['accion'])) {
            // Formato antiguo (existente)
            switch($_GET['accion']) {
                case 'listar':
                    $notificationManager = NotificationManager::getInstance();
                    $notificationManager->loadFromSession();
                    
                    $notifications = $notificationManager->getAllNotifications();
                    $unread_count = $notificationManager->getUnreadCount();
                    
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'notifications' => $notifications,
                        'unread_count' => $unread_count
                    ]);
                    break;
                    
                case 'no_leidas':
                    $notificationManager = NotificationManager::getInstance();
                    $notificationManager->loadFromSession();
                    
                    $unread_notifications = $notificationManager->getUnreadNotifications();
                    
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'notifications' => $unread_notifications,
                        'count' => count($unread_notifications)
                    ]);
                    break;
                    
                case 'verificar_nuevas':
                    $notificationManager = NotificationManager::getInstance();
                    $notificationManager->loadFromSession();
                    
                    $last_check = $_SESSION['last_notification_check'] ?? 0;
                    $current_time = time();
                    
                    // Generar notificaciones automáticas si ha pasado tiempo
                    if (($current_time - $last_check) > 300) { // 5 minutos
                        $notificationManager->generateSystemNotifications();
                        $_SESSION['last_notification_check'] = $current_time;
                    }
                    
                    // Obtener todas las notificaciones como nuevas (simplificado)
                    $all_notifications = $notificationManager->getAllNotifications();
                    $unread_notifications = $notificationManager->getUnreadNotifications();
                    
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'new_notifications' => $unread_notifications,
                        'count' => count($unread_notifications),
                        'last_check' => $last_check
                    ]);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'Acción no válida']);
                    break;
            }
        } elseif(isset($_GET['type'])) {
            // Nuevo formato del dashboard
            $type = $_GET['type'] ?? '';
            $user_id = $_GET['user_id'] ?? 1;
            
            if (empty($type)) {
                http_response_code(400);
                echo json_encode(['error' => 'Tipo de notificación no especificado']);
                exit;
            }
            
            // Obtener notificaciones según el tipo
            $notifications = getDashboardNotifications($type, $user_id, $db);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'count' => count($notifications)
            ]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetros no especificados']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}

// Función para obtener notificaciones del dashboard
function getDashboardNotifications($type, $user_id, $db) {
    $notifications = [];
    
    switch ($type) {
        case 'system_alerts':
            $notifications = [
                ['id' => 1, 'message' => 'Backup programado en 2 horas', 'type' => 'warning', 'time' => '10:30 AM'],
                ['id' => 2, 'message' => 'Sistema funcionando normalmente', 'type' => 'info', 'time' => '09:15 AM'],
                ['id' => 3, 'message' => 'Actualización disponible', 'type' => 'success', 'time' => '08:00 AM']
            ];
            break;
            
        case 'security_issues':
            $notifications = [
                ['id' => 1, 'message' => 'Intento de acceso detectado', 'type' => 'danger', 'time' => '11:45 AM'],
                ['id' => 2, 'message' => 'Contraseña expirando pronto', 'type' => 'warning', 'time' => '10:20 AM']
            ];
            break;
            
        case 'backup_status':
            $notifications = [
                ['id' => 1, 'message' => 'Backup completado exitosamente', 'type' => 'success', 'time' => '06:00 AM'],
                ['id' => 2, 'message' => 'Próximo backup en 22 horas', 'type' => 'info', 'time' => '08:00 AM']
            ];
            break;
            
        case 'user_activity':
            $notifications = [
                ['id' => 1, 'message' => 'Nuevo usuario registrado', 'type' => 'info', 'time' => '09:30 AM'],
                ['id' => 2, 'message' => 'Usuario admin inició sesión', 'type' => 'success', 'time' => '08:15 AM'],
                ['id' => 3, 'message' => 'Múltiples intentos de login fallidos', 'type' => 'warning', 'time' => '07:45 AM']
            ];
            break;
            
        default:
            $notifications = [
                ['id' => 1, 'message' => 'Notificación genérica', 'type' => 'info', 'time' => date('h:i A')]
            ];
            break;
    }
    
    return $notifications;
}
?>
