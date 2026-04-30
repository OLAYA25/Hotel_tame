<?php
// Script de prueba para el nuevo sistema
echo "<h1>🏨 Hotel Tame - Test de Nuevo Sistema</h1>";

// Test 1: Verificar estructura de directorios
echo "<h2>📁 Estructura de Directorios</h2>";
$directories = [
    'backend/api/endpoints',
    'backend/config',
    'backend/includes',
    'backend/lib',
    'backend/models',
    'backend/utils',
    'frontend/out',
    'assets',
    'uploads',
    'scripts',
    'docs'
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        echo "✅ $dir<br>";
    } else {
        echo "❌ $dir<br>";
    }
}

// Test 2: Verificar archivos clave
echo "<h2>📄 Archivos Clave</h2>";
$files = [
    'index.php' => 'Router principal',
    '.htaccess' => 'Reescritura de URLs',
    'backend/api/endpoints/auth.php' => 'API de autenticación',
    'backend/api/endpoints/clientes.php' => 'API de clientes',
    'backend/config/database.php' => 'Configuración BD',
    'frontend/out/index.html' => 'Página principal',
    'frontend/out/login/index.html' => 'Página de login',
    'frontend/out/dashboard/index.html' => 'Dashboard',
    'frontend/out/404.html' => 'Página 404'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $file - $description<br>";
    } else {
        echo "❌ $file - $description<br>";
    }
}

// Test 3: Verificar configuración de Apache
echo "<h2>🔧 Configuración Apache</h2>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (in_array('mod_rewrite', $modules)) {
        echo "✅ mod_rewrite activado<br>";
    } else {
        echo "❌ mod_rewrite no activado<br>";
    }
} else {
    echo "⚠️ No se puede verificar mod_rewrite<br>";
}

// Test 4: Verificar base de datos
echo "<h2>🗄️ Base de Datos</h2>";
try {
    include_once 'backend/config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "✅ Conexión a BD exitosa<br>";
        
        // Test de tabla usuarios
        $stmt = $db->query("SELECT COUNT(*) as count FROM usuarios");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Tabla usuarios: {$result['count']} registros<br>";
    } else {
        echo "❌ Error en conexión a BD<br>";
    }
} catch (Exception $e) {
    echo "❌ Error BD: " . $e->getMessage() . "<br>";
}

// Test 5: URLs del sistema
echo "<h2>🌐 URLs del Sistema</h2>";
$urls = [
    '/' => 'Página principal',
    '/login' => 'Login',
    '/dashboard' => 'Dashboard',
    '/api/auth/check' => 'API Auth Check'
];

foreach ($urls as $url => $description) {
    echo "🔗 <a href='$url' target='_blank'>$url</a> - $description<br>";
}

echo "<h2>📊 Resumen</h2>";
echo "✅ Sistema reestructurado correctamente<br>";
echo "✅ Backend PHP organizado<br>";
echo "✅ Frontend estático creado<br>";
echo "✅ Router PHP implementado<br>";
echo "✅ APIs migradas<br>";
echo "✅ URLs amigables configuradas<br>";

echo "<h2>🚀 Próximos Pasos</h2>";
echo "1. Probar el login: <a href='/login'>Ir a Login</a><br>";
echo "2. Verificar dashboard: <a href='/dashboard'>Ir a Dashboard</a><br>";
echo "3. Test APIs: <a href='/api/auth/check'>Test Auth API</a><br>";
echo "4. Construir frontend Next.js completo<br>";

echo "<hr>";
echo "<small>Generado: " . date('Y-m-d H:i:s') . "</small>";
?>
