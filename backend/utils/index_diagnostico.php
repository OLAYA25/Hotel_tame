<?php
// Versión de diagnóstico del index.php original
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Diagnóstico Index</title></head><body>";
echo "<h1>🔍 DIAGNÓSTICO INDEX.PHP</h1>";

try {
    echo "<p>✅ Paso 1: PHP funciona</p>";
    
    // Paso 2: Verificar sesión
    echo "<p>🔍 Paso 2: Verificando sesión...</p>";
    session_start();
    echo "<p>✅ Sesión iniciada</p>";
    
    if (!isset($_SESSION['usuario'])) {
        echo "<p>❌ No hay sesión de usuario - Redirigiendo...</p>";
        echo "<script>window.location='login.php';</script>";
        exit;
    }
    echo "<p>✅ Usuario en sesión: " . $_SESSION['usuario']['nombre'] . "</p>";
    
    // Paso 3: Probar database.php
    echo "<p>🔍 Paso 3: Cargando database.php...</p>";
    require_once __DIR__ . '/../../config/database.php';
    echo "<p>✅ database.php cargado</p>";
    
    // Paso 4: Probar conexión
    echo "<p>🔍 Paso 4: Probando conexión BD...</p>";
    $database = new Database();
    $db = $database->getConnection();
    if ($db) {
        echo "<p>✅ Conexión BD exitosa</p>";
    } else {
        echo "<p>❌ Error en conexión BD</p>";
    }
    
    // Paso 5: Probar consulta
    echo "<p>🔍 Paso 5: Probando consulta...</p>";
    try {
        $stmt = $db->query("SELECT COUNT(*) as total FROM habitaciones WHERE deleted_at IS NULL");
        $row = $stmt ? $stmt->fetch() : null;
        $total_habitaciones = $row['total'] ?? 0;
        echo "<p>✅ Consulta exitosa: $total_habitaciones habitaciones</p>";
    } catch (Exception $e) {
        echo "<p>❌ Error en consulta: " . $e->getMessage() . "</p>";
    }
    
    // Paso 6: Probar header.php
    echo "<p>🔍 Paso 6: Probando header.php...</p>";
    if (file_exists('includes/header.php')) {
        echo "<p>✅ header.php existe</p>";
        // Intentar incluir header
        include __DIR__ . '/../../includes/header.php';
        echo "<p>✅ header.php incluido</p>";
    } else {
        echo "<p>❌ header.php no existe</p>";
    }
    
    // Paso 7: Probar sidebar.php
    echo "<p>🔍 Paso 7: Probando sidebar.php...</p>";
    if (file_exists('includes/sidebar.php')) {
        echo "<p>✅ sidebar.php existe</p>";
        // Intentar incluir sidebar
        include 'includes/sidebar.php';
        echo "<p>✅ sidebar.php incluido</p>";
    } else {
        echo "<p>❌ sidebar.php no existe</p>";
    }
    
    // Si llegamos aquí, mostrar contenido simple
    echo "<div style='margin-left: 270px; padding: 20px;'>";
    echo "<h1>Dashboard Funcionando</h1>";
    echo "<p>✅ Todos los componentes cargados correctamente</p>";
    echo "<p>Usuario: " . $_SESSION['usuario']['nombre'] . " " . $_SESSION['usuario']['apellido'] . "</p>";
    echo "<p>Rol: " . $_SESSION['usuario']['rol'] . "</p>";
    echo "<p>Habitaciones: $total_habitaciones</p>";
    echo "</div>";
    
    // Paso 8: Probar footer.php
    echo "<p>🔍 Paso 8: Probando footer.php...</p>";
    if (file_exists('includes/footer.php')) {
        echo "<p>✅ footer.php existe</p>";
        include __DIR__ . '/../../includes/footer.php';
        echo "<p>✅ footer.php incluido</p>";
    } else {
        echo "<p>❌ footer.php no existe</p>";
    }
    
    echo "<p>✅ Todos los pasos completados exitosamente</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<p style='color: red;'>❌ Error Fatal: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
}

echo "</body></html>";
?>
