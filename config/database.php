<?php
/**
 * Conexión a base de datos mejorada con PDO
 */

require_once __DIR__ . '/app.php';

class Database {
    private static $instance = null;
    private $connection;
    private $stmt;
    
    private function __construct() {
        try {
            $config = App::getDatabaseConfig();
            $dsn = "mysql:host={$config['host']};dbname={$config['name']};charset={$config['charset']}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']}"
            ];
            
            $this->connection = new PDO($dsn, $config['user'], $config['pass'], $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            
            if (App::isDevelopment()) {
                throw new Exception("Error de conexión a la base de datos: " . $e->getMessage());
            } else {
                throw new Exception("Error de conexión al servidor");
            }
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function prepare($sql) {
        $this->stmt = $this->connection->prepare($sql);
        return $this;
    }
    
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
        return $this;
    }
    
    public function execute($params = []) {
        try {
            if (!empty($params)) {
                return $this->stmt->execute($params);
            }
            return $this->stmt->execute();
        } catch (PDOException $e) {
            error_log("Database execute error: " . $e->getMessage());
            throw new Exception("Error en la consulta a la base de datos");
        }
    }
    
    public function fetch() {
        return $this->stmt->fetch();
    }
    
    public function fetchAll() {
        return $this->stmt->fetchAll();
    }
    
    public function fetchColumn() {
        return $this->stmt->fetchColumn();
    }
    
    public function rowCount() {
        return $this->stmt->rowCount();
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollBack() {
        return $this->connection->rollBack();
    }
    
    public function quote($string) {
        return $this->connection->quote($string);
    }
    
    public function getError() {
        return $this->stmt->errorInfo();
    }
    
    /**
     * Ejecutar consulta con prepared statements
     */
    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Obtener información de la conexión
     */
    public function getConnectionInfo() {
        return [
            'driver' => $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME),
            'version' => $this->connection->getAttribute(PDO::ATTR_SERVER_VERSION),
            'charset' => $this->connection->getAttribute(PDO::ATTR_CLIENT_CHARSET)
        ];
    }
}
