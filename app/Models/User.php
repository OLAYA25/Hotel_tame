<?php
/**
 * Modelo de Usuarios
 */

class User extends Model {
    protected $table = 'usuarios';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nombre', 'apellido', 'email', 'password', 'telefono', 
        'rol', 'activo', 'hotel_id'
    ];
    
    /**
     * Autenticar usuario
     */
    public function authenticate($email, $password) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE email = :email AND deleted_at IS NULL AND activo = 1";
        
        $user = $this->db->prepare($sql)
                        ->bind(':email', $email)
                        ->fetch();
        
        if ($user && SecurityHelper::verifyPassword($password, $user['password'])) {
            // Limpiar intentos fallidos
            unset($_SESSION['login_attempts'][$email]);
            unset($_SESSION['locked_until']);
            
            return $user;
        }
        
        return false;
    }
    
    /**
     * Crear usuario con contraseña segura
     */
    public function createWithPassword($data) {
        $data['password'] = SecurityHelper::hashPassword($data['password']);
        return $this->create($data);
    }
    
    /**
     * Actualizar contraseña
     */
    public function updatePassword($id, $newPassword) {
        $hashedPassword = SecurityHelper::hashPassword($newPassword);
        $sql = "UPDATE {$this->table} SET password = :password WHERE {$this->primaryKey} = :id";
        
        return $this->db->prepare($sql)
                        ->bind(':password', $hashedPassword)
                        ->bind(':id', $id)
                        ->execute();
    }
    
    /**
     * Obtener usuarios por rol
     */
    public function getByRole($role) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE rol = :rol AND deleted_at IS NULL AND activo = 1";
        
        return $this->db->prepare($sql)
                        ->bind(':rol', $role)
                        ->fetchAll();
    }
    
    /**
     * Verificar si email existe
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE email = :email AND deleted_at IS NULL";
        
        $params = [':email' => $email];
        
        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        
        $result = $this->db->prepare($sql)->execute($params)->fetch();
        return $result['count'] > 0;
    }
}
