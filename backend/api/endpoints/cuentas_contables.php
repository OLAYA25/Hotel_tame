<?php
header("Access-Control-Allow-Origin: *");;
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../models/CuentaContable.php';

$database = new Database();
$db = $database->getConnection();

$cuenta = new CuentaContable($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            // Obtener cuenta específica
            $cuenta->id = $_GET['id'];
            
            if($cuenta->getById()) {
                $cuenta_arr = array(
                    "id" => $cuenta->id,
                    "codigo" => $cuenta->codigo,
                    "nombre" => $cuenta->nombre,
                    "tipo" => $cuenta->tipo,
                    "nivel" => $cuenta->nivel,
                    "cuenta_padre_id" => $cuenta->cuenta_padre_id,
                    "descripcion" => $cuenta->descripcion,
                    "activa" => $cuenta->activa,
                    "created_at" => $cuenta->created_at,
                    "updated_at" => $cuenta->updated_at
                );
                
                http_response_code(200);
                echo json_encode($cuenta_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Cuenta no encontrada."));
            }
        } else {
            // Obtener todas las cuentas
            $stmt = $cuenta->getAll();
            $cuentas = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cuentas[] = $row;
            }
            
            http_response_code(200);
            echo json_encode($cuentas);
        }
        break;
        
    case 'POST':
        // Crear nueva cuenta
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->codigo) && !empty($data->nombre) && !empty($data->tipo)) {
            $cuenta->codigo = $data->codigo;
            $cuenta->nombre = $data->nombre;
            $cuenta->tipo = $data->tipo;
            $cuenta->nivel = $data->nivel ?? 1;
            $cuenta->cuenta_padre_id = $data->cuenta_padre_id ?? null;
            $cuenta->descripcion = $data->descripcion ?? '';
            
            if($cuenta->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Cuenta creada exitosamente.", "id" => $cuenta->id));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo crear la cuenta."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos. Se requiere código, nombre y tipo."));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Método no permitido."));
        break;
}
?>
