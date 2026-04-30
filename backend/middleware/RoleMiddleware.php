<?php
/**
 * Middleware de Roles y Permisos
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/AuthMiddleware.php';

class RoleMiddleware {
    
    /**
     * Permisos por rol
     */
    private static $permissions = [
        'admin' => [
            'reservas' => ['create', 'read', 'update', 'delete'],
            'clientes' => ['create', 'read', 'update', 'delete'],
            'habitaciones' => ['create', 'read', 'update', 'delete', 'change_status'],
            'usuarios' => ['create', 'read', 'update', 'delete'],
            'facturas' => ['create', 'read', 'update', 'delete'],
            'reportes' => ['read', 'create'],
            'configuracion' => ['read', 'update'],
            'auditoria' => ['read']
        ],
        'gerente' => [
            'reservas' => ['create', 'read', 'update', 'delete'],
            'clientes' => ['create', 'read', 'update'],
            'habitaciones' => ['read', 'update', 'change_status'],
            'facturas' => ['create', 'read', 'update'],
            'reportes' => ['read', 'create'],
            'auditoria' => ['read']
        ],
        'recepcion' => [
            'reservas' => ['create', 'read', 'update'],
            'clientes' => ['create', 'read', 'update'],
            'habitaciones' => ['read', 'change_status'],
            'checkin' => ['create', 'read'],
            'checkout' => ['create', 'read']
        ],
        'limpieza' => [
            'habitaciones' => ['read', 'change_status'],
            'limpieza' => ['create', 'read', 'update']
        ],
        'contabilidad' => [
            'facturas' => ['create', 'read', 'update', 'delete'],
            'reportes' => ['read', 'create'],
            'pagos' => ['create', 'read', 'update'],
            'auditoria' => ['read']
        ]
    ];
    
    /**
     * Verificar si el usuario tiene permiso para una acción
     */
    public static function requirePermission($resource, $action) {
        $user = AuthMiddleware::user();
        
        if (!$user) {
            self::forbiddenResponse('No autenticado');
        }
        
        $role = $user['rol'];
        
        if (!isset(self::$permissions[$role])) {
            self::forbiddenResponse('Rol no definido');
        }
        
        $rolePermissions = self::$permissions[$role];
        
        if (!isset($rolePermissions[$resource])) {
            self::forbiddenResponse('Recurso no definido para este rol');
        }
        
        if (!in_array($action, $rolePermissions[$resource])) {
            self::forbiddenResponse('No tienes permisos para esta acción');
        }
        
        return true;
    }
    
    /**
     * Verificar si el usuario tiene alguno de los roles requeridos
     */
    public static function requireRole($roles) {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        if (!AuthMiddleware::hasAnyRole($roles)) {
            self::forbiddenResponse('Rol requerido: ' . implode(', ', $roles));
        }
        
        return true;
    }
    
    /**
     * Verificar si el usuario tiene permiso (sin terminar ejecución)
     */
    public static function hasPermission($resource, $action) {
        $user = AuthMiddleware::user();
        
        if (!$user) {
            return false;
        }
        
        $role = $user['rol'];
        
        if (!isset(self::$permissions[$role])) {
            return false;
        }
        
        $rolePermissions = self::$permissions[$role];
        
        if (!isset($rolePermissions[$resource])) {
            return false;
        }
        
        return in_array($action, $rolePermissions[$resource]);
    }
    
    /**
     * Obtener todos los permisos del usuario actual
     */
    public static function getUserPermissions() {
        $user = AuthMiddleware::user();
        
        if (!$user) {
            return [];
        }
        
        return self::$permissions[$user['rol']] ?? [];
    }
    
    /**
     * Verificar si el usuario es admin
     */
    public static function isAdmin() {
        return AuthMiddleware::hasRole('admin');
    }
    
    /**
     * Verificar si el usuario puede gestionar usuarios
     */
    public static function canManageUsers() {
        return self::hasPermission('usuarios', 'create');
    }
    
    /**
     * Verificar si el usuario puede gestionar reservas
     */
    public static function canManageReservations() {
        return self::hasPermission('reservas', 'create');
    }
    
    /**
     * Verificar si el usuario puede ver reportes
     */
    public static function canViewReports() {
        return self::hasPermission('reportes', 'read');
    }
    
    /**
     * Verificar si el usuario puede cambiar estado de habitaciones
     */
    public static function canChangeRoomStatus() {
        return self::hasPermission('habitaciones', 'change_status');
    }
    
    /**
     * Respuesta de acceso denegado
     */
    private static function forbiddenResponse($message) {
        if (self::isApiRequest()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Acceso denegado',
                'message' => $message
            ]);
            exit;
        } else {
            // Para requests web, mostrar página de error
            http_response_code(403);
            include __DIR__ . '/../../resources/views/errors/403.php';
            exit;
        }
    }
    
    /**
     * Verificar si es una request API
     */
    private static function isApiRequest() {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return strpos($uri, '/api/') === 0;
    }
    
    /**
     * Obtener lista de todos los roles disponibles
     */
    public static function getAvailableRoles() {
        return array_keys(self::$permissions);
    }
    
    /**
     * Obtener permisos de un rol específico
     */
    public static function getRolePermissions($role) {
        return self::$permissions[$role] ?? [];
    }
}
