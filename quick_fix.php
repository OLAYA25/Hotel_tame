<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'config/env.php';

$results = [];

// 1. Corregir constraint
try {
    include_once 'backend/config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        // Eliminar constraints antiguos
        try {
            $db->exec("ALTER TABLE habitaciones DROP INDEX numero");
            $results[] = "Constraint 'numero' eliminado";
        } catch (Exception $e) {
            $results[] = "Constraint 'numero' no existe o ya fue eliminado";
        }
        
        try {
            $db->exec("ALTER TABLE habitaciones DROP INDEX unique_numero_activo");
            $results[] = "Constraint 'unique_numero_activo' eliminado";
        } catch (Exception $e) {
            $results[] = "Constraint 'unique_numero_activo' no existe";
        }
        
        // Agregar constraint correcto
        $db->exec("ALTER TABLE habitaciones ADD UNIQUE KEY unique_numero_activo (numero, deleted_at)");
        $results[] = "Constraint correcto agregado";
        
        // Marcar todo como eliminado
        $db->exec("UPDATE habitaciones SET deleted_at = NOW() WHERE deleted_at IS NULL");
        $results[] = "Datos anteriores marcados como eliminados";
        
        // Insertar habitaciones nuevas
        $rooms = [
            ['101', 'simple', 25000, 1, 1, 'Habitación simple con vista a la calle'],
            ['102', 'doble', 35000, 2, 1, 'Habitación doble con cama matrimonial'],
            ['103', 'doble', 35000, 2, 1, 'Habitación doble con dos camas individuales'],
            ['104', 'doble', 40000, 2, 1, 'Habitación doble premium'],
            ['201', 'suite', 50000, 2, 2, 'Suite junior con sala de estar'],
            ['202', 'suite', 60000, 3, 2, 'Suite presidencial con jacuzzi'],
        ];
        
        $inserted = 0;
        foreach ($rooms as $room) {
            $stmt = $db->prepare("INSERT INTO habitaciones (numero, tipo, precio_noche, capacidad, piso, descripcion, estado) VALUES (?, ?, ?, ?, ?, ?, 'disponible')");
            $stmt->execute($room);
            $inserted++;
        }
        
        $results[] = "Se insertaron $inserted habitaciones nuevas";
        
        // Verificar resultado final
        $stmt = $db->query("SELECT COUNT(*) as count FROM habitaciones WHERE deleted_at IS NULL");
        $count = $stmt->fetch()['count'];
        $results[] = "Total habitaciones activas: $count";
        
    } else {
        $results[] = "Error: No se pudo conectar a la base de datos";
    }
} catch (Exception $e) {
    $results[] = "Error: " . $e->getMessage();
}

echo json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'action' => 'quick_fix',
    'results' => $results,
    'next_url' => 'https://hotel-tame.com.cymconstructorasas.com/Hotel_tame/habitaciones'
]);
?>
