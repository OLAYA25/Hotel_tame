<?php
class Turno {
    private $conn;
    private $table_name = "turnos";
    
    public $id;
    public $usuario_id;
    public $tipo_turno_id;
    public $fecha;
    public $hora_entrada_real;
    public $hora_salida_real;
    public $estado;
    public $notas;
    public $supervisor_id;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Obtener todos los turnos con filtros
    public function getAll($fecha_inicio = null, $fecha_fin = null, $usuario_id = null, $estado = null) {
        $query = "SELECT t.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido,
                 tt.nombre as tipo_turno_nombre, tt.hora_inicio, tt.hora_fin, tt.color,
                 s.nombre as supervisor_nombre, s.apellido as supervisor_apellido
                 FROM " . $this->table_name . " t
                 JOIN usuarios u ON t.usuario_id = u.id
                 JOIN tipos_turno tt ON t.tipo_turno_id = tt.id
                 LEFT JOIN usuarios s ON t.supervisor_id = s.id
                 WHERE 1=1";
        
        $params = [];
        
        if ($fecha_inicio) {
            $query .= " AND t.fecha >= :fecha_inicio";
            $params[':fecha_inicio'] = $fecha_inicio;
        }
        
        if ($fecha_fin) {
            $query .= " AND t.fecha <= :fecha_fin";
            $params[':fecha_fin'] = $fecha_fin;
        }
        
        if ($usuario_id) {
            $query .= " AND t.usuario_id = :usuario_id";
            $params[':usuario_id'] = $usuario_id;
        }
        
        if ($estado) {
            $query .= " AND t.estado = :estado";
            $params[':estado'] = $estado;
        }
        
        $query .= " ORDER BY t.fecha DESC, tt.hora_inicio ASC";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt;
    }
    
    // Crear turno
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (usuario_id, tipo_turno_id, fecha, estado, notas, supervisor_id) 
                 VALUES (:usuario_id, :tipo_turno_id, :fecha, :estado, :notas, :supervisor_id)";
        
        $stmt = $this->conn->prepare($query);
        
        $this->usuario_id = htmlspecialchars(strip_tags($this->usuario_id));
        $this->tipo_turno_id = htmlspecialchars(strip_tags($this->tipo_turno_id));
        $this->fecha = htmlspecialchars(strip_tags($this->fecha));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->notas = htmlspecialchars(strip_tags($this->notas));
        
