<?php
class ReservaHuesped {
    private $conn;
    private $table_name = "reserva_huespedes";
    
    public $id;
    public $reserva_id;
    public $persona_id;
    public $rol_en_reserva;
    public $parentesco;
    public $es_menor;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Crear relación reserva-huésped
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (reserva_id, persona_id, rol_en_reserva, parentesco, es_menor)
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $this->reserva_id);
        $stmt->bindParam(2, $this->persona_id);
        $stmt->bindParam(3, $this->rol_en_reserva);
        $stmt->bindParam(4, $this->parentesco);
        $stmt->bindParam(5, $this->es_menor);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
    
    // Obtener huéspedes por reserva
    public function getByReserva($reserva_id) {
        $query = "SELECT 
                    rh.*,
                    p.nombre,
                    p.apellido,
                    p.tipo_documento,
                    p.numero_documento,
                    p.email,
                    p.telefono,
                    p.fecha_nacimiento,
                    TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) as edad_actual
                 FROM " . $this->table_name . " rh
                 JOIN personas p ON rh.persona_id = p.id
                 WHERE rh.reserva_id = ?
                 ORDER BY 
                    CASE WHEN rh.rol_en_reserva = 'principal' THEN 1 ELSE 2 END,
                    p.nombre, p.apellido";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $reserva_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener reservas por persona
    public function getByPersona($persona_id) {
        $query = "SELECT 
                    rh.*,
                    r.fecha_entrada,
                    r.fecha_salida,
                    r.estado,
                    h.numero as habitacion_numero,
                    h.tipo as habitacion_tipo
                 FROM " . $this->table_name . " rh
                 JOIN reservas r ON rh.reserva_id = r.id
                 JOIN habitaciones h ON r.habitacion_id = h.id
                 WHERE rh.persona_id = ?
                 ORDER BY r.fecha_entrada DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $persona_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Verificar si persona ya está en reserva
    public function existsInReserva($reserva_id, $persona_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . "
                 WHERE reserva_id = ? AND persona_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $reserva_id);
        $stmt->bindParam(2, $persona_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
    
    // Actualizar relación
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET rol_en_reserva = ?, parentesco = ?, es_menor = ?, updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $this->rol_en_reserva);
        $stmt->bindParam(2, $this->parentesco);
        $stmt->bindParam(3, $this->es_menor);
        $stmt->bindParam(4, $this->id);
        
        return $stmt->execute();
    }
    
    // Eliminar relación
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        return $stmt->execute();
    }
    
    // Eliminar todos los huéspedes de una reserva
    public function deleteByReserva($reserva_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE reserva_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $reserva_id);
        
        return $stmt->execute();
    }
    
    // Agregar múltiples huéspedes a una reserva (transacción)
    public function agregarHuespedesAReserva($reserva_id, $huespedes) {
        $this->conn->beginTransaction();
        
        try {
            foreach ($huespedes as $huesped) {
                $query = "INSERT INTO " . $this->table_name . "
                        (reserva_id, persona_id, rol_en_reserva, parentesco, es_menor)
                        VALUES (?, ?, ?, ?, ?)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->execute([
                    $reserva_id,
                    $huesped['persona_id'],
                    $huesped['rol_en_reserva'],
                    $huesped['parentesco'] ?? null,
                    $huesped['es_menor'] ?? false
                ]);
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
    
    // Obtener ocupación real de una reserva
    public function getOcupacionReal($reserva_id) {
        $query = "SELECT 
                    COUNT(*) as total_huespedes,
                    SUM(CASE WHEN rol_en_reserva = 'principal' THEN 1 ELSE 0 END) as principales,
                    SUM(CASE WHEN rol_en_reserva = 'acompanante' THEN 1 ELSE 0 END) as acompanantes,
                    SUM(CASE WHEN es_menor = TRUE THEN 1 ELSE 0 END) as menores,
                    SUM(CASE WHEN es_menor = FALSE THEN 1 ELSE 0 END) as adultos
                 FROM " . $this->table_name . "
                 WHERE reserva_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $reserva_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtener estadísticas generales
    public function getEstadisticasGenerales($fecha_inicio = null, $fecha_fin = null) {
        $fecha_inicio = $fecha_inicio ?? date('Y-m-01');
        $fecha_fin = $fecha_fin ?? date('Y-m-d');
        
        $query = "SELECT 
                    COUNT(DISTINCT rh.reserva_id) as total_reservas,
                    COUNT(rh.id) as total_huespedes,
                    SUM(CASE WHEN rh.rol_en_reserva = 'principal' THEN 1 ELSE 0 END) as total_principales,
                    SUM(CASE WHEN rh.rol_en_reserva = 'acompanante' THEN 1 ELSE 0 END) as total_acompanantes,
                    SUM(CASE WHEN rh.es_menor = TRUE THEN 1 ELSE 0 END) as total_menores,
                    SUM(CASE WHEN rh.es_menor = FALSE THEN 1 ELSE 0 END) as total_adultos,
                    COUNT(DISTINCT rh.persona_id) as personas_unicas
                 FROM " . $this->table_name . " rh
                 JOIN reservas r ON rh.reserva_id = r.id
                 WHERE r.fecha_entrada BETWEEN ? AND ?
                 AND r.deleted_at IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $fecha_inicio);
        $stmt->bindParam(2, $fecha_fin);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtener personas frecuentes
    public function getPersonasFrecuentes($limite = 10) {
        $query = "SELECT 
                    p.id,
                    p.nombre,
                    p.apellido,
                    p.numero_documento,
                    COUNT(rh.reserva_id) as total_reservas,
                    SUM(r.precio_total) as total_gastado,
                    MAX(r.fecha_entrada) as ultima_visita
                 FROM " . $this->table_name . " rh
                 JOIN personas p ON rh.persona_id = p.id
                 JOIN reservas r ON rh.reserva_id = r.id
                 WHERE r.deleted_at IS NULL AND p.deleted_at IS NULL
                 GROUP BY p.id, p.nombre, p.apellido, p.numero_documento
                 HAVING total_reservas > 1
                 ORDER BY total_reservas DESC, total_gastado DESC
                 LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Validar que solo haya un principal por reserva
    public function validarUnicoPrincipal($reserva_id, $exclude_id = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . "
                 WHERE reserva_id = ? AND rol_en_reserva = 'principal'";
        
        if ($exclude_id) {
            $query .= " AND id != ?";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $reserva_id);
        
        if ($exclude_id) {
            $stmt->bindParam(2, $exclude_id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] == 0;
    }
}
?>
