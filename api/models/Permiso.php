<?php
class Permiso {
    private $conn;
    private $table_name = "permisos";
    
    public $id;
    public $modulo_id;
    public $nombre;
    public $descripcion;
    public $clave;
    public $created_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Obtener todos los permisos agrupados por módulo
    public function getAllByModulo() {
        $query = "SELECT p.*, m.nombre as modulo_nombre, m.icono as modulo_icono 
                 FROM " . $this->table_name . " p 
                 JOIN modulos m ON p.modulo_id = m.id 
                 WHERE m.activo = TRUE 
                 ORDER BY m.orden, m.nombre, p.nombre";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    // Obtener permisos de un usuario (por sus roles)
    public function getPermisosByUsuario($usuario_id) {
        $query = "SELECT DISTINCT p.clave, p.nombre, m.nombre as modulo, m.ruta 
                 FROM permisos p 
                 JOIN roles_permisos rp ON p.id = rp.permiso_id 
                 JOIN usuarios_roles ur ON rp.rol_id = ur.rol_id 
                 JOIN roles r ON ur.rol_id = r.id 
                 JOIN modulos m ON p.modulo_id = m.id 
                 WHERE ur.usuario_id = :usuario_id 
                 AND r.activo = TRUE 
                 AND m.activo = TRUE 
                 ORDER BY m.orden, p.nombre";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();
        return $stmt;
    }
    
    // Verificar si un usuario tiene un permiso específico
    public function usuarioHasPermiso($usuario_id, $clave_permiso) {
        $query = "SELECT COUNT(*) as count 
                 FROM permisos p 
                 JOIN roles_permisos rp ON p.id = rp.permiso_id 
                 JOIN usuarios_roles ur ON rp.rol_id = ur.rol_id 
                 JOIN roles r ON ur.rol_id = r.id 
                 WHERE ur.usuario_id = :usuario_id 
                 AND p.clave = :clave 
                 AND r.activo = TRUE";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":clave", $clave_permiso);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }
    
    // Obtener permisos asignados a un rol
    public function getPermisosByRol($rol_id) {
        $query = "SELECT p.* 
                 FROM " . $this->table_name . " p 
                 JOIN roles_permisos rp ON p.id = rp.permiso_id 
                 WHERE rp.rol_id = :rol_id 
                 ORDER BY p.nombre";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":rol_id", $rol_id);
        $stmt->execute();
        return $stmt;
    }
}
?>
