<?php
class TransaccionContable {
    private $conn;
    private $table_name = "transacciones_contables";
    
    public $id;
    public $numero_comprobante;
    public $fecha;
    public $descripcion;
    public $tipo_transaccion;
    public $monto_total;
    public $usuario_id;
    public $referencia_tipo;
    public $referencia_id;
    public $estado;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Obtener transacciones con filtros
    public function getAll($fecha_inicio = null, $fecha_fin = null, $tipo = null, $estado = null) {
        $query = "SELECT tc.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido
                 FROM " . $this->table_name . " tc
                 JOIN usuarios u ON tc.usuario_id = u.id
                 WHERE 1=1";
        
        $params = [];
        
        if ($fecha_inicio) {
            $query .= " AND tc.fecha >= :fecha_inicio";
            $params[':fecha_inicio'] = $fecha_inicio;
        }
        
        if ($fecha_fin) {
            $query .= " AND tc.fecha <= :fecha_fin";
            $params[':fecha_fin'] = $fecha_fin;
        }
        
        if ($tipo) {
            $query .= " AND tc.tipo_transaccion = :tipo";
            $params[':tipo'] = $tipo;
        }
        
        if ($estado) {
            $query .= " AND tc.estado = :estado";
            $params[':estado'] = $estado;
        }
        
        $query .= " ORDER BY tc.fecha DESC, tc.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt;
    }
    
    // Crear transacción con detalles (partida doble)
    public function createWithDetalles($detalles) {
        try {
            $this->conn->beginTransaction();
            
            // Insertar transacción principal
            $query = "INSERT INTO " . $this->table_name . " 
                     (numero_comprobante, fecha, descripcion, tipo_transaccion, monto_total, usuario_id, referencia_tipo, referencia_id, estado) 
                     VALUES (:numero_comprobante, :fecha, :descripcion, :tipo_transaccion, :monto_total, :usuario_id, :referencia_tipo, :referencia_id, :estado)";
            
            $stmt = $this->conn->prepare($query);
            
            // Generar número de comprobante automático
            if (empty($this->numero_comprobante)) {
                $this->numero_comprobante = $this->generarNumeroComprobante();
            }
            
            $stmt->bindParam(":numero_comprobante", $this->numero_comprobante);
            $stmt->bindParam(":fecha", $this->fecha);
            $stmt->bindParam(":descripcion", $this->descripcion);
            $stmt->bindParam(":tipo_transaccion", $this->tipo_transaccion);
            $stmt->bindParam(":monto_total", $this->monto_total);
            $stmt->bindParam(":usuario_id", $this->usuario_id);
            $stmt->bindParam(":referencia_tipo", $this->referencia_tipo);
            $stmt->bindParam(":referencia_id", $this->referencia_id);
            $stmt->bindParam(":estado", $this->estado);
            
            if($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                
                // Insertar detalles
                $query_detalle = "INSERT INTO transaccion_detalles 
                                 (transaccion_id, cuenta_id, tipo_movimiento, monto, descripcion) 
                                 VALUES (:transaccion_id, :cuenta_id, :tipo_movimiento, :monto, :descripcion)";
                
                $stmt_detalle = $this->conn->prepare($query_detalle);
                
                foreach ($detalles as $detalle) {
                    $stmt_detalle->bindParam(":transaccion_id", $this->id);
                    $stmt_detalle->bindParam(":cuenta_id", $detalle['cuenta_id']);
                    $stmt_detalle->bindParam(":tipo_movimiento", $detalle['tipo_movimiento']);
                    $stmt_detalle->bindParam(":monto", $detalle['monto']);
                    $stmt_detalle->bindParam(":descripcion", $detalle['descripcion']);
                    $stmt_detalle->execute();
                }
                
                $this->conn->commit();
                return true;
            }
            
            $this->conn->rollback();
            return false;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error en transacción: " . $e->getMessage());
            return false;
        }
    }
    
    // Generar número de comprobante único
    private function generarNumeroComprobante() {
        $prefijo = date('Y-m');
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                 WHERE numero_comprobante LIKE :prefijo";
        
        $stmt = $this->conn->prepare($query);
        $prefijo_like = $prefijo . '%';
        $stmt->bindParam(":prefijo", $prefijo_like);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $secuencia = ($row['count'] ?? 0) + 1;
        
        return $prefijo . '-' . str_pad($secuencia, 4, '0', STR_PAD_LEFT);
    }
    
    // Confirmar transacción
    public function confirmar() {
        $query = "UPDATE " . $this->table_name . " 
                 SET estado = 'confirmada', updated_at = NOW() 
                 WHERE id = :id AND estado = 'borrador'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            $this->estado = 'confirmada';
            return true;
        }
        return false;
    }
    
    // Obtener detalles de una transacción
    public function getDetalles() {
        $query = "SELECT td.*, c.codigo, c.nombre as cuenta_nombre
                 FROM transaccion_detalles td
                 JOIN cuentas_contables c ON td.cuenta_id = c.id
                 WHERE td.transaccion_id = :id
                 ORDER BY td.id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        return $stmt;
    }
    
    // Obtener resumen financiero
    public function getResumenFinanciero($fecha_inicio, $fecha_fin) {
        $query = "SELECT 
                 SUM(CASE WHEN tipo_transaccion = 'ingreso' AND estado = 'confirmada' THEN monto_total ELSE 0 END) as total_ingresos,
                 SUM(CASE WHEN tipo_transaccion = 'egreso' AND estado = 'confirmada' THEN monto_total ELSE 0 END) as total_egresos,
                 COUNT(CASE WHEN estado = 'confirmada' THEN 1 END) as transacciones_confirmadas,
                 COUNT(CASE WHEN estado = 'borrador' THEN 1 END) as transacciones_borrador
                 FROM " . $this->table_name . "
                 WHERE fecha BETWEEN :fecha_inicio AND :fecha_fin";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fecha_inicio", $fecha_inicio);
        $stmt->bindParam(":fecha_fin", $fecha_fin);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
