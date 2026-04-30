<?php
/**
 * Excepción de validación
 */

class ValidationException extends Exception {
    private $errors;
    
    public function __construct($message, $errors = []) {
        parent::__construct($message);
        $this->errors = $errors;
    }
    
    public function getErrors() {
        return $this->errors;
    }
}

/**
 * Excepción de negocio
 */

class BusinessException extends Exception {
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Excepción de recurso no encontrado
 */

class NotFoundException extends Exception {
    public function __construct($message, $code = 404, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Excepción de autorización
 */

class AuthorizationException extends Exception {
    public function __construct($message, $code = 403, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
