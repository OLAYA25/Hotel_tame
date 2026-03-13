<?php
/**
 * Router principal API REST - Versión Completa
 */

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Controllers\AuthController;
use App\Controllers\ReservaController;
use App\Controllers\HousekeepingController;
use App\Services\AuthService;
use App\Services\AppLogger;

class ApiRouter {
    private array $routes = [];
    
    public function __construct() {
        $this->loadRoutes();
    }
    
    /**
     * Cargar rutas de la API
     */
    private function loadRoutes(): void {
        $this->routes = [
            // Autenticación
            'POST /api/auth/login' => [
                'controller' => 'AuthController',
                'method' => 'login',
                'permission' => null
            ],
            'POST /api/auth/logout' => [
                'controller' => 'AuthController',
                'method' => 'logout',
                'permission' => null
            ],
            'POST /api/auth/refresh' => [
                'controller' => 'AuthController',
                'method' => 'refresh',
                'permission' => null
            ],
            'GET /api/auth/me' => [
                'controller' => 'AuthController',
                'method' => 'me',
                'permission' => null
            ],
            'POST /api/auth/change-password' => [
                'controller' => 'AuthController',
                'method' => 'changePassword',
                'permission' => null
            ],
            
            // Reservas
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
            
            // Housekeeping
            'GET /api/housekeeping/tasks' => [
                'controller' => 'HousekeepingController',
                'method' => 'getTasks',
                'permission' => ['housekeeping', 'read']
            ],
            'POST /api/housekeeping/tasks' => [
                'controller' => 'HousekeepingController',
                'method' => 'createTask',
                'permission' => ['housekeeping', 'create']
            ],
            'PUT /api/housekeeping/tasks/{id}/status' => [
                'controller' => 'HousekeepingController',
                'method' => 'updateTaskStatus',
                'permission' => ['housekeeping', 'update']
            ],
            'GET /api/housekeeping/statistics' => [
                'controller' => 'HousekeepingController',
                'method' => 'getStatistics',
                'permission' => ['housekeeping', 'read']
            ],
            'POST /api/housekeeping/generate-checkout-tasks' => [
                'controller' => 'HousekeepingController',
                'method' => 'generateCheckoutTasks',
                'permission' => ['housekeeping', 'create']
            ],
            'GET /api/housekeeping/room/{id}/history' => [
                'controller' => 'HousekeepingController',
                'method' => 'getRoomHistory',
                'permission' => ['housekeeping', 'read']
            ],
            'GET /api/housekeeping/staff/pending' => [
                'controller' => 'HousekeepingController',
                'method' => 'getStaffPendingTasks',
                'permission' => ['housekeeping', 'read']
            ],
            
            // Web Booking
            'GET /api/web-booking/search-rooms' => [
                'controller' => 'WebBookingController',
                'method' => 'searchRooms',
                'permission' => null // Public endpoint
            ],
            'GET /api/web-booking/room/{id}' => [
                'controller' => 'WebBookingController',
                'method' => 'getRoomDetails',
                'permission' => null // Public endpoint
            ],
            'POST /api/web-booking/create' => [
                'controller' => 'WebBookingController',
                'method' => 'createBooking',
                'permission' => null // Public endpoint
            ],
            'POST /api/web-booking/payment' => [
                'controller' => 'WebBookingController',
                'method' => 'processPayment',
                'permission' => null // Public endpoint
            ],
            
            // CRM
            'GET /api/crm/guests/{id}' => [
                'controller' => 'CRMController',
                'method' => 'getGuestProfile',
                'permission' => ['crm', 'read']
            ],
            'PUT /api/crm/guests/{id}/preferences' => [
                'controller' => 'CRMController',
                'method' => 'updateGuestPreferences',
                'permission' => ['crm', 'update']
            ],
            'POST /api/crm/guests/{id}/communications' => [
                'controller' => 'CRMController',
                'method' => 'addCommunication',
                'permission' => ['crm', 'create']
            ],
            'POST /api/crm/reviews' => [
                'controller' => 'CRMController',
                'method' => 'addGuestReview',
                'permission' => null // Public endpoint
            ],
            'POST /api/crm/reviews/{id}/respond' => [
                'controller' => 'CRMController',
                'method' => 'respondToReview',
                'permission' => ['crm', 'update']
            ],
            'GET /api/crm/statistics' => [
                'controller' => 'CRMController',
                'method' => 'getGuestStatistics',
                'permission' => ['crm', 'read']
            ],
            'GET /api/crm/follow-up' => [
                'controller' => 'CRMController',
                'method' => 'getGuestsWithFollowUp',
                'permission' => ['crm', 'read']
            ],
            'GET /api/crm/guests/{id}/recommendations' => [
                'controller' => 'CRMController',
                'method' => 'getGuestRoomRecommendations',
                'permission' => ['crm', 'read']
            ],
            
            // Dynamic Pricing
            'GET /api/pricing/calculate/{room_id}' => [
                'controller' => 'PricingController',
                'method' => 'calculatePrice',
                'permission' => ['pricing', 'read']
            ],
            'GET /api/pricing/rules' => [
                'controller' => 'PricingController',
                'method' => 'getPricingRules',
                'permission' => ['pricing', 'read']
            ],
            'POST /api/pricing/rules' => [
                'controller' => 'PricingController',
                'method' => 'createPricingRule',
                'permission' => ['pricing', 'create']
            ],
            'GET /api/pricing/analytics' => [
                'controller' => 'PricingController',
                'method' => 'getPricingAnalytics',
                'permission' => ['pricing', 'read']
            ],
            'POST /api/pricing/competitor' => [
                'controller' => 'PricingController',
                'method' => 'updateCompetitorPricing',
                'permission' => ['pricing', 'update']
            ],
            
            // Analytics Dashboard
            'GET /api/analytics/dashboard' => [
                'controller' => 'AnalyticsController',
                'method' => 'getDashboard',
                'permission' => ['analytics', 'read']
            ],
            'GET /api/analytics/realtime' => [
                'controller' => 'AnalyticsController',
                'method' => 'getRealTimeMetrics',
                'permission' => ['analytics', 'read']
            ],
            'GET /api/analytics/occupancy' => [
                'controller' => 'AnalyticsController',
                'method' => 'getOccupancyAnalytics',
                'permission' => ['analytics', 'read']
            ],
            'GET /api/analytics/revenue' => [
                'controller' => 'AnalyticsController',
                'method' => 'getRevenueAnalytics',
                'permission' => ['analytics', 'read']
            ],
            'GET /api/analytics/guests' => [
                'controller' => 'AnalyticsController',
                'method' => 'getGuestAnalytics',
                'permission' => ['analytics', 'read']
            ],
            
            // Notifications
            'GET /api/notifications' => [
                'controller' => 'NotificationController',
                'method' => 'getUserNotifications',
                'permission' => ['notifications', 'read']
            ],
            'POST /api/notifications' => [
                'controller' => 'NotificationController',
                'method' => 'createNotification',
                'permission' => ['notifications', 'create']
            ],
            'PUT /api/notifications/{id}/read' => [
                'controller' => 'NotificationController',
                'method' => 'markAsRead',
                'permission' => ['notifications', 'update']
            ],
            'PUT /api/notifications/read-all' => [
                'controller' => 'NotificationController',
                'method' => 'markAllAsRead',
                'permission' => ['notifications', 'update']
            ],
            'GET /api/notifications/unread-count' => [
                'controller' => 'NotificationController',
                'method' => 'getUnreadCount',
                'permission' => ['notifications', 'read']
            ],
            'GET /api/notifications/statistics' => [
                'controller' => 'NotificationController',
                'method' => 'getNotificationStatistics',
                'permission' => ['notifications', 'read']
            ],
            'POST /api/notifications/generate-daily' => [
                'controller' => 'NotificationController',
                'method' => 'generateDailyNotifications',
                'permission' => ['notifications', 'create']
            ],
            
            // Reports
            'GET /api/reports/occupancy' => [
                'controller' => 'ReportController',
                'method' => 'generateOccupancyReport',
                'permission' => ['reports', 'read']
            ],
            'GET /api/reports/revenue' => [
                'controller' => 'ReportController',
                'method' => 'generateRevenueReport',
                'permission' => ['reports', 'read']
            ],
            'GET /api/reports/guests' => [
                'controller' => 'ReportController',
                'method' => 'generateGuestReport',
                'permission' => ['reports', 'read']
            ],
            'GET /api/reports/services' => [
                'controller' => 'ReportController',
                'method' => 'generateServiceReport',
                'permission' => ['reports', 'read']
            ],
            'GET /api/reports/housekeeping' => [
                'controller' => 'ReportController',
                'method' => 'generateHousekeepingReport',
                'permission' => ['reports', 'read']
            ],
            'GET /api/reports/available' => [
                'controller' => 'ReportController',
                'method' => 'getAvailableReports',
                'permission' => ['reports', 'read']
            ],
            'GET /api/reports/generated' => [
                'controller' => 'ReportController',
                'method' => 'getGeneratedReports',
                'permission' => ['reports', 'read']
            ],
            
            // Queue Management
            'POST /api/queue/push' => [
                'controller' => 'QueueController',
                'method' => 'pushJob',
                'permission' => ['queue', 'create']
            ],
            'POST /api/queue/process' => [
                'controller' => 'QueueController',
                'method' => 'processJobs',
                'permission' => ['queue', 'update']
            ],
            'GET /api/queue/stats' => [
                'controller' => 'QueueController',
                'method' => 'getQueueStats',
                'permission' => ['queue', 'read']
            ],
            'POST /api/queue/clear' => [
                'controller' => 'QueueController',
                'method' => 'clearQueue',
                'permission' => ['queue', 'delete']
            ],
            'POST /api/queue/retry-failed' => [
                'controller' => 'QueueController',
                'method' => 'retryFailedJobs',
                'permission' => ['queue', 'update']
            ],
            
            // Cache Management
            'GET /api/cache/stats' => [
                'controller' => 'CacheController',
                'method' => 'getCacheStats',
                'permission' => ['cache', 'read']
            ],
            'DELETE /api/cache/clear' => [
                'controller' => 'CacheController',
                'method' => 'clearCache',
                'permission' => ['cache', 'delete']
            ],
            'DELETE /api/cache/pattern/{pattern}' => [
                'controller' => 'CacheController',
                'method' => 'invalidatePattern',
                'permission' => ['cache', 'delete']
            ],
            'GET /api/cache/refresh/{key}' => [
                'controller' => 'CacheController',
                'method' => 'refreshCache',
                'permission' => ['cache', 'update']
            ],
            
            // Hotel Configuration (Multi-hotel)
            'GET /api/hotel/config' => [
                'controller' => 'HotelController',
                'method' => 'getHotelConfig',
                'permission' => ['hotel', 'read']
            ],
            'PUT /api/hotel/config' => [
                'controller' => 'HotelController',
                'method' => 'updateHotelConfig',
                'permission' => ['hotel', 'update']
            ],
            'GET /api/hotel/settings' => [
                'controller' => 'HotelController',
                'method' => 'getHotelSettings',
                'permission' => ['hotel', 'read']
            ],
            'PUT /api/hotel/settings' => [
                'controller' => 'HotelController',
                'method' => 'updateHotelSettings',
                'permission' => ['hotel', 'update']
            ],
            'GET /api/hotel/facilities' => [
                'controller' => 'HotelController',
                'method' => 'getFacilities',
                'permission' => ['hotel', 'read']
            ],
            'POST /api/hotel/facilities' => [
                'controller' => 'HotelController',
                'method' => 'createFacility',
                'permission' => ['hotel', 'create']
            ],
            
            // System Health
            'GET /api/health' => [
                'controller' => 'HealthController',
                'method' => 'healthCheck',
                'permission' => null // Public endpoint
            ],
            'GET /api/health/database' => [
                'controller' => 'HealthController',
                'method' => 'databaseHealth',
                'permission' => ['system', 'read']
            ],
            'GET /api/health/cache' => [
                'controller' => 'HealthController',
                'method' => 'cacheHealth',
                'permission' => ['system', 'read']
            ],
            'GET /api/health/queue' => [
                'controller' => 'HealthController',
                'method' => 'queueHealth',
                'permission' => ['system', 'read']
            ]
        ];
    }
    
