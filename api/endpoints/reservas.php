<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../models/Reserva.php';
include_once '../models/Habitacion.php';

$database = new Database();
$db = $database->getConnection();
$reserva = new Reserva($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Actualizar automáticamente reservas completadas antes de cualquier consulta
        $actualizadas = $reserva->actualizarReservasCompletadas();
        
        // Logging para debugging (opcional)
        if ($actualizadas > 0) {
            error_log("Reservas actualizadas automáticamente: " . $actualizadas);
        }
        
        if(isset($_GET['accion']) && $_GET['accion'] === 'actualizar_completadas') {
            // Endpoint específico para actualizar reservas completadas
            $pendientes = $reserva->getReservasPendientesActualizacion();
            $actualizadas = $reserva->actualizarReservasCompletadas();
            
            http_response_code(200);
            echo json_encode(array(
                "message" => "Proceso completado",
                "reservas_pendientes" => count($pendientes),
                "reservas_actualizadas" => $actualizadas,
                "detalles" => $pendientes
            ));
            break;
        }
        
        if(isset($_GET['id'])) {
            $reserva->id = $_GET['id'];
            if($reserva->getById()) {
                $reserva_arr = array(
                    "id" => $reserva->id,
                    "cliente_id" => $reserva->cliente_id,
                    "habitacion_id" => $reserva->habitacion_id,
                    "fecha_entrada" => $reserva->fecha_entrada,
                    "fecha_salida" => $reserva->fecha_salida,
                    "estado" => $reserva->estado,
                    "total" => $reserva->total,
                    "metodo_pago" => $reserva->metodo_pago,
                    "noches" => $reserva->noches,
                    "num_huespedes" => $reserva->num_huespedes,
                    "numero_huespedes" => $reserva->numero_huespedes,
                    "observaciones" => $reserva->notas,
                    "created_at" => $reserva->created_at
                );
                http_response_code(200);
                echo json_encode($reserva_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Reserva no encontrada."));
            }
        } elseif(isset($_GET['recent'])) {
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
            $stmt = $reserva->getRecent($limit);
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $reservas_arr = array();
                $reservas_arr["records"] = array();
                
                while ($row = $stmt->fetch()) {
                    $reserva_item = array(
                        "id" => $row['id'],
                        "cliente_id" => $row['cliente_id'],
                        "cliente_nombre" => $row['cliente_nombre'],
                        "habitacion_id" => $row['habitacion_id'],
                        "habitacion_numero" => $row['habitacion_numero'],
                        "habitacion_tipo" => $row['habitacion_tipo'],
                        "fecha_entrada" => $row['fecha_entrada'],
                        "fecha_salida" => $row['fecha_salida'],
                        "estado" => $row['estado'],
                        "total" => $row['total'],
                        "metodo_pago" => $row['metodo_pago'],
                        "noches" => $row['noches'],
                        "num_huespedes" => $row['num_huespedes'],
                        "numero_huespedes" => $row['num_huespedes'],
                        "created_at" => $row['created_at']
                    );
                    array_push($reservas_arr["records"], $reserva_item);
                }
                
                http_response_code(200);
                echo json_encode($reservas_arr);
            } else {
                http_response_code(200);
                echo json_encode(array("records" => array()));
            }
        } else {
            $stmt = $reserva->getAll();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $reservas_arr = array();
                $reservas_arr["records"] = array();
                
                while ($row = $stmt->fetch()) {
                    $reserva_item = array(
                        "id" => $row['id'],
                        "cliente_id" => $row['cliente_id'],
                        "cliente_nombre" => $row['cliente_nombre'],
                        "cliente_email" => $row['cliente_email'],
                        "cliente_telefono" => $row['cliente_telefono'],
                        "habitacion_id" => $row['habitacion_id'],
                        "habitacion_numero" => $row['habitacion_numero'],
                        "habitacion_tipo" => $row['habitacion_tipo'],
                        "fecha_entrada" => $row['fecha_entrada'],
                        "fecha_salida" => $row['fecha_salida'],
                        "estado" => $row['estado'],
                        "total" => $row['total'],
                        "metodo_pago" => $row['metodo_pago'],
                        "noches" => $row['noches'],
                        "num_huespedes" => $row['num_huespedes'],
                        "numero_huespedes" => $row['num_huespedes'],
                        "created_at" => $row['created_at']
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
        
        // Logging para debugging
        error_log("POST data received: " . json_encode($data));
        
        if(!empty($data->cliente_id) && !empty($data->habitacion_id) && 
           !empty($data->fecha_entrada) && !empty($data->fecha_salida)) {
            
            $reserva->habitacion_id = $data->habitacion_id;
            $reserva->fecha_entrada = $data->fecha_entrada;
            $reserva->fecha_salida = $data->fecha_salida;
            
            if($reserva->verificarDisponibilidad()) {
                // Calcular número de noches si no se envía
                $noches = null;
                if (isset($data->noches) && !empty($data->noches)) {
                    $noches = intval($data->noches);
                } elseif (!empty($data->fecha_entrada) && !empty($data->fecha_salida)) {
                    try {
                        $d1 = new DateTime($data->fecha_entrada);
                        $d2 = new DateTime($data->fecha_salida);
                        $interval = $d1->diff($d2);
                        $noches = max(1, intval($interval->days));
                    } catch (Exception $e) {
                        $noches = 1;
                    }
                } else {
                    $noches = 1;
                }

                // Obtener precio por noche de la habitación
                $stmtH = $db->prepare('SELECT precio_noche FROM habitaciones WHERE id = :id AND deleted_at IS NULL');
                $stmtH->bindParam(':id', $reserva->habitacion_id, PDO::PARAM_INT);
                $stmtH->execute();
                $rowH = $stmtH->fetch(PDO::FETCH_ASSOC);
                $precioNoche = $rowH['precio_noche'] ?? 0;

                $reserva->cliente_id = $data->cliente_id;
                $reserva->estado = $data->estado ?? "pendiente";
                $reserva->precio_noche = $precioNoche;
                $reserva->noches = $noches;
                $reserva->total = isset($data->total) && !empty($data->total) ? $data->total : ($precioNoche * $noches);
                $reserva->metodo_pago = $data->metodo_pago ?? "Efectivo";
                $reserva->notas = $data->observaciones ?? null;
                
                // Usar el número de huéspedes del frontend
                $numero_huespedes = intval($data->numero_huespedes ?? 1);
                error_log("Huéspedes del frontend: " . $numero_huespedes);
                $reserva->num_huespedes = $numero_huespedes; // Usar el campo real de la BD
                
                if($reserva->create()) {
                    $reserva_id = $db->lastInsertId();
                    
                    // Procesar acompañantes si existen
                    if (!empty($data->acompanantes) && is_array($data->acompanantes)) {
                        try {
                            $stmt = $db->prepare("SHOW TABLES LIKE 'acompanantes'");
                            $stmt->execute();
                            $tableExists = $stmt->rowCount() > 0;
                            
                            if ($tableExists) {
                                include_once '../models/Acompanante.php';
                                $acompanante = new Acompanante($db);
                                
                                foreach ($data->acompanantes as $acompanante_data) {
                                    $acompanante->reserva_id = $reserva_id;
                                    $acompanante->nombre = $acompanante_data->nombre;
                                    $acompanante->apellido = $acompanante_data->apellido;
                                    $acompanante->tipo_documento = $acompanante_data->tipo_documento;
                                    $acompanante->numero_documento = $acompanante_data->numero_documento;
                                    $acompanante->parentesco = $acompanante_data->parentesco ?? 'Otro';
                                    
                                    // Crear acompañante
                                    $acompanante->create();
                                }
                            } else {
                                // La tabla no existe, guardar acompañantes como JSON en observaciones
                                error_log("Tabla acompanantes no existe, guardando como JSON en observaciones");
                                
                                $observaciones = $data->observaciones ?? '';
                                // eliminar bloque previo ACOMPANANTES si existe
                                $observaciones = preg_replace("/\\n\\n?ACOMPANANTES:\\n[\\s\\S]*$/", "", $observaciones);
                                $observaciones = trim($observaciones);
                                
                                // Solo guardar si hay acompañantes
                                if (!empty($data->acompanantes) && count($data->acompanantes) > 0) {
                                    $acompanantes_json = json_encode($data->acompanantes);
                                    if (!empty($observaciones)) {
                                        $observaciones .= "\n\n";
                                    }
                                    $observaciones .= "ACOMPANANTES:\n" . $acompanantes_json;
                                }
                                
                                $update_stmt = $db->prepare("UPDATE reservas SET notas = ? WHERE id = ?");
                                $update_stmt->execute([$observaciones, $reserva_id]);
                            }
                        } catch (Exception $e) {
                            error_log("Error al procesar acompañantes: " . $e->getMessage());
                            // Continuar con la creación de la reserva aunque falle el procesamiento de acompañantes
                        }
                    }
                    
                    // Actualizar estado de la habitación a 'ocupada'
                    $habitacion = new Habitacion($db);
                    $habitacion->id = $reserva->habitacion_id;
                    $habitacion->estado = 'ocupada';
                    $habitacion->cambiarEstado();
                    
                    // Verificar si se cancelaron automáticamente reservas pendientes
                    $cancelaciones = $reserva->getCancelacionesRecientes($reserva->habitacion_id, $reserva->id, $reserva->fecha_entrada, $reserva->fecha_salida);
                    
                    $response = array("message" => "Reserva creada exitosamente.");
                    
                    if (!empty($data->acompanantes) && is_array($data->acompanantes)) {
                        $response["acompanantes_registrados"] = count($data->acompanantes);
                        $response["message"] .= " Se registraron " . count($data->acompanantes) . " acompañante(s).";
                    }
                    
                    if (!empty($cancelaciones)) {
                        $response["cancelaciones_automaticas"] = $cancelaciones;
                        $response["message"] .= " Se cancelaron automáticamente " . count($cancelaciones) . " reserva(s) pendiente(s) por conflicto.";
                    }
                    
                    http_response_code(201);
                    echo json_encode($response);
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "No se pudo crear la reserva."));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "La habitación no está disponible para las fechas seleccionadas."));
            }
        } else {
            http_response_code(400);
            $missing_fields = [];
            if (empty($data->cliente_id)) $missing_fields[] = "cliente_id";
            if (empty($data->habitacion_id)) $missing_fields[] = "habitacion_id";
            if (empty($data->fecha_entrada)) $missing_fields[] = "fecha_entrada";
            if (empty($data->fecha_salida)) $missing_fields[] = "fecha_salida";
            
            echo json_encode(array(
                "message" => "Datos incompletos. Campos requeridos: " . implode(", ", $missing_fields),
                "missing_fields" => $missing_fields,
                "received_data" => $data
            ));
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $reserva->id = $data->id;
            
            // Si solo se está actualizando el estado, usar método específico
            if (isset($data->estado) && count((array)$data) == 2) {
                $reserva->estado = $data->estado;
                
                if($reserva->updateEstado()) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Estado de reserva actualizado exitosamente."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "No se pudo actualizar el estado de la reserva."));
                }
            } else {
                // Actualización completa de la reserva
                $reserva->cliente_id = $data->cliente_id;
                $reserva->habitacion_id = $data->habitacion_id;
                $reserva->fecha_entrada = $data->fecha_entrada;
                $reserva->fecha_salida = $data->fecha_salida;
                $reserva->estado = $data->estado;
                // Calcular noches si no viene
                if (isset($data->noches) && !empty($data->noches)) {
                    $noches = intval($data->noches);
                } elseif (!empty($data->fecha_entrada) && !empty($data->fecha_salida)) {
                    try {
                        $d1 = new DateTime($data->fecha_entrada);
                        $d2 = new DateTime($data->fecha_salida);
                        $interval = $d1->diff($d2);
                        $noches = max(1, intval($interval->days));
                    } catch (Exception $e) {
                        $noches = 1;
                    }
                } else {
                    $noches = 1;
                }

                // Obtener precio por noche de la habitación
                $stmtH = $db->prepare('SELECT precio_noche FROM habitaciones WHERE id = :id AND deleted_at IS NULL');
                $stmtH->bindParam(':id', $reserva->habitacion_id, PDO::PARAM_INT);
                $stmtH->execute();
                $rowH = $stmtH->fetch(PDO::FETCH_ASSOC);
                $precioNoche = $rowH['precio_noche'] ?? 0;

                $reserva->precio_noche = $precioNoche;
                $reserva->noches = $noches;
                $reserva->total = isset($data->total) && !empty($data->total) ? $data->total : ($precioNoche * $noches);
                $reserva->metodo_pago = $data->metodo_pago;
                $reserva->notas = $data->observaciones ?? null;

                // Usar el número de huéspedes del frontend
                $numero_huespedes = intval($data->numero_huespedes ?? 1);
                error_log("Huéspedes del frontend (PUT): " . $numero_huespedes);
                $reserva->num_huespedes = $numero_huespedes;
                
                if($reserva->update()) {
                    // Procesar acompañantes si existen (reemplazar lista completa)
                    if (isset($data->acompanantes) && is_array($data->acompanantes)) {
                        try {
                            $stmt = $db->prepare("SHOW TABLES LIKE 'acompanantes'");
                            $stmt->execute();
                            $tableExists = $stmt->rowCount() > 0;

                            if ($tableExists) {
                                include_once '../models/Acompanante.php';
                                $acompanante = new Acompanante($db);

                                // eliminar existentes
                                $acompanante->deleteByReserva($reserva->id);

                                // insertar actuales
                                foreach ($data->acompanantes as $acompanante_data) {
                                    $acompanante->reserva_id = $reserva->id;
                                    $acompanante->nombre = $acompanante_data->nombre ?? '';
                                    $acompanante->apellido = $acompanante_data->apellido ?? '';
                                    $acompanante->tipo_documento = $acompanante_data->tipo_documento ?? ($acompanante_data->tipo_doc ?? '');
                                    $acompanante->numero_documento = $acompanante_data->numero_documento ?? ($acompanante_data->num_doc ?? '');
                                    $acompanante->fecha_nacimiento = $acompanante_data->fecha_nacimiento ?? ($acompanante_data->fecha_nac ?? null);
                                    $acompanante->parentesco = $acompanante_data->parentesco ?? 'Otro';

                                    // solo crear si tiene mínimos
                                    if (!empty($acompanante->nombre) && !empty($acompanante->apellido) && !empty($acompanante->tipo_documento) && !empty($acompanante->numero_documento)) {
                                        $acompanante->create();
                                    }
                                }
                            } else {
                                // fallback: guardar JSON en observaciones
                                $observaciones = $data->observaciones ?? '';
                                // eliminar bloque previo ACOMPANANTES si existe
                                $observaciones = preg_replace("/\\n\\n?ACOMPANANTES:\\n[\\s\\S]*$/", "", $observaciones);
                                $observaciones = trim($observaciones);
                                
                                // Solo guardar si hay acompañantes
                                if (!empty($data->acompanantes) && count($data->acompanantes) > 0) {
                                    $acompanantes_json = json_encode($data->acompanantes);
                                    if (!empty($observaciones)) {
                                        $observaciones .= "\n\n";
                                    }
                                    $observaciones .= "ACOMPANANTES:\n" . $acompanantes_json;
                                }

                                $update_stmt = $db->prepare("UPDATE reservas SET notas = ? WHERE id = ?");
                                $update_stmt->execute([$observaciones, $reserva->id]);
                            }
                        } catch (Exception $e) {
                            error_log("Error al procesar acompañantes en PUT: " . $e->getMessage());
                        }
                    }

                    http_response_code(200);
                    $resp = array("message" => "Reserva actualizada exitosamente.");
                    if (isset($data->acompanantes) && is_array($data->acompanantes)) {
                        $resp["acompanantes_registrados"] = count($data->acompanantes);
                    }
                    echo json_encode($resp);
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "No se pudo actualizar la reserva."));
                }
            }
        } else {
            http_response_code(400);
            $missing_fields = [];
            if (empty($data->cliente_id)) $missing_fields[] = "cliente_id";
            if (empty($data->habitacion_id)) $missing_fields[] = "habitacion_id";
            if (empty($data->fecha_entrada)) $missing_fields[] = "fecha_entrada";
            if (empty($data->fecha_salida)) $missing_fields[] = "fecha_salida";
            
            echo json_encode(array(
                "message" => "Datos incompletos. Campos requeridos: " . implode(", ", $missing_fields),
                "missing_fields" => $missing_fields,
                "received_data" => $data
            ));
        }
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $reserva->id = $data->id;
            
            // Obtener ID de habitación antes de eliminar
            $stmt = $db->prepare('SELECT habitacion_id FROM reservas WHERE id = :id');
            $stmt->bindParam(':id', $data->id);
            $stmt->execute();
            $habitacion_id = $stmt->fetchColumn();
            
            if($reserva->delete()) {
                // Actualizar estado de la habitación a 'disponible'
                if ($habitacion_id) {
                    $habitacion = new Habitacion($db);
                    $habitacion->id = $habitacion_id;
                    $habitacion->estado = 'disponible';
                    $habitacion->cambiarEstado();
                }
                
                http_response_code(200);
                echo json_encode(array("message" => "Reserva eliminada exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo eliminar la reserva."));
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
