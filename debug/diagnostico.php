<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico - Hotel Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔍 Diagnóstico del Sistema Hotelero</h1>
    
    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    echo "<h2>✅ Verificación de Componentes</h2>";
    
    // 1. Verificar PHP
    echo "<p><strong>PHP Version:</strong> <span class='success'>" . phpversion() . "</span></p>";
    
    // 2. Verificar sesión
    echo "<h3>🔐 Sesión</h3>";
    session_start();
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo "<p class='success'>✅ Sesión activa</p>";
        if (isset($_SESSION['usuario'])) {
            echo "<p class='success'>✅ Usuario en sesión: " . $_SESSION['usuario']['nombre'] . " " . $_SESSION['usuario']['apellido'] . "</p>";
            echo "<p class='success'>✅ Rol: " . $_SESSION['usuario']['rol'] . "</p>";
        } else {
            echo "<p class='error'>❌ No hay usuario en sesión</p>";
            echo "<p><a href='login.php'>Ir al login</a></p>";
        }
    } else {
        echo "<p class='error'>❌ Sesión no activa</p>";
    }
    
    // 3. Verificar conexión a base de datos
    echo "<h3>🗄️ Base de Datos</h3>";
    try {
        require_once 'config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        if ($db) {
            echo "<p class='success'>✅ Conexión a base de datos exitosa</p>";
            
            // Verificar tablas
            $stmt = $db->query("SHOW TABLES");
            $tables = $stmt->fetchAll();
            echo "<p class='info'>📊 Tablas encontradas: " . count($tables) . "</p>";
            
            // Verificar tabla usuarios
            $stmt = $db->query("SELECT COUNT(*) as count FROM usuarios");
            $result = $stmt->fetch();
            echo "<p class='success'>👥 Usuarios registrados: " . $result['count'] . "</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error de base de datos: " . $e->getMessage() . "</p>";
    }
    
    // 4. Verificar archivos importantes
    echo "<h3>📁 Archivos del Sistema</h3>";
    $archivos = [
        'config/database.php' => 'Configuración de BD',
        'includes/header_simple.php' => 'Header simple',
        'includes/sidebar_simple.php' => 'Sidebar simple',
        'includes/footer_simple.php' => 'Footer simple',
        'contabilidad_simple.php' => 'Módulo contable',
        'index_simple.php' => 'Dashboard'
    ];
    
    foreach ($archivos as $archivo => $descripcion) {
        if (file_exists($archivo)) {
            echo "<p class='success'>✅ $descripcion: $archivo</p>";
        } else {
            echo "<p class='error'>❌ $descripcion: $archivo (no encontrado)</p>";
        }
    }
    
    // 5. Verificar errores recientes
    echo "<h3>🐛 Errores Recientes</h3>";
    $log_file = '/opt/lampp/logs/php_error_log';
    if (file_exists($log_file)) {
        $errors = file_get_contents($log_file);
        if (!empty($errors)) {
            echo "<p class='error'>Últimos errores:</p>";
            echo "<pre>" . htmlspecialchars(substr($errors, -1000)) . "</pre>";
        } else {
            echo "<p class='success'>✅ No hay errores recientes</p>";
        }
    } else {
        echo "<p class='info'>ℹ️ No se encontró archivo de log</p>";
    }
    
    // 6. Verificar permisos
    echo "<h3>🔒 Permisos de Directorio</h3>";
    if (is_writable('.')) {
        echo "<p class='success'>✅ Directorio actual escribible</p>";
    } else {
        echo "<p class='error'>❌ Directorio actual no escribible</p>";
    }
    ?>
    
    <h2>🚀 Enlaces de Prueba</h2>
    <ul>
        <li><a href="index_simple.php">Dashboard Simple</a></li>
        <li><a href="contabilidad_simple.php">Contabilidad Simple</a></li>
        <li><a href="login.php">Login</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
    
    <h2>🛠️ Acciones Recomendadas</h2>
    <?php
    if (!isset($_SESSION['usuario'])) {
        echo "<p class='error'>❌ No hay sesión activa. <a href='login.php'>Iniciar sesión</a></p>";
    } else {
        echo "<p class='success'>✅ Todo parece funcionar correctamente. Prueba los enlaces de arriba.</p>";
    }
    ?>
    
    <script>
        // Verificar JavaScript
        document.write('<p class="success">✅ JavaScript está activo</p>');
    </script>
</body>
</html>
