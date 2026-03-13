<?php

namespace Config;

class Config {
    private static array $cache = [];
    
    /**
     * Get configuration value
     */
    public static function get(string $key, mixed $default = null): mixed {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }
        
        $value = $_ENV[$key] ?? $default;
        self::$cache[$key] = $value;
        
        return $value;
    }
    
    /**
     * Get database configuration
     */
    public static function getDatabaseConfig(): array {
        return [
            'host' => self::get('DB_HOST', 'localhost'),
            'port' => self::get('DB_PORT', 3306),
            'database' => self::get('DB_DATABASE', 'hotel_management_system'),
            'username' => self::get('DB_USERNAME', 'root'),
            'password' => self::get('DB_PASSWORD', ''),
            'charset' => self::get('DB_CHARSET', 'utf8mb4'),
            'collation' => self::get('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'options' => [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_PERSISTENT => true,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . self::get('DB_CHARSET', 'utf8mb4')
            ]
        ];
    }
    
    /**
     * Get app configuration
     */
    public static function getAppConfig(): array {
        return [
            'name' => self::get('HOTEL_NAME', 'Hotel Tame'),
            'env' => self::get('APP_ENV', 'production'),
            'debug' => self::get('APP_DEBUG', false),
            'url' => self::get('APP_URL', 'http://localhost'),
            'timezone' => self::get('HOTEL_TIMEZONE', 'UTC'),
            'currency' => self::get('HOTEL_CURRENCY', 'COP'),
            'checkin_time' => self::get('HOTEL_CHECKIN_TIME', '15:00'),
            'checkout_time' => self::get('HOTEL_CHECKOUT_TIME', '12:00')
        ];
    }
    
    /**
     * Get JWT configuration
     */
    public static function getJWTConfig(): array {
        return [
            'secret' => self::get('JWT_SECRET'),
            'ttl' => (int) self::get('JWT_TTL', 3600),
            'refresh_ttl' => (int) self::get('JWT_REFRESH_TTL', 604800),
            'algorithm' => 'HS256'
        ];
    }
    
    /**
     * Get mail configuration
     */
    public static function getMailConfig(): array {
        return [
            'mailer' => self::get('MAIL_MAILER', 'smtp'),
            'host' => self::get('MAIL_HOST', '127.0.0.1'),
            'port' => (int) self::get('MAIL_PORT', 2525),
            'username' => self::get('MAIL_USERNAME'),
            'password' => self::get('MAIL_PASSWORD'),
            'encryption' => self::get('MAIL_ENCRYPTION'),
            'from' => [
                'address' => self::get('MAIL_FROM_ADDRESS', 'info@hoteltame.com'),
                'name' => self::get('MAIL_FROM_NAME', 'Hotel Tame')
            ]
        ];
    }
    
    /**
     * Get logging configuration
     */
    public static function getLogConfig(): array {
        return [
            'channel' => self::get('LOG_CHANNEL', 'stack'),
            'level' => self::get('LOG_LEVEL', 'debug'),
            'path' => self::get('LOG_PATH', __DIR__ . '/../logs')
        ];
    }
    
    /**
     * Get security configuration
     */
    public static function getSecurityConfig(): array {
        return [
            'bcrypt_rounds' => (int) self::get('BCRYPT_ROUNDS', 12),
            'max_login_attempts' => (int) self::get('MAX_LOGIN_ATTEMPTS', 5),
            'lockout_duration' => (int) self::get('LOCKOUT_DURATION', 900),
            'app_key' => self::get('APP_KEY')
        ];
    }
    
    /**
     * Check if application is in debug mode
     */
    public static function isDebug(): bool {
        return self::get('APP_DEBUG', false);
    }
    
    /**
     * Check if application is in production
     */
    public static function isProduction(): bool {
        return self::get('APP_ENV') === 'production';
    }
    
    /**
     * Check if application is in development
     */
    public static function isDevelopment(): bool {
        return self::get('APP_ENV') === 'development';
    }
}
