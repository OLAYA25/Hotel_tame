<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

// Variables para la búsqueda y filtrado
$fecha_entrada = $_GET['fecha_entrada'] ?? '';
$fecha_salida = $_GET['fecha_salida'] ?? '';
$huespedes = $_GET['huespedes'] ?? 1;
$habitacion_id = $_GET['habitacion_id'] ?? '';

$habitaciones_disponibles = [];
$habitacion_seleccionada = null;
$error = '';
$success = '';

// Procesar reserva
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reservar') {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Validar datos
        $habitacion_id = $_POST['habitacion_id'] ?? '';
        $fecha_entrada = $_POST['fecha_entrada'] ?? '';
        $fecha_salida = $_POST['fecha_salida'] ?? '';
        $cliente_nombre = $_POST['cliente_nombre'] ?? '';
        $cliente_email = $_POST['cliente_email'] ?? '';
        $cliente_telefono = $_POST['cliente_telefono'] ?? '';
        $cliente_documento = $_POST['cliente_documento'] ?? '';
        $observaciones = $_POST['observaciones'] ?? '';
        
        if (empty($habitacion_id) || empty($fecha_entrada) || empty($fecha_salida) || 
            empty($cliente_nombre) || empty($cliente_email) || empty($cliente_documento)) {
            throw new Exception('Por favor completa todos los campos obligatorios');
        }
        
        // Validar fechas
        $fecha_entrada_dt = new DateTime($fecha_entrada);
        $fecha_salida_dt = new DateTime($fecha_salida);
        $hoy = new DateTime();
        
        if ($fecha_entrada_dt < $hoy) {
            throw new Exception('La fecha de entrada no puede ser anterior a hoy');
        }
        
        if ($fecha_salida_dt <= $fecha_entrada_dt) {
            throw new Exception('La fecha de salida debe ser posterior a la fecha de entrada');
        }
        
        // Verificar disponibilidad
        $stmt = $db->prepare("SELECT COUNT(*) as conflictos FROM reservas 
                             WHERE habitacion_id = ? AND estado = 'confirmada' 
                             AND ((fecha_entrada <= ? AND fecha_salida > ?) 
                             OR (fecha_entrada < ? AND fecha_salida >= ?))");
        $stmt->execute([$habitacion_id, $fecha_entrada, $fecha_entrada, $fecha_salida, $fecha_salida]);
        $conflictos = $stmt->fetch()['conflictos'];
        
        if ($conflictos > 0) {
            throw new Exception('La habitación no está disponible en las fechas seleccionadas');
        }
        
        // Obtener información de la habitación
        $stmt = $db->prepare("SELECT * FROM habitaciones WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$habitacion_id]);
        $habitacion = $stmt->fetch();
        
        if (!$habitacion) {
            throw new Exception('Habitación no encontrada');
        }
        
        // Calcular noches y total
        $intervalo = $fecha_entrada_dt->diff($fecha_salida_dt);
        $noches = $intervalo->days;
        $total = $habitacion['precio_noche'] * $noches;
        
        // Iniciar transacción
        $db->beginTransaction();
        
        // Verificar si el cliente ya existe
        $stmt = $db->prepare("SELECT id FROM clientes WHERE email = ? OR documento = ?");
        $stmt->execute([$cliente_email, $cliente_documento]);
        $cliente_existente = $stmt->fetch();
        
        if ($cliente_existente) {
            $cliente_id = $cliente_existente['id'];
        } else {
            // Crear nuevo cliente
            $stmt = $db->prepare("INSERT INTO clientes (nombre, email, telefono, documento, fecha_creacion) 
                                 VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$cliente_nombre, $cliente_email, $cliente_telefono, $cliente_documento]);
            $cliente_id = $db->lastInsertId();
        }
        
        // Crear reserva
        $stmt = $db->prepare("INSERT INTO reservas (cliente_id, habitacion_id, fecha_entrada, fecha_salida, 
                             estado, total, observaciones, fecha_creacion) 
                             VALUES (?, ?, ?, ?, 'pendiente', ?, ?, NOW())");
        $stmt->execute([$cliente_id, $habitacion_id, $fecha_entrada, $fecha_salida, $total, $observaciones]);
        $reserva_id = $db->lastInsertId();
        
        $db->commit();
        
        $success = "¡Reserva realizada con éxito! Tu número de reserva es: #{$reserva_id}. Te hemos enviado un email de confirmación.";
        
        // Limpiar formulario
        $_POST = [];
        $habitacion_id = '';
        
    } catch (Exception $e) {
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }
        $error = $e->getMessage();
    }
}

