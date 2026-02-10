<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../models/Modulo.php';

$database = new Database();
$db = $database->getConnection();

$modulo = new Modulo($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['usuario_id'])) {
            // Obtener módulos accesibles para un usuario
            $stmt = $modulo->getModulosByUsuario($_GET['usuario_id']);
            $modulos_arr = array();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $modulos_arr[] = $row;
            }
            
            http_response_code(200);
            echo json_encode($modulos_arr);
        } else if(isset($_GET['check'])) {
            // Verificar acceso a módulo específico
            if(isset($_GET['usuario_id']) && isset($_GET['ruta'])) {
                $has_access = $modulo->usuarioHasAcceso($_GET['usuario_id'], $_GET['ruta']);
                http_response_code(200);
                echo json_encode(array("has_access" => $has_access));
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Se requiere usuario_id y ruta"));
            }
        } else {
            // Obtener todos los módulos activos
            $stmt = $modulo->getActivos();
            $modulos_arr = array();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $modulos_arr[] = $row;
            }
            
            http_response_code(200);
            echo json_encode($modulos_arr);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Método no permitido."));
        break;
}
?>
