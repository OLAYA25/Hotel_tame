<?php
class Producto {
    private $conn;
    private $table_name = "productos";

    public $id;
    public $nombre;
    public $descripcion;
    public $categoria;
    public $precio;
    public $imagen_url;
    public $stock;
    public $activo;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los productos activos
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE deleted_at IS NULL AND activo = TRUE 
                  ORDER BY categoria ASC, nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener todos los productos (incluyendo inactivos)
    public function getAllWithInactive() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE deleted_at IS NULL 
                  ORDER BY categoria ASC, nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener producto por ID
    public function getById() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE id = ? AND deleted_at IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch();
        if($row) {
            $this->nombre = $row['nombre'];
            $this->descripcion = $row['descripcion'];
            $this->categoria = $row['categoria'];
            $this->precio = $row['precio'];
            $this->imagen_url = $row['imagen_url'];
            $this->stock = $row['stock'];
            $this->activo = $row['activo'];
            return true;
        }
        return false;
    }

    // Obtener productos por categoría
    public function getByCategory($categoria) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE categoria = ? AND deleted_at IS NULL AND activo = TRUE 
                  ORDER BY nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $categoria);
        $stmt->execute();
        return $stmt;
    }

    // Crear producto
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nombre, descripcion, categoria, precio, imagen_url, stock, activo) 
                  VALUES (:nombre, :descripcion, :categoria, :precio, :imagen_url, :stock, :activo)";
        
        $stmt = $this->conn->prepare($query);
        
        // Validar categoría
        $categorias_permitidas = ['comida', 'bebida', 'snack', 'higiene', 'otros'];
        if (!in_array($this->categoria, $categorias_permitidas)) {
            $this->categoria = 'otros';
        }
        
        // Limpiar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->imagen_url = htmlspecialchars(strip_tags($this->imagen_url));
        
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":categoria", $this->categoria);
        $stmt->bindParam(":precio", $this->precio);
        $stmt->bindParam(":imagen_url", $this->imagen_url);
        $stmt->bindParam(":stock", $this->stock);
        $stmt->bindParam(":activo", $this->activo);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Actualizar producto
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre = :nombre, 
                      descripcion = :descripcion, 
                      categoria = :categoria, 
                      precio = :precio, 
                      imagen_url = :imagen_url, 
                      stock = :stock, 
                      activo = :activo,
                      updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Validar categoría
        $categorias_permitidas = ['comida', 'bebida', 'snack', 'higiene', 'otros'];
        if (!in_array($this->categoria, $categorias_permitidas)) {
            $this->categoria = 'otros';
        }
        
        // Limpiar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre ?? ""));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion ?? ""));
        $this->imagen_url = htmlspecialchars(strip_tags($this->imagen_url ?? ""));
        $this->id = htmlspecialchars(strip_tags($this->id ?? ""));
        
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":categoria", $this->categoria);
        $stmt->bindParam(":precio", $this->precio);
        $stmt->bindParam(":imagen_url", $this->imagen_url);
        $stmt->bindParam(":stock", $this->stock);
        $stmt->bindParam(":activo", $this->activo);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Eliminar producto (soft delete)
    public function delete() {
        $query = "UPDATE " . $this->table_name . " 
                  SET deleted_at = NOW() 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Actualizar stock
    public function updateStock($cantidad) {
        $query = "UPDATE " . $this->table_name . " 
                  SET stock = stock + ?, updated_at = NOW() 
                  WHERE id = ? AND deleted_at IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $cantidad);
        $stmt->bindParam(2, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Verificar stock disponible
    public function checkStock($cantidad) {
        $query = "SELECT stock FROM " . $this->table_name . " 
                  WHERE id = ? AND deleted_at IS NULL AND activo = TRUE";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch();
        return $row && $row['stock'] >= $cantidad;
    }
}
?>
