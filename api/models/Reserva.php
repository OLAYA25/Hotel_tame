<?php
class Reserva {
    private $conn;
    private $table_name = "reservas";

    public $id;
    public $cliente_id;
    public $habitacion_id;
    public $fecha_entrada;
    public $fecha_salida;
    public $estado;
    public $total;
    public $metodo_pago;
    public $noches;
    public $precio_noche;
    public $numero_huespedes;
    public $num_huespedes; // Campo real de la BD
    public $notas;
    public $created_at;
    public $updated_at;
    public $deleted_at;

    // Estados válidos para una reserva
    private $estados_validos = ['pendiente', 'confirmada', 'cancelada', 'completada'];

    public function __construct($db) {
        $this->conn = $db;
    }

    // Validar datos de la reserva
    public function validar() {
        $errores = [];
        
        // Validar fechas
        if (empty($this->fecha_entrada) || empty($this->fecha_salida)) {
            $errores[] = "Las fechas de entrada y salida son obligatorias";
        } elseif (strtotime($this->fecha_entrada) >= strtotime($this->fecha_salida)) {
            $errores[] = "La fecha de entrada debe ser anterior a la fecha de salida";
        } elseif (strtotime($this->fecha_entrada) < strtotime(date('Y-m-d', strtotime('-90 days')))) {
            $errores[] = "La fecha de entrada no puede ser anterior a 90 días";
        }
        
        // Validar cliente y habitación
        if (empty($this->cliente_id) || !is_numeric($this->cliente_id)) {
            $errores[] = "El cliente es obligatorio";
        }
        
        if (empty($this->habitacion_id) || !is_numeric($this->habitacion_id)) {
            $errores[] = "La habitación es obligatoria";
        }
        
        // Validar estado
        if (!empty($this->estado) && !in_array($this->estado, $this->estados_validos)) {
            $errores[] = "El estado no es válido";
        }
        
        // Validar precios
        if (!empty($this->precio_noche) && (!is_numeric($this->precio_noche) || $this->precio_noche <= 0)) {
            $errores[] = "El precio por noche debe ser un número positivo";
        }
        
        if (!empty($this->total) && (!is_numeric($this->total) || $this->total < 0)) {
            $errores[] = "El total debe ser un número positivo o cero";
        }
        
        // Validar método de pago
        $metodos_validos = ['efectivo', 'tarjeta', 'transferencia', 'paypal', 'otro'];
        if (!empty($this->metodo_pago) && !in_array($this->metodo_pago, $metodos_validos)) {
            $errores[] = "El método de pago no es válido";
        }
        
        // Validar capacidad de la habitación
        if (!empty($this->habitacion_id) && !empty($this->numero_huespedes)) {
            try {
                $query = "SELECT capacidad FROM habitaciones WHERE id = :habitacion_id AND deleted_at IS NULL";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':habitacion_id', $this->habitacion_id);
                $stmt->execute();
                $habitacion = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($habitacion && $this->numero_huespedes > $habitacion['capacidad']) {
                    $errores[] = "El número de huéspedes ({$this->numero_huespedes}) excede la capacidad máxima de la habitación ({$habitacion['capacidad']})";
                }
            } catch (Exception $e) {
                $errores[] = "Error al validar la capacidad de la habitación";
            }
        }
        
