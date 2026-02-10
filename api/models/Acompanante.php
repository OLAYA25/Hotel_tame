<?php
class Acompanante {
    private $conn;
    private $table_name = "acompanantes";
    
    public $id;
    public $reserva_id;
    public $nombre;
    public $apellido;
    public $tipo_documento;
    public $numero_documento;
    public $fecha_nacimiento;
    public $parentesco;
    public $edad;
    public $es_menor;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Crear acompañante
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (reserva_id, nombre, apellido, tipo_documento, numero_documento, fecha_nacimiento, parentesco)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $this->reserva_id);
        $stmt->bindParam(2, $this->nombre);
        $stmt->bindParam(3, $this->apellido);
        $stmt->bindParam(4, $this->tipo_documento);
        $stmt->bindParam(5, $this->numero_documento);
        $stmt->bindParam(6, $this->fecha_nacimiento);
        $stmt->bindParam(7, $this->parentesco);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
    
    // Obtener acompañantes por reserva
    public function getByReserva($reserva_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE reserva_id = ? ORDER BY created_at";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $reserva_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener todos los acompañantes
    public function getAll($limit = 50, $offset = 0) {
        $query = "SELECT a.*, r.fecha_entrada, r.fecha_salida, 
                        c.nombre as cliente_principal, h.numero as habitacion_numero
                 FROM " . $this->table_name . " a
                 JOIN reservas r ON a.reserva_id = r.id
                 JOIN clientes c ON r.cliente_id = c.id
                 JOIN habitaciones h ON r.habitacion_id = h.id
                 ORDER BY a.created_at DESC
                 LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->bindParam(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Actualizar acompañante
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET nombre = ?, apellido = ?, tipo_documento = ?, numero_documento = ?,
                    fecha_nacimiento = ?, parentesco = ?
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $this->nombre);
        $stmt->bindParam(2, $this->apellido);
        $stmt->bindParam(3, $this->tipo_documento);
        $stmt->bindParam(4, $this->numero_documento);
        $stmt->bindParam(5, $this->fecha_nacimiento);
        $stmt->bindParam(6, $this->parentesco);
        $stmt->bindParam(7, $this->id);
        
        return $stmt->execute();
    }
    
    // Eliminar acompañante
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        return $stmt->execute();
    }
    
    // Eliminar todos los acompañantes de una reserva
    public function deleteByReserva($reserva_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE reserva_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $reserva_id);
        
        return $stmt->execute();
    }
    
    // Obtener estadísticas de ocupación real
    public function getEstadisticasOcupacion($fecha_inicio = null, $fecha_fin = null) {
        $fecha_inicio = $fecha_inicio ?? date('Y-m-01');
        $fecha_fin = $fecha_fin ?? date('Y-m-d');
        
        $query = "SELECT 
                    COUNT(DISTINCT a.reserva_id) as reservas_con_acompanantes,
                    COUNT(a.id) as total_acompanantes,
                    SUM(CASE WHEN a.es_menor = TRUE THEN 1 ELSE 0 END) as total_menores,
                    SUM(CASE WHEN a.es_menor = FALSE THEN 1 ELSE 0 END) as total_adultos,
                    AVG(a.edad) as edad_promedio,
                    MIN(a.edad) as edad_minima,
                    MAX(a.edad) as edad_maxima
                 FROM " . $this->table_name . " a
                 JOIN reservas r ON a.reserva_id = r.id
                 WHERE r.fecha_entrada BETWEEN ? AND ?
                 AND r.estado = 'confirmada'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $fecha_inicio);
        $stmt->bindParam(2, $fecha_fin);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtener ocupación real por habitación
    public function getOcupacionReal($fecha_inicio = null, $fecha_fin = null) {
        $fecha_inicio = $fecha_inicio ?? date('Y-m-01');
        $fecha_fin = $fecha_fin ?? date('Y-m-d');
        
        $query = "CALL sp_ocupacion_real(?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $fecha_inicio);
        $stmt->bindParam(2, $fecha_fin);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Validar documento único por reserva
    public function validarDocumentoUnico($reserva_id, $numero_documento, $exclude_id = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . "
                 WHERE reserva_id = ? AND numero_documento = ?";
        
        if ($exclude_id) {
            $query .= " AND id != ?";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $reserva_id);
        $stmt->bindParam(2, $numero_documento);
        
        if ($exclude_id) {
            $stmt->bindParam(3, $exclude_id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] == 0;
    }
}
?>
