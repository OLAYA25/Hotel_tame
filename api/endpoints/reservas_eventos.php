<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Incluir archivos necesarios
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/ReservaEvento.php';

// Obtener conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Crear objeto ReservaEvento
$reserva_evento = new ReservaEvento($db);

// Obtener método de la solicitud
$method = $_SERVER['REQUEST_METHOD'];

// Obtener ID si existe
$id = isset($_GET['id']) ? $_GET['id'] : null;

switch($method) {
    case 'GET':
        if($id) {
            // Leer una reserva de evento específica
            $reserva_evento->id = $id;
            $reserva_evento->readOne();
            
            if($reserva_evento->evento_id) {
                $reserva_arr = array(
                    "id" => $reserva_evento->id,
                    "evento_id" => $reserva_evento->evento_id,
                    "cliente_id" => $reserva_evento->cliente_id,
                    "fecha_reserva" => $reserva_evento->fecha_reserva,
                    "cantidad_personas" => $reserva_evento->cantidad_personas,
                    "precio_unitario" => $reserva_evento->precio_unitario,
                    "precio_total" => $reserva_evento->precio_total,
                    "estado" => $reserva_evento->estado,
                    "metodo_pago" => $reserva_evento->metodo_pago,
                    "notas" => $reserva_evento->notas
                );
                
                http_response_code(200);
                echo json_encode($reserva_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Reserva de evento no encontrada."));
            }
        } else {
            // Leer todas las reservas de eventos o filtrar por cliente/evento
            $cliente_id = isset($_GET['cliente_id']) ? $_GET['cliente_id'] : null;
            $evento_id = isset($_GET['evento_id']) ? $_GET['evento_id'] : null;
            
            if($cliente_id) {
                $stmt = $reserva_evento->getReservasByCliente($cliente_id);
            } elseif($evento_id) {
                $stmt = $reserva_evento->getReservasByEvento($evento_id);
            } else {
                $stmt = $reserva_evento->read();
            }
            
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $reservas_arr = array();
                $reservas_arr["records"] = array();
                
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $reserva_item = array(
                        "id" => $id,
                        "evento_id" => $evento_id,
                        "cliente_id" => $cliente_id,
                        "fecha_reserva" => $fecha_reserva,
                        "cantidad_personas" => $cantidad_personas,
                        "precio_unitario" => $precio_unitario,
                        "precio_total" => $precio_total,
                        "estado" => $estado,
                        "metodo_pago" => $metodo_pago,
                        "notas" => $notas,
                        "nombre_evento" => isset($nombre_evento) ? $nombre_evento : "",
                        "fecha_evento" => isset($fecha_evento) ? $fecha_evento : "",
                        "tipo_evento" => isset($tipo_evento) ? $tipo_evento : "",
                        "nombre_cliente" => isset($nombre_cliente) ? $nombre_cliente : "",
                        "apellido_cliente" => isset($apellido_cliente) ? $apellido_cliente : ""
                    );
                    
                    array_push($reservas_arr["records"], $reserva_item);
                }
                
                http_response_code(200);
                echo json_encode($reservas_arr);
            } else {
                http_response_code(200);
                echo json_encode(array("records" => array()));
            }
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->evento_id) && !empty($data->cliente_id) && !empty($data->cantidad_personas)) {
            $reserva_evento->evento_id = $data->evento_id;
            $reserva_evento->cliente_id = $data->cliente_id;
            $reserva_evento->cantidad_personas = $data->cantidad_personas;
            $reserva_evento->precio_unitario = $data->precio_unitario ?? 0;
            $reserva_evento->precio_total = $data->precio_total ?? ($data->cantidad_personas * $data->precio_unitario);
            $reserva_evento->estado = $data->estado ?? "pendiente";
            $reserva_evento->metodo_pago = $data->metodo_pago ?? "";
            $reserva_evento->notas = $data->notas ?? "";
            
            if($reserva_evento->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Reserva de evento creada exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo crear la reserva de evento."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos. Se requiere: evento_id, cliente_id y cantidad_personas"));
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $reserva_evento->id = $data->id;
            $reserva_evento->estado = $data->estado ?? "pendiente";
            $reserva_evento->metodo_pago = $data->metodo_pago ?? "";
            $reserva_evento->notas = $data->notas ?? "";
            
            if($reserva_evento->update()) {
                http_response_code(200);
                echo json_encode(array("message" => "Reserva de evento actualizada exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo actualizar la reserva de evento."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos. Se requiere ID."));
        }
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $reserva_evento->id = $data->id;
            
            if($reserva_evento->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "Reserva de evento eliminada exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo eliminar la reserva de evento."));
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
