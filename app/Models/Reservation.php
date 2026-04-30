<?php
/**
 * Modelo de Reservas
 */

class Reservation extends Model {
    protected $table = 'reservas';
    protected $primaryKey = 'id';
    protected $fillable = [
        'habitacion_id', 'fecha_entrada', 'fecha_salida', 'estado',
        'precio_total', 'observaciones', 'hotel_id'
    ];
    
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
            $sql .= " AND {$this->primaryKey} != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        
        $result = $this->db->prepare($sql)->execute($params)->fetch();
        return $result['count'] == 0;
    }
    
    /**
     * Crear reserva con validación
     */
    public function createWithValidation($data, $clientes) {
        $this->db->beginTransaction();
        
        try {
            // Verificar disponibilidad
            if (!$this->checkAvailability($data['habitacion_id'], $data['fecha_entrada'], $data['fecha_salida'])) {
                throw new Exception("La habitación no está disponible en las fechas seleccionadas");
            }
            
            // Crear reserva
            $reservaId = $this->create($data);
            
            // Asociar clientes
            $this->associateClients($reservaId, $clientes);
            
            // Actualizar estado de habitación
            $this->updateRoomStatus($data['habitacion_id'], 'reservada');
            
            $this->db->commit();
            return $reservaId;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Asociar clientes a reserva
     */
    private function associateClients($reservaId, $clientes) {
        $sql = "INSERT INTO reserva_clientes (reserva_id, cliente_id, rol) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        foreach ($clientes as $cliente) {
            $stmt->execute([$reservaId, $cliente['id'], $cliente['rol']]);
        }
    }
    
    /**
     * Obtener reservas con detalles
     */
    public function getWithDetails($conditions = []) {
        $sql = "SELECT r.*, h.numero as habitacion_numero, h.tipo as habitacion_tipo,
                       GROUP_CONCAT(CONCAT(c.nombre, ' ', c.apellido, ' (', rc.rol, ')') SEPARATOR ', ') as clientes
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
        
        return $this->db->prepare($sql)->execute($params)->fetchAll();
    }
    
    /**
     * Obtener reservas del día
     */
    public function getTodayReservations() {
        $sql = "SELECT r.*, h.numero as habitacion_numero,
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
     * Cancelar reserva
     */
    public function cancel($id, $motivo = '') {
        $this->db->beginTransaction();
        
        try {
            // Obtener datos de reserva
            $reserva = $this->getById($id);
            if (!$reserva) {
                throw new Exception("Reserva no encontrada");
            }
            
            // Actualizar estado
            $this->update($id, ['estado' => 'cancelada']);
            
            // Liberar habitación
            $this->updateRoomStatus($reserva['habitacion_id'], 'disponible');
            
            // Registrar auditoría
            AuditHelper::log($_SESSION['usuario']['id'], 'cancelar_reserva', $this->table, $id);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Actualizar estado de habitación
     */
    private function updateRoomStatus($habitacionId, $estado) {
        $sql = "UPDATE habitaciones SET estado = :estado WHERE id = :id";
        return $this->db->prepare($sql)
                        ->bind(':estado', $estado)
                        ->bind(':id', $habitacionId)
                        ->execute();
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
                    SUM(precio_total) as ingresos_totales,
                    AVG(precio_total) as precio_promedio
                FROM {$this->table}
                WHERE deleted_at IS NULL AND {$dateCondition}";
        
        return $this->db->prepare($sql)->fetch();
    }
}
