<?php
/**
 * Repositorio base para acceso a datos
 */

abstract class Repository {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $relations = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtener todos los registros
     */
    public function all($conditions = [], $orderBy = null, $limit = null, $offset = 0) {
        $sql = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = :$key";
                $params[":$key"] = $value;
            }
            $sql .= " AND " . implode(' AND ', $where);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
        }
        
        return $this->db->prepare($sql)->execute($params)->fetchAll();
    }
    
    /**
     * Obtener registro por ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE {$this->primaryKey} = :id AND deleted_at IS NULL";
        
        return $this->db->prepare($sql)
                       ->bind(':id', $id)
                       ->fetch();
    }
    
    /**
     * Buscar registros
     */
    public function search($query, $fields = [], $limit = 50) {
        if (empty($fields)) {
            $fields = ['nombre', 'apellido']; // Default fields
        }
        
        $conditions = [];
        foreach ($fields as $field) {
            $conditions[] = "$field LIKE :query";
        }
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE (" . implode(' OR ', $conditions) . ")
                AND deleted_at IS NULL
                ORDER BY created_at DESC
                LIMIT :limit";
        
        $searchTerm = "%{$query}%";
        
        return $this->db->prepare($sql)
                       ->bind(':query', $searchTerm)
                       ->bind(':limit', $limit, PDO::PARAM_INT)
                       ->fetchAll();
    }
    
    /**
     * Crear registro
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $columns = implode(', ', array_keys($data));
        $values = implode(', :', array_keys($data));
        $params = [];
        
        foreach ($data as $key => $value) {
            $params[":$key"] = $value;
        }
        
        $sql = "INSERT INTO {$this->table} ($columns) VALUES (:$values)";
        
        $this->db->prepare($sql)->execute($params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar registro
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $set = [];
        $params = [':id' => $id];
        
        foreach ($data as $key => $value) {
            $set[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . 
                " WHERE {$this->primaryKey} = :id AND deleted_at IS NULL";
        
        return $this->db->prepare($sql)->execute($params);
    }
    
    /**
     * Eliminar registro (soft delete)
     */
    public function delete($id) {
        $sql = "UPDATE {$this->table} 
                SET deleted_at = NOW() 
                WHERE {$this->primaryKey} = :id";
        
        return $this->db->prepare($sql)
                       ->bind(':id', $id)
                       ->execute();
    }
    
    /**
     * Eliminar permanentemente
     */
    public function forceDelete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        
        return $this->db->prepare($sql)
                       ->bind(':id', $id)
                       ->execute();
    }
    
    /**
     * Contar registros
     */
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE deleted_at IS NULL";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = :$key";
                $params[":$key"] = $value;
            }
            $sql .= " AND " . implode(' AND ', $where);
        }
        
        $result = $this->db->prepare($sql)->execute($params)->fetch();
        return $result['count'];
    }
    
    /**
     * Verificar si existe registro
     */
    public function exists($field, $value, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE $field = :value AND deleted_at IS NULL";
        
        $params = [':value' => $value];
        
        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        
        $result = $this->db->prepare($sql)->execute($params)->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Obtener con relaciones
     */
    public function with($relations) {
        $this->relations = $relations;
        return $this;
    }
    
    /**
     * Ejecutar consulta personalizada
     */
    public function query($sql, $params = []) {
        return $this->db->prepare($sql)->execute($params);
    }
    
    /**
     * Iniciar transacción
     */
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    /**
     * Confirmar transacción
     */
    public function commit() {
        return $this->db->commit();
    }
    
    /**
     * Revertir transacción
     */
    public function rollBack() {
        return $this->db->rollBack();
    }
}
