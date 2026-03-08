<?php

class ReservaEvento {
    private $conn;
    private $table_name = "reservas_eventos";
    
    public $id;
    public $evento_id;
    public $cliente_id;
    public $fecha_reserva;
    public $cantidad_personas;
    public $precio_unitario;
    public $precio_total;
    public $estado;
    public $metodo_pago;
    public $notas;
    public $created_at;
    public $updated_at;
    public $deleted_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Crear reserva de evento
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                (evento_id, cliente_id, cantidad_personas, precio_unitario, precio_total, estado, metodo_pago, notas) 
                VALUES (:evento_id, :cliente_id, :cantidad_personas, :precio_unitario, :precio_total, :estado, :metodo_pago, :notas)";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->evento_id = htmlspecialchars(strip_tags($this->evento_id));
        $this->cliente_id = htmlspecialchars(strip_tags($this->cliente_id));
        $this->cantidad_personas = htmlspecialchars(strip_tags($this->cantidad_personas));
        $this->precio_unitario = htmlspecialchars(strip_tags($this->precio_unitario));
        $this->precio_total = htmlspecialchars(strip_tags($this->precio_total));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->metodo_pago = htmlspecialchars(strip_tags($this->metodo_pago));
        $this->notas = htmlspecialchars(strip_tags($this->notas));
        
        // Bind parameters
        $stmt->bindParam(":evento_id", $this->evento_id);
        $stmt->bindParam(":cliente_id", $this->cliente_id);
        $stmt->bindParam(":cantidad_personas", $this->cantidad_personas);
        $stmt->bindParam(":precio_unitario", $this->precio_unitario);
        $stmt->bindParam(":precio_total", $this->precio_total);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":metodo_pago", $this->metodo_pago);
        $stmt->bindParam(":notas", $this->notas);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Leer todas las reservas de eventos
    public function read() {
        $query = "SELECT re.*, e.nombre as nombre_evento, e.fecha_evento, e.tipo_evento,
                        c.nombre as nombre_cliente, c.apellido as apellido_cliente
                FROM " . $this->table_name . " re
                LEFT JOIN eventos e ON re.evento_id = e.id
                LEFT JOIN clientes c ON re.cliente_id = c.id
                WHERE re.deleted_at IS NULL 
                ORDER BY re.fecha_reserva DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Leer una reserva de evento por ID
    public function readOne() {
        $query = "SELECT re.*, e.nombre as nombre_evento, e.fecha_evento, e.tipo_evento,
                        c.nombre as nombre_cliente, c.apellido as apellido_cliente
                FROM " . $this->table_name . " re
                LEFT JOIN eventos e ON re.evento_id = e.id
                LEFT JOIN clientes c ON re.cliente_id = c.id
                WHERE re.id = ? AND re.deleted_at IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->evento_id = $row['evento_id'];
            $this->cliente_id = $row['cliente_id'];
            $this->fecha_reserva = $row['fecha_reserva'];
            $this->cantidad_personas = $row['cantidad_personas'];
            $this->precio_unitario = $row['precio_unitario'];
            $this->precio_total = $row['precio_total'];
            $this->estado = $row['estado'];
            $this->metodo_pago = $row['metodo_pago'];
            $this->notas = $row['notas'];
            
            return true;
        }
        
        return false;
    }
    
    // Actualizar reserva de evento
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                SET estado = :estado,
                    metodo_pago = :metodo_pago,
                    notas = :notas,
                    updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->metodo_pago = htmlspecialchars(strip_tags($this->metodo_pago));
        $this->notas = htmlspecialchars(strip_tags($this->notas));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind parameters
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":metodo_pago", $this->metodo_pago);
        $stmt->bindParam(":notas", $this->notas);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Eliminar reserva de evento (soft delete)
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
    
    // Obtener reservas por cliente
    public function getReservasByCliente($cliente_id) {
        $query = "SELECT re.*, e.nombre as nombre_evento, e.fecha_evento, e.tipo_evento
                FROM " . $this->table_name . " re
                LEFT JOIN eventos e ON re.evento_id = e.id
                WHERE re.cliente_id = ? AND re.deleted_at IS NULL 
                ORDER BY re.fecha_reserva DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $cliente_id);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Obtener reservas por evento
    public function getReservasByEvento($evento_id) {
        $query = "SELECT re.*, c.nombre as nombre_cliente, c.apellido as apellido_cliente
                FROM " . $this->table_name . " re
                LEFT JOIN clientes c ON re.cliente_id = c.id
                WHERE re.evento_id = ? AND re.deleted_at IS NULL 
                ORDER BY re.fecha_reserva DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $evento_id);
        $stmt->execute();
        
        return $stmt;
    }
}
?>
