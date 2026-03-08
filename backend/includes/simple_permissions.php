<?php
// Sistema simplificado de permisos para evitar problemas de rutas

// Desactivar warnings temporalmente para evitar errores de display
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

class SimplePermissionHelper {
    private static $permisos_usuario = null;
    private static $usuario_id = null;
    private static $checking_access = []; // Evitar recursión
    private static $initialized = false;
    private static $debug_mode = false; // Cambiar a true para depuración
    private static $use_fallback = false; // Usar sistema de emergencia
    
    public static function initialize($usuario_id) {
        // Evitar múltiples inicializaciones
        if (self::$initialized && self::$usuario_id === $usuario_id) {
            return;
        }
        
        self::$usuario_id = $usuario_id;
        self::$permisos_usuario = [];
        self::$checking_access = [];
        
        try {
            // Validar que el usuario_id sea válido
            if (empty($usuario_id) || !is_numeric($usuario_id)) {
                throw new Exception("ID de usuario inválido");
            }
            
            // Usar conexión directa para evitar problemas de rutas
            $database = new Database();
            $db = $database->getConnection();
            
            if (!$db) {
                throw new Exception("No se pudo establecer conexión a la base de datos");
            }
            
            // Optimizar consulta para evitar bucles infinitos y exceso de datos
            $query = "SELECT DISTINCT p.clave, p.nombre, m.nombre as modulo, m.ruta 
                     FROM permisos p 
                     JOIN roles_permisos rp ON p.id = rp.permiso_id 
                     JOIN usuarios_roles ur ON rp.rol_id = ur.rol_id 
                     JOIN roles r ON ur.rol_id = r.id 
                     JOIN modulos m ON p.modulo_id = m.id 
                     WHERE ur.usuario_id = :usuario_id 
                     AND r.activo = TRUE 
                     AND m.activo = TRUE 
                     AND p.clave IS NOT NULL 
                     AND p.clave != ''
                     ORDER BY m.orden, p.nombre
                     LIMIT 100"; // Limitar para evitar problemas de memoria
            
            $stmt = $db->prepare($query);
            if (!$stmt) {
                throw new Exception("Error preparando la consulta de permisos");
            }
            
            $stmt->bindParam(":usuario_id", $usuario_id, PDO::PARAM_INT);
            if (!$stmt->execute()) {
                throw new Exception("Error ejecutando la consulta de permisos");
            }
            
            $counter = 0;
            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false && $counter < 100) {
                // Validar que $row sea un array y tenga la clave 'clave'
                if (is_array($row) && isset($row['clave']) && $row['clave'] !== null && $row['clave'] !== '') {
                    // Sanitizar la clave para evitar problemas
                    $clave = trim($row['clave']);
                    if ($clave !== '') {
                        self::$permisos_usuario[$clave] = $row;
                        
                        // Logging de depuración
                        if (self::$debug_mode) {
                            error_log("Permiso cargado: {$clave}");
                        }
                    } else if (self::$debug_mode) {
                        error_log("Clave vacía encontrada en row: " . print_r($row, true));
                    }
                } else if (self::$debug_mode) {
                    error_log("Row inválido encontrado: " . var_export($row, true));
                }
                $counter++;
            }
            
            if (self::$debug_mode) {
                error_log("Total permisos cargados: " . count(self::$permisos_usuario));
            }
            
            self::$initialized = true;
            
        } catch (Exception $e) {
            error_log("Error en SimplePermissionHelper: " . $e->getMessage());
            
            // Usar sistema de fallback en caso de error
            self::useFallback();
        }
    }
    
    private static function useFallback() {
        // Incluir el archivo fallback
        require_once __DIR__ . '/simple_permissions_fallback.php';
        
        // Usar el sistema fallback directamente
        if (isset($_SESSION['usuario']['rol'])) {
            $rol = $_SESSION['usuario']['rol'];
            self::$permisos_usuario = self::getBasicPermissionsByRole($rol);
        } else {
            self::$permisos_usuario = ['default_access' => true];
        }
        
        self::$initialized = true;
        self::$use_fallback = true;
        
        error_log("Usando sistema de permisos fallback para rol: " . $_SESSION['usuario']['rol']);
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
        // Evitar recursión infinita
        if (isset(self::$checking_access[$ruta_modulo])) {
            return false; // Romper recursión
        }
        
        self::$checking_access[$ruta_modulo] = true;
        
        try {
            if (self::$permisos_usuario === null) {
                unset(self::$checking_access[$ruta_modulo]);
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
                unset(self::$checking_access[$ruta_modulo]);
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
                $hasAccess = self::hasPermission('contabilidad_ver');
                unset(self::$checking_access[$ruta_modulo]);
                return $hasAccess;
            }
            
            $permiso_ver = str_replace('.php', '', $ruta_modulo) . '_ver';
            $hasPermission = self::hasPermission($permiso_ver);
            
            unset(self::$checking_access[$ruta_modulo]);
            return $hasPermission;
            
        } catch (Exception $e) {
            unset(self::$checking_access[$ruta_modulo]);
            error_log("Error en canAccessModule: " . $e->getMessage());
            return false;
        }
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
