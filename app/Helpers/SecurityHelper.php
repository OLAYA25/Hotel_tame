<?php
/**
 * Helper de seguridad
 */

require_once __DIR__ . '/../../config/app.php';

class SecurityHelper {
    
    /**
     * Generar hash de contraseña seguro
     */
    public static function hashPassword($password) {
        return password_hash($password, App::HASH_ALGO);
    }
    
    /**
     * Verificar contraseña
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Sanitizar input
     */
    public static function sanitize($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Generar token CSRF
     */
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(App::CSRF_TOKEN_LENGTH));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verificar token CSRF
     */
    public static function verifyCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            throw new Exception("Token CSRF inválido");
        }
        return true;
    }
    
    /**
     * Validar email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    /**
     * Generar string aleatorio
     */
    public static function generateRandomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Regenerar ID de sesión
     */
    public static function regenerateSession() {
        session_regenerate_id(true);
    }
    
    /**
     * Validar sesión activa
     */
    public static function validateSession() {
        if (!isset($_SESSION['usuario']) || !isset($_SESSION['last_activity'])) {
            return false;
        }
        
        // Verificar tiempo de inactividad
        if (time() - $_SESSION['last_activity'] > App::SESSION_LIFETIME) {
            session_destroy();
            return false;
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    /**
     * Registrar intento de login fallido
     */
    public static function logFailedAttempt($email) {
        $attempts = $_SESSION['login_attempts'][$email] ?? 0;
        $_SESSION['login_attempts'][$email] = $attempts + 1;
        
        if ($attempts >= App::MAX_LOGIN_ATTEMPTS) {
            $_SESSION['locked_until'] = time() + 900; // 15 minutos
        }
    }
    
    /**
     * Verificar si cuenta está bloqueada
     */
    public static function isAccountLocked($email) {
        return isset($_SESSION['locked_until']) && time() < $_SESSION['locked_until'];
    }
}
