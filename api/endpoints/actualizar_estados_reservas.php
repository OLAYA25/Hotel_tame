<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include_once '../../backend/config/database.php';
include_once '../models/Reserva.php';
include_once '../models/Habitacion.php';

$database = new Database();
$db = $database->getConnection();

try {
    $hoy = date('Y-m-d');
    
    // 1. Cancelar reservas pendientes cuya fecha de entrada ya pasó
    $query_cancelar_pendientes = "UPDATE reservas 
                                 SET estado = 'cancelada', 
                                     updated_at = NOW()
                                 WHERE estado = 'pendiente' 
                                 AND fecha_entrada < :hoy 
                                 AND deleted_at IS NULL";
    
    $stmt_cancelar = $db->prepare($query_cancelar_pendientes);
    $stmt_cancelar->bindParam(':hoy', $hoy);
    $stmt_cancelar->execute();
    $canceladas = $stmt_cancelar->rowCount();
    
    // 2. Completar reservas confirmadas cuya fecha de salida ya pasó
    $query_completar_confirmadas = "UPDATE reservas 
                                    SET estado = 'completada', 
                                        updated_at = NOW()
                                    WHERE estado = 'confirmada' 
                                    AND fecha_salida < :hoy 
                                    AND deleted_at IS NULL";
    
    $stmt_completar = $db->prepare($query_completar_confirmadas);
    $stmt_completar->bindParam(':hoy', $hoy);
    $stmt_completar->execute();
    $completadas = $stmt_completar->rowCount();
    
    // 3. Actualizar estado de habitaciones - versión simplificada
    $query_reset_habitaciones = "UPDATE habitaciones 
                                SET estado = 'disponible' 
                                WHERE estado IN ('ocupada', 'disponible') 
                                AND deleted_at IS NULL";
    
    $stmt_reset = $db->prepare($query_reset_habitaciones);
    $stmt_reset->execute();
    $actualizadas = $stmt_reset->rowCount();
    
    http_response_code(200);
    echo json_encode([
        "message" => "Estados actualizados exitosamente",
        "reservas_canceladas" => $canceladas,
        "reservas_completadas" => $completadas,
        "habitaciones_actualizadas" => $actualizadas,
        "fecha_procesamiento" => $hoy
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "message" => "Error al actualizar estados", 
        "error" => $e->getMessage()
    ]);
}
?>
