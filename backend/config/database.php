<?php
require_once __DIR__ . '/../../config/env.php';

// Compatibilidad: .env usa DB_DATABASE / DB_USERNAME / DB_PASSWORD; legado DB_NAME / DB_USER / DB_PASS
if (!defined('DB_HOST')) {
    define('DB_HOST', hotel_tame_env('DB_HOST', 'localhost'));
}
if (!defined('DB_USER')) {
    define('DB_USER', hotel_tame_env('DB_USERNAME', hotel_tame_env('DB_USER', 'root')));
}
if (!defined('DB_PASS')) {
    define('DB_PASS', hotel_tame_env('DB_PASSWORD', hotel_tame_env('DB_PASS', '')));
}
if (!defined('DB_NAME')) {
    define('DB_NAME', hotel_tame_env('DB_DATABASE', hotel_tame_env('DB_NAME', 'hotel_management_system')));
}

$htDebug = filter_var(hotel_tame_env('APP_DEBUG', true), FILTER_VALIDATE_BOOLEAN);
if ($htDebug) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
}

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            http_response_code(500);
            $show = filter_var(hotel_tame_env('APP_DEBUG', true), FILTER_VALIDATE_BOOLEAN);
            $msg = $show
                ? 'Error de conexión a la base de datos: ' . $e->getMessage()
                : 'Error de conexión a la base de datos.';
            echo json_encode(['message' => $msg]);
            exit;
        }

        return $this->conn;
    }

    public function backup($filepath) {
        try {
            $db_name = $this->db_name;
            $host = $this->host;
            $username = $this->username;
            $password = $this->password;

            $command = "mysqldump --single-transaction --routines --triggers --host={$host} --user={$username} --password={$password} {$db_name} > {$filepath}";

            $output = [];
            $return_var = 0;
            exec($command, $output, $return_var);

            if ($return_var === 0 && file_exists($filepath) && filesize($filepath) > 0) {
                return true;
            }
            error_log('Error en backup: ' . implode("\n", $output));
            return false;
        } catch (Exception $e) {
            error_log('Error creando backup: ' . $e->getMessage());
            return false;
        }
    }

    public function restore($filepath) {
        try {
            $db_name = $this->db_name;
            $host = $this->host;
            $username = $this->username;
            $password = $this->password;

            if (!file_exists($filepath)) {
                throw new Exception('Archivo de backup no encontrado');
            }

            $command = "mysql --host={$host} --user={$username} --password={$password} {$db_name} < {$filepath}";

            $output = [];
            $return_var = 0;
            exec($command, $output, $return_var);

            if ($return_var === 0) {
                return true;
            }
            error_log('Error en restore: ' . implode("\n", $output));
            return false;
        } catch (Exception $e) {
            error_log('Error restaurando backup: ' . $e->getMessage());
            return false;
        }
    }
}

function executeQuery($sql, $params = []) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception('Error preparando la consulta');
        }
        $stmt->execute($params);
        return $stmt;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
}
