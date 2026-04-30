<?php

namespace App\Controllers;

use App\Services\AuthService;
use Config\Config;
use Exception;

class AuthController {
    private AuthService $authService;
    
    public function __construct() {
        $this->authService = new AuthService();
    }
    
    /**
     * Login user
     */
    public function login(): void {
        header('Content-Type: application/json');
        
        try {
            $input = $this->getJsonInput();
            
            // Validate input
            $this->validateLoginInput($input);
            
            // Authenticate
            $result = $this->authService->login($input['email'], $input['password']);
            
            // Set CSRF token
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Login successful',
                'data' => $result,
                'csrf_token' => $_SESSION['csrf_token']
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Authentication failed',
                'message' => $e->getMessage()
            ], 401);
        }
    }
    
    /**
     * Logout user
     */
    public function logout(): void {
        header('Content-Type: application/json');
        
        try {
            $token = $this->getBearerToken();
            if ($token) {
                $this->authService->logout($token);
            }
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Logout successful'
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Logout failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Refresh token
     */
    public function refresh(): void {
        header('Content-Type: application/json');
        
        try {
            $input = $this->getJsonInput();
            
            if (!isset($input['refresh_token'])) {
                throw new Exception('Refresh token is required');
            }
            
            $result = $this->authService->refreshToken($input['refresh_token']);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => $result
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Token refresh failed',
                'message' => $e->getMessage()
            ], 401);
        }
    }
    
    /**
     * Get current user
     */
    public function me(): void {
        header('Content-Type: application/json');
        
        try {
            $token = $this->getBearerToken();
            if (!$token) {
                throw new Exception('Token is required');
            }
            
            $payload = $this->authService->validateToken($token);
            
            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'user' => $payload->user,
                    'expires_at' => date('Y-m-d H:i:s', $payload->exp)
                ]
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Authentication failed',
                'message' => $e->getMessage()
            ], 401);
        }
    }
    
    /**
     * Change password
     */
    public function changePassword(): void {
        header('Content-Type: application/json');
        
        try {
            $input = $this->getJsonInput();
            
            // Validate input
            $this->validatePasswordInput($input);
            
            // Get current user
            $payload = $this->authService->validateToken($this->getBearerToken());
            $user = $this->getUserById($payload->sub);
            
            if (!$user) {
                throw new Exception('User not found');
            }
            
            // Verify current password
            if (!password_verify($input['current_password'], $user['password'])) {
                throw new Exception('Current password is incorrect');
            }
            
            // Update password
            $newPassword = password_hash($input['new_password'], PASSWORD_DEFAULT);
            $query = "UPDATE usuarios SET password = :password, updated_at = NOW() WHERE id = :id";
            Database::execute($query, [
                'password' => $newPassword,
                'id' => $user['id']
            ]);
            
            // Invalidate all tokens for this user
            $this->invalidateUserTokens($user['id']);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Password change failed',
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Validate login input
     */
    private function validateLoginInput(array $input): void {
        $errors = [];
        
        if (empty($input['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        if (empty($input['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($input['password']) < 6) {
            $errors['password'] = 'Password must be at least 6 characters';
        }
        
        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }
    
    /**
     * Validate password input
     */
    private function validatePasswordInput(array $input): void {
        $errors = [];
        
        if (empty($input['current_password'])) {
            $errors['current_password'] = 'Current password is required';
        }
        
        if (empty($input['new_password'])) {
            $errors['new_password'] = 'New password is required';
        } elseif (strlen($input['new_password']) < 8) {
            $errors['new_password'] = 'New password must be at least 8 characters';
        }
        
        if (empty($input['confirm_password'])) {
            $errors['confirm_password'] = 'Password confirmation is required';
        } elseif ($input['new_password'] !== $input['confirm_password']) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        
        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }
    
    /**
     * Get JSON input
     */
    private function getJsonInput(): array {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
    
    /**
     * Get Bearer token
     */
    private function getBearerToken(): ?string {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        
        if ($authHeader && strpos($authHeader, 'Bearer ') === 0) {
            return substr($authHeader, 7);
        }
        
        return null;
    }
    
    /**
     * Get user by ID
     */
    private function getUserById(int $id): ?array {
        $query = "SELECT * FROM usuarios WHERE id = :id AND deleted_at IS NULL";
        return Database::fetch($query, ['id' => $id]);
    }
    
    /**
     * Invalidate user tokens
     */
    private function invalidateUserTokens(int $userId): void {
        // This would require a more sophisticated token management system
        // For now, we'll just log it
        error_log("User {$userId} changed password - tokens should be invalidated");
    }
    
    /**
     * Send JSON response
     */
    private function jsonResponse(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}

class ValidationException extends \Exception {
    private array $errors;
    
    public function __construct(string $message, array $errors = []) {
        parent::__construct($message);
        $this->errors = $errors;
    }
    
    public function getErrors(): array {
        return $this->errors;
    }
}
