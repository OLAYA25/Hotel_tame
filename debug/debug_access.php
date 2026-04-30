<?php
// Script de diagnóstico para problemas de acceso
session_start();

echo "<h1>Diagnóstico de Acceso</h1>";

// 1. Verificar sesión
echo "<h2>1. Estado de Sesión</h2>";
if (isset($_SESSION['usuario'])) {
    echo "<p style='color: green;'>✅ Sesión activa</p>";
    echo "<pre>";
    print_r($_SESSION['usuario']);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>❌ No hay sesión activa</p>";
}

// 2. Verificar permisos
echo "<h2>2. Sistema de Permisos</h2>";
require_once 'includes/simple_permissions.php';

if (isset($_SESSION['usuario'])) {
    SimplePermissionHelper::initialize($_SESSION['usuario']['id']);
    $permisos = SimplePermissionHelper::getUserPermissions();
    echo "<p>Permisos cargados: " . count($permisos) . "</p>";
    echo "<pre>";
    print_r($permisos);
    echo "</pre>";
    
    // 3. Verificar acceso a módulos específicos
    echo "<h2>3. Verificación de Acceso a Módulos</h2>";
    $modulos_test = [
        'mis_actividades.php',
        'mis_actividades_v2.php',
        'dashboard_advanced.php',
        'index.php'
    ];
    
    foreach ($modulos_test as $modulo) {
        $acceso = SimplePermissionHelper::canAccessModule($modulo);
        $color = $acceso ? 'green' : 'red';
        $icono = $acceso ? '✅' : '❌';
        echo "<p style='color: {$color};'>{$icono} {$modulo}: " . ($acceso ? 'Permitido' : 'Denegado') . "</p>";
    }
}

// 4. Verificar archivos
echo "<h2>4. Verificación de Archivos</h2>";
$archivos_test = [
    'mis_actividades_v2.php',
    'dashboard_advanced.php',
    'includes/simple_permissions.php',
    'includes/auth_middleware.php'
];

foreach ($archivos_test as $archivo) {
    $existe = file_exists($archivo);
    $color = $existe ? 'green' : 'red';
    $icono = $existe ? '✅' : '❌';
    echo "<p style='color: {$color};'>{$icono} {$archivo}: " . ($existe ? 'Existe' : 'No existe') . "</p>";
}

// 5. Probar acceso directo
echo "<h2>5. Prueba de Acceso Directo</h2>";
if (isset($_SESSION['usuario'])) {
    echo "<p><a href='mis_actividades_v2.php' target='_blank'>Probar acceso a mis_actividades_v2.php</a></p>";
    echo "<p><a href='dashboard_advanced.php' target='_blank'>Probar acceso a dashboard_advanced.php</a></p>";
}

echo "<h2>6. Información del Servidor</h2>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";

// 7. Limpiar sesión de prueba
echo "<h2>7. Acciones</h2>";
echo "<p><a href='?clear_cache=1'>Limpiar caché de sesión</a></p>";
echo "<p><a href='index.php'>Volver al index</a></p>";

if (isset($_GET['clear_cache'])) {
    session_unset();
    session_destroy();
    session_start();
    echo "<p style='color: orange;'>🔄 Sesión limpiada. Recarga la página.</p>";
    echo "<meta http-equiv='refresh' content='2'>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
h1 { color: #333; }
h2 { color: #666; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
</style>
