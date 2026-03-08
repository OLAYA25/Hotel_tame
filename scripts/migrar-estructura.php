#!/usr/bin/php
<?php
// scripts/migrar-estructura.php
echo "=== INICIANDO MIGRACIÓN DE ESTRUCTURA ===\n\n";

$root = __DIR__ . '/..';
$newBackend = $root . '/backend';
$newFrontend = $root . '/frontend';

// Crear estructura de directorios
$directories = [
    'backend/api/endpoints',
    'backend/config',
    'backend/includes',
    'backend/lib',
    'backend/models',
    'backend/utils',
    'backend/legacy-views',
    'frontend/out',
    'assets/css',
    'assets/images',
    'assets/js',
    'uploads/habitaciones',
    'uploads/products',
    'uploads/events',
    'scripts',
    'docs',
    'backups/archivos-legacy'
];

foreach ($directories as $dir) {
    $path = $root . '/' . $dir;
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
        echo "✓ Creado: $path\n";
    } else {
        echo "○ Ya existe: $path\n";
    }
}

echo "\n=== VERIFICANDO ESTRUCTURA ===\n";

// Verificar estructura final
$requiredDirs = [
    'backend/api/endpoints',
    'backend/config',
    'backend/includes', 
    'backend/lib',
    'backend/models',
    'backend/utils',
    'backend/legacy-views',
    'frontend/out',
    'assets',
    'uploads',
    'scripts',
    'docs',
    'backups/archivos-legacy'
];

foreach ($requiredDirs as $dir) {
    $path = $root . '/' . $dir;
    if (is_dir($path)) {
        echo "✓ Directorio OK: $dir\n";
    } else {
        echo "✗ FALTANTE: $dir\n";
    }
}

echo "\n=== VERIFICANDO ARCHIVOS CLAVE ===\n";

$checkFiles = [
    'backend/api/endpoints/clientes.php',
    'backend/api/endpoints/reservas.php',
    'backend/api/endpoints/usuarios.php',
    'backend/config/database.php',
    'backend/includes/simple_permissions.php',
    'backend/lib/RoleBasedDashboard.php',
    'index_router.php',
    'next.config.mjs',
    '.htaccess_new'
];

foreach ($checkFiles as $file) {
    $path = $root . '/' . $file;
    if (file_exists($path)) {
        echo "✓ Archivo OK: $file\n";
    } else {
        echo "✗ FALTANTE: $file\n";
    }
}

echo "\n=== ESTRUCTURA FINAL ===\n";
echo "Raíz del proyecto: $root\n";
echo "Backend: $newBackend\n";
echo "Frontend: $newFrontend\n";

echo "\n=== PRÓXIMOS PASOS ===\n";
echo "1. Construir frontend Next.js: cd frontend && npm run build\n";
echo "2. Activar nuevo router: mv index.php index_old.php && mv index_router.php index.php\n";
echo "3. Activar nuevo .htaccess: mv .htaccess .htaccess_old && mv .htaccess_new .htaccess\n";
echo "4. Probar URLs: http://localhost/Hotel_tame/\n";

echo "\n=== MIGRACIÓN COMPLETADA ===\n";
?>
