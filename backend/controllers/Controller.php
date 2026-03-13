<?php
/**
 * Controlador base mejorado
 */

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/RoleMiddleware.php';
require_once __DIR__ . '/../exceptions/Exceptions.php';

abstract class Controller {
    protected $user;
    
    public function __construct() {
        $this->authenticate();
        $this->user = AuthMiddleware::user();
    }
    
    /**
     * Verificar autenticación
     */
    protected function authenticate() {
        try {
            $this->user = AuthMiddleware::handle();
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'No autorizado',
                'message' => $e->getMessage()
            ], 401);
        }
    }
    
    /**
     * Verificar permisos
     */
    protected function requirePermission($resource, $action) {
        try {
            RoleMiddleware::requirePermission($resource, $action);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Acceso denegado',
                'message' => $e->getMessage()
            ], 403);
        }
    }
    
    /**
     * Verificar rol
     */
    protected function requireRole($roles) {
        try {
            RoleMiddleware::requireRole($roles);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Acceso denegado',
                'message' => $e->getMessage()
            ], 403);
        }
    }
    
    /**
     * Enviar respuesta JSON estándar
     */
    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Enviar respuesta de éxito
     */
    protected function successResponse($message = 'Operación exitosa', $data = null) {
        $response = [
            'success' => true,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        $this->jsonResponse($response);
    }
    
    /**
     * Enviar respuesta de error
     */
    protected function errorResponse($message = 'Error en la operación', $errors = null, $statusCode = 400) {
        $response = [
            'success' => false,
            'error' => $message
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        $this->jsonResponse($response, $statusCode);
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
                        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
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
                        
                    case 'numeric':
                        if ($value && !is_numeric($value)) {
                            $errors[$field][] = "El campo $field debe ser numérico";
                        }
                        break;
                        
                    case 'date':
                        if ($value && !DateTime::createFromFormat('Y-m-d', $value)) {
                            $errors[$field][] = "El campo $field debe ser una fecha válida (YYYY-MM-DD)";
                        }
                        break;
                }
            }
        }
        
        if (!empty($errors)) {
            $this->errorResponse('Datos inválidos', $errors, 422);
        }
        
        return true;
    }
    
    /**
     * Obtener datos POST/PUT sanitizados
     */
    protected function getInput() {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true) ?? $_POST;
        
        // Sanitizar datos
        if (is_array($data)) {
            array_walk_recursive($data, function(&$value) {
                if (is_string($value)) {
                    $value = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
                }
            });
        }
        
        return $data;
    }
    
    /**
     * Verificar token CSRF
     */
    protected function verifyCSRF() {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        if (empty($token)) {
            $this->errorResponse('Token CSRF requerido', null, 403);
        }
        
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            $this->errorResponse('Token CSRF inválido', null, 403);
        }
    }
    
    /**
     * Obtener parámetros de la URL
     */
    protected function getParams() {
        $params = [];
        
        // Obtener parámetros de la URL
        if (isset($_SERVER['PATH_INFO'])) {
            $pathParts = explode('/', trim($_SERVER['PATH_INFO'], '/'));
            array_shift($pathParts); // Quitar el primer elemento vacío
            
            for ($i = 0; $i < count($pathParts); $i += 2) {
                if (isset($pathParts[$i + 1])) {
                    $params[$pathParts[$i]] = $pathParts[$i + 1];
                }
            }
        }
        
        return $params;
    }
    
    /**
     * Obtener ID del recurso
     */
    protected function getId() {
        $params = $this->getParams();
        return $params['id'] ?? null;
    }
    
    /**
     * Validar ID numérico
     */
    protected function validateId($id) {
        if (!is_numeric($id) || $id <= 0) {
            $this->errorResponse('ID inválido', null, 400);
        }
        
        return (int)$id;
    }
    
    /**
     * Manejar excepciones
     */
    protected function handleException($exception) {
        error_log("Controller Exception: " . $exception->getMessage());
        
        if ($exception instanceof ValidationException) {
            $this->errorResponse($exception->getMessage(), $exception->getErrors(), 422);
        } elseif ($exception instanceof BusinessException) {
            $this->errorResponse($exception->getMessage(), null, 400);
        } elseif ($exception instanceof NotFoundException) {
            $this->errorResponse($exception->getMessage(), null, 404);
        } elseif ($exception instanceof AuthorizationException) {
            $this->errorResponse($exception->getMessage(), null, 403);
        } else {
            $this->errorResponse('Error interno del servidor', null, 500);
        }
    }
}

/**
 * Función helper para array_walk_recursive
 */
if (!function_exists('array_walk_recursive')) {
    function array_walk_recursive(&$array, $callback) {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                array_walk_recursive($value, $callback);
            } else {
                $callback($value, $key);
            }
        }
    }
}
