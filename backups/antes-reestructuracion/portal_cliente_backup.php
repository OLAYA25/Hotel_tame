<?php
require_once 'config/database.php';

// Iniciar sesión para el cliente
session_start();

// Procesar login del cliente
if (isset($_POST['action']) && $_POST['action'] == 'login') {
    $email = $_POST['email'];
    $documento = $_POST['documento'];
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $stmt = $db->prepare("SELECT id, nombre, apellido, email, telefono FROM clientes WHERE email = :email AND (documento = :documento OR numero_documento = :documento) AND deleted_at IS NULL");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':documento', $documento);
        $stmt->execute();
        
        $cliente = $stmt->fetch();
        
        if ($cliente) {
            $_SESSION['cliente'] = $cliente;
            $success = "Inicio de sesión exitoso";
        } else {
            $error = "Email o documento incorrectos";
        }
    } catch (Exception $e) {
        $error = "Error en el sistema: " . $e->getMessage();
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
if (isset($_SESSION['cliente'])) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $stmt = $db->prepare("SELECT r.*, h.numero, h.tipo, h.precio_noche as precio_habitacion 
                              FROM reservas r 
                              JOIN habitaciones h ON r.habitacion_id = h.id 
                              WHERE r.cliente_id = :cliente_id AND r.deleted_at IS NULL 
                              ORDER BY r.created_at DESC");
        $stmt->bindParam(':cliente_id', $_SESSION['cliente']['id']);
        $stmt->execute();
        
        $reservas_cliente = $stmt->fetchAll();
    } catch (Exception $e) {
        $error = "Error al cargar tus reservas: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal del Cliente - Hotel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .portal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
        }
        .reservation-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .reservation-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .status-confirmada { background: #d4edda; color: #155724; }
        .status-pendiente { background: #fff3cd; color: #856404; }
        .status-cancelada { background: #f8d7da; color: #721c24; }
        .status-completada { background: #d1ecf1; color: #0c5460; }
        .login-form {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 400px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand text-primary" href="home.php">
                <i class="fas fa-hotel me-2"></i>Hotel Management
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
                        <a class="nav-link" href="reservas_online.php">Reservas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="portal_cliente.php">Mi Portal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="home.php#servicios">Servicios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="home.php#contacto">Contacto</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <?php if (!isset($_SESSION['cliente'])): ?>
        <!-- Login Section -->
        <section class="portal-header">
            <div class="container text-center">
                <h1 class="display-4 fw-bold mb-3">Portal del Cliente</h1>
                <p class="lead">Accede a tus reservas y gestiona tu estancia</p>
            </div>
        </section>

        <section class="py-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="login-form">
                            <h3 class="text-center mb-4">
                                <i class="fas fa-user-circle text-primary me-2"></i>
                                Iniciar Sesión
                            </h3>
                            
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($success)): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?php echo htmlspecialchars($success); ?>
                                </div>
                            <?php endif; ?>

                            <form method="post">
                                <input type="hidden" name="action" value="login">
                                
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" name="email" required 
                                               placeholder="tu@email.com">
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Número de Documento</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                        <input type="text" class="form-control" name="documento" required 
                                               placeholder="DNI, Pasaporte, etc.">
                                    </div>
                                    <small class="text-muted">Usa el mismo documento que usaste al registrar tu reserva</small>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100 btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Acceder a mi Portal
                                </button>
                            </form>
                            
                            <div class="text-center mt-4">
                                <p class="text-muted mb-0">¿No tienes una reserva?</p>
                                <a href="reservas_online.php" class="btn btn-outline-primary btn-sm mt-2">
                                    <i class="fas fa-plus me-1"></i>Hacer una Reserva
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    <?php else: ?>
        <!-- Client Dashboard -->
        <section class="portal-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-5 fw-bold mb-2">
                            <i class="fas fa-user-circle me-2"></i>
                            Bienvenido, <?php echo htmlspecialchars($_SESSION['cliente']['nombre']); ?>
                        </h1>
                        <p class="lead mb-0">Gestiona tus reservas y preferencias</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="portal_cliente.php?action=logout" class="btn btn-light">
                            <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                        </a>
                        <li class="nav-item ms-2">
                            <a href="login.php" class="btn btn-outline-primary">
                                <i class="fas fa-sign-in-alt me-1"></i>Acceso Admin
                            </a>
                        </li>
                    </div>
                </div>
            </div>
        </section>

        <!-- Client Info Section -->
        <section class="py-4 bg-light">
            <div class="container">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-user fa-2x text-primary mb-3"></i>
                                <h6 class="text-muted">Nombre</h6>
                                <p class="mb-0 fw-bold"><?php echo htmlspecialchars($_SESSION['cliente']['nombre'] . ' ' . $_SESSION['cliente']['apellido']); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-envelope fa-2x text-primary mb-3"></i>
                                <h6 class="text-muted">Email</h6>
                                <p class="mb-0 fw-bold"><?php echo htmlspecialchars($_SESSION['cliente']['email']); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-phone fa-2x text-primary mb-3"></i>
                                <h6 class="text-muted">Teléfono</h6>
                                <p class="mb-0 fw-bold"><?php echo htmlspecialchars($_SESSION['cliente']['telefono']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Reservations Section -->
        <section class="py-5">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold">Mis Reservas</h2>
                    <a href="reservas_online.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nueva Reserva
                    </a>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($reservas_cliente)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No tienes reservas aún</h4>
                        <p class="text-muted">Tu primera reserva aparecerá aquí</p>
                        <a href="reservas_online.php" class="btn btn-primary mt-3">
                            <i class="fas fa-plus me-2"></i>Hacer tu Primera Reserva
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($reservas_cliente as $reserva): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card reservation-card h-100">
                                    <div class="card-header bg-white border-bottom">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge status-<?php echo $reserva['estado']; ?>">
                                                <?php echo ucfirst($reserva['estado']); ?>
                                            </span>
                                            <small class="text-muted">#<?php echo $reserva['id']; ?></small>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <i class="fas fa-bed text-primary me-2"></i>
                                            Habitación <?php echo htmlspecialchars($reserva['numero']); ?>
                                        </h5>
                                        <p class="text-muted small mb-3">
                                            <?php echo ucfirst(htmlspecialchars($reserva['tipo'])); ?>
                                        </p>
                                        
                                        <div class="mb-3">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-calendar-check text-muted me-2"></i>
                                                <small><strong>Check-in:</strong> <?php echo date('d/m/Y', strtotime($reserva['fecha_entrada'])); ?></small>
                                            </div>
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-calendar-times text-muted me-2"></i>
                                                <small><strong>Check-out:</strong> <?php echo date('d/m/Y', strtotime($reserva['fecha_salida'])); ?></small>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-dollar-sign text-muted me-2"></i>
                                                <small><strong>Total:</strong> $<?php echo number_format($reserva['precio_total'], 0, ',', '.'); ?></small>
                                            </div>
                                        </div>
                                        
                                        <?php if ($reserva['comentarios']): ?>
                                            <div class="mb-3">
                                                <small class="text-muted"><strong>Comentarios:</strong></small>
                                                <p class="small text-muted mb-0"><?php echo htmlspecialchars($reserva['comentarios']); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer bg-white border-top">
                                        <div class="d-flex gap-2">
                                            <?php if ($reserva['estado'] == 'confirmada'): ?>
                                                <button class="btn btn-sm btn-outline-primary flex-fill" onclick="verDetalles(<?php echo $reserva['id']; ?>)">
                                                    <i class="fas fa-eye me-1"></i>Ver Detalles
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="cancelarReserva(<?php echo $reserva['id']; ?>)">
                                                    <i class="fas fa-times me-1"></i>Cancelar
                                                </button>
                                            <?php elseif ($reserva['estado'] == 'pendiente'): ?>
                                                <button class="btn btn-sm btn-outline-success flex-fill" onclick="verDetalles(<?php echo $reserva['id']; ?>)">
                                                    <i class="fas fa-clock me-1"></i>Pendiente
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-outline-info flex-fill" onclick="verDetalles(<?php echo $reserva['id']; ?>)">
                                                    <i class="fas fa-info-circle me-1"></i>Ver Historial
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Quick Actions Section -->
        <section class="py-5 bg-light">
            <div class="container">
                <h3 class="fw-bold text-center mb-4">Acciones Rápidas</h3>
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-bed fa-2x text-primary mb-3"></i>
                                <h6 class="card-title">Nueva Reserva</h6>
                                <p class="card-text small text-muted">Reserva otra habitación</p>
                                <a href="reservas_online.php" class="btn btn-primary btn-sm">Reservar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-headset fa-2x text-primary mb-3"></i>
                                <h6 class="card-title">Soporte</h6>
                                <p class="card-text small text-muted">Ayuda con tu reserva</p>
                                <button class="btn btn-primary btn-sm" onclick="contactarSoporte()">Contactar</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-file-invoice fa-2x text-primary mb-3"></i>
                                <h6 class="card-title">Facturas</h6>
                                <p class="card-text small text-muted">Descarga tus facturas</p>
                                <button class="btn btn-primary btn-sm" onclick="verFacturas()">Ver</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-cog fa-2x text-primary mb-3"></i>
                                <h6 class="card-title">Preferencias</h6>
                                <p class="card-text small text-muted">Configura tus preferencias</p>
                                <button class="btn btn-primary btn-sm" onclick="editarPreferencias()">Editar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function verDetalles(reservaId) {
            alert('Mostrando detalles de la reserva #' + reservaId + '. Esta función estará disponible pronto.');
        }

        function cancelarReserva(reservaId) {
            if (confirm('¿Estás seguro de que deseas cancelar esta reserva? Esta acción no se puede deshacer.')) {
                $.ajax({
                    url: 'api/endpoints/reservas.php',
                    type: 'DELETE',
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify({ id: reservaId }),
                    success: function(response) {
                        showNotification('Reserva cancelada exitosamente', 'success');
                        setTimeout(() => location.reload(), 1500);
                    },
                    error: function(xhr) {
                        let msg = 'Error al cancelar la reserva';
                        try {
                            const json = JSON.parse(xhr.responseText);
                            msg = json.message || json.error || xhr.responseText;
                        } catch (e) {
                            msg = xhr.responseText || msg;
                        }
                        showNotification(msg, 'error');
                    }
                });
            }
        }

        function contactarSoporte() {
            showNotification('Redirigiendo al soporte...', 'info');
        }

        function verFacturas() {
            showNotification('Sistema de facturas próximamente disponible', 'info');
        }

        function editarPreferencias() {
            showNotification('Editor de preferencias próximamente disponible', 'info');
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
            notification.style.zIndex = '9999';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
    </script>
</body>
</html>
