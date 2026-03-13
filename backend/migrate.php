<?php
/**
 * CLI para ejecutar migraciones
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Migration.php';

// Verificar si se ejecuta desde CLI
if (php_sapi_name() !== 'cli') {
    echo "Este script debe ejecutarse desde la línea de comandos.\n";
    exit(1);
}

$migration = new Migration();

// Obtener comando
$command = $argv[1] ?? 'help';

switch ($command) {
    case 'migrate':
        echo "Iniciando migración...\n";
        $migration->migrate();
        break;
        
    case 'rollback':
        $steps = isset($argv[2]) ? (int)$argv[2] : 1;
        echo "Revirtiendo últimas $steps migraciones...\n";
        $migration->rollback($steps);
        break;
        
    case 'status':
        $migration->status();
        break;
        
    case 'create':
        if (!isset($argv[2])) {
            echo "Uso: php migrate.php create <nombre> [descripción]\n";
            exit(1);
        }
        $name = $argv[2];
        $description = $argv[3] ?? '';
        $migration->create($name, $description);
        break;
        
    case 'help':
    default:
        echo "Hotel Tame PMS - Sistema de Migraciones\n\n";
        echo "Comandos disponibles:\n";
        echo "  migrate        Ejecuta todas las migraciones pendientes\n";
        echo "  rollback [n]  Revierte las últimas n migraciones (default: 1)\n";
        echo "  status        Muestra el estado de las migraciones\n";
        echo "  create <name> Crea una nueva migración\n";
        echo "  help          Muestra esta ayuda\n\n";
        echo "Ejemplos:\n";
        echo "  php migrate.php migrate\n";
        echo "  php migrate.php rollback 2\n";
        echo "  php migrate.php create add_user_table \"Añadir tabla de usuarios\"\n";
        break;
}
