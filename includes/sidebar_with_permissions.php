<?php
require_once __DIR__ . '/../api/utils/PermissionHelper.php';

// Inicializar sistema de permisos para el usuario actual
PermissionHelper::initialize($_SESSION['usuario']['id']);

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
        <?php
        // Obtener módulos accesibles del usuario
        $modulos_accesibles = PermissionHelper::getAccessibleModules();
        
        foreach ($modulos_accesibles as $modulo) {
            $active_class = ($current_page === $modulo['ruta']) ? 'active' : '';
            $icon_class = '';
            
            // Asignar iconos según el módulo
            switch($modulo['nombre']) {
                case 'dashboard': $icon_class = 'fas fa-home'; break;
                case 'habitaciones': $icon_class = 'fas fa-bed'; break;
                case 'usuarios': $icon_class = 'fas fa-users'; break;
                case 'clientes': $icon_class = 'fas fa-user-tie'; break;
                case 'productos': $icon_class = 'fas fa-box'; break;
                case 'pedidos': $icon_class = 'fas fa-shopping-cart'; break;
                case 'reservas': $icon_class = 'fas fa-calendar-check'; break;
                case 'eventos': $icon_class = 'fas fa-calendar-alt'; break;
                case 'espacios_eventos': $icon_class = 'fas fa-door-open'; break;
                case 'reservas_eventos': $icon_class = 'fas fa-calendar-plus'; break;
                case 'contabilidad': $icon_class = 'fas fa-calculator'; break;
                case 'reportes': $icon_class = 'fas fa-chart-line'; break;
                case 'turnos': $icon_class = 'fas fa-clock'; break;
                default: $icon_class = 'fas fa-circle';
            }
            
            // Nombre amigable para mostrar
            $nombre_mostrar = ucfirst(str_replace('_', ' ', $modulo['nombre']));
            
            echo "<a href=\"{$modulo['ruta']}\" class=\"nav-link text-white {$active_class}\">
                    <i class=\"{$icon_class}\"></i> {$nombre_mostrar}
                  </a>";
        }
        ?>
        
        <!-- Enlace de gestión de roles (solo para administradores) -->
        <?php if (hasPermission('usuarios_gestionar_roles')): ?>
        <a href="roles.php" class="nav-link text-white <?php echo $current_page == 'roles.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-shield"></i> Roles y Permisos
        </a>
        <?php endif; ?>
        
        <hr class="my-2 border-white border-opacity-25">
        
        <a href="logout.php" class="nav-link text-white">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </a>
        </div>
    </nav>
</div>
