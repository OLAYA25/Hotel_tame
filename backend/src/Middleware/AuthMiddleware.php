<?php

namespace App\Middleware;

use App\Services\AuthService;
use Config\Config;
use Exception;

class AuthMiddleware {
    private static ?array $user = null;
    
    /**
     * Handle authentication
     */
    public static function handle(): ?array {
        // Check if user already authenticated in this request
        if (self::$user !== null) {
            return self::$user;
        }
        
        // Get token from header
        $token = self::getBearerToken();
        
        if (!$token) {
            // Try to get from session (for web requests)
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (isset($_SESSION['user'])) {
                self::$user = $_SESSION['user'];
                return self::$user;
            }
            
            return null;
        }
        
        try {
            $authService = new AuthService();
            $payload = $authService->validateToken($token);
            self::$user = (array) $payload->user;
            
            return self::$user;
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Require authentication
     */
    public static function require(): array {
        $user = self::handle();
        
        if ($user === null) {
            self::unauthorizedResponse();
        }
        
        return $user;
    }
    
    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated(): bool {
        return self::handle() !== null;
    }
    
    /**
     * Get current user
     */
    public static function user(): ?array {
        return self::$user ?? self::handle();
    }
    
    /**
     * Get user ID
     */
    public static function userId(): ?int {
        $user = self::user();
        return $user ? $user['id'] : null;
    }
    
    /**
     * Get user role
     */
    public static function userRole(): ?string {
        $user = self::user();
        return $user ? $user['rol'] : null;
    }
    
    /**
     * Check if user has specific role
     */
    public static function hasRole(string $role): bool {
        return self::userRole() === $role;
    }
    
    /**
     * Check if user has any of the specified roles
     */
    public static function hasAnyRole(array $roles): bool {
        return in_array(self::userRole(), $roles);
    }
    
    /**
     * Get Bearer token from request
     */
    private static function getBearerToken(): ?string {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        
        if ($authHeader && strpos($authHeader, 'Bearer ') === 0) {
            return substr($authHeader, 7);
        }
        
        return null;
    }
    
    /**
     * Send unauthorized response
     */
    private static function unauthorizedResponse(): void {
        if (self::isApiRequest()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'Authentication required'
            ]);
            exit;
        } else {
            // For web requests, redirect to login
            header('Location: /login');
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
     * Clear user cache (for testing)
     */
    public static function clear(): void {
        self::$user = null;
    }
}