    /**
     * Ejecutar router
     */
    public function handle(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $this->getRequestUri();
        
        $startTime = microtime(true);
        
        try {
            // Buscar ruta exacta
            if (isset($this->routes["$method $uri"])) {
                $this->executeRoute($this->routes["$method $uri"]);
                $this->logApiRequest($method, $uri, 200, microtime(true) - $startTime);
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
                        $this->logApiRequest($method, $uri, 200, microtime(true) - $startTime);
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
            
            $this->logApiRequest($method, $uri, 404, microtime(true) - $startTime);
            
        } catch (Exception $e) {
            $this->logApiRequest($method, $uri, 500, microtime(true) - $startTime, $e->getMessage());
            
            if (Config::isDebug()) {
                $this->sendResponse([
                    'success' => false,
                    'error' => 'Error interno del servidor',
                    'message' => $e->getMessage(),
                    'trace' => $e->getTrace()
                ], 500);
            } else {
                $this->sendResponse([
                    'success' => false,
                    'error' => 'Error interno del servidor',
                    'message' => 'Error procesando la solicitud'
                ], 500);
            }
        }
    }
    
    /**
     * Ejecutar ruta específica
     */
    private function executeRoute(array $routeInfo): void {
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
        $controllerFile = __DIR__ . "/Controllers/{$routeInfo['controller']}.php";
        
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
    private function getRequestUri(): string {
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
    private function sendResponse(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Registrar API request en logs
     */
    private function logApiRequest(string $method, string $endpoint, int $statusCode, float $responseTime, string $error = null): void {
        AppLogger::api($method, $endpoint, [
            'status_code' => $statusCode,
            'response_time' => $responseTime,
            'error' => $error
        ], $statusCode);
    }
}

// Ejecutar router
$router = new ApiRouter();
$router->handle();
