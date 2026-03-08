<?php
// Habilitar todos los errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug de reservas_online.php</h1>";

// 1. Verificar si el archivo de configuración existe
echo "<h2>1. Verificando archivo de configuración</h2>";
if (file_exists('config/database.php')) {
    echo "✅ Archivo config/database.php encontrado<br>";
} else {
    echo "❌ Archivo config/database.php NO encontrado<br>";
    exit;
}

// 2. Intentar incluir el archivo
echo "<h2>2. Incluyendo archivo de configuración</h2>";
try {
    require_once 'config/database.php';
    echo "✅ Archivo incluido correctamente<br>";
} catch (Exception $e) {
    echo "❌ Error incluyendo archivo: " . $e->getMessage() . "<br>";
    exit;
}

// 3. Verificar constantes de base de datos
echo "<h2>3. Verificando constantes de base de datos</h2>";
echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NO DEFINIDA') . "<br>";
echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NO DEFINIDA') . "<br>";
echo "DB_USER: " . (defined('DB_USER') ? DB_USER : 'NO DEFINIDA') . "<br>";

// 4. Intentar conexión a la base de datos
echo "<h2>4. Intentando conexión a la base de datos</h2>";
try {
    $database = new Database();
    $db = $database->getConnection();
    echo "✅ Conexión a la base de datos exitosa<br>";
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "<br>";
    exit;
}

// 5. Verificar si existen las tablas necesarias
echo "<h2>5. Verificando tablas necesarias</h2>";
$tablas = ['habitaciones', 'clientes', 'reservas'];
foreach ($tablas as $tabla) {
    try {
        $stmt = $db->query("SHOW TABLES LIKE '$tabla'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabla '$tabla' existe<br>";
        } else {
            echo "❌ Tabla '$tabla' NO existe<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error verificando tabla '$tabla': " . $e->getMessage() . "<br>";
    }
}

// 6. Intentar una consulta simple
echo "<h2>6. Intentando consulta simple</h2>";
try {
    $stmt = $db->query("SELECT COUNT(*) as total FROM habitaciones WHERE deleted_at IS NULL");
    $result = $stmt->fetch();
    echo "✅ Consulta exitosa. Total habitaciones: " . $result['total'] . "<br>";
} catch (Exception $e) {
    echo "❌ Error en consulta: " . $e->getMessage() . "<br>";
}

echo "<h2>7. Verificando variables GET</h2>";
echo "Fecha entrada: " . ($_GET['fecha_entrada'] ?? 'No definida') . "<br>";
echo "Fecha salida: " . ($_GET['fecha_salida'] ?? 'No definida') . "<br>";
echo "Huéspedes: " . ($_GET['huespedes'] ?? 'No definido') . "<br>";

echo "<h2>✅ Depuración completada</h2>";
echo "<p><a href='reservas_online.php'>Intentar cargar la página original</a></p>";
?>
