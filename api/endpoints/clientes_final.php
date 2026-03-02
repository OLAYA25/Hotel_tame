<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, DELETE");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Obtener cliente por ID
                $query = "SELECT id, nombre, apellido, email, telefono, documento, tipo_documento, fecha_nacimiento, ciudad, pais as nacionalidad, direccion 
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
            
        case 'POST':
            $data = json_decode(file_get_contents("php://input"));
            
            if(!empty($data->nombre) && !empty($data->apellido) && !empty($data->documento)) {
                
                // Verificar si el documento ya existe
                $check_query = "SELECT id FROM clientes WHERE documento = ? AND deleted_at IS NULL";
                $check_stmt = $db->prepare($check_query);
                $check_stmt->bindParam(1, $data->documento);
                $check_stmt->execute();
                
                if($check_stmt->rowCount() > 0) {
                    http_response_code(409);
                    echo json_encode(array(
                        "message" => "Ya existe un cliente con este documento.",
                        "error" => "duplicate_document"
                    ));
                    break;
                }
                
                $query = "INSERT INTO clientes (nombre, apellido, email, telefono, documento, tipo_documento, fecha_nacimiento, ciudad, pais, direccion, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                
                $stmt = $db->prepare($query);
                
                $stmt->bindParam(1, $data->nombre);
                $stmt->bindParam(2, $data->apellido);
                $stmt->bindParam(3, $data->email);
                $stmt->bindParam(4, $data->telefono);
                $stmt->bindParam(5, $data->documento);
                $stmt->bindParam(6, $data->tipo_documento);
                $stmt->bindParam(7, $data->fecha_nacimiento);
                $stmt->bindParam(8, $data->ciudad);
                $stmt->bindParam(9, $data->pais);
                $stmt->bindParam(10, $data->direccion);
                
                if($stmt->execute()) {
                    $id = $db->lastInsertId();
                    http_response_code(201);
                    echo json_encode(array(
                        "message" => "Cliente creado exitosamente.",
                        "id" => $id
                    ));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "No se pudo crear el cliente."));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Datos incompletos. Nombre, apellido y documento son requeridos."));
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
