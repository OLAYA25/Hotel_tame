<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Config\Config;
use Database\Database;
use Exception;

class AuthService {
    private string $jwtSecret;
    private int $jwtTtl;
    
    public function __construct() {
        $jwtConfig = Config::getJWTConfig();
        $this->jwtSecret = $jwtConfig['secret'];
        $this->jwtTtl = $jwtConfig['ttl'];
    }
    
    /**
     * Authenticate user
     */
    public function login(string $email, string $password): array {
        // Get user from database
        $user = $this->getUserByEmail($email);
        
        if (!$user) {
            throw new AuthenticationException('Invalid credentials');
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            $this->recordFailedLogin($email);
            throw new AuthenticationException('Invalid credentials');
        }
        
        // Check if account is locked
        if ($this->isAccountLocked($email)) {
            throw new AuthenticationException('Account temporarily locked');
        }
        
        // Clear failed attempts
        $this->clearFailedAttempts($email);
        
        // Update last login
        $this->updateLastLogin($user['id']);
        
        // Generate JWT token
        $token = $this->generateToken($user);
        
        // Store session
        $this->createSession($user);
        
        return [
            'user' => $this->sanitizeUser($user),
            'token' => $token,
            'expires_in' => $this->jwtTtl
        ];
    }
    
    /**
     * Logout user
     */
    public function logout(string $token): void {
        try {
            // Decode token to get user info
            $payload = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            
            // Invalidate token by adding to blacklist
            $this->invalidateToken($token, $payload->exp);
            
            // Destroy session
            $this->destroySession();
            
        } catch (Exception $e) {
            // Token might be invalid/expired, but still clear session
            $this->destroySession();
        }
    }
    
    /**
     * Validate JWT token
     */
    public function validateToken(string $token): array {
        try {
            $payload = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            
            // Check if token is blacklisted
            if ($this->isTokenBlacklisted($token)) {
                throw new AuthenticationException('Token has been invalidated');
            }
            
            return (array) $payload;
            
        } catch (ExpiredException $e) {
            throw new AuthenticationException('Token has expired');
        } catch (SignatureInvalidException $e) {
            throw new AuthenticationException('Invalid token signature');
        } catch (Exception $e) {
            throw new AuthenticationException('Invalid token');
        }
    }
    
    /**
     * Refresh token
     */
    public function refreshToken(string $refreshToken): array {
        try {
            $payload = JWT::decode($refreshToken, new Key($this->jwtSecret, 'HS256'));
            
            // Check if refresh token is valid type
            if ($payload->type !== 'refresh') {
                throw new AuthenticationException('Invalid refresh token');
            }
            
            // Get user
            $user = $this->getUserById($payload->sub);
            if (!$user) {
                throw new AuthenticationException('User not found');
            }
            
            // Generate new tokens
            $accessToken = $this->generateToken($user);
            $newRefreshToken = $this->generateRefreshToken($user);
            
            return [
                'access_token' => $accessToken,
                'refresh_token' => $newRefreshToken,
                'expires_in' => $this->jwtTtl
            ];
            
        } catch (Exception $e) {
            throw new AuthenticationException('Invalid refresh token');
        }
    }
    
    /**
     * Generate JWT token
     */
    private function generateToken(array $user): string {
        $payload = [
            'iss' => Config::get('APP_URL'),
            'sub' => $user['id'],
            'iat' => time(),
            'exp' => time() + $this->jwtTtl,
            'type' => 'access',
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['rol'],
                'name' => $user['nombre'] . ' ' . $user['apellido']
            ]
        ];
        
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }
    
    /**
     * Generate refresh token
     */
    private function generateRefreshToken(array $user): string {
        $payload = [
            'iss' => Config::get('APP_URL'),
            'sub' => $user['id'],
            'iat' => time(),
            'exp' => time() + Config::getJWTConfig()['refresh_ttl'],
            'type' => 'refresh'
        ];
        
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }
    
    /**
     * Get user by email
     */
    private function getUserByEmail(string $email): ?array {
        $query = "SELECT * FROM usuarios WHERE email = :email AND deleted_at IS NULL AND activo = 1";
        return Database::fetch($query, ['email' => $email]);
    }
    
    /**
     * Get user by ID
     */
    private function getUserById(int $id): ?array {
        $query = "SELECT * FROM usuarios WHERE id = :id AND deleted_at IS NULL AND activo = 1";
        return Database::fetch($query, ['id' => $id]);
    }
    
    /**
     * Record failed login attempt
     */
    private function recordFailedLogin(string $email): void {
        $query = "INSERT INTO login_attempts (email, ip_address, attempted_at) VALUES (:email, :ip, NOW())";
        Database::execute($query, [
            'email' => $email,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }
    
    /**
     * Check if account is locked
     */
    private function isAccountLocked(string $email): bool {
        $maxAttempts = Config::getSecurityConfig()['max_login_attempts'];
        $lockoutDuration = Config::getSecurityConfig()['lockout_duration'];
        
        $query = "SELECT COUNT(*) as attempts 
                  FROM login_attempts 
                  WHERE email = :email 
                  AND attempted_at > DATE_SUB(NOW(), INTERVAL :seconds SECOND)";
        
        $result = Database::fetch($query, [
            'email' => $email,
            'seconds' => $lockoutDuration
        ]);
        
        return $result['attempts'] >= $maxAttempts;
    }
    
    /**
     * Clear failed attempts
     */
    private function clearFailedAttempts(string $email): void {
        $query = "DELETE FROM login_attempts WHERE email = :email";
        Database::execute($query, ['email' => $email]);
    }
    
    /**
     * Update last login
     */
    private function updateLastLogin(int $userId): void {
        $query = "UPDATE usuarios SET ultimo_login = NOW() WHERE id = :id";
        Database::execute($query, ['id' => $userId]);
    }
    
    /**
     * Create session
     */
    private function createSession(array $user): void {
        session_start();
        $_SESSION['user'] = $this->sanitizeUser($user);
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Destroy session
     */
    private function destroySession(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
    
    /**
     * Invalidate token
     */
    private function invalidateToken(string $token, int $expiresAt): void {
        $query = "INSERT INTO token_blacklist (token, expires_at) VALUES (:token, :expires_at)";
        Database::execute($query, [
            'token' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', $expiresAt)
        ]);
    }
    
    /**
     * Check if token is blacklisted
     */
    private function isTokenBlacklisted(string $token): bool {
        $query = "SELECT COUNT(*) as count FROM token_blacklist 
                  WHERE token = :token AND expires_at > NOW()";
        
        $result = Database::fetch($query, [
            'token' => hash('sha256', $token)
        ]);
        
        return $result['count'] > 0;
    }
    
    /**
     * Sanitize user data
     */
    private function sanitizeUser(array $user): array {
        return [
            'id' => $user['id'],
            'nombre' => $user['nombre'],
            'apellido' => $user['apellido'],
            'email' => $user['email'],
            'rol' => $user['rol'],
            'telefono' => $user['telefono'] ?? null
        ];
    }
}

class AuthenticationException extends \Exception {
    // Custom exception for authentication errors
}
