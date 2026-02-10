<?php
// Sistema simplificado de permisos para evitar problemas de rutas
class SimplePermissionHelper {
    private static $permisos_usuario = null;
    private static $usuario_id = null;
    
    public static function initialize($usuario_id) {
        self::$usuario_id = $usuario_id;
        self::$permisos_usuario = [];
        
        try {
            // Usar conexión directa para evitar problemas de rutas
            $database = new Database();
            $db = $database->getConnection();
            
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
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(":usuario_id", $usuario_id);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                self::$permisos_usuario[$row['clave']] = $row;
            }
        } catch (Exception $e) {
            error_log("Error en SimplePermissionHelper: " . $e->getMessage());
            // En caso de error, permitir acceso por defecto
            self::$permisos_usuario = ['default_access' => true];
        }
    }
    
    public static function hasPermission($clave_permiso) {
        if (self::$permisos_usuario === null) {
            return true; // Permitir por defecto si no hay permisos cargados
        }
        
        return isset(self::$permisos_usuario[$clave_permiso]);
    }
    
    public static function canAccessModule($ruta_modulo) {
        if (self::$permisos_usuario === null) {
            return true; // Permitir por defecto
        }
        
        $permiso_ver = str_replace('.php', '', $ruta_modulo) . '_ver';
        return self::hasPermission($permiso_ver);
    }
    
    public static function getUserPermissions() {
        return self::$permisos_usuario ?? [];
    }
    
    public static function getAccessibleModules() {
        if (self::$permisos_usuario === null) {
            return [];
        }
        
        $modulos = [];
        foreach (self::$permisos_usuario as $permiso) {
            if (!isset($modulos[$permiso['modulo']])) {
                $modulos[$permiso['modulo']] = [
                    'nombre' => $permiso['modulo'],
                    'ruta' => $permiso['ruta']
                ];
            }
        }
        
        return array_values($modulos);
    }
    
    public static function requirePermission($clave_permiso) {
        if (!self::hasPermission($clave_permiso)) {
            header('HTTP/1.0 403 Forbidden');
            header('Location: index.php?error=permission_denied');
            exit;
        }
    }
    
    public static function requireModuleAccess($ruta_modulo) {
        if (!self::canAccessModule($ruta_modulo)) {
            header('HTTP/1.0 403 Forbidden');
            header('Location: index.php?error=access_denied');
            exit;
        }
    }
}
?>
