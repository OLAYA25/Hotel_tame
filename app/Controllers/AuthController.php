<?php
/**
 * Controlador de Autenticación
 */

require_once __DIR__ . '/../../config/env.php';

class AuthController extends Controller {
    private $userModel;
    
    public function __construct() {
        // No requerir autenticación para login/logout
        $this->userModel = new User();
    }
    
    /**
     * Login de usuario
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->getPostData();
            
            // Validar datos
            $this->validate($data, [
                'email' => ['required', 'email'],
                'password' => ['required', 'min' => 6]
            ]);
            
            $email = $data['email'];
            $password = $data['password'];
            
            // Verificar si cuenta está bloqueada
            if (SecurityHelper::isAccountLocked($email)) {
                $this->jsonResponse(['error' => 'Cuenta temporalmente bloqueada. Intenta más tarde.'], 423);
            }
            
            // Intentar autenticación
            $user = $this->userModel->authenticate($email, $password);
            
            if ($user) {
                // Regenerar sesión
                SecurityHelper::regenerateSession();
                
                // Establecer sesión
                $_SESSION['usuario'] = [
                    'id' => $user['id'],
                    'nombre' => $user['nombre'],
                    'apellido' => $user['apellido'],
                    'email' => $user['email'],
                    'rol' => $user['rol'],
                    'telefono' => $user['telefono']
                ];
                
                $_SESSION['last_activity'] = time();
                
                // Registrar login exitoso
                AuditHelper::log($user['id'], 'login', 'usuarios', $user['id']);
                
                $this->jsonResponse([
                    'message' => 'Login exitoso',
                    'user' => $_SESSION['usuario'],
                    'redirect' => hotel_tame_url_path('dashboard')
                ]);
                
            } else {
                // Registrar intento fallido
                SecurityHelper::logFailedAttempt($email);
                
                $this->jsonResponse([
                    'error' => 'Credenciales incorrectas'
                ], 401);
            }
        }
    }
    
    /**
     * Logout de usuario
     */
    public function logout() {
        if (isset($_SESSION['usuario'])) {
            // Registrar logout
            AuditHelper::log($_SESSION['usuario']['id'], 'logout', 'usuarios', $_SESSION['usuario']['id']);
        }
        
        // Destruir sesión
        session_destroy();
        
        $this->jsonResponse([
            'message' => 'Logout exitoso',
            'redirect' => hotel_tame_url_path('login')
        ]);
    }
    
    /**
     * Verificar estado de sesión
     */
    public function check() {
        if (SecurityHelper::validateSession()) {
            $this->jsonResponse([
                'authenticated' => true,
                'user' => $_SESSION['usuario']
            ]);
        } else {
            $this->jsonResponse([
                'authenticated' => false
            ]);
        }
    }
    
    /**
     * Cambiar contraseña
     */
    public function changePassword() {
        $this->verifyCSRF();
        
        $data = $this->getPostData();
        
        // Validar datos
        $this->validate($data, [
            'current_password' => ['required'],
            'new_password' => ['required', 'min' => 8],
            'confirm_password' => ['required']
        ]);
        
        // Verificar que las nuevas contraseñas coincidan
        if ($data['new_password'] !== $data['confirm_password']) {
            $this->jsonResponse(['error' => 'Las contraseñas no coinciden'], 422);
        }
        
        // Verificar contraseña actual
        $user = $this->userModel->authenticate($this->user['email'], $data['current_password']);
        
        if (!$user) {
            $this->jsonResponse(['error' => 'La contraseña actual es incorrecta'], 400);
        }
        
        try {
            // Actualizar contraseña
            $this->userModel->updatePassword($this->user['id'], $data['new_password']);
            
            // Registrar cambio de contraseña
            AuditHelper::log($this->user['id'], 'cambiar_password', 'usuarios', $this->user['id']);
            
            $this->jsonResponse(['message' => 'Contraseña actualizada exitosamente']);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => 'Error al actualizar contraseña'], 500);
        }
    }
    
    /**
     * Obtener información del usuario actual
     */
    public function profile() {
        $this->jsonResponse($_SESSION['usuario']);
    }
}
