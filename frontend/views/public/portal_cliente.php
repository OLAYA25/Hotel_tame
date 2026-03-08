<?php
require_once __DIR__ . '/../../../backend/config/database.php';

// Iniciar sesión para el cliente
session_start();

// Inicializar variables para evitar warnings
$error = '';
$success = '';

// Procesar login del cliente
if (isset($_POST['action']) && $_POST['action'] == 'login') {
    $email = trim($_POST['email'] ?? '');
    $documento = trim($_POST['documento'] ?? '');
    
    if (empty($email) || empty($documento)) {
        $error = "Por favor completa todos los campos";
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $stmt = $db->prepare("SELECT id, nombre, apellido, email, telefono, documento 
                                 FROM clientes 
                                 WHERE (email = :email OR documento = :documento) 
                                 AND deleted_at IS NULL");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':documento', $documento);
            $stmt->execute();
            
            $cliente = $stmt->fetch();
            
            if ($cliente) {
                $_SESSION['cliente'] = $cliente;
                $success = "Inicio de sesión exitoso. ¡Bienvenido de nuevo!";
            } else {
                $error = "No encontramos una cuenta con esos datos. Verifica tu email y documento.";
            }
        } catch (Exception $e) {
            $error = "Error en el sistema: " . $e->getMessage();
        }
    }
}

// Procesar logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header('Location: portal_cliente.php');
    exit;
}

// Obtener reservas del cliente si está logueado
$reservas_cliente = [];
$reservas_activas = [];
$reservas_historial = [];

