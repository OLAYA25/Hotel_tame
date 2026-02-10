<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../models/PedidoProducto.php';
include_once '../models/Producto.php';

$database = new Database();
$db = $database->getConnection();
$pedido = new PedidoProducto($db);
$producto = new Producto($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $pedido->id = $_GET['id'];
            if($pedido->getById()) {
                // Obtener detalles del pedido
                $detalles = $pedido->getDetalles($pedido->id);
                $detalles_arr = array();
                while($row = $detalles->fetch()) {
                    $detalles_arr[] = array(
                        "producto_id" => $row['producto_id'],
                        "producto_nombre" => $row['producto_nombre'],
                        "categoria" => $row['categoria'],
                        "cantidad" => $row['cantidad'],
                        "precio_unitario" => $row['precio_unitario'],
                        "subtotal" => $row['subtotal']
                    );
                }
                
                $pedido_arr = array(
                    "id" => $pedido->id,
                    "habitacion_id" => $pedido->habitacion_id,
                    "habitacion_numero" => $pedido->habitacion_numero,
                    "cliente_id" => $pedido->cliente_id,
                    "cliente_nombre" => $pedido->cliente_nombre,
                    "usuario_id" => $pedido->usuario_id,
                    "usuario_nombre" => $pedido->usuario_nombre,
                    "estado" => $pedido->estado,
                    "subtotal" => $pedido->subtotal,
                    "total" => $pedido->total,
                    "notas" => $pedido->notas,
                    "fecha_pedido" => $pedido->fecha_pedido,
                    "fecha_entrega" => $pedido->fecha_entrega,
                    "detalles" => $detalles_arr
                );
                http_response_code(200);
                echo json_encode($pedido_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Pedido no encontrado."));
            }
        } else {
            $stmt = $pedido->getAll();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $pedidos_arr = array();
                $pedidos_arr["records"] = array();
                
                while ($row = $stmt->fetch()) {
                    $pedido_item = array(
                        "id" => $row['id'],
                        "habitacion_id" => $row['habitacion_id'],
                        "habitacion_numero" => $row['habitacion_numero'],
                        "cliente_id" => $row['cliente_id'],
                        "cliente_nombre" => $row['cliente_nombre'],
                        "usuario_id" => $row['usuario_id'],
                        "usuario_nombre" => $row['usuario_nombre'],
                        "estado" => $row['estado'],
                        "subtotal" => $row['subtotal'],
                        "total" => $row['total'],
                        "notas" => $row['notas'],
                        "fecha_pedido" => $row['fecha_pedido'],
                        "fecha_entrega" => $row['fecha_entrega']
                    );
                    array_push($pedidos_arr["records"], $pedido_item);
                }
                
                http_response_code(200);
                echo json_encode($pedidos_arr);
            } else {
                http_response_code(200);
                echo json_encode(array("records" => array()));
            }
        }
        break;
        
    case 'POST':
        error_log("=== INICIO POST PEDIDO ===");
        $data = json_decode(file_get_contents("php://input"));
        error_log("Data recibida: " . json_encode($data));
        
        if(!empty($data->habitacion_id) && !empty($data->detalles) && count($data->detalles) > 0) {
            error_log("Validación básica pasada");
            $pedido->habitacion_id = $data->habitacion_id;
            $pedido->cliente_id = $data->cliente_id ?? null;
            $pedido->usuario_id = $data->usuario_id ?? 1; // Usuario actual (pasado desde frontend)
            $pedido->estado = 'pendiente';
            $pedido->notas = $data->notas ?? "";
            
            error_log("Pedido configurado: habitacion_id={$pedido->habitacion_id}, cliente_id={$pedido->cliente_id}");
            
            // Calcular totales
            $subtotal = 0;
            $detalles_validos = true;
            
            foreach($data->detalles as $index => $detalle) {
                error_log("Procesando detalle $index: " . json_encode($detalle));
                if(empty($detalle->producto_id) || empty($detalle->cantidad) || empty($detalle->precio_unitario)) {
                    error_log("Detalle inválido en $index");
                    $detalles_validos = false;
                    break;
                }
                $subtotal += $detalle->cantidad * $detalle->precio_unitario;
            }
            
            error_log("Subtotal calculado: $subtotal, detalles_validos: " . ($detalles_validos ? 'true' : 'false'));
            
            if(!$detalles_validos) {
                http_response_code(400);
                echo json_encode(array("message" => "Detalles del pedido incompletos o inválidos."));
                break;
            }
            
            $pedido->subtotal = $subtotal;
            $pedido->total = $subtotal; // Sin impuestos por ahora
            
            error_log("Intentando crear pedido...");
            if($pedido->create()) {
                error_log("Pedido creado con ID: {$pedido->id}");
                // Agregar detalles del pedido
                $todo_bien = true;
                foreach($data->detalles as $index => $detalle) {
                    error_log("Procesando detalle $index para agregar...");
                    $producto->id = $detalle->producto_id;
                    if($producto->checkStock($detalle->cantidad)) {
                        error_log("Stock suficiente, descontando...");
                        // Descontar stock
                        $producto->updateStock(-$detalle->cantidad);
                        // Agregar detalle
                        if(!$pedido->agregarDetalle($pedido->id, $detalle->producto_id, $detalle->cantidad, $detalle->precio_unitario)) {
                            error_log("Error al agregar detalle $index");
                            $todo_bien = false;
                            break;
                        }
                    } else {
                        error_log("Stock insuficiente para producto {$detalle->producto_id}");
                        $todo_bien = false;
                        break;
                    }
                }
                
                if($todo_bien) {
                    error_log("Pedido completado exitosamente");
                    http_response_code(201);
                    echo json_encode(array("message" => "Pedido creado exitosamente.", "pedido_id" => $pedido->id));
                } else {
                    error_log("Error en detalles, haciendo rollback");
                    // Rollback: eliminar pedido y devolver stock
                    $pedido->id = $pedido->id;
                    $pedido->delete();
                    http_response_code(400);
                    echo json_encode(array("message" => "No hay stock suficiente para algunos productos."));
                }
            } else {
                error_log("Error al crear pedido principal");
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo crear el pedido."));
            }
        } else {
            error_log("Datos incompletos");
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos. Se requiere: habitacion_id y detalles"));
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $pedido->id = $data->id;
            $pedido->estado = $data->estado ?? "";
            $pedido->fecha_entrega = ($data->estado == 'entregado') ? date('Y-m-d H:i:s') : null;
            
            if($pedido->updateEstado()) {
                http_response_code(200);
                echo json_encode(array("message" => "Estado del pedido actualizado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo actualizar el estado del pedido."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos. Se requiere ID."));
        }
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $pedido->id = $data->id;
            
            // Devolver stock de los productos
            $detalles = $pedido->getDetalles($pedido->id);
            while($row = $detalles->fetch()) {
                $producto->id = $row['producto_id'];
                $producto->updateStock($row['cantidad']);
            }
            
            if($pedido->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "Pedido eliminado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo eliminar el pedido."));
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
