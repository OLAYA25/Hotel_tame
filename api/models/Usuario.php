<?php
class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $nombre;
    public $apellido;
    public $email;
    public $password;
    public $rol;
    public $telefono;
    public $activo;
    public $created_at;
    public $lastError;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los usuarios
    public function getAll() {
        $query = "SELECT id, nombre, apellido, email, rol, telefono, activo, created_at 
                  FROM " . $this->table_name . " 
                  WHERE deleted_at IS NULL 
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener usuario por ID
    public function getById() {
        $query = "SELECT id, nombre, apellido, email, rol, telefono, activo, created_at 
                  FROM " . $this->table_name . " 
                  WHERE id = ? AND deleted_at IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch();
        if($row) {
            $this->nombre = $row['nombre'];
            $this->email = $row['email'];
            $this->rol = $row['rol'];
            $this->telefono = $row['telefono'];
            $this->apellido = $row['apellido'];
            $this->activo = $row['activo'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    // Crear usuario
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nombre, apellido, email, password, rol, telefono, activo) 
                  VALUES (:nombre, :apellido, :email, :password, :rol, :telefono, :activo)";
        
        $stmt = $this->conn->prepare($query);
        
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellido = htmlspecialchars(strip_tags($this->apellido));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        $this->rol = htmlspecialchars(strip_tags($this->rol));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":apellido", $this->apellido);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":rol", $this->rol);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":activo", $this->activo);
        
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

    // Actualizar usuario con contraseña
    public function updateWithPassword() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre = :nombre, 
                      apellido = :apellido, 
                      email = :email, 
                      password = :password,
                      rol = :rol, 
                      telefono = :telefono, 
                      activo = :activo,
                      updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellido = htmlspecialchars(strip_tags($this->apellido));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->rol = htmlspecialchars(strip_tags($this->rol));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":apellido", $this->apellido);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":rol", $this->rol);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":activo", $this->activo);
        $stmt->bindParam(":id", $this->id);
        
        try {
            if($stmt->execute()) {
                return true;
            } else {
                $this->lastError = "Error al ejecutar la consulta: " . implode(", ", $stmt->errorInfo());
                return false;
            }
        } catch(PDOException $exception) {
            $this->lastError = $exception->getMessage();
            return false;
        }
    }
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre = :nombre, 
                      apellido = :apellido, 
                      email = :email, 
                      rol = :rol, 
                      telefono = :telefono, 
                      activo = :activo,
                      updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellido = htmlspecialchars(strip_tags($this->apellido));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->rol = htmlspecialchars(strip_tags($this->rol));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":apellido", $this->apellido);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":rol", $this->rol);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":activo", $this->activo);
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

    // Eliminar usuario (soft delete)
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

    // Verificar email único
    public function emailExists() {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE email = ? AND deleted_at IS NULL LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }
}
?>
