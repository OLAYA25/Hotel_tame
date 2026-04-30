<?php
/**
 * Validador de Clientes
 */

class ClienteValidator {
    
    /**
     * Validar datos de cliente
     */
    public static function validate($data) {
        $errors = [];
        
        // Validar nombre
        if (empty($data['nombre'])) {
            $errors['nombre'] = 'El nombre es requerido';
        } elseif (strlen($data['nombre']) < 2) {
            $errors['nombre'] = 'El nombre debe tener al menos 2 caracteres';
        } elseif (strlen($data['nombre']) > 100) {
            $errors['nombre'] = 'El nombre no puede exceder 100 caracteres';
        }
        
        // Validar apellido
        if (empty($data['apellido'])) {
            $errors['apellido'] = 'El apellido es requerido';
        } elseif (strlen($data['apellido']) < 2) {
            $errors['apellido'] = 'El apellido debe tener al menos 2 caracteres';
        } elseif (strlen($data['apellido']) > 100) {
            $errors['apellido'] = 'El apellido no puede exceder 100 caracteres';
        }
        
        // Validar email
        if (empty($data['email'])) {
            $errors['email'] = 'El email es requerido';
        } elseif (!self::isValidEmail($data['email'])) {
            $errors['email'] = 'El email no es válido';
        } elseif (strlen($data['email']) > 100) {
            $errors['email'] = 'El email no puede exceder 100 caracteres';
        }
        
        // Validar teléfono
        if (empty($data['telefono'])) {
            $errors['telefono'] = 'El teléfono es requerido';
        } elseif (!self::isValidPhone($data['telefono'])) {
            $errors['telefono'] = 'El teléfono no es válido';
        }
        
        // Validar documento
        if (empty($data['documento'])) {
            $errors['documento'] = 'El documento es requerido';
        } elseif (!self::isValidDocument($data['documento'])) {
            $errors['documento'] = 'El documento no es válido';
        } elseif (strlen($data['documento']) > 20) {
            $errors['documento'] = 'El documento no puede exceder 20 caracteres';
        }
        
        // Validar tipo de documento
        if (empty($data['tipo_documento'])) {
            $errors['tipo_documento'] = 'El tipo de documento es requerido';
        } elseif (!in_array($data['tipo_documento'], self::getDocumentTypes())) {
            $errors['tipo_documento'] = 'Tipo de documento inválido';
        }
        
        // Validar dirección
        if (!empty($data['direccion']) && strlen($data['direccion']) > 255) {
            $errors['direccion'] = 'La dirección no puede exceder 255 caracteres';
        }
        
        // Validar ciudad
        if (!empty($data['ciudad']) && strlen($data['ciudad']) > 50) {
            $errors['ciudad'] = 'La ciudad no puede exceder 50 caracteres';
        }
        
        return $errors;
    }
    
    /**
     * Validar email
     */
    private static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    /**
     * Validar teléfono
     */
    private static function isValidPhone($phone) {
        // Remover caracteres no numéricos
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Validar longitud (entre 7 y 15 dígitos)
        return strlen($phone) >= 7 && strlen($phone) <= 15;
    }
    
    /**
     * Validar documento
     */
    private static function isValidDocument($document) {
        // Remover espacios y caracteres especiales
        $document = preg_replace('/[^a-zA-Z0-9]/', '', $document);
        
        // Validar longitud (entre 5 y 20 caracteres)
        return strlen($document) >= 5 && strlen($document) <= 20;
    }
    
    /**
     * Validar si documento ya existe
     */
    public static function validateUniqueDocument($document, $excludeId = null) {
        require_once __DIR__ . '/../../config/database.php';
        
        try {
            $db = Database::getInstance();
            $sql = "SELECT COUNT(*) as count FROM clientes 
                    WHERE documento = :documento AND deleted_at IS NULL";
            
            $params = [':documento' => $document];
            
            if ($excludeId) {
                $sql .= " AND id != :exclude_id";
                $params[':exclude_id'] = $excludeId;
            }
            
            $result = $db->prepare($sql)->execute($params)->fetch();
            return $result['count'] == 0;
        } catch (Exception $e) {
            error_log("Error validando documento único: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar si email ya existe
     */
    public static function validateUniqueEmail($email, $excludeId = null) {
        require_once __DIR__ . '/../../config/database.php';
        
        try {
            $db = Database::getInstance();
            $sql = "SELECT COUNT(*) as count FROM clientes 
                    WHERE email = :email AND deleted_at IS NULL";
            
            $params = [':email' => $email];
            
            if ($excludeId) {
                $sql .= " AND id != :exclude_id";
                $params[':exclude_id'] = $excludeId;
            }
            
            $result = $db->prepare($sql)->execute($params)->fetch();
            return $result['count'] == 0;
        } catch (Exception $e) {
            error_log("Error validando email único: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener tipos de documento válidos
     */
    private static function getDocumentTypes() {
        return ['CC', 'CE', 'TI', 'PAS', 'NIT', 'PP', 'PEP'];
    }
    
    /**
     * Sanitizar datos de cliente
     */
    public static function sanitize($data) {
        return [
            'nombre' => ucwords(strtolower(trim($data['nombre'] ?? ''))),
            'apellido' => ucwords(strtolower(trim($data['apellido'] ?? ''))),
            'email' => strtolower(trim($data['email'] ?? '')),
            'telefono' => preg_replace('/[^0-9]/', '', $data['telefono'] ?? ''),
            'documento' => preg_replace('/[^a-zA-Z0-9]/', '', $data['documento'] ?? ''),
            'tipo_documento' => strtoupper(trim($data['tipo_documento'] ?? 'CC')),
            'direccion' => trim($data['direccion'] ?? ''),
            'ciudad' => ucwords(strtolower(trim($data['ciudad'] ?? ''))),
            'pais' => ucwords(strtolower(trim($data['pais'] ?? 'Colombia')))
        ];
    }
}
