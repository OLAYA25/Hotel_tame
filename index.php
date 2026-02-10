<?php
require_once 'config/database.php';

// Verificar sesión de usuario
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Incluir middleware de autenticación y permisos
require_once 'includes/auth_middleware.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Total habitaciones
    $stmt = $db->query("SELECT COUNT(*) as total FROM habitaciones WHERE deleted_at IS NULL");
    $row = $stmt ? $stmt->fetch() : null;
    $total_habitaciones = $row['total'] ?? 0;

    $stmt = $db->query("SELECT COUNT(*) as total FROM habitaciones WHERE estado = 'disponible' AND deleted_at IS NULL");
    $row = $stmt ? $stmt->fetch() : null;
    $habitaciones_disponibles = $row['total'] ?? 0;

    // Reservas activas
    $stmt = $db->query("SELECT COUNT(*) as total FROM reservas WHERE estado IN ('confirmada', 'pendiente') AND deleted_at IS NULL");
    $row = $stmt ? $stmt->fetch() : null;
    $reservas_activas = $row['total'] ?? 0;

    // Total clientes
    $stmt = $db->query("SELECT COUNT(*) as total FROM clientes WHERE deleted_at IS NULL");
    $row = $stmt ? $stmt->fetch() : null;
    $total_clientes = $row['total'] ?? 0;

    // Ingresos del mes desde reservas confirmadas
    $stmt = $db->query("SELECT COALESCE(SUM(precio_total), 0) as total 
                                 FROM reservas 
                                 WHERE estado = 'confirmada' 
                                 AND deleted_at IS NULL
                                 AND MONTH(fecha_entrada) = MONTH(CURRENT_DATE()) 
                                 AND YEAR(fecha_entrada) = YEAR(CURRENT_DATE())");
    $row = $stmt ? $stmt->fetch() : null;
    $ingresos_mes = $row['total'] ?? 0;

    // Actividad reciente
    $stmt = $db->query("SELECT r.id, r.estado, r.precio_total, r.created_at, c.nombre as cliente_nombre, c.apellido as cliente_apellido, h.numero as habitacion_numero, h.tipo as habitacion_tipo FROM reservas r JOIN clientes c ON r.cliente_id = c.id JOIN habitaciones h ON r.habitacion_id = h.id WHERE r.deleted_at IS NULL ORDER BY r.created_at DESC LIMIT 5");
    $actividad_reciente = $stmt ? $stmt->fetchAll() : [];

} catch (Exception $e) {
    die("Error cargando datos: " . $e->getMessage());
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div id="notification-container"></div>
    
    <div class="page-header">
        <h1>Dashboard - Sistema de Gestión Hotelera</h1>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stats-card primary">
                <div class="icon">
                    <i class="fas fa-bed"></i>
                </div>
                <h3><?php echo $total_habitaciones; ?></h3>
                <p>Total Habitaciones</p>
                <span class="badge bg-success"><?php echo $habitaciones_disponibles; ?> disponibles</span>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stats-card success">
                <div class="icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3><?php echo $reservas_activas; ?></h3>
                <p>Reservas Activas</p>
                <span class="badge bg-warning">Confirmadas y pendientes</span>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stats-card info">
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3><?php echo $total_clientes; ?></h3>
                <p>Total Clientes</p>
                <span class="badge bg-info">Clientes registrados</span>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stats-card primary">
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <h3>$<?php echo number_format($ingresos_mes, 0, ',', '.'); ?></h3>
                <p>Ingresos del Mes</p>
                <span class="badge bg-success">Total del mes actual</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="table-container">
                <h4 class="mb-4">Actividad Reciente</h4>
                <div class="table-responsive">
                    <table class="table">
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
                            <?php 
                            if (!empty($actividad_reciente)):
                                foreach ($actividad_reciente as $row): 
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="activity-icon me-3">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <strong><?php echo htmlspecialchars($row['cliente_nombre'] . ' ' . $row['cliente_apellido']); ?></strong>
                                    </div>
                                </td>
                                <td>Habitación <?php echo htmlspecialchars($row['habitacion_numero']); ?> - <?php echo ucfirst($row['habitacion_tipo']); ?></td>
                                <td>
                                    <?php
                                    $badge_class = '';
                                    switch($row['estado']) {
                                        case 'confirmada': $badge_class = 'bg-success'; break;
                                        case 'pendiente': $badge_class = 'bg-warning'; break;
                                        case 'cancelada': $badge_class = 'bg-danger'; break;
                                        default: $badge_class = 'bg-secondary';
                                    }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo ucfirst($row['estado']); ?>
                                    </span>
                                </td>
                                <td><strong>$<?php echo number_format($row['precio_total'], 0, ',', '.'); ?></strong></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                            </tr>
                            <?php 
                                endforeach;
                            else:
                            ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay actividad reciente</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$db = null; // cerrar conexión PDO
include 'includes/footer.php'; 
?>
