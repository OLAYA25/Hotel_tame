<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../backend/config/database.php';
include_once '../models/Cliente.php';

function logClientesError($context, $error) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
    $logFile = $logDir . '/clientes_errors.log';
    $entry = date('Y-m-d H:i:s') . " [{$context}] " . (is_array($error) ? json_encode($error) : $error) . PHP_EOL;
    @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
}

$database = new Database();
$db = $database->getConnection();
$cliente = new Cliente($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $cliente->id = $_GET['id'];
            
            if($cliente->getById()) {
                $cliente_arr = array(
                    "id" => $cliente->id,
                    "nombre" => $cliente->nombre,
                    "apellido" => $cliente->apellido ?? '',
                    "tipo_documento" => $cliente->tipo_documento ?? null,
                    "email" => $cliente->email,
                    "telefono" => $cliente->telefono,
                    "documento" => $cliente->documento,
                    "fecha_nacimiento" => $cliente->fecha_nacimiento ?? null,
                    "ciudad" => $cliente->ciudad ?? null,
                    "pais" => $cliente->pais ?? null,
                    "motivo_viaje" => $cliente->motivo_viaje ?? 'turismo',
                    "direccion" => $cliente->direccion,
                    "acompanantes_info" => $cliente->acompanantes_info,
                    "created_at" => $cliente->created_at
                );
                http_response_code(200);
                echo json_encode($cliente_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Cliente no encontrado."));
            }
        } else {
            try {
                // Parámetros de paginación
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12; // 12 clientes por página (3 filas de 4 columnas)
                $offset = ($page - 1) * $limit;
                
                // Parámetros de búsqueda
                $search = isset($_GET['search']) ? trim($_GET['search']) : '';
                
                $stmt = $cliente->getAllWithPagination($limit, $offset, $search);
                $num = $stmt->rowCount();
                
                // Obtener total para paginación
                $total_stmt = $cliente->getTotalCount($search);
                $total_row = $total_stmt->fetch();
                $total = $total_row['total'];
                
                if($num > 0) {
                    $clientes_arr = array();
                    $clientes_arr["records"] = array();
                    $clientes_arr["pagination"] = array(
                        "page" => $page,
                        "limit" => $limit,
                        "total" => (int)$total,
                        "pages" => ceil($total / $limit),
                        "has_next" => ($page * $limit) < $total,
                        "has_prev" => $page > 1
                    );
                    
                    while ($row = $stmt->fetch()) {
                        $cliente_item = array(
                            "id" => $row['id'],
                            "nombre" => $row['nombre'],
                            "apellido" => $row['apellido'] ?? '',
                            "tipo_documento" => $row['tipo_documento'] ?? null,
                            "email" => $row['email'],
                            "telefono" => $row['telefono'],
                            "documento" => $row['documento'],
                            "fecha_nacimiento" => $row['fecha_nacimiento'] ?? null,
                            "ciudad" => $row['ciudad'] ?? null,
                            "pais" => $row['pais'] ?? null,
                            "direccion" => $row['direccion'],
                            "motivo_viaje" => $row['motivo_viaje'] ?? 'turismo',
                            "acompanantes_info" => $row['acompanantes_info'] ?? null,
                            "created_at" => $row['created_at']
                        );
                        array_push($clientes_arr["records"], $cliente_item);
                    }
                    
                    http_response_code(200);
                    echo json_encode($clientes_arr);
                } else {
                    http_response_code(200);
                    echo json_encode(array(
                        "records" => array(),
                        "pagination" => array(
                            "page" => $page,
                            "limit" => $limit,
                            "total" => 0,
                            "pages" => 0,
                            "has_next" => false,
                            "has_prev" => false
                        )
                    ));
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(array(
                    "message" => "Error al cargar clientes", 
                    "error" => $e->getMessage(),
                    "file" => $e->getFile(),
                    "line" => $e->getLine()
                ));
            }
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        // Accept either numero_documento or documento from frontend
        $doc = $data->numero_documento ?? $data->documento ?? null;

        if(!empty($data->nombre) && !empty($data->email) && !empty($doc)) {
            $cliente->nombre = $data->nombre;
            $cliente->apellido = $data->apellido ?? '';
            $cliente->email = $data->email;
            $cliente->telefono = $data->telefono ?? "";

            // Validar tipo_documento contra los valores permitidos
            $allowedTipos = array('DNI', 'Pasaporte', 'Cedula');
            $tipo = isset($data->tipo_documento) ? trim($data->tipo_documento) : '';
            if ($tipo === '') {
                $tipo = 'DNI'; // valor por defecto en la BD
            }
            if (!in_array($tipo, $allowedTipos, true)) {
                http_response_code(400);
                $msg = array("message" => "tipo_documento inválido.", "allowed" => $allowedTipos, "received" => $data->tipo_documento ?? null);
                logClientesError('VALIDATION', $msg);
                echo json_encode($msg);
                exit;
            }

            $cliente->tipo_documento = $tipo;
            $cliente->documento = $doc;
            $cliente->fecha_nacimiento = $data->fecha_nacimiento ?? null;
            $cliente->ciudad = $data->ciudad ?? '';
            $cliente->pais = $data->pais ?? '';
            $cliente->motivo_viaje = $data->motivo_viaje ?? 'turismo';
            $cliente->direccion = $data->direccion ?? "";
            $cliente->acompanantes_info = $data->acompanantes ?? null; // Guardar acompañantes como JSON
            
            // Iniciar transacción para guardar cliente y acompañantes
            $db->beginTransaction();
            
            try {
                if($cliente->create()) {
                    $cliente_id = $db->lastInsertId();
                    
                    // Procesar acompañantes si existen
                    if (!empty($data->acompanantes) && is_array($data->acompanantes)) {
                        include_once '../models/Acompanante.php';
                        $acompanante = new Acompanante($db);
                        
                        foreach ($data->acompanantes as $acompanante_data) {
                            // Validar datos del acompañante
                            if (!empty($acompanante_data->nombre) && !empty($acompanante_data->apellido) && 
                                !empty($acompanante_data->tipo_documento) && !empty($acompanante_data->numero_documento)) {
                                
                                $acompanante->reserva_id = null; // Se asignará cuando se haga la reserva
                                $acompanante->nombre = $acompanante_data->nombre;
                                $acompanante->apellido = $acompanante_data->apellido;
                                $acompanante->tipo_documento = $acompanante_data->tipo_documento;
                                $acompanante->numero_documento = $acompanante_data->numero_documento;
                                $acompanante->fecha_nacimiento = $acompanante_data->fecha_nacimiento ?? null;
                                $acompanante->parentesco = $acompanante_data->parentesco ?? 'Otro';
                                
                                // Guardar acompañante temporalmente asociado al cliente
                                // Nota: Esto podría requerir una tabla intermedia o guardar como JSON en cliente
                                // Por ahora, guardamos en una tabla temporal o como metadata
                                
                                // Opción 1: Guardar en tabla de acompanantes_pendientes
                                $query = "INSERT INTO acompanantes_pendientes (cliente_id, nombre, apellido, tipo_documento, numero_documento, fecha_nacimiento, parentesco) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?)";
                                $stmt = $db->prepare($query);
                                $stmt->execute([
                                    $cliente_id,
                                    $acompanante->nombre,
                                    $acompanante->apellido,
                                    $acompanante->tipo_documento,
                                    $acompanante->numero_documento,
                                    $acompanante->fecha_nacimiento,
                                    $acompanante->parentesco
                                ]);
                            }
                        }
                    }
                    
                    $db->commit();
                    http_response_code(201);
                    echo json_encode(array(
                        "message" => "Cliente creado exitosamente.",
                        "cliente_id" => $cliente_id,
                        "acompanantes_count" => !empty($data->acompanantes) ? count($data->acompanantes) : 0
                    ));
                } else {
                    $db->rollBack();
                    http_response_code(503);
                    $err = $cliente->lastError ?? 'Unknown';
                    logClientesError('CREATE', $err);
                    echo json_encode(array("message" => "No se pudo crear el cliente.", "error" => $err));
                }
            } catch (Exception $e) {
                $db->rollBack();
                http_response_code(500);
                logClientesError('TRANSACTION', $e->getMessage());
                echo json_encode(array("message" => "Error en la transacción.", "error" => $e->getMessage()));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos."));
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $cliente->id = $data->id;
            $doc = $data->numero_documento ?? $data->documento ?? null;
            $cliente->nombre = $data->nombre;
            $cliente->apellido = $data->apellido ?? '';
            $cliente->email = $data->email;
            $cliente->telefono = $data->telefono ?? "";

            // Validar tipo_documento contra los valores permitidos
            $allowedTipos = array('DNI', 'Pasaporte', 'Cedula');
            $tipo = isset($data->tipo_documento) ? trim($data->tipo_documento) : '';
            if ($tipo === '') {
                $tipo = 'DNI';
            }
            if (!in_array($tipo, $allowedTipos, true)) {
                http_response_code(400);
                $msg = array("message" => "tipo_documento inválido.", "allowed" => $allowedTipos, "received" => $data->tipo_documento ?? null);
                logClientesError('VALIDATION', $msg);
                echo json_encode($msg);
                exit;
            }

            $cliente->tipo_documento = $tipo;
            $cliente->documento = $doc;
            $cliente->fecha_nacimiento = $data->fecha_nacimiento ?? null;
            $cliente->ciudad = $data->ciudad ?? '';
            $cliente->pais = $data->pais ?? '';
            $cliente->motivo_viaje = $data->motivo_viaje ?? 'turismo';
            $cliente->direccion = $data->direccion ?? "";
            $cliente->acompanantes_info = $data->acompanantes ?? null; // Guardar acompañantes como JSON
            
            if($cliente->update()) {
                http_response_code(200);
                echo json_encode(array("message" => "Cliente actualizado exitosamente."));
            } else {
                http_response_code(503);
                $err = $cliente->lastError ?? 'Unknown';
                logClientesError('UPDATE', $err);
                echo json_encode(array("message" => "No se pudo actualizar el cliente.", "error" => $err));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos."));
        }
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $cliente->id = $data->id;
            
            if($cliente->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "Cliente eliminado exitosamente."));
            } else {
                http_response_code(503);
                $err = $cliente->lastError ?? 'Unknown';
                logClientesError('DELETE', $err);
                echo json_encode(array("message" => "No se pudo eliminar el cliente.", "error" => $err));
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
