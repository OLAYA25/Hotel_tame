<?php
/**
 * Validador de Reservas
 */

class ReservaValidator {
    
    /**
     * Validar datos de reserva
     */
    public static function validate($data) {
        $errors = [];
        
        // Validar habitación
        if (empty($data['habitacion_id'])) {
            $errors['habitacion_id'] = 'La habitación es requerida';
        } elseif (!is_numeric($data['habitacion_id'])) {
            $errors['habitacion_id'] = 'ID de habitación inválido';
        }
        
        // Validar fechas
        if (empty($data['fecha_entrada'])) {
            $errors['fecha_entrada'] = 'La fecha de entrada es requerida';
        } elseif (!self::isValidDate($data['fecha_entrada'])) {
            $errors['fecha_entrada'] = 'Formato de fecha inválido';
        }
        
        if (empty($data['fecha_salida'])) {
            $errors['fecha_salida'] = 'La fecha de salida es requerida';
        } elseif (!self::isValidDate($data['fecha_salida'])) {
            $errors['fecha_salida'] = 'Formato de fecha inválido';
        }
        
        // Validar lógica de fechas
        if (isset($data['fecha_entrada']) && isset($data['fecha_salida'])) {
            $entrada = new DateTime($data['fecha_entrada']);
            $salida = new DateTime($data['fecha_salida']);
            
            if ($entrada >= $salida) {
                $errors['fecha_salida'] = 'La fecha de salida debe ser posterior a la de entrada';
            }
            
            if ($entrada < new DateTime()) {
                $errors['fecha_entrada'] = 'La fecha de entrada no puede ser anterior a hoy';
            }
            
            $noches = $entrada->diff($salida)->days;
            if ($noches > 30) {
                $errors['fecha_salida'] = 'La estancia no puede exceder 30 noches';
            }
        }
        
        // Validar clientes
        if (empty($data['clientes']) || !is_array($data['clientes'])) {
            $errors['clientes'] = 'Debe especificar al menos un cliente';
        } else {
            $titular = false;
            foreach ($data['clientes'] as $index => $cliente) {
                if (empty($cliente['id'])) {
                    $errors["clientes_{$index}"] = 'ID de cliente requerido';
                }
                
                if (empty($cliente['rol'])) {
                    $errors["clientes_{$index}_rol"] = 'Rol del cliente requerido';
                } elseif (!in_array($cliente['rol'], ['titular', 'acompanante'])) {
                    $errors["clientes_{$index}_rol"] = 'Rol inválido';
                }
                
                if ($cliente['rol'] === 'titular') {
                    $titular = true;
                }
            }
            
            if (!$titular) {
                $errors['clientes'] = 'Debe especificar al menos un cliente titular';
            }
        }
        
        return $errors;
    }
    
    /**
     * Validar disponibilidad de habitación
     */
    public static function validateAvailability($habitacionId, $fechaEntrada, $fechaSalida, $excludeId = null) {
        require_once __DIR__ . '/../../config/database.php';
        
        try {
            $db = Database::getInstance();
            $sql = "SELECT COUNT(*) as count FROM reservas
                    WHERE habitacion_id = :habitacion_id
                    AND estado IN ('confirmada', 'ocupada')
                    AND fecha_entrada <= :fecha_salida
                    AND fecha_salida >= :fecha_entrada
                    AND deleted_at IS NULL";
            
            $params = [
                ':habitacion_id' => $habitacionId,
                ':fecha_entrada' => $fechaEntrada,
                ':fecha_salida' => $fechaSalida
            ];
            
            if ($excludeId) {
                $sql .= " AND id != :exclude_id";
                $params[':exclude_id'] = $excludeId;
            }
            
            $result = $db->prepare($sql)->execute($params)->fetch();
            return $result['count'] == 0;
        } catch (Exception $e) {
            error_log("Error validando disponibilidad: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar fecha
     */
    private static function isValidDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    /**
     * Validar reglas de negocio
     */
    public static function validateBusinessRules($data) {
        $errors = [];
        
        // Validar que no sea fin de semana largo (si aplica)
        $entrada = new DateTime($data['fecha_entrada']);
        $salida = new DateTime($data['fecha_salida']);
        
        // Validar capacidad máxima
        if (isset($data['clientes']) && count($data['clientes']) > 4) {
            $errors['clientes'] = 'No se permiten más de 4 huéspedes por habitación';
        }
        
        // Validar tiempo mínimo de antelación
        $hoy = new DateTime();
        $diferencia = $hoy->diff($entrada);
        if ($diferencia->days < 1 && $diferencia->h < 2) {
            $errors['fecha_entrada'] = 'La reserva debe hacerse con al menos 2 horas de antelación';
        }
        
        return $errors;
    }
}
