<?php
/**
 * Modelo de Habitaciones
 */

class Room extends Model {
    protected $table = 'habitaciones';
    protected $primaryKey = 'id';
    protected $fillable = [
        'numero', 'tipo', 'capacidad', 'precio_base', 'estado', 
        'piso', 'descripcion', 'hotel_id'
    ];
    
    const ESTADOS = [
        'disponible' => 'Disponible',
        'reservada' => 'Reservada',
        'ocupada' => 'Ocupada',
        'limpieza' => 'En Limpieza',
        'mantenimiento' => 'En Mantenimiento',
        'fuera_servicio' => 'Fuera de Servicio'
    ];
    
    /**
     * Obtener habitaciones disponibles para fechas
     */
    public function getAvailable($fechaEntrada, $fechaSalida, $tipo = null) {
        $sql = "SELECT h.* FROM {$this->table} h
                WHERE h.estado = 'disponible'
                AND h.deleted_at IS NULL
                AND h.id NOT IN (
                    SELECT r.habitacion_id 
                    FROM reservas r 
                    WHERE r.estado IN ('confirmada', 'ocupada')
                    AND r.fecha_entrada <= :fecha_salida
                    AND r.fecha_salida >= :fecha_entrada
                    AND r.deleted_at IS NULL
                )";
        
        $params = [
            ':fecha_entrada' => $fechaEntrada,
            ':fecha_salida' => $fechaSalida
        ];
        
        if ($tipo) {
            $sql .= " AND h.tipo = :tipo";
            $params[':tipo'] = $tipo;
        }
        
        $sql .= " ORDER BY h.numero";
        
        return $this->db->prepare($sql)->execute($params)->fetchAll();
    }
    
    /**
     * Cambiar estado con historial
     */
    public function changeStatus($id, $nuevoEstado, $usuarioId) {
        $this->db->beginTransaction();
        
        try {
            // Obtener estado actual
            $habitacion = $this->getById($id);
            if (!$habitacion) {
                throw new Exception("Habitación no encontrada");
            }
            
            // Actualizar estado
            $this->update($id, ['estado' => $nuevoEstado]);
            
            // Registrar en historial
            $this->recordStatusHistory($id, $habitacion['estado'], $nuevoEstado, $usuarioId);
            
            // Registrar auditoría
            AuditHelper::log($usuarioId, 'cambiar_estado_habitacion', $this->table, $id);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Registrar historial de cambios de estado
     */
    private function recordStatusHistory($habitacionId, $estadoAnterior, $estadoNuevo, $usuarioId) {
        $sql = "INSERT INTO historial_habitaciones 
                (habitacion_id, estado_anterior, estado_nuevo, usuario_id, fecha) 
                VALUES (?, ?, ?, ?, NOW())";
        
        return $this->db->prepare($sql)->execute([
            $habitacionId, $estadoAnterior, $estadoNuevo, $usuarioId
        ]);
    }
    
    /**
     * Obtener historial de habitación
     */
    public function getHistory($habitacionId, $limit = 50) {
        $sql = "SELECT hh.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido
                FROM historial_habitaciones hh
                LEFT JOIN usuarios u ON hh.usuario_id = u.id
                WHERE hh.habitacion_id = :habitacion_id
                ORDER BY hh.fecha DESC
                LIMIT :limit";
        
        return $this->db->prepare($sql)
                        ->bind(':habitacion_id', $habitacionId)
                        ->bind(':limit', $limit, PDO::PARAM_INT)
                        ->fetchAll();
    }
    
    /**
     * Obtener ocupación por tipo
     */
    public function getOccupancyByType() {
        $sql = "SELECT tipo, 
                       COUNT(*) as total,
                       COUNT(CASE WHEN estado = 'ocupada' THEN 1 END) as ocupadas,
                       COUNT(CASE WHEN estado = 'disponible' THEN 1 END) as disponibles,
                       COUNT(CASE WHEN estado = 'reservada' THEN 1 END) as reservadas
                FROM {$this->table}
                WHERE deleted_at IS NULL
                GROUP BY tipo
                ORDER BY tipo";
        
        return $this->db->prepare($sql)->fetchAll();
    }
    
    /**
     * Obtener mapa de ocupación para calendario
     */
    public function getOccupancyMap($fechaInicio, $fechaFin) {
        $sql = "SELECT h.numero, h.tipo, h.estado,
                       r.id as reserva_id, r.fecha_entrada, r.fecha_salida, r.estado as reserva_estado,
                       CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre
                FROM {$this->table} h
                LEFT JOIN reservas r ON h.id = r.habitacion_id 
                    AND r.estado IN ('confirmada', 'ocupada')
                    AND r.fecha_entrada <= :fecha_fin 
                    AND r.fecha_salida >= :fecha_inicio
                    AND r.deleted_at IS NULL
                LEFT JOIN reserva_clientes rc ON r.id = rc.reserva_id AND rc.rol = 'titular'
                LEFT JOIN clientes c ON rc.cliente_id = c.id
                WHERE h.deleted_at IS NULL
                ORDER BY h.numero, h.tipo";
        
        return $this->db->prepare($sql)
                        ->bind(':fecha_inicio', $fechaInicio)
                        ->bind(':fecha_fin', $fechaFin)
                        ->fetchAll();
    }
    
    /**
     * Obtener estadísticas de habitaciones
     */
    public function getStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total_habitaciones,
                    COUNT(CASE WHEN estado = 'disponible' THEN 1 END) as disponibles,
                    COUNT(CASE WHEN estado = 'ocupada' THEN 1 END) as ocupadas,
                    COUNT(CASE WHEN estado = 'reservada' THEN 1 END) as reservadas,
                    COUNT(CASE WHEN estado = 'limpieza' THEN 1 END) en_limpieza,
                    COUNT(CASE WHEN estado = 'mantenimiento' THEN 1 END) en_mantenimiento,
                    AVG(precio_base) as precio_promedio
                FROM {$this->table}
                WHERE deleted_at IS NULL";
        
        return $this->db->prepare($sql)->fetch();
    }
}
