<?php
// scripts/actualizar-includes.php
echo "=== ACTUALIZANDO INCLUDES EN ARCHIVOS PHP ===\n\n";

$root = __DIR__ . '/..';

function updateIncludesInFile($filepath) {
    if (!file_exists($filepath)) {
        echo "✗ Archivo no encontrado: $filepath\n";
        return false;
    }
    
    $content = file_get_contents($filepath);
    
    // Actualizar includes
    $patterns = [
        '/include.*[\'"]\.\.\/includes\//' => 'include __DIR__ . \'/../../includes/\'',
        '/require.*[\'"]\.\.\/config\//' => 'require __DIR__ . \'/../../config/\'',
        '/include_once.*[\'"]\.\.\/lib\//' => 'include_once __DIR__ . \'/../../lib/\'',
        '/include.*[\'"]\.\.\/\.\.\/includes\//' => 'include __DIR__ . \'/../../includes/\'',
        '/require.*[\'"]\.\.\/\.\.\/config\//' => 'require __DIR__ . \'/../../config/\'',
        '/include_once.*[\'"]\.\.\/\.\.\/lib\//' => 'include_once __DIR__ . \'/../../lib/\'',
    ];
    
    $originalContent = $content;
    foreach ($patterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    if ($content !== $originalContent) {
        file_put_contents($filepath, $content);
        echo "✓ Actualizado: " . basename($filepath) . "\n";
        return true;
    }
    
    return false;
}

// Recorrer todos los archivos PHP en backend/api/endpoints/
$files = glob($root . '/backend/api/endpoints/*.php');
foreach ($files as $file) {
    updateIncludesInFile($file);
}

// Actualizar archivos en backend/utils/
$utilsFiles = glob($root . '/backend/utils/*.php');
foreach ($utilsFiles as $file) {
    updateIncludesInFile($file);
}

echo "\n=== VERIFICACIÓN DE RUTAS ACTUALIZADAS ===\n";

// Verificar algunos archivos clave
$checkFiles = [
    'backend/api/endpoints/clientes.php',
    'backend/api/endpoints/reservas.php',
    'backend/api/endpoints/usuarios.php',
    'backend/api/endpoints/notifications.php',
    'backend/api/endpoints/widgets.php'
];

foreach ($checkFiles as $file) {
    $path = $root . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        if (strpos($content, '../../includes/') !== false || strpos($content, '../../config/') !== false) {
            echo "✓ Actualizado: $file\n";
        } else {
            echo "? Sin cambios: $file\n";
        }
    } else {
        echo "✗ No encontrado: $file\n";
    }
}

echo "\n=== ACTUALIZACIÓN DE INCLUDES COMPLETADA ===\n";
?>
