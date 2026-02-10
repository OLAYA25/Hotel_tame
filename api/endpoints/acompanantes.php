<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../models/Acompanante.php';

$database = new Database();
$db = $database->getConnection();

$acompanante = new Acompanante($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['reserva_id'])) {
            // Obtener acompañantes por reserva
            $acompanante->reserva_id = $_GET['reserva_id'];
            $stmt = $acompanante->getByReserva($acompanante->reserva_id);
            $acompanantes = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $acompanantes[] = $row;
            }
            
            http_response_code(200);
            echo json_encode($acompanantes);
            
        } elseif(isset($_GET['estadisticas'])) {
            // Obtener estadísticas de ocupación
            $fecha_inicio = $_GET['fecha_inicio'] ?? null;
            $fecha_fin = $_GET['fecha_fin'] ?? null;
            
            $estadisticas = $acompanante->getEstadisticasOcupacion($fecha_inicio, $fecha_fin);
            
            http_response_code(200);
            echo json_encode($estadisticas);
            
        } elseif(isset($_GET['ocupacion_real'])) {
            // Obtener ocupación real
            $fecha_inicio = $_GET['fecha_inicio'] ?? null;
            $fecha_fin = $_GET['fecha_fin'] ?? null;
            
            $ocupacion = $acompanante->getOcupacionReal($fecha_inicio, $fecha_fin);
            
            http_response_code(200);
            echo json_encode($ocupacion);
            
        } else {
            // Obtener todos los acompañantes
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
            $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
            
            $stmt = $acompanante->getAll($limit, $offset);
            $acompanantes = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $acompanantes[] = $row;
            }
            
            http_response_code(200);
            echo json_encode([
                "records" => $acompanantes,
                "total" => count($acompanantes)
            ]);
        }
        break;
        
    case 'POST':
        // Crear nuevo acompañante
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->reserva_id) && !empty($data->nombre) && !empty($data->apellido) && 
           !empty($data->tipo_documento) && !empty($data->numero_documento)) {
            
            $acompanante->reserva_id = $data->reserva_id;
            $acompanante->nombre = $data->nombre;
            $acompanante->apellido = $data->apellido;
            $acompanante->tipo_documento = $data->tipo_documento;
            $acompanante->numero_documento = $data->numero_documento;
            $acompanante->fecha_nacimiento = $data->fecha_nacimiento ?? null;
            $acompanante->parentesco = $data->parentesco ?? 'Otro';
            
            // Validar documento único
            if(!$acompanante->validarDocumentoUnico($acompanante->reserva_id, $acompanante->numero_documento)) {
                http_response_code(400);
                echo json_encode(["message" => "El número de documento ya está registrado para esta reserva"]);
                break;
            }
            
            if($acompanante->create()) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Acompañante creado exitosamente",
                    "id" => $acompanante->id
                ]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo crear el acompañante"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos. Se requiere reserva_id, nombre, apellido, tipo_documento y numero_documento"]);
        }
        break;
        
    case 'PUT':
        // Actualizar acompañante
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $acompanante->id = $data->id;
            
            // Obtener datos actuales
            $stmt = $acompanante->conn->prepare("SELECT * FROM acompanantes WHERE id = ?");
            $stmt->bindParam(1, $acompanante->id);
            $stmt->execute();
            $actual = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($actual) {
                $acompanante->nombre = $data->nombre ?? $actual['nombre'];
                $acompanante->apellido = $data->apellido ?? $actual['apellido'];
                $acompanante->tipo_documento = $data->tipo_documento ?? $actual['tipo_documento'];
                $acompanante->numero_documento = $data->numero_documento ?? $actual['numero_documento'];
                $acompanante->fecha_nacimiento = $data->fecha_nacimiento ?? $actual['fecha_nacimiento'];
                $acompanante->parentesco = $data->parentesco ?? $actual['parentesco'];
                
                // Validar documento único (excluyendo el actual)
                if(!$acompanante->validarDocumentoUnico($actual['reserva_id'], $acompanante->numero_documento, $acompanante->id)) {
                    http_response_code(400);
                    echo json_encode(["message" => "El número de documento ya está registrado para esta reserva"]);
                    break;
                }
                
                if($acompanante->update()) {
                    http_response_code(200);
                    echo json_encode(["message" => "Acompañante actualizado exitosamente"]);
                } else {
                    http_response_code(503);
                    echo json_encode(["message" => "No se pudo actualizar el acompañante"]);
                }
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Acompañante no encontrado"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "ID de acompañante no proporcionado"]);
        }
        break;
        
    case 'DELETE':
        if(isset($_GET['id'])) {
            // Eliminar acompañante específico
            $acompanante->id = $_GET['id'];
            
            if($acompanante->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Acompañante eliminado exitosamente"]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudo eliminar el acompañante"]);
            }
        } elseif(isset($_GET['reserva_id'])) {
            // Eliminar todos los acompañantes de una reserva
            $acompanante->reserva_id = $_GET['reserva_id'];
            
            if($acompanante->deleteByReserva($acompanante->reserva_id)) {
                http_response_code(200);
                echo json_encode(["message" => "Acompañantes de la reserva eliminados exitosamente"]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "No se pudieron eliminar los acompañantes"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "ID de acompañante o reserva no proporcionado"]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(["message" => "Método no permitido"]);
        break;
}
?>
