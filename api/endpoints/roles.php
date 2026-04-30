<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../backend/config/database.php';
include_once '../models/Rol.php';
include_once '../models/Permiso.php';
include_once '../models/Modulo.php';

$database = new Database();
$db = $database->getConnection();

$rol = new Rol($db);
$permiso = new Permiso($db);
$modulo = new Modulo($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            // Obtener rol específico con sus permisos
            $rol->id = $_GET['id'];
            if($rol->getById()) {
                $rol_arr = array(
                    "id" => $rol->id,
                    "nombre" => $rol->nombre,
                    "descripcion" => $rol->descripcion,
                    "nivel_acceso" => $rol->nivel_acceso,
                    "activo" => $rol->activo,
                    "created_at" => $rol->created_at,
                    "updated_at" => $rol->updated_at,
                    "permisos" => []
                );
                
                // Obtener permisos del rol
                $stmt_permisos = $rol->getPermisos();
                while ($row = $stmt_permisos->fetch(PDO::FETCH_ASSOC)) {
                    $rol_arr["permisos"][] = $row;
                }
                
                http_response_code(200);
                echo json_encode($rol_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Rol no encontrado."));
            }
        } else {
            // Obtener todos los roles
            $stmt = $rol->getAll();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $roles_arr = array();
                $roles_arr["records"] = array();
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $rol_item = array(
                        "id" => $row['id'],
                        "nombre" => $row['nombre'],
                        "descripcion" => $row['descripcion'],
                        "nivel_acceso" => $row['nivel_acceso'],
                        "activo" => $row['activo'],
                        "usuarios_count" => $row['usuarios_count'],
                        "created_at" => $row['created_at'],
                        "updated_at" => $row['updated_at']
                    );
                    array_push($roles_arr["records"], $rol_item);
                }
                
                http_response_code(200);
                echo json_encode($roles_arr);
            } else {
                http_response_code(200);
                echo json_encode(array("records" => []));
            }
        }
        break;
        
    case 'POST':
        // Crear nuevo rol
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->nombre)) {
            $rol->nombre = $data->nombre;
            $rol->descripcion = $data->descripcion ?? '';
            $rol->nivel_acceso = $data->nivel_acceso ?? 1;
            $rol->activo = $data->activo ?? true;
            
            if($rol->create()) {
                // Asignar permisos si se proporcionaron
                if (!empty($data->permisos_ids)) {
                    $rol->assignPermisos($data->permisos_ids);
                }
                
                http_response_code(201);
                echo json_encode(array("message" => "Rol creado exitosamente.", "id" => $rol->id));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo crear el rol."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos. Se requiere nombre."));
        }
        break;
        
    case 'PUT':
        // Actualizar rol
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $rol->id = $data->id;
            
            if($rol->getById()) {
                $rol->nombre = $data->nombre ?? $rol->nombre;
                $rol->descripcion = $data->descripcion ?? $rol->descripcion;
                $rol->nivel_acceso = $data->nivel_acceso ?? $rol->nivel_acceso;
                $rol->activo = $data->activo ?? $rol->activo;
                
                if($rol->update()) {
                    // Actualizar permisos si se proporcionaron
                    if (isset($data->permisos_ids)) {
                        $rol->assignPermisos($data->permisos_ids);
                    }
                    
                    http_response_code(200);
                    echo json_encode(array("message" => "Rol actualizado exitosamente."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "No se pudo actualizar el rol."));
                }
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Rol no encontrado."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos. Se requiere ID."));
        }
        break;
        
    case 'DELETE':
        // Eliminar rol
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $rol->id = $data->id;
            
            if($rol->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "Rol eliminado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo eliminar el rol."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos. Se requiere ID."));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Método no permitido."));
        break;
}
?>
