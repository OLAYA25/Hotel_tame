<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../models/Turno.php';
include_once '../models/TipoTurno.php';

$database = new Database();
$db = $database->getConnection();

$turno = new Turno($db);
$tipoTurno = new TipoTurno($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['accion'])) {
            switch($_GET['accion']) {
                case 'turnos_usuario':
                    // Obtener turnos de un usuario específico
                    $usuario_id = $_GET['usuario_id'] ?? 0;
                    $fecha_inicio = $_GET['fecha_inicio'] ?? null;
                    $fecha_fin = $_GET['fecha_fin'] ?? null;
                    
                    $stmt = $turno->getByUsuario($usuario_id, $fecha_inicio, $fecha_fin);
                    $turnos = [];
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $turnos[] = $row;
                    }
                    
                    http_response_code(200);
                    echo json_encode($turnos);
                    break;
                    
                case 'resumen_usuario':
                    // Obtener resumen de actividades de un usuario
                    $usuario_id = $_GET['usuario_id'] ?? 0;
                    $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
                    $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
                    
                    $resumen = $turno->getResumenUsuario($usuario_id, $fecha_inicio, $fecha_fin);
                    
                    http_response_code(200);
                    echo json_encode($resumen);
                    break;
                    
                case 'actividades_usuario':
                    // Obtener todas las actividades de un usuario (turnos, reservas, pedidos, tareas)
                    $usuario_id = $_GET['usuario_id'] ?? 0;
                    $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
                    $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
                    
                    $actividades = $turno->getAllActividadesUsuario($usuario_id, $fecha_inicio, $fecha_fin);
                    
                    http_response_code(200);
                    echo json_encode($actividades);
                    break;
                    
                case 'tipos_turno':
                    // Obtener tipos de turnos activos
                    $stmt = $tipoTurno->getActivos();
                    $tipos = [];
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $tipos[] = $row;
                    }
                    
                    http_response_code(200);
                    echo json_encode($tipos);
                    break;
                    
                case 'estadisticas':
                    // Estadísticas generales de turnos
                    $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
                    $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
                    
                    $estadisticas = $turno->getEstadisticas($fecha_inicio, $fecha_fin);
                    
                    http_response_code(200);
                    echo json_encode($estadisticas);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(array("message" => "Acción no válida"));
                    break;
            }
        } else if(isset($_GET['id'])) {
            // Obtener turno específico
            $turno->id = $_GET['id'];
            
            if($turno->getById()) {
                $turno_arr = array(
                    "id" => $turno->id,
                    "usuario_id" => $turno->usuario_id,
                    "tipo_turno_id" => $turno->tipo_turno_id,
                    "fecha" => $turno->fecha,
                    "hora_entrada_real" => $turno->hora_entrada_real,
                    "hora_salida_real" => $turno->hora_salida_real,
                    "estado" => $turno->estado,
                    "notas" => $turno->notas,
                    "supervisor_id" => $turno->supervisor_id,
                    "created_at" => $turno->created_at,
                    "updated_at" => $turno->updated_at
                );
                
                http_response_code(200);
                echo json_encode($turno_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Turno no encontrado."));
            }
        } else {
            // Obtener todos los turnos con filtros
            $fecha_inicio = $_GET['fecha_inicio'] ?? null;
            $fecha_fin = $_GET['fecha_fin'] ?? null;
            $empleado_id = $_GET['empleado_id'] ?? null;
            $estado = $_GET['estado'] ?? null;
            
            $stmt = $turno->getAll($fecha_inicio, $fecha_fin, $empleado_id, $estado);
            $turnos = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $turnos[] = $row;
            }
            
            http_response_code(200);
            echo json_encode($turnos);
        }
        break;
        
    case 'POST':
        // Crear nuevo turno
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->usuario_id) && !empty($data->tipo_turno_id) && 
           !empty($data->fecha) && !empty($data->hora_inicio) && !empty($data->hora_fin)) {
            
            $turno->usuario_id = $data->usuario_id;
            $turno->tipo_turno_id = $data->tipo_turno_id;
            $turno->fecha = $data->fecha;
            $turno->hora_entrada_real = $data->hora_inicio;
            $turno->hora_salida_real = $data->hora_fin;
            $turno->estado = $data->estado ?? 'programado';
            $turno->notas = $data->notas ?? '';
            $turno->supervisor_id = $data->supervisor_id ?? null;
            
            if($turno->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Turno creado exitosamente.", "id" => $turno->id));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo crear el turno."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos. Se requiere empleado_id, tipo_turno_id, fecha_inicio y fecha_fin."));
        }
        break;
        
    case 'PUT':
        // Actualizar turno
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $turno->id = $data->id;
            
            if($turno->getById()) {
                $turno->usuario_id = $data->usuario_id ?? $turno->usuario_id;
                $turno->tipo_turno_id = $data->tipo_turno_id ?? $turno->tipo_turno_id;
                $turno->fecha = $data->fecha ?? $turno->fecha;
                $turno->hora_entrada_real = $data->hora_inicio ?? $turno->hora_entrada_real;
                $turno->hora_salida_real = $data->hora_fin ?? $turno->hora_salida_real;
                $turno->estado = $data->estado ?? $turno->estado;
                $turno->notas = $data->notas ?? $turno->notas;
                $turno->supervisor_id = $data->supervisor_id ?? $turno->supervisor_id;
                
                if($turno->update()) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Turno actualizado exitosamente."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "No se pudo actualizar el turno."));
                }
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Turno no encontrado."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos. Se requiere ID."));
        }
        break;
        
    case 'DELETE':
        // Eliminar turno (soft delete)
        if(isset($_GET['id'])) {
            $turno->id = $_GET['id'];
            
            if($turno->getById()) {
                if($turno->delete()) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Turno eliminado exitosamente."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "No se pudo eliminar el turno."));
                }
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Turno no encontrado."));
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
