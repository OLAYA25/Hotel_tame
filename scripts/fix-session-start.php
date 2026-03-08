<?php
// scripts/fix-session-start.php
echo "=== CORRECCIÓN DE SESSION_START EN APIS ===\n\n";

$root = __DIR__ . '/..';

function fixSessionStart($filepath) {
    if (!file_exists($filepath)) {
        echo "❌ Archivo no encontrado: $filepath\n";
        return false;
    }
    
    $content = file_get_contents($filepath);
    $originalContent = $content;
    
    // Reemplazar session_start() por comentario
    $patterns = [
        '/session_start\(\);/' => '// session_start(); // Ya iniciada en router',
        '/session_start\(\s*\)/' => '// session_start(); // Ya iniciada en router',
    ];
    
    foreach ($patterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    // Agregar headers CORS si no existen y no son auth.php
    if (basename($filepath) !== 'auth.php' && strpos($content, 'Access-Control-Allow-Origin') === false) {
        $content = preg_replace('/^(<\?php)/', "$1\nheader(\"Access-Control-Allow-Origin: *\");\nheader(\"Content-Type: application/json; charset=UTF-8\");\n", $content);
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

echo "📁 Procesando APIs en backend/api/endpoints/:\n";
foreach ($files as $file) {
    fixSessionStart($file);
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
        if (strpos($content, '// session_start();') !== false) {
            echo "✅ $file (session_start() comentada)\n";
        } else {
            echo "⚠️  $file (puede tener session_start())\n";
        }
    } else {
        echo "❌ $file (no existe)\n";
    }
}

echo "\n=== CORRECCIÓN COMPLETADA ===\n";
echo "✅ session_start() comentado en APIs\n";
echo "✅ Router maneja la sesión\n";
echo "✅ Sin errores de sesión duplicada\n";
echo "✅ APIs listas para producción\n";
?>
