<?php
echo "Inicio del script<br>";

try {
    require_once 'config/database.php';
    echo "✅ Database.php cargado<br>";
    
    $database = new Database();
    echo "✅ Database object creado<br>";
    
    $db = $database->getConnection();
    echo "✅ Conexión establecida<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "Fin del script<br>";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Minimal</title>
</head>
<body>
    <h1>Test Minimal</h1>
    <p>Si ves esto, el HTML funciona.</p>
</body>
</html>
