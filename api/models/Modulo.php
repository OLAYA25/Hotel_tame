<?php
class Modulo {
    private $conn;
    private $table_name = "modulos";
    
    public $id;
    public $nombre;
    public $descripcion;
    public $icono;
    public $ruta;
    public $orden;
    public $activo;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Obtener todos los módulos activos
    public function getActivos() {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE activo = TRUE 
                 ORDER BY orden ASC, nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    // Obtener módulos accesibles para un usuario
    public function getModulosByUsuario($usuario_id) {
        $query = "SELECT DISTINCT m.* 
                 FROM modulos m 
                 JOIN permisos p ON m.id = p.modulo_id 
                 JOIN roles_permisos rp ON p.id = rp.permiso_id 
                 JOIN usuarios_roles ur ON rp.rol_id = ur.rol_id 
                 JOIN roles r ON ur.rol_id = r.id 
                 WHERE ur.usuario_id = :usuario_id 
                 AND m.activo = TRUE 
                 AND r.activo = TRUE 
                 ORDER BY m.orden ASC, m.nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();
        return $stmt;
    }
    
    // Verificar si un usuario tiene acceso a un módulo
    public function usuarioHasAcceso($usuario_id, $ruta_modulo) {
        $query = "SELECT COUNT(*) as count 
                 FROM modulos m 
                 JOIN permisos p ON m.id = p.modulo_id 
                 JOIN roles_permisos rp ON p.id = rp.permiso_id 
                 JOIN usuarios_roles ur ON rp.rol_id = ur.rol_id 
                 JOIN roles r ON ur.rol_id = r.id 
                 WHERE ur.usuario_id = :usuario_id 
                 AND m.ruta = :ruta 
                 AND m.activo = TRUE 
                 AND r.activo = TRUE 
                 AND p.clave LIKE :permiso_ver";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":ruta", $ruta_modulo);
        $permiso_ver = str_replace('.php', '', $ruta_modulo) . '_ver';
        $stmt->bindParam(":permiso_ver", $permiso_ver);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }
}
?>
