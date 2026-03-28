<?php
// Script de diagnóstico para pedidos
require_once __DIR__ . '/backend/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "=== CONEXIÓN OK ===\n";
    
    // Test 1: Verificar tabla
    $stmt = $db->query("SHOW TABLES LIKE 'pedidos_productos'");
    echo "Tabla existe: " . ($stmt->rowCount() > 0 ? "SÍ" : "NO") . "\n";
    
    // Test 2: Contar directo
    $stmt = $db->query("SELECT COUNT(*) as total FROM pedidos_productos WHERE deleted_at IS NULL");
    $row = $stmt->fetch();
    echo "Total pedidos (directo): " . $row['total'] . "\n";
    
    // Test 3: Ver estructura
    $stmt = $db->query("DESCRIBE pedidos_productos");
    echo "\n=== ESTRUCTURA ===\n";
    while($col = $stmt->fetch()) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }
    
    // Test 4: Últimos pedidos
    echo "\n=== ÚLTIMOS PEDIDOS ===\n";
    $stmt = $db->query("SELECT id, tipo_pedido, estado, total, deleted_at FROM pedidos_productos ORDER BY id DESC LIMIT 3");
    while($row = $stmt->fetch()) {
        echo "ID: {$row['id']}, Tipo: {$row['tipo_pedido']}, Estado: {$row['estado']}, Total: {$row['total']}, Deleted: " . ($row['deleted_at'] ? $row['deleted_at'] : 'NULL') . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
