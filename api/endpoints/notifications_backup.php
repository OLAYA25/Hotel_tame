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
//     echo json_encode(['error' => 'No autorizado', 'debug' => 'No session found']);
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
                    
                    $new_notifications = $notificationManager->getNewNotifications($last_check);
                    
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'new_notifications' => $new_notifications,
                        'count' => count($new_notifications)
                    ]);
                    break;
                    
                case 'marcar_leidas':
                    $notificationManager = NotificationManager::getInstance();
                    $notificationManager->loadFromSession();
                    
                    $notification_ids = $_GET['ids'] ?? [];
                    if (!empty($notification_ids)) {
                        $notificationManager->markAsRead($notification_ids);
                    }
                    
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Notificaciones marcadas como leídas'
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
                    $notificationManager = NotificationManager::getInstance();
                    $notificationManager->loadFromSession();
                    
                    $notification_id = $_GET['id'] ?? 0;
                    if ($notification_id > 0) {
                        $notificationManager->deleteNotification($notification_id);
                    }
                    
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Notificación eliminada'
                    ]);
                    break;
                    
                case 'limpiar':
                    $notificationManager = NotificationManager::getInstance();
                    $notificationManager->loadFromSession();
                    
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
            echo json_encode(['error' => 'Acción no especificada']);
        }
        break;
                    
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
            // Soportar peticiones del dashboard (formato nuevo)
            $type = $_GET['type'] ?? '';
            $user_id = $_GET['user_id'] ?? $_SESSION['usuario']['id'] ?? 1;
            
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
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Método no permitido"));
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
            
        case 'occupancy_alerts':
            $notifications = [
                ['id' => 1, 'message' => 'Ocupación del 90% alcanzada', 'type' => 'warning', 'time' => '10:00 AM'],
                ['id' => 2, 'message' => 'Habitación 105 necesita limpieza', 'type' => 'info', 'time' => '09:30 AM']
            ];
            break;
            
        case 'staff_issues':
            $notifications = [
                ['id' => 1, 'message' => 'Personal de mantenimiento reportado ausente', 'type' => 'warning', 'time' => '08:00 AM'],
                ['id' => 2, 'message' => 'Turno de recepción cubierto', 'type' => 'success', 'time' => '07:30 AM']
            ];
            break;
            
        case 'revenue_alerts':
            $notifications = [
                ['id' => 1, 'message' => 'Meta de ingresos diaria alcanzada', 'type' => 'success', 'time' => '04:00 PM'],
                ['id' => 2, 'message' => 'Reserva premium confirmada', 'type' => 'success', 'time' => '02:30 PM']
            ];
            break;
            
        case 'guest_feedback':
            $notifications = [
                ['id' => 1, 'message' => 'Nueva reseña de 5 estrellas', 'type' => 'success', 'time' => '03:45 PM'],
                ['id' => 2, 'message' => 'Queja de cliente en habitación 201', 'type' => 'warning', 'time' => '11:20 AM']
            ];
            break;
            
        case 'new_reservations':
            $notifications = [
                ['id' => 1, 'message' => 'Nueva reserva para habitación 305', 'type' => 'info', 'time' => '02:15 PM'],
                ['id' => 2, 'message' => 'Reserva cancelada - habitación 102', 'type' => 'warning', 'time' => '01:45 PM']
            ];
            break;
            
        case 'checkin_pending':
            $notifications = [
                ['id' => 1, 'message' => 'Check-in pendiente - Juan Pérez', 'type' => 'info', 'time' => '02:00 PM'],
                ['id' => 2, 'message' => 'Check-in pendiente - María García', 'type' => 'info', 'time' => '01:30 PM']
            ];
            break;
            
        case 'guest_messages':
            $notifications = [
                ['id' => 1, 'message' => 'Mensaje de huésped - Hab. 101', 'type' => 'info', 'time' => '12:30 PM'],
                ['id' => 2, 'message' => 'Solicitud de room service - Hab. 205', 'type' => 'info', 'time' => '11:45 AM']
            ];
            break;
            
        case 'payment_alerts':
            $notifications = [
                ['id' => 1, 'message' => 'Pago procesado exitosamente', 'type' => 'success', 'time' => '03:00 PM'],
                ['id' => 2, 'message' => 'Tarjeta rechazada - Hab. 103', 'type' => 'danger', 'time' => '10:30 AM']
            ];
            break;
            
        case 'payment_processed':
            $notifications = [
                ['id' => 1, 'message' => 'Pago de reserva #1234 procesado', 'type' => 'success', 'time' => '02:45 PM'],
                ['id' => 2, 'message' => 'Reembolso emitido - Reserva #5678', 'type' => 'info', 'time' => '11:15 AM']
            ];
            break;
            
        case 'invoice_due':
            $notifications = [
                ['id' => 1, 'message' => 'Factura #1234 vence mañana', 'type' => 'warning', 'time' => '09:00 AM'],
                ['id' => 2, 'message' => 'Factura #1235 vence en 3 días', 'type' => 'info', 'time' => '08:30 AM']
            ];
            break;
            
        case 'expense_reports':
            $notifications = [
                ['id' => 1, 'message' => 'Reporte de gastos pendiente aprobación', 'type' => 'warning', 'time' => '10:15 AM'],
                ['id' => 2, 'message' => 'Gastos de mantenimiento aprobados', 'type' => 'success', 'time' => '09:30 AM']
            ];
            break;
            
        case 'financial_alerts':
            $notifications = [
                ['id' => 1, 'message' => 'Presupuesto mensual casi agotado', 'type' => 'warning', 'time' => '11:00 AM'],
                ['id' => 2, 'message' => 'Ingresos superaron proyección', 'type' => 'success', 'time' => '04:30 PM']
            ];
            break;
            
        case 'work_orders':
            $notifications = [
                ['id' => 1, 'message' => 'Orden de trabajo #123 completada', 'type' => 'success', 'time' => '03:15 PM'],
                ['id' => 2, 'message' => 'Nueva orden de trabajo - Hab. 201', 'type' => 'info', 'time' => '10:45 AM']
            ];
            break;
            
        case 'emergency_requests':
            $notifications = [
                ['id' => 1, 'message' => 'Emergencia: Fuga en habitación 101', 'type' => 'danger', 'time' => '10:30 AM'],
                ['id' => 2, 'message' => 'Emergencia eléctrica resuelta', 'type' => 'success', 'time' => '09:15 AM']
            ];
            break;
            
        case 'equipment_status':
            $notifications = [
                ['id' => 1, 'message' => 'Aire acondicionado requiere mantenimiento', 'type' => 'warning', 'time' => '08:45 AM'],
                ['id' => 2, 'message' => 'Calentador funcionando normalmente', 'type' => 'success', 'time' => '07:00 AM']
            ];
            break;
            
        case 'maintenance_schedule':
            $notifications = [
                ['id' => 1, 'message' => 'Mantenimiento programado - Piso 2', 'type' => 'info', 'time' => '09:00 AM'],
                ['id' => 2, 'message' => 'Inspección de seguridad semanal', 'type' => 'info', 'time' => '07:30 AM']
            ];
            break;
            
        case 'room_assignments':
            $notifications = [
                ['id' => 1, 'message' => 'Habitaciones asignadas al personal de limpieza', 'type' => 'success', 'time' => '08:00 AM'],
                ['id' => 2, 'message' => 'Nueva asignación - María López', 'type' => 'info', 'time' => '07:45 AM']
            ];
            break;
            
        case 'cleaning_schedule':
            $notifications = [
                ['id' => 1, 'message' => 'Limpieza de habitaciones completada', 'type' => 'success', 'time' => '11:30 AM'],
                ['id' => 2, 'message' => 'Programa de limpieza actualizado', 'type' => 'info', 'time' => '08:15 AM']
            ];
            break;
            
        case 'quality_checks':
            $notifications = [
                ['id' => 1, 'message' => 'Control de calidad aprobado - Hab. 101', 'type' => 'success', 'time' => '10:00 AM'],
                ['id' => 2, 'message' => 'Inspección pendiente - Piso 3', 'type' => 'warning', 'time' => '09:30 AM']
            ];
            break;
            
        case 'supervisor_requests':
            $notifications = [
                ['id' => 1, 'message' => 'Solicitud de suministros aprobada', 'type' => 'success', 'time' => '11:45 AM'],
                ['id' => 2, 'message' => 'Recordatorio: Revisar habitaciones del piso 2', 'type' => 'info', 'time' => '08:30 AM']
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
