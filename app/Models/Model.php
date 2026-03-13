<?php
/**
 * Modelo base para todos los modelos
 */

abstract class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtener todos los registros
     */
    public function getAll($conditions = []) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = :$key";
                $params[":$key"] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        return $this->db->prepare($sql)->execute($params)->fetchAll();
    }
    
    /**
     * Obtener registro por ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->db->prepare($sql)->bind(':id', $id)->fetch();
    }
    
    /**
     * Crear nuevo registro
     */
    public function create($data) {
        $data = $this->filterFillable($data);
        
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
        $data = $this->filterFillable($data);
        
        $set = [];
        $params = [':id' => $id];
        
        foreach ($data as $key => $value) {
            $set[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE {$this->primaryKey} = :id";
        
        return $this->db->prepare($sql)->execute($params);
    }
    
    /**
     * Eliminar registro (soft delete)
     */
    public function delete($id) {
        $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE {$this->primaryKey} = :id";
        return $this->db->prepare($sql)->bind(':id', $id)->execute();
    }
    
    /**
     * Eliminar permanentemente
     */
    public function forceDelete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->db->prepare($sql)->bind(':id', $id)->execute();
    }
    
    /**
     * Filtrar solo campos permitidos
     */
    protected function filterFillable($data) {
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * Contar registros
     */
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = :$key";
                $params[":$key"] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $result = $this->db->prepare($sql)->execute($params)->fetch();
        return $result['count'];
    }
}
