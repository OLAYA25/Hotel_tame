<?php

namespace Database;

use Config\Config;
use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;
    
    /**
     * Get database connection
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                $config = Config::getDatabaseConfig();
                
                $dsn = sprintf(
                    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                    $config['host'],
                    $config['port'],
                    $config['database'],
                    $config['charset']
                );
                
                self::$instance = new PDO($dsn, $config['username'], $config['password'], $config['options']);
                
            } catch (PDOException $e) {
                if (Config::isDebug()) {
                    throw new DatabaseException('Database connection failed: ' . $e->getMessage(), 0, $e);
                } else {
                    throw new DatabaseException('Database connection failed');
                }
            }
        }
        
        return self::$instance;
    }
    
    /**
     * Close database connection
     */
    public static function closeConnection(): void {
        self::$instance = null;
    }
    
    /**
     * Begin transaction
     */
    public static function beginTransaction(): bool {
        return self::getConnection()->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public static function commit(): bool {
        return self::getConnection()->commit();
    }
    
    /**
     * Rollback transaction
     */
    public static function rollBack(): bool {
        return self::getConnection()->rollBack();
    }
    
    /**
     * Get last insert ID
     */
    public static function lastInsertId(): string|false {
        return self::getConnection()->lastInsertId();
    }
    
    /**
     * Execute query
     */
    public static function execute(string $query, array $params = []): \PDOStatement {
        $stmt = self::getConnection()->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Fetch single record
     */
    public static function fetch(string $query, array $params = []): array|false {
        return self::execute($query, $params)->fetch();
    }
    
    /**
     * Fetch all records
     */
    public static function fetchAll(string $query, array $params = []): array {
        return self::execute($query, $params)->fetchAll();
    }
    
    /**
     * Fetch column
     */
    public static function fetchColumn(string $query, array $params = []): mixed {
        return self::execute($query, $params)->fetchColumn();
    }
    
    /**
     * Get connection info
     */
    public static function getConnectionInfo(): array {
        $pdo = self::getConnection();
        return [
            'driver' => $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
            'server_version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
            'client_version' => $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION),
            'connection_status' => $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)
        ];
    }
}

class DatabaseException extends \Exception {
    // Custom exception for database errors
}
