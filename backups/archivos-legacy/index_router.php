<?php
// index.php - Router principal
session_start();

// Definir rutas públicas que sirven archivos estáticos de Next.js
$staticRoutes = [
    '/' => 'frontend/out/index.html',
    '/habitaciones' => 'frontend/out/habitaciones/index.html',
    '/portal-cliente' => 'frontend/out/portal-cliente/index.html',
    '/contacto' => 'frontend/out/contacto/index.html',
    '/login' => 'frontend/out/login/index.html',
    '/register' => 'frontend/out/register/index.html',
    '/about' => 'frontend/out/about/index.html',
];

// Definir rutas del dashboard (protegidas)
$dashboardRoutes = [
    '/dashboard' => 'frontend/out/dashboard/index.html',
    '/dashboard/reservas' => 'frontend/out/dashboard/reservas/index.html',
    '/dashboard/clientes' => 'frontend/out/dashboard/clientes/index.html',
    '/dashboard/usuarios' => 'frontend/out/dashboard/usuarios/index.html',
    '/dashboard/contabilidad' => 'frontend/out/dashboard/contabilidad/index.html',
    '/dashboard/habitaciones' => 'frontend/out/dashboard/habitaciones/index.html',
    '/dashboard/eventos' => 'frontend/out/dashboard/eventos/index.html',
    '/dashboard/productos' => 'frontend/out/dashboard/productos/index.html',
    '/dashboard/reportes' => 'frontend/out/dashboard/reportes/index.html',
    '/dashboard/mis-actividades' => 'frontend/out/dashboard/mis-actividades/index.html',
];

// Definir rutas de API (redirigen a backend PHP)
$apiRoutes = [
    '/api/clientes' => 'backend/api/endpoints/clientes.php',
    '/api/reservas' => 'backend/api/endpoints/reservas.php',
    '/api/usuarios' => 'backend/api/endpoints/usuarios.php',
    '/api/habitaciones' => 'backend/api/endpoints/habitaciones.php',
    '/api/eventos' => 'backend/api/endpoints/eventos.php',
    '/api/productos' => 'backend/api/endpoints/productos.php',
    '/api/auth' => 'backend/api/endpoints/auth.php',
    '/api/notifications' => 'backend/api/endpoints/notifications.php',
    '/api/widgets' => 'backend/api/endpoints/widgets.php',
];

// Obtener la ruta solicitada
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = dirname($scriptName);

// Eliminar el base path y query string
$path = str_replace($basePath, '', $requestUri);
$path = parse_url($path, PHP_URL_PATH);
$path = rtrim($path, '/') ?: '/';

// Función para verificar autenticación en dashboard
function checkDashboardAuth() {
    if (!isset($_SESSION['usuario'])) {
        header('Location: /Hotel_tame/login');
        exit;
    }
}

// Rutas de API
if (strpos($path, '/api/') === 0) {
    $apiFile = $apiRoutes[$path] ?? null;
    if ($apiFile && file_exists(__DIR__ . '/' . $apiFile)) {
        require_once __DIR__ . '/' . $apiFile;
        exit;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'API endpoint not found']);
        exit;
    }
}

// Rutas del dashboard (protegidas)
if (strpos($path, '/dashboard') === 0) {
    checkDashboardAuth();
    $dashboardFile = $dashboardRoutes[$path] ?? null;
    if ($dashboardFile && file_exists(__DIR__ . '/' . $dashboardFile)) {
        readfile(__DIR__ . '/' . $dashboardFile);
        exit;
    } else {
        // Intentar con ruta anidada
        $cleanPath = str_replace('/dashboard/', '', $path);
        $possibleFile = 'frontend/out/dashboard/' . $cleanPath . '/index.html';
        if (file_exists(__DIR__ . '/' . $possibleFile)) {
            readfile(__DIR__ . '/' . $possibleFile);
            exit;
        }
    }
}

// Rutas públicas estáticas
if (isset($staticRoutes[$path])) {
    if (file_exists(__DIR__ . '/' . $staticRoutes[$path])) {
        readfile(__DIR__ . '/' . $staticRoutes[$path]);
        exit;
    }
}

// Servir archivos estáticos (CSS, JS, imágenes)
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2)$/', $path)) {
    $staticFile = __DIR__ . '/frontend/out' . $path;
    if (file_exists($staticFile)) {
        $mimeType = mime_content_type($staticFile);
        header('Content-Type: ' . $mimeType);
        readfile($staticFile);
        exit;
    }
}

// Si no se encuentra, mostrar 404 personalizado
http_response_code(404);
readfile(__DIR__ . '/frontend/out/404.html');
?>
