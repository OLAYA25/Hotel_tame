<?php
require_once __DIR__ . '/config/bootstrap.php';

// Verificar si hay datos de reserva
if (!isset($_GET['room_id']) || !isset($_GET['checkin']) || !isset($_GET['checkout'])) {
    header('Location: reservas_online.php');
    exit;
}

$roomId = $_GET['room_id'];
$checkin = $_GET['checkin'];
$checkout = $_GET['checkout'];
$adults = $_GET['adults'] ?? 2;
$children = $_GET['children'] ?? 0;

// Obtener información de la habitación
require_once __DIR__ . '/backend/src/Database/Database.php';

use Database\Database;

try {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT id, numero, tipo, capacidad, precio_base, descripcion 
                          FROM habitaciones 
                          WHERE id = :id AND deleted_at IS NULL");
    $stmt->execute([':id' => $roomId]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$room) {
        throw new Exception('Habitación no encontrada');
    }
    
    // Calcular noches y precio total
    $checkinDate = new DateTime($checkin);
    $checkoutDate = new DateTime($checkout);
    $nights = $checkinDate->diff($checkoutDate)->days;
    $totalPrice = $room['precio_base'] * $nights;
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    exit;
}

// Procesar formulario de reserva
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Iniciar transacción
        $db->beginTransaction();
        
        // Crear cliente
        $stmt = $db->prepare("INSERT INTO clientes (nombre, apellido, email, telefono, documento, tipo_documento, created_at) 
                              VALUES (:nombre, :apellido, :email, :telefono, :documento, :tipo_documento, NOW())");
        $stmt->execute([
            ':nombre' => $_POST['nombre'],
            ':apellido' => $_POST['apellido'],
            ':email' => $_POST['email'],
            ':telefono' => $_POST['telefono'],
            ':documento' => $_POST['documento'],
            ':tipo_documento' => $_POST['tipo_documento']
        ]);
        $clientId = $db->lastInsertId();
        
        // Crear reserva
        $stmt = $db->prepare("INSERT INTO reservas (habitacion_id, fecha_entrada, fecha_salida, estado, precio_total, created_at) 
                              VALUES (:habitacion_id, :fecha_entrada, :fecha_salida, 'pendiente', :precio_total, NOW())");
        $stmt->execute([
            ':habitacion_id' => $roomId,
            ':fecha_entrada' => $checkin,
            ':fecha_salida' => $checkout,
            ':precio_total' => $totalPrice
        ]);
        $reservationId = $db->lastInsertId();
        
        // Asociar cliente a reserva
        $stmt = $db->prepare("INSERT INTO reserva_clientes (reserva_id, cliente_id, rol, created_at) 
                              VALUES (:reserva_id, :cliente_id, 'titular', NOW())");
        $stmt->execute([
            ':reserva_id' => $reservationId,
            ':cliente_id' => $clientId
        ]);
        
        // Actualizar estado de habitación
        $stmt = $db->prepare("UPDATE habitaciones SET estado = 'reservada' WHERE id = :id");
        $stmt->execute([':id' => $roomId]);
        
        $db->commit();
        
        // Redirigir a página de confirmación
        header('Location: reserva_confirmacion_simple.php?reservation_id=' . $reservationId);
        exit;
        
    } catch (Exception $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Reserva - Hotel Tame</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --accent-color: #f39c12;
            --light-bg: #ecf0f1;
            --dark-text: #2c3e50;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: var(--dark-text);
        }

        .booking-header {
            background: linear-gradient(rgba(44, 62, 80, 0.9), rgba(52, 73, 94, 0.9));
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }

        .booking-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .booking-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .room-summary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 30px;
        }

        .form-section {
            padding: 30px;
        }

        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.25);
        }

        .btn-primary-custom {
            background: var(--primary-color);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            background: #34495e;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(44, 62, 80, 0.3);
        }

        .price-display {
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary-color);
        }

        .booking-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }

        .step {
            background: #e0e0e0;
            color: #666;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            position: relative;
            z-index: 2;
        }

        .step.active {
            background: var(--primary-color);
            color: white;
        }

        .required-asterisk {
            color: var(--secondary-color);
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .booking-container {
                padding: 0 10px;
            }
            
            .room-summary {
                padding: 20px;
            }
            
            .form-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="booking-header">
        <div class="booking-container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold mb-2">Completa tu Reserva</h1>
                    <p class="lead mb-0">Estás a un paso de confirmar tu estancia en Hotel Tame</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="reservas_online.php" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-2"></i>Volver a buscar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="booking-container">
        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step active">1</div>
            <div class="step active">2</div>
            <div class="step">3</div>
        </div>

        <div class="row">
            <!-- Room Summary -->
            <div class="col-lg-4">
                <div class="booking-card">
                    <div class="room-summary">
                        <h4 class="mb-3">Habitación <?= $room['numero'] ?></h4>
                        <p class="mb-2"><i class="fas fa-bed me-2"></i><?= ucfirst($room['tipo']) ?></p>
                        <p class="mb-2"><i class="fas fa-users me-2"></i>Capacidad: <?= $room['capacidad'] ?> personas</p>
                        <p class="mb-3"><i class="fas fa-calendar me-2"></i>
                            <?= date('d/m/Y', strtotime($checkin)) ?> - 
                            <?= date('d/m/Y', strtotime($checkout)) ?>
                        </p>
                        <div class="price-display">
                            $<?= number_format($totalPrice, 0, ',', '.') ?>
                        </div>
                        <small class="d-block mt-2"><?= $nights ?> noches</small>
                    </div>
                </div>

                <!-- Booking Summary -->
                <div class="booking-card">
                    <div class="form-section">
                        <h5 class="mb-3">Resumen de Reserva</h5>
                        <div class="booking-summary">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Habitación <?= $room['numero'] ?></span>
                                <span><?= ucfirst($room['tipo']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Noches</span>
                                <span><?= $nights ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Precio por noche</span>
                                <span>$<?= number_format($room['precio_base'], 0, ',', '.') ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total</span>
                                <span class="text-danger">$<?= number_format($totalPrice, 0, ',', '.') ?></span>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>Política de cancelación: Cancelación gratuita hasta 48 horas antes del check-in</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="col-lg-8">
                <div class="booking-card">
                    <div class="form-section">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= $error ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="bookingForm">
                            <h4 class="mb-4">Información del Huésped</h4>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nombre <span class="required-asterisk">*</span></label>
                                    <input type="text" class="form-control" name="nombre" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Apellido <span class="required-asterisk">*</span></label>
                                    <input type="text" class="form-control" name="apellido" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="required-asterisk">*</span></label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Teléfono <span class="required-asterisk">*</span></label>
                                    <input type="tel" class="form-control" name="telefono" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tipo de Documento <span class="required-asterisk">*</span></label>
                                    <select class="form-select" name="tipo_documento" required>
                                        <option value="CC">Cédula</option>
                                        <option value="CE">Cédula de Extranjería</option>
                                        <option value="PA">Pasaporte</option>
                                        <option value="TI">Tarjeta de Identidad</option>
                                    </select>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Número de Documento <span class="required-asterisk">*</span></label>
                                    <input type="text" class="form-control" name="documento" required>
                                </div>
                            </div>

                            <hr class="my-4">

                            <h4 class="mb-4">Información Adicional</h4>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Hora de llegada estimada</label>
                                    <select class="form-select" name="hora_llegada">
                                        <option value="12:00">12:00 PM</option>
                                        <option value="13:00">1:00 PM</option>
                                        <option value="14:00">2:00 PM</option>
                                        <option value="15:00" selected>3:00 PM</option>
                                        <option value="16:00">4:00 PM</option>
                                        <option value="17:00">5:00 PM</option>
                                        <option value="18:00">6:00 PM</option>
                                        <option value="19:00">7:00 PM</option>
                                        <option value="20:00">8:00 PM</option>
                                        <option value="21:00">9:00 PM</option>
                                        <option value="22:00">10:00 PM</option>
                                        <option value="23:00">11:00 PM</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Número de vuelo (opcional)</label>
                                    <input type="text" class="form-control" name="vuelo" placeholder="Si aplica">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Comentarios o Solicitudes Especiales</label>
                                <textarea class="form-control" name="comentarios" rows="3" placeholder="Ej: Habitación en piso alto, cama extra, etc."></textarea>
                            </div>

                            <hr class="my-4">

                            <h4 class="mb-4">Términos y Condiciones</h4>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terminos" required>
                                    <label class="form-check-label" for="terminos">
                                        Acepto los términos y condiciones y la política de privacidad <span class="required-asterisk">*</span>
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary-custom btn-lg">
                                    <i class="fas fa-check-circle me-2"></i>Confirmar Reserva
                                </button>
                                <a href="reservas_online.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Volver a buscar habitaciones
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            // Validación básica
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor, complete todos los campos obligatorios');
                return;
            }
            
            // Mostrar loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';
            submitBtn.disabled = true;
        });

        // Validación de email
        document.querySelector('input[name="email"]').addEventListener('blur', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    </script>
</body>
</html>
