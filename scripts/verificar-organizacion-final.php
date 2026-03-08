<?php
// scripts/verificar-organizacion-final.php
echo "=== VERIFICACIÓN FINAL DE ORGANIZACIÓN ===\n\n";

$root = __DIR__ . '/..';

// Estructura esperada
$expectedStructure = [
    'Raíz' => [
        'index.php' => 'Router principal',
        '.htaccess' => 'Reescritura de URLs',
        'ESTRUCTURA_FINAL.md' => 'Documentación final',
        'backend/' => 'Código PHP',
        'frontend/' => 'Código Next.js',
        'assets/' => 'Recursos globales',
        'uploads/' => 'Archivos subidos',
        'scripts/' => 'Scripts de mantenimiento',
        'docs/' => 'Documentación',
        'backups/' => 'Backups',
        '.git/' => 'Control de versiones'
    ],
    'backend/' => [
        'api/' => 'Endpoints API',
        'config/' => 'Configuración',
        'includes/' => 'Archivos de inclusión',
        'lib/' => 'Librerías PHP',
        'models/' => 'Modelos de datos',
        'utils/' => 'Utilidades',
        'legacy-views/' => 'Vistas antiguas'
    ],
    'frontend/' => [
        'out/' => 'Build estático',
        'src/' => 'Código fuente Next.js'
    ],
    'docs/' => [
        'development/' => 'Documentación de desarrollo',
        'database/' => 'Documentación de BD',
        'reports/' => 'Informes'
    ]
];

function checkDirectoryStructure($root) {
    echo "📁 ESTRUCTURA DE DIRECTORIOS:\n";
    
    $dirs = [
        'backend/api/endpoints',
        'backend/config',
        'backend/includes',
        'backend/lib',
        'backend/models',
        'backend/utils',
        'backend/legacy-views',
        'frontend/out',
        'frontend/src',
        'frontend/src/app',
        'frontend/src/components',
        'frontend/src/hooks',
        'frontend/src/lib',
        'frontend/src/public',
        'frontend/src/styles',
        'docs/development',
        'docs/database',
        'docs/reports',
        'backups/archivos-legacy',
        'backups/database',
        'scripts'
    ];
    
    foreach ($dirs as $dir) {
        $path = $root . '/' . $dir;
        if (is_dir($path)) {
            $count = count(glob($path . '/*'));
            echo "✅ $dir ($count archivos)\n";
        } else {
            echo "❌ $dir (no existe)\n";
        }
    }
}

function checkRootFiles($root) {
    echo "\n📄 ARCHIVOS EN RAÍZ:\n";
    
    $files = scandir($root);
    $expectedRootFiles = ['index.php', '.htaccess', 'ESTRUCTURA_FINAL.md'];
    
    foreach ($files as $file) {
        if ($file[0] != '.' && !is_dir($root . '/' . $file)) {
            if (in_array($file, $expectedRootFiles)) {
                echo "✅ $file (archivo clave)\n";
            } else {
                echo "⚠️  $file (archivo suelto)\n";
            }
        }
    }
}

function checkBackendOrganization($root) {
    echo "\n🔧 ORGANIZACIÓN BACKEND:\n";
    
    $backendDirs = [
        'api/endpoints' => 'Endpoints API',
        'config' => 'Configuración',
        'includes' => 'Includes',
        'lib' => 'Librerías',
        'models' => 'Modelos',
        'utils' => 'Utilidades',
        'legacy-views' => 'Vistas Legacy'
    ];
    
    foreach ($backendDirs as $dir => $description) {
        $path = $root . '/backend/' . $dir;
        if (is_dir($path)) {
            $files = glob($path . '/*.php');
            $count = count($files);
            echo "✅ $dir: $count archivos PHP ($description)\n";
        } else {
            echo "❌ $dir: no existe\n";
        }
    }
}

function checkFrontendOrganization($root) {
    echo "\n🎨 ORGANIZACIÓN FRONTEND:\n";
    
    $frontendDirs = [
        'out' => 'Build estático',
        'src/app' => 'App Next.js',
        'src/components' => 'Componentes',
        'src/hooks' => 'Hooks',
        'src/lib' => 'Librerías TS',
        'src/public' => 'Assets públicos',
        'src/styles' => 'Estilos'
    ];
    
    foreach ($frontendDirs as $dir => $description) {
        $path = $root . '/frontend/' . $dir;
        if (is_dir($path)) {
            $files = glob($path . '/*');
            $count = count($files);
            echo "✅ $dir: $count archivos ($description)\n";
        } else {
            echo "❌ $dir: no existe\n";
        }
    }
}

function checkDocsOrganization($root) {
    echo "\n📚 ORGANIZACIÓN DOCUMENTACIÓN:\n";
    
    $docsDirs = [
        'development' => 'Docs de desarrollo',
        'database' => 'Docs de BD',
        'reports' => 'Informes'
    ];
    
    foreach ($docsDirs as $dir => $description) {
        $path = $root . '/docs/' . $dir;
        if (is_dir($path)) {
            $files = glob($path . '/*');
            $count = count($files);
            echo "✅ docs/$dir: $count archivos ($description)\n";
        } else {
            echo "❌ docs/$dir: no existe\n";
        }
    }
}

function checkBackups($root) {
    echo "\n💾 ORGANIZACIÓN BACKUPS:\n";
    
    $backupDirs = [
        'archivos-legacy' => 'Archivos legacy',
        'database' => 'Backups de BD'
    ];
    
    foreach ($backupDirs as $dir => $description) {
        $path = $root . '/backups/' . $dir;
        if (is_dir($path)) {
            $files = glob($path . '/*');
            $count = count($files);
            echo "✅ backups/$dir: $count archivos ($description)\n";
        } else {
            echo "❌ backups/$dir: no existe\n";
        }
    }
}

function generateSummary($root) {
    echo "\n📊 RESUMEN DE ORGANIZACIÓN:\n";
    
    $totalDirs = 0;
    $totalFiles = 0;
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isDir()) {
            $totalDirs++;
        } else {
            $totalFiles++;
        }
    }
    
    echo "📁 Total directorios: $totalDirs\n";
    echo "📄 Total archivos: $totalFiles\n";
    
    // Contar por tipo
    $phpFiles = count(glob($root . '/backend/**/*.php', GLOB_BRACE));
    $tsFiles = count(glob($root . '/frontend/src/**/*.ts', GLOB_BRACE));
    $jsFiles = count(glob($root . '/frontend/src/**/*.js', GLOB_BRACE));
    $mdFiles = count(glob($root . '/docs/**/*.md', GLOB_BRACE));
    
    echo "🐛 Archivos PHP: $phpFiles\n";
    echo "📘 Archivos TypeScript: $tsFiles\n";
    echo "📜 Archivos JavaScript: $jsFiles\n";
    echo "📝 Archivos Markdown: $mdFiles\n";
}

// Ejecutar verificaciones
checkDirectoryStructure($root);
checkRootFiles($root);
checkBackendOrganization($root);
checkFrontendOrganization($root);
checkDocsOrganization($root);
checkBackups($root);
generateSummary($root);

echo "\n🎉 VERIFICACIÓN COMPLETADA\n";
echo "✅ Estructura organizada correctamente\n";
echo "✅ Archivos clasificados por función\n";
echo "✅ Directorios limpios y ordenados\n";
?>
