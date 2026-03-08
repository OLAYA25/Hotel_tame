<?php
// Sistema de permisos de emergencia (fallback) para evitar errores
class SimplePermissionHelper {
    private static $permisos_usuario = null;
    private static $usuario_id = null;
    private static $initialized = false;
    
    public static function initialize($usuario_id) {
        // Evitar múltiples inicializaciones
        if (self::$initialized && self::$usuario_id === $usuario_id) {
            return;
        }
        
        self::$usuario_id = $usuario_id;
        self::$initialized = true;
        
        // Permisos básicos por rol (fallback)
        if (isset($_SESSION['usuario']['rol'])) {
            $rol = $_SESSION['usuario']['rol'];
            self::$permisos_usuario = self::getBasicPermissionsByRole($rol);
        } else {
            // Si no hay rol, dar acceso básico
            self::$permisos_usuario = ['default_access' => true];
        }
    }
    
    private static function getBasicPermissionsByRole($rol) {
        $permissions = [];
        
        switch ($rol) {
            case 'admin':
                $permissions = [
                    'admin_ver' => true,
                    'usuarios_ver' => true,
                    'usuarios_editar' => true,
                    'usuarios_eliminar' => true,
                    'habitaciones_ver' => true,
                    'habitaciones_editar' => true,
                    'habitaciones_eliminar' => true,
                    'clientes_ver' => true,
                    'clientes_editar' => true,
                    'clientes_eliminar' => true,
                    'reservas_ver' => true,
                    'reservas_editar' => true,
                    'reservas_eliminar' => true,
                    'productos_ver' => true,
                    'productos_editar' => true,
                    'productos_eliminar' => true,
                    'pedidos_ver' => true,
                    'pedidos_editar' => true,
                    'pedidos_eliminar' => true,
                    'contabilidad_ver' => true,
                    'contabilidad_editar' => true,
                    'reportes_ver' => true,
                    'backup_manager_ver' => true,
                    'settings_ver' => true,
                    'default_access' => true
                ];
                break;
                
            case 'gerente':
                $permissions = [
                    'habitaciones_ver' => true,
                    'habitaciones_editar' => true,
                    'clientes_ver' => true,
                    'clientes_editar' => true,
                    'reservas_ver' => true,
                    'reservas_editar' => true,
                    'productos_ver' => true,
                    'productos_editar' => true,
                    'pedidos_ver' => true,
                    'pedidos_editar' => true,
                    'contabilidad_ver' => true,
                    'reportes_ver' => true,
                    'backup_manager_ver' => true,
                    'default_access' => true
                ];
                break;
                
            case 'recepcionista':
                $permissions = [
                    'habitaciones_ver' => true,
                    'clientes_ver' => true,
                    'clientes_editar' => true,
                    'reservas_ver' => true,
                    'reservas_editar' => true,
                    'productos_ver' => true,
                    'pedidos_ver' => true,
                    'pedidos_editar' => true,
                    'default_access' => true
                ];
                break;
                
            case 'Contador':
            case 'Auxiliar Contable':
                $permissions = [
                    'clientes_ver' => true,
                    'reservas_ver' => true,
                    'productos_ver' => true,
                    'pedidos_ver' => true,
                    'contabilidad_ver' => true,
                    'contabilidad_editar' => true,
                    'reportes_ver' => true,
                    'backup_manager_ver' => true,
                    'default_access' => true
                ];
                break;
                
            case 'mantenimiento':
                $permissions = [
                    'habitaciones_ver' => true,
                    'habitaciones_editar' => true,
                    'default_access' => true
                ];
                break;
                
            case 'limpieza':
                $permissions = [
                    'habitaciones_ver' => true,
                    'default_access' => true
                ];
                break;
                
            default:
                $permissions = ['default_access' => true];
                break;
        }
        
        return $permissions;
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
        
        // Módulos que siempre deben ser accesibles para usuarios autenticados
        $modules_always_allowed = [
            'mis_actividades.php',
            'mis_actividades_v2.php',
            'index.php',
            'dashboard.php',
            'settings.php'
        ];
        
        if (in_array($ruta_modulo, $modules_always_allowed)) {
            return true;
        }
        
        // Módulos de contabilidad permitidos para roles financieros
        $accounting_modules = [
            'contabilidad.php',
            'reportes.php',
            'backup_manager.php'
        ];
        
        if (in_array($ruta_modulo, $accounting_modules)) {
            // Permitir si tiene acceso a contabilidad (evitar recursión)
            return self::hasPermission('contabilidad_ver');
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
        
        $modules = [];
        foreach (self::$permisos_usuario as $clave => $permiso) {
            if (strpos($clave, '_ver') !== false) {
                $module_name = str_replace('_ver', '', $clave);
                $modules[] = [
                    'nombre' => $module_name,
                    'ruta' => $module_name . '.php'
                ];
            }
        }
        
        return $modules;
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
