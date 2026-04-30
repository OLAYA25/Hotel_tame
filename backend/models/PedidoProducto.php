<?php
class PedidoProducto {
    private $conn;
    private $table_name = "pedidos_productos";

    public $id;
    public $habitacion_id;
    public $cliente_id;
    public $usuario_id;
    public $estado;
    public $subtotal;
    public $total;
    public $notas;
    public $fecha_pedido;
    public $fecha_entrega;
    public $habitacion_numero;
    public $cliente_nombre;
    public $usuario_nombre;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Crear pedido
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (habitacion_id, cliente_id, usuario_id, estado, subtotal, total, notas) 
                  VALUES (:habitacion_id, :cliente_id, :usuario_id, :estado, :subtotal, :total, :notas)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":habitacion_id", $this->habitacion_id);
        $stmt->bindParam(":cliente_id", $this->cliente_id);
        $stmt->bindParam(":usuario_id", $this->usuario_id);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":subtotal", $this->subtotal);
        $stmt->bindParam(":total", $this->total);
        $stmt->bindParam(":notas", $this->notas);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Obtener todos los pedidos
    public function getAll() {
        $query = "SELECT p.*, h.numero as habitacion_numero, 
                         CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre,
                         CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre
                  FROM " . $this->table_name . " p
                  LEFT JOIN habitaciones h ON p.habitacion_id = h.id
                  LEFT JOIN clientes c ON p.cliente_id = c.id
                  LEFT JOIN usuarios u ON p.usuario_id = u.id
                  WHERE p.deleted_at IS NULL 
                  ORDER BY p.fecha_pedido DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener pedidos con paginación y filtros
    public function getAllWithPagination($limit = 10, $offset = 0, $estado = '', $fecha = '', $busqueda = '') {
        $query = "SELECT p.*, h.numero as habitacion_numero, 
                         CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre,
                         CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre
                  FROM " . $this->table_name . " p
                  LEFT JOIN habitaciones h ON p.habitacion_id = h.id
                  LEFT JOIN clientes c ON p.cliente_id = c.id
                  LEFT JOIN usuarios u ON p.usuario_id = u.id
                  WHERE p.deleted_at IS NULL";
        
        $params = array();
        
        // Agregar filtros
        if (!empty($estado)) {
            $query .= " AND p.estado = ?";
            $params[] = $estado;
        }
        
        if (!empty($fecha)) {
            $query .= " AND DATE(p.fecha_pedido) = ?";
            $params[] = $fecha;
        }
        
        if (!empty($busqueda)) {
            $query .= " AND (h.numero LIKE ? OR CONCAT(c.nombre, ' ', c.apellido) LIKE ? OR c.nombre LIKE ? OR c.apellido LIKE ?)";
            $searchParam = "%{$busqueda}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        $query .= " ORDER BY p.fecha_pedido DESC 
                   LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        
        $stmt->execute();
        return $stmt;
    }

    // Obtener conteo total para paginación
    public function getTotalCount($estado = '', $fecha = '', $busqueda = '') {
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table_name . " p
                  LEFT JOIN habitaciones h ON p.habitacion_id = h.id
                  LEFT JOIN clientes c ON p.cliente_id = c.id
                  LEFT JOIN usuarios u ON p.usuario_id = u.id
                  WHERE p.deleted_at IS NULL";
        
        $params = array();
        
        // Agregar filtros
        if (!empty($estado)) {
            $query .= " AND p.estado = ?";
            $params[] = $estado;
        }
        
        if (!empty($fecha)) {
            $query .= " AND DATE(p.fecha_pedido) = ?";
            $params[] = $fecha;
        }
        
        if (!empty($busqueda)) {
            $query .= " AND (h.numero LIKE ? OR CONCAT(c.nombre, ' ', c.apellido) LIKE ? OR c.nombre LIKE ? OR c.apellido LIKE ?)";
            $searchParam = "%{$busqueda}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        
        $stmt->execute();
        return $stmt;
    }

    // Obtener pedido por ID
    public function getById() {
        $query = "SELECT p.*, h.numero as habitacion_numero, 
                         CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre,
                         CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre
                  FROM " . $this->table_name . " p
                  LEFT JOIN habitaciones h ON p.habitacion_id = h.id
                  LEFT JOIN clientes c ON p.cliente_id = c.id
                  LEFT JOIN usuarios u ON p.usuario_id = u.id
                  WHERE p.id = ? AND p.deleted_at IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch();
        if($row) {
            $this->habitacion_id = $row['habitacion_id'];
            $this->cliente_id = $row['cliente_id'];
            $this->usuario_id = $row['usuario_id'];
            $this->estado = $row['estado'];
            $this->subtotal = $row['subtotal'];
            $this->total = $row['total'];
            $this->notas = $row['notas'];
            $this->fecha_pedido = $row['fecha_pedido'];
            $this->fecha_entrega = $row['fecha_entrega'];
            $this->habitacion_numero = $row['habitacion_numero'];
            $this->cliente_nombre = $row['cliente_nombre'];
            $this->usuario_nombre = $row['usuario_nombre'];
            return true;
        }
        return false;
    }

    // Actualizar estado del pedido
    public function updateEstado() {
        $query = "UPDATE " . $this->table_name . " 
                  SET estado = :estado, 
                      fecha_entrega = :fecha_entrega,
                      updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":fecha_entrega", $this->fecha_entrega);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Eliminar pedido (soft delete)
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

    // Agregar detalle al pedido
    public function agregarDetalle($pedido_id, $producto_id, $cantidad, $precio_unitario, $cliente_id = null) {
        $query = "INSERT INTO pedido_productos_detalles 
                  (pedido_id, producto_id, cliente_id, cantidad, precio_unitario, subtotal) 
                  VALUES (:pedido_id, :producto_id, :cliente_id, :cantidad, :precio_unitario, :subtotal)";
        
        $stmt = $this->conn->prepare($query);
        
        $subtotal = $cantidad * $precio_unitario;
        
        $stmt->bindParam(":pedido_id", $pedido_id);
        $stmt->bindParam(":producto_id", $producto_id);
        $stmt->bindParam(":cliente_id", $cliente_id);
        $stmt->bindParam(":cantidad", $cantidad);
        $stmt->bindParam(":precio_unitario", $precio_unitario);
        $stmt->bindParam(":subtotal", $subtotal);
        
        return $stmt->execute();
    }

    // Obtener detalles de un pedido
    public function getDetalles($pedido_id) {
        $query = "SELECT d.*, p.nombre as producto_nombre, p.categoria,
                         c.nombre as cliente_nombre, c.apellido as cliente_apellido
                  FROM pedido_productos_detalles d
                  JOIN productos p ON d.producto_id = p.id
                  LEFT JOIN clientes c ON d.cliente_id = c.id
                  WHERE d.pedido_id = ?
                  ORDER BY d.created_at";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $pedido_id);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Obtener pedidos por reserva_id (buscando por habitacion_id)
    public function getByReservaId($habitacion_id) {
        $query = "SELECT pp.*, h.numero as habitacion_numero, 
                         c.nombre as cliente_nombre, c.apellido as cliente_apellido,
                         u.nombre as usuario_nombre
                  FROM pedidos_productos pp
                  LEFT JOIN habitaciones h ON pp.habitacion_id = h.id
                  LEFT JOIN clientes c ON pp.cliente_id = c.id
                  LEFT JOIN usuarios u ON pp.usuario_id = u.id
                  WHERE pp.habitacion_id = ?
                  ORDER BY pp.fecha_pedido DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $habitacion_id);
        $stmt->execute();
        
        return $stmt;
    }
}
?>
