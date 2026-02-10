<?php
class TipoTurno {
    private $conn;
    private $table_name = "tipos_turno";
    
    public $id;
    public $nombre;
    public $hora_inicio;
    public $hora_fin;
    public $descripcion;
    public $color;
    public $activo;
    public $created_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Obtener todos los tipos de turno
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE activo = TRUE 
                 ORDER BY hora_inicio, nombre";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    // Obtener tipos de turno activos (método alias para getAll)
    public function getActivos() {
        return $this->getAll();
    }
    
    // Obtener tipo de turno por ID
    public function getById() {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE id = :id AND activo = TRUE";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->nombre = $row['nombre'];
            $this->hora_inicio = $row['hora_inicio'];
            $this->hora_fin = $row['hora_fin'];
            $this->descripcion = $row['descripcion'];
            $this->color = $row['color'];
            $this->activo = $row['activo'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }
    
    // Crear tipo de turno
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (nombre, hora_inicio, hora_fin, descripcion, color, activo) 
                 VALUES (:nombre, :hora_inicio, :hora_fin, :descripcion, :color, :activo)";
        
        $stmt = $this->conn->prepare($query);
        
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->hora_inicio = htmlspecialchars(strip_tags($this->hora_inicio));
        $this->hora_fin = htmlspecialchars(strip_tags($this->hora_fin));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->color = htmlspecialchars(strip_tags($this->color));
        
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":hora_inicio", $this->hora_inicio);
        $stmt->bindParam(":hora_fin", $this->hora_fin);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":color", $this->color);
        $stmt->bindParam(":activo", $this->activo);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
    
    // Actualizar tipo de turno
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET nombre = :nombre, 
                     hora_inicio = :hora_inicio, 
                     hora_fin = :hora_fin, 
                     descripcion = :descripcion, 
                     color = :color, 
                     activo = :activo
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->hora_inicio = htmlspecialchars(strip_tags($this->hora_inicio));
        $this->hora_fin = htmlspecialchars(strip_tags($this->hora_fin));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->color = htmlspecialchars(strip_tags($this->color));
        
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":hora_inicio", $this->hora_inicio);
        $stmt->bindParam(":hora_fin", $this->hora_fin);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":color", $this->color);
        $stmt->bindParam(":activo", $this->activo);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Eliminar tipo de turno (desactivar)
    public function delete() {
        $query = "UPDATE " . $this->table_name . " 
                 SET activo = FALSE 
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
