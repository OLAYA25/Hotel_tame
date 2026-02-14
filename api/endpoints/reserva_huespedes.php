<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../models/ReservaHuesped.php';
include_once '../models/Persona.php';

function logReservaHuespedesError($context, $error) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
    $logFile = $logDir . '/reserva_huespedes_errors.log';
    $entry = date('Y-m-d H:i:s') . " [{$context}] " . (is_array($error) ? json_encode($error) : $error) . PHP_EOL;
    @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
}

$database = new Database();
$db = $database->getConnection();
$reservaHuesped = new ReservaHuesped($db);
$persona = new Persona($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['reserva_id'])) {
            // Obtener huéspedes de una reserva específica
            $reserva_id = $_GET['reserva_id'];
            $huespedes = $reservaHuesped->getByReserva($reserva_id);
            
            // Agregar ocupación real
            $ocupacion = $reservaHuesped->getOcupacionReal($reserva_id);
            
            http_response_code(200);
            echo json_encode([
                "reserva_id" => $reserva_id,
                "huespedes" => $huespedes,
                "ocupacion_real" => $ocupacion
            ]);
        } elseif(isset($_GET['persona_id'])) {
            // Obtener reservas de una persona específica
            $persona_id = $_GET['persona_id'];
            $reservas = $reservaHuesped->getByPersona($persona_id);
            
            http_response_code(200);
            echo json_encode([
                "persona_id" => $persona_id,
                "reservas" => $reservas,
                "total" => count($reservas)
            ]);
        } elseif(isset($_GET['estadisticas'])) {
            // Obtener estadísticas generales
            $fecha_inicio = $_GET['fecha_inicio'] ?? null;
            $fecha_fin = $_GET['fecha_fin'] ?? null;
            
            $estadisticas = $reservaHuesped->getEstadisticasGenerales($fecha_inicio, $fecha_fin);
            
            http_response_code(200);
            echo json_encode($estadisticas);
        } elseif(isset($_GET['frecuentes'])) {
            // Obtener personas frecuentes
            $limite = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
            $frecuentes = $reservaHuesped->getPersonasFrecuentes($limite);
            
            http_response_code(200);
            echo json_encode([
                "personas_frecuentes" => $frecuentes,
                "total" => count($frecuentes)
            ]);
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Se requiere reserva_id o persona_id."));
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->reserva_id) && !empty($data->persona_id) && !empty($data->rol_en_reserva)) {
            
            // Verificar que la persona no ya esté en la reserva
            if($reservaHuesped->existsInReserva($data->reserva_id, $data->persona_id)) {
                http_response_code(400);
                echo json_encode(array("message" => "La persona ya está registrada en esta reserva."));
                break;
            }
            
            // Validar que solo haya un principal por reserva
            if($data->rol_en_reserva === 'principal') {
                if(!$reservaHuesped->validarUnicoPrincipal($data->reserva_id)) {
                    http_response_code(400);
                    echo json_encode(array("message" => "Ya existe un huésped principal en esta reserva."));
                    break;
                }
            }
            
            // Asignar datos
            $reservaHuesped->reserva_id = $data->reserva_id;
            $reservaHuesped->persona_id = $data->persona_id;
            $reservaHuesped->rol_en_reserva = $data->rol_en_reserva;
            $reservaHuesped->parentesco = $data->parentesco ?? null;
            
            // Calcular si es menor
            $persona->id = $data->persona_id;
            $persona->getById();
            $es_menor = false;
            if ($persona->fecha_nacimiento) {
                $edad = $persona->getEdadActual();
                $es_menor = ($edad !== null && $edad < 18);
            }
            $reservaHuesped->es_menor = $es_menor;
            
            if($reservaHuesped->create()) {
                // Actualizar tipo de persona si es necesario
                $persona->actualizarTipoPersona();
                
                http_response_code(201);
                echo json_encode(array(
                    "message" => "Huésped agregado a la reserva exitosamente.",
                    "id" => $reservaHuesped->id,
                    "es_menor" => $es_menor
                ));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo agregar el huésped a la reserva."));
            }
        } elseif(!empty($data->reserva_id) && !empty($data->huespedes)) {
            // Agregar múltiples huéspedes a una reserva
            $db->beginTransaction();
            
            try {
                $huespedes_data = [];
                
                foreach ($data->huespedes as $huesped) {
                    // Verificar si la persona existe, si no, crearla
                    $persona_existente = new Persona($db);
                    $persona_existente->numero_documento = $huesped->numero_documento;
                    
                    if(!$persona_existente->getByDocumento()) {
                        // Crear nueva persona
                        $persona_existente->nombre = $huesped->nombre;
                        $persona_existente->apellido = $huesped->apellido;
                        $persona_existente->tipo_documento = $huesped->tipo_documento;
                        $persona_existente->numero_documento = $huesped->numero_documento;
                        $persona_existente->fecha_nacimiento = $huesped->fecha_nacimiento ?? null;
                        $persona_existente->email = $huesped->email ?? null;
                        $persona_existente->telefono = $huesped->telefono ?? null;
                        $persona_existente->tipo_persona = 'ocasional';
                        
                        if(!$persona_existente->create()) {
                            throw new Exception("No se pudo crear la persona: {$huesped->nombre} {$huesped->apellido}");
                        }
                    }
                    
                    // Calcular si es menor
                    $es_menor = false;
                    if ($persona_existente->fecha_nacimiento) {
                        $edad = $persona_existente->getEdadActual();
                        $es_menor = ($edad !== null && $edad < 18);
                    }
                    
                    $huespedes_data[] = [
                        'persona_id' => $persona_existente->id,
                        'rol_en_reserva' => $huesped->rol_en_reserva ?? 'acompanante',
                        'parentesco' => $huesped->parentesco ?? null,
                        'es_menor' => $es_menor
                    ];
                }
                
                // Agregar todos los huéspedes a la reserva
                if($reservaHuesped->agregarHuespedesAReserva($data->reserva_id, $huespedes_data)) {
                    $db->commit();
                    
                    http_response_code(201);
                    echo json_encode(array(
                        "message" => "Huéspedes agregados a la reserva exitosamente.",
                        "total_huespedes" => count($huespedes_data)
                    ));
                } else {
                    throw new Exception("No se pudieron agregar los huéspedes a la reserva");
                }
            } catch (Exception $e) {
                $db->rollBack();
                http_response_code(500);
                logReservaHuespedesError('BULK_CREATE', $e->getMessage());
                echo json_encode(array("message" => "Error en la transacción.", "error" => $e->getMessage()));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos. Se requiere reserva_id, persona_id y rol_en_reserva, o reserva_id con lista de huespedes."));
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $reservaHuesped->id = $data->id;
            
            // Obtener datos actuales
            $query = "SELECT * FROM reserva_huespedes WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $reservaHuesped->id);
            $stmt->execute();
            $actual = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($actual) {
                // Validar cambio de rol a principal
                if(isset($data->rol_en_reserva) && $data->rol_en_reserva === 'principal' && $actual['rol_en_reserva'] !== 'principal') {
                    if(!$reservaHuesped->validarUnicoPrincipal($actual['reserva_id'], $reservaHuesped->id)) {
                        http_response_code(400);
                        echo json_encode(array("message" => "Ya existe un huésped principal en esta reserva."));
                        break;
                    }
                }
                
                $reservaHuesped->rol_en_reserva = $data->rol_en_reserva ?? $actual['rol_en_reserva'];
                $reservaHuesped->parentesco = $data->parentesco ?? $actual['parentesco'];
                $reservaHuesped->es_menor = $data->es_menor ?? $actual['es_menor'];
                
                if($reservaHuesped->update()) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Relación huésped-reserva actualizada exitosamente."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "No se pudo actualizar la relación."));
                }
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Relación huésped-reserva no encontrada."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "ID no proporcionado."));
        }
        break;
        
    case 'DELETE':
        if(isset($_GET['id'])) {
            // Eliminar relación específica
            $reservaHuesped->id = $_GET['id'];
            
            if($reservaHuesped->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "Huésped removido de la reserva exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo remover el huésped de la reserva."));
            }
        } elseif(isset($_GET['reserva_id'])) {
            // Eliminar todos los huéspedes de una reserva
            $reserva_id = $_GET['reserva_id'];
            
            if($reservaHuesped->deleteByReserva($reserva_id)) {
                http_response_code(200);
                echo json_encode(array("message" => "Todos los huéspedes removidos de la reserva exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudieron remover los huéspedes de la reserva."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "ID de relación o reserva no proporcionado."));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Método no permitido."));
        break;
}
?>
