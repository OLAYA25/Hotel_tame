<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, DELETE");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Obtener cliente por ID
                $query = "SELECT id, nombre, apellido, email, telefono, documento, tipo_documento, fecha_nacimiento, ciudad, pais, direccion 
                          FROM clientes 
                          WHERE id = ? AND deleted_at IS NULL";
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $_GET['id']);
                $stmt->execute();
                
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($row) {
                    echo json_encode($row);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Cliente no encontrado"));
                }
            } else {
                // Obtener todos los clientes
                $query = "SELECT id, nombre, apellido, email, telefono, documento 
                          FROM clientes 
                          WHERE deleted_at IS NULL 
                          ORDER BY created_at DESC 
                          LIMIT 50";
                
                $stmt = $db->prepare($query);
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
            }
            break;
            
        case 'DELETE':
            $data = json_decode(file_get_contents("php://input"));
            
            if (!isset($data->id) && !isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(array("message" => "ID es requerido"));
                break;
            }
            
            $id = $data->id ?? $_GET['id'];
            
            // Soft delete - marcar como eliminado
            $query = "UPDATE clientes SET deleted_at = NOW() WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $id);
            
            if($stmt->execute()) {
                echo json_encode(array("message" => "Cliente eliminado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo eliminar el cliente."));
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(array("message" => "Método no permitido."));
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "message" => "Error al procesar la solicitud", 
        "error" => $e->getMessage()
    ));
}
?>
