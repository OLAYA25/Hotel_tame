<?php
header("Access-Control-Allow-Origin: *");;
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Endpoint de prueba sin autenticación para depuración
$type = $_GET['type'] ?? '';
$user_id = $_GET['user_id'] ?? 1;

if (empty($type)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo de notificación no especificado']);
    exit;
}

// Logging para depuración
error_log("Notifications TEST API - Request: " . $_SERVER['REQUEST_URI']);
error_log("Notifications TEST API - Type: " . $type);
error_log("Notifications TEST API - User ID: " . $user_id);

// Obtener notificaciones según el tipo
$notifications = getDashboardNotifications($type, $user_id, null);

http_response_code(200);
echo json_encode([
    'success' => true,
    'notifications' => $notifications,
    'count' => count($notifications),
    'debug' => 'Test endpoint - no auth required'
]);

// Función copiada del archivo original
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
