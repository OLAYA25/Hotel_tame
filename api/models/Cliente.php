<?php
class Cliente {
    private $conn;
    private $table_name = "clientes";

    public $id;
    public $nombre;
    public $apellido;
    public $email;
    public $telefono;
    public $documento;
    public $fecha_nacimiento;
    public $tipo_documento;
    public $ciudad;
    public $pais;
    public $motivo_viaje;
    public $direccion;
    public $acompanantes_info;
    public $created_at;
    public $lastError;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para verificar si una columna existe en la tabla
    private function checkColumnExists($columnName) {
        $query = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                 WHERE TABLE_SCHEMA = DATABASE() 
                 AND TABLE_NAME = ? 
                 AND COLUMN_NAME = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->table_name, $columnName]);
        $count = $stmt->fetchColumn();
        return $count > 0;
    }

    // Obtener todos los clientes
    public function getAll() {
        // Consulta básica sin campos opcionales para evitar errores
        $query = "SELECT c.id, c.nombre, c.apellido, c.email, c.telefono, c.tipo_documento, c.documento, c.fecha_nacimiento, c.ciudad, c.pais, c.direccion, c.created_at
                  FROM " . $this->table_name . " c
                  WHERE c.deleted_at IS NULL 
                  ORDER BY c.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener cliente por ID
    public function getById() {
        // Consulta segura que verifica si existen los campos adicionales
        $query = "SELECT c.id, c.nombre, c.apellido, c.email, c.telefono, c.tipo_documento, c.documento, c.fecha_nacimiento, c.ciudad, c.pais, c.direccion, c.created_at,
                         CASE 
                             WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'clientes' AND COLUMN_NAME = 'motivo_viaje') > 0 
                             THEN c.motivo_viaje 
                             ELSE 'turismo' 
                         END as motivo_viaje,
                         CASE 
                             WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'clientes' AND COLUMN_NAME = 'acompanantes_info') > 0 
                             THEN c.acompanantes_info 
                             ELSE NULL 
                         END as acompanantes_info
                  FROM " . $this->table_name . " c
                  WHERE c.id = ? AND c.deleted_at IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch();
        if($row) {
            $this->nombre = $row['nombre'];
            $this->apellido = $row['apellido'] ?? '';
            $this->email = $row['email'];
            $this->telefono = $row['telefono'];
            $this->tipo_documento = $row['tipo_documento'] ?? null;
            $this->documento = $row['documento'];
            $this->fecha_nacimiento = $row['fecha_nacimiento'] ?? null;
            $this->ciudad = $row['ciudad'] ?? null;
            $this->pais = $row['pais'] ?? null;
            $this->direccion = $row['direccion'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    // Crear cliente
    public function create() {
        // Verificar qué campos existen en la tabla
        $motivoViajeExists = $this->checkColumnExists('motivo_viaje');
        $acompanantesInfoExists = $this->checkColumnExists('acompanantes_info');
        
        // Construir consulta dinámicamente según los campos existentes
        $fields = "nombre, apellido, tipo_documento, documento, email, telefono, fecha_nacimiento, ciudad, pais, direccion";
        $values = ":nombre, :apellido, :tipo_documento, :documento, :email, :telefono, :fecha_nacimiento, :ciudad, :pais, :direccion";
        
        if ($motivoViajeExists) {
            $fields .= ", motivo_viaje";
            $values .= ", :motivo_viaje";
        }
        if ($acompanantesInfoExists) {
            $fields .= ", acompanantes_info";
            $values .= ", :acompanantes_info";
        }
        
        $query = "INSERT INTO " . $this->table_name . " 
              ($fields) VALUES ($values)";
        
        $stmt = $this->conn->prepare($query);
        
        $this->nombre = htmlspecialchars(strip_tags($this->nombre ?? ''));
        $this->apellido = htmlspecialchars(strip_tags($this->apellido ?? ''));
        $this->tipo_documento = htmlspecialchars(strip_tags($this->tipo_documento ?? ''));
        $this->documento = htmlspecialchars(strip_tags($this->documento ?? ''));
        $this->email = htmlspecialchars(strip_tags($this->email ?? ''));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono ?? ''));
        $this->fecha_nacimiento = !empty($this->fecha_nacimiento) ? $this->fecha_nacimiento : null;
        $this->ciudad = htmlspecialchars(strip_tags($this->ciudad ?? ''));
        $this->pais = htmlspecialchars(strip_tags($this->pais ?? ''));
        $this->motivo_viaje = htmlspecialchars(strip_tags($this->motivo_viaje ?? 'turismo'));
        $this->direccion = htmlspecialchars(strip_tags($this->direccion ?? ''));
        $this->acompanantes_info = !empty($this->acompanantes_info) ? json_encode($this->acompanantes_info) : null;
        
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":apellido", $this->apellido);
        $stmt->bindParam(":tipo_documento", $this->tipo_documento);
        $stmt->bindParam(":documento", $this->documento);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":fecha_nacimiento", $this->fecha_nacimiento);
        $stmt->bindParam(":ciudad", $this->ciudad);
        $stmt->bindParam(":pais", $this->pais);
        $stmt->bindParam(":direccion", $this->direccion);
        
        if ($motivoViajeExists) {
            $stmt->bindParam(":motivo_viaje", $this->motivo_viaje);
        }
        if ($acompanantesInfoExists) {
            $stmt->bindParam(":acompanantes_info", $this->acompanantes_info);
        }
        
        try {
            if($stmt->execute()) {
                return true;
            } else {
                $err = $stmt->errorInfo();
                $this->lastError = implode(' | ', $err);
                return false;
            }
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    // Actualizar cliente
    public function update() {
        // Verificar qué campos existen en la tabla
        $motivoViajeExists = $this->checkColumnExists('motivo_viaje');
        $acompanantesInfoExists = $this->checkColumnExists('acompanantes_info');
        
        // Construir consulta dinámicamente según los campos existentes
        $setFields = "nombre = :nombre, 
                      apellido = :apellido, 
                      tipo_documento = :tipo_documento, 
                      documento = :documento, 
                      email = :email, 
                      telefono = :telefono, 
                      fecha_nacimiento = :fecha_nacimiento, 
                      ciudad = :ciudad, 
                      pais = :pais, 
                      direccion = :direccion";
        
        if ($motivoViajeExists) {
            $setFields .= ", motivo_viaje = :motivo_viaje";
        }
        if ($acompanantesInfoExists) {
            $setFields .= ", acompanantes_info = :acompanantes_info";
        }
        
        $setFields .= ", updated_at = NOW()";
        
        $query = "UPDATE " . $this->table_name . " 
                  SET $setFields
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->nombre = htmlspecialchars(strip_tags($this->nombre ?? ''));
        $this->apellido = htmlspecialchars(strip_tags($this->apellido ?? ''));
        $this->email = htmlspecialchars(strip_tags($this->email ?? ''));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono ?? ''));
        $this->documento = htmlspecialchars(strip_tags($this->documento ?? ''));
        $this->fecha_nacimiento = !empty($this->fecha_nacimiento) ? $this->fecha_nacimiento : null;
        $this->motivo_viaje = htmlspecialchars(strip_tags($this->motivo_viaje ?? 'turismo'));
        $this->direccion = htmlspecialchars(strip_tags($this->direccion ?? ''));
        $this->acompanantes_info = !empty($this->acompanantes_info) ? json_encode($this->acompanantes_info) : null;
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":apellido", $this->apellido);
        $stmt->bindParam(":tipo_documento", $this->tipo_documento);
        $stmt->bindParam(":documento", $this->documento);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":fecha_nacimiento", $this->fecha_nacimiento);
        $stmt->bindParam(":ciudad", $this->ciudad);
        $stmt->bindParam(":pais", $this->pais);
        $stmt->bindParam(":direccion", $this->direccion);
        $stmt->bindParam(":id", $this->id);
        
        if ($motivoViajeExists) {
            $stmt->bindParam(":motivo_viaje", $this->motivo_viaje);
        }
        if ($acompanantesInfoExists) {
            $stmt->bindParam(":acompanantes_info", $this->acompanantes_info);
        }

        try {
            if($stmt->execute()) {
                return true;
            } else {
                $err = $stmt->errorInfo();
                $this->lastError = implode(' | ', $err);
                return false;
            }
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    // Eliminar cliente (soft delete)
    public function delete() {
        $query = "UPDATE " . $this->table_name . " 
                  SET deleted_at = NOW() 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);
        
        try {
            if($stmt->execute()) {
                return true;
            } else {
                $err = $stmt->errorInfo();
                $this->lastError = implode(' | ', $err);
                return false;
            }
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    // Buscar cliente por documento
    public function getByDocumento() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE documento = ? AND deleted_at IS NULL LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->documento);
        $stmt->execute();
        return $stmt;
    }
}
?>
