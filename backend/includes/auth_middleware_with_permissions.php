<?php
// Middleware de verificación de permisos
require_once __DIR__ . '/../api/utils/PermissionHelper.php';

// Verificar que haya una sesión activa
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Inicializar sistema de permisos para el usuario actual
try {
    PermissionHelper::initialize($_SESSION['usuario']['id']);
} catch (Exception $e) {
    // Si hay un error en la inicialización, registrar y continuar
    error_log("Error en auth_middleware: " . $e->getMessage());
    // En caso de error, permitir acceso pero registrar el problema
}

// Obtener la página actual
$current_page = basename($_SERVER['PHP_SELF']);

// El index.php siempre debe ser accesible para usuarios autenticados
if ($current_page !== 'index.php') {
    // Verificar acceso al módulo (solo si el sistema de permisos está disponible)
    if (!PermissionHelper::canAccessModule($current_page)) {
        // Redirigir al dashboard con mensaje de error
        header('Location: index.php?error=access_denied');
        exit;
    }
}

// Función helper para verificar permisos específicos en las páginas
function checkPermission($clave_permiso) {
    return PermissionHelper::hasPermission($clave_permiso);
}

// Función helper para requerir permisos específicos
function requirePermission($clave_permiso) {
    return PermissionHelper::requirePermission($clave_permiso);
}

// Funciones helper para compatibilidad con el sidebar
function hasPermission($clave_permiso) {
    return PermissionHelper::hasPermission($clave_permiso);
}

function canAccessModule($ruta_modulo) {
    return PermissionHelper::canAccessModule($ruta_modulo);
}
?>