if (isset($_SESSION['cliente'])) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $cliente_id = $_SESSION['cliente']['id'];
        
        // Obtener todas las reservas del cliente
        $stmt = $db->prepare("SELECT r.*, h.numero, h.tipo, h.precio_noche as precio_habitacion,
                                     h.imagen as habitacion_imagen
                              FROM reservas r 
                              JOIN habitaciones h ON r.habitacion_id = h.id 
                              WHERE r.cliente_id = :cliente_id 
                              ORDER BY r.fecha_creacion DESC");
        $stmt->bindParam(':cliente_id', $cliente_id);
        $stmt->execute();
        $reservas_cliente = $stmt->fetchAll();
        
        // Separar reservas activas de historial
        $hoy = date('Y-m-d');
        foreach ($reservas_cliente as $reserva) {
            if ($reserva['estado'] === 'confirmada' && $reserva['fecha_salida'] >= $hoy) {
                $reservas_activas[] = $reserva;
            } else {
                $reservas_historial[] = $reserva;
            }
        }
        
    } catch (Exception $e) {
        error_log("Error obteniendo reservas del cliente: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Cliente - Hotel Tame</title>
    <meta name="description" content="Accede a tu portal personal de Hotel Tame para gestionar tus reservas y ver tu historial.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/Hotel_tame/assets/css/web.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="client-portal">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-web fixed-top">
        <div class="container">
            <a class="navbar-brand" href="home.php">
                <i class="fas fa-hotel me-2"></i>Hotel Tame
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reservas_online.php">Reservar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="portal_cliente.php">Mi Portal</a>
                    </li>
                    <?php if (isset($_SESSION['cliente'])): ?>
                        <li class="nav-item">
                            <a href="?action=logout" class="btn btn-outline-custom btn-sm ms-3">
                                <i class="fas fa-sign-out-alt me-1"></i>Cerrar Sesión
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <?php if (!isset($_SESSION['cliente'])): ?>
        <!-- Login Section -->
        <section class="section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-5">
                        <div class="client-login-card">
                            <div class="text-center mb-4">
                                <i class="fas fa-user-circle fa-4x text-primary mb-3"></i>
                                <h2 class="fw-bold">Portal Cliente</h2>
                                <p class="text-muted">Accede a tu área personal para gestionar tus reservas</p>
                            </div>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <?= htmlspecialchars($error) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?= htmlspecialchars($success) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <input type="hidden" name="action" value="login">
                                
                                <div class="mb-3">
                                    <label class="form-label-custom">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                        <input type="email" name="email" class="form-control form-control-custom" 
                                               placeholder="tu@email.com" required>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label-custom">Documento de Identidad</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-id-card"></i>
                                        </span>
                                        <input type="text" name="documento" class="form-control form-control-custom" 
                                               placeholder="Tu número de documento" required>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary-custom w-100 mb-3">
                                    <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                                </button>
                                
                                <div class="text-center">
                                    <small class="text-muted">
                                        ¿No tienes cuenta? 
                                        <a href="reservas_online.php" class="text-primary">Haz una reserva</a>
                                        y se creará automáticamente.
                                    </small>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php else: ?>
        <!-- Client Dashboard -->
        <section class="section">
            <div class="container">
                <!-- Welcome Header -->
                <div class="client-welcome">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="fw-bold mb-2">
                                <i class="fas fa-user-circle me-2"></i>
                                ¡Bienvenido, <?= htmlspecialchars($_SESSION['cliente']['nombre']) ?>!
                            </h1>
                            <p class="mb-0">Gestiona tus reservas y disfruta de tu experiencia en Hotel Tame</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <a href="reservas_online.php" class="btn btn-light">
                                <i class="fas fa-plus-circle me-2"></i>Nueva Reserva
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-check fa-3x text-primary mb-3"></i>
                                <h3 class="fw-bold"><?= count($reservas_activas) ?></h3>
                                <p class="text-muted mb-0">Reservas Activas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-history fa-3x text-success mb-3"></i>
                                <h3 class="fw-bold"><?= count($reservas_historial) ?></h3>
                                <p class="text-muted mb-0">Reservas Anteriores</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-star fa-3x text-warning mb-3"></i>
                                <h3 class="fw-bold">VIP</h3>
                                <p class="text-muted mb-0">Nivel Cliente</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Reservations -->
                <?php if (!empty($reservas_activas)): ?>
                    <div class="mb-5">
                        <h3 class="fw-bold mb-4">
                            <i class="fas fa-clock me-2"></i>Mis Reservas Activas
                        </h3>
                        
                        <div class="row g-4">
                            <?php foreach ($reservas_activas as $reserva): ?>
                                <div class="col-md-6">
                                    <div class="reservation-card">
                                        <div class="row align-items-center">
                                            <div class="col-md-4">
                                                <?php if ($reserva['habitacion_imagen']): ?>
                                                    <img src="/Hotel_tame/assets/images/habitaciones/<?= $reserva["habitacion_imagen'] ?>" 
                                                         class="img-fluid rounded" alt="Habitación">
                                                <?php else: ?>
                                                    <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=300&h=200&fit=crop" 
                                                         class="img-fluid rounded" alt="Habitación">
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h5 class="fw-bold mb-0">
                                                        <?= htmlspecialchars($reserva['tipo']) ?> - <?= htmlspecialchars($reserva['numero']) ?>
                                                    </h5>
                                                    <span class="reservation-status status-confirmada">
                                                        <?= ucfirst($reserva['estado']) ?>
                                                    </span>
                                                </div>
                                                
                                                <div class="mb-2">
                                                    <i class="fas fa-calendar text-muted me-2"></i>
                                                    <small class="text-muted">
                                                        <?= date('d/m/Y', strtotime($reserva['fecha_entrada'])) ?> - 
                                                        <?= date('d/m/Y', strtotime($reserva['fecha_salida'])) ?>
                                                    </small>
                                                </div>
                                                
                                                <div class="mb-2">
                                                    <i class="fas fa-dollar-sign text-muted me-2"></i>
                                                    <small class="text-muted">
                                                        Total: $<?= number_format($reserva['total'], 0, ',', '.') ?>
                                                    </small>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <i class="fas fa-hashtag text-muted me-2"></i>
                                                    <small class="text-muted">
                                                        Reserva #<?= $reserva['id'] ?>
                                                    </small>
                                                </div>
                                                
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="verDetalles(<?= $reserva['id'] ?>)">
                                                        <i class="fas fa-eye me-1"></i>Ver Detalles
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-success" onclick="modificarReserva(<?= $reserva['id'] ?>)">
                                                        <i class="fas fa-edit me-1"></i>Modificar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 mb-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No tienes reservas activas</h4>
                        <p class="text-muted">¿Listo para tu próxima escapada?</p>
                        <a href="reservas_online.php" class="btn btn-primary-custom">
                            <i class="fas fa-plus-circle me-2"></i>Hacer una Reserva
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Reservation History -->
                <?php if (!empty($reservas_historial)): ?>
                    <div>
                        <h3 class="fw-bold mb-4">
                            <i class="fas fa-history me-2"></i>Historial de Reservas
                        </h3>
                        
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Reserva</th>
                                                <th>Habitación</th>
                                                <th>Fechas</th>
                                                <th>Total</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($reservas_historial as $reserva): ?>
                                                <tr>
                                                    <td>
                                                        <strong>#<?= $reserva['id'] ?></strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?= date('d/m/Y', strtotime($reserva['fecha_creacion'])) ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <?= htmlspecialchars($reserva['tipo']) ?> - <?= htmlspecialchars($reserva['numero']) ?>
                                                    </td>
                                                    <td>
                                                        <?= date('d/m/Y', strtotime($reserva['fecha_entrada'])) ?>
                                                        <br>
                                                        <?= date('d/m/Y', strtotime($reserva['fecha_salida'])) ?>
                                                    </td>
                                                    <td>
                                                        $<?= number_format($reserva['total'], 0, ',', '.') ?>
                                                    </td>
                                                    <td>
                                                        <span class="reservation-status status-<?= $reserva['estado'] ?>">
                                                            <?= ucfirst($reserva['estado']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary" onclick="verDetalles(<?= $reserva['id'] ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-success" onclick="reservarNueva()">
                                                            <i class="fas fa-redo"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Hotel Tame</h5>
                    <p class="text-muted">Tu experiencia hotelera de lujo</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="text-muted small mb-0">© 2024 Hotel Tame. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Reservation Details Modal -->
    <div class="modal fade" id="detallesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalles de Reserva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detallesContent">
                    <!-- Content loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="imprimirReserva()">
                        <i class="fas fa-print me-2"></i>Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function verDetalles(reservaId) {
            // Simulación - en producción esto haría una llamada AJAX
            const detallesModal = new bootstrap.Modal(document.getElementById('detallesModal'));
            const detallesContent = document.getElementById('detallesContent');
            
            detallesContent.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando detalles...</p>
                </div>
            `;
            
            detallesModal.show();
            
            // Simular carga de datos
            setTimeout(() => {
                const clienteNombre = '<?= isset($_SESSION['cliente']) ? htmlspecialchars($_SESSION['cliente']['nombre'] . ' ' . ($_SESSION['cliente']['apellido'] ?? '')) : "Cliente" ?>';
                const clienteEmail = '<?= isset($_SESSION['cliente']) ? htmlspecialchars($_SESSION['cliente']['email']) : "email@example.com" ?>';
                const clienteTelefono = '<?= isset($_SESSION['cliente']) ? htmlspecialchars($_SESSION['cliente']['telefono'] ?? 'No especificado') : "No especificado" ?>';
                
                detallesContent.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold">Información de Reserva</h6>
                            <p><strong>Número:</strong> #${reservaId}</p>
                            <p><strong>Estado:</strong> <span class="badge bg-success">Confirmada</span></p>
                            <p><strong>Fecha de creación:</strong> ${new Date().toLocaleDateString('es-CO')}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold">Información de Huésped</h6>
                            <p><strong>Nombre:</strong> ${clienteNombre}</p>
                            <p><strong>Email:</strong> ${clienteEmail}</p>
                            <p><strong>Teléfono:</strong> ${clienteTelefono}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold">Detalles de Habitación</h6>
                            <p><strong>Tipo:</strong> Suite Deluxe</p>
                            <p><strong>Número:</strong> 105</p>
                            <p><strong>Capacidad:</strong> 2 personas</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold">Fechas y Tarifas</h6>
                            <p><strong>Check-in:</strong> ${new Date().toLocaleDateString('es-CO')}</p>
                            <p><strong>Check-out:</strong> ${new Date(Date.now() + 86400000).toLocaleDateString('es-CO')}</p>
                            <p><strong>Total:</strong> <span class="text-primary fw-bold">$298.000</span></p>
                        </div>
                    </div>
                `;
            }, 1000);
        }
        
        function modificarReserva(reservaId) {
            if (confirm('¿Deseas modificar esta reserva? Te contactaremos para confirmar los cambios.')) {
                // En producción esto haría una llamada AJAX
                alert('Solicitud de modificación enviada. Te contactaremos pronto.');
            }
        }
        
        function reservarNueva() {
            window.location.href = 'reservas_online.php';
        }
        
        function imprimirReserva() {
            window.print();
        }
    </script>
</body>
</html>
