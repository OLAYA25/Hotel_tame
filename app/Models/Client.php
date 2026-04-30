<?php
/**
 * Modelo de Clientes
 */

class Client extends Model {
    protected $table = 'clientes';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nombre', 'apellido', 'email', 'telefono', 'documento', 
        'tipo_documento', 'direccion', 'ciudad', 'pais', 'hotel_id'
    ];
    
    /**
     * Buscar clientes por nombre o documento
     */
    public function search($query) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE (nombre LIKE :query OR apellido LIKE :query OR documento LIKE :query)
                AND deleted_at IS NULL
                ORDER BY nombre, apellido
                LIMIT 20";
        
        $searchTerm = "%{$query}%";
        return $this->db->prepare($sql)
                        ->bind(':query', $searchTerm)
                        ->fetchAll();
    }
    
    /**
     * Obtener clientes frecuentes
     */
    public function getFrequentClients($limit = 10) {
        $sql = "SELECT c.*, COUNT(r.id) as total_reservas
                FROM {$this->table} c
                LEFT JOIN reserva_clientes rc ON c.id = rc.cliente_id
                LEFT JOIN reservas r ON rc.reserva_id = r.id
                WHERE c.deleted_at IS NULL
                GROUP BY c.id
                HAVING total_reservas > 0
                ORDER BY total_reservas DESC, c.nombre
                LIMIT :limit";
        
        return $this->db->prepare($sql)
                        ->bind(':limit', $limit, PDO::PARAM_INT)
                        ->fetchAll();
    }
    
    /**
     * Verificar si documento existe
     */
    public function documentExists($document, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE documento = :document AND deleted_at IS NULL";
        
        $params = [':document' => $document];
        
        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        
        $result = $this->db->prepare($sql)->execute($params)->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Obtener estadísticas de clientes
     */
    public function getStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total_clientes,
                    COUNT(CASE WHEN email IS NOT NULL AND email != '' THEN 1 END) as con_email,
                    COUNT(CASE WHEN telefono IS NOT NULL AND telefono != '' THEN 1 END) as con_telefono,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as nuevos_30_dias
                FROM {$this->table}
                WHERE deleted_at IS NULL";
        
        return $this->db->prepare($sql)->fetch();
    }
}
