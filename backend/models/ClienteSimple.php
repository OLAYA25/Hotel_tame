<?php
class ClienteSimple {
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
    public $direccion;
    public $created_at;
    public $lastError;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los clientes (solo campos básicos que sabemos que existen)
    public function getAll() {
        $query = "SELECT id, nombre, apellido, email, telefono, tipo_documento, documento, fecha_nacimiento, ciudad, pais, direccion, created_at 
                  FROM " . $this->table_name . " 
                  WHERE deleted_at IS NULL 
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener cliente por ID (solo campos básicos)
    public function getById() {
        $query = "SELECT id, nombre, apellido, email, telefono, tipo_documento, documento, fecha_nacimiento, ciudad, pais, direccion, created_at 
                  FROM " . $this->table_name . " 
                  WHERE id = ? AND deleted_at IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch();
        if($row) {
            $this->nombre = $row['nombre'];
            $this->apellido = $row['apellido'] ?? '';
            $this->email = $row['email'];
            $this->telefono = $row['telefono'];
            $this->documento = $row['documento'];
            $this->tipo_documento = $row['tipo_documento'] ?? null;
            $this->fecha_nacimiento = $row['fecha_nacimiento'] ?? null;
            $this->ciudad = $row['ciudad'] ?? null;
            $this->pais = $row['pais'] ?? null;
            $this->direccion = $row['direccion'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    // Crear cliente (solo campos básicos)
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
              (nombre, apellido, tipo_documento, documento, email, telefono, fecha_nacimiento, ciudad, pais, direccion) 
              VALUES (:nombre, :apellido, :tipo_documento, :documento, :email, :telefono, :fecha_nacimiento, :ciudad, :pais, :direccion)";
        
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
        $this->direccion = htmlspecialchars(strip_tags($this->direccion ?? ''));
        
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

    // Actualizar cliente (solo campos básicos)
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre = :nombre, 
                      apellido = :apellido, 
                      tipo_documento = :tipo_documento, 
                      documento = :documento, 
                      email = :email, 
                      telefono = :telefono, 
                      fecha_nacimiento = :fecha_nacimiento, 
                      ciudad = :ciudad, 
                      pais = :pais, 
                      direccion = :direccion,
                      updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->nombre = htmlspecialchars(strip_tags($this->nombre ?? ''));
        $this->apellido = htmlspecialchars(strip_tags($this->apellido ?? ''));
        $this->email = htmlspecialchars(strip_tags($this->email ?? ''));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono ?? ''));
        $this->documento = htmlspecialchars(strip_tags($this->documento ?? ''));
        $this->fecha_nacimiento = !empty($this->fecha_nacimiento) ? $this->fecha_nacimiento : null;
        $this->direccion = htmlspecialchars(strip_tags($this->direccion ?? ''));
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
}
?>
