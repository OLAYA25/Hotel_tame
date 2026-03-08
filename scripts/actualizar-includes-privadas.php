<?php
// scripts/actualizar-includes-privadas.php

function actualizarIncludesVistasPrivadas() {
    $vistasPrivadas = glob(__DIR__ . '/../frontend/views/private/*.php');
    
    foreach ($vistasPrivadas as $file) {
        echo "\n=== Procesando: " . basename($file) . " ===\n";
        $content = file_get_contents($file);
        $original = $content;
        $cambios = 0;
        
        // Actualizar includes de header.php
        $content = preg_replace(
            '/(include|require_once)[\'"\s]+includes\/header\.php[\'"\s]*;?/',
            'include __DIR__ . \'/../../backend/includes/header.php\';',
            $content
        );
        
        // Actualizar includes de sidebar.php
        $content = preg_replace(
            '/(include|require_once)[\'"\s]+includes\/sidebar\.php[\'"\s]*;?/',
            'include __DIR__ . \'/../../backend/includes/sidebar.php\';',
            $content
        );
        
        // Actualizar includes de footer.php
        $content = preg_replace(
            '/(include|require_once)[\'"\s]+includes\/footer\.php[\'"\s]*;?/',
            'include __DIR__ . \'/../../backend/includes/footer.php\';',
            $content
        );
        
        // Actualizar includes de auth_middleware.php
        $content = preg_replace(
            '/(include|require_once)[\'"\s]+includes\/auth_middleware\.php[\'"\s]*;?/',
            'require_once __DIR__ . \'/../../backend/includes/auth_middleware.php\';',
            $content
        );
        
        // Actualizar includes de simple_permissions.php
        $content = preg_replace(
            '/(include|require_once)[\'"\s]+includes\/simple_permissions\.php[\'"\s]*;?/',
            'require_once __DIR__ . \'/../../backend/includes/simple_permissions.php\';',
            $content
        );
        
        // Actualizar includes de librerías especiales
        $content = preg_replace(
            '/(include|require_once)[\'"\s]+lib\/RoleBasedDashboard\.php[\'"\s]*;?/',
            'require_once __DIR__ . \'/../../backend/utils/lib/RoleBasedDashboard.php\';',
            $content
        );
        
        // Actualizar includes de NotificationManager
        $content = preg_replace(
            '/(include|require_once)[\'"\s]+lib\/NotificationManager\.php[\'"\s]*;?/',
            'require_once __DIR__ . \'/../../backend/lib/NotificationManager.php\';',
            $content
        );
        
        if ($content !== $original) {
            file_put_contents($file, $content);
            echo "✅ Archivo actualizado\n";
        } else {
            echo "ℹ️  No se requirieron cambios\n";
        }
    }
}

actualizarIncludesVistasPrivadas();
echo "\n=== ACTUALIZACIÓN DE INCLUDES COMPLETADA ===\n";
?>
