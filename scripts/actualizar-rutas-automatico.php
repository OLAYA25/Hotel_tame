<?php
// scripts/actualizar-rutas-automatico.php
// ATENCIÓN: Este script actualiza rutas en archivos movidos

function actualizarRutasEnArchivo($filepath) {
    if (!file_exists($filepath)) {
        echo "Archivo no existe: $filepath\n";
        return false;
    }
    
    $content = file_get_contents($filepath);
    $original = $content;
    $cambios = 0;
    
    echo "\n=== Procesando: $filepath ===\n";
    
    // 1. Actualizar includes de config/database.php
    $patrones_config = [
        '/require_once[\'"]\.\.\/config\/database\.php[\'"]/',
        '/require_once[\'"]config\/database\.php[\'"]/',
        '/include[\'"]\.\.\/config\/database\.php[\'"]/',
        '/include[\'"]config\/database\.php[\'"]/'
    ];
    
    foreach ($patrones_config as $patron) {
        if (preg_match($patron, $content)) {
            $content = preg_replace($patron, 'require_once __DIR__ . \'/../../backend/config/database.php\'', $content);
            $cambios++;
            echo "✓ Actualizado config/database.php\n";
        }
    }
    
    // 2. Actualizar includes de backend/includes/ para vistas privadas
    if (strpos($filepath, '/frontend/views/private/') !== false) {
        $patrones_includes = [
            '/require_once[\'"]includes\/([^\';\']+)[\'"]/',
            '/include[\'"]includes\/([^\';\']+)[\'"]/'
        ];
        
        foreach ($patrones_includes as $patron) {
            if (preg_match($patron, $content)) {
                $content = preg_replace($patron, 'require_once __DIR__ . \'/../../backend/includes/$1\'', $content);
                $cambios++;
                echo "✓ Actualizado includes/ a backend/includes/\n";
            }
        }
    }
    
    // 3. Actualizar includes de backend/includes/ para vistas públicas
    if (strpos($filepath, '/frontend/views/public/') !== false) {
        // Las vistas públicas solo necesitan auth_middleware si lo usan
        $patrones_auth = [
            '/require_once[\'"]includes\/auth_middleware\.php[\'"]/',
            '/include[\'"]includes\/auth_middleware\.php[\'"]/'
        ];
        
        foreach ($patrones_auth as $patron) {
            if (preg_match($patron, $content)) {
                $content = preg_replace($patron, 'require_once __DIR__ . \'/../../backend/includes/auth_middleware.php\'', $content);
                $cambios++;
                echo "✓ Actualizado auth_middleware.php\n";
            }
        }
    }
    
    // 4. Actualizar rutas de assets a rutas absolutas
    $patrones_assets = [
        '/(href|src)=[\'"]assets\/([^\'"]+)[\'"]/',
        '/(href|src)=[\'"]\.\/assets\/([^\'"]+)[\'"]/'
    ];
    
    foreach ($patrones_assets as $patron) {
        if (preg_match($patron, $content)) {
            $content = preg_replace($patron, '$1="/Hotel_tame/assets/$2"', $content);
            $cambios++;
            echo "✓ Actualizado rutas de assets\n";
        }
    }
    
    // 5. Actualizar redirecciones de login
    $patrones_login = [
        '/header\([\'"]Location: login\.php[\'"]/',
        '/header\([\'"]Location: \.\/login\.php[\'"]/'
    ];
    
    foreach ($patrones_login as $patron) {
        if (preg_match($patron, $content)) {
            $content = preg_replace($patron, 'header(\'Location: /Hotel_tame/login\')', $content);
            $cambios++;
            echo "✓ Actualizado redirección login\n";
        }
    }
    
    // 6. Actualizar redirecciones de index/dashboard
    $patrones_dashboard = [
        '/header\([\'"]Location: index\.php[\'"]/',
        '/header\([\'"]Location: \.\/index\.php[\'"]/'
    ];
    
    foreach ($patrones_dashboard as $patron) {
        if (preg_match($patron, $content)) {
            $content = preg_replace($patron, 'header(\'Location: /Hotel_tame/dashboard\')', $content);
            $cambios++;
            echo "✓ Actualizado redirección dashboard\n";
        }
    }
    
    // 7. Actualizar includes de librerías especiales (solo para vistas que las usan)
    if (strpos($filepath, 'mis_actividades_v2.php') !== false) {
        $patrones_lib = [
            '/require_once[\'"]lib\/RoleBasedDashboard\.php[\'"]/'
        ];
        
        foreach ($patrones_lib as $patron) {
            if (preg_match($patron, $content)) {
                $content = preg_replace($patron, 'require_once __DIR__ . \'/../../backend/utils/lib/RoleBasedDashboard.php\'', $content);
                $cambios++;
                echo "✓ Actualizado lib/RoleBasedDashboard.php\n";
            }
        }
    }
    
    // Guardar cambios si hubo modificaciones
    if ($content !== $original) {
        file_put_contents($filepath, $content);
        echo "✅ Archivo actualizado con $cambios cambios\n";
        return true;
    } else {
        echo "ℹ️  No se requirieron cambios\n";
        return false;
    }
}

// Procesar todas las vistas públicas
echo "=== PROCESANDO VISTAS PÚBLICAS ===\n";
$publicViews = glob(__DIR__ . '/../frontend/views/public/*.php');
foreach ($publicViews as $file) {
    actualizarRutasEnArchivo($file);
}

// Procesar todas las vistas privadas
echo "\n=== PROCESANDO VISTAS PRIVADAS ===\n";
$privateViews = glob(__DIR__ . '/../frontend/views/private/*.php');
foreach ($privateViews as $file) {
    actualizarRutasEnArchivo($file);
}

echo "\n=== PROCESO COMPLETADO ===\n";
echo "REVISAR MANUALMENTE los archivos actualizados antes de continuar.\n";
echo "Verificar en navegador: http://localhost/Hotel_tame/\n";
?>
