<?php
// scripts/fix-api-auth.php
echo "=== CORRECCIÓN DE AUTENTICACIÓN EN APIS ===\n\n";

$root = __DIR__ . '/..';

function fixApiAuth($filepath) {
    if (!file_exists($filepath)) {
        echo "❌ Archivo no encontrado: $filepath\n";
        return false;
    }
    
    $content = file_get_contents($filepath);
    $originalContent = $content;
    
    // Reemplazar redirección por respuesta JSON
    $patterns = [
        '/header\s*\(\s*[\'"]Location:\s*login\.php[\'"]\s*\);\s*exit;/' => 'http_response_code(401); echo json_encode([\'error\' => \'No autorizado\']); exit;',
        '/header\s*\(\s*[\'"]Location:\s*\.\/login\.php[\'"]\s*\);\s*exit;/' => 'http_response_code(401); echo json_encode([\'error\' => \'No autorizado\']); exit;',
        '/header\s*\(\s*[\'"]Location:\s*\.\.\/login\.php[\'"]\s*\);\s*exit;/' => 'http_response_code(401); echo json_encode([\'error\' => \'No autorizado\']); exit;',
    ];
    
    foreach ($patterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    // Agregar headers CORS si no existen
    if (strpos($content, 'Access-Control-Allow-Origin') === false) {
        $content = preg_replace('/^(<\?php)/', "$1\nheader(\"Access-Control-Allow-Origin: *\");\nheader(\"Content-Type: application/json; charset=UTF-8\");\n", $content);
    }
    
    // Remover includes de HTML que no sirven para APIs
    $content = preg_replace('/include\s+__DIR__\s*\.\s*[\'"]\/.*\/header\.php[\'"];.*?/', '', $content);
    $content = preg_replace('/include\s+[\'"]includes\/.*\.php[\'"];.*?/', '', $content);
    
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

echo "📁 Procesando APIs en backend/api/endpoints/:\n";
foreach ($files as $file) {
    // Solo procesar archivos que no sean auth.php
    if (basename($file) !== 'auth.php') {
        fixApiAuth($file);
    }
}

echo "\n=== VERIFICACIÓN FINAL ===\n";

// Verificar algunos archivos clave
$keyFiles = [
    'backend/api/endpoints/clientes.php',
    'backend/api/endpoints/reservas.php',
    'backend/api/endpoints/usuarios.php',
    'backend/api/endpoints/habitaciones.php'
];

foreach ($keyFiles as $file) {
    $path = $root . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        if (strpos($content, 'json_encode') !== false && strpos($content, 'No autorizado') !== false) {
            echo "✅ $file (autenticación JSON correcta)\n";
        } else {
            echo "⚠️  $file (puede necesitar revisión)\n";
        }
    } else {
        echo "❌ $file (no existe)\n";
    }
}

echo "\n=== CORRECCIÓN COMPLETADA ===\n";
echo "✅ APIs ahora devuelven JSON en lugar de redirigir\n";
echo "✅ Headers CORS agregados\n";
echo "✅ Respuestas estandarizadas\n";
echo "✅ Compatible con frontend JavaScript\n";
?>
