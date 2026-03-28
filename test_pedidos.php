<?php
// Test script para diagnosticar el problema de pedidos
require_once __DIR__ . '/backend/config/database.php';
require_once __DIR__ . '/api/models/PedidoProducto.php';

$database = new Database();
$db = $database->getConnection();
$pedido = new PedidoProducto($db);

echo "=== TEST DIRECTO DE BASE DE DATOS ===\n";

// Test 1: Contar directamente
$stmt = $db->query("SELECT COUNT(*) as total FROM pedidos_productos WHERE deleted_at IS NULL");
$row = $stmt->fetch();
echo "Count directo: " . $row['total'] . "\n";

// Test 2: Usar el modelo
echo "\n=== TEST DEL MODELO ===\n";
$stmt = $pedido->getTotalCount('', '', '', '');
$row = $stmt->fetch();
echo "Count via modelo: " . $row['total'] . "\n";

// Test 3: Verificar pedidos recientes
echo "\n=== PEDIDOS RECIENTES ===\n";
$stmt = $db->query("SELECT id, tipo_pedido, estado, total, fecha_pedido FROM pedidos_productos WHERE deleted_at IS NULL ORDER BY id DESC LIMIT 5");
while ($row = $stmt->fetch()) {
    echo "ID: {$row['id']}, Tipo: {$row['tipo_pedido']}, Estado: {$row['estado']}, Total: {$row['total']}\n";
}

echo "\n=== FIN DEL TEST ===\n";
