<?php
/**
 * Middleware de Autenticación
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Helpers/SecurityHelper.php';

class AuthMiddleware {
    
    /**
     * Verificar si el usuario está autenticado
     */
    public static function handle() {
        if (!SecurityHelper::validateSession()) {
            self::unauthorizedResponse();
        }
        
        return $_SESSION['usuario'];
    }
    
    /**
     * Verificar autenticación para API
     */
    public static function api() {
        // Para API, verificar token o sesión
        $token = self::getBearerToken();
        
        if ($token) {
            return self::validateApiToken($token);
        } else {
            return self::handle();
        }
    }
    
    /**
     * Obtener token Bearer del header
     */
    private static function getBearerToken() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        
        if ($authHeader && strpos($authHeader, 'Bearer ') === 0) {
            return substr($authHeader, 7);
        }
        
        return null;
    }
    
    /**
     * Validar token de API
     */
    private static function validateApiToken($token) {
        // Implementar validación de token JWT o similar
        // Por ahora, usar sesión
        return self::handle();
    }
    
    /**
     * Respuesta de no autorizado
     */
    private static function unauthorizedResponse() {
        if (self::isApiRequest()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'No autorizado',
                'message' => 'Debe iniciar sesión para acceder a este recurso'
            ]);
            exit;
        } else {
            // Para requests web, redirigir al login
            header('Location: /Hotel_tame/login');
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
     * Obtener usuario actual
     */
    public static function user() {
        return $_SESSION['usuario'] ?? null;
    }
    
    /**
     * Verificar si usuario tiene rol específico
     */
    public static function hasRole($role) {
        $user = self::user();
        return $user && $user['rol'] === $role;
    }
    
    /**
     * Verificar si usuario tiene alguno de los roles
     */
    public static function hasAnyRole($roles) {
        $user = self::user();
        return $user && in_array($user['rol'], $roles);
    }
    
    /**
     * Obtener ID del usuario actual
     */
    public static function userId() {
        $user = self::user();
        return $user ? $user['id'] : null;
    }
}