        $stmt->bindParam(":usuario_id", $this->usuario_id);
        $stmt->bindParam(":tipo_turno_id", $this->tipo_turno_id);
        $stmt->bindParam(":fecha", $this->fecha);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":notas", $this->notas);
        $stmt->bindParam(":supervisor_id", $this->supervisor_id);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
    
    // Actualizar turno
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET usuario_id = :usuario_id, 
                     tipo_turno_id = :tipo_turno_id, 
                     fecha = :fecha,
                     hora_entrada_real = :hora_entrada_real,
                     hora_salida_real = :hora_salida_real,
                     estado = :estado,
                     notas = :notas,
                     supervisor_id = :supervisor_id,
                     updated_at = NOW()
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->usuario_id = htmlspecialchars(strip_tags($this->usuario_id));
        $this->tipo_turno_id = htmlspecialchars(strip_tags($this->tipo_turno_id));
        $this->fecha = htmlspecialchars(strip_tags($this->fecha));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->notas = htmlspecialchars(strip_tags($this->notas));
        
        $stmt->bindParam(":usuario_id", $this->usuario_id);
        $stmt->bindParam(":tipo_turno_id", $this->tipo_turno_id);
        $stmt->bindParam(":fecha", $this->fecha);
        $stmt->bindParam(":hora_entrada_real", $this->hora_entrada_real);
        $stmt->bindParam(":hora_salida_real", $this->hora_salida_real);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":notas", $this->notas);
        $stmt->bindParam(":supervisor_id", $this->supervisor_id);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Eliminar turno
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Obtener turnos de un usuario en un rango de fechas
    public function getTurnosUsuario($usuario_id, $fecha_inicio, $fecha_fin) {
        $query = "SELECT t.*, tt.nombre as tipo_turno_nombre, tt.hora_inicio, tt.hora_fin, tt.color
                 FROM " . $this->table_name . " t
                 JOIN tipos_turno tt ON t.tipo_turno_id = tt.id
                 WHERE t.usuario_id = :usuario_id 
                 AND t.fecha BETWEEN :fecha_inicio AND :fecha_fin
                 ORDER BY t.fecha, tt.hora_inicio";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":fecha_inicio", $fecha_inicio);
        $stmt->bindParam(":fecha_fin", $fecha_fin);
        $stmt->execute();
        return $stmt;
    }
    
    // Obtener resumen de horas trabajadas
    public function getResumenHoras($usuario_id, $fecha_inicio, $fecha_fin) {
        $query = "SELECT 
                 COUNT(*) as total_turnos,
                 SUM(CASE WHEN t.estado = 'completado' THEN 1 ELSE 0 END) as turnos_completados,
                 SUM(CASE WHEN t.estado = 'ausente' THEN 1 ELSE 0 END) as ausencias,
                 SUM(TIMESTAMPDIFF(HOUR, t.hora_entrada_real, t.hora_salida_real)) as horas_trabajadas
                 FROM " . $this->table_name . " t
                 WHERE t.usuario_id = :usuario_id 
                 AND t.fecha BETWEEN :fecha_inicio AND :fecha_fin
                 AND t.estado IN ('completado', 'en_curso')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":fecha_inicio", $fecha_inicio);
        $stmt->bindParam(":fecha_fin", $fecha_fin);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtener turnos de un usuario específico
    public function getByUsuario($usuario_id, $fecha_inicio = null, $fecha_fin = null) {
        $query = "SELECT t.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido,
                 tt.nombre as tipo_turno_nombre, tt.hora_inicio, tt.hora_fin, tt.color
                 FROM " . $this->table_name . " t
                 JOIN usuarios u ON t.usuario_id = u.id
                 JOIN tipos_turno tt ON t.tipo_turno_id = tt.id
                 WHERE t.usuario_id = :usuario_id";
        
        $params = [':usuario_id' => $usuario_id];
        
        if ($fecha_inicio) {
            $query .= " AND t.fecha >= :fecha_inicio";
            $params[':fecha_inicio'] = $fecha_inicio;
        }
        
        if ($fecha_fin) {
            $query .= " AND t.fecha <= :fecha_fin";
            $params[':fecha_fin'] = $fecha_fin;
        }
        
        $query .= " ORDER BY t.fecha DESC";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt;
    }
    
    // Obtener resumen de actividades de un usuario
    public function getResumenUsuario($usuario_id, $fecha_inicio, $fecha_fin) {
        $query = "SELECT 
                 COUNT(*) as total_actividades,
                 SUM(CASE WHEN t.estado = 'completado' THEN 1 ELSE 0 END) as actividades_completadas,
                 SUM(CASE WHEN t.estado = 'programado' THEN 1 ELSE 0 END) as actividades_programadas,
                 SUM(CASE WHEN t.estado = 'ausente' THEN 1 ELSE 0 END) as ausencias,
                 SUM(TIMESTAMPDIFF(HOUR, t.hora_entrada_real, COALESCE(t.hora_salida_real, NOW()))) as horas_trabajadas
                 FROM " . $this->table_name . " t
                 WHERE t.usuario_id = :usuario_id 
                 AND t.fecha BETWEEN :fecha_inicio AND :fecha_fin";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":fecha_inicio", $fecha_inicio);
        $stmt->bindParam(":fecha_fin", $fecha_fin);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtener todas las actividades de un usuario (turnos, reservas, pedidos, tareas)
    public function getAllActividadesUsuario($usuario_id, $fecha_inicio, $fecha_fin) {
        $actividades = [];
        
        // Obtener turnos
        $query_turnos = "SELECT 'turno' as tipo, t.id, t.fecha as fecha_actividad, 
                        tt.hora_inicio as hora_inicio, t.estado, 
                        CONCAT('Turno: ', tt.nombre) as descripcion,
                        t.notas as detalles
                        FROM " . $this->table_name . " t
                        JOIN tipos_turno tt ON t.tipo_turno_id = tt.id
                        WHERE t.usuario_id = :usuario_id 
                        AND t.fecha BETWEEN :fecha_inicio AND :fecha_fin";
        
        $stmt = $this->conn->prepare($query_turnos);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":fecha_inicio", $fecha_inicio);
        $stmt->bindParam(":fecha_fin", $fecha_fin);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $actividades[] = $row;
        }
        
        // Aquí se pueden agregar más consultas para reservas, pedidos, tareas
        // Por ahora solo retornamos los turnos
        
        return $actividades;
    }
    
    // Obtener estadísticas generales de turnos
    public function getEstadisticas($fecha_inicio, $fecha_fin) {
        $query = "SELECT 
                 COUNT(*) as total_turnos,
                 SUM(CASE WHEN t.estado = 'completado' THEN 1 ELSE 0 END) as turnos_completados,
                 SUM(CASE WHEN t.estado = 'programado' THEN 1 ELSE 0 END) as turnos_programados,
                 SUM(CASE WHEN t.estado = 'ausente' THEN 1 ELSE 0 END) as ausencias,
                 COUNT(DISTINCT t.usuario_id) as empleados_activos,
                 SUM(TIMESTAMPDIFF(HOUR, t.hora_entrada_real, COALESCE(t.hora_salida_real, NOW()))) as horas_totales
                 FROM " . $this->table_name . " t
                 WHERE t.fecha BETWEEN :fecha_inicio AND :fecha_fin";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fecha_inicio", $fecha_inicio);
        $stmt->bindParam(":fecha_fin", $fecha_fin);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtener turno por ID
    public function getById() {
        $query = "SELECT t.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido,
                 tt.nombre as tipo_turno_nombre, tt.hora_inicio, tt.hora_fin, tt.color,
                 s.nombre as supervisor_nombre, s.apellido as supervisor_apellido
                 FROM " . $this->table_name . " t
                 JOIN usuarios u ON t.usuario_id = u.id
                 JOIN tipos_turno tt ON t.tipo_turno_id = tt.id
                 LEFT JOIN usuarios s ON t.supervisor_id = s.id
                 WHERE t.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->usuario_id = $row['usuario_id'];
            $this->tipo_turno_id = $row['tipo_turno_id'];
            $this->fecha = $row['fecha'];
            $this->hora_entrada_real = $row['hora_entrada_real'];
            $this->hora_salida_real = $row['hora_salida_real'];
            $this->estado = $row['estado'];
            $this->notas = $row['notas'];
            $this->supervisor_id = $row['supervisor_id'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }
}
?>