        return $errores;
    }

    // Calcular automáticamente noches y total
    public function calcularTotales() {
        if (!empty($this->fecha_entrada) && !empty($this->fecha_salida)) {
            $entrada = new DateTime($this->fecha_entrada);
            $salida = new DateTime($this->fecha_salida);
            $this->noches = $entrada->diff($salida)->days;
            
            if (!empty($this->precio_noche) && is_numeric($this->precio_noche)) {
                $this->total = $this->noches * $this->precio_noche;
            }
        }
    }

    // Obtener todas las reservas con información de cliente y habitación
    public function getAll($fecha_inicio = null, $fecha_fin = null, $estado = null, $cliente_id = null) {
        $query = "SELECT r.*, 
                    r.precio_total AS total,
                    r.num_noches AS noches,
                    c.nombre as cliente_nombre, 
                    c.email as cliente_email, 
                    c.telefono as cliente_telefono,
                    h.numero as habitacion_numero, 
                    h.tipo as habitacion_tipo,
                    h.precio_noche as habitacion_precio
                FROM " . $this->table_name . " r
                LEFT JOIN clientes c ON r.cliente_id = c.id
                LEFT JOIN habitaciones h ON r.habitacion_id = h.id
                WHERE r.deleted_at IS NULL";
        
        $params = [];
        
        if ($fecha_inicio) {
            $query .= " AND r.fecha_entrada >= :fecha_inicio";
            $params[':fecha_inicio'] = $fecha_inicio;
        }
        
        if ($fecha_fin) {
            $query .= " AND r.fecha_salida <= :fecha_fin";
            $params[':fecha_fin'] = $fecha_fin;
        }
        
        if ($estado) {
            $query .= " AND r.estado = :estado";
            $params[':estado'] = $estado;
        }
        
        if ($cliente_id) {
            $query .= " AND r.cliente_id = :cliente_id";
            $params[':cliente_id'] = $cliente_id;
        }
        
        $query .= " ORDER BY r.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt;
    }

    // Obtener reserva por ID
    public function getById() {
        $query = "SELECT r.*, 
                    r.precio_total AS total,
                    r.num_noches AS noches,
                    c.nombre as cliente_nombre, 
                    c.email as cliente_email,
                    c.telefono as cliente_telefono,
                    h.numero as habitacion_numero, 
                    h.tipo as habitacion_tipo,
                    h.precio_noche as habitacion_precio
                FROM " . $this->table_name . " r
                LEFT JOIN clientes c ON r.cliente_id = c.id
                LEFT JOIN habitaciones h ON r.habitacion_id = h.id
                WHERE r.id = :id AND r.deleted_at IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->cliente_id = $row['cliente_id'];
            $this->habitacion_id = $row['habitacion_id'];
            $this->fecha_entrada = $row['fecha_entrada'];
            $this->fecha_salida = $row['fecha_salida'];
            $this->estado = $row['estado'];
            $this->total = $row['total'] ?? $row['precio_total'] ?? null;
            $this->metodo_pago = $row['metodo_pago'];
            $this->noches = $row['noches'] ?? $row['num_noches'] ?? null;
            $this->precio_noche = $row['precio_noche'] ?? $row['habitacion_precio'] ?? null;
            $this->num_huespedes = $row['num_huespedes'];
            $this->numero_huespedes = $row['num_huespedes']; // Para compatibilidad
            $this->notas = $row['notas'] ?? null;
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    // Obtener reservas por cliente
    public function getByCliente($cliente_id, $limit = null) {
        $query = "SELECT r.*, 
                    r.precio_total AS total,
                    r.num_noches AS noches,
                    h.numero as habitacion_numero, 
                    h.tipo as habitacion_tipo
                FROM " . $this->table_name . " r
                LEFT JOIN habitaciones h ON r.habitacion_id = h.id
                WHERE r.cliente_id = :cliente_id AND r.deleted_at IS NULL 
                ORDER BY r.created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cliente_id", $cliente_id);
        
        if ($limit) {
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt;
    }

    // Obtener reservas por habitación
    public function getByHabitacion($habitacion_id, $fecha_inicio = null, $fecha_fin = null) {
        $query = "SELECT r.*, 
                    r.precio_total AS total,
                    r.num_noches AS noches,
                    c.nombre as cliente_nombre, 
                    c.email as cliente_email
                FROM " . $this->table_name . " r
                LEFT JOIN clientes c ON r.cliente_id = c.id
                WHERE r.habitacion_id = :habitacion_id AND r.deleted_at IS NULL
                AND r.estado IN ('confirmada', 'pendiente')";
        
        $params = [':habitacion_id' => $habitacion_id];
        
        if ($fecha_inicio) {
            $query .= " AND r.fecha_entrada >= :fecha_inicio";
            $params[':fecha_inicio'] = $fecha_inicio;
        }
        
        if ($fecha_fin) {
            $query .= " AND r.fecha_salida <= :fecha_fin";
            $params[':fecha_fin'] = $fecha_fin;
        }
        
        $query .= " ORDER BY r.fecha_entrada ASC";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt;
    }

    // Obtener reservas recientes
    public function getRecent($limit = 10) {
        $query = "SELECT r.*, 
                    r.precio_total AS total,
                    r.num_noches AS noches,
                    c.nombre as cliente_nombre,
                    h.numero as habitacion_numero, 
                    h.tipo as habitacion_tipo
                FROM " . $this->table_name . " r
                LEFT JOIN clientes c ON r.cliente_id = c.id
                LEFT JOIN habitaciones h ON r.habitacion_id = h.id
                WHERE r.deleted_at IS NULL 
                ORDER BY r.created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    // Crear reserva
    public function create() {
        // Validar datos antes de crear
        $errores = $this->validar();
        if (!empty($errores)) {
            throw new Exception(implode(', ', $errores));
        }
        
        // Calcular totales automáticamente si no se proporcionan
        if (empty($this->noches) || empty($this->total)) {
            $this->calcularTotales();
        }
        
        // Verificar disponibilidad
        if (!$this->verificarDisponibilidad()) {
            throw new Exception("La habitación no está disponible en las fechas seleccionadas");
        }
        
        $query = "INSERT INTO " . $this->table_name . " 
            (cliente_id, habitacion_id, fecha_entrada, fecha_salida, estado, precio_noche, precio_total, metodo_pago, num_noches, num_huespedes, notas) 
            VALUES (:cliente_id, :habitacion_id, :fecha_entrada, :fecha_salida, :estado, :precio_noche, :total, :metodo_pago, :noches, :num_huespedes, :notas)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->cliente_id = htmlspecialchars(strip_tags($this->cliente_id));
        $this->habitacion_id = htmlspecialchars(strip_tags($this->habitacion_id));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->metodo_pago = htmlspecialchars(strip_tags($this->metodo_pago));
        
        $stmt->bindParam(":cliente_id", $this->cliente_id);
        $stmt->bindParam(":habitacion_id", $this->habitacion_id);
        $stmt->bindParam(":fecha_entrada", $this->fecha_entrada);
        $stmt->bindParam(":fecha_salida", $this->fecha_salida);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":precio_noche", $this->precio_noche);
        $stmt->bindParam(":total", $this->total);
        $stmt->bindParam(":metodo_pago", $this->metodo_pago);
        $stmt->bindParam(":noches", $this->noches);
        $stmt->bindParam(":num_huespedes", $this->numero_huespedes);
        $stmt->bindParam(":notas", $this->notas);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            
            // Si es una reserva confirmada, cancelar automáticamente reservas pendientes en conflicto
            if ($this->estado === 'confirmada') {
                $canceladas = $this->cancelarReservasPendientesConflicto();
            }
            
            return true;
        }
        return false;
    }

    // Actualizar reserva
    public function update() {
        // Validar datos antes de actualizar
        $errores = $this->validar();
        if (!empty($errores)) {
            throw new Exception(implode(', ', $errores));
        }
        
        // Calcular totales automáticamente si no se proporcionan
        if (empty($this->noches) || empty($this->total)) {
            $this->calcularTotales();
        }
        
        $query = "UPDATE " . $this->table_name . " 
                  SET cliente_id = :cliente_id, 
                      habitacion_id = :habitacion_id, 
                      fecha_entrada = :fecha_entrada, 
                      fecha_salida = :fecha_salida, 
                      estado = :estado, 
                      precio_noche = :precio_noche,
                      precio_total = :total, 
                      metodo_pago = :metodo_pago, 
                      num_noches = :noches,
                      num_huespedes = :num_huespedes,
                      notas = :notas,
                      updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->cliente_id = htmlspecialchars(strip_tags($this->cliente_id));
        $this->habitacion_id = htmlspecialchars(strip_tags($this->habitacion_id));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->metodo_pago = htmlspecialchars(strip_tags($this->metodo_pago));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(":cliente_id", $this->cliente_id);
        $stmt->bindParam(":habitacion_id", $this->habitacion_id);
        $stmt->bindParam(":fecha_entrada", $this->fecha_entrada);
        $stmt->bindParam(":fecha_salida", $this->fecha_salida);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":precio_noche", $this->precio_noche);
        $stmt->bindParam(":total", $this->total);
        $stmt->bindParam(":metodo_pago", $this->metodo_pago);
        $stmt->bindParam(":noches", $this->noches);
        $stmt->bindParam(":num_huespedes", $this->num_huespedes);
        $stmt->bindParam(":notas", $this->notas);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            // Si se actualizó a confirmada, cancelar automáticamente reservas pendientes en conflicto
            if ($this->estado === 'confirmada') {
                $canceladas = $this->cancelarReservasPendientesConflicto();
            }
            return true;
        }
        return false;
    }

    // Actualizar estado de la reserva
    public function updateEstado() {
        if (!in_array($this->estado, $this->estados_validos)) {
            throw new Exception("El estado no es válido");
        }
        
        $query = "UPDATE " . $this->table_name . " 
                  SET estado = :estado, 
                      updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Verificar disponibilidad de habitación (CORREGIDO Y MEJORADO)
    public function verificarDisponibilidad() {
        // Excluir la reserva actual si estamos actualizando
        $excluir_id = isset($this->id) ? $this->id : 0;
        
        // Verificar si hay otras reservas confirmadas en las mismas fechas
        $query = "SELECT COUNT(*) as count 
                  FROM " . $this->table_name . " 
                  WHERE habitacion_id = :habitacion_id 
                  AND id != :excluir_id
                  AND estado = 'confirmada'
                  AND deleted_at IS NULL
                  AND (
                      (fecha_entrada < :fecha_salida AND fecha_salida > :fecha_entrada)
                  )";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":habitacion_id", $this->habitacion_id);
        $stmt->bindParam(":excluir_id", $excluir_id);
        $stmt->bindParam(":fecha_entrada", $this->fecha_entrada);
        $stmt->bindParam(":fecha_salida", $this->fecha_salida);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] == 0;
    }

    // Obtener estadísticas de reservas
    public function getEstadisticas($fecha_inicio = null, $fecha_fin = null) {
        $query = "SELECT 
                    COUNT(*) as total_reservas,
                    SUM(CASE WHEN estado = 'confirmada' THEN 1 ELSE 0 END) as confirmadas,
                    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN estado = 'cancelada' THEN 1 ELSE 0 END) as canceladas,
                    SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END) as completadas,
                    SUM(precio_total) as ingresos_totales,
                    AVG(precio_total) as ingreso_promedio
                  FROM " . $this->table_name . " 
                  WHERE deleted_at IS NULL";
        
        $params = [];
        
        if ($fecha_inicio) {
            $query .= " AND fecha_entrada >= :fecha_inicio";
            $params[':fecha_inicio'] = $fecha_inicio;
        }
        
        if ($fecha_fin) {
            $query .= " AND fecha_salida <= :fecha_fin";
            $params[':fecha_fin'] = $fecha_fin;
        }
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener habitaciones ocupadas en un rango de fechas
    public function getHabitacionesOcupadas($fecha_inicio, $fecha_fin) {
        $query = "SELECT DISTINCT h.id, h.numero, h.tipo
                  FROM " . $this->table_name . " r
                  JOIN habitaciones h ON r.habitacion_id = h.id
                  WHERE r.estado IN ('confirmada', 'pendiente')
                  AND r.deleted_at IS NULL
                  AND (
                      (r.fecha_entrada < :fecha_fin AND r.fecha_salida > :fecha_inicio)
                  )
                  ORDER BY h.numero";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fecha_inicio", $fecha_inicio);
        $stmt->bindParam(":fecha_fin", $fecha_fin);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Actualizar automáticamente reservas que deberían estar completadas
    public function actualizarReservasCompletadas() {
        $hoy = date('Y-m-d');
        
        $query = "UPDATE " . $this->table_name . " 
                  SET estado = 'completada', 
                      updated_at = NOW()
                  WHERE estado IN ('confirmada', 'pendiente')
                  AND deleted_at IS NULL
                  AND fecha_salida < :hoy";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":hoy", $hoy);
        
        if($stmt->execute()) {
            return $stmt->rowCount(); // Retorna el número de filas afectadas
        }
        return 0;
    }

    // Obtener reservas que necesitan ser actualizadas (para debugging)
    public function getReservasPendientesActualizacion() {
        $hoy = date('Y-m-d');
        
        $query = "SELECT r.id, r.fecha_salida, r.estado, 
                        h.numero as habitacion_numero,
                        c.nombre as cliente_nombre
                  FROM " . $this->table_name . " r
                  LEFT JOIN habitaciones h ON r.habitacion_id = h.id
                  LEFT JOIN clientes c ON r.cliente_id = c.id
                  WHERE r.estado IN ('confirmada', 'pendiente')
                  AND r.deleted_at IS NULL
                  AND r.fecha_salida < :hoy
                  ORDER BY r.fecha_salida ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":hoy", $hoy);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Eliminar reserva (soft delete)
    public function delete() {
        // Verificar si la reserva existe
        if (empty($this->id)) {
            throw new Exception("ID de reserva es requerido");
        }

        // Soft delete: marcar como eliminada en lugar de borrar físicamente
        $query = "UPDATE " . $this->table_name . " 
                  SET deleted_at = NOW() 
                  WHERE id = :id AND deleted_at IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        if ($stmt->execute()) {
            return $stmt->rowCount() > 0;
        }
        
        return false;
    }

    // Cancelar automáticamente reservas pendientes cuando una habitación es confirmada por otro cliente
    public function cancelarReservasPendientesConflicto() {
        // Verificar si esta es una reserva confirmada
        if ($this->estado !== 'confirmada') {
            return false;
        }

        // Buscar reservas pendientes que entren en conflicto con esta reserva confirmada
        $query = "UPDATE " . $this->table_name . " 
                  SET estado = 'cancelada', 
                      updated_at = NOW()
                  WHERE habitacion_id = :habitacion_id 
                  AND estado = 'pendiente'
                  AND deleted_at IS NULL
                  AND id != :id_actual
                  AND (
                      (fecha_entrada < :fecha_salida AND fecha_salida > :fecha_entrada)
                  )";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":habitacion_id", $this->habitacion_id);
        $stmt->bindParam(":id_actual", $this->id);
        $stmt->bindParam(":fecha_entrada", $this->fecha_entrada);
        $stmt->bindParam(":fecha_salida", $this->fecha_salida);
        
        if ($stmt->execute()) {
            $canceladas = $stmt->rowCount();
            if ($canceladas > 0) {
                // Registrar las reservas canceladas para notificación
                $this->registrarCancelacionesAutomaticas($this->habitacion_id, $this->id, $this->fecha_entrada, $this->fecha_salida);
            }
            return $canceladas;
        }
        
        return 0;
    }

    // Registrar cancelaciones automáticas para auditoría
    public function registrarCancelacionesAutomaticas($habitacion_id, $reserva_confirmada_id, $fecha_entrada, $fecha_salida) {
        $query = "SELECT r.id, r.cliente_id, c.nombre as cliente_nombre, c.email as cliente_email
                  FROM " . $this->table_name . " r
                  JOIN clientes c ON r.cliente_id = c.id
                  WHERE r.habitacion_id = :habitacion_id 
                  AND r.estado = 'cancelada'
                  AND r.deleted_at IS NULL
                  AND r.id != :reserva_confirmada_id
                  AND (
                      (r.fecha_entrada < :fecha_salida AND r.fecha_salida > :fecha_entrada)
                  )
                  AND r.updated_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":habitacion_id", $habitacion_id);
        $stmt->bindParam(":reserva_confirmada_id", $reserva_confirmada_id);
        $stmt->bindParam(":fecha_entrada", $fecha_entrada);
        $stmt->bindParam(":fecha_salida", $fecha_salida);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener cancelaciones recientes para notificación
    public function getCancelacionesRecientes($habitacion_id, $reserva_confirmada_id, $fecha_entrada, $fecha_salida) {
        return $this->registrarCancelacionesAutomaticas($habitacion_id, $reserva_confirmada_id, $fecha_entrada, $fecha_salida);
    }
}
?>
