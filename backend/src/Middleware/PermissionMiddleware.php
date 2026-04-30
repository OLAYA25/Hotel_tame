<?php

namespace App\Middleware;

use Database\Database;
use Exception;

class PermissionMiddleware {
    private static array $permissions = [];
    
    /**
     * Load user permissions
     */
    private static function loadPermissions(int $userId): array {
        if (isset(self::$permissions[$userId])) {
            return self::$permissions[$userId];
        }
        
        $query = "SELECT p.codigo, p.descripcion, p.modulo
                  FROM permissions p
                  JOIN role_permissions rp ON p.id = rp.permission_id
                  JOIN roles r ON rp.role_id = r.id
                  JOIN usuarios u ON u.rol_id = r.id
                  WHERE u.id = :user_id AND u.deleted_at IS NULL AND u.activo = 1";
        
        $permissions = Database::fetchAll($query, ['user_id' => $userId]);
        
        // Group by module
        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            $groupedPermissions[$permission['modulo']][] = $permission['codigo'];
        }
        
        self::$permissions[$userId] = $groupedPermissions;
        
        return $groupedPermissions;
    }
    
    /**
     * Require specific permission
     */
    public static function require(string $permission, ?string $module = null): void {
        $user = AuthMiddleware::user();
        
        if (!$user) {
            self::forbiddenResponse('Authentication required');
        }
        
        $userPermissions = self::loadPermissions($user['id']);
        
        if ($module && !isset($userPermissions[$module])) {
            self::forbiddenResponse("Module '{$module}' not accessible");
        }
        
        if ($module && !in_array($permission, $userPermissions[$module])) {
            self::forbiddenResponse("Permission '{$permission}' required");
        }
        
        // Check global permissions if no module specified
        if (!$module) {
            $hasPermission = false;
            foreach ($userPermissions as $modulePermissions) {
                if (in_array($permission, $modulePermissions)) {
                    $hasPermission = true;
                    break;
                }
            }
            
            if (!$hasPermission) {
                self::forbiddenResponse("Permission '{$permission}' required");
            }
        }
    }
    
    /**
     * Check if user has permission
     */
    public static function has(string $permission, ?string $module = null): bool {
        $user = AuthMiddleware::user();
        
        if (!$user) {
            return false;
        }
        
        $userPermissions = self::loadPermissions($user['id']);
        
        if ($module && !isset($userPermissions[$module])) {
            return false;
        }
        
        if ($module) {
            return in_array($permission, $userPermissions[$module]);
        }
        
        // Check all modules
        foreach ($userPermissions as $modulePermissions) {
            if (in_array($permission, $modulePermissions)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if user has any of the specified permissions
     */
    public static function hasAny(array $permissions, ?string $module = null): bool {
        foreach ($permissions as $permission) {
            if (self::has($permission, $module)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if user has all specified permissions
     */
    public static function hasAll(array $permissions, ?string $module = null): bool {
        foreach ($permissions as $permission) {
            if (!self::has($permission, $module)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get user permissions
     */
    public static function getUserPermissions(?int $userId = null): array {
        if ($userId === null) {
            $user = AuthMiddleware::user();
            if (!$user) {
                return [];
            }
            $userId = $user['id'];
        }
        
        return self::loadPermissions($userId);
    }
    
    /**
     * Check if user is admin (has all permissions)
     */
    public static function isAdmin(): bool {
        $user = AuthMiddleware::user();
        return $user && $user['rol'] === 'admin';
    }
    
    /**
     * Check if user can manage reservations
     */
    public static function canManageReservations(): bool {
        return self::hasAny(['crear_reserva', 'editar_reserva', 'cancelar_reserva'], 'reservas');
    }
    
    /**
     * Check if user can manage clients
     */
    public static function canManageClients(): bool {
        return self::hasAny(['crear_cliente', 'editar_cliente', 'eliminar_cliente'], 'clientes');
    }
    
    /**
     * Check if user can manage rooms
     */
    public static function canManageRooms(): bool {
        return self::hasAny(['crear_habitacion', 'editar_habitacion', 'cambiar_estado_habitacion'], 'habitaciones');
    }
    
    /**
     * Check if user can view reports
     */
    public static function canViewReports(): bool {
        return self::has('ver_reportes', 'reportes');
    }
    
    /**
     * Check if user can manage users
     */
    public static function canManageUsers(): bool {
        return self::hasAny(['crear_usuario', 'editar_usuario', 'eliminar_usuario'], 'usuarios');
    }
    
    /**
     * Check if user can manage billing
     */
    public static function canManageBilling(): bool {
        return self::hasAny(['crear_factura', 'editar_factura', 'ver_facturas'], 'facturacion');
    }
    
    /**
     * Send forbidden response
     */
    private static function forbiddenResponse(string $message): void {
        if (self::isApiRequest()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Forbidden',
                'message' => $message
            ]);
            exit;
        } else {
            // For web requests, show error page
            http_response_code(403);
            include __DIR__ . '/../../resources/views/errors/403.php';
            exit;
        }
    }
    
    /**
     * Check if this is an API request
     */
    private static function isApiRequest(): bool {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return strpos($uri, '/api/') === 0;
    }
    
    /**
     * Clear permissions cache (for testing)
     */
    public static function clear(): void {
        self::$permissions = [];
    }
}
