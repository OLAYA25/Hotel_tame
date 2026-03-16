<?php
require_once __DIR__ . '/config/bootstrap.php';

// Verificar si hay un ID de reserva
if (!isset($_GET['reservation_id'])) {
    header('Location: reservas_online.php');
    exit;
}

$reservationId = $_GET['reservation_id'];

// Obtener información de la reserva
require_once __DIR__ . '/backend/src/Database/Database.php';

use Database\Database;

try {
    $db = Database::getConnection();
    $sql = "SELECT r.*, h.numero as habitacion_numero, h.tipo as habitacion_tipo,
                   c.nombre, c.apellido, c.email, c.telefono
            FROM reservas r
            LEFT JOIN habitaciones h ON r.habitacion_id = h.id
            LEFT JOIN reserva_clientes rc ON r.id = rc.reserva_id AND rc.rol = 'titular'
            LEFT JOIN clientes c ON rc.cliente_id = c.id
            WHERE r.id = :reservation_id AND r.deleted_at IS NULL";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([':reservation_id' => $reservationId]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        throw new Exception('Reserva no encontrada');
    }
    
    // Calcular noches
    $checkinDate = new DateTime($reservation['fecha_entrada']);
    $checkoutDate = new DateTime($reservation['fecha_salida']);
    $nights = $checkinDate->diff($checkoutDate)->days;
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Reserva - Hotel Tame</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --accent-color: #f39c12;
            --success-color: #27ae60;
            --light-bg: #ecf0f1;
            --dark-text: #2c3e50;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: var(--dark-text);
        }

        .success-header {
            background: linear-gradient(rgba(39, 174, 96, 0.9), rgba(46, 204, 113, 0.9));
            color: white;
            padding: 60px 0;
            text-align: center;
            margin-bottom: 40px;
        }

        .success-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            animation: checkmark 0.5s ease-in-out;
        }

        @keyframes checkmark {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        .confirmation-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .confirmation-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 25px;
            text-align: center;
        }

        .card-body-custom {
            padding: 30px;
        }

        .reservation-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .detail-row:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--success-color);
        }

        .detail-label {
            font-weight: 600;
            color: #666;
        }

        .detail-value {
            color: var(--dark-text);
        }

        .qr-code {
            width: 150px;
            height: 150px;
            background: #f0f0f0;
            border: 2px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            border-radius: 10px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-custom {
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary-custom {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary-custom:hover {
            background: #34495e;
            transform: translateY(-2px);
            color: white;
        }

        .btn-success-custom {
            background: var(--success-color);
            color: white;
        }

        .btn-success-custom:hover {
            background: #229954;
            transform: translateY(-2px);
            color: white;
        }

        .btn-outline-custom {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline-custom:hover {
            background: var(--primary-color);
            color: white;
        }

        .info-box {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e0e0e0;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -34px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--primary-color);
            border: 2px solid white;
        }

        .timeline-item.completed::before {
            background: var(--success-color);
        }

        @media (max-width: 768px) {
            .confirmation-container {
                padding: 0 10px;
            }
            
            .card-body-custom {
                padding: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-custom {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Success Header -->
    <div class="success-header">
        <div class="confirmation-container">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1 class="display-5 fw-bold mb-3">¡Reserva Confirmada!</h1>
            <p class="lead">Tu reserva ha sido procesada exitosamente</p>
        </div>
    </div>

    <div class="confirmation-container">
        <!-- Confirmation Card -->
        <div class="confirmation-card">
            <div class="card-header-custom">
                <h3 class="mb-2">Confirmación de Reserva #<?= $reservationId ?></h3>
                <p class="mb-0">Gracias por elegir Hotel Tame</p>
            </div>
            
            <div class="card-body-custom">
                <div class="row">
                    <div class="col-md-8">
                        <!-- Reservation Details -->
                        <div class="reservation-details">
                            <h5 class="mb-3">Detalles de la Reserva</h5>
                            
                            <div class="detail-row">
                                <span class="detail-label">Habitación</span>
                                <span class="detail-value"><?= $reservation['habitacion_numero'] ?> - <?= ucfirst($reservation['habitacion_tipo']) ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Huesped Principal</span>
                                <span class="detail-value"><?= $reservation['nombre'] ?> <?= $reservation['apellido'] ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Email</span>
                                <span class="detail-value"><?= $reservation['email'] ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Teléfono</span>
                                <span class="detail-value"><?= $reservation['telefono'] ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Fecha de Entrada</span>
                                <span class="detail-value"><?= date('d/m/Y', strtotime($reservation['fecha_entrada'])) ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Fecha de Salida</span>
                                <span class="detail-value"><?= date('d/m/Y', strtotime($reservation['fecha_salida'])) ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Noches</span>
                                <span class="detail-value"><?= $nights ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Total Pagado</span>
                                <span class="detail-value">$<?= number_format($reservation['precio_total'], 0, ',', '.') ?></span>
                            </div>
                        </div>

                        <!-- Important Information -->
                        <div class="info-box">
                            <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Información Importante</h5>
                            <ul class="mb-0">
                                <li><strong>Check-in:</strong> 3:00 PM | <strong>Check-out:</strong> 12:00 PM</li>
                                <li>Presenta este código de confirmación al llegar al hotel</li>
                                <li>Cancelación gratuita hasta 48 horas antes del check-in</li>
                                <li>Hemos enviado un email de confirmación a <?= $reservation['email'] ?></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <!-- QR Code -->
                        <div class="text-center mb-4">
                            <h5 class="mb-3">Código de Reserva</h5>
                            <div class="qr-code">
                                <div>
                                    <i class="fas fa-qrcode fa-3x text-muted"></i>
                                </div>
                            </div>
                            <p class="mt-2 mb-0"><strong><?= $reservationId ?></strong></p>
                                    <small class="text-muted">Código de confirmación</small>
                        </div>

                        <!-- Next Steps Timeline -->
                        <div class="timeline">
                            <h5 class="mb-3">Próximos Pasos</h5>
                            
                            <div class="timeline-item completed">
                                <h6>Reserva Confirmada</h6>
                                <small class="text-muted">Ahora mismo</small>
                            </div>
                            
                            <div class="timeline-item">
                                <h6>Recibirás Email de Confirmación</h6>
                                <small class="text-muted">En los próximos minutos</small>
                            </div>
                            
                            <div class="timeline-item">
                                <h6>Check-in</h6>
                                <small class="text-muted"><?= date('d/m/Y', strtotime($reservation['fecha_entrada'])) ?> a las 3:00 PM</small>
                            </div>
                            
                            <div class="timeline-item">
                                <h6>Check-out</h6>
                                <small class="text-muted"><?= date('d/m/Y', strtotime($reservation['fecha_salida'])) ?> a las 12:00 PM</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons mt-4">
                    <a href="#" class="btn btn-success-custom" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Imprimir Confirmación
                    </a>
                    <a href="mailto:?subject=Confirmación de Reserva Hotel Tame&body=Mi reserva #%<?= $reservationId ?> ha sido confirmada. Ver detalles en: <?= $_SERVER['HTTP_HOST'] ?>/reserva_confirmacion_simple.php?reservation_id=<?= $reservationId ?>" class="btn btn-primary-custom">
                        <i class="fas fa-envelope me-2"></i>Enviar por Email
                    </a>
                    <a href="reservas_online.php" class="btn btn-outline-custom">
                        <i class="fas fa-calendar me-2"></i>Hacer Otra Reserva
                    </a>
                    <a href="index.php" class="btn btn-outline-custom">
                        <i class="fas fa-home me-2"></i>Volver al Inicio
                    </a>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="confirmation-card">
            <div class="card-body-custom">
                <h4 class="mb-4">Servicios Incluidos</h4>
                
                <div class="row">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="text-center">
                            <i class="fas fa-wifi fa-2x text-primary mb-2"></i>
                            <h6>WiFi Gratis</h6>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="text-center">
                            <i class="fas fa-tv fa-2x text-primary mb-2"></i>
                            <h6>TV Cable</h6>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="text-center">
                            <i class="fas fa-snowflake fa-2x text-primary mb-2"></i>
                            <h6>Aire Acondicionado</h6>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="text-center">
                            <i class="fas fa-soap fa-2x text-primary mb-2"></i>
                            <h6>Artículos de Aseo</h6>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="mb-3">Información de Contacto</h5>
                <div class="row">
                    <div class="col-md-4">
                        <p><i class="fas fa-phone me-2"></i> +57 1 2345678</p>
                    </div>
                    <div class="col-md-4">
                        <p><i class="fas fa-envelope me-2"></i> info@hoteltame.com</p>
                    </div>
                    <div class="col-md-4">
                        <p><i class="fas fa-map-marker-alt me-2"></i> Bogotá, Colombia</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Hotel Tame</h5>
                    <p>Tu hogar lejos de casa</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; 2024 Hotel Tame. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-scroll to top on load
        window.addEventListener('load', function() {
            window.scrollTo(0, 0);
        });

        // Print functionality
        function printConfirmation() {
            window.print();
        }

        // Share functionality
        function shareReservation() {
            if (navigator.share) {
                navigator.share({
                    title: 'Confirmación de Reserva - Hotel Tame',
                    text: 'Mi reserva #' + <?= $reservationId ?> ha sido confirmada',
                    url: window.location.href
                });
            } else {
                // Fallback for browsers that don't support Web Share API
                const shareUrl = 'https://wa.me/?text=' + encodeURIComponent('Mi reserva #' + <?= $reservationId ?> en Hotel Tame ha sido confirmada. ' + window.location.href);
                window.open(shareUrl, '_blank');
            }
        }

        // Add to calendar functionality
        function addToCalendar() {
            const startDate = '<?= date('Ymd', strtotime($reservation['fecha_entrada'])) ?>';
            const endDate = '<?= date('Ymd', strtotime($reservation['fecha_salida'])) ?>';
            const startTime = '150000'; // 3:00 PM
            const endTime = '120000';   // 12:00 PM
            
            const calendarUrl = `https://calendar.google.com/calendar/render?action=TEMPLATE&dates=${startDate}T${startTime}/${endDate}T${endTime}&text=Reserva+Hotel+Tame&details=Reserva+#+<?= $reservationId ?>+-+Habitación+<?= $reservation['habitacion_numero'] ?>&location=Hotel+Tame`;
            
            window.open(calendarUrl, '_blank');
        }

        // Countdown to check-in (optional enhancement)
        function updateCountdown() {
            const checkinDate = new Date('<?= $reservation['fecha_entrada'] ?>T15:00:00');
            const now = new Date();
            const diff = checkinDate - now;
            
            if (diff > 0) {
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                
                // Update countdown display if element exists
                const countdownElement = document.getElementById('countdown');
                if (countdownElement) {
                    countdownElement.textContent = `${days} días, ${hours} horas para tu check-in`;
                }
            }
        }

        // Update countdown every minute
        setInterval(updateCountdown, 60000);
        updateCountdown();
    </script>
</body>
</html>
