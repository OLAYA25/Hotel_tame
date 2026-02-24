<?php
require_once 'config/database.php';

// Obtener datos del hotel para mostrar en la página
$habitaciones_disponibles = 0;
$total_habitaciones = 0;
$proximas_reservas = [];
$habitaciones_destacadas = [];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Obtener estadísticas de habitaciones
    $stmt = $db->query("SELECT COUNT(*) as total FROM habitaciones WHERE deleted_at IS NULL");
    $total_habitaciones = $stmt->fetch()['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as disponibles FROM habitaciones WHERE estado = 'disponible' AND deleted_at IS NULL");
    $habitaciones_disponibles = $stmt->fetch()['disponibles'];
    
    // Obtener habitaciones destacadas para mostrar
    $stmt = $db->query("SELECT id, numero, tipo, precio_noche as precio, capacidad, descripcion, imagen 
                        FROM habitaciones 
                        WHERE estado = 'disponible' AND deleted_at IS NULL 
                        ORDER BY precio_noche ASC LIMIT 6");
    $habitaciones_destacadas = $stmt->fetchAll();
    
    // Obtener próximas reservas confirmadas
    $stmt = $db->query("SELECT r.*, h.numero, h.tipo, c.nombre as cliente_nombre 
                        FROM reservas r 
                        JOIN habitaciones h ON r.habitacion_id = h.id 
                        JOIN clientes c ON r.cliente_id = c.id 
                        WHERE r.estado = 'confirmada' AND r.fecha_entrada >= CURDATE() 
                        ORDER BY r.fecha_entrada ASC LIMIT 5");
    $proximas_reservas = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Error en home.php: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Tame - Bienvenidos a tu Hogar lejos de Casa</title>
    <meta name="description" content="Hotel Tame ofrece comodidad, lujo y servicio excepcional. Descubre nuestras habitaciones y servicios de primera clase.">
    <meta name="keywords" content="hotel, alojamiento, lujo, comodidad, reservas, habitaciones">
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
                        <a class="nav-link active" href="home.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#habitaciones">Habitaciones</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#servicios">Servicios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#galeria">Galería</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contacto">Contacto</a>
                    </li>
                    <li class="nav-item ms-3">
                        <a href="reservas_online.php" class="btn btn-primary-custom btn-sm">Reservar Ahora</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a href="portal_cliente.php" class="btn btn-outline-custom btn-sm">Mi Portal</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Bienvenidos a Hotel Tame</h1>
                <p class="hero-subtitle">Experimenta el lujo, la comodidad y el servicio excepcional en nuestro hotel de primera categoría</p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="reservas_online.php" class="btn btn-light btn-lg px-4">
                        <i class="fas fa-calendar-check me-2"></i>Reservar Ahora
                    </a>
                    <a href="#habitaciones" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-bed me-2"></i>Ver Habitaciones
                    </a>
                </div>
                
                <!-- Estadísticas en vivo -->
                <div class="row mt-5 pt-5">
                    <div class="col-md-4 mb-3">
                        <div class="text-white">
                            <h3 class="fw-bold"><?= $total_habitaciones ?></h3>
                            <p class="mb-0">Habitaciones Totales</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-white">
                            <h3 class="fw-bold"><?= $habitaciones_disponibles ?></h3>
                            <p class="mb-0">Disponibles Hoy</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-white">
                            <h3 class="fw-bold"><?= count($proximas_reservas) ?></h3>
                            <p class="mb-0">Próximas Reservas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Booking -->
    <section class="section">
        <div class="container">
            <div class="booking-form">
                <h3 class="text-center mb-4 fw-bold">Reserva Rápida</h3>
                <form action="reservas_online.php" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label-custom">Check-in</label>
                        <input type="date" name="fecha_entrada" class="form-control form-control-custom" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-custom">Check-out</label>
                        <input type="date" name="fecha_salida" class="form-control form-control-custom" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-custom">Huéspedes</label>
                        <select name="huespedes" class="form-control form-control-custom">
                            <option value="1">1 Huésped</option>
                            <option value="2">2 Huéspedes</option>
                            <option value="3">3 Huéspedes</option>
                            <option value="4">4 Huéspedes</option>
                            <option value="5">5+ Huéspedes</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="fas fa-search me-2"></i>Buscar Disponibilidad
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="section bg-light">
        <div class="container">
            <h2 class="section-title">¿Por qué elegir Hotel Tame?</h2>
            <p class="section-subtitle">Descubre nuestras características exclusivas y servicios de lujo</p>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="text-center mb-3">
                            <i class="fas fa-wifi fa-3x text-primary"></i>
                        </div>
                        <h5 class="feature-title">WiFi de Alta Velocidad</h5>
                        <p class="feature-description">Conexión a internet gratuita y de alta velocidad en todas las habitaciones y áreas comunes</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="text-center mb-3">
                            <i class="fas fa-swimming-pool fa-3x text-primary"></i>
                        </div>
                        <h5 class="feature-title">Piscina Climatizada</h5>
                        <p class="feature-description">Disfruta de nuestra piscina exterior climatizada todo el año con bar y servicio de toallas</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="text-center mb-3">
                            <i class="fas fa-utensils fa-3x text-primary"></i>
                        </div>
                        <h5 class="feature-title">Restaurante Gourmet</h5>
                        <p class="feature-description">Cocina internacional con chefs de renombre, desayuno buffet y room service 24/7</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="text-center mb-3">
                            <i class="fas fa-spa fa-3x text-primary"></i>
                        </div>
                        <h5 class="feature-title">Spa & Wellness</h5>
                        <p class="feature-description">Relájate en nuestro spa completo con masajes, tratamientos faciales y sauna</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="text-center mb-3">
                            <i class="fas fa-dumbbell fa-3x text-primary"></i>
                        </div>
                        <h5 class="feature-title">Gimnasio Moderno</h5>
                        <p class="feature-description">Equipo de última generación, clases grupales y entrenadores personales disponibles</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="text-center mb-3">
                            <i class="fas fa-car fa-3x text-primary"></i>
                        </div>
                        <h5 class="feature-title">Parking Gratuito</h5>
                        <p class="feature-description">Estacionamiento seguro gratuito y servicio de transporte al aeropuerto disponible</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Rooms Section -->
    <section id="habitaciones" class="section">
        <div class="container">
            <h2 class="section-title">Nuestras Habitaciones</h2>
            <p class="section-subtitle">Espacios elegantemente diseñados para tu máxima comodidad</p>
            
            <?php if (!empty($habitaciones_destacadas)): ?>
                <div class="row g-4">
                    <?php foreach ($habitaciones_destacadas as $habitacion): ?>
                        <div class="col-md-4">
                            <div class="room-card">
                                <?php if ($habitacion['imagen']): ?>
                                    <img src="assets/images/habitaciones/<?= $habitacion['imagen'] ?>" 
                                         class="room-image" 
                                         alt="Habitación <?= htmlspecialchars($habitacion['tipo']) ?>">
                                <?php else: ?>
                                    <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400&h=300&fit=crop" 
                                         class="room-image" 
                                         alt="Habitación <?= htmlspecialchars($habitacion['tipo']) ?>">
                                <?php endif; ?>
                                
                                <div class="room-card-body">
                                    <h5 class="room-title">Habitación <?= htmlspecialchars($habitacion['tipo']) ?> - <?= htmlspecialchars($habitacion['numero']) ?></h5>
                                    <p class="room-description">
                                        <?= htmlspecialchars(substr($habitacion['descripcion'] ?? 'Habitación confortable y elegante', 0, 100)) ?>...
                                    </p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <i class="fas fa-users text-muted me-1"></i>
                                            <small class="text-muted"><?= $habitacion['capacidad'] ?> personas</small>
                                        </div>
                                        <div class="price-tag">
                                            $<?= number_format($habitacion['precio'], 0, ',', '.') ?>/noche
                                        </div>
                                    </div>
                                    
                                    <a href="reservas_online.php?habitacion_id=<?= $habitacion['id'] ?>" 
                                       class="btn btn-primary-custom w-100">
                                        <i class="fas fa-calendar-check me-2"></i>Reservar Ahora
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-bed fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No hay habitaciones disponibles en este momento.</p>
                </div>
            <?php endif; ?>
            
            <div class="text-center mt-5">
                <a href="reservas_online.php" class="btn btn-outline-custom btn-lg">
                    <i class="fas fa-th-large me-2"></i>Ver Todas las Habitaciones
                </a>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="servicios" class="section bg-light">
        <div class="container">
            <h2 class="section-title">Nuestros Servicios Premium</h2>
            <p class="section-subtitle">Todo lo que necesitas para una estancia perfecta</p>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="d-flex align-items-start mb-4">
                        <div class="flex-shrink-0">
                            <div class="bg-primary text-white rounded-circle p-3 me-3">
                                <i class="fas fa-concierge-bell"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold">Servicio de Habitación 24/7</h5>
                            <p class="text-muted">Menú completo disponible las 24 horas, desde desayunos hasta cenas gourmet</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-start mb-4">
                        <div class="flex-shrink-0">
                            <div class="bg-success text-white rounded-circle p-3 me-3">
                                <i class="fas fa-briefcase"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold">Centro de Negocios</h5>
                            <p class="text-muted">Salas de reuniones equipadas, WiFi de alta velocidad y servicios de secretaría</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-start mb-4">
                        <div class="flex-shrink-0">
                            <div class="bg-info text-white rounded-circle p-3 me-3">
                                <i class="fas fa-child"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold">Club Infantil</h5>
                            <p class="text-muted">Actividades supervisadas para niños, área de juegos y cuidado profesional</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-start mb-4">
                        <div class="flex-shrink-0">
                            <div class="bg-warning text-white rounded-circle p-3 me-3">
                                <i class="fas fa-plane"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold">Servicios de Transporte</h5>
                            <p class="text-muted">Traslados al aeropuerto, tours turísticos y alquiler de vehículos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section id="galeria" class="section">
        <div class="container">
            <h2 class="section-title">Galería del Hotel</h2>
            <p class="section-subtitle">Explora nuestras instalaciones y espacios</p>
            
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="room-card">
                        <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400&h=300&fit=crop" 
                             class="room-image" alt="Habitación Deluxe">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="room-card">
                        <img src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=400&h=300&fit=crop" 
                             class="room-image" alt="Suite Presidencial">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="room-card">
                        <img src="https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=400&h=300&fit=crop" 
                             class="room-image" alt="Restaurante">
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="room-card">
                        <img src="https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800&h=400&fit=crop" 
                             class="room-image" alt="Piscina">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="room-card">
                        <img src="https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=400&h=300&fit=crop" 
                             class="room-image" alt="Spa">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contacto" class="section bg-light">
        <div class="container">
            <h2 class="section-title">Contacto</h2>
            <p class="section-subtitle">Estamos aquí para ayudarte con cualquier consulta</p>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="contact-info">
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">
                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                Ubicación
                            </h5>
                            <p class="text-muted">Av. Principal #123, Centro Histórico<br>Ciudad, País 12345</p>
                        </div>
                        
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">
                                <i class="fas fa-phone text-primary me-2"></i>
                                Teléfono
                            </h5>
                            <p class="text-muted">
                                +1 234 567 8900<br>
                                +1 234 567 8901 (WhatsApp)
                            </p>
                        </div>
                        
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                Email
                            </h5>
                            <p class="text-muted">
                                info@hoteltame.com<br>
                                reservas@hoteltame.com
                            </p>
                        </div>
                        
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">
                                <i class="fas fa-clock text-primary me-2"></i>
                                Horario
                            </h5>
                            <p class="text-muted">
                                Recepción: 24/7<br>
                                Restaurante: 6:00 AM - 11:00 PM<br>
                                Spa: 8:00 AM - 8:00 PM
                            </p>
                        </div>
                        
                        <div class="d-flex gap-3">
                            <a href="#" class="text-primary fs-4"><i class="fab fa-facebook"></i></a>
                            <a href="#" class="text-primary fs-4"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="text-primary fs-4"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-primary fs-4"><i class="fab fa-linkedin"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="contact-form">
                        <h5 class="fw-bold mb-4">Envíanos un Mensaje</h5>
                        <form onsubmit="enviarContacto(event)">
                            <div class="mb-3">
                                <label class="form-label-custom">Nombre Completo</label>
                                <input type="text" class="form-control form-control-custom" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-custom">Email</label>
                                <input type="email" class="form-control form-control-custom" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-custom">Teléfono</label>
                                <input type="tel" class="form-control form-control-custom">
                            </div>
                            <div class="mb-3">
                                <label class="form-label-custom">Mensaje</label>
                                <textarea class="form-control form-control-custom" rows="4" 
                                          placeholder="Cuéntanos sobre tu consulta o reserva..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary-custom w-100">
                                <i class="fas fa-paper-plane me-2"></i>Enviar Mensaje
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-hotel me-2"></i>Hotel Tame
                    </h5>
                    <p class="text-muted">Tu experiencia hotelera de lujo y confort. Donde cada detalle está pensado para tu bienestar.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white fs-5"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white fs-5"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white fs-5"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white fs-5"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3">Enlaces Rápidos</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="home.php" class="text-muted text-decoration-none">Inicio</a></li>
                        <li class="mb-2"><a href="#habitaciones" class="text-muted text-decoration-none">Habitaciones</a></li>
                        <li class="mb-2"><a href="#servicios" class="text-muted text-decoration-none">Servicios</a></li>
                        <li class="mb-2"><a href="reservas_online.php" class="text-muted text-decoration-none">Reservas</a></li>
                        <li class="mb-2"><a href="portal_cliente.php" class="text-muted text-decoration-none">Portal Cliente</a></li>
                    </ul>
                </div>
                
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3">Newsletter</h5>
                    <p class="text-muted mb-3">Suscríbete para recibir ofertas exclusivas y novedades</p>
                    <form onsubmit="suscribirNewsletter(event)" class="d-flex">
                        <input type="email" class="form-control me-2" placeholder="Tu email" required>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <hr class="border-secondary my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">© 2024 Hotel Tame. Todos los derechos reservados.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-muted me-3">Política de Privacidad</a>
                    <a href="#" class="text-muted me-3">Términos y Condiciones</a>
                    <a href="#" class="text-muted">Política de Cancelación</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" class="btn btn-primary position-fixed bottom-0 end-0 m-3" style="display: none; z-index: 1000;">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-web');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
            
            // Back to top button
            const backToTop = document.getElementById('backToTop');
            if (window.scrollY > 300) {
                backToTop.style.display = 'block';
            } else {
                backToTop.style.display = 'none';
            }
        });

        // Back to top functionality
        document.getElementById('backToTop').addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const offset = 80; // Navbar height
                    const targetPosition = target.offsetTop - offset;
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Contact form
        function enviarContacto(event) {
            event.preventDefault();
            alert('Gracias por tu mensaje. Nos contactaremos contigo pronto.');
            event.target.reset();
        }

        // Newsletter subscription
        function suscribirNewsletter(event) {
            event.preventDefault();
            alert('¡Gracias por suscribirte a nuestro newsletter!');
            event.target.reset();
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
                checkInInput.value = today;
            }
            
            if (checkOutInput) {
                checkOutInput.min = tomorrowStr;
                checkOutInput.value = tomorrowStr;
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
