<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar bg-primary text-white" id="sidebar">
    <div class="sidebar-header p-3">
        <h3 class="text-center">
            <i class="fas fa-hotel"></i> Hotel Management
        </h3>
        <div class="text-center mt-2">
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
    <nav class="nav flex-column p-3">
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
        <a href="roles.php" class="nav-link text-white <?php echo $current_page == 'roles.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-shield"></i> Roles y Permisos
        </a>
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
        <a href="eventos.php" class="nav-link text-white <?php echo $current_page == 'eventos.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i> Eventos
        </a>
        <a href="espacios_eventos.php" class="nav-link text-white <?php echo $current_page == 'espacios_eventos.php' ? 'active' : ''; ?>">
            <i class="fas fa-door-open"></i> Espacios de Eventos
        </a>
        <a href="reservas_eventos.php" class="nav-link text-white <?php echo $current_page == 'reservas_eventos.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-plus"></i> Reservas de Eventos
        </a>
        <hr class="my-2 border-white border-opacity-25">
        
        <a href="logout.php" class="nav-link text-white">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </a>
    </nav>
</div>