// Buscar habitaciones disponibles
if (!empty($fecha_entrada) && !empty($fecha_salida)) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Validar fechas
        $fecha_entrada_dt = new DateTime($fecha_entrada);
        $fecha_salida_dt = new DateTime($fecha_salida);
        $hoy = new DateTime();
        
        if ($fecha_entrada_dt >= $fecha_salida_dt) {
            throw new Exception('La fecha de salida debe ser posterior a la fecha de entrada');
        }
        
        // Obtener habitaciones disponibles
        $stmt = $db->prepare("SELECT h.*, 
                             (SELECT COUNT(*) FROM reservas r 
                              WHERE r.habitacion_id = h.id AND r.estado = 'confirmada'
                              AND ((r.fecha_entrada <= ? AND r.fecha_salida > ?) 
                              OR (r.fecha_entrada < ? AND r.fecha_salida >= ?))) as conflictos
                             FROM habitaciones h 
                             WHERE h.deleted_at IS NULL AND h.estado = 'disponible'
                             HAVING conflictos = 0
                             ORDER BY h.precio_noche ASC");
        $stmt->execute([$fecha_entrada, $fecha_entrada, $fecha_salida, $fecha_salida]);
        $habitaciones_disponibles = $stmt->fetchAll();
        
        // Obtener habitación seleccionada si se especificó
        if (!empty($habitacion_id)) {
            foreach ($habitaciones_disponibles as $h) {
                if ($h['id'] == $habitacion_id) {
                    $habitacion_seleccionada = $h;
                    break;
                }
            }
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas Online - Hotel Tame</title>
    <meta name="description" content="Reserva tu habitación en Hotel Tame fácilmente. Disponibilidad en tiempo real y confirmación instantánea.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/web.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
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
                        <a class="nav-link" href="#habitaciones">Habitaciones</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="home.php#servicios">Servicios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="reservas_online.php">Reservar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="portal_cliente.php">Mi Portal</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-booking">
        <div class="container">
            <div class="text-center">
                <h1 class="display-4 fw-bold mb-4">Reservas Online</h1>
                <p class="lead mb-0">Encuentra tu habitación perfecta en simples pasos</p>
            </div>
        </div>
    </section>

    <!-- Booking Form Section -->
    <section class="section">
        <div class="container">
            <!-- Alertas -->
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

            <!-- Search Form -->
            <div class="booking-form">
                <h3 class="text-center mb-4 fw-bold">
                    <i class="fas fa-search me-2"></i>Buscar Disponibilidad
                </h3>
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label-custom">Check-in</label>
                        <input type="date" name="fecha_entrada" class="form-control form-control-custom" 
                               value="<?= htmlspecialchars($fecha_entrada) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-custom">Check-out</label>
                        <input type="date" name="fecha_salida" class="form-control form-control-custom" 
                               value="<?= htmlspecialchars($fecha_salida) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-custom">Huéspedes</label>
                        <select name="huespedes" class="form-control form-control-custom">
                            <option value="1" <?= $huespedes == 1 ? 'selected' : '' ?>>1 Huésped</option>
                            <option value="2" <?= $huespedes == 2 ? 'selected' : '' ?>>2 Huéspedes</option>
                            <option value="3" <?= $huespedes == 3 ? 'selected' : '' ?>>3 Huéspedes</option>
                            <option value="4" <?= $huespedes == 4 ? 'selected' : '' ?>>4 Huéspedes</option>
                            <option value="5" <?= $huespedes == 5 ? 'selected' : '' ?>>5+ Huéspedes</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="fas fa-search me-2"></i>Buscar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Available Rooms -->
            <?php if (!empty($fecha_entrada) && !empty($fecha_salida)): ?>
                <?php if (!empty($habitaciones_disponibles)): ?>
                    <div class="mt-5">
                        <h3 class="fw-bold mb-4">
                            <i class="fas fa-bed me-2"></i>
                            Habitaciones Disponibles
                            <span class="badge bg-primary ms-2"><?= count($habitaciones_disponibles) ?></span>
                        </h3>
                        
                        <div class="row g-4">
                            <?php foreach ($habitaciones_disponibles as $habitacion): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="room-card <?= $habitacion_seleccionada && $habitacion_seleccionada['id'] == $habitacion['id'] ? 'border-primary border-2' : '' ?>">
                                        <?php if ($habitacion['imagen']): ?>
                                            <img src="assets/images/habitaciones/<?= $habitacion['imagen'] ?>" 
                                                 class="room-image" alt="Habitación <?= htmlspecialchars($habitacion['tipo']) ?>">
                                        <?php else: ?>
                                            <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400&h=300&fit=crop" 
                                                 class="room-image" alt="Habitación <?= htmlspecialchars($habitacion['tipo']) ?>">
                                        <?php endif; ?>
                                        
                                        <div class="room-card-body">
                                            <h5 class="room-title"><?= htmlspecialchars($habitacion['tipo']) ?> - <?= htmlspecialchars($habitacion['numero']) ?></h5>
                                            <p class="room-description">
                                                <?= htmlspecialchars(substr($habitacion['descripcion'] ?? 'Habitación confortable y elegante', 0, 80)) ?>...
                                            </p>
                                            
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <i class="fas fa-users text-muted me-1"></i>
                                                    <small class="text-muted"><?= $habitacion['capacidad'] ?> personas</small>
                                                </div>
                                                <div class="price-tag">
                                                    $<?= number_format($habitacion['precio_noche'], 0, ',', '.') ?>/noche
                                                </div>
                                            </div>
                                            
                                            <?php
                                            $intervalo = new DateTime($fecha_entrada)->diff(new DateTime($fecha_salida));
                                            $noches = $intervalo->days;
                                            $total = $habitacion['precio_noche'] * $noches;
                                            ?>
                                            
                                            <div class="mb-3">
                                                <small class="text-muted">
                                                    <?= $noches ?> noche(s) - Total: <strong>$<?= number_format($total, 0, ',', '.') ?></strong>
                                                </small>
                                            </div>
                                            
                                            <button type="button" class="btn btn-primary-custom w-100" 
                                                    onclick="seleccionarHabitacion(<?= $habitacion['id'] ?>)">
                                                <i class="fas fa-calendar-check me-2"></i>Seleccionar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 mt-5">
                        <i class="fas fa-bed fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No hay habitaciones disponibles</h4>
                        <p class="text-muted">Lo sentimos, no encontramos habitaciones disponibles para las fechas seleccionadas.</p>
                        <p class="text-muted">Intenta con otras fechas o contacta directamente con nuestro hotel.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Reservation Form -->
            <?php if ($habitacion_seleccionada): ?>
                <div class="mt-5">
                    <h3 class="fw-bold mb-4">
                        <i class="fas fa-user-edit me-2"></i>Completar Reserva
                    </h3>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="booking-form">
                                <form method="POST">
                                    <input type="hidden" name="action" value="reservar">
                                    <input type="hidden" name="habitacion_id" value="<?= $habitacion_seleccionada['id'] ?>">
                                    <input type="hidden" name="fecha_entrada" value="<?= $fecha_entrada ?>">
                                    <input type="hidden" name="fecha_salida" value="<?= $fecha_salida ?>">
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label-custom">Nombre Completo *</label>
                                            <input type="text" name="cliente_nombre" class="form-control form-control-custom" 
                                                   value="<?= htmlspecialchars($_POST['cliente_nombre'] ?? '') ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label-custom">Email *</label>
                                            <input type="email" name="cliente_email" class="form-control form-control-custom" 
                                                   value="<?= htmlspecialchars($_POST['cliente_email'] ?? '') ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label-custom">Teléfono</label>
                                            <input type="tel" name="cliente_telefono" class="form-control form-control-custom" 
                                                   value="<?= htmlspecialchars($_POST['cliente_telefono'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label-custom">Documento de Identidad *</label>
                                            <input type="text" name="cliente_documento" class="form-control form-control-custom" 
                                                   value="<?= htmlspecialchars($_POST['cliente_documento'] ?? '') ?>" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label-custom">Observaciones</label>
                                            <textarea name="observaciones" class="form-control form-control-custom" rows="3" 
                                                      placeholder="Indica aquí cualquier solicitud especial..."><?= htmlspecialchars($_POST['observaciones'] ?? '') ?></textarea>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="terminos" required>
                                                <label class="form-check-label" for="terminos">
                                                    Acepto los <a href="#" class="text-primary">términos y condiciones</a> y la 
                                                    <a href="#" class="text-primary">política de cancelación</a>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary-custom btn-lg w-100">
                                                <i class="fas fa-check-circle me-2"></i>Confirmar Reserva
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="booking-form">
                                <h5 class="fw-bold mb-3">Resumen de Reserva</h5>
                                
                                <div class="mb-3">
                                    <small class="text-muted">Habitación</small>
                                    <h6 class="fw-bold"><?= htmlspecialchars($habitacion_seleccionada['tipo']) ?> - <?= htmlspecialchars($habitacion_seleccionada['numero']) ?></h6>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted">Fechas</small>
                                    <p class="mb-0"><?= date('d/m/Y', strtotime($fecha_entrada)) ?> - <?= date('d/m/Y', strtotime($fecha_salida)) ?></p>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted">Noches</small>
                                    <p class="mb-0 fw-bold"><?= $noches ?> noche(s)</p>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted">Precio por noche</small>
                                    <p class="mb-0">$<?= number_format($habitacion_seleccionada['precio_noche'], 0, ',', '.') ?></p>
                                </div>
                                
                                <hr>
                                
                                <div class="mb-3">
                                    <small class="text-muted">Total a pagar</small>
                                    <h4 class="fw-bold text-primary">$<?= number_format($total, 0, ',', '.') ?></h4>
                                </div>
                                
                                <div class="alert alert-info small">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>Política de cancelación:</strong> Cancelación gratuita hasta 24 horas antes del check-in.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function seleccionarHabitacion(habitacionId) {
            const url = new URL(window.location);
            url.searchParams.set('habitacion_id', habitacionId);
            window.location.href = url.toString();
        }

        // Set minimum dates for booking
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const tomorrowStr = tomorrow.toISOString().split('T')[0];
            
            const checkInInput = document.querySelector('input[name="fecha_entrada"]');
            const checkOutInput = document.querySelector('input[name="fecha_salida"]');
            
            if (checkInInput) {
                checkInInput.min = today;
            }
            
            if (checkOutInput) {
                checkOutInput.min = tomorrowStr;
            }
            
            // Update checkout minimum when checkin changes
            if (checkInInput && checkOutInput) {
                checkInInput.addEventListener('change', function() {
                    const checkInDate = new Date(this.value);
                    const minCheckOut = new Date(checkInDate);
                    minCheckOut.setDate(minCheckOut.getDate() + 1);
                    checkOutInput.min = minCheckOut.toISOString().split('T')[0];
                    
                    if (new Date(checkOutInput.value) <= checkInDate) {
                        checkOutInput.value = minCheckOut.toISOString().split('T')[0];
                    }
                });
            }
        });
    </script>
</body>
</html>
