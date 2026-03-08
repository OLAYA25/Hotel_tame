<?php
// scripts/test-router.php
echo "=== TEST DEL ROUTER - HOTEL TAME ===\n\n";

// Función para probar una URL
function testUrl($url, $descripcion) {
    echo "🔗 Probando: $url\n";
    echo "📝 $descripcion\n";
    
    // Simular la lógica del router
    $requestUri = $url;
    $basePath = '/Hotel_tame';
    $path = str_replace($basePath, '', $requestUri);
    $path = parse_url($path, PHP_URL_PATH);
    $path = rtrim($path, '/') ?: '/';
    
    // Mapeo de URLs del router
    $routeMap = [
        '/' => 'frontend/views/public/home.php',
        '/home' => 'frontend/views/public/home.php',
        '/portal-cliente' => 'frontend/views/public/portal_cliente.php',
        '/login' => 'frontend/views/public/login.php',
        '/habitaciones' => 'frontend/views/public/habitaciones.php',
        '/dashboard' => 'frontend/views/private/index.php',
        '/reservas' => 'frontend/views/private/reservas.php',
        '/clientes' => 'frontend/views/private/clientes.php',
        '/usuarios' => 'frontend/views/private/usuarios.php',
        '/contabilidad' => 'frontend/views/private/contabilidad.php',
        '/reportes' => 'frontend/views/private/reportes.php',
        '/settings' => 'frontend/views/private/settings.php',
        '/eventos' => 'frontend/views/private/eventos.php',
        '/productos' => 'frontend/views/private/productos.php',
        '/pedidos-productos' => 'frontend/views/private/pedidos_productos.php',
        '/dashboard-advanced' => 'frontend/views/private/dashboard_advanced.php',
        '/mis-actividades' => 'frontend/views/private/mis_actividades_v2.php'
    ];
    
    if (isset($routeMap[$path])) {
        $archivo = $routeMap[$path];
        if (file_exists(__DIR__ . '/../' . $archivo)) {
            echo "✅ Encontrado: $archivo\n";
        } else {
            echo "❌ Archivo no encontrado: $archivo\n";
        }
    } else {
        echo "❌ Ruta no mapeada: $path\n";
    }
    echo "\n";
}

// Probar URLs principales
testUrl('/Hotel_tame/', 'Página principal (home)');
testUrl('/Hotel_tame/home', 'Home (alternativa)');
testUrl('/Hotel_tame/portal-cliente', 'Portal de clientes');
testUrl('/Hotel_tame/login', 'Login');
testUrl('/Hotel_tame/habitaciones', 'Habitaciones');

// Probar URLs privadas
testUrl('/Hotel_tame/dashboard', 'Dashboard admin');
testUrl('/Hotel_tame/reservas', 'Gestión de reservas');
testUrl('/Hotel_tame/clientes', 'Gestión de clientes');
testUrl('/Hotel_tame/usuarios', 'Gestión de usuarios');
testUrl('/Hotel_tame/settings', 'Configuración');

// Probar URLs especiales
testUrl('/Hotel_tame/dashboard-advanced', 'Dashboard avanzado');
testUrl('/Hotel_tame/mis-actividades', 'Dashboard inteligente');

echo "=== VERIFICACIÓN DE ARCHIVOS CRÍTICOS ===\n\n";

// Verificar archivos críticos
$archivosCriticos = [
    'index.php' => 'Router principal',
    'frontend/views/public/home.php' => 'Home público',
    'frontend/views/public/login.php' => 'Login',
    'frontend/views/private/index.php' => 'Dashboard',
    'frontend/views/private/reservas.php' => 'Reservas',
    'backend/includes/header.php' => 'Header (crítico)',
    'backend/includes/sidebar.php' => 'Sidebar (crítico)',
    'backend/includes/footer.php' => 'Footer',
    'backend/config/database.php' => 'BD (crítico)',
    'backend/includes/auth_middleware.php' => 'Auth (crítico)',
    'assets/css/style.css' => 'Estilos principales',
    'assets/css/dashboard.css' => 'Estilos dashboard'
];

foreach ($archivosCriticos as $archivo => $descripcion) {
    if (file_exists(__DIR__ . '/../' . $archivo)) {
        echo "✅ $archivo → $descripcion\n";
    } else {
        echo "❌ $archivo → $descripcion (FALTA)\n";
    }
}

echo "\n=== PRÓXIMOS PASOS ===\n";
echo "1. Abrir navegador y probar las URLs manualmente\n";
echo "2. Verificar que todo se vea idéntico al original\n";
echo "3. Probar login y funcionalidades\n";
echo "4. Verificar APIs\n";
echo "5. Si todo funciona, ¡reestructuración completada!\n\n";

echo "🎯 REGLA FINAL: El usuario NO debe notar la diferencia.\n";
?>
