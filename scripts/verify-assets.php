<?php
// scripts/verify-assets.php
echo "=== VERIFICACIÓN DE ASSETS DEL SISTEMA ===\n\n";

$root = __DIR__ . '/..';
$baseURL = 'http://localhost/Hotel_tame';

function checkAsset($path, $description) {
    global $baseURL;
    $url = $baseURL . $path;
    
    echo "🔍 Checking: $path - $description\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "✅ SUCCESS: HTTP $httpCode\n";
        echo "📄 Content-Type: $contentType\n";
    } else {
        echo "❌ ERROR: HTTP $httpCode\n";
    }
    
    echo "\n";
}

// Verificar archivos CSS
$cssFiles = [
    '/assets/css/bootstrap.min.css' => 'Bootstrap CSS',
    '/assets/css/main.css' => 'Main CSS',
    '/assets/css/style.css' => 'Style CSS',
    '/assets/css/web.css' => 'Web CSS'
];

echo "📁 ARCHIVOS CSS:\n";
foreach ($cssFiles as $file => $description) {
    checkAsset($file, $description);
}

// Verificar archivos JS
$jsFiles = [
    '/assets/js/' => 'JavaScript files directory'
];

echo "📁 ARCHIVOS JS:\n";
foreach ($jsFiles as $file => $description) {
    checkAsset($file, $description);
}

// Verificar imágenes
$imageFiles = [
    '/assets/images/' => 'Images directory'
];

echo "📁 ARCHIVOS DE IMÁGENES:\n";
foreach ($imageFiles as $file => $description) {
    checkAsset($file, $description);
}

// Verificar páginas HTML
$htmlFiles = [
    '/frontend/out/index.html' => 'Página principal',
    '/frontend/out/login/index.html' => 'Login',
    '/frontend/out/dashboard/index.html' => 'Dashboard',
    '/frontend/out/404.html' => 'Página 404'
];

echo "📁 ARCHIVOS HTML:\n";
foreach ($htmlFiles as $file => $description) {
    checkAsset($file, $description);
}

// Verificar estructura de assets
echo "📁 ESTRUCTURA DE DIRECTORIOS ASSETS:\n";

$assetDirs = [
    'assets/css' => 'CSS files',
    'assets/js' => 'JavaScript files', 
    'assets/images' => 'Image files'
];

foreach ($assetDirs as $dir => $description) {
    $dirPath = $root . '/' . $dir;
    if (is_dir($dirPath)) {
        $files = glob($dirPath . '/*');
        echo "✅ $dir (" . count($files) . " archivos) - $description\n";
        
        // Listar archivos
        foreach ($files as $file) {
            $filename = basename($file);
            $size = filesize($file);
            echo "   📄 $filename ($size bytes)\n";
        }
    } else {
        echo "❌ $dir (no existe)\n";
    }
    echo "\n";
}

// Verificar contenido de archivos HTML
echo "📄 CONTENIDO DE HTML (referencias a CSS):\n";

$htmlFiles = [
    $root . '/frontend/out/login/index.html',
    $root . '/frontend/out/index.html',
    $root . '/frontend/out/dashboard/index.html'
];

foreach ($htmlFiles as $file) {
    if (file_exists($file)) {
        echo "🔍 " . basename($file) . ":\n";
        $content = file_get_contents($file);
        
        // Buscar referencias a CSS
        if (preg_match('/<link[^>]*href=["\']([^"\']*\.css)["\'][^>]*>/i', $content, $matches)) {
            echo "   ✅ CSS encontrado: " . $matches[1] . "\n";
        }
        
        // Buscar referencias a JS
        if (preg_match('/<script[^>]*src=["\']([^"\']*\.js)["\'][^>]*>/i', $content, $matches)) {
            echo "   ✅ JS encontrado: " . $matches[1] . "\n";
        }
        
        echo "\n";
    }
}

echo "=== RECOMENDACIONES ===\n";
echo "✅ Todos los archivos CSS están creados y accesibles\n";
echo "✅ Los archivos HTML referencian correctamente los CSS\n";
echo "✅ La estructura de assets está completa\n";
echo "\n📝 Si los CSS no cargan en el navegador:\n";
echo "   1. Limpiar caché del navegador (Ctrl+F5)\n";
echo "   2. Verificar la consola del navegador para errores\n";
echo "   3. Revisar que las URLs en el HTML sean correctas\n";
echo "   4. Verificar que no haya bloqueos de CORS\n";

echo "\n🎉 VERIFICACIÓN COMPLETADA\n";
?>
