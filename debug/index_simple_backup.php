<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Hotel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            padding: 20px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            background: #f8f9fa;
            min-height: 100vh;
        }
        .nav-link {
            display: block;
            color: white;
            text-decoration: none;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
        }
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
        }
        .nav-link.active {
            background: #3498db;
        }
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background: #3498db;
            color: white;
            font-weight: bold;
        }
        .stat-card {
            text-align: center;
            padding: 20px;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #2c3e50;
        }
        .badge-success { background: #27ae60; }
        .badge-warning { background: #f39c12; }
        .badge-info { background: #3498db; }
    </style>
</head>
<body>
    <?php
    session_start();
    if (!isset($_SESSION['usuario'])) {
        header('Location: login_simple.php');
        exit;
    }
    ?>
    
    <div class="sidebar">
        <div style="text-align: center; margin-bottom: 30px;">
            <h3>🏨 Hotel Management</h3>
            <div style="margin-top: 15px;">
                <div style="font-size: 14px;">👤 <?php echo $_SESSION['usuario']['nombre']; ?> <?php echo $_SESSION['usuario']['apellido']; ?></div>
                <div style="font-size: 12px; opacity: 0.8;"><?php echo ucfirst($_SESSION['usuario']['rol']); ?></div>
            </div>
        </div>
        <nav>
            <a href="index_simple.php" class="nav-link active">🏠 Dashboard</a>
            <a href="#" class="nav-link">🛏️ Habitaciones</a>
            <a href="#" class="nav-link">👥 Clientes</a>
            <a href="#" class="nav-link">📅 Reservas</a>
            <a href="#" class="nav-link">📦 Productos</a>
            <a href="#" class="nav-link">🛒 Pedidos</a>
            <a href="#" class="nav-link">🎉 Eventos</a>
            <a href="#" class="nav-link">🧮 Contabilidad</a>
            <a href="#" class="nav-link">📊 Reportes</a>
            <hr style="border-color: rgba(255,255,255,0.3); margin: 20px 0;">
            <a href="logout.php" class="nav-link">🚪 Cerrar Sesión</a>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="card">
            <div class="card-body">
                <h1>Dashboard - Sistema de Gestión Hotelera</h1>
                <p class="text-muted">Panel principal de control</p>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 20px;">
            <div class="card stat-card">
                <h3 style="color: #3498db;">25</h3>
                <p>Total Habitaciones</p>
            </div>
            <div class="card stat-card">
                <h3 style="color: #27ae60;">12</h3>
                <p>Reservas Activas</p>
            </div>
            <div class="card stat-card">
                <h3 style="color: #f39c12;">156</h3>
                <p>Total Clientes</p>
            </div>
            <div class="card stat-card">
                <h3 style="color: #e74c3c;">$15.2M</h3>
                <p>Ingresos del Mes</p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                Actividad Reciente
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Habitación</th>
                            <th>Estado</th>
                            <th>Precio</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Juan Pérez</td>
                            <td>Hab. 101</td>
                            <td><span class="badge badge-success">Confirmada</span></td>
                            <td>$250,000</td>
                            <td>2024-12-15</td>
                        </tr>
                        <tr>
                            <td>María García</td>
                            <td>Hab. 205</td>
                            <td><span class="badge badge-warning">Pendiente</span></td>
                            <td>$180,000</td>
                            <td>2024-12-14</td>
                        </tr>
                        <tr>
                            <td>Carlos López</td>
                            <td>Hab. 302</td>
                            <td><span class="badge badge-success">Confirmada</span></td>
                            <td>$320,000</td>
                            <td>2024-12-13</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        console.log('✅ Dashboard cargado correctamente');
    </script>
</body>
</html>
