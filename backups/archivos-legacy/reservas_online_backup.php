<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Obtener habitaciones disponibles
    $stmt = $db->query("SELECT id, numero, tipo, precio_noche as precio, capacidad, descripcion FROM habitaciones WHERE estado = 'disponible' AND deleted_at IS NULL ORDER BY tipo, numero");
    $habitaciones = $stmt ? $stmt->fetchAll() : [];

} catch (Exception $e) {
    $error = "Error conectando a la base de datos: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas Online - Hotel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero-booking {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
        }
        .room-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .price-tag {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 8px 16px;
            border-radius: 25px;
            font-weight: bold;
        }
        .booking-form {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        .step::after {
            content: '';
            position: absolute;
            top: 15px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #ddd;
            z-index: -1;
        }
        .step:last-child::after {
            display: none;
        }
        .step.active .step-number {
            background: #667eea;
            color: white;
        }
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #ddd;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
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
                        <a class="nav-link active" href="reservas_online.php">Reservas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="home.php#servicios">Servicios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="home.php#contacto">Contacto</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a href="login.php" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-1"></i>Acceso Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-booking">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Reserva tu Habitación</h1>
                    <p class="lead mb-4">Disfruta de una experiencia inolvidable en nuestro hotel. Reserva fácilmente online.</p>
                    <div class="d-flex gap-3">
                        <div class="text-center">
                            <i class="fas fa-bed fa-2x mb-2"></i>
                            <p class="mb-0">50+ Habitaciones</p>
                        </div>
                        <div class="text-center">
                            <i class="fas fa-star fa-2x mb-2"></i>
                            <p class="mb-0">5 Estrellas</p>
                        </div>
                        <div class="text-center">
                            <i class="fas fa-users fa-2x mb-2"></i>
                            <p class="mb-0">1000+ Clientes</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="booking-form">
                        <h4 class="mb-4">Reserva Rápida</h4>
                        <form id="quickBookingForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Check-in</label>
                                    <input type="date" class="form-control" id="checkin" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Check-out</label>
                                    <input type="date" class="form-control" id="checkout" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Adultos</label>
                                    <select class="form-select" id="adultos">
                                        <option value="1">1</option>
                                        <option value="2" selected>2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Niños</label>
                                    <select class="form-select" id="ninos">
                                        <option value="0">0</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Habitaciones</label>
                                    <select class="form-select" id="num_habitaciones">
                                        <option value="1" selected>1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Buscar Disponibilidad
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Available Rooms Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Habitaciones Disponibles</h2>
                <p class="text-muted">Selecciona la habitación perfecta para tu estancia</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php else: ?>
                <div class="row g-4" id="roomsGrid">
                    <?php if (empty($habitaciones)): ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle me-2"></i>
                                No hay habitaciones disponibles en este momento. Por favor, intenta más tarde.
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($habitaciones as $habitacion): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card room-card h-100">
                                    <div class="position-relative">
                                        <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400&h=250&fit=crop" 
                                             class="card-img-top" alt="<?php echo htmlspecialchars($habitacion['tipo']); ?>">
                                        <span class="position-absolute top-0 end-0 m-2 price-tag">
                                            $<?php echo number_format($habitacion['precio'], 0, ',', '.'); ?>/noche
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            Habitación <?php echo htmlspecialchars($habitacion['numero']); ?> - 
                                            <?php echo ucfirst(htmlspecialchars($habitacion['tipo'])); ?>
                                        </h5>
                                        <div class="mb-2">
                                            <i class="fas fa-users text-muted me-1"></i>
                                            <small class="text-muted">Capacidad: <?php echo $habitacion['capacidad']; ?> personas</small>
                                        </div>
                                        <p class="card-text text-muted small">
                                            <?php echo htmlspecialchars($habitacion['descripcion'] ?? 'Habitación cómoda y moderna con todas las comodidades necesarias para una estancia agradable.'); ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-wifi text-muted me-2"></i>
                                                <i class="fas fa-tv text-muted me-2"></i>
                                                <i class="fas fa-snowflake text-muted"></i>
                                            </div>
                                            <button class="btn btn-primary btn-sm" onclick="seleccionarHabitacion(<?php echo $habitacion['id']; ?>, '<?php echo htmlspecialchars($habitacion['numero']); ?>', '<?php echo htmlspecialchars($habitacion['tipo']); ?>', <?php echo $habitacion['precio']; ?>)">
                                                Seleccionar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Booking Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Completar Reserva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="bookingForm" onsubmit="procesarReserva(event)">
                    <div class="modal-body">
                        <!-- Step Indicator -->
                        <div class="step-indicator">
                            <div class="step active" id="step1">
                                <div class="step-number">1</div>
                                <small>Datos Personales</small>
                            </div>
                            <div class="step" id="step2">
                                <div class="step-number">2</div>
                                <small>Confirmación</small>
                            </div>
                        </div>

                        <!-- Step 1: Personal Data -->
                        <div id="step1Content">
                            <input type="hidden" id="habitacion_id">
                            <input type="hidden" id="habitacion_numero">
                            <input type="hidden" id="habitacion_tipo">
                            <input type="hidden" id="habitacion_precio">
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <span id="roomInfo"></span>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" id="nombre" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Apellido *</label>
                                    <input type="text" class="form-control" id="apellido" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Teléfono *</label>
                                    <input type="tel" class="form-control" id="telefono" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Check-in *</label>
                                    <input type="date" class="form-control" id="reserva_checkin" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Check-out *</label>
                                    <input type="date" class="form-control" id="reserva_checkout" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Comentarios adicionales</label>
                                <textarea class="form-control" id="comentarios" rows="3" placeholder="¿Necesitas algo especial para tu estancia?"></textarea>
                            </div>
                        </div>

                        <!-- Step 2: Confirmation -->
                        <div id="step2Content" style="display: none;">
                            <div class="alert alert-success">
                                <h6><i class="fas fa-check-circle me-2"></i>Resumen de tu Reserva</h6>
                                <div id="resumenReserva"></div>
                            </div>
                            <div class="text-center">
                                <p class="text-muted">Al confirmar, recibirás un email con los detalles de tu reserva.</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="prevBtn" onclick="cambiarPaso(-1)" style="display: none;">Anterior</button>
                        <button type="button" class="btn btn-primary" id="nextBtn" onclick="cambiarPaso(1)">Siguiente</button>
                        <button type="submit" class="btn btn-success" id="confirmBtn" style="display: none;">
                            <i class="fas fa-check me-2"></i>Confirmar Reserva
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let currentStep = 1;
        let selectedRoom = null;

        // Set minimum dates for booking
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('checkin').min = today;
            document.getElementById('checkout').min = today;
            document.getElementById('reserva_checkin').min = today;
            document.getElementById('reserva_checkout').min = today;
        });

        // Quick booking form
        document.getElementById('quickBookingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const checkin = document.getElementById('checkin').value;
            const checkout = document.getElementById('checkout').value;
            
            if (new Date(checkout) <= new Date(checkin)) {
                alert('La fecha de check-out debe ser posterior al check-in');
                return;
            }
            
            // Scroll to rooms section
            document.querySelector('#roomsGrid').scrollIntoView({ behavior: 'smooth' });
            
            // Filter rooms based on availability (simulation)
            showNotification('Buscando habitaciones disponibles...', 'info');
        });

        function seleccionarHabitacion(id, numero, tipo, precio) {
            selectedRoom = { id, numero, tipo, precio };
            
            // Set form values
            document.getElementById('habitacion_id').value = id;
            document.getElementById('habitacion_numero').value = numero;
            document.getElementById('habitacion_tipo').value = tipo;
            document.getElementById('habitacion_precio').value = precio;
            
            // Set room info
            document.getElementById('roomInfo').innerHTML = 
                `Has seleccionado: Habitación ${numero} - ${tipo} ($${precio}/noche)`;
            
            // Copy dates from quick form if available
            const quickCheckin = document.getElementById('checkin').value;
            const quickCheckout = document.getElementById('checkout').value;
            if (quickCheckin) document.getElementById('reserva_checkin').value = quickCheckin;
            if (quickCheckout) document.getElementById('reserva_checkout').value = quickCheckout;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('bookingModal'));
            modal.show();
        }

        function cambiarPaso(direccion) {
            if (direccion === 1 && !validarPasoActual()) {
                return;
            }
            
            // Hide current step
            document.getElementById(`step${currentStep}Content`).style.display = 'none';
            document.getElementById(`step${currentStep}`).classList.remove('active');
            
            // Show next step
            currentStep += direccion;
            document.getElementById(`step${currentStep}Content`).style.display = 'block';
            document.getElementById(`step${currentStep}`).classList.add('active');
            
            // Update buttons
            document.getElementById('prevBtn').style.display = currentStep === 1 ? 'none' : 'inline-block';
            document.getElementById('nextBtn').style.display = currentStep === 2 ? 'none' : 'inline-block';
            document.getElementById('confirmBtn').style.display = currentStep === 2 ? 'inline-block' : 'none';
            
            // Generate summary if step 2
            if (currentStep === 2) {
                generarResumen();
            }
        }

        function validarPasoActual() {
            if (currentStep === 1) {
                const nombre = document.getElementById('nombre').value;
                const apellido = document.getElementById('apellido').value;
                const email = document.getElementById('email').value;
                const telefono = document.getElementById('telefono').value;
                const checkin = document.getElementById('reserva_checkin').value;
                const checkout = document.getElementById('reserva_checkout').value;
                
                if (!nombre || !apellido || !email || !telefono || !checkin || !checkout) {
                    showNotification('Por favor, completa todos los campos obligatorios', 'error');
                    return false;
                }
                
                if (new Date(checkout) <= new Date(checkin)) {
                    showNotification('La fecha de check-out debe ser posterior al check-in', 'error');
                    return false;
                }
            }
            return true;
        }

        function generarResumen() {
            const nombre = document.getElementById('nombre').value;
            const apellido = document.getElementById('apellido').value;
            const email = document.getElementById('email').value;
            const telefono = document.getElementById('telefono').value;
            const checkin = document.getElementById('reserva_checkin').value;
            const checkout = document.getElementById('reserva_checkout').value;
            const comentarios = document.getElementById('comentarios').value;
            
            const nights = Math.ceil((new Date(checkout) - new Date(checkin)) / (1000 * 60 * 60 * 24));
            const total = nights * selectedRoom.precio;
            
            const resumen = `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Cliente:</strong> ${nombre} ${apellido}</p>
                        <p><strong>Email:</strong> ${email}</p>
                        <p><strong>Teléfono:</strong> ${telefono}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Habitación:</strong> ${selectedRoom.numero} - ${selectedRoom.tipo}</p>
                        <p><strong>Check-in:</strong> ${formatDate(checkin)}</p>
                        <p><strong>Check-out:</strong> ${formatDate(checkout)}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Noches:</strong> ${nights}</p>
                        <p><strong>Precio por noche:</strong> $${selectedRoom.precio}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Total a pagar:</strong> <span class="text-success fs-4">$${total}</span></p>
                    </div>
                </div>
                ${comentarios ? `<p><strong>Comentarios:</strong> ${comentarios}</p>` : ''}
            `;
            
            document.getElementById('resumenReserva').innerHTML = resumen;
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
        }

        function procesarReserva(event) {
            event.preventDefault();
            
            const data = {
                habitacion_id: parseInt(document.getElementById('habitacion_id').value),
                nombre: document.getElementById('nombre').value,
                apellido: document.getElementById('apellido').value,
                email: document.getElementById('email').value,
                telefono: document.getElementById('telefono').value,
                fecha_entrada: document.getElementById('reserva_checkin').value,
                fecha_salida: document.getElementById('reserva_checkout').value,
                comentarios: document.getElementById('comentarios').value,
                precio_total: calcularTotal(),
                estado: 'pendiente'
            };
            
            $.ajax({
                url: 'api/endpoints/reservas.php',
                type: 'POST',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function(response) {
                    $('#bookingModal').modal('hide');
                    showNotification('¡Reserva confirmada! Recibirás un email con los detalles.', 'success');
                    // Reset form
                    document.getElementById('bookingForm').reset();
                    currentStep = 1;
                    document.getElementById('step1Content').style.display = 'block';
                    document.getElementById('step2Content').style.display = 'none';
                    document.getElementById('step2').classList.remove('active');
                    document.getElementById('step1').classList.add('active');
                },
                error: function(xhr) {
                    let msg = 'Error al procesar la reserva';
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

        function calcularTotal() {
            const checkin = new Date(document.getElementById('reserva_checkin').value);
            const checkout = new Date(document.getElementById('reserva_checkout').value);
            const nights = Math.ceil((checkout - checkin) / (1000 * 60 * 60 * 24));
            return nights * selectedRoom.precio;
        }

        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
            notification.style.zIndex = '9999';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
    </script>
</body>
</html>
