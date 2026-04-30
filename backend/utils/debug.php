<?php
// Deshabilitar toda salida de búfer para ver errores inmediatos
if (ob_get_level()) ob_end_clean();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<!DOCTYPE html><html><head><title>DEBUG</title></head><body>";
echo "<h1>🔍 DEBUG INMEDIATO</h1>";

try {
    echo "<p>✅ Paso 1: PHP funciona</p>";
    
    // Verificar sesión
    echo "<p>🔍 Paso 2: Verificando sesión...</p>";
    session_start();
    echo "<p>✅ Sesión iniciada</p>";
    
    if (isset($_SESSION['usuario'])) {
        echo "<p>✅ Usuario en sesión: " . $_SESSION['usuario']['nombre'] . "</p>";
    } else {
        echo "<p>❌ No hay usuario en sesión</p>";
        // Simular usuario para prueba
        $_SESSION['usuario'] = [
            'nombre' => 'Admin',
            'apellido' => 'Test',
            'rol' => 'admin'
        ];
        echo "<p>🔧 Usuario simulado creado</p>";
    }
    
    // Verificar archivo de configuración
    echo "<p>🔍 Paso 3: Verificando config/database.php...</p>";
    if (file_exists('config/database.php')) {
        echo "<p>✅ config/database.php existe</p>";
        require_once __DIR__ . '/../../config/database.php';
        echo "<p>✅ config/database.php cargado</p>";
        
        // Probar conexión
        echo "<p>🔍 Paso 4: Probando conexión BD...</p>";
        $database = new Database();
        $db = $database->getConnection();
        if ($db) {
            echo "<p>✅ Conexión BD exitosa</p>";
        } else {
            echo "<p>❌ Error en conexión BD</p>";
        }
    } else {
        echo "<p>❌ config/database.php no existe</p>";
    }
    
    // Verificar includes
    echo "<p>🔍 Paso 5: Verificando includes...</p>";
    $includes = [
        'includes/header_simple.php',
        'includes/sidebar_simple.php',
        'includes/footer_simple.php'
    ];
    
    foreach ($includes as $include) {
        if (file_exists($include)) {
            echo "<p>✅ $include existe</p>";
        } else {
            echo "<p>❌ $include no existe</p>";
        }
    }
    
    // Intentar cargar una página simple
    echo "<p>🔍 Paso 6: Intentando cargar página simple...</p>";
    
    echo "<div style='background: #f0f0f0; padding: 20px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h2>🏨 Hotel Management System</h2>";
    echo "<p>Usuario: " . $_SESSION['usuario']['nombre'] . " " . $_SESSION['usuario']['apellido'] . "</p>";
    echo "<p>Rol: " . $_SESSION['usuario']['rol'] . "</p>";
    echo "<p>Fecha: " . date('Y-m-d H:i:s') . "</p>";
    echo "<p>PHP Version: " . phpversion() . "</p>";
    
    // Test de tabla
    echo "<table border='1' style='width: 100%; margin: 20px 0;'>";
    echo "<tr><th>Módulo</th><th>Estado</th><th>Acción</th></tr>";
    echo "<tr><td>Dashboard</td><td>✅ Activo</td><td><a href='?page=dashboard'>Ir</a></td></tr>";
    echo "<tr><td>Contabilidad</td><td>✅ Activo</td><td><a href='?page=contabilidad'>Ir</a></td></tr>";
    echo "<tr><td>Habitaciones</td><td>✅ Activo</td><td><a href='?page=habitaciones'>Ir</a></td></tr>";
    echo "</table>";
    echo "</div>";
    
    // Test JavaScript
    echo "<script>";
    echo "console.log('✅ JavaScript funciona');";
    echo "document.addEventListener('DOMContentLoaded', function() {";
    echo "    console.log('✅ DOM cargado');";
    echo "    document.body.style.backgroundColor = '#f0fff0';";
    echo "});";
    echo "</script>";
    
    echo "<p>✅ Todos los pasos completados</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
} catch (Error $e) {
    echo "<p style='color: red;'>❌ Error Fatal: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
}

echo "</body></html>";
?>
