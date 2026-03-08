<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../backend/config/database.php';
include_once '../models/Persona.php';
include_once '../models/ReservaHuesped.php';

function logPersonasError($context, $error) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
    $logFile = $logDir . '/personas_errors.log';
    $entry = date('Y-m-d H:i:s') . " [{$context}] " . (is_array($error) ? json_encode($error) : $error) . PHP_EOL;
    @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
}

$database = new Database();
$db = $database->getConnection();
$persona = new Persona($db);
$reservaHuesped = new ReservaHuesped($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            // Obtener persona específica con su historial
            $persona->id = $_GET['id'];
            
            if($persona->getById()) {
                $persona_arr = array(
                    "id" => $persona->id,
                    "nombre" => $persona->nombre,
                    "apellido" => $persona->apellido,
                    "tipo_documento" => $persona->tipo_documento,
                    "numero_documento" => $persona->numero_documento,
                    "fecha_nacimiento" => $persona->fecha_nacimiento,
                    "email" => $persona->email,
                    "telefono" => $persona->telefono,
                    "direccion" => $persona->direccion,
                    "ciudad" => $persona->ciudad,
                    "pais" => $persona->pais,
                    "tipo_persona" => $persona->tipo_persona,
                    "preferencias" => $persona->preferencias,
                    "edad_actual" => $persona->getEdadActual(),
                    "created_at" => $persona->created_at,
                    "updated_at" => $persona->updated_at
                );
                
                // Agregar historial de reservas
                $historial = $persona->getHistorialReservas();
                $estadisticas = $persona->getEstadisticas();
                
                $persona_arr["historial_reservas"] = $historial;
                $persona_arr["estadisticas"] = $estadisticas;
                
                http_response_code(200);
                echo json_encode($persona_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Persona no encontrada."));
            }
        } elseif(isset($_GET['documento'])) {
            // Buscar persona por documento
            $persona->numero_documento = $_GET['documento'];
            
            if($persona->getByDocumento()) {
                $persona_arr = array(
                    "id" => $persona->id,
                    "nombre" => $persona->nombre,
                    "apellido" => $persona->apellido,
                    "tipo_documento" => $persona->tipo_documento,
                    "numero_documento" => $persona->numero_documento,
                    "fecha_nacimiento" => $persona->fecha_nacimiento,
                    "email" => $persona->email,
                    "telefono" => $persona->telefono,
                    "tipo_persona" => $persona->tipo_persona,
                    "edad_actual" => $persona->getEdadActual(),
                    "created_at" => $persona->created_at
                );
                
                http_response_code(200);
                echo json_encode($persona_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Persona no encontrada."));
            }
        } elseif(isset($_GET['buscar'])) {
            // Búsqueda de personas
            $termino = $_GET['buscar'];
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
            
            $personas = $persona->search($termino, $limit);
            
            http_response_code(200);
            echo json_encode([
                "termino" => $termino,
                "results" => $personas,
                "total" => count($personas)
            ]);
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
            // Obtener todas las personas
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
            $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
            
            try {
                $personas = $persona->getAll($limit, $offset);
                
                http_response_code(200);
                echo json_encode([
                    "records" => $personas,
                    "total" => count($personas)
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(array(
                    "message" => "Error al cargar personas", 
                    "error" => $e->getMessage()
                ));
            }
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->nombre) && !empty($data->apellido) && !empty($data->numero_documento) && !empty($data->tipo_documento)) {
            
            // Validar documento único
            $persona->numero_documento = $data->numero_documento;
            if(!$persona->validarDocumentoUnico()) {
                http_response_code(400);
                echo json_encode(array("message" => "El número de documento ya está registrado."));
                break;
            }
            
            // Asignar datos
            $persona->nombre = $data->nombre;
            $persona->apellido = $data->apellido;
            $persona->tipo_documento = $data->tipo_documento;
            $persona->numero_documento = $data->numero_documento;
            $persona->fecha_nacimiento = $data->fecha_nacimiento ?? null;
            $persona->email = $data->email ?? null;
            $persona->telefono = $data->telefono ?? null;
            $persona->direccion = $data->direccion ?? null;
            $persona->ciudad = $data->ciudad ?? null;
            $persona->pais = $data->pais ?? 'Colombia';
            $persona->tipo_persona = $data->tipo_persona ?? 'ocasional';
            $persona->preferencias = !empty($data->preferencias) ? json_encode($data->preferencias) : null;
            
            // Iniciar transacción para crear persona y posible relación con reserva
            $db->beginTransaction();
            
            try {
                if($persona->create()) {
                    $persona_id = $persona->id;
                    
                    // Si se incluye reserva_id, crear relación
                    if (!empty($data->reserva_id)) {
                        $reservaHuesped->reserva_id = $data->reserva_id;
                        $reservaHuesped->persona_id = $persona_id;
                        $reservaHuesped->rol_en_reserva = $data->rol_en_reserva ?? 'acompanante';
                        $reservaHuesped->parentesco = $data->parentesco ?? null;
                        
                        // Calcular si es menor
                        $es_menor = false;
                        if ($persona->fecha_nacimiento) {
                            $edad = $persona->getEdadActual();
                            $es_menor = ($edad !== null && $edad < 18);
                        }
                        $reservaHuesped->es_menor = $es_menor;
                        
                        if (!$reservaHuesped->create()) {
                            throw new Exception("No se pudo crear la relación con la reserva");
                        }
                    }
                    
                    $db->commit();
                    http_response_code(201);
                    echo json_encode(array(
                        "message" => "Persona creada exitosamente.",
                        "persona_id" => $persona_id,
                        "tipo_persona" => $persona->tipo_persona
                    ));
                } else {
                    $db->rollBack();
                    http_response_code(503);
                    echo json_encode(array("message" => "No se pudo crear la persona."));
                }
            } catch (Exception $e) {
                $db->rollBack();
                http_response_code(500);
                logPersonasError('CREATE', $e->getMessage());
                echo json_encode(array("message" => "Error en la transacción.", "error" => $e->getMessage()));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos. Se requiere nombre, apellido, numero_documento y tipo_documento."));
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $persona->id = $data->id;
            
            // Obtener datos actuales para validar documento
            $persona_actual = new Persona($db);
            $persona_actual->id = $data->id;
            $persona_actual->getById();
            
            // Validar documento único (excluyendo el actual)
            if (isset($data->numero_documento) && $data->numero_documento !== $persona_actual->numero_documento) {
                $persona->numero_documento = $data->numero_documento;
                if(!$persona->validarDocumentoUnico($data->id)) {
                    http_response_code(400);
                    echo json_encode(array("message" => "El número de documento ya está registrado."));
                    break;
                }
            }
            
            // Asignar datos
            $persona->nombre = $data->nombre ?? $persona_actual->nombre;
            $persona->apellido = $data->apellido ?? $persona_actual->apellido;
            $persona->tipo_documento = $data->tipo_documento ?? $persona_actual->tipo_documento;
            $persona->numero_documento = $data->numero_documento ?? $persona_actual->numero_documento;
            $persona->fecha_nacimiento = $data->fecha_nacimiento ?? $persona_actual->fecha_nacimiento;
            $persona->email = $data->email ?? $persona_actual->email;
            $persona->telefono = $data->telefono ?? $persona_actual->telefono;
            $persona->direccion = $data->direccion ?? $persona_actual->direccion;
            $persona->ciudad = $data->ciudad ?? $persona_actual->ciudad;
            $persona->pais = $data->pais ?? $persona_actual->pais;
            $persona->tipo_persona = $data->tipo_persona ?? $persona_actual->tipo_persona;
            $persona->preferencias = isset($data->preferencias) ? json_encode($data->preferencias) : $persona_actual->preferencias;
            
            if($persona->update()) {
                // Actualizar tipo persona basado en historial
                $persona->actualizarTipoPersona();
                
                http_response_code(200);
                echo json_encode(array(
                    "message" => "Persona actualizada exitosamente.",
                    "tipo_persona_actualizado" => $persona->tipo_persona
                ));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo actualizar la persona."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "ID no proporcionado."));
        }
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $persona->id = $data->id;
            
            // Verificar si tiene reservas activas
            $historial = $persona->getHistorialReservas();
            $reservas_activas = array_filter($historial, function($reserva) {
                return in_array($reserva['estado_reserva'], ['confirmada', 'checkin']);
            });
            
            if (!empty($reservas_activas)) {
                http_response_code(400);
                echo json_encode(array(
                    "message" => "No se puede eliminar la persona. Tiene reservas activas.",
                    "reservas_activas" => count($reservas_activas)
                ));
                break;
            }
            
            if($persona->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "Persona eliminada exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo eliminar la persona."));
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
