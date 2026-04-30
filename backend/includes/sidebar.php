<?php
require_once __DIR__ . '/simple_permissions.php';

if (!function_exists('hotel_tame_define_web_constants')) {
    require_once __DIR__ . '/../../config/env.php';
}
hotel_tame_define_web_constants();
$WB = HOTEL_TAME_WEB_BASE;

SimplePermissionHelper::initialize($_SESSION['usuario']['id']);

$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
if ($WB !== '' && str_starts_with($uriPath, $WB)) {
    $uriPath = substr($uriPath, strlen($WB)) ?: '/';
}
$current_page = ltrim($uriPath, '/');
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
        <a href="<?php echo $WB; ?>/dashboard" class="nav-link text-white <?php echo $current_page == 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="<?php echo $WB; ?>/habitaciones" class="nav-link text-white <?php echo $current_page == 'habitaciones' ? 'active' : ''; ?>">
            <i class="fas fa-bed"></i> Habitaciones
        </a>
        <?php if ($_SESSION['usuario']['rol'] === 'admin'): ?>
        <a href="<?php echo $WB; ?>/usuarios" class="nav-link text-white <?php echo $current_page == 'usuarios' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Usuarios
        </a>
        <a href="<?php echo $WB; ?>/settings" class="nav-link text-white <?php echo $current_page == 'settings' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i> Configuración
        </a>
        <!-- <a href="roles.php" class="nav-link text-white <?php echo $current_page == 'roles.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-shield"></i> Roles y Permisos
        </a> -->
        <?php endif; ?>
        <a href="<?php echo $WB; ?>/clientes" class="nav-link text-white <?php echo $current_page == 'clientes' ? 'active' : ''; ?>">
            <i class="fas fa-user-tie"></i> Clientes
        </a>
        <a href="<?php echo $WB; ?>/productos" class="nav-link text-white <?php echo $current_page == 'productos' ? 'active' : ''; ?>">
            <i class="fas fa-box"></i> Productos
        </a>
        <a href="<?php echo $WB; ?>/pedidos-productos" class="nav-link text-white <?php echo $current_page == 'pedidos-productos' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i> Pedidos
        </a>
        <a href="<?php echo $WB; ?>/reservas" class="nav-link text-white <?php echo $current_page == 'reservas' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i> Reservas
        </a>
        
        <!-- Módulos reactivados con permisos granulares -->
        <?php if ($_SESSION['usuario']['rol'] === 'admin' || $_SESSION['usuario']['rol'] === 'gerente'): ?>
        <a href="<?php echo $WB; ?>/turnos" class="nav-link text-white <?php echo $current_page == 'turnos' ? 'active' : ''; ?>">
            <i class="fas fa-user-clock"></i> Gestión de Turnos
        </a>
        <?php endif; ?>
        
        <a href="<?php echo $WB; ?>/mis-actividades" class="nav-link text-white <?php echo $current_page == 'mis-actividades' ? 'active' : ''; ?>">
            <i class="fas fa-tasks"></i> Mis Actividades
        </a>
        
        <!-- Módulos de eventos (futuros) -->
        <?php if ($_SESSION['usuario']['rol'] === 'admin'): ?>
        <a href="<?php echo $WB; ?>/eventos" class="nav-link text-white <?php echo $current_page == 'eventos' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i> Eventos
        </a>
        <a href="<?php echo $WB; ?>/espacios-eventos" class="nav-link text-white <?php echo $current_page == 'espacios-eventos' ? 'active' : ''; ?>">
            <i class="fas fa-door-open"></i> Espacios de Eventos
        </a>
        <a href="<?php echo $WB; ?>/reservas-eventos" class="nav-link text-white <?php echo $current_page == 'reservas-eventos' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-plus"></i> Reservas de Eventos
        </a>
        <?php endif; ?>
       <?php if ($_SESSION['usuario']['rol'] === 'admin' || $_SESSION['usuario']['rol'] === 'Contador' || $_SESSION['usuario']['rol'] === 'Auxiliar Contable'): ?>
        <a href="<?php echo $WB; ?>/contabilidad" class="nav-link text-white <?php echo $current_page == 'contabilidad' ? 'active' : ''; ?>">
            <i class="fas fa-calculator"></i> Contabilidad
        </a> 
       <a href="<?php echo $WB; ?>/reportes" class="nav-link text-white <?php echo $current_page == 'reportes' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i> Reportes
        </a> 
       <?php endif; ?>
        <hr class="my-1 border-white border-opacity-25">
        
        <a href="<?php echo $WB; ?>/logout" class="nav-link text-white">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </a>
    </nav>
</div>
