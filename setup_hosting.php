<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'config/env.php';

echo json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'action' => 'setup_hosting',
    'message' => 'Script para configurar datos iniciales en hosting',
    'instructions' => [
        'Este script creará las tablas necesarias si no existen',
        'Insertará datos iniciales de habitaciones',
        'Configurará el sistema para producción'
    ]
]);

// Función para verificar y crear tablas
function setupDatabase() {
    try {
        include_once 'backend/config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            return ['status' => 'error', 'message' => 'No se pudo conectar a la base de datos'];
        }
        
        // Verificar si existe la tabla habitaciones
        $check_table = "SHOW TABLES LIKE 'habitaciones'";
        $stmt = $db->query($check_table);
        
        if ($stmt->rowCount() == 0) {
            // Crear tabla habitaciones
            $create_table = "
            CREATE TABLE habitaciones (
                id int(11) NOT NULL AUTO_INCREMENT,
                numero varchar(10) NOT NULL,
                tipo enum('simple','doble','suite','presidencial') NOT NULL,
                precio_noche decimal(10,2) NOT NULL,
                capacidad int(11) NOT NULL DEFAULT 1,
                estado enum('disponible','ocupada','mantenimiento','limpieza') DEFAULT 'disponible',
                piso int(11) NOT NULL,
                descripcion text DEFAULT NULL,
                amenidades longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`amenidades`)),
                imagen_url varchar(255) DEFAULT NULL,
                created_at timestamp NOT NULL DEFAULT current_timestamp(),
                updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                deleted_at timestamp NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY unique_numero_activo (numero, deleted_at),
                KEY idx_numero (numero),
                KEY idx_tipo (tipo),
                KEY idx_estado (estado)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ";
            
            $db->exec($create_table);
            return ['status' => 'success', 'message' => 'Tabla habitaciones creada'];
        } else {
            return ['status' => 'info', 'message' => 'Tabla habitaciones ya existe'];
        }
        
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

// Función para insertar habitaciones de ejemplo
function insertSampleRooms() {
    try {
        include_once 'backend/config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            return ['status' => 'error', 'message' => 'No se pudo conectar a la base de datos'];
        }
        
        // Habitaciones de ejemplo
        $rooms = [
            ['numero' => '101', 'tipo' => 'simple', 'precio_noche' => 25000, 'capacidad' => 1, 'piso' => 1, 'descripcion' => 'Habitación simple con vista a la calle'],
            ['numero' => '102', 'tipo' => 'doble', 'precio_noche' => 35000, 'capacidad' => 2, 'piso' => 1, 'descripcion' => 'Habitación doble con cama matrimonial'],
            ['numero' => '103', 'tipo' => 'doble', 'precio_noche' => 35000, 'capacidad' => 2, 'piso' => 1, 'descripcion' => 'Habitación doble con dos camas individuales'],
            ['numero' => '201', 'tipo' => 'suite', 'precio_noche' => 50000, 'capacidad' => 2, 'piso' => 2, 'descripcion' => 'Suite junior con sala de estar'],
            ['numero' => '202', 'tipo' => 'suite', 'precio_noche' => 60000, 'capacidad' => 3, 'piso' => 2, 'descripcion' => 'Suite presidencial con jacuzzi'],
        ];
        
        $inserted = 0;
        $duplicates = 0;
        
        foreach ($rooms as $room) {
            // Verificar si ya existe una habitación activa con ese número
            $check = "SELECT id FROM habitaciones WHERE numero = ? AND deleted_at IS NULL";
            $stmt = $db->prepare($check);
            $stmt->execute([$room['numero']]);
            
            if ($stmt->rowCount() == 0) {
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
            } else {
                $duplicates++;
            }
        }
        
        return [
            'status' => 'success', 
            'message' => "Se insertaron $inserted habitaciones nuevas. $duplicates ya existían.",
            'inserted' => $inserted,
            'duplicates' => $duplicates
        ];
        
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

// Si se llama con parámetro action=setup
if (isset($_GET['action']) && $_GET['action'] === 'setup') {
    $setup_result = setupDatabase();
    $insert_result = insertSampleRooms();
    
    echo json_encode([
        'timestamp' => date('Y-m-d H:i:s'),
        'setup_result' => $setup_result,
        'insert_result' => $insert_result,
        'next_steps' => [
            '1. Verificar que las habitaciones se crearon correctamente',
            '2. Probar la página de habitaciones',
            '3. Configurar imágenes si es necesario'
        ]
    ]);
}
?>
