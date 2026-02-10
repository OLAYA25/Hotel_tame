<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Incluir archivos necesarios
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../models/Evento.php';
include_once __DIR__ . '/../utils/FileUpload.php';

// Obtener conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Crear objeto Evento
$evento = new Evento($db);

// Obtener método de la solicitud
$method = $_SERVER['REQUEST_METHOD'];

// Obtener ID si existe
$id = isset($_GET['id']) ? $_GET['id'] : null;

switch($method) {
    case 'GET':
        if($id) {
            // Leer un evento específico
            $evento->id = $id;
            $evento->readOne();
            
            if($evento->nombre) {
                $evento_arr = array(
                    "id" => $evento->id,
                    "nombre" => $evento->nombre,
                    "descripcion" => $evento->descripcion,
                    "tipo_evento" => $evento->tipo_evento,
                    "capacidad_maxima" => $evento->capacidad_maxima,
                    "precio_por_persona" => $evento->precio_por_persona,
                    "precio_total" => $evento->precio_total,
                    "fecha_evento" => $evento->fecha_evento,
                    "hora_inicio" => $evento->hora_inicio,
                    "hora_fin" => $evento->hora_fin,
                    "imagen_url" => $evento->imagen_url,
                    "estado" => $evento->estado,
                    "activo" => $evento->activo,
                    "created_at" => $evento->created_at,
                    "updated_at" => $evento->updated_at
                );
                
                http_response_code(200);
                echo json_encode($evento_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Evento no encontrado."));
            }
        } else {
            // Leer todos los eventos o eventos disponibles
            $disponible = isset($_GET['disponible']) ? $_GET['disponible'] : false;
            
            if($disponible === 'true') {
                $stmt = $evento->getAvailableEvents();
            } else {
                $stmt = $evento->read();
            }
            
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $eventos_arr = array();
                $eventos_arr["records"] = array();
                
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $evento_item = array(
                        "id" => $id,
                        "nombre" => $nombre,
                        "descripcion" => $descripcion,
                        "tipo_evento" => $tipo_evento,
                        "capacidad_maxima" => $capacidad_maxima,
                        "precio_por_persona" => $precio_por_persona,
                        "precio_total" => $precio_total,
                        "fecha_evento" => $fecha_evento,
                        "hora_inicio" => $hora_inicio,
                        "hora_fin" => $hora_fin,
                        "imagen_url" => $imagen_url,
                        "estado" => $estado,
                        "activo" => $activo,
                        "created_at" => $created_at,
                        "updated_at" => $updated_at
                    );
                    
                    array_push($eventos_arr["records"], $evento_item);
                }
                
                http_response_code(200);
                echo json_encode($eventos_arr);
            } else {
                http_response_code(200);
                echo json_encode(array("records" => array()));
            }
        }
        break;
        
    case 'POST':
        // Debug: log de datos recibidos
        error_log("POST request received");
        error_log("FILES: " . print_r($_FILES, true));
        error_log("POST: " . print_r($_POST, true));
        
        // Manejar subida de archivos - usar la ruta correcta
        $upload = new FileUpload(__DIR__ . '/../../assets/images/events');
        $imagen_url = '';
        
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            error_log("Processing file upload");
            $uploadResult = $upload->uploadFile($_FILES['imagen'], 'evento');
            if ($uploadResult['success']) {
                $imagen_url = 'assets/images/events/' . $uploadResult['fileName'];
                error_log("File uploaded successfully: " . $imagen_url);
            } else {
                error_log("File upload failed: " . $uploadResult['message']);
                http_response_code(400);
                echo json_encode(array("message" => $uploadResult['message']));
                break;
            }
        }
        
        // Si hay datos JSON, procesar también
        $data = json_decode(file_get_contents("php://input"));
        error_log("JSON data: " . print_r($data, true));
        
        // Si no hay JSON, usar $_POST
        if (!$data && !empty($_POST)) {
            $data = (object)$_POST;
            error_log("Using POST data: " . print_r($data, true));
        }
        
        if(!empty($data->nombre) && !empty($data->tipo_evento) && !empty($data->precio_por_persona)) {
            $evento->nombre = $data->nombre;
            $evento->descripcion = $data->descripcion ?? "";
            $evento->tipo_evento = $data->tipo_evento;
            $evento->capacidad_maxima = $data->capacidad_maxima ?? 0;
            $evento->precio_por_persona = $data->precio_por_persona;
            $evento->precio_total = $data->precio_total ?? ($data->precio_por_persona * $data->capacidad_maxima);
            $evento->fecha_evento = $data->fecha_evento ?? date('Y-m-d');
            $evento->hora_inicio = $data->hora_inicio ?? '09:00:00';
            $evento->hora_fin = $data->hora_fin ?? '17:00:00';
            $evento->imagen_url = $imagen_url ?: ($data->imagen_url ?? "");
            $evento->estado = $data->estado ?? "disponible";
            $evento->activo = isset($data->activo) ? $data->activo : true;
            
            error_log("Creating event with data: " . print_r([
                'nombre' => $evento->nombre,
                'tipo_evento' => $evento->tipo_evento,
                'precio_por_persona' => $evento->precio_por_persona,
                'imagen_url' => $evento->imagen_url
            ], true));
            
            if($evento->create()) {
                http_response_code(201);
                echo json_encode(array(
                    "message" => "Evento creado exitosamente.",
                    "imagen_url" => $imagen_url
                ));
            } else {
                error_log("Failed to create event");
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo crear el evento."));
            }
        } else {
            error_log("Missing required fields");
            error_log("nombre: " . ($data->nombre ?? 'empty'));
            error_log("tipo_evento: " . ($data->tipo_evento ?? 'empty'));
            error_log("precio_por_persona: " . ($data->precio_por_persona ?? 'empty'));
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos. Se requiere: nombre, tipo_evento y precio_por_persona"));
        }
        break;
        
    case 'PUT':
        // Debug: log de datos recibidos
        error_log("PUT request received");
        error_log("FILES: " . print_r($_FILES, true));
        error_log("POST: " . print_r($_POST, true));
        error_log("GET: " . print_r($_GET, true));
        
        // Manejar subida de archivos - usar la ruta correcta
        $upload = new FileUpload(__DIR__ . '/../../assets/images/events');
        $imagen_url = '';
        
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $upload->uploadFile($_FILES['imagen'], 'evento');
            if ($uploadResult['success']) {
                $imagen_url = 'assets/images/events/' . $uploadResult['fileName'];
            } else {
                http_response_code(400);
                echo json_encode(array("message" => $uploadResult['message']));
                break;
            }
        }
        
        // Para PUT con FormData, usar $_POST en lugar de JSON
        $data = (object)$_POST;
        error_log("Using POST data for PUT: " . print_r($data, true));
        
        if(!empty($data->id)) {
            $evento->id = $data->id;
            $evento->nombre = $data->nombre ?? "";
            $evento->descripcion = $data->descripcion ?? "";
            $evento->tipo_evento = $data->tipo_evento ?? "";
            $evento->capacidad_maxima = $data->capacidad_maxima ?? 0;
            $evento->precio_por_persona = $data->precio_por_persona ?? 0;
            $evento->precio_total = $data->precio_total ?? 0;
            $evento->fecha_evento = $data->fecha_evento ?? date('Y-m-d');
            $evento->hora_inicio = $data->hora_inicio ?? '09:00:00';
            $evento->hora_fin = $data->hora_fin ?? '17:00:00';
            $evento->imagen_url = $imagen_url ?: ($data->imagen_url ?? "");
            $evento->estado = $data->estado ?? "disponible";
            $evento->activo = isset($data->activo) ? $data->activo : true;
            
            error_log("Updating event with data: " . print_r([
                'id' => $evento->id,
                'nombre' => $evento->nombre,
                'tipo_evento' => $evento->tipo_evento,
                'precio_por_persona' => $evento->precio_por_persona,
                'imagen_url' => $evento->imagen_url
            ], true));
            
            if($evento->update()) {
                http_response_code(200);
                echo json_encode(array(
                    "message" => "Evento actualizado exitosamente.",
                    "imagen_url" => $imagen_url
                ));
            } else {
                error_log("Failed to update event");
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo actualizar el evento."));
            }
        } else {
            error_log("Missing ID for PUT request");
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos. Se requiere ID."));
        }
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $evento->id = $data->id;
            
            if($evento->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "Evento eliminado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo eliminar el evento."));
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
