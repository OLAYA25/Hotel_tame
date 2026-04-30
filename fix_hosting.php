<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'config/env.php';

echo json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'action' => 'fix_hosting',
    'message' => 'Script para corregir constraint y limpiar datos del hosting'
]);

// Función para verificar y corregir la estructura de la tabla
function fixTableStructure() {
    try {
        include_once 'backend/config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            return ['status' => 'error', 'message' => 'No se pudo conectar a la base de datos'];
        }
        
        // Eliminar el constraint antiguo si existe
        $drop_constraints = [
            "ALTER TABLE habitaciones DROP INDEX numero",
            "ALTER TABLE habitaciones DROP INDEX unique_numero_activo"
        ];
        
        foreach ($drop_constraints as $sql) {
            try {
                $db->exec($sql);
            } catch (Exception $e) {
                // Ignorar errores si el constraint no existe
                error_log("Constraint drop info: " . $e->getMessage());
            }
        }
        
        // Agregar el constraint correcto
        $add_constraint = "ALTER TABLE habitaciones ADD UNIQUE KEY unique_numero_activo (numero, deleted_at)";
        $db->exec($add_constraint);
        
        return ['status' => 'success', 'message' => 'Estructura de tabla corregida'];
        
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

// Función para limpiar y reinsertar datos
function cleanAndInsertRooms() {
    try {
        include_once 'backend/config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            return ['status' => 'error', 'message' => 'No se pudo conectar a la base de datos'];
        }
        
        // Primero, marcar todas las habitaciones existentes como eliminadas (soft delete)
        $soft_delete_all = "UPDATE habitaciones SET deleted_at = NOW() WHERE deleted_at IS NULL";
        $db->exec($soft_delete_all);
        
        // Habitaciones de ejemplo
        $rooms = [
            ['numero' => '101', 'tipo' => 'simple', 'precio_noche' => 25000, 'capacidad' => 1, 'piso' => 1, 'descripcion' => 'Habitación simple con vista a la calle'],
            ['numero' => '102', 'tipo' => 'doble', 'precio_noche' => 35000, 'capacidad' => 2, 'piso' => 1, 'descripcion' => 'Habitación doble con cama matrimonial'],
            ['numero' => '103', 'tipo' => 'doble', 'precio_noche' => 35000, 'capacidad' => 2, 'piso' => 1, 'descripcion' => 'Habitación doble con dos camas individuales'],
            ['numero' => '104', 'tipo' => 'doble', 'precio_noche' => 40000, 'capacidad' => 2, 'piso' => 1, 'descripcion' => 'Habitación doble premium'],
            ['numero' => '201', 'tipo' => 'suite', 'precio_noche' => 50000, 'capacidad' => 2, 'piso' => 2, 'descripcion' => 'Suite junior con sala de estar'],
            ['numero' => '202', 'tipo' => 'suite', 'precio_noche' => 60000, 'capacidad' => 3, 'piso' => 2, 'descripcion' => 'Suite presidencial con jacuzzi'],
        ];
        
        $inserted = 0;
        
        foreach ($rooms as $room) {
            // Insertar nueva habitación
            $insert = "INSERT INTO habitaciones (numero, tipo, precio_noche, capacidad, piso, descripcion, estado) 
                      VALUES (?, ?, ?, ?, ?, ?, 'disponible')";
            $stmt = $db->prepare($insert);
            $stmt->execute([
                $room['numero'],
                $room['tipo'],
                $room['precio_noche'],
                $room['capacidad'],
                $room['piso'],
                $room['descripcion']
            ]);
            $inserted++;
        }
        
        return [
            'status' => 'success', 
            'message' => "Se insertaron $inserted habitaciones nuevas.",
            'inserted' => $inserted
        ];
        
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

// Si se llama con parámetro action=fix
if (isset($_GET['action']) && $_GET['action'] === 'fix') {
    $structure_result = fixTableStructure();
    $insert_result = cleanAndInsertRooms();
    
    echo json_encode([
        'timestamp' => date('Y-m-d H:i:s'),
        'structure_result' => $structure_result,
        'insert_result' => $insert_result,
        'next_steps' => [
            '1. Verificar que las habitaciones se crearon correctamente',
            '2. Probar la página de habitaciones',
            '3. Verificar que el constraint funciona correctamente'
        ]
    ]);
}
?>
