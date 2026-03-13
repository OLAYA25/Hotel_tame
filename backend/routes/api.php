<?php
/**
 * Router principal API REST
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/LoggingMiddleware.php';

// Iniciar sesión
session_start();

// Configurar headers CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/**
 * Router API RESTful
 */
class ApiRouter {
    private $routes = [];
    
    public function __construct() {
        $this->loadRoutes();
    }
    
    /**
     * Cargar rutas de la API
     */
    private function loadRoutes() {
        $this->routes = [
            // Rutas de reservas
            'GET /api/reservas' => [
                'controller' => 'ReservaController',
                'method' => 'index',
                'permission' => ['reservas', 'read']
            ],
            'GET /api/reservas/{id}' => [
                'controller' => 'ReservaController',
                'method' => 'show',
                'permission' => ['reservas', 'read']
            ],
            'POST /api/reservas' => [
                'controller' => 'ReservaController',
                'method' => 'store',
                'permission' => ['reservas', 'create']
            ],
            'PUT /api/reservas/{id}' => [
                'controller' => 'ReservaController',
                'method' => 'update',
                'permission' => ['reservas', 'update']
            ],
            'DELETE /api/reservas/{id}' => [
                'controller' => 'ReservaController',
                'method' => 'destroy',
                'permission' => ['reservas', 'delete']
            ],
            'POST /api/reservas/{id}/confirm' => [
                'controller' => 'ReservaController',
                'method' => 'confirm',
                'permission' => ['reservas', 'update']
            ],
            'POST /api/reservas/{id}/checkin' => [
                'controller' => 'ReservaController',
                'method' => 'checkIn',
                'permission' => ['reservas', 'update']
            ],
            'POST /api/reservas/{id}/checkout' => [
                'controller' => 'ReservaController',
                'method' => 'checkOut',
                'permission' => ['reservas', 'update']
            ],
            'GET /api/reservas/availability' => [
                'controller' => 'ReservaController',
                'method' => 'availability',
                'permission' => ['reservas', 'read']
            ],
            'GET /api/reservas/calendar' => [
                'controller' => 'ReservaController',
                'method' => 'calendar',
                'permission' => ['reservas', 'read']
            ],
            'GET /api/reservas/statistics' => [
                'controller' => 'ReservaController',
                'method' => 'statistics',
                'permission' => ['reservas', 'read']
            ],
            
            // Rutas de autenticación
            'POST /api/auth/login' => [
                'controller' => 'AuthController',
                'method' => 'login',
                'permission' => null // Pública
            ],
            'POST /api/auth/logout' => [
                'controller' => 'AuthController',
                'method' => 'logout',
                'permission' => null // Pública
            ],
            'GET /api/auth/check' => [
                'controller' => 'AuthController',
                'method' => 'check',
                'permission' => null // Pública
            ],
            'GET /api/auth/profile' => [
                'controller' => 'AuthController',
                'method' => 'profile',
                'permission' => null // Requiere autenticación pero no permiso específico
            ],
            'POST /api/auth/change-password' => [
                'controller' => 'AuthController',
                'method' => 'changePassword',
                'permission' => null // Requiere autenticación pero no permiso específico
            ],
            
            // Rutas de habitaciones
            'GET /api/habitaciones' => [
                'controller' => 'HabitacionController',
                'method' => 'index',
                'permission' => ['habitaciones', 'read']
            ],
            'GET /api/habitaciones/{id}' => [
                'controller' => 'HabitacionController',
                'method' => 'show',
                'permission' => ['habitaciones', 'read']
            ],
            'POST /api/habitaciones' => [
                'controller' => 'HabitacionController',
                'method' => 'store',
                'permission' => ['habitaciones', 'create']
            ],
            'PUT /api/habitaciones/{id}' => [
                'controller' => 'HabitacionController',
                'method' => 'update',
                'permission' => ['habitaciones', 'update']
            ],
            'DELETE /api/habitaciones/{id}' => [
                'controller' => 'HabitacionController',
                'method' => 'destroy',
                'permission' => ['habitaciones', 'delete']
            ],
            'GET /api/habitaciones/available' => [
                'controller' => 'HabitacionController',
                'method' => 'available',
                'permission' => ['habitaciones', 'read']
            ],
            'POST /api/habitaciones/{id}/change-status' => [
                'controller' => 'HabitacionController',
                'method' => 'changeStatus',
                'permission' => ['habitaciones', 'change_status']
            ],
            'GET /api/habitaciones/occupancy' => [
                'controller' => 'HabitacionController',
                'method' => 'occupancy',
                'permission' => ['habitaciones', 'read']
            ]
        ];
    }
    
