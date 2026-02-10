<?php

class EspacioEvento {
    private $conn;
    private $table_name = "espacios_eventos";
    
    public $id;
    public $nombre;
    public $descripcion;
    public $tipo_espacio;
    public $capacidad_maxima;
    public $precio_hora;
    public $precio_completo;
    public $ubicacion;
    public $caracteristicas;
    public $imagen_url;
    public $estado;
    public $activo;
    public $created_at;
    public $updated_at;
    public $deleted_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Crear espacio de evento
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                (nombre, descripcion, tipo_espacio, capacidad_maxima, precio_hora, precio_completo, ubicacion, caracteristicas, imagen_url, estado, activo) 
                VALUES (:nombre, :descripcion, :tipo_espacio, :capacidad_maxima, :precio_hora, :precio_completo, :ubicacion, :caracteristicas, :imagen_url, :estado, :activo)";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->tipo_espacio = htmlspecialchars(strip_tags($this->tipo_espacio));
        $this->ubicacion = htmlspecialchars(strip_tags($this->ubicacion));
        $this->caracteristicas = htmlspecialchars(strip_tags($this->caracteristicas));
        $this->imagen_url = htmlspecialchars(strip_tags($this->imagen_url));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        
        // Bind parameters
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":tipo_espacio", $this->tipo_espacio);
        $stmt->bindParam(":capacidad_maxima", $this->capacidad_maxima);
        $stmt->bindParam(":precio_hora", $this->precio_hora);
        $stmt->bindParam(":precio_completo", $this->precio_completo);
        $stmt->bindParam(":ubicacion", $this->ubicacion);
        $stmt->bindParam(":caracteristicas", $this->caracteristicas);
        $stmt->bindParam(":imagen_url", $this->imagen_url);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":activo", $this->activo);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Leer todos los espacios de eventos
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE deleted_at IS NULL 
                ORDER BY nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Leer un espacio de evento por ID
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
            $this->tipo_espacio = $row['tipo_espacio'];
            $this->capacidad_maxima = $row['capacidad_maxima'];
            $this->precio_hora = $row['precio_hora'];
            $this->precio_completo = $row['precio_completo'];
            $this->ubicacion = $row['ubicacion'];
            $this->caracteristicas = $row['caracteristicas'];
            $this->imagen_url = $row['imagen_url'];
            $this->estado = $row['estado'];
            $this->activo = $row['activo'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }
    
    // Actualizar espacio de evento
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                SET nombre = :nombre,
                    descripcion = :descripcion,
                    tipo_espacio = :tipo_espacio,
                    capacidad_maxima = :capacidad_maxima,
                    precio_hora = :precio_hora,
                    precio_completo = :precio_completo,
                    ubicacion = :ubicacion,
                    caracteristicas = :caracteristicas,
                    imagen_url = :imagen_url,
                    estado = :estado,
                    activo = :activo,
                    updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->tipo_espacio = htmlspecialchars(strip_tags($this->tipo_espacio));
        $this->ubicacion = htmlspecialchars(strip_tags($this->ubicacion));
        $this->caracteristicas = htmlspecialchars(strip_tags($this->caracteristicas));
        $this->imagen_url = htmlspecialchars(strip_tags($this->imagen_url));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind parameters
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":tipo_espacio", $this->tipo_espacio);
        $stmt->bindParam(":capacidad_maxima", $this->capacidad_maxima);
        $stmt->bindParam(":precio_hora", $this->precio_hora);
        $stmt->bindParam(":precio_completo", $this->precio_completo);
        $stmt->bindParam(":ubicacion", $this->ubicacion);
        $stmt->bindParam(":caracteristicas", $this->caracteristicas);
        $stmt->bindParam(":imagen_url", $this->imagen_url);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":activo", $this->activo);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Eliminar espacio de evento (soft delete)
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
    
    // Obtener espacios disponibles
    public function getAvailableSpaces() {
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE estado = 'disponible' AND activo = 1 AND deleted_at IS NULL 
                ORDER BY nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Actualizar estado del espacio
    public function updateEstado() {
        $query = "UPDATE " . $this->table_name . " 
                SET estado = :estado, 
                    updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
