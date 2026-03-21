<?php
// index.php - Router principal mejorado
session_start();

// Definir constante base para el proyecto
define('HOTEL_TAME_ROOT', __DIR__);

// Mapeo de URLs a archivos PHP ORIGINALES
$routeMap = [
    '/' => 'frontend/views/public/home.php',
    '/home' => 'frontend/views/public/home.php',
    '/home.php' => 'frontend/views/public/home.php',
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
    '/mis-actividades' => 'frontend/views/private/mis_actividades.php',
    '/logout' => 'logout.php',
    '/turnos' => 'frontend/views/private/turnos.php',
    '/espacios-eventos' => 'frontend/views/private/espacios_eventos.php',
    '/reservas-eventos' => 'frontend/views/private/reservas_eventos.php',
    '/informe-huespedes' => 'frontend/views/private/informe_huespedes.php',
    '/backup-manager' => 'frontend/views/private/backup_manager.php',
    '/reservas-online' => 'frontend/views/public/reservas_online.php',
    '/reserva-form' => 'frontend/views/extra-public/reserva_form.php',
    '/reserva-confirmacion' => 'frontend/views/extra-public/reserva_confirmacion.php',
    '/informe-ocupacion' => 'frontend/views/extra-public/informe_ocupacion_real.php',
    '/index-simple' => 'frontend/views/extra-public/index_simple.php',
    '/roles' => 'frontend/views/extra-private/roles.php',
    '/tareas-limpieza' => 'frontend/views/extra-private/tareas_limpieza.php',
    '/standalone' => 'frontend/views/extra-private/standalone.php'
];

// Mapeo de APIs
$apiMap = [
    '/api/clientes' => 'api/endpoints/clientes.php',
    '/api/reservas' => 'api/endpoints/reservas.php',
    '/api/habitaciones' => 'api/endpoints/habitaciones.php',
    '/api/usuarios' => 'api/endpoints/usuarios.php',
    '/api/notifications' => 'api/endpoints/notifications.php',
    '/api/widgets' => 'api/endpoints/widgets.php',
    '/api/settings' => 'api/endpoints/settings.php'
];

// Función para obtener la ruta actual
function getRequestPath() {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $basePath = '/Hotel_tame';
    $path = str_replace($basePath, '', $requestUri);
    $path = parse_url($path, PHP_URL_PATH);
    return rtrim($path, '/') ?: '/';
}

// Función para incluir archivo con configuración previa
function includeWithConfig($filepath) {
    // Establecer constantes para que las vistas encuentren sus dependencias
    define('BACKEND_ROOT', HOTEL_TAME_ROOT . '/backend');
    define('ASSETS_URL', '/Hotel_tame/assets');
    
    // Incluir el archivo
    require_once $filepath;
}

// Obtener ruta solicitada
$path = getRequestPath();

// Manejar APIs
if (strpos($path, '/api/') === 0) {
    if (isset($apiMap[$path]) && file_exists(HOTEL_TAME_ROOT . '/' . $apiMap[$path])) {
        require_once HOTEL_TAME_ROOT . '/' . $apiMap[$path];
        exit;
    }
    http_response_code(404);
    echo json_encode(['error' => 'API endpoint not found']);
    exit;
}

// Manejar vistas
if (isset($routeMap[$path]) && file_exists(HOTEL_TAME_ROOT . '/' . $routeMap[$path])) {
    includeWithConfig(HOTEL_TAME_ROOT . '/' . $routeMap[$path]);
    exit;
}

// Si no encuentra, verificar si existe archivo directo
$directFile = HOTEL_TAME_ROOT . '/' . ltrim($path, '/');
if (file_exists($directFile) && is_file($directFile)) {
    require_once $directFile;
    exit;
}

// 404
http_response_code(404);
echo "<h1>Página no encontrada</h1><p>La ruta '$path' no existe.</p>";
?>
