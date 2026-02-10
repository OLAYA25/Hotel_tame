<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../models/TransaccionContable.php';

$database = new Database();
$db = $database->getConnection();

$transaccion = new TransaccionContable($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'POST':
        try {
            // Obtener reservas confirmadas que no tengan transacción contable
            $query = "SELECT r.*, c.nombre as cliente_nombre, c.apellido as cliente_apellido, h.numero as habitacion_numero, h.tipo as habitacion_tipo
                     FROM reservas r 
                     JOIN clientes c ON r.cliente_id = c.id 
                     JOIN habitaciones h ON r.habitacion_id = h.id 
                     LEFT JOIN transacciones_contables tc ON (tc.referencia_tipo = 'reserva' AND tc.referencia_id = r.id)
                     WHERE r.estado = 'confirmada' 
                     AND r.deleted_at IS NULL 
                     AND tc.id IS NULL";
            
            $stmt = $db->query($query);
            $reservas_pendientes = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            
            $sincronizadas = 0;
            $errores = [];
            
            foreach ($reservas_pendientes as $reserva) {
                try {
                    // Crear transacción contable por cada reserva confirmada
                    $transaccion->referencia_tipo = 'reserva';
                    $transaccion->referencia_id = $reserva['id'];
                    $transaccion->numero_comprobante = 'RES-' . str_pad($reserva['id'], 6, '0', STR_PAD_LEFT);
                    $transaccion->fecha = $reserva['fecha_entrada'];
                    $transaccion->descripcion = "Ingreso por reserva - Hab. {$reserva['habitacion_numero']} ({$reserva['habitacion_tipo']}) - Cliente: {$reserva['cliente_nombre']} {$reserva['cliente_apellido']}";
                    $transaccion->tipo_transaccion = 'ingreso';
                    $transaccion->monto_total = $reserva['precio_total'] ?? $reserva['total'] ?? 0;
                    $transaccion->usuario_id = $_SESSION['usuario']['id'] ?? 1;
                    $transaccion->estado = 'confirmada';
                    
                    // Usar inserción directa para crear transacciones contables
                    try {
                        $query = "INSERT INTO transacciones_contables 
                                 (numero_comprobante, fecha, descripcion, tipo_transaccion, monto_total, usuario_id, referencia_tipo, referencia_id, estado, created_at) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                        
                        $stmt = $db->prepare($query);
                        $stmt->execute([
                            $transaccion->numero_comprobante,
                            $transaccion->fecha,
                            $transaccion->descripcion,
                            $transaccion->tipo_transaccion,
                            $transaccion->monto_total,
                            $transaccion->usuario_id,
                            $transaccion->referencia_tipo,
                            $transaccion->referencia_id,
                            $transaccion->estado
                        ]);
                        
                        if ($stmt->rowCount() > 0) {
                            $sincronizadas++;
                        } else {
                            $errores[] = "Error al insertar transacción para reserva {$reserva['id']}";
                        }
                    } catch (Exception $innerE) {
                        $errores[] = "Error en método de creación para reserva {$reserva['id']}: " . $innerE->getMessage();
                    }
                } catch (Exception $e) {
                    $errores[] = "Error procesando reserva {$reserva['id']}: " . $e->getMessage();
                }
            }
            
            http_response_code(200);
            echo json_encode([
                "message" => "Sincronización completada",
                "sincronizadas" => $sincronizadas,
                "errores" => $errores,
                "total_procesadas" => count($reservas_pendientes)
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "message" => "Error en sincronización",
                "error" => $e->getMessage()
            ]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(["message" => "Método no permitido"]);
        break;
}
?>
