<?php
/**
 * Configuración central de la aplicación
 */

class App {
    // Database
    const DB_HOST = 'localhost';
    const DB_NAME = 'hotel_management_system';
    const DB_USER = 'root';
    const DB_PASS = '';
    const DB_CHARSET = 'utf8mb4';
    
    // Application
    const APP_NAME = 'Hotel Tame PMS';
    const APP_VERSION = '2.0.0';
    const APP_URL = 'http://localhost/Hotel_tame';
    const APP_ENV = 'development'; // development, staging, production
    const TIMEZONE = 'America/Bogota';
    
    // Security
    const HASH_ALGO = PASSWORD_DEFAULT;
    const SESSION_LIFETIME = 3600; // 1 hour
    const MAX_LOGIN_ATTEMPTS = 5;
    const CSRF_TOKEN_LENGTH = 32;
    
    // Hotel Settings
    const DEFAULT_CHECKIN_TIME = '15:00';
    const DEFAULT_CHECKOUT_TIME = '12:00';
    const CURRENCY = 'COP';
    
    // API
    const API_VERSION = 'v1';
    const API_PREFIX = '/api';
    
    // File Upload
    const MAX_FILE_SIZE = 5242880; // 5MB
    const ALLOWED_FILE_TYPES = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
    
    // Pagination
    const DEFAULT_PAGE_SIZE = 20;
    const MAX_PAGE_SIZE = 100;
    
    // Cache
    const CACHE_ENABLED = true;
    const CACHE_TTL = 3600; // 1 hour
    
    public static function get($key) {
        return constant("self::$key");
    }
    
    public static function isDevelopment() {
        return self::APP_ENV === 'development';
    }
    
    public static function isProduction() {
        return self::APP_ENV === 'production';
    }
    
    public static function getDatabaseConfig() {
        return [
            'host' => self::DB_HOST,
            'name' => self::DB_NAME,
            'user' => self::DB_USER,
            'pass' => self::DB_PASS,
            'charset' => self::DB_CHARSET
        ];
    }
    
    public static function getUploadPath() {
        return __DIR__ . '/../../uploads';
    }
    
    public static function getLogsPath() {
        return __DIR__ . '/../../logs';
    }
}

// Establecer timezone
date_default_timezone_set(App::TIMEZONE);

// Configuración de errores según entorno
if (App::isDevelopment()) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
}

ini_set('log_errors', 1);
ini_set('error_log', App::getLogsPath() . '/error.log');
