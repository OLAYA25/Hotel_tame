<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Iniciando página...<br>";

try {
    require_once 'config/database.php';
    echo "✅ Configuración cargada<br>";
    
    $database = new Database();
    $db = $database->getConnection();
    echo "✅ Conexión a BD exitosa<br>";
    
    // Variables simples
    $fecha_entrada = $_GET['fecha_entrada'] ?? '';
    $fecha_salida = $_GET['fecha_salida'] ?? '';
    
    echo "Fecha entrada: $fecha_entrada<br>";
    echo "Fecha salida: $fecha_salida<br>";
    
    if (!empty($fecha_entrada) && !empty($fecha_salida)) {
        echo "Buscando habitaciones...<br>";
        
        $stmt = $db->prepare("SELECT * FROM habitaciones WHERE deleted_at IS NULL LIMIT 5");
        $stmt->execute();
        $habitaciones = $stmt->fetchAll();
        
        echo "✅ Encontradas " . count($habitaciones) . " habitaciones<br>";
        
        foreach ($habitaciones as $h) {
            echo "- " . $h['numero'] . " - " . $h['tipo'] . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Línea: " . $e->getLine() . "<br>";
    echo "Archivo: " . $e->getFile() . "<br>";
}

echo "<br>Finalizando página...<br>";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test</title>
</head>
<body>
    <h1>Página de prueba</h1>
    <p>Si ves esto, el PHP funciona.</p>
</body>
</html>
