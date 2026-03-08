<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../backend/config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if($method === 'GET') {
    try {
        // Total habitaciones
        $query = "SELECT COUNT(*) as total FROM habitaciones WHERE deleted_at IS NULL";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $total_habitaciones = $stmt->fetch()['total'];
        
        // Habitaciones disponibles
        $query = "SELECT COUNT(*) as disponibles FROM habitaciones WHERE estado = 'disponible' AND deleted_at IS NULL";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $disponibles = $stmt->fetch()['disponibles'];
        
        // Reservas activas (confirmadas y pendientes)
        $query = "SELECT COUNT(*) as activas FROM reservas WHERE estado IN ('confirmada', 'pendiente') AND deleted_at IS NULL";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $reservas_activas = $stmt->fetch()['activas'];
        
        // Total clientes
        $query = "SELECT COUNT(*) as total FROM clientes WHERE deleted_at IS NULL";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $total_clientes = $stmt->fetch()['total'];
        
        // Ingresos del mes actual
        $query = "SELECT SUM(total) as ingresos FROM reservas 
                  WHERE estado = 'confirmada' 
                  AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
                  AND YEAR(created_at) = YEAR(CURRENT_DATE())
                  AND deleted_at IS NULL";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $ingresos_mes = $stmt->fetch()['ingresos'] ?? 0;
        
        // Ingresos totales
        $query = "SELECT SUM(total) as total FROM reservas 
                  WHERE estado = 'confirmada' 
                  AND deleted_at IS NULL";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $ingresos_totales = $stmt->fetch()['total'] ?? 0;
        
        $stats = array(
            "totalHabitaciones" => (int)$total_habitaciones,
            "disponibles" => (int)$disponibles,
            "reservasActivas" => (int)$reservas_activas,
            "totalClientes" => (int)$total_clientes,
            "ingresosDelMes" => (float)$ingresos_mes,
            "ingresosTotales" => (float)$ingresos_totales
        );
        
        http_response_code(200);
        echo json_encode($stats);
        
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Error al obtener estadísticas: " . $e->getMessage()));
    }
} else {
    http_response_code(405);
    echo json_encode(array("message" => "Método no permitido."));
}
?>
