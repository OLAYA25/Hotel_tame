<?php
class CuentaContable {
    private $conn;
    private $table_name = "cuentas_contables";
    
    public $id;
    public $codigo;
    public $nombre;
    public $tipo;
    public $nivel;
    public $cuenta_padre_id;
    public $descripcion;
    public $activa;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Obtener todas las cuentas activas
    public function getAll() {
        $query = "SELECT c.*, 
                 (SELECT nombre FROM cuentas_contables WHERE id = c.cuenta_padre_id) as cuenta_padre_nombre
                 FROM " . $this->table_name . " c 
                 WHERE c.activa = TRUE 
                 ORDER BY c.codigo, c.nombre";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    // Obtener cuenta por ID
    public function getById() {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE id = :id AND activa = TRUE";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->codigo = $row['codigo'];
            $this->nombre = $row['nombre'];
            $this->tipo = $row['tipo'];
            $this->nivel = $row['nivel'];
            $this->cuenta_padre_id = $row['cuenta_padre_id'];
            $this->descripcion = $row['descripcion'];
            $this->activa = $row['activa'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }
    
    // Obtener cuentas por tipo
    public function getByTipo($tipo) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE tipo = :tipo AND activa = TRUE 
                 ORDER BY codigo, nombre";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":tipo", $tipo);
        $stmt->execute();
        return $stmt;
    }
    
    // Crear cuenta
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (codigo, nombre, tipo, nivel, cuenta_padre_id, descripcion) 
                 VALUES (:codigo, :nombre, :tipo, :nivel, :cuenta_padre_id, :descripcion)";
        
        $stmt = $this->conn->prepare($query);
        
        $this->codigo = htmlspecialchars(strip_tags($this->codigo));
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        
        $stmt->bindParam(":codigo", $this->codigo);
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":tipo", $this->tipo);
        $stmt->bindParam(":nivel", $this->nivel);
        $stmt->bindParam(":cuenta_padre_id", $this->cuenta_padre_id);
        $stmt->bindParam(":descripcion", $this->descripcion);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
    
    // Obtener balance de comprobación
    public function getBalanceComprobacion($fecha_inicio, $fecha_fin) {
        $query = "SELECT c.codigo, c.nombre, c.tipo,
                 COALESCE(SUM(CASE WHEN td.tipo_movimiento = 'debe' THEN td.monto ELSE 0 END), 0) as total_debe,
                 COALESCE(SUM(CASE WHEN td.tipo_movimiento = 'haber' THEN td.monto ELSE 0 END), 0) as total_haber
                 FROM cuentas_contables c
                 LEFT JOIN transaccion_detalles td ON c.id = td.cuenta_id
                 LEFT JOIN transacciones_contables tc ON td.transaccion_id = tc.id
                 WHERE c.activa = TRUE 
                 AND (tc.fecha BETWEEN :fecha_inicio AND :fecha_fin OR tc.fecha IS NULL)
                 GROUP BY c.id, c.codigo, c.nombre, c.tipo
                 ORDER BY c.codigo";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fecha_inicio", $fecha_inicio);
        $stmt->bindParam(":fecha_fin", $fecha_fin);
        $stmt->execute();
        return $stmt;
    }
}
?>
