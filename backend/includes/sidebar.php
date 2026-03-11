<?php
require_once __DIR__ . '/simple_permissions.php';

// Inicializar sistema de permisos para el usuario actual
SimplePermissionHelper::initialize($_SESSION['usuario']['id']);

$current_page = str_replace('/Hotel_tame/', '', $_SERVER['REQUEST_URI']);
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
        <a href="/Hotel_tame/dashboard" class="nav-link text-white <?php echo $current_page == 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="/Hotel_tame/habitaciones" class="nav-link text-white <?php echo $current_page == 'habitaciones' ? 'active' : ''; ?>">
            <i class="fas fa-bed"></i> Habitaciones
        </a>
        <?php if ($_SESSION['usuario']['rol'] === 'admin'): ?>
        <a href="/Hotel_tame/usuarios" class="nav-link text-white <?php echo $current_page == 'usuarios' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Usuarios
        </a>
        <a href="/Hotel_tame/settings" class="nav-link text-white <?php echo $current_page == 'settings' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i> Configuración
        </a>
        <!-- <a href="roles.php" class="nav-link text-white <?php echo $current_page == 'roles.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-shield"></i> Roles y Permisos
        </a> -->
        <?php endif; ?>
        <a href="/Hotel_tame/clientes" class="nav-link text-white <?php echo $current_page == 'clientes' ? 'active' : ''; ?>">
            <i class="fas fa-user-tie"></i> Clientes
        </a>
        <a href="/Hotel_tame/productos" class="nav-link text-white <?php echo $current_page == 'productos' ? 'active' : ''; ?>">
            <i class="fas fa-box"></i> Productos
        </a>
        <a href="/Hotel_tame/pedidos-productos" class="nav-link text-white <?php echo $current_page == 'pedidos-productos' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i> Pedidos
        </a>
        <a href="/Hotel_tame/reservas" class="nav-link text-white <?php echo $current_page == 'reservas' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i> Reservas
        </a>
        
        <!-- Módulos reactivados con permisos granulares -->
        <?php if ($_SESSION['usuario']['rol'] === 'admin' || $_SESSION['usuario']['rol'] === 'gerente'): ?>
        <a href="/Hotel_tame/turnos" class="nav-link text-white <?php echo $current_page == 'turnos' ? 'active' : ''; ?>">
            <i class="fas fa-user-clock"></i> Gestión de Turnos
        </a>
        <?php endif; ?>
        
        <a href="/Hotel_tame/mis-actividades" class="nav-link text-white <?php echo $current_page == 'mis-actividades' ? 'active' : ''; ?>">
            <i class="fas fa-tasks"></i> Mis Actividades
        </a>
        
        <!-- Módulos de eventos (futuros) -->
        <?php if ($_SESSION['usuario']['rol'] === 'admin'): ?>
        <a href="/Hotel_tame/eventos" class="nav-link text-white <?php echo $current_page == 'eventos' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i> Eventos
        </a>
        <a href="/Hotel_tame/espacios-eventos" class="nav-link text-white <?php echo $current_page == 'espacios-eventos' ? 'active' : ''; ?>">
            <i class="fas fa-door-open"></i> Espacios de Eventos
        </a>
        <a href="/Hotel_tame/reservas-eventos" class="nav-link text-white <?php echo $current_page == 'reservas-eventos' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-plus"></i> Reservas de Eventos
        </a>
        <?php endif; ?>
       <?php if ($_SESSION['usuario']['rol'] === 'admin' || $_SESSION['usuario']['rol'] === 'Contador' || $_SESSION['usuario']['rol'] === 'Auxiliar Contable'): ?>
        <a href="/Hotel_tame/contabilidad" class="nav-link text-white <?php echo $current_page == 'contabilidad' ? 'active' : ''; ?>">
            <i class="fas fa-calculator"></i> Contabilidad
        </a> 
       <a href="/Hotel_tame/reportes" class="nav-link text-white <?php echo $current_page == 'reportes' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i> Reportes
        </a> 
       <?php endif; ?>
        <hr class="my-1 border-white border-opacity-25">
        
        <a href="/Hotel_tame/logout" class="nav-link text-white">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </a>
    </nav>
</div>
