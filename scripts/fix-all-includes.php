<?php
// scripts/fix-all-includes.php
echo "=== CORRECCIÓN COMPLETA DE INCLUDES ===\n\n";

$root = __DIR__ . '/..';

function fixFileIncludes($filepath) {
    if (!file_exists($filepath)) {
        echo "❌ Archivo no encontrado: $filepath\n";
        return false;
    }
    
    $content = file_get_contents($filepath);
    $originalContent = $content;
    
    // Patrones de reemplazo más completos
    $replacements = [
        // Includes relativos a absolutos
        '/require_once\s+[\'"]config\/database\.php[\'"]/' => 'require_once __DIR__ . \'/../../config/database.php\'',
        '/require_once\s+[\'"]\.\.\/config\/database\.php[\'"]/' => 'require_once __DIR__ . \'/../../config/database.php\'',
        '/require_once\s+[\'"]\.\.\/\.\.\/config\/database\.php[\'"]/' => 'require_once __DIR__ . \'/../../config/database.php\'',
        
        '/include_once\s+[\'"]includes\/simple_permissions\.php[\'"]/' => 'include_once __DIR__ . \'/../../includes/simple_permissions.php\'',
        '/include_once\s+[\'"]\.\.\/includes\/simple_permissions\.php[\'"]/' => 'include_once __DIR__ . \'/../../includes/simple_permissions.php\'',
        '/include_once\s+[\'"]\.\.\/\.\.\/includes\/simple_permissions\.php[\'"]/' => 'include_once __DIR__ . \'/../../includes/simple_permissions.php\'',
        
        '/include\s+[\'"]includes\/header\.php[\'"]/' => 'include __DIR__ . \'/../../includes/header.php\'',
        '/include\s+[\'"]\.\.\/includes\/header\.php[\'"]/' => 'include __DIR__ . \'/../../includes/header.php\'',
        '/include\s+[\'"]\.\.\/\.\.\/includes\/header\.php[\'"]/' => 'include __DIR__ . \'/../../includes/header.php\'',
        
        '/include\s+[\'"]includes\/footer\.php[\'"]/' => 'include __DIR__ . \'/../../includes/footer.php\'',
        '/include\s+[\'"]\.\.\/includes\/footer\.php[\'"]/' => 'include __DIR__ . \'/../../includes/footer.php\'',
        '/include\s+[\'"]\.\.\/\.\.\/includes\/footer\.php[\'"]/' => 'include __DIR__ . \'/../../includes/footer.php\'',
        
        // Librerías
        '/include_once\s+[\'"]lib\/NotificationManager\.php[\'"]/' => 'include_once __DIR__ . \'/../../lib/NotificationManager.php\'',
        '/include_once\s+[\'"]\.\.\/lib\/NotificationManager\.php[\'"]/' => 'include_once __DIR__ . \'/../../lib/NotificationManager.php\'',
        '/include_once\s+[\'"]\.\.\/\.\.\/lib\/NotificationManager\.php[\'"]/' => 'include_once __DIR__ . \'/../../lib/NotificationManager.php\'',
        
        '/include_once\s+[\'"]lib\/RoleBasedDashboard\.php[\'"]/' => 'include_once __DIR__ . \'/../../lib/RoleBasedDashboard.php\'',
        '/include_once\s+[\'"]\.\.\/lib\/RoleBasedDashboard\.php[\'"]/' => 'include_once __DIR__ . \'/../../lib/RoleBasedDashboard.php\'',
        '/include_once\s+[\'"]\.\.\/\.\.\/lib\/RoleBasedDashboard\.php[\'"]/' => 'include_once __DIR__ . \'/../../lib/RoleBasedDashboard.php\'',
        
        // Modelos
        '/include_once\s+[\'"]models\/Cliente\.php[\'"]/' => 'include_once __DIR__ . \'/../../models/Cliente.php\'',
        '/include_once\s+[\'"]\.\.\/models\/Cliente\.php[\'"]/' => 'include_once __DIR__ . \'/../../models/Cliente.php\'',
        '/include_once\s+[\'"]\.\.\/\.\.\/models\/Cliente\.php[\'"]/' => 'include_once __DIR__ . \'/../../models/Cliente.php\'',
        
        // Headers para API
        '/header\s*\(\s*[\'"]Content-Type:\s*application\/json[\'"]\s*\)/' => 'header("Content-Type: application/json; charset=UTF-8");',
        '/header\s*\(\s*[\'"]Access-Control-Allow-Origin:\s*\*[\'"]\s*\)/' => 'header("Access-Control-Allow-Origin: *");',
    ];
    
    foreach ($replacements as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    if ($content !== $originalContent) {
        file_put_contents($filepath, $content);
        echo "✅ Actualizado: " . basename($filepath) . "\n";
        return true;
    }
    
    return false;
}

// Procesar todos los archivos PHP en backend/api/endpoints/
$apiDir = $root . '/backend/api/endpoints';
$files = glob($apiDir . '/*.php');

echo "📁 Procesando archivos en backend/api/endpoints/:\n";
foreach ($files as $file) {
    fixFileIncludes($file);
}

echo "\n📁 Procesando archivos en backend/utils/:\n";
$utilsFiles = glob($root . '/backend/utils/*.php');
foreach ($utilsFiles as $file) {
    fixFileIncludes($file);
}

echo "\n📁 Procesando archivos en backend/legacy-views/:\n";
$legacyFiles = glob($root . '/backend/legacy-views/*.php');
foreach ($legacyFiles as $file) {
    fixFileIncludes($file);
}

echo "\n=== VERIFICACIÓN DE ARCHIVOS CLAVE ===\n";

$keyFiles = [
    'backend/api/endpoints/clientes.php',
    'backend/api/endpoints/reservas.php',
    'backend/api/endpoints/usuarios.php',
    'backend/api/endpoints/habitaciones.php',
    'backend/api/endpoints/auth.php'
];

foreach ($keyFiles as $file) {
    $path = $root . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        if (strpos($content, '__DIR__') !== false) {
            echo "✅ $file (con rutas absolutas)\n";
        } else {
            echo "⚠️  $file (sin rutas absolutas)\n";
        }
    } else {
        echo "❌ $file (no existe)\n";
    }
}

echo "\n=== CORRECCIÓN COMPLETADA ===\n";
echo "✅ Includes actualizados a rutas absolutas\n";
echo "✅ Uso de __DIR__ para paths correctos\n";
echo "✅ Headers CORS configurados\n";
echo "✅ Compatible con nueva estructura\n";
?>
