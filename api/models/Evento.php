<?php

class Evento {
    private $conn;
    private $table_name = "eventos";
    
    public $id;
    public $nombre;
    public $descripcion;
    public $tipo_evento;
    public $capacidad_maxima;
    public $precio_por_persona;
    public $precio_total;
    public $fecha_evento;
    public $hora_inicio;
    public $hora_fin;
    public $imagen_url;
    public $estado;
    public $activo;
    public $created_at;
    public $updated_at;
    public $deleted_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Crear evento
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                (nombre, descripcion, tipo_evento, capacidad_maxima, precio_por_persona, precio_total, fecha_evento, hora_inicio, hora_fin, imagen_url, estado, activo) 
                VALUES (:nombre, :descripcion, :tipo_evento, :capacidad_maxima, :precio_por_persona, :precio_total, :fecha_evento, :hora_inicio, :hora_fin, :imagen_url, :estado, :activo)";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->tipo_evento = htmlspecialchars(strip_tags($this->tipo_evento));
        $this->imagen_url = htmlspecialchars(strip_tags($this->imagen_url));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        
        // Bind parameters
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":tipo_evento", $this->tipo_evento);
        $stmt->bindParam(":capacidad_maxima", $this->capacidad_maxima);
        $stmt->bindParam(":precio_por_persona", $this->precio_por_persona);
        $stmt->bindParam(":precio_total", $this->precio_total);
        $stmt->bindParam(":fecha_evento", $this->fecha_evento);
        $stmt->bindParam(":hora_inicio", $this->hora_inicio);
        $stmt->bindParam(":hora_fin", $this->hora_fin);
        $stmt->bindParam(":imagen_url", $this->imagen_url);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":activo", $this->activo);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Leer todos los eventos
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE deleted_at IS NULL 
                ORDER BY fecha_evento ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Leer un evento por ID
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE id = ? AND deleted_at IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->nombre = $row['nombre'];
            $this->descripcion = $row['descripcion'];
            $this->tipo_evento = $row['tipo_evento'];
            $this->capacidad_maxima = $row['capacidad_maxima'];
            $this->precio_por_persona = $row['precio_por_persona'];
            $this->precio_total = $row['precio_total'];
            $this->fecha_evento = $row['fecha_evento'];
            $this->hora_inicio = $row['hora_inicio'];
            $this->hora_fin = $row['hora_fin'];
            $this->imagen_url = $row['imagen_url'];
            $this->estado = $row['estado'];
            $this->activo = $row['activo'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }
    
    // Actualizar evento
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                SET nombre = :nombre,
                    descripcion = :descripcion,
                    tipo_evento = :tipo_evento,
                    capacidad_maxima = :capacidad_maxima,
                    precio_por_persona = :precio_por_persona,
                    precio_total = :precio_total,
                    fecha_evento = :fecha_evento,
                    hora_inicio = :hora_inicio,
                    hora_fin = :hora_fin,
                    imagen_url = :imagen_url,
                    estado = :estado,
                    activo = :activo,
                    updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->tipo_evento = htmlspecialchars(strip_tags($this->tipo_evento));
        $this->imagen_url = htmlspecialchars(strip_tags($this->imagen_url));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind parameters
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":tipo_evento", $this->tipo_evento);
        $stmt->bindParam(":capacidad_maxima", $this->capacidad_maxima);
        $stmt->bindParam(":precio_por_persona", $this->precio_por_persona);
        $stmt->bindParam(":precio_total", $this->precio_total);
        $stmt->bindParam(":fecha_evento", $this->fecha_evento);
        $stmt->bindParam(":hora_inicio", $this->hora_inicio);
        $stmt->bindParam(":hora_fin", $this->hora_fin);
        $stmt->bindParam(":imagen_url", $this->imagen_url);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":activo", $this->activo);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Eliminar evento (soft delete)
    public function delete() {
        $query = "UPDATE " . $this->table_name . " 
                SET deleted_at = NOW() 
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Obtener eventos disponibles
    public function getAvailableEvents() {
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE estado = 'disponible' AND activo = 1 AND deleted_at IS NULL 
                AND fecha_evento >= CURDATE()
                ORDER BY fecha_evento ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
}
?>
