<?php
// test-home.php - Prueba simple del home
echo "=== PROBANDO HOME DIRECTAMENTE ===\n";

// Simular variables de servidor que necesitaría el router
$_SERVER['REQUEST_URI'] = '/Hotel_tame/';
$_SERVER['SCRIPT_NAME'] = '/Hotel_tame/index.php';

// Incluir el home directamente para probar
require_once 'frontend/views/public/home.php';

echo "=== PRUEBA COMPLETADA ===\n";
?>
