<?php
// Middleware de verificación de permisos
require_once __DIR__ . '/simple_permissions.php';

// Verificar que haya una sesión activa
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Inicializar sistema de permisos para el usuario actual
try {
    SimplePermissionHelper::initialize($_SESSION['usuario']['id']);
} catch (Exception $e) {
    // Si hay un error en la inicialización, registrar y continuar
    error_log("Error en auth_middleware: " . $e->getMessage());
}

// Obtener la página actual
$current_page = basename($_SERVER['PHP_SELF']);

// El index.php siempre debe ser accesible para usuarios autenticados
if ($current_page !== 'index.php') {
    // Permitir acceso a informe_huespedes si tiene permiso de contabilidad
    if ($current_page === 'informe_huespedes.php') {
        if (!SimplePermissionHelper::canAccessModule('contabilidad.php')) {
            header('Location: index.php?error=access_denied');
            exit;
        }
    } else {
        // Verificar acceso al módulo
        if (!SimplePermissionHelper::canAccessModule($current_page)) {
            // Redirigir al dashboard con mensaje de error
            header('Location: index.php?error=access_denied');
            exit;
        }
    }
}

// Función helper para verificar permisos específicos en las páginas
function checkPermission($clave_permiso) {
    return SimplePermissionHelper::hasPermission($clave_permiso);
}

// Función helper para requerir permisos específicos
function requirePermission($clave_permiso) {
    return SimplePermissionHelper::requirePermission($clave_permiso);
}
?>
