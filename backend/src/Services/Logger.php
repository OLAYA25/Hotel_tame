<?php

namespace App\Services;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Config\Config;

class AppLogger {
    private static array $loggers = [];
    
    /**
     * Get logger instance
     */
    public static function getLogger(string $name = 'app'): MonologLogger {
        if (!isset(self::$loggers[$name])) {
            $logConfig = Config::getLogConfig();
            
            $logger = new MonologLogger($name);
            
            // Add handlers based on configuration
            if ($logConfig['channel'] === 'stack') {
                // Multiple handlers
                $logger->pushHandler(new StreamHandler(
                    $logConfig['path'] . '/app.log',
                    Level::fromName($logConfig['level'])
                ));
                
                $logger->pushHandler(new RotatingFileHandler(
                    $logConfig['path'] . '/error.log',
                    30,
                    Level::Error
                ));
                
                $logger->pushHandler(new StreamHandler(
                    $logConfig['path'] . '/security.log',
                    Level::Warning
                ));
            } else {
                // Single handler
                $logger->pushHandler(new StreamHandler(
                    $logConfig['path'] . '/' . $name . '.log',
                    Level::fromName($logConfig['level'])
                ));
            }
            
            self::$loggers[$name] = $logger;
        }
        
        return self::$loggers[$name];
    }
    
    /**
     * Log info message
     */
    public static function info(string $message, array $context = []): void {
        self::getLogger()->info($message, $context);
    }
    
    /**
     * Log error message
     */
    public static function error(string $message, array $context = []): void {
        self::getLogger()->error($message, $context);
    }
    
    /**
     * Log warning message
     */
    public static function warning(string $message, array $context = []): void {
        self::getLogger()->warning($message, $context);
    }
    
    /**
     * Log debug message
     */
    public static function debug(string $message, array $context = []): void {
        if (Config::isDebug()) {
            self::getLogger()->debug($message, $context);
        }
    }
    
    /**
     * Log security event
     */
    public static function security(string $message, array $context = []): void {
        $securityLogger = new MonologLogger('security');
        $securityLogger->pushHandler(new StreamHandler(
            Config::getLogConfig()['path'] . '/security.log',
            Level::Warning
        ));
        
        $context['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $context['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $context['timestamp'] = date('Y-m-d H:i:s');
        
        $securityLogger->warning($message, $context);
    }
    
    /**
     * Log database query
     */
    public static function database(string $query, array $params = [], float $executionTime = null): void {
        if (Config::isDebug()) {
            $dbLogger = new MonologLogger('database');
            $dbLogger->pushHandler(new StreamHandler(
                Config::getLogConfig()['path'] . '/database.log',
                Level::Debug
            ));
            
            $context = [
                'query' => $query,
                'params' => $params,
                'execution_time' => $executionTime
            ];
            
            $dbLogger->debug('Database Query', $context);
        }
    }
    
    /**
     * Log API request
     */
    public static function api(string $method, string $endpoint, array $data = [], int $statusCode = 200, float $responseTime = null): void {
        $apiLogger = new MonologLogger('api');
        $apiLogger->pushHandler(new StreamHandler(
            Config::getLogConfig()['path'] . '/api.log',
            Level::Info
        ));
        
        $context = [
            'method' => $method,
            'endpoint' => $endpoint,
            'status_code' => $statusCode,
            'response_time' => $responseTime,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        // Don't log sensitive data
        $safeData = $data;
        unset($safeData['password'], $safeData['token'], $safeData['csrf_token']);
        
        if (!empty($safeData)) {
            $context['data'] = $safeData;
        }
        
        $message = sprintf('%s %s - %d', $method, $endpoint, $statusCode);
        $apiLogger->info($message, $context);
    }
    
    /**
     * Log business event
     */
    public static function business(string $event, array $context = []): void {
        $businessLogger = new MonologLogger('business');
        $businessLogger->pushHandler(new StreamHandler(
            Config::getLogConfig()['path'] . '/business.log',
            Level::Info
        ));
        
        $context['event'] = $event;
        $context['timestamp'] = date('Y-m-d H:i:s');
        
        $businessLogger->info('Business Event', $context);
    }
    
    /**
     * Log performance metrics
     */
    public static function performance(string $metric, mixed $value, array $context = []): void {
        $perfLogger = new MonologLogger('performance');
        $perfLogger->pushHandler(new StreamHandler(
            Config::getLogConfig()['path'] . '/performance.log',
            Level::Info
        ));
        
        $context['metric'] = $metric;
        $context['value'] = $value;
        $context['timestamp'] = date('Y-m-d H:i:s');
        
        $perfLogger->info('Performance Metric', $context);
    }
    
    /**
     * Log exception
     */
    public static function exception(\Throwable $exception, array $context = []): void {
        $context['exception'] = [
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => Config::isDebug() ? $exception->getTrace() : []
        ];
        
        self::error('Exception: ' . $exception->getMessage(), $context);
    }
}
