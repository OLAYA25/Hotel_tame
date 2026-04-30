<?php
class Habitacion {
    private $conn;
    private $table_name = "habitaciones";

    public $id;
    public $numero;
    public $tipo;
    public $precio_noche;
    public $estado;
    public $piso;
    public $capacidad;
    public $descripcion;
    public $imagen_url;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todas las habitaciones
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE deleted_at IS NULL 
                  ORDER BY numero ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener habitación por ID
    public function getById() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE id = ? AND deleted_at IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch();
        if($row) {
            $this->numero = $row['numero'];
            $this->tipo = $row['tipo'];
            // compatibilidad: la columna en la BD puede llamarse 'precio' o 'precio_noche'
            $this->precio_noche = $row['precio'] ?? $row['precio_noche'] ?? null;
            $this->estado = $row['estado'];
            $this->piso = $row['piso'];
            $this->capacidad = $row['capacidad'];
            $this->descripcion = $row['descripcion'];
            return true;
        }
        return false;
    }

    // Obtener habitaciones con estado real según fechas actuales
    public function getConEstadoReal() {
        $query = "SELECT h.*, 
                         CASE 
                             WHEN h.estado = 'mantenimiento' THEN 'mantenimiento'
                             WHEN r_confirmada.habitacion_id IS NOT NULL THEN 'ocupada'
                             WHEN r_futura.habitacion_id IS NOT NULL THEN 'disponible'
                             ELSE 'disponible'
                         END as estado_real
                  FROM " . $this->table_name . " h
                  LEFT JOIN (
                      SELECT DISTINCT habitacion_id 
                      FROM reservas 
                      WHERE estado = 'confirmada' 
                      AND deleted_at IS NULL
                      AND fecha_entrada <= CURDATE() 
                      AND fecha_salida >= CURDATE()
                  ) r_confirmada ON h.id = r_confirmada.habitacion_id
                  LEFT JOIN (
                      SELECT DISTINCT habitacion_id 
                      FROM reservas 
                      WHERE estado = 'confirmada' 
                      AND deleted_at IS NULL
                      AND fecha_entrada > CURDATE()
                  ) r_futura ON h.id = r_futura.habitacion_id
                  WHERE h.deleted_at IS NULL 
                  ORDER BY h.numero ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener habitaciones disponibles
    public function getDisponibles() {
        // Modificado para incluir habitaciones que solo tienen reservas pendientes
        $query = "SELECT h.*, 
                         CASE 
                             WHEN r_pendientes.habitacion_id IS NOT NULL THEN 'disponible'
                             ELSE h.estado
                         END as estado_real
                  FROM " . $this->table_name . " h
                  LEFT JOIN (
                      SELECT DISTINCT habitacion_id 
                      FROM reservas 
                      WHERE estado = 'confirmada' 
                      AND deleted_at IS NULL
                      AND (
                          (fecha_entrada <= CURDATE() AND fecha_salida > CURDATE())
                          OR 
                          (fecha_entrada > CURDATE())
                      )
                  ) r_confirmadas ON h.id = r_confirmadas.habitacion_id
                  LEFT JOIN (
                      SELECT DISTINCT habitacion_id 
                      FROM reservas 
                      WHERE estado = 'pendiente' 
                      AND deleted_at IS NULL
                      AND fecha_entrada >= CURDATE()
                  ) r_pendientes ON h.id = r_pendientes.habitacion_id
                  WHERE h.deleted_at IS NULL 
                  AND h.estado != 'mantenimiento'
                  AND r_confirmadas.habitacion_id IS NULL
                  ORDER BY h.precio_noche ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Crear habitación
    public function create() {
        // Primero verificar si ya existe una habitación con ese número
        $check_query = "SELECT id FROM " . $this->table_name . " 
                       WHERE numero = :numero AND deleted_at IS NULL";
        
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(":numero", $this->numero);
        $check_stmt->execute();
        
        if($check_stmt->rowCount() > 0) {
            // Ya existe una habitación con ese número
            return false;
        }
        
        // insertar en la columna `precio_noche` (coincide con el esquema SQL)
        $query = "INSERT INTO " . $this->table_name . " 
                  (numero, tipo, precio_noche, estado, piso, capacidad, descripcion, imagen_url) 
                  VALUES (:numero, :tipo, :precio, :estado, :piso, :capacidad, :descripcion, :imagen_url)";
        
        $stmt = $this->conn->prepare($query);
        
        // Validar que el tipo sea uno de los valores permitidos
        $tipos_permitidos = ['simple', 'doble', 'suite', 'presidencial'];
        if (!in_array($this->tipo, $tipos_permitidos)) {
            $this->tipo = 'simple'; // valor por defecto
        }
        
        $this->numero = htmlspecialchars(strip_tags($this->numero));
        $this->tipo = htmlspecialchars(strip_tags($this->tipo));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->imagen_url = htmlspecialchars(strip_tags($this->imagen_url));
        
        $stmt->bindParam(":numero", $this->numero);
        $stmt->bindParam(":tipo", $this->tipo);
        $stmt->bindParam(":precio", $this->precio_noche);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":piso", $this->piso);
        $stmt->bindParam(":capacidad", $this->capacidad);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":imagen_url", $this->imagen_url);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Actualizar habitación
    public function update() {
        // actualizar la columna `precio_noche`
        $query = "UPDATE " . $this->table_name . " 
                  SET numero = :numero, 
                      tipo = :tipo, 
                      precio_noche = :precio, 
                      estado = :estado, 
                      piso = :piso, 
                      capacidad = :capacidad, 
                      descripcion = :descripcion,
                      imagen_url = :imagen_url,
                      updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Validar que el tipo sea uno de los valores permitidos
        $tipos_permitidos = ['simple', 'doble', 'suite', 'presidencial'];
        if (!in_array($this->tipo, $tipos_permitidos)) {
            $this->tipo = 'simple'; // valor por defecto
        }
        
        $this->numero = htmlspecialchars(strip_tags($this->numero ?? ""));
        $this->tipo = htmlspecialchars(strip_tags($this->tipo ?? ""));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion ?? ""));
        $this->imagen_url = htmlspecialchars(strip_tags($this->imagen_url ?? ""));
        $this->id = htmlspecialchars(strip_tags($this->id ?? ""));
        
        $stmt->bindParam(":numero", $this->numero);
        $stmt->bindParam(":tipo", $this->tipo);
        $stmt->bindParam(":precio", $this->precio_noche);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":piso", $this->piso);
        $stmt->bindParam(":capacidad", $this->capacidad);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":imagen_url", $this->imagen_url);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Eliminar habitación (soft delete)
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

    // Cambiar estado de habitación
    public function cambiarEstado() {
        $query = "UPDATE " . $this->table_name . " 
                  SET estado = :estado, 
                      updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
