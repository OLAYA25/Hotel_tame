<?php
/**
 * Controlador base
 */

abstract class Controller {
    protected $user;
    
    public function __construct() {
        $this->authenticate();
        $this->user = $_SESSION['usuario'];
    }
    
    /**
     * Verificar autenticación
     */
    protected function authenticate() {
        if (!SecurityHelper::validateSession()) {
            header('Location: ' . hotel_tame_url_path('login'));
            exit;
        }
    }
    
    /**
     * Verificar permisos de rol
     */
    protected function requireRole($roles) {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        if (!in_array($this->user['rol'], $roles)) {
            http_response_code(403);
            echo json_encode(['error' => 'No tienes permisos para esta acción']);
            exit;
        }
    }
    
    /**
     * Enviar respuesta JSON
     */
    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Validar datos de entrada
     */
    protected function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            
            foreach ($fieldRules as $rule => $ruleValue) {
                switch ($rule) {
                    case 'required':
                        if (empty($value)) {
                            $errors[$field][] = "El campo $field es requerido";
                        }
                        break;
                        
                    case 'email':
                        if ($value && !SecurityHelper::validateEmail($value)) {
                            $errors[$field][] = "El campo $field debe ser un email válido";
                        }
                        break;
                        
                    case 'min':
                        if ($value && strlen($value) < $ruleValue) {
                            $errors[$field][] = "El campo $field debe tener al menos $ruleValue caracteres";
                        }
                        break;
                        
                    case 'max':
                        if ($value && strlen($value) > $ruleValue) {
                            $errors[$field][] = "El campo $field no puede tener más de $ruleValue caracteres";
                        }
                        break;
                }
            }
        }
        
        if (!empty($errors)) {
            $this->jsonResponse(['errors' => $errors], 422);
        }
        
        return true;
    }
    
    /**
     * Obtener datos POST sanitizados
     */
    protected function getPostData() {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true) ?? $_POST;
        
        return SecurityHelper::sanitize($data);
    }
    
    /**
     * Verificar token CSRF
     */
    protected function verifyCSRF() {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        if (!SecurityHelper::verifyCSRFToken($token)) {
            $this->jsonResponse(['error' => 'Token CSRF inválido'], 403);
        }
    }
}
