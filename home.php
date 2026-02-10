<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Management - Bienvenidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        .feature-card {
            transition: transform 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .feature-card:hover {
            transform: translateY(-10px);
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .cta-button {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            padding: 12px 30px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .cta-button:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand text-primary" href="#home">
                <i class="fas fa-hotel me-2"></i>Hotel Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#habitaciones">Habitaciones</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#servicios">Servicios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#nosotros">Nosotros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contacto">Contacto</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a href="reservas_online.php" class="btn btn-outline-primary me-2">
                            <i class="fas fa-calendar-plus me-1"></i>Reservar
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a href="portal_cliente.php" class="btn btn-outline-primary me-2">
                            <i class="fas fa-user me-1"></i>Mi Portal
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-1"></i>Acceso Administrativo
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Bienvenidos a Hotel Management</h1>
            <p class="lead mb-5">Experimenta el lujo y la comodidad en nuestro hotel de primera categoría</p>
            <div class="d-flex gap-3 justify-content-center">
                <button class="btn btn-light btn-lg cta-button me-2" onclick="window.location.href='#habitaciones'">
                    <i class="fas fa-search me-2"></i>Ver Habitaciones
                </button>
                <button class="btn btn-outline-light btn-lg" onclick="window.location.href='reservas_online.php'">
                    <i class="fas fa-calendar-plus me-2"></i>Reservar Ahora
                </button>
                <button class="btn btn-outline-light btn-lg" onclick="window.location.href='#contacto'">
                    <i class="fas fa-phone me-2"></i>Contactar
                </button>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="fw-bold">¿Por qué elegirnos?</h2>
                    <p class="text-muted">Descubre nuestras características exclusivas</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card h-100 p-4">
                        <div class="text-center mb-3">
                            <i class="fas fa-wifi fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title text-center">WiFi Gratis</h5>
                        <p class="card-text text-center text-muted">Conexión a internet de alta velocidad en todas las habitaciones</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100 p-4">
                        <div class="text-center mb-3">
                            <i class="fas fa-swimming-pool fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title text-center">Piscina</h5>
                        <p class="card-text text-center text-muted">Disfruta de nuestra piscina climatizada todo el año</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100 p-4">
                        <div class="text-center mb-3">
                            <i class="fas fa-utensils fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title text-center">Restaurante</h5>
                        <p class="card-text text-center text-muted">Cocina gourmet con chefs de renombre internacional</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="servicios" class="py-5 bg-light">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="fw-bold">Nuestros Servicios</h2>
                    <p class="text-muted">Todo lo que necesitas para una estancia perfecta</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center p-4">
                            <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle p-3 mx-auto mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-spa fs-1"></i>
                            </div>
                            <h5 class="card-title">Spa & Wellness</h5>
                            <p class="card-text text-muted">Relájate en nuestro spa con tratamientos de lujo</p>
                            <ul class="list-unstyled text-muted small">
                                <li><i class="fas fa-check text-success me-2"></i> Masajes relajantes</li>
                                <li><i class="fas fa-check text-success me-2"></i> Tratamientos faciales</li>
                                <li><i class="fas fa-check text-success me-2"></i> Sauna y jacuzzi</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center p-4">
                            <div class="icon-box bg-success bg-opacity-10 text-success rounded-circle p-3 mx-auto mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-dumbbell fs-1"></i>
                            </div>
                            <h5 class="card-title">Gimnasio</h5>
                            <p class="card-text text-muted">Mantén tu rutina de ejercicio en nuestro gimnasio moderno</p>
                            <ul class="list-unstyled text-muted small">
                                <li><i class="fas fa-check text-success me-2"></i> Equipamiento moderno</li>
                                <li><i class="fas fa-check text-success me-2"></i> Entrenadores personales</li>
                                <li><i class="fas fa-check text-success me-2"></i> Clases grupales</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center p-4">
                            <div class="icon-box bg-info bg-opacity-10 text-info rounded-circle p-3 mx-auto mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-concierge-bell fs-1"></i>
                            </div>
                            <h5 class="card-title">Servicio de Habitación</h5>
                            <p class="card-text text-muted">Atención 24/7 para todas tus necesidades</p>
                            <ul class="list-unstyled text-muted small">
                                <li><i class="fas fa-check text-success me-2"></i> Menú completo 24h</li>
                                <li><i class="fas fa-check text-success me-2"></i> Limpieza diaria</li>
                                <li><i class="fas fa-check text-success me-2"></i> Lavandería</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center p-4">
                            <div class="icon-box bg-warning bg-opacity-10 text-warning rounded-circle p-3 mx-auto mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-car fs-1"></i>
                            </div>
                            <h5 class="card-title">Parking & Transporte</h5>
                            <p class="card-text text-muted">Estacionamiento seguro y servicios de transporte</p>
                            <ul class="list-unstyled text-muted small">
                                <li><i class="fas fa-check text-success me-2"></i> Parking gratuito</li>
                                <li><i class="fas fa-check text-success me-2"></i> Servicio al aeropuerto</li>
                                <li><i class="fas fa-check text-success me-2"></i> Alquiler de coches</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center p-4">
                            <div class="icon-box bg-danger bg-opacity-10 text-danger rounded-circle p-3 mx-auto mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-briefcase fs-1"></i>
                            </div>
                            <h5 class="card-title">Centro de Negocios</h5>
                            <p class="card-text text-muted">Espacios equipados para reuniones y trabajo</p>
                            <ul class="list-unstyled text-muted small">
                                <li><i class="fas fa-check text-success me-2"></i> Salas de reuniones</li>
                                <li><i class="fas fa-check text-success me-2"></i> WiFi alta velocidad</li>
                                <li><i class="fas fa-check text-success me-2"></i> Impresión y copias</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center p-4">
                            <div class="icon-box bg-secondary bg-opacity-10 text-secondary rounded-circle p-3 mx-auto mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-child fs-1"></i>
                            </div>
                            <h5 class="card-title">Club Infantil</h5>
                            <p class="card-text text-muted">Actividades supervisadas para los más pequeños</p>
                            <ul class="list-unstyled text-muted small">
                                <li><i class="fas fa-check text-success me-2"></i> Juegos educativos</li>
                                <li><i class="fas fa-check text-success me-2"></i> Área de juegos</li>
                                <li><i class="fas fa-check text-success me-2"></i> Cuidado profesional</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Rooms Preview -->
    <section id="habitaciones" class="py-5 bg-light">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="fw-bold">Nuestras Habitaciones</h2>
                    <p class="text-muted">Espacios diseñados para tu confort</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400" class="card-img-top" alt="Habitación Estándar">
                        <div class="card-body">
                            <h5 class="card-title">Habitación Estándar</h5>
                            <p class="card-text">Comodidad y funcionalidad para una estancia agradable</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 text-primary">$89/noche</span>
                                <button class="btn btn-primary btn-sm" onclick="verDetalles('estandar')">Ver más</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <img src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=400" class="card-img-top" alt="Suite Junior">
                        <div class="card-body">
                            <h5 class="card-title">Suite Junior</h5>
                            <p class="card-text">Espacio adicional y lujo para tu comodidad</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 text-primary">$149/noche</span>
                                <button class="btn btn-primary btn-sm" onclick="verDetalles('junior')">Ver más</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <img src="https://images.unsplash.com/photo-1590490360237-c33b5c792dc0?w=400" class="card-img-top" alt="Suite Presidencial">
                        <div class="card-body">
                            <h5 class="card-title">Suite Presidencial</h5>
                            <p class="card-text">El máximo lujo y exclusividad para huéspedes especiales</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 text-primary">$299/noche</span>
                                <button class="btn btn-primary btn-sm" onclick="verDetalles('presidencial')">Ver más</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contacto" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h3 class="fw-bold mb-4">Contacto</h3>
                    <div class="mb-3">
                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                        <strong>Dirección:</strong> Av. Principal 123, Ciudad
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-phone text-primary me-2"></i>
                        <strong>Teléfono:</strong> +1 234 567 890
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        <strong>Email:</strong> info@hotelmanagement.com
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-clock text-primary me-2"></i>
                        <strong>Horario:</strong> 24/7 Recepción disponible
                    </div>
                </div>
                <div class="col-md-6">
                    <h3 class="fw-bold mb-4">Reserva Rápida</h3>
                    <form onsubmit="enviarReserva(event)">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mensaje</label>
                            <textarea class="form-control" rows="3" placeholder="Cuéntanos sobre tu reserva..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-paper-plane me-2"></i>Enviar Solicitud
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Hotel Management</h5>
                    <p class="text-muted">Tu experiencia hotelera de lujo</p>
                </div>
                <div class="col-md-6 text-end">
                    <div class="mb-2">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin fa-lg"></i></a>
                    </div>
                    <p class="text-muted small mb-0">© 2024 Hotel Management. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function verDetalles(tipo) {
            alert('Redirigiendo a detalles de habitación ' + tipo + '. Esta página estará disponible pronto.');
        }

        function enviarReserva(event) {
            event.preventDefault();
            alert('Gracias por tu solicitud. Nos contactaremos pronto.');
            event.target.reset();
        }

        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>
