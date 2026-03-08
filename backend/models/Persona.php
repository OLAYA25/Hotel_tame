<?php
class Persona {
    private $conn;
    private $table_name = "personas";
    
    public $id;
    public $nombre;
    public $apellido;
    public $tipo_documento;
    public $numero_documento;
    public $fecha_nacimiento;
    public $email;
    public $telefono;
    public $direccion;
    public $ciudad;
    public $pais;
    public $tipo_persona;
    public $preferencias;
    public $created_at;
    public $updated_at;
    public $deleted_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Crear persona
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (nombre, apellido, tipo_documento, numero_documento, fecha_nacimiento, 
                 email, telefono, direccion, ciudad, pais, tipo_persona, preferencias)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $this->nombre);
        $stmt->bindParam(2, $this->apellido);
        $stmt->bindParam(3, $this->tipo_documento);
        $stmt->bindParam(4, $this->numero_documento);
        $stmt->bindParam(5, $this->fecha_nacimiento);
        $stmt->bindParam(6, $this->email);
        $stmt->bindParam(7, $this->telefono);
        $stmt->bindParam(8, $this->direccion);
        $stmt->bindParam(9, $this->ciudad);
        $stmt->bindParam(10, $this->pais);
        $stmt->bindParam(11, $this->tipo_persona);
        $stmt->bindParam(12, $this->preferencias);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
    
    // Obtener persona por ID
    public function getById() {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE id = ? AND deleted_at IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->populateFromRow($row);
            return true;
        }
        return false;
    }
    
    // Buscar persona por documento
    public function getByDocumento() {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE numero_documento = ? AND deleted_at IS NULL LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->numero_documento);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->populateFromRow($row);
            return true;
        }
        return false;
    }
    
    // Buscar personas por nombre o documento
    public function search($termino, $limit = 50) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE (nombre LIKE ? OR apellido LIKE ? OR numero_documento LIKE ?) 
                 AND deleted_at IS NULL
                 ORDER BY nombre, apellido
                 LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $search_term = "%{$termino}%";
        $stmt->bindParam(1, $search_term);
        $stmt->bindParam(2, $search_term);
        $stmt->bindParam(3, $search_term);
        $stmt->bindParam(4, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener todas las personas
    public function getAll($limit = 100, $offset = 0) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE deleted_at IS NULL
                 ORDER BY nombre, apellido
                 LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->bindParam(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Actualizar persona
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET nombre = ?, apellido = ?, tipo_documento = ?, numero_documento = ?,
                    fecha_nacimiento = ?, email = ?, telefono = ?, direccion = ?,
                    ciudad = ?, pais = ?, tipo_persona = ?, preferencias = ?,
                    updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $this->nombre);
        $stmt->bindParam(2, $this->apellido);
        $stmt->bindParam(3, $this->tipo_documento);
        $stmt->bindParam(4, $this->numero_documento);
        $stmt->bindParam(5, $this->fecha_nacimiento);
        $stmt->bindParam(6, $this->email);
        $stmt->bindParam(7, $this->telefono);
        $stmt->bindParam(8, $this->direccion);
        $stmt->bindParam(9, $this->ciudad);
        $stmt->bindParam(10, $this->pais);
        $stmt->bindParam(11, $this->tipo_persona);
        $stmt->bindParam(12, $this->preferencias);
        $stmt->bindParam(13, $this->id);
        
        return $stmt->execute();
    }
    
    // Eliminar persona (soft delete)
    public function delete() {
        $query = "UPDATE " . $this->table_name . " 
                SET deleted_at = NOW() 
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        return $stmt->execute();
    }
    
    // Obtener historial de reservas de una persona
    public function getHistorialReservas() {
        $query = "SELECT 
                    r.id as reserva_id,
                    r.fecha_entrada,
                    r.fecha_salida,
                    h.numero as habitacion_numero,
                    h.tipo as habitacion_tipo,
                    rh.rol_en_reserva,
                    rh.parentesco,
                    r.estado as estado_reserva,
                    r.precio_total
                 FROM reserva_huespedes rh
                 JOIN reservas r ON rh.reserva_id = r.id
                 JOIN habitaciones h ON r.habitacion_id = h.id
                 WHERE rh.persona_id = ? AND r.deleted_at IS NULL
                 ORDER BY r.fecha_entrada DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener estadísticas de persona
    public function getEstadisticas() {
        $query = "SELECT 
                    COUNT(*) as total_reservas,
                    SUM(CASE WHEN rh.rol_en_reserva = 'principal' THEN 1 ELSE 0 END) as veces_principal,
                    SUM(CASE WHEN rh.rol_en_reserva = 'acompanante' THEN 1 ELSE 0 END) as veces_acompanante,
                    SUM(r.precio_total) as total_gastado,
                    MIN(r.fecha_entrada) as primera_visita,
                    MAX(r.fecha_entrada) as ultima_visita,
                    AVG(r.precio_total) as promedio_gasto
                 FROM reserva_huespedes rh
                 JOIN reservas r ON rh.reserva_id = r.id
                 WHERE rh.persona_id = ? AND r.deleted_at IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Actualizar tipo de persona basado en su historial
    public function actualizarTipoPersona() {
        $query = "SELECT COUNT(*) as total_reservas
                 FROM reserva_huespedes 
                 WHERE persona_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_reservas = $result['total_reservas'];
        
        $nuevo_tipo = ($total_reservas > 1) ? 'cliente_frecuente' : 'ocasional';
        
        $update_query = "UPDATE " . $this->table_name . "
                        SET tipo_persona = ?, updated_at = NOW()
                        WHERE id = ?";
        
        $update_stmt = $this->conn->prepare($update_query);
        $update_stmt->bindParam(1, $nuevo_tipo);
        $update_stmt->bindParam(2, $this->id);
        
        return $update_stmt->execute();
    }
    
    // Validar documento único
    public function validarDocumentoUnico($exclude_id = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . "
                 WHERE numero_documento = ? AND deleted_at IS NULL";
        
        if ($exclude_id) {
            $query .= " AND id != ?";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->numero_documento);
        
        if ($exclude_id) {
            $stmt->bindParam(2, $exclude_id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] == 0;
    }
    
    // Método auxiliar para poblar objeto desde fila de BD
    private function populateFromRow($row) {
        $this->id = $row['id'];
        $this->nombre = $row['nombre'];
        $this->apellido = $row['apellido'];
        $this->tipo_documento = $row['tipo_documento'];
        $this->numero_documento = $row['numero_documento'];
        $this->fecha_nacimiento = $row['fecha_nacimiento'];
        $this->email = $row['email'];
        $this->telefono = $row['telefono'];
        $this->direccion = $row['direccion'];
        $this->ciudad = $row['ciudad'];
        $this->pais = $row['pais'];
        $this->tipo_persona = $row['tipo_persona'];
        $this->preferencias = $row['preferencias'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
        $this->deleted_at = $row['deleted_at'];
    }
    
    // Obtener edad actual
    public function getEdadActual() {
        if (!$this->fecha_nacimiento) {
            return null;
        }
        
        $query = "SELECT TIMESTAMPDIFF(YEAR, ?, CURDATE()) as edad";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->fecha_nacimiento);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['edad'];
    }
}
?>
