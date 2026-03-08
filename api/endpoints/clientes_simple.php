<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../backend/config/database.php';
include_once '../models/ClienteSimple.php';

$database = new Database();
$db = $database->getConnection();
$cliente = new ClienteSimple($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $cliente->id = $_GET['id'];
            
            if($cliente->getById()) {
                $cliente_arr = array(
                    "id" => $cliente->id,
                    "nombre" => $cliente->nombre,
                    "apellido" => $cliente->apellido ?? '',
                    "tipo_documento" => $cliente->tipo_documento ?? null,
                    "email" => $cliente->email,
                    "telefono" => $cliente->telefono,
                    "documento" => $cliente->documento,
                    "fecha_nacimiento" => $cliente->fecha_nacimiento ?? null,
                    "ciudad" => $cliente->ciudad ?? null,
                    "pais" => $cliente->pais ?? null,
                    "direccion" => $cliente->direccion,
                    "created_at" => $cliente->created_at
                );
                http_response_code(200);
                echo json_encode($cliente_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Cliente no encontrado."));
            }
        } else {
            try {
                $stmt = $cliente->getAll();
                $num = $stmt->rowCount();
                
                if($num > 0) {
                    $clientes_arr = array();
                    $clientes_arr["records"] = array();
                    
                    while ($row = $stmt->fetch()) {
                        $cliente_item = array(
                            "id" => $row['id'],
                            "nombre" => $row['nombre'],
                            "apellido" => $row['apellido'] ?? '',
                            "tipo_documento" => $row['tipo_documento'] ?? null,
                            "email" => $row['email'],
                            "telefono" => $row['telefono'],
                            "documento" => $row['documento'],
                            "fecha_nacimiento" => $row['fecha_nacimiento'] ?? null,
                            "ciudad" => $row['ciudad'] ?? null,
                            "pais" => $row['pais'] ?? null,
                            "direccion" => $row['direccion'],
                            "created_at" => $row['created_at']
                        );
                        array_push($clientes_arr["records"], $cliente_item);
                    }
                    
                    http_response_code(200);
                    echo json_encode($clientes_arr);
                } else {
                    http_response_code(200);
                    echo json_encode(array("records" => array()));
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(array(
                    "message" => "Error al cargar clientes", 
                    "error" => $e->getMessage(),
                    "file" => $e->getFile(),
                    "line" => $e->getLine()
                ));
            }
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        // Validación básica
        if(empty($data->nombre) || empty($data->email)) {
            http_response_code(400);
            echo json_encode(array("message" => "Nombre y email son obligatorios."));
            exit;
        }
        
        // Validar tipo_documento contra los valores permitidos
        $allowedTipos = array('DNI', 'Pasaporte', 'Cedula');
        $tipo = isset($data->tipo_documento) ? trim($data->tipo_documento) : '';
        if ($tipo === '') {
            $tipo = 'DNI';
        }
        if (!in_array($tipo, $allowedTipos, true)) {
            http_response_code(400);
            echo json_encode(array("message" => "tipo_documento inválido.", "allowed" => $allowedTipos, "received" => $data->tipo_documento ?? null));
            exit;
        }

        $doc = $data->numero_documento ?? $data->documento ?? null;
        
        $cliente->nombre = $data->nombre;
        $cliente->apellido = $data->apellido ?? '';
        $cliente->tipo_documento = $tipo;
        $cliente->documento = $doc;
        $cliente->fecha_nacimiento = $data->fecha_nacimiento ?? null;
        $cliente->ciudad = $data->ciudad ?? '';
        $cliente->pais = $data->pais ?? '';
        $cliente->direccion = $data->direccion ?? "";
        $cliente->email = $data->email;
        $cliente->telefono = $data->telefono ?? "";
        
        if($cliente->create()) {
            http_response_code(201);
            echo json_encode(array("message" => "Cliente creado exitosamente."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "No se pudo crear el cliente.", "error" => $cliente->lastError));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Método no permitido."));
        break;
}
?>
