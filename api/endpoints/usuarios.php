<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../models/Usuario.php';

// Helper para registrar errores del endpoint en un fichero dentro de api/logs/
function logApiError($context, $error) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/usuarios_errors.log';
    $entry = date('Y-m-d H:i:s') . " [{$context}] " . (is_array($error) ? json_encode($error) : $error) . PHP_EOL;
    @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
}

$database = new Database();
$db = $database->getConnection();
$usuario = new Usuario($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            // Obtener usuario por ID
            $usuario->id = $_GET['id'];
            if($usuario->getById()) {
                $usuario_arr = array(
                    "id" => $usuario->id,
                    "nombre" => $usuario->nombre,
                    "apellido" => $usuario->apellido,
                    "email" => $usuario->email,
                    "rol" => $usuario->rol,
                    "telefono" => $usuario->telefono,
                    "nacionalidad" => $usuario->nacionalidad,
                    "activo" => $usuario->activo,
                    "created_at" => $usuario->created_at
                );
                http_response_code(200);
                echo json_encode($usuario_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Usuario no encontrado."));
            }
        } else {
            // Obtener todos los usuarios
            $stmt = $usuario->getAll();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $usuarios_arr = array();
                $usuarios_arr["records"] = array();
                
                while ($row = $stmt->fetch()) {
                    $usuario_item = array(
                        "id" => $row['id'],
                        "nombre" => $row['nombre'],
                        "apellido" => $row['apellido'],
                        "email" => $row['email'],
                        "rol" => $row['rol'],
                        "telefono" => $row['telefono'],
                        "activo" => (bool)$row['activo'],
                        "created_at" => $row['created_at']
                    );
                    array_push($usuarios_arr["records"], $usuario_item);
                }
                
                http_response_code(200);
                echo json_encode($usuarios_arr);
            } else {
                http_response_code(200);
                echo json_encode(array("records" => array()));
            }
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->nombre) && !empty($data->apellido) && !empty($data->email) && !empty($data->password) && !empty($data->rol)) {
            $usuario->nombre = $data->nombre;
            $usuario->apellido = $data->apellido;
            $usuario->email = $data->email;
            $usuario->password = $data->password;
            $usuario->rol = $data->rol;
            $usuario->telefono = $data->telefono ?? "";
            $usuario->nacionalidad = $data->nacionalidad ?? "Colombia";
            $usuario->activo = $data->activo ?? 1;
            
            if(!$usuario->emailExists()) {
                if($usuario->create()) {
                    http_response_code(201);
                    echo json_encode(array("message" => "Usuario creado exitosamente."));
                } else {
                    http_response_code(503);
                    $err = $usuario->lastError ?? 'Unknown error';
                    logApiError('CREATE', $err);
                    echo json_encode(array("message" => "No se pudo crear el usuario.", "error" => $err));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "El email ya está registrado."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos."));
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $usuario->id = $data->id;
            $usuario->nombre = $data->nombre;
            $usuario->apellido = $data->apellido ?? '';
            $usuario->email = $data->email;
            $usuario->rol = $data->rol;
            $usuario->telefono = $data->telefono ?? "";
            $usuario->nacionalidad = $data->nacionalidad ?? "Colombia";
            $usuario->activo = $data->activo ?? 1;
            
            // Si se proporciona una nueva contraseña, actualizarla
            if (!empty($data->password)) {
                $usuario->password = password_hash($data->password, PASSWORD_DEFAULT);
                if($usuario->updateWithPassword()) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Usuario actualizado exitosamente."));
                } else {
                    http_response_code(503);
                    $err = $usuario->lastError ?? 'Unknown error';
                    logApiError('UPDATE', $err);
                    echo json_encode(array("message" => "No se pudo actualizar el usuario.", "error" => $err));
                }
            } else {
                // Actualizar sin cambiar la contraseña
                if($usuario->update()) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Usuario actualizado exitosamente."));
                } else {
                    http_response_code(503);
                    $err = $usuario->lastError ?? 'Unknown error';
                    logApiError('UPDATE', $err);
                    echo json_encode(array("message" => "No se pudo actualizar el usuario.", "error" => $err));
                }
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos."));
        }
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $usuario->id = $data->id;
            
            if($usuario->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "Usuario eliminado exitosamente."));
            } else {
                http_response_code(503);
                $err = $usuario->lastError ?? 'Unknown error';
                logApiError('DELETE', $err);
                echo json_encode(array("message" => "No se pudo eliminar el usuario.", "error" => $err));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "ID no proporcionado."));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Método no permitido."));
        break;
}
?>
