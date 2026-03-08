<!DOCTYPE html>
<html>
<head>
    <title>Test Básico</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .card { border: 1px solid #ddd; margin: 10px 0; padding: 15px; border-radius: 5px; }
        .btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .sidebar { width: 250px; background: #007bff; color: white; padding: 20px; position: fixed; height: 100vh; left: 0; top: 0; }
        .main-content { margin-left: 270px; padding: 20px; }
        .nav-link { display: block; color: white; text-decoration: none; padding: 10px; margin: 5px 0; border-radius: 5px; }
        .nav-link:hover { background: rgba(255,255,255,0.1); }
        .nav-link.active { background: rgba(255,255,255,0.2); }
    </style>
</head>
<body>
    <?php
    // Iniciar sesión
    session_start();
    
    // Simular usuario si no existe
    if (!isset($_SESSION['usuario'])) {
        $_SESSION['usuario'] = [
            'nombre' => 'Usuario',
            'apellido' => 'Test',
            'rol' => 'admin'
        ];
    }
    ?>
    
    <div class="sidebar">
        <h3>🏨 Hotel Management</h3>
        <p style="margin: 15px 0; font-size: 14px;">
            👤 <?php echo $_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellido']; ?><br>
            <span style="background: white; color: #007bff; padding: 2px 8px; border-radius: 10px; font-size: 12px;">
                <?php echo ucfirst($_SESSION['usuario']['rol']); ?>
            </span>
        </p>
        <hr style="border-color: rgba(255,255,255,0.3);">
        <a href="test.php" class="nav-link active">🏠 Dashboard</a>
        <a href="test.php?page=contabilidad" class="nav-link">🧮 Contabilidad</a>
        <a href="test.php?page=habitaciones" class="nav-link">🛏️ Habitaciones</a>
        <a href="test.php?page=clientes" class="nav-link">👥 Clientes</a>
        <a href="test.php?page=reportes" class="nav-link">📊 Reportes</a>
        <hr style="border-color: rgba(255,255,255,0.3);">
        <a href="logout.php" class="nav-link">🚪 Cerrar Sesión</a>
    </div>
    
    <div class="main-content">
        <?php
        $page = $_GET['page'] ?? 'dashboard';
        
        switch($page) {
            case 'contabilidad':
                echo '<h1>🧮 Módulo Contable</h1>';
                echo '<div class="card">';
                echo '<h3>💰 Resumen Financiero</h3>';
                echo '<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px;">';
                echo '<div style="background: #28a745; color: white; padding: 20px; border-radius: 8px; text-align: center;">
                        <h4>$15.2M</h4><small>Ingresos del Mes</small></div>';
                echo '<div style="background: #dc3545; color: white; padding: 20px; border-radius: 8px; text-align: center;">
                        <h4>$8.7M</h4><small>Egresos del Mes</small></div>';
                echo '<div style="background: #007bff; color: white; padding: 20px; border-radius: 8px; text-align: center;">
                        <h4>$6.5M</h4><small>Balance Neto</small></div>';
                echo '<div style="background: #17a2b8; color: white; padding: 20px; border-radius: 8px; text-align: center;">
                        <h4>47</h4><small>Transacciones</small></div>';
                echo '</div>';
                echo '</div>';
                
                echo '<div class="card">';
                echo '<h3>📋 Transacciones Recientes</h3>';
                echo '<table style="width: 100%; border-collapse: collapse;">';
                echo '<tr style="background: #f8f9fa;"><th>Comprobante</th><th>Fecha</th><th>Descripción</th><th>Monto</th><th>Estado</th></tr>';
                echo '<tr><td>2024-12-001</td><td>2024-12-15</td><td>Ingreso por reservas</td><td>$250,000</td><td>✅ Confirmada</td></tr>';
                echo '<tr><td>2024-12-002</td><td>2024-12-14</td><td>Compras suministros</td><td>$150,000</td><td>✅ Confirmada</td></tr>';
                echo '<tr><td>2024-12-003</td><td>2024-12-13</td><td>Servicios restaurante</td><td>$180,000</td><td>✅ Confirmada</td></tr>';
                echo '</table>';
                echo '</div>';
                break;
                
            case 'habitaciones':
                echo '<h1>🛏️ Gestión de Habitaciones</h1>';
                echo '<div class="card"><h3>Estado de Habitaciones</h3><p>25 habitaciones totales</p></div>';
                break;
                
            case 'clientes':
                echo '<h1>👥 Gestión de Clientes</h1>';
                echo '<div class="card"><h3>Clientes Registrados</h3><p>156 clientes activos</p></div>';
                break;
                
            case 'reportes':
                echo '<h1>📊 Reportes Financieros</h1>';
                echo '<div class="card"><h3>Reportes Disponibles</h3><p>Reportes mensuales y anuales</p></div>';
                break;
                
            default:
                echo '<h1>🏠 Dashboard - Sistema Hotelero</h1>';
                echo '<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px;">';
                echo '<div class="card" style="text-align: center;"><h3>25</h3><p>Total Habitaciones</p></div>';
                echo '<div class="card" style="text-align: center;"><h3>12</h3><p>Reservas Activas</p></div>';
                echo '<div class="card" style="text-align: center;"><h3>156</h3><p>Total Clientes</p></div>';
                echo '<div class="card" style="text-align: center;"><h3>$15.2M</h3><p>Ingresos del Mes</p></div>';
                echo '</div>';
                
                echo '<div class="card">';
                echo '<h3>📈 Actividad Reciente</h3>';
                echo '<table style="width: 100%; border-collapse: collapse;">';
                echo '<tr style="background: #f8f9fa;"><th>Cliente</th><th>Habitación</th><th>Estado</th><th>Precio</th><th>Fecha</th></tr>';
                echo '<tr><td>Juan Pérez</td><td>Hab. 101</td><td>✅ Confirmada</td><td>$250,000</td><td>2024-12-15</td></tr>';
                echo '<tr><td>María García</td><td>Hab. 205</td><td>⏳ Pendiente</td><td>$180,000</td><td>2024-12-14</td></tr>';
                echo '<tr><td>Carlos López</td><td>Hab. 302</td><td>✅ Confirmada</td><td>$320,000</td><td>2024-12-13</td></tr>';
                echo '</table>';
                echo '</div>';
        }
        ?>
        
        <div class="card">
            <h3>🔧 Estado del Sistema</h3>
            <p><strong>✅ PHP:</strong> <?php echo phpversion(); ?></p>
            <p><strong>✅ Sesión:</strong> Activa</p>
            <p><strong>✅ Usuario:</strong> <?php echo $_SESSION['usuario']['nombre']; ?></p>
            <p><strong>✅ Rol:</strong> <?php echo $_SESSION['usuario']['rol']; ?></p>
            <p><strong>✅ Fecha:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
        
        <div style="margin-top: 20px;">
            <button class="btn" onclick="alert('Función JavaScript activa')">🧪 Test JavaScript</button>
            <button class="btn" onclick="location.reload()">🔄 Recargar Página</button>
            <a href="diagnostico.php" class="btn">🔍 Diagnóstico Completo</a>
        </div>
    </div>
    
    <script>
        console.log('✅ JavaScript cargado correctamente');
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ DOM completamente cargado');
        });
    </script>
</body>
</html>
