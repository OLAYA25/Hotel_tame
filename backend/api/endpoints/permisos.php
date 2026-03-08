<?php
header("Access-Control-Allow-Origin: *");;
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../models/Permiso.php';
include_once '../models/Modulo.php';

$database = new Database();
$db = $database->getConnection();

$permiso = new Permiso($db);
$modulo = new Modulo($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['usuario_id'])) {
            // Obtener permisos de un usuario específico
            $stmt = $permiso->getPermisosByUsuario($_GET['usuario_id']);
            $permisos_arr = array();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $permisos_arr[] = $row;
            }
            
            http_response_code(200);
            echo json_encode($permisos_arr);
        } else if(isset($_GET['rol_id'])) {
            // Obtener permisos de un rol específico
            $stmt = $permiso->getPermisosByRol($_GET['rol_id']);
            $permisos_arr = array();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $permisos_arr[] = $row;
            }
            
            http_response_code(200);
            echo json_encode($permisos_arr);
        } else if(isset($_GET['check'])) {
            // Verificar permiso específico para usuario
            if(isset($_GET['usuario_id']) && isset($_GET['permiso'])) {
                $has_permiso = $permiso->usuarioHasPermiso($_GET['usuario_id'], $_GET['permiso']);
                http_response_code(200);
                echo json_encode(array("has_permission" => $has_permiso));
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Se requiere usuario_id y permiso"));
            }
        } else {
            // Obtener todos los permisos agrupados por módulo
            $stmt = $permiso->getAllByModulo();
            $permisos_por_modulo = array();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $modulo_nombre = $row['modulo_nombre'];
                
                if (!isset($permisos_por_modulo[$modulo_nombre])) {
                    $permisos_por_modulo[$modulo_nombre] = array(
                        'nombre' => $modulo_nombre,
                        'icono' => $row['modulo_icono'],
                        'permisos' => array()
                    );
                }
                
                $permisos_por_modulo[$modulo_nombre]['permisos'][] = array(
                    'id' => $row['id'],
                    'nombre' => $row['nombre'],
                    'descripcion' => $row['descripcion'],
                    'clave' => $row['clave']
                );
            }
            
            http_response_code(200);
            echo json_encode(array_values($permisos_por_modulo));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Método no permitido."));
        break;
}
?>
