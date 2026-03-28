<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../backend/config/database.php';
include_once '../models/ReservaHuesped.php';
include_once '../models/Persona.php';

try {
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
                
                // Obtener huéspedes con detalles de persona
                $query = "SELECT rh.*, p.nombre, p.apellido, p.email, p.telefono, p.documento as numero_documento 
                         FROM reserva_huespedes rh 
                         LEFT JOIN personas p ON rh.persona_id = p.id 
                         WHERE rh.reserva_id = ? AND rh.deleted_at IS NULL";
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $reserva_id);
                $stmt->execute();
                
                $huespedes = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $huespedes[] = [
                        'id' => $row['persona_id'],
                        'cliente_id' => $row['persona_id'],
                        'nombre' => $row['nombre'],
                        'apellido' => $row['apellido'],
                        'email' => $row['email'],
                        'telefono' => $row['telefono'],
                        'documento' => $row['numero_documento'],
                        'es_titular' => false
                    ];
                }
                
                // Si no hay huéspedes, obtener cliente principal de la reserva
                if (count($huespedes) === 0) {
                    $queryCliente = "SELECT r.cliente_id, p.nombre, p.apellido 
                                    FROM reservas r 
                                    LEFT JOIN personas p ON r.cliente_id = p.id 
                                    WHERE r.id = ?";
                    $stmtCliente = $db->prepare($queryCliente);
                    $stmtCliente->bindParam(1, $reserva_id);
                    $stmtCliente->execute();
                    
                    if ($rowCliente = $stmtCliente->fetch(PDO::FETCH_ASSOC)) {
                        $huespedes[] = [
                            'id' => $rowCliente['cliente_id'],
                            'cliente_id' => $rowCliente['cliente_id'],
                            'nombre' => $rowCliente['nombre'],
                            'apellido' => $rowCliente['apellido'],
                            'es_titular' => true
                        ];
                    }
                }
                
                http_response_code(200);
                echo json_encode([
                    "records" => $huespedes,
                    "total" => count($huespedes)
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
            } else {
                http_response_code(400);
                echo json_encode([
                    "error" => true,
                    "message" => "Se requiere reserva_id o persona_id."
                ]);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents("php://input"));
            
            if(!empty($data->reserva_id) && !empty($data->huespedes)) {
                // Nuevo formato: eliminar anteriores e insertar nuevos
                $reserva_id = $data->reserva_id;
                $huespedes = $data->huespedes;
                
                $db->beginTransaction();
                
                try {
                    // 1) Eliminar acompañantes anteriores
                    $deleteQuery = "DELETE FROM reserva_huespedes WHERE reserva_id = ?";
                    $deleteStmt = $db->prepare($deleteQuery);
                    $deleteStmt->bindParam(1, $reserva_id);
                    $deleteStmt->execute();
                    
                    $guardados = 0;
                    
                    // 2) Insertar los nuevos acompañantes
                    foreach ($huespedes as $huesped) {
                        // Buscar si la persona existe por ID
                        $persona_existente = new Persona($db);
                        $persona_existente->id = $huesped->id;
                        
                        if($persona_existente->getById()) {
                            // La persona existe, agregar a reserva_huespedes
                            $insertQuery = "INSERT INTO reserva_huespedes (reserva_id, persona_id, rol_en_reserva, es_menor, created_at) 
                                           VALUES (?, ?, 'acompanante', 0, NOW())";
                            $insertStmt = $db->prepare($insertQuery);
                            $insertStmt->bindParam(1, $reserva_id);
                            $insertStmt->bindParam(2, $huesped->id);
                            
                            if($insertStmt->execute()) {
                                $guardados++;
                            }
                        }
                    }
                    
                    $db->commit();
                    
                    http_response_code(200);
                    echo json_encode([
                        "success" => true,
                        "acompanantes_guardados" => $guardados,
                        "message" => "Se guardaron $guardados acompañantes correctamente"
                    ]);
                    
                } catch (Exception $e) {
                    $db->rollBack();
                    http_response_code(500);
                    echo json_encode([
                        "error" => true,
                        "message" => $e->getMessage()
                    ]);
                }
            } elseif(!empty($data->reserva_id) && !empty($data->persona_id) && !empty($data->rol_en_reserva)) {
                // Formato antiguo: agregar un solo huésped
                if($reservaHuesped->existsInReserva($data->reserva_id, $data->persona_id)) {
                    http_response_code(400);
                    echo json_encode([
                        "error" => true,
                        "message" => "La persona ya está registrada en esta reserva."
                    ]);
                    break;
                }
                
                if($data->rol_en_reserva === 'principal') {
                    if(!$reservaHuesped->validarUnicoPrincipal($data->reserva_id)) {
                        http_response_code(400);
                        echo json_encode([
                            "error" => true,
                            "message" => "Ya existe un huésped principal en esta reserva."
                        ]);
                        break;
                    }
                }
                
                $reservaHuesped->reserva_id = $data->reserva_id;
                $reservaHuesped->persona_id = $data->persona_id;
                $reservaHuesped->rol_en_reserva = $data->rol_en_reserva;
                $reservaHuesped->parentesco = $data->parentesco ?? null;
                
                $persona->id = $data->persona_id;
                $persona->getById();
                $es_menor = false;
                if ($persona->fecha_nacimiento) {
                    $edad = $persona->getEdadActual();
                    $es_menor = ($edad !== null && $edad < 18);
                }
                $reservaHuesped->es_menor = $es_menor;
                
                if($reservaHuesped->create()) {
                    $persona->actualizarTipoPersona();
                    
                    http_response_code(201);
                    echo json_encode([
                        "success" => true,
                        "message" => "Huésped agregado a la reserva exitosamente.",
                        "id" => $reservaHuesped->id,
                        "es_menor" => $es_menor
                    ]);
                } else {
                    http_response_code(503);
                    echo json_encode([
                        "error" => true,
                        "message" => "No se pudo agregar el huésped a la reserva."
                    ]);
                }
            } else {
                http_response_code(400);
                echo json_encode([
                    "error" => true,
                    "message" => "Datos incompletos. Se requiere reserva_id y huespedes, o reserva_id, persona_id y rol_en_reserva."
                ]);
            }
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents("php://input"));
            
            if(!empty($data->id)) {
                $reservaHuesped->id = $data->id;
                
                $query = "SELECT * FROM reserva_huespedes WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $reservaHuesped->id);
                $stmt->execute();
                $actual = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if($actual) {
                    if(isset($data->rol_en_reserva) && $data->rol_en_reserva === 'principal' && $actual['rol_en_reserva'] !== 'principal') {
                        if(!$reservaHuesped->validarUnicoPrincipal($actual['reserva_id'], $reservaHuesped->id)) {
                            http_response_code(400);
                            echo json_encode([
                                "error" => true,
                                "message" => "Ya existe un huésped principal en esta reserva."
                            ]);
                            break;
                        }
                    }
                    
                    $reservaHuesped->rol_en_reserva = $data->rol_en_reserva ?? $actual['rol_en_reserva'];
                    $reservaHuesped->parentesco = $data->parentesco ?? $actual['parentesco'];
                    $reservaHuesped->es_menor = $data->es_menor ?? $actual['es_menor'];
                    
                    if($reservaHuesped->update()) {
                        http_response_code(200);
                        echo json_encode([
                            "success" => true,
                            "message" => "Relación huésped-reserva actualizada exitosamente."
                        ]);
                    } else {
                        http_response_code(503);
                        echo json_encode([
                            "error" => true,
                            "message" => "No se pudo actualizar la relación."
                        ]);
                    }
                } else {
                    http_response_code(404);
                    echo json_encode([
                        "error" => true,
                        "message" => "Relación huésped-reserva no encontrada."
                    ]);
                }
            } else {
                http_response_code(400);
                echo json_encode([
                    "error" => true,
                    "message" => "ID no proporcionado."
                ]);
            }
            break;
            
        case 'DELETE':
            if(isset($_GET['id'])) {
                $reservaHuesped->id = $_GET['id'];
                
                if($reservaHuesped->delete()) {
                    http_response_code(200);
                    echo json_encode([
                        "success" => true,
                        "message" => "Huésped removido de la reserva exitosamente."
                    ]);
                } else {
                    http_response_code(503);
                    echo json_encode([
                        "error" => true,
                        "message" => "No se pudo remover el huésped de la reserva."
                    ]);
                }
            } elseif(isset($_GET['reserva_id'])) {
                $reserva_id = $_GET['reserva_id'];
                
                if($reservaHuesped->deleteByReserva($reserva_id)) {
                    http_response_code(200);
                    echo json_encode([
                        "success" => true,
                        "message" => "Todos los huéspedes removidos de la reserva exitosamente."
                    ]);
                } else {
                    http_response_code(503);
                    echo json_encode([
                        "error" => true,
                        "message" => "No se pudieron remover los huéspedes de la reserva."
                    ]);
                }
            } else {
                http_response_code(400);
                echo json_encode([
                    "error" => true,
                    "message" => "ID de relación o reserva no proporcionado."
                ]);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                "error" => true,
                "message" => "Método no permitido."
            ]);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => true,
        "message" => $e->getMessage()
    ]);
}
?>
