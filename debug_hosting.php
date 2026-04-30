<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Cargar configuración
require_once 'config/env.php';

echo json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'environment' => [
        'APP_ENV' => hotel_tame_env('APP_ENV', 'not_set'),
        'APP_DEBUG' => hotel_tame_env('APP_DEBUG', 'not_set'),
        'APP_URL' => hotel_tame_env('APP_URL', 'not_set'),
    ],
    'database' => [
        'DB_HOST' => hotel_tame_env('DB_HOST', 'not_set'),
        'DB_DATABASE' => hotel_tame_env('DB_DATABASE', 'not_set'),
        'DB_USERNAME' => hotel_tame_env('DB_USERNAME', 'not_set'),
        'DB_PASSWORD' => hotel_tame_env('DB_PASSWORD', 'not_set') ? '***SET***' : 'EMPTY',
    ],
    'connection_test' => testDatabaseConnection(),
    'habitaciones_count' => countHabitaciones(),
    'files' => [
        '.env_exists' => file_exists('.env'),
        'env_readable' => is_readable('.env'),
        'database_php_exists' => file_exists('backend/config/database.php'),
    ]
]);

function testDatabaseConnection() {
    try {
        include_once 'backend/config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        if ($db) {
            return ['status' => 'success', 'message' => 'Database connection OK'];
        } else {
            return ['status' => 'failed', 'message' => 'Could not get database connection'];
        }
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

function countHabitaciones() {
    try {
        include_once 'backend/config/database.php';
        include_once 'api/models/Habitacion.php';
        
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            return ['status' => 'no_connection', 'count' => 0];
        }
        
        $habitacion = new Habitacion($db);
        $stmt = $habitacion->getAll();
        $count = $stmt->rowCount();
        
        return ['status' => 'success', 'count' => $count];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage(), 'count' => 0];
    }
}
?>
