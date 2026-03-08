<?php
// Script de diagnóstico para problemas de memoria
ini_set('memory_limit', '256M'); // Aumentar límite temporalmente

echo "<h1>Diagnóstico de Memoria y Permisos</h1>";

// 1. Verificar uso de memoria actual
echo "<h2>1. Uso de Memoria</h2>";
$memory_usage = memory_get_usage(true);
$memory_limit = ini_get('memory_limit');
echo "<p>Memoria usada: " . number_format($memory_usage / 1024 / 1024, 2) . " MB</p>";
echo "<p>Límite de memoria: {$memory_limit}</p>";
echo "<p>Memoria pico: " . number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB</p>";

// 2. Verificar sesión
echo "<h2>2. Estado de Sesión</h2>";
session_start();
if (isset($_SESSION['usuario'])) {
    echo "<p style='color: green;'>✅ Sesión activa</p>";
    echo "<pre>";
    echo "ID: " . $_SESSION['usuario']['id'] . "\n";
    echo "Rol: " . $_SESSION['usuario']['rol'] . "\n";
    echo "Nombre: " . $_SESSION['usuario']['nombre'] . " " . $_SESSION['usuario']['apellido'] . "\n";
    echo "</pre>";
} else {
    echo "<p style='color: red;'>❌ No hay sesión activa</p>";
}

// 3. Probar sistema de permisos
echo "<h2>3. Sistema de Permisos</h2>";
if (isset($_SESSION['usuario'])) {
    require_once 'includes/simple_permissions.php';
    
    // Medir memoria antes de inicializar
    $memory_before = memory_get_usage(true);
    
    try {
        SimplePermissionHelper::initialize($_SESSION['usuario']['id']);
        $memory_after = memory_get_usage(true);
        $memory_used = $memory_after - $memory_before;
        
        echo "<p style='color: green;'>✅ Permisos inicializados correctamente</p>";
        echo "<p>Memoria usada en inicialización: " . number_format($memory_used / 1024, 2) . " KB</p>";
        
        // Probar acceso a módulos
        $modules_test = [
            'reportes.php',
            'contabilidad.php',
            'backup_manager.php',
            'mis_actividades_v2.php',
            'settings.php'
        ];
        
        echo "<h3>Prueba de Acceso a Módulos:</h3>";
        foreach ($modules_test as $modulo) {
            $acceso = SimplePermissionHelper::canAccessModule($modulo);
            $color = $acceso ? 'green' : 'red';
            $icono = $acceso ? '✅' : '❌';
            echo "<p style='color: {$color};'>{$icono} {$modulo}: " . ($acceso ? 'Permitido' : 'Denegado') . "</p>";
        }
        
        // Verificar permisos cargados
        $permisos = SimplePermissionHelper::getUserPermissions();
        echo "<h3>Permisos Cargados (" . count($permisos) . "):</h3>";
        echo "<pre>";
        foreach ($permisos as $clave => $permiso) {
            echo "- {$clave}: {$permiso['nombre']} ({$permiso['modulo']})\n";
        }
        echo "</pre>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en permisos: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ Se requiere sesión activa para probar permisos</p>";
}

// 4. Verificar configuración de PHP
echo "<h2>4. Configuración de PHP</h2>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Memory Limit: " . ini_get('memory_limit') . "</p>";
echo "<p>Max Execution Time: " . ini_get('max_execution_time') . "</p>";
echo "<p>Post Max Size: " . ini_get('post_max_size') . "</p>";
echo "<p>Upload Max Filesize: " . ini_get('upload_max_filesize') . "</p>";

// 5. Probar acceso directo a reportes.php
echo "<h2>5. Prueba de Acceso Directo</h2>";
echo "<p><a href='reportes.php' target='_blank'>Probar acceso a reportes.php</a></p>";
echo "<p><a href='backup_manager.php' target='_blank'>Probar acceso a backup_manager.php</a></p>";
echo "<p><a href='settings.php' target='_blank'>Probar acceso a settings.php</a></p>";

// 6. Limpiar memoria y mostrar estado final
echo "<h2>6. Estado Final</h2>";
gc_collect_cycles();
$final_memory = memory_get_usage(true);
echo "<p>Memoria final: " . number_format($final_memory / 1024 / 1024, 2) . " MB</p>";
echo "<p>Memoria pico final: " . number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB</p>";

// 7. Recomendaciones
echo "<h2>7. Recomendaciones</h2>";
if ($final_memory > 50 * 1024 * 1024) { // 50MB
    echo "<p style='color: red;'>⚠️ Uso de memoria elevado. Considera optimizar las consultas.</p>";
} else {
    echo "<p style='color: green;'>✅ Uso de memoria dentro de límites normales.</p>";
}

echo "<p><a href='index.php'>Volver al index</a></p>";

// Estilos
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
h1 { color: #333; }
h2 { color: #666; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
h3 { color: #888; }
</style>";
?>