    /**
     * Ejecutar router
     */
    public function handle() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getRequestUri();
        
        try {
            // Buscar ruta exacta
            if (isset($this->routes["$method $uri"])) {
                $this->executeRoute($this->routes["$method $uri"]);
                return;
            }
            
            // Buscar ruta con parámetros
            foreach ($this->routes as $route => $routeInfo) {
                if (strpos($route, $method) === 0) {
                    $routePattern = str_replace('{id}', '(\d+)', $route);
                    $pattern = str_replace('/', '\/', $routePattern);
                    
                    if (preg_match("/^$pattern$/", $uri, $matches)) {
                        $routeInfo['params'] = $matches;
                        $this->executeRoute($routeInfo);
                        return;
                    }
                }
            }
            
            // Si no encuentra ruta
            $this->sendResponse([
                'success' => false,
                'error' => 'Endpoint no encontrado',
                'message' => "La ruta $method $uri no existe"
            ], 404);
            
        } catch (Exception $e) {
            error_log("Router Error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'error' => 'Error interno del servidor',
                'message' => App::isDevelopment() ? $e->getMessage() : 'Error procesando la solicitud'
            ], 500);
        }
    }
    
    /**
     * Ejecutar ruta específica
     */
    private function executeRoute($routeInfo) {
        // Verificar permisos si es necesario
        if ($routeInfo['permission'] && !AuthMiddleware::user()) {
            $this->sendResponse([
                'success' => false,
                'error' => 'No autorizado',
                'message' => 'Debe iniciar sesión para acceder a este recurso'
            ], 401);
            return;
        }
        
        // Cargar controlador
        $controllerFile = __DIR__ . "/controllers/{$routeInfo['controller']}.php";
        
        if (!file_exists($controllerFile)) {
            $this->sendResponse([
                'success' => false,
                'error' => 'Controlador no encontrado',
                'message' => "El controlador {$routeInfo['controller']} no existe"
            ], 500);
            return;
        }
        
        require_once $controllerFile;
        $controllerClass = $routeInfo['controller'];
        
        if (!class_exists($controllerClass)) {
            $this->sendResponse([
                'success' => false,
                'error' => 'Clase de controlador no encontrada',
                'message' => "La clase $controllerClass no existe"
            ], 500);
            return;
        }
        
        $controller = new $controllerClass();
        
        // Verificar si existe el método
        if (!method_exists($controller, $routeInfo['method'])) {
            $this->sendResponse([
                'success' => false,
                'error' => 'Método no encontrado',
                'message' => "El método {$routeInfo['method']} no existe en el controlador"
            ], 500);
            return;
        }
        
        // Ejecutar método con parámetros
        $params = $routeInfo['params'] ?? [];
        call_user_func_array([$controller, $routeInfo['method']], $params);
    }
    
    /**
     * Obtener URI de la solicitud
     */
    private function getRequestUri() {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $basePath = '/Hotel_tame';
        
        // Remover base path y query string
        $uri = str_replace($basePath, '', $uri);
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';
        
        return $uri;
    }
    
    /**
     * Enviar respuesta JSON
     */
    private function sendResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

// Ejecutar router
$router = new ApiRouter();
$router->handle();
