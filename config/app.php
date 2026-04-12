<?php
/**
 * Configuración central: valores por defecto locales; en hosting sobrescribir con .env
 */
require_once __DIR__ . '/env.php';

class App {
    const APP_NAME = 'Hotel Tame PMS';
    const APP_VERSION = '2.0.0';
    const HASH_ALGO = PASSWORD_DEFAULT;
    const SESSION_LIFETIME = 3600;
    const MAX_LOGIN_ATTEMPTS = 5;
    const CSRF_TOKEN_LENGTH = 32;
    const DEFAULT_CHECKIN_TIME = '15:00';
    const DEFAULT_CHECKOUT_TIME = '12:00';
    const CURRENCY = 'COP';
    const API_VERSION = 'v1';
    const API_PREFIX = '/api';
    const MAX_FILE_SIZE = 5242880;
    const ALLOWED_FILE_TYPES = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
    const DEFAULT_PAGE_SIZE = 20;
    const MAX_PAGE_SIZE = 100;
    const CACHE_ENABLED = true;
    const CACHE_TTL = 3600;

    public static function get($key) {
        return constant("self::$key");
    }

    public static function isDevelopment(): bool {
        return hotel_tame_env('APP_ENV', 'development') === 'development';
    }

    public static function isProduction(): bool {
        return hotel_tame_env('APP_ENV', 'production') === 'production';
    }

    public static function getDatabaseConfig(): array {
        return [
            'host' => hotel_tame_env('DB_HOST', 'localhost'),
            'name' => hotel_tame_env('DB_DATABASE', hotel_tame_env('DB_NAME', 'hotel_management_system')),
            'user' => hotel_tame_env('DB_USERNAME', hotel_tame_env('DB_USER', 'root')),
            'pass' => hotel_tame_env('DB_PASSWORD', hotel_tame_env('DB_PASS', '')),
            'charset' => hotel_tame_env('DB_CHARSET', 'utf8mb4'),
        ];
    }

    public static function getUploadPath(): string {
        return __DIR__ . '/../../uploads';
    }

    public static function getLogsPath(): string {
        return __DIR__ . '/../../logs';
    }
}

date_default_timezone_set(hotel_tame_env('HOTEL_TIMEZONE', 'America/Bogota'));

if (App::isDevelopment()) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
}

ini_set('log_errors', '1');
ini_set('error_log', App::getLogsPath() . '/error.log');
