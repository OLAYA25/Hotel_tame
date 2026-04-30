<?php
header("Access-Control-Allow-Origin: *");;
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        // Validación básica
        if(empty($data->nombre) || empty($data->email)) {
            http_response_code(400);
            echo json_encode(array("message" => "Nombre y email son obligatorios."));
            exit;
        }
        
        // Validar tipo_documento
        $allowedTipos = array('DNI', 'Pasaporte', 'Cedula');
        $tipo = isset($data->tipo_documento) ? trim($data->tipo_documento) : '';
        if ($tipo === '') {
            $tipo = 'DNI';
        }
        if (!in_array($tipo, $allowedTipos, true)) {
            http_response_code(400);
            echo json_encode(array("message" => "tipo_documento inválido.", "allowed" => $allowedTipos));
            exit;
        }

        $doc = $data->numero_documento ?? $data->documento ?? null;
        
        // Validar que el documento no esté duplicado
        $check_query = "SELECT id FROM clientes WHERE documento = ? AND deleted_at IS NULL";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(1, $doc);
        $check_stmt->execute();
        
        if($check_stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(array(
                "message" => "Ya existe un cliente con este documento.",
                "error" => "duplicate_document"
            ));
            exit;
        }
        
        try {
            $query = "INSERT INTO clientes (nombre, apellido, tipo_documento, documento, email, telefono, fecha_nacimiento, ciudad, pais, direccion) 
                      VALUES (:nombre, :apellido, :tipo_documento, :documento, :email, :telefono, :fecha_nacimiento, :ciudad, :pais, :direccion)";
            
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(":nombre", $data->nombre);
            $stmt->bindParam(":apellido", $data->apellido);
            $stmt->bindParam(":tipo_documento", $tipo);
            $stmt->bindParam(":documento", $doc);
            $stmt->bindParam(":email", $data->email);
            $stmt->bindParam(":telefono", $data->telefono);
            $stmt->bindParam(":fecha_nacimiento", $data->fecha_nacimiento);
            $stmt->bindParam(":ciudad", $data->ciudad);
            $paisValue = $data->nacionalidad ?? $data->pais ?? null;
            $stmt->bindParam(":pais", $paisValue);
            $stmt->bindParam(":direccion", $data->direccion);
            
            if($stmt->execute()) {
                http_response_code(201);
                echo json_encode(array("message" => "Cliente creado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo crear el cliente."));
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array(
                "message" => "Error al crear cliente", 
                "error" => $e->getMessage()
            ));
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        
        // Validación básica
        if(empty($data->id) || empty($data->nombre) || empty($data->email)) {
            http_response_code(400);
            echo json_encode(array("message" => "ID, nombre y email son obligatorios."));
            exit;
        }
        
        // Validar tipo_documento
        $allowedTipos = array('DNI', 'Pasaporte', 'Cedula');
        $tipo = isset($data->tipo_documento) ? trim($data->tipo_documento) : '';
        if ($tipo === '') {
            $tipo = 'DNI';
        }
        if (!in_array($tipo, $allowedTipos, true)) {
            http_response_code(400);
            echo json_encode(array("message" => "tipo_documento inválido.", "allowed" => $allowedTipos));
            exit;
        }

        $doc = $data->numero_documento ?? $data->documento ?? null;
        
        // Validar que el documento no esté duplicado (excluyendo el cliente actual)
        $check_query = "SELECT id FROM clientes WHERE documento = ? AND id != ? AND deleted_at IS NULL";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(1, $doc);
        $check_stmt->bindParam(2, $data->id);
        $check_stmt->execute();
        
        if($check_stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(array(
                "message" => "Ya existe otro cliente con este documento.",
                "error" => "duplicate_document"
            ));
            exit;
        }
        
        try {
            $query = "UPDATE clientes 
                      SET nombre = :nombre, 
                          apellido = :apellido, 
                          tipo_documento = :tipo_documento, 
                          documento = :documento, 
                          email = :email, 
                          telefono = :telefono, 
                          fecha_nacimiento = :fecha_nacimiento, 
                          ciudad = :ciudad, 
                          pais = :pais, 
                          direccion = :direccion,
                          updated_at = NOW()
                      WHERE id = :id";
            
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(":id", $data->id);
            $stmt->bindParam(":nombre", $data->nombre);
            $stmt->bindParam(":apellido", $data->apellido);
            $stmt->bindParam(":tipo_documento", $tipo);
            $stmt->bindParam(":documento", $doc);
            $stmt->bindParam(":email", $data->email);
            $stmt->bindParam(":telefono", $data->telefono);
            $stmt->bindParam(":fecha_nacimiento", $data->fecha_nacimiento);
            $stmt->bindParam(":ciudad", $data->ciudad);
            $paisValue = $data->nacionalidad ?? $data->pais ?? null;
            $stmt->bindParam(":pais", $paisValue);
            $stmt->bindParam(":direccion", $data->direccion);
            
            if($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("message" => "Cliente actualizado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo actualizar el cliente."));
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array(
                "message" => "Error al actualizar cliente", 
                "error" => $e->getMessage()
            ));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Método no permitido."));
        break;
}
?>
