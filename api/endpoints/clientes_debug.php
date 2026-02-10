<?php
// Habilitar todos los errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Función de depuración
function debug_log($message) {
    error_log("[CLIENTES_DEBUG] " . $message);
}

debug_log("Iniciando endpoint de clientes");

try {
    // Verificar que los archivos existan
    if (!file_exists('../config/database.php')) {
        throw new Exception('Archivo database.php no encontrado');
    }
    if (!file_exists('../models/ClienteSimple.php')) {
        throw new Exception('Archivo ClienteSimple.php no encontrado');
    }
    
    debug_log("Archivos verificados, incluyendo dependencias");
    
    include_once '../config/database.php';
    include_once '../models/ClienteSimple.php';
    
    debug_log("Dependencias incluidas");
    
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    debug_log("Conexión a base de datos establecida");
    
    $cliente = new ClienteSimple($db);
    debug_log("Modelo ClienteSimple creado");
    
    $method = $_SERVER['REQUEST_METHOD'];
    debug_log("Método HTTP: " . $method);
    
    switch($method) {
        case 'GET':
            debug_log("Procesando GET request");
            
            if(isset($_GET['id'])) {
                debug_log("Obteniendo cliente por ID: " . $_GET['id']);
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
                debug_log("Obteniendo todos los clientes");
                $stmt = $cliente->getAll();
                $num = $stmt->rowCount();
                debug_log("Número de clientes encontrados: " . $num);
                
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
                    
                    debug_log("Clientes procesados correctamente");
                    http_response_code(200);
                    echo json_encode($clientes_arr);
                } else {
                    debug_log("No se encontraron clientes");
                    http_response_code(200);
                    echo json_encode(array("records" => array()));
                }
            }
            break;
            
        default:
            debug_log("Método no permitido: " . $method);
            http_response_code(405);
            echo json_encode(array("message" => "Método no permitido."));
            break;
    }
    
} catch (Exception $e) {
    debug_log("ERROR: " . $e->getMessage());
    debug_log("FILE: " . $e->getFile());
    debug_log("LINE: " . $e->getLine());
    
    http_response_code(500);
    echo json_encode(array(
        "message" => "Error en el servidor", 
        "error" => $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine(),
        "trace" => $e->getTraceAsString()
    ));
} catch (Error $e) {
    debug_log("ERROR FATAL: " . $e->getMessage());
    debug_log("FILE: " . $e->getFile());
    debug_log("LINE: " . $e->getLine());
    
    http_response_code(500);
    echo json_encode(array(
        "message" => "Error fatal en el servidor", 
        "error" => $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine(),
        "trace" => $e->getTraceAsString()
    ));
}

debug_log("Endpoint finalizado");
?>
