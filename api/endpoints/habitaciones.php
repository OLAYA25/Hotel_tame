<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../backend/config/database.php';
include_once '../models/Habitacion.php';
include_once '../utils/RoomFileUpload.php';

$database = new Database();
$db = $database->getConnection();
$habitacion = new Habitacion($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $habitacion->id = $_GET['id'];
            if($habitacion->getById()) {
                $habitacion_arr = array(
                    "id" => $habitacion->id,
                    "numero" => $habitacion->numero,
                    "tipo" => $habitacion->tipo,
                    "precio" => $habitacion->precio_noche,
                    "estado" => $habitacion->estado,
                    "piso" => $habitacion->piso,
                    "capacidad" => $habitacion->capacidad,
                    "descripcion" => $habitacion->descripcion,
                    "imagen_url" => $habitacion->imagen_url
                );
                http_response_code(200);
                echo json_encode($habitacion_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Habitación no encontrada"));
            }
        } elseif(isset($_GET['disponibles'])) {
            $stmt = $habitacion->getDisponibles();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $habitaciones_arr = array();
                $habitaciones_arr["records"] = array();
                
                while ($row = $stmt->fetch()) {
                    $habitacion_item = array(
                        "id" => $row['id'],
                        "numero" => $row['numero'],
                        "tipo" => $row['tipo'],
                        // compatibilidad: la BD puede devolver 'precio' o 'precio_noche'
                        "precio" => isset($row['precio']) ? $row['precio'] : ($row['precio_noche'] ?? null),
                        "estado" => isset($row['estado_real']) ? $row['estado_real'] : $row['estado'],
                        "piso" => $row['piso'],
                        "capacidad" => $row['capacidad'],
                        "descripcion" => $row['descripcion'],
                        "imagen_url" => $row['imagen_url'] ?? null
                    );
                    array_push($habitaciones_arr["records"], $habitacion_item);
                }
                
                http_response_code(200);
                echo json_encode($habitaciones_arr);
            } else {
                http_response_code(200);
                echo json_encode(array('records' => []));
            }
        } elseif(isset($_GET['estado_real'])) {
            // Nuevo endpoint para obtener habitaciones con estado real según fechas actuales
            $stmt = $habitacion->getConEstadoReal();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $habitaciones_arr = array();
                $habitaciones_arr["records"] = array();
                
                while ($row = $stmt->fetch()) {
                    $habitacion_item = array(
                        "id" => $row['id'],
                        "numero" => $row['numero'],
                        "tipo" => $row['tipo'],
                        "precio" => isset($row['precio']) ? $row['precio'] : ($row['precio_noche'] ?? null),
                        "estado" => $row['estado_real'],
                        "piso" => $row['piso'],
                        "capacidad" => $row['capacidad'],
                        "descripcion" => $row['descripcion'],
                        "imagen_url" => $row['imagen_url'] ?? null
                    );
                    array_push($habitaciones_arr["records"], $habitacion_item);
                }
                
                http_response_code(200);
                echo json_encode($habitaciones_arr);
            } else {
                http_response_code(200);
                echo json_encode(array('records' => []));
            }
        } else {
            $stmt = $habitacion->getAll();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $habitaciones_arr = array();
                $habitaciones_arr["records"] = array();
                
                while ($row = $stmt->fetch()) {
                    $habitacion_item = array(
                        "id" => $row['id'],
                        "numero" => $row['numero'],
                        "tipo" => $row['tipo'],
                        "precio" => isset($row['precio']) ? $row['precio'] : ($row['precio_noche'] ?? null),
                        "estado" => $row['estado'],
                        "piso" => $row['piso'],
                        "capacidad" => $row['capacidad'],
                        "descripcion" => $row['descripcion'],
                        "imagen_url" => $row['imagen_url'] ?? null
                    );
                    array_push($habitaciones_arr["records"], $habitacion_item);
                }
                
                http_response_code(200);
                echo json_encode($habitaciones_arr);
            } else {
                http_response_code(200);
                echo json_encode(array("records" => array()));
            }
        }
        break;
        
    case 'POST':
        // Soporte para ambos formatos: FormData y JSON
        $upload = new RoomFileUpload('../../uploads/rooms');
        $imagen_url = '';
        
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            error_log("Processing room image upload");
            $uploadResult = $upload->uploadFile($_FILES['imagen'], 'habitacion');
            if ($uploadResult && $uploadResult['success']) {
                $imagen_url = 'uploads/rooms/' . $uploadResult['filename'];
                error_log("Room image uploaded successfully: " . $imagen_url);
                error_log("Format: " . ($uploadResult['format'] ?? 'unknown'));
                error_log("Compression: " . ($uploadResult['compression_ratio'] ?? 0) . "%");
            } else {
                $errorMsg = $uploadResult['message'] ?? 'Error desconocido al subir imagen';
                error_log("Room image upload failed: " . $errorMsg);
                http_response_code(400);
                echo json_encode(array("message" => $errorMsg));
                break;
            }
        }
        
        // Soporte para ambos formatos: FormData y JSON
        $data = new stdClass();
        
        // Intentar obtener desde $_POST primero (FormData)
        if (!empty($_POST)) {
            $data->numero = $_POST['numero'] ?? null;
            $data->tipo = $_POST['tipo'] ?? null;
            $data->precio_noche = $_POST['precio_noche'] ?? $_POST['precio'] ?? null;
            $data->estado = $_POST['estado'] ?? "disponible";
            $data->piso = $_POST['piso'] ?? 1;
            $data->capacidad = $_POST['capacidad'] ?? 2;
            $data->descripcion = $_POST['descripcion'] ?? "";
            $data->imagen_url = $imagen_url;
        } else {
            // Si no hay $_POST, intentar con JSON
            $json_input = file_get_contents('php://input');
            if (!empty($json_input)) {
                $data = json_decode($json_input);
                $data->imagen_url = $imagen_url;
            }
        }
        
        if(!empty($data->numero) && !empty($data->tipo) && !empty($data->precio_noche)) {
            $habitacion->numero = $data->numero;
            $habitacion->tipo = $data->tipo;
            $habitacion->precio_noche = $data->precio_noche;
            $habitacion->estado = $data->estado;
            $habitacion->piso = $data->piso;
            $habitacion->capacidad = $data->capacidad;
            $habitacion->descripcion = $data->descripcion;
            $habitacion->imagen_url = $imagen_url;
            
            if($habitacion->create()) {
                http_response_code(201);
                echo json_encode(array(
                    "message" => "Habitación creada exitosamente.",
                    "imagen_url" => $imagen_url
                ));
            } else {
                // Verificar si fue por duplicación
                $check_query = "SELECT id FROM habitaciones WHERE numero = ? AND deleted_at IS NULL";
                $check_stmt = $db->prepare($check_query);
                $check_stmt->execute([$data->numero]);
                
                if($check_stmt->rowCount() > 0) {
                    http_response_code(409); // Conflict
                    echo json_encode(array("message" => "Ya existe una habitación con el número '{$data->numero}'."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "No se pudo crear la habitación."));
                }
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos. Se requiere: numero, tipo y precio_noche"));
        }
        break;
        
    case 'PUT':
        // Manejar subida de archivos y FormData
        $upload = new RoomFileUpload('../../uploads/rooms');
        $imagen_url = '';
        $imagenAnterior = '';
        
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            error_log("Processing room image upload for UPDATE");
            $uploadResult = $upload->uploadFile($_FILES['imagen'], 'habitacion');
            if ($uploadResult && $uploadResult['success']) {
                $imagen_url = 'uploads/rooms/' . $uploadResult['filename'];
                error_log("Room image uploaded successfully: " . $imagen_url);
            } else {
                $errorMsg = $uploadResult['message'] ?? 'Error desconocido al subir imagen';
                error_log("Room image upload failed: " . $errorMsg);
                http_response_code(400);
                echo json_encode(array("message" => $errorMsg));
                break;
            }
        }
        
        // Soporte para ambos formatos: FormData y JSON
        $data = new stdClass();
        
        // Intentar obtener desde $_POST primero (FormData)
        if (!empty($_POST)) {
            $data->id = $_POST['id'] ?? null;
            $data->numero = $_POST['numero'] ?? "";
            $data->tipo = $_POST['tipo'] ?? "";
            $data->precio_noche = $_POST['precio_noche'] ?? $_POST['precio'] ?? 0;
            $data->estado = $_POST['estado'] ?? "";
            $data->piso = $_POST['piso'] ?? 1;
            $data->capacidad = $_POST['capacidad'] ?? 1;
            $data->descripcion = $_POST['descripcion'] ?? "";
            $imagenAnterior = $_POST['imagen_url'] ?? "";
            $data->imagen_url = $imagen_url ?: $imagenAnterior;
        } else {
            // Si no hay $_POST, intentar con JSON
            $json_input = file_get_contents('php://input');
            if (!empty($json_input)) {
                $data = json_decode($json_input);
                $imagenAnterior = $data->imagen_url ?? "";
                $data->imagen_url = $imagen_url ?: $imagenAnterior;
            }
        }
        
        if(!empty($data->id)) {
            $habitacion->id = $data->id;
            $habitacion->numero = $data->numero;
            $habitacion->tipo = $data->tipo;
            $habitacion->precio_noche = $data->precio_noche;
            $habitacion->estado = $data->estado;
            $habitacion->piso = $data->piso;
            $habitacion->capacidad = $data->capacidad;
            $habitacion->descripcion = $data->descripcion;
            $habitacion->imagen_url = $data->imagen_url;
            
            // Obtener imagen anterior antes de actualizar
            $habitacion->getById();
            $imagenAnteriorPath = $habitacion->imagen_url;
            
            if($habitacion->update()) {
                // Eliminar imagen anterior si se subió una nueva
                if (!empty($imagen_url) && !empty($imagenAnteriorPath) && $imagen_url !== $imagenAnteriorPath) {
                    $upload->deletePreviousImage($imagenAnteriorPath);
                }
                
                http_response_code(200);
                echo json_encode(array(
                    "message" => "Habitación actualizada exitosamente.",
                    "imagen_url" => $imagen_url
                ));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo actualizar la habitación."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos. Se requiere ID."));
        }
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $habitacion->id = $data->id;
            
            if($habitacion->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "Habitación eliminada exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo eliminar la habitación."));
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
