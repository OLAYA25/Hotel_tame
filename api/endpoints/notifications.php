<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../lib/NotificationManager.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['accion'])) {
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
                    
                    // Obtener notificaciones no leídas
                    $unread_notifications = $notificationManager->getUnreadNotifications();
                    
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'new_notifications' => $unread_notifications,
                        'count' => count($unread_notifications)
                    ]);
                    break;
                    
                case 'contador':
                    $notificationManager = NotificationManager::getInstance();
                    $notificationManager->loadFromSession();
                    
                    $unread_count = $notificationManager->getUnreadCount();
                    
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'unread_count' => $unread_count
                    ]);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(array("message" => "Acción no válida"));
                    break;
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Acción no especificada"));
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if(isset($data['accion'])) {
            switch($data['accion']) {
                case 'crear':
                    $notificationManager = NotificationManager::getInstance();
                    $notificationManager->loadFromSession();
                    
                    $type = $data['tipo'] ?? 'info';
                    $title = $data['titulo'] ?? 'Notificación';
                    $message = $data['mensaje'] ?? '';
                    $notification_data = $data['datos'] ?? null;
                    $persistent = $data['persistente'] ?? false;
                    
                    $notification_id = $notificationManager->addNotification(
                        $type, 
                        $title, 
                        $message, 
                        $notification_data, 
                        $persistent
                    );
                    
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Notificación creada exitosamente',
                        'notification_id' => $notification_id
                    ]);
                    break;
                    
                case 'marcar_leida':
                    $notification_id = $data['notification_id'] ?? '';
                    
                    if (empty($notification_id)) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'ID de notificación no especificado']);
                        break;
                    }
                    
                    $notificationManager = NotificationManager::getInstance();
                    $notificationManager->loadFromSession();
                    $notificationManager->markAsRead($notification_id);
                    
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Notificación marcada como leída'
                    ]);
                    break;
                    
                case 'marcar_todas_leidas':
                    $notificationManager = NotificationManager::getInstance();
                    $notificationManager->loadFromSession();
                    $notificationManager->markAllAsRead();
                    
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Todas las notificaciones marcadas como leídas'
                    ]);
                    break;
                    
                case 'eliminar':
                    $notification_id = $data['notification_id'] ?? '';
                    
                    if (empty($notification_id)) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'ID de notificación no especificado']);
                        break;
                    }
                    
                    $notificationManager = NotificationManager::getInstance();
                    $notificationManager->loadFromSession();
                    
                    // Eliminar notificación del array
                    $notifications = $notificationManager->getAllNotifications();
                    $notifications = array_filter($notifications, function($notif) use ($notification_id) {
                        return $notif['id'] !== $notification_id;
                    });
                    
                    $_SESSION['notifications'] = array_values($notifications);
                    
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Notificación eliminada exitosamente'
                    ]);
                    break;
                    
                case 'limpiar':
                    $notificationManager = NotificationManager::getInstance();
                    $notificationManager->loadFromSession();
                    
                    // Eliminar todas las notificaciones no persistentes
                    $notifications = $notificationManager->getAllNotifications();
                    $notifications = array_filter($notifications, function($notif) {
                        return $notif['persistent'];
                    });
                    
                    $_SESSION['notifications'] = array_values($notifications);
                    $_SESSION['unread_notifications'] = 0;
                    
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Notificaciones limpiadas exitosamente'
                    ]);
                    break;
                    
                case 'generar_sistema':
                    $notificationManager = NotificationManager::getInstance();
                    $notificationManager->loadFromSession();
                    $notificationManager->generateSystemNotifications();
                    
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Notificaciones del sistema generadas'
                    ]);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(array("message" => "Acción no válida"));
                    break;
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Acción no especificada"));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Método no permitido"));
        break;
}
?>
