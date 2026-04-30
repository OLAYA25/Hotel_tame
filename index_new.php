<?php
/**
 * Router principal mejorado con MVC
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/app/Models/Model.php';
require_once __DIR__ . '/app/Helpers/SecurityHelper.php';
require_once __DIR__ . '/app/Helpers/AuditHelper.php';
require_once __DIR__ . '/app/Controllers/Controller.php';

// Cargar modelos
require_once __DIR__ . '/app/Models/User.php';
require_once __DIR__ . '/app/Models/Client.php';
require_once __DIR__ . '/app/Models/Room.php';
require_once __DIR__ . '/app/Models/Reservation.php';

// Cargar controladores
require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/ReservationController.php';

// Iniciar sesión
session_start();

// Definir constantes para el router
define('HOTEL_TAME_ROOT', __DIR__);
define('BACKEND_ROOT', HOTEL_TAME_ROOT . '/backend');
define('ASSETS_URL', '/Hotel_tame/assets');

// Mapeo de rutas MVC
$routeMap = [
    // Rutas públicas
    '/' => 'HomeController@index',
    '/home' => 'HomeController@index',
    '/login' => 'AuthController@login',
    '/logout' => 'AuthController@logout',
    
    // Rutas de dashboard
    '/dashboard' => 'DashboardController@index',
    
    // Rutas de reservas
    '/reservas' => 'ReservationController@index',
    '/api/reservas' => 'ReservationController@index',
    '/api/reservas/create' => 'ReservationController@store',
    '/api/reservas/show' => 'ReservationController@show',
    '/api/reservas/update' => 'ReservationController@update',
    '/api/reservas/cancel' => 'ReservationController@cancel',
    '/api/reservas/check-availability' => 'ReservationController@checkAvailability',
    '/api/reservas/today' => 'ReservationController@today',
    '/api/reservas/statistics' => 'ReservationController@statistics',
    
    // Rutas de clientes
    '/clientes' => 'ClientController@index',
    '/api/clientes' => 'ClientController@index',
    '/api/clientes/search' => 'ClientController@search',
    '/api/clientes/create' => 'ClientController@store',
    '/api/clientes/update' => 'ClientController@update',
    
    // Rutas de habitaciones
    '/habitaciones' => 'RoomController@index',
    '/api/habitaciones' => 'RoomController@index',
    '/api/habitaciones/available' => 'RoomController@getAvailable',
    '/api/habitaciones/occupancy' => 'RoomController@getOccupancy',
    '/api/habitaciones/change-status' => 'RoomController@changeStatus',
    
    // Rutas de autenticación
    '/api/auth/login' => 'AuthController@login',
    '/api/auth/logout' => 'AuthController@logout',
    '/api/auth/check' => 'AuthController@check',
    '/api/auth/profile' => 'AuthController@profile',
    '/api/auth/change-password' => 'AuthController@changePassword',
    
    // Rutas legacy (compatibilidad)
    '/usuarios' => 'frontend/views/private/usuarios.php',
    '/contabilidad' => 'frontend/views/private/contabilidad.php',
    '/reportes' => 'frontend/views/private/reportes.php',
    '/settings' => 'frontend/views/private/settings.php',
];

// Función para obtener la ruta actual
function getRequestPath() {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $basePath = '/Hotel_tame';
    $path = str_replace($basePath, '', $requestUri);
    $path = parse_url($path, PHP_URL_PATH);
    return rtrim($path, '/') ?: '/';
}

// Función para ejecutar controlador
function executeController($controllerAction, $params = []) {
    list($controllerName, $method) = explode('@', $controllerAction);
    
    $controllerClass = $controllerName;
    
    // Verificar si existe el controlador
    if (!class_exists($controllerClass)) {
        http_response_code(404);
        echo json_encode(['error' => 'Controller not found']);
        exit;
    }
    
    $controller = new $controllerClass();
    
    // Verificar si existe el método
    if (!method_exists($controller, $method)) {
        http_response_code(404);
        echo json_encode(['error' => 'Method not found']);
        exit;
    }
    
    // Ejecutar el método
    return call_user_func_array([$controller, $method], $params);
}

// Obtener ruta solicitada
$path = getRequestPath();

// Manejar rutas API
if (strpos($path, '/api/') === 0) {
    if (isset($routeMap[$path])) {
        executeController($routeMap[$path]);
        exit;
    }
    
    // Intentar coincidencia de patrones
    foreach ($routeMap as $route => $controllerAction) {
        if (strpos($route, '/api/') === 0 && strpos($route, ':') !== false) {
            $pattern = str_replace(':id', '(\d+)', $route);
            $pattern = str_replace('/', '\/', $pattern);
            
            if (preg_match("/^$pattern$/", $path, $matches)) {
                array_shift($matches); // Quitar la coincidencia completa
                executeController($controllerAction, $matches);
                exit;
            }
        }
    }
    
    http_response_code(404);
    echo json_encode(['error' => 'API endpoint not found']);
    exit;
}

// Manejar rutas de vistas
if (isset($routeMap[$path])) {
    $target = $routeMap[$path];
    
    // Si es una acción de controlador
    if (strpos($target, '@') !== false) {
        executeController($target);
        exit;
    }
    
    // Si es un archivo de vista
    if (file_exists(HOTEL_TAME_ROOT . '/' . $target)) {
        // Incluir configuración para vistas
        define('BACKEND_ROOT', HOTEL_TAME_ROOT . '/backend');
        define('ASSETS_URL', '/Hotel_tame/assets');
        
        require_once HOTEL_TAME_ROOT . '/' . $target;
        exit;
    }
}

// Si no encuentra, verificar si existe archivo directo
$directFile = HOTEL_TAME_ROOT . '/' . ltrim($path, '/');
if (file_exists($directFile) && is_file($directFile)) {
    require_once $directFile;
    exit;
}

// 404
http_response_code(404);
echo json_encode(['error' => 'Route not found']);
