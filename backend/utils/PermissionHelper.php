<?php
// Incluir las clases necesarias
include_once __DIR__ . '/../../config/database.php';

// Sistema de gestión de permisos
class PermissionHelper {
    private static $permisos_usuario = null;
    private static $usuario_id = null;
    
    // Inicializar el sistema de permisos para el usuario actual
    public static function initialize($usuario_id) {
        self::$usuario_id = $usuario_id;
        self::$permisos_usuario = [];
        
        try {
            // Incluir los modelos necesarios
            include_once __DIR__ . '/../models/Permiso.php';
            include_once __DIR__ . '/../models/Modulo.php';
            
            $database = new Database();
            $db = $database->getConnection();
            
            $permiso = new Permiso($db);
            $stmt = $permiso->getPermisosByUsuario($usuario_id);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                self::$permisos_usuario[$row['clave']] = $row;
            }
        } catch (Exception $e) {
            error_log("Error inicializando permisos: " . $e->getMessage());
        }
    }
    
    // Verificar si el usuario tiene un permiso específico
    public static function hasPermission($clave_permiso) {
        if (self::$permisos_usuario === null) {
            return false;
        }
        
        return isset(self::$permisos_usuario[$clave_permiso]);
    }
    
    // Verificar si el usuario tiene acceso a un módulo
    public static function canAccessModule($ruta_modulo) {
        if (self::$permisos_usuario === null) {
            // Si no hay permisos cargados, permitir acceso por defecto
            return true;
        }
        
        $permiso_ver = str_replace('.php', '', $ruta_modulo) . '_ver';
        return self::hasPermission($permiso_ver);
    }
    
    // Obtener todos los permisos del usuario
    public static function getUserPermissions() {
        return self::$permisos_usuario ?? [];
    }
    
    // Obtener módulos accesibles del usuario
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
    
    // Verificar permiso en tiempo real (base de datos)
    public static function checkPermission($usuario_id, $clave_permiso) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $permiso = new Permiso($db);
            return $permiso->usuarioHasPermiso($usuario_id, $clave_permiso);
        } catch (Exception $e) {
            error_log("Error verificando permiso: " . $e->getMessage());
            return false;
        }
    }
    
    // Middleware para verificar acceso a módulos
    public static function requireModuleAccess($ruta_modulo) {
        if (!self::canAccessModule($ruta_modulo)) {
            header('HTTP/1.0 403 Forbidden');
            header('Location: index.php?error=access_denied');
            exit;
        }
    }
    
    // Middleware para verificar permiso específico
    public static function requirePermission($clave_permiso) {
        if (!self::hasPermission($clave_permiso)) {
            header('HTTP/1.0 403 Forbidden');
            header('Location: index.php?error=permission_denied');
            exit;
        }
    }
}
?>
