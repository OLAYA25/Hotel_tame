<?php
// Endpoint que usa la clase Database del sistema
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

try {
    // Usar la clase Database del sistema - ruta corregida
    include_once '../../backend/config/database.php';
    
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    // Query simple para obtener clientes
    $query = "SELECT id, nombre, apellido, email, telefono, documento FROM clientes WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 10";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $clientes = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $clientes[] = array(
            "id" => $row['id'],
            "nombre" => $row['nombre'],
            "apellido" => $row['apellido'] ?? '',
            "email" => $row['email'],
            "telefono" => $row['telefono'],
            "documento" => $row['documento']
        );
    }
    
    echo json_encode(array("records" => $clientes));
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "error" => $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ));
}
?>
