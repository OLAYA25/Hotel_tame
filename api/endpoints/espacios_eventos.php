<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Incluir archivos necesarios
include_once '../../backend/config/database.php';
include_once __DIR__ . '/../models/EspacioEvento.php';
include_once __DIR__ . '/../utils/FileUpload.php';

// Obtener conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Crear objeto EspacioEvento
$espacio = new EspacioEvento($db);

// Obtener método de la solicitud
$method = $_SERVER['REQUEST_METHOD'];

// Obtener ID si existe
$id = isset($_GET['id']) ? $_GET['id'] : null;

switch($method) {
    case 'GET':
        if($id) {
            // Leer un espacio de evento específico
            $espacio->id = $id;
            $espacio->readOne();
            
            if($espacio->nombre) {
                $espacio_arr = array(
                    "id" => $espacio->id,
                    "nombre" => $espacio->nombre,
                    "descripcion" => $espacio->descripcion,
                    "tipo_espacio" => $espacio->tipo_espacio,
                    "capacidad_maxima" => $espacio->capacidad_maxima,
                    "precio_hora" => $espacio->precio_hora,
                    "precio_completo" => $espacio->precio_completo,
                    "ubicacion" => $espacio->ubicacion,
                    "caracteristicas" => $espacio->caracteristicas,
                    "imagen_url" => $espacio->imagen_url,
                    "estado" => $espacio->estado,
                    "activo" => $espacio->activo,
                    "created_at" => $espacio->created_at,
                    "updated_at" => $espacio->updated_at
                );
                
                http_response_code(200);
                echo json_encode($espacio_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Espacio de evento no encontrado."));
            }
        } else {
            // Leer todos los espacios o espacios disponibles
            $disponible = isset($_GET['disponible']) ? $_GET['disponible'] : false;
            
            if($disponible === 'true') {
                $stmt = $espacio->getAvailableSpaces();
            } else {
                $stmt = $espacio->read();
            }
            
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $espacios_arr = array();
                $espacios_arr["records"] = array();
                
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $espacio_item = array(
                        "id" => $id,
                        "nombre" => $nombre,
                        "descripcion" => $descripcion,
                        "tipo_espacio" => $tipo_espacio,
                        "capacidad_maxima" => $capacidad_maxima,
                        "precio_hora" => $precio_hora,
                        "precio_completo" => $precio_completo,
                        "ubicacion" => $ubicacion,
                        "caracteristicas" => $caracteristicas,
                        "imagen_url" => $imagen_url,
                        "estado" => $estado,
                        "activo" => $activo,
                        "created_at" => $created_at,
                        "updated_at" => $updated_at
                    );
                    
                    array_push($espacios_arr["records"], $espacio_item);
                }
                
                http_response_code(200);
                echo json_encode($espacios_arr);
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
        $upload = new FileUpload(__DIR__ . '/../../assets/images/spaces');
        $imagen_url = '';
        
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            error_log("Processing file upload");
            $uploadResult = $upload->uploadFile($_FILES['imagen'], 'espacio');
            if ($uploadResult['success']) {
                $imagen_url = 'assets/images/spaces/' . $uploadResult['fileName'];
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
        
        if(!empty($data->nombre) && !empty($data->tipo_espacio) && !empty($data->precio_hora)) {
            $espacio->nombre = $data->nombre;
            $espacio->descripcion = $data->descripcion ?? "";
            $espacio->tipo_espacio = $data->tipo_espacio;
            $espacio->capacidad_maxima = $data->capacidad_maxima ?? 0;
            $espacio->precio_hora = $data->precio_hora;
            $espacio->precio_completo = $data->precio_completo ?? ($data->precio_hora * 8); // 8 horas por defecto
            $espacio->ubicacion = $data->ubicacion ?? "";
            $espacio->caracteristicas = $data->caracteristicas ?? "";
            $espacio->imagen_url = $imagen_url ?: ($data->imagen_url ?? "");
            $espacio->estado = $data->estado ?? "disponible";
            $espacio->activo = isset($data->activo) ? $data->activo : true;
            
            error_log("Creating space with data: " . print_r([
                'nombre' => $espacio->nombre,
                'tipo_espacio' => $espacio->tipo_espacio,
                'precio_hora' => $espacio->precio_hora,
                'imagen_url' => $espacio->imagen_url
            ], true));
            
            if($espacio->create()) {
                http_response_code(201);
                echo json_encode(array(
                    "message" => "Espacio de evento creado exitosamente.",
                    "imagen_url" => $imagen_url
                ));
            } else {
                error_log("Failed to create space");
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo crear el espacio de evento."));
            }
        } else {
            error_log("Missing required fields");
            error_log("nombre: " . ($data->nombre ?? 'empty'));
            error_log("tipo_espacio: " . ($data->tipo_espacio ?? 'empty'));
            error_log("precio_hora: " . ($data->precio_hora ?? 'empty'));
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos. Se requiere: nombre, tipo_espacio y precio_hora"));
        }
        break;
        
    case 'PUT':
        // Debug: log de datos recibidos
        error_log("PUT request received");
        error_log("FILES: " . print_r($_FILES, true));
        error_log("POST: " . print_r($_POST, true));
        error_log("GET: " . print_r($_GET, true));
        
        // Manejar subida de archivos - usar la ruta correcta
        $upload = new FileUpload(__DIR__ . '/../../assets/images/spaces');
        $imagen_url = '';
        
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $upload->uploadFile($_FILES['imagen'], 'espacio');
            if ($uploadResult['success']) {
                $imagen_url = 'assets/images/spaces/' . $uploadResult['fileName'];
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
            $espacio->id = $data->id;
            $espacio->nombre = $data->nombre ?? "";
            $espacio->descripcion = $data->descripcion ?? "";
            $espacio->tipo_espacio = $data->tipo_espacio ?? "";
            $espacio->capacidad_maxima = $data->capacidad_maxima ?? 0;
            $espacio->precio_hora = $data->precio_hora ?? 0;
            $espacio->precio_completo = $data->precio_completo ?? 0;
            $espacio->ubicacion = $data->ubicacion ?? "";
            $espacio->caracteristicas = $data->caracteristicas ?? "";
            $espacio->imagen_url = $imagen_url ?: ($data->imagen_url ?? "");
            $espacio->estado = $data->estado ?? "disponible";
            $espacio->activo = isset($data->activo) ? $data->activo : true;
            
            error_log("Updating space with data: " . print_r([
                'id' => $espacio->id,
                'nombre' => $espacio->nombre,
                'tipo_espacio' => $espacio->tipo_espacio,
                'precio_hora' => $espacio->precio_hora,
                'imagen_url' => $espacio->imagen_url
            ], true));
            
            if($espacio->update()) {
                http_response_code(200);
                echo json_encode(array(
                    "message" => "Espacio de evento actualizado exitosamente.",
                    "imagen_url" => $imagen_url
                ));
            } else {
                error_log("Failed to update space");
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo actualizar el espacio de evento."));
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
            $espacio->id = $data->id;
            
            if($espacio->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "Espacio de evento eliminado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo eliminar el espacio de evento."));
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
