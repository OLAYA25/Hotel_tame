<?php
header("Access-Control-Allow-Origin: *");;
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    
    if (!empty($search)) {
        // Búsqueda con LIKE
        $query = "SELECT id, nombre, apellido, email, telefono, documento 
                  FROM clientes 
                  WHERE deleted_at IS NULL 
                  AND (nombre LIKE ? OR apellido LIKE ? OR documento LIKE ? OR email LIKE ?)
                  ORDER BY nombre, apellido 
                  LIMIT 20";
        
        $searchParam = "%{$search}%";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $searchParam);
        $stmt->bindParam(2, $searchParam);
        $stmt->bindParam(3, $searchParam);
        $stmt->bindParam(4, $searchParam);
    } else {
        // Obtener primeros 20 clientes si no hay búsqueda
        $query = "SELECT id, nombre, apellido, email, telefono, documento 
                  FROM clientes 
                  WHERE deleted_at IS NULL 
                  ORDER BY nombre, apellido 
                  LIMIT 20";
        
        $stmt = $db->prepare($query);
    }
    
    $stmt->execute();
    
    $clientes = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $clientes[] = array(
            "id" => $row['id'],
            "nombre" => $row['nombre'],
            "apellido" => $row['apellido'] ?? '',
            "email" => $row['email'],
            "telefono" => $row['telefono'],
            "documento" => $row['documento'],
            "texto_completo" => ($row['nombre'] . ' ' . $row['apellido'] . ' ' . $row['documento'] . ' ' . $row['email'])
        );
    }
    
    echo json_encode(array("results" => $clientes));
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "message" => "Error al buscar clientes", 
        "error" => $e->getMessage()
    ));
}
?>
