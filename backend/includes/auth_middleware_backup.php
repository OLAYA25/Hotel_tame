<?php
// Middleware temporal sin sistema de permisos
// Verificar sesión de usuario
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Función helper temporal (siempre devuelve true para no bloquear)
function checkPermission($clave_permiso) {
    return true;
}

function requirePermission($clave_permiso) {
    return true;
}

function hasPermission($clave_permiso) {
    return true;
}

function canAccessModule($ruta_modulo) {
    return true;
}
?>
