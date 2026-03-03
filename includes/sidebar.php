<?php
require_once __DIR__ . '/simple_permissions.php';

// Inicializar sistema de permisos para el usuario actual
SimplePermissionHelper::initialize($_SESSION['usuario']['id']);

$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar bg-primary text-white" id="sidebar">
    <div class="sidebar-header p-2">
        <h3 class="text-center">
            <i class="fas fa-hotel"></i> Hotel
        </h3>
        <div class="text-center mt-1">
            <small>
                <i class="fas fa-user-circle me-1"></i>
                <?php echo htmlspecialchars($_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellido']); ?>
            </small>
            <br>
            <span class="badge bg-light text-primary">
                <?php echo ucfirst(htmlspecialchars($_SESSION['usuario']['rol'])); ?>
            </span>
        </div>
    </div>
    <nav class="nav p-2">
        <a href="index.php" class="nav-link text-white <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="habitaciones.php" class="nav-link text-white <?php echo $current_page == 'habitaciones.php' ? 'active' : ''; ?>">
            <i class="fas fa-bed"></i> Habitaciones
        </a>
        <?php if ($_SESSION['usuario']['rol'] === 'admin'): ?>
        <a href="usuarios.php" class="nav-link text-white <?php echo $current_page == 'usuarios.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Usuarios
        </a>
        <!-- <a href="roles.php" class="nav-link text-white <?php echo $current_page == 'roles.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-shield"></i> Roles y Permisos
        </a> -->
        <?php endif; ?>
        <a href="clientes.php" class="nav-link text-white <?php echo $current_page == 'clientes.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-tie"></i> Clientes
        </a>
        <a href="productos.php" class="nav-link text-white <?php echo $current_page == 'productos.php' ? 'active' : ''; ?>">
            <i class="fas fa-box"></i> Productos
        </a>
        <a href="pedidos_productos.php" class="nav-link text-white <?php echo $current_page == 'pedidos_productos.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i> Pedidos
        </a>
        <a href="reservas.php" class="nav-link text-white <?php echo $current_page == 'reservas.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i> Reservas
        </a>
        <!-- <a href="eventos.php" class="nav-link text-white <?php echo $current_page == 'eventos.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i> Eventos
        </a> -->
        <!-- <a href="espacios_eventos.php" class="nav-link text-white <?php echo $current_page == 'espacios_eventos.php' ? 'active' : ''; ?>">
            <i class="fas fa-door-open"></i> Espacios de Eventos
        </a> -->
        <!-- <a href="reservas_eventos.php" class="nav-link text-white <?php echo $current_page == 'reservas_eventos.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-plus"></i> Reservas de Eventos
        </a> -->
        <!-- <?php if ($_SESSION['usuario']['rol'] === 'admin'): ?> -->
        <!-- <a href="turnos.php" class="nav-link text-white <?php echo $current_page == 'turnos.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-clock"></i> Gestión de Turnos
        </a> -->
        <!-- <?php endif; ?> -->
        <!-- <a href="mis_actividades.php" class="nav-link text-white <?php echo $current_page == 'mis_actividades.php' ? 'active' : ''; ?>">
            <i class="fas fa-tasks"></i> Mis Actividades
        </a> -->
       <?php if ($_SESSION['usuario']['rol'] === 'admin' || $_SESSION['usuario']['rol'] === 'Contador' || $_SESSION['usuario']['rol'] === 'Auxiliar Contable'): ?>
        <a href="contabilidad.php" class="nav-link text-white <?php echo $current_page == 'contabilidad.php' ? 'active' : ''; ?>">
            <i class="fas fa-calculator"></i> Contabilidad
        </a> 
       <a href="reportes.php" class="nav-link text-white <?php echo $current_page == 'reportes.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i> Reportes
        </a> 
       <a href="backup_manager.php" class="nav-link text-white <?php echo $current_page == 'backup_manager.php' ? 'active' : ''; ?>">
            <i class="fas fa-database"></i> Backups
        </a>
       <?php endif; ?>
        <hr class="my-1 border-white border-opacity-25">
        
        <a href="logout.php" class="nav-link text-white">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </a>
    </nav>
</div>
