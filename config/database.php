<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hotel_management_system'); // Nombre de la base de datos

// Habilitar errores para depuración (desactivar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    public $conn;

    // Devuelve una conexión PDO
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
            // Mostrar mensaje amigable en desarrollo
            http_response_code(500);
            echo json_encode(array("message" => "Error de conexión a la base de datos: " . $e->getMessage()));
            exit;
        }

        return $this->conn;
    }
}

// Helper opcional para compatibilidad (usa PDO internamente)
function executeQuery($sql, $params = []) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta");
        }
        $stmt->execute($params);
        return $stmt;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
}
?>
