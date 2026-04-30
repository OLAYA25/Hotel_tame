<?php
require_once 'config/database.php';

// Verificar sesión de usuario
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// NO incluir el middleware complejo que causa problemas

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

    // Ingresos del mes
    $stmt = $db->query("SELECT COALESCE(SUM(precio_total), 0) as total FROM reservas WHERE MONTH(fecha_entrada) = MONTH(CURRENT_DATE()) AND YEAR(fecha_entrada) = YEAR(CURRENT_DATE()) AND deleted_at IS NULL");
    $row = $stmt ? $stmt->fetch() : null;
    $ingresos_mes = $row['total'] ?? 0;

    // Actividad reciente
    $stmt = $db->query("SELECT r.id, r.estado, r.precio_total, r.created_at, c.nombre as cliente_nombre, c.apellido as cliente_apellido, h.numero as habitacion_numero, h.tipo as habitacion_tipo FROM reservas r JOIN clientes c ON r.cliente_id = c.id JOIN habitaciones h ON r.habitacion_id = h.id WHERE r.deleted_at IS NULL ORDER BY r.created_at DESC LIMIT 5");
    $actividad_reciente = $stmt ? $stmt->fetchAll() : [];

} catch (Exception $e) {
    die("Error cargando datos: " . $e->getMessage());
}

// Usar includes simples que funcionan
include 'includes/header_simple.php';
include 'includes/sidebar_simple.php';
?>

<div class="main-content">
    <div id="notification-container"></div>
    
    <div class="page-header">
        <h1>Dashboard - Sistema de Gestión Hotelera</h1>
        <p class="text-muted mb-0">Panel principal de control</p>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Habitaciones</h6>
                            <h3><?php echo $total_habitaciones; ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-bed fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Reservas Activas</h6>
                            <h3><?php echo $reservas_activas; ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Clientes</h6>
                            <h3><?php echo $total_clientes; ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-tie fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Ingresos del Mes</h6>
                            <h3>$<?php echo number_format($ingresos_mes, 0, ',', '.'); ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Actividad Reciente</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
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
                        <?php if (!empty($actividad_reciente)): ?>
                            <?php foreach ($actividad_reciente as $actividad): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($actividad['cliente_nombre'] . ' ' . $actividad['cliente_apellido']); ?></td>
                                    <td><?php echo htmlspecialchars($actividad['habitacion_numero'] . ' - ' . $actividad['habitacion_tipo']); ?></td>
                                    <td>
                                        <?php
                                        $estado_badge = $actividad['estado'] === 'confirmada' ? 'success' : 
                                                       ($actividad['estado'] === 'pendiente' ? 'warning' : 'secondary');
                                        ?>
                                        <span class="badge bg-<?php echo $estado_badge; ?>">
                                            <?php echo ucfirst(htmlspecialchars($actividad['estado'])); ?>
                                        </span>
                                    </td>
                                    <td>$<?php echo number_format($actividad['precio_total'], 0, ',', '.'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($actividad['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No hay actividad reciente</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    console.log('Dashboard cargado correctamente');
});
</script>

<?php include 'includes/footer_simple.php'; ?>
