<?php
class Rol {
    private $conn;
    private $table_name = "roles";
    
    public $id;
    public $nombre;
    public $descripcion;
    public $nivel_acceso;
    public $activo;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Obtener todos los roles
    public function getAll() {
        $query = "SELECT r.*, 
                 (SELECT COUNT(*) FROM usuarios_roles ur WHERE ur.rol_id = r.id) as usuarios_count
                 FROM " . $this->table_name . " r 
                 WHERE r.deleted_at IS NULL 
                 ORDER BY r.nivel_acceso DESC, r.nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    // Obtener rol por ID
    public function getById() {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE id = :id AND deleted_at IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->nombre = $row['nombre'];
            $this->descripcion = $row['descripcion'];
            $this->nivel_acceso = $row['nivel_acceso'];
            $this->activo = $row['activo'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }
    
    // Crear rol
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (nombre, descripcion, nivel_acceso, activo) 
                 VALUES (:nombre, :descripcion, :nivel_acceso, :activo)";
        
        $stmt = $this->conn->prepare($query);
        
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":nivel_acceso", $this->nivel_acceso);
        $stmt->bindParam(":activo", $this->activo);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
    
    // Actualizar rol
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET nombre = :nombre, 
                     descripcion = :descripcion, 
                     nivel_acceso = :nivel_acceso, 
                     activo = :activo,
                     updated_at = NOW()
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":nivel_acceso", $this->nivel_acceso);
        $stmt->bindParam(":activo", $this->activo);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Eliminar rol (soft delete)
    public function delete() {
        $query = "UPDATE " . $this->table_name . " 
                 SET deleted_at = NOW() 
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Obtener permisos de un rol
    public function getPermisos() {
        $query = "SELECT p.* 
                 FROM permisos p 
                 JOIN roles_permisos rp ON p.id = rp.permiso_id 
                 WHERE rp.rol_id = :id 
                 ORDER BY p.nombre";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        return $stmt;
    }
    
    // Asignar permisos a un rol
    public function assignPermisos($permisos_ids) {
        // Primero eliminar permisos existentes
        $query = "DELETE FROM roles_permisos WHERE rol_id = :rol_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":rol_id", $this->id);
        $stmt->execute();
        
        // Luego insertar nuevos permisos
        if (!empty($permisos_ids)) {
            $query = "INSERT INTO roles_permisos (rol_id, permiso_id) VALUES ";
            $values = [];
            $params = [];
            
            foreach ($permisos_ids as $i => $permiso_id) {
                $values[] = "(:rol_id{$i}, :permiso_id{$i})";
                $params[":rol_id{$i}"] = $this->id;
                $params[":permiso_id{$i}"] = $permiso_id;
            }
            
            $query .= implode(", ", $values);
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            return $stmt->execute();
        }
        
        return true;
    }
    
    // Verificar si rol tiene un permiso específico
    public function hasPermiso($clave_permiso) {
        $query = "SELECT COUNT(*) as count 
                 FROM roles_permisos rp 
                 JOIN permisos p ON rp.permiso_id = p.id 
                 WHERE rp.rol_id = :rol_id AND p.clave = :clave";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":rol_id", $this->id);
        $stmt->bindParam(":clave", $clave_permiso);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }
}
?>
