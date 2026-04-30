<?php
/**
 * Repositorio de Reservas
 */

require_once __DIR__ . '/Repository.php';

class ReservaRepository extends Repository {
    protected $table = 'reservas';
    
    /**
     * Obtener reservas con detalles
     */
    public function getWithDetails($conditions = [], $limit = null, $offset = 0) {
        $sql = "SELECT r.*, 
                       h.numero as habitacion_numero, 
                       h.tipo as habitacion_tipo,
                       h.piso as habitacion_piso,
                       GROUP_CONCAT(DISTINCT CONCAT(c.nombre, ' ', c.apellido, ' (', rc.rol, ')') SEPARATOR ', ') as clientes
                FROM {$this->table} r
                LEFT JOIN habitaciones h ON r.habitacion_id = h.id
                LEFT JOIN reserva_clientes rc ON r.id = rc.reserva_id
                LEFT JOIN clientes c ON rc.cliente_id = c.id
                WHERE r.deleted_at IS NULL";
        
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "r.$key = :$key";
                $params[":$key"] = $value;
            }
            $sql .= " AND " . implode(' AND ', $where);
        }
        
        $sql .= " GROUP BY r.id ORDER BY r.fecha_entrada DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
        }
        
        return $this->db->prepare($sql)->execute($params)->fetchAll();
    }
    
    /**
     * Verificar disponibilidad de habitación
     */
    public function checkAvailability($habitacionId, $fechaEntrada, $fechaSalida, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}
                WHERE habitacion_id = :habitacion_id
                AND estado IN ('confirmada', 'ocupada')
                AND fecha_entrada <= :fecha_salida
                AND fecha_salida >= :fecha_entrada
                AND deleted_at IS NULL";
        
        $params = [
            ':habitacion_id' => $habitacionId,
            ':fecha_entrada' => $fechaEntrada,
            ':fecha_salida' => $fechaSalida
        ];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        
        $result = $this->db->prepare($sql)->execute($params)->fetch();
        return $result['count'] == 0;
    }
    
    /**
     * Obtener reservas del día
     */
    public function getTodayReservations() {
        $sql = "SELECT r.*, 
                       h.numero as habitacion_numero,
                       h.tipo as habitacion_tipo,
                       GROUP_CONCAT(CONCAT(c.nombre, ' ', c.apellido) SEPARATOR ', ') as clientes
                FROM {$this->table} r
                LEFT JOIN habitaciones h ON r.habitacion_id = h.id
                LEFT JOIN reserva_clientes rc ON r.id = rc.reserva_id
                LEFT JOIN clientes c ON rc.cliente_id = c.id
                WHERE (DATE(r.fecha_entrada) = CURDATE() OR DATE(r.fecha_salida) = CURDATE())
                AND r.deleted_at IS NULL
                GROUP BY r.id
                ORDER BY r.fecha_entrada";
        
        return $this->db->prepare($sql)->fetchAll();
    }
    
    /**
     * Obtener reservas por rango de fechas
     */
    public function getByDateRange($fechaInicio, $fechaFin, $habitacionId = null) {
        $sql = "SELECT r.*, h.numero as habitacion_numero, h.tipo as habitacion_tipo
                FROM {$this->table} r
                LEFT JOIN habitaciones h ON r.habitacion_id = h.id
                WHERE r.fecha_entrada <= :fecha_fin 
                AND r.fecha_salida >= :fecha_inicio
                AND r.deleted_at IS NULL";
        
        $params = [
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin' => $fechaFin
        ];
        
        if ($habitacionId) {
            $sql .= " AND r.habitacion_id = :habitacion_id";
            $params[':habitacion_id'] = $habitacionId;
        }
        
        $sql .= " ORDER BY r.fecha_entrada, h.numero";
        
        return $this->db->prepare($sql)->execute($params)->fetchAll();
    }
    
    /**
     * Obtener reservas por cliente
     */
    public function getByClient($clienteId, $limit = 10) {
        $sql = "SELECT r.*, h.numero as habitacion_numero, h.tipo as habitacion_tipo
                FROM {$this->table} r
                LEFT JOIN habitaciones h ON r.habitacion_id = h.id
                LEFT JOIN reserva_clientes rc ON r.id = rc.reserva_id
                WHERE rc.cliente_id = :cliente_id 
                AND r.deleted_at IS NULL
                ORDER BY r.fecha_entrada DESC
                LIMIT :limit";
        
        return $this->db->prepare($sql)
                       ->bind(':cliente_id', $clienteId)
                       ->bind(':limit', $limit, PDO::PARAM_INT)
                       ->fetchAll();
    }
    
    /**
     * Obtener estadísticas de reservas
     */
    public function getStatistics($period = 'month') {
        $dateCondition = match($period) {
            'day' => 'DATE(created_at) = CURDATE()',
            'week' => 'WEEK(created_at) = WEEK(NOW())',
            'month' => 'MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())',
            'year' => 'YEAR(created_at) = YEAR(NOW())',
            default => '1=1'
        };
        
        $sql = "SELECT 
                    COUNT(*) as total_reservas,
                    COUNT(CASE WHEN estado = 'confirmada' THEN 1 END) as confirmadas,
                    COUNT(CASE WHEN estado = 'cancelada' THEN 1 END) as canceladas,
                    COUNT(CASE WHEN estado = 'finalizada' THEN 1 END) as finalizadas,
                    SUM(precio_total) as ingresos_totales,
                    AVG(precio_total) as precio_promedio
                FROM {$this->table}
                WHERE deleted_at IS NULL AND {$dateCondition}";
        
        return $this->db->prepare($sql)->fetch();
    }
    
    /**
     * Cambiar estado de reserva
     */
    public function changeStatus($id, $estado) {
        $sql = "UPDATE {$this->table} 
                SET estado = :estado, updated_at = NOW() 
                WHERE id = :id AND deleted_at IS NULL";
        
        return $this->db->prepare($sql)
                       ->bind(':estado', $estado)
                       ->bind(':id', $id)
                       ->execute();
    }
    
    /**
     * Obtener ocupación por período
     */
    public function getOccupancyByPeriod($fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    DATE(r.fecha_entrada) as fecha,
                    COUNT(*) as total_reservas,
                    COUNT(DISTINCT r.habitacion_id) as habitaciones_ocupadas,
                    SUM(r.precio_total) as ingresos_dia
                FROM {$this->table} r
                WHERE r.fecha_entrada BETWEEN :fecha_inicio AND :fecha_fin
                AND r.estado IN ('confirmada', 'ocupada', 'finalizada')
                AND r.deleted_at IS NULL
                GROUP BY DATE(r.fecha_entrada)
                ORDER BY fecha";
        
        return $this->db->prepare($sql)
                       ->bind(':fecha_inicio', $fechaInicio)
                       ->bind(':fecha_fin', $fechaFin)
                       ->fetchAll();
    }
}
