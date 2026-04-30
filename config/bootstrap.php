<?php
/**
 * Application Bootstrap
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Define constants
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost');

// Error reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set($_ENV['HOTEL_TIMEZONE'] ?? 'UTC');

// Set error handler
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Set exception handler
set_exception_handler(function($exception) {
    http_response_code(500);
    header('Content-Type: application/json');
    
    $response = [
        'success' => false,
        'error' => 'Internal Server Error',
        'message' => APP_DEBUG ? $exception->getMessage() : 'An error occurred',
        'trace' => APP_DEBUG ? $exception->getTrace() : []
    ];
    
    echo json_encode($response);
    exit;
});
