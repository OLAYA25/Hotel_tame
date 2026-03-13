<?php
// Versión simplificada de reservas_online.php sin dependencias complejas

// Datos de ejemplo para habitaciones
$sampleRooms = [
    ['id' => 1, 'numero' => '101', 'tipo' => 'estandar', 'capacidad' => 2, 'precio_noche' => 80000, 'descripcion' => 'Habitación estándar confortable y elegante'],
    ['id' => 2, 'numero' => '201', 'tipo' => 'suite', 'capacidad' => 4, 'precio_noche' => 150000, 'descripcion' => 'Suite espaciosa con vista a la ciudad'],
    ['id' => 3, 'numero' => '102', 'tipo' => 'deluxe', 'capacidad' => 2, 'precio_noche' => 120000, 'descripcion' => 'Habitación deluxe con amenities premium'],
    ['id' => 4, 'numero' => '301', 'tipo' => 'familiar', 'capacidad' => 6, 'precio_noche' => 200000, 'descripcion' => 'Habitación familiar ideal para grupos'],
    ['id' => 5, 'numero' => '103', 'tipo' => 'estandar', 'capacidad' => 2, 'precio_noche' => 85000, 'descripcion' => 'Habitación estándar renovada'],
    ['id' => 6, 'numero' => '202', 'tipo' => 'suite', 'capacidad' => 4, 'precio_noche' => 180000, 'descripcion' => 'Suite ejecutiva con área de trabajo']
];

// Procesar búsqueda
$searchResults = [];
$searchPerformed = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['checkin']) && isset($_GET['checkout'])) {
    try {
        $searchParams = [
            'checkin' => $_GET['checkin'],
            'checkout' => $_GET['checkout'],
            'adults' => $_GET['adults'] ?? 2,
            'children' => $_GET['children'] ?? 0,
            'room_type' => $_GET['room_type'] ?? null,
            'max_price' => $_GET['max_price'] ?? null
        ];
        
        // Simular búsqueda - filtrar habitaciones según criterios
        $searchResults = [];
        foreach ($sampleRooms as $room) {
            // Filtrar por tipo si se especifica
            if ($searchParams['room_type'] && $room['tipo'] !== $searchParams['room_type']) {
                continue;
            }
            
            // Filtrar por precio máximo si se especifica
            if ($searchParams['max_price'] && $room['precio_noche'] > $searchParams['max_price']) {
                continue;
            }
            
            // Calcular noches y precio total
            $checkinDate = new DateTime($searchParams['checkin']);
            $checkoutDate = new DateTime($searchParams['checkout']);
            $nights = $checkinDate->diff($checkoutDate)->days;
            
            // Agregar información adicional
            $room['habitacion_id'] = $room['id'];
            $room['habitacion_numero'] = $room['numero'];
            $room['habitacion_tipo'] = $room['tipo'];
            $room['nights'] = $nights;
            $room['price_per_night'] = $room['precio_noche'];
            $room['total_price'] = $room['precio_noche'] * $nights;
            
            $searchResults[] = $room;
        }
        
        $searchPerformed = true;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Tipos de habitación
$roomTypes = ['estandar', 'suite', 'deluxe', 'familiar'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas Online - Hotel Tame</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
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

        .hero-section {
            background: linear-gradient(rgba(44, 62, 80, 0.9), rgba(52, 73, 94, 0.9)), url('https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0 60px;
            margin-bottom: 40px;
        }

        .search-form {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            margin-top: -30px;
            position: relative;
            z-index: 10;
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

        .room-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }

        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .room-image {
            height: 200px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }

        .room-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--accent-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .price-tag {
            font-size: 24px;
            font-weight: 700;
            color: var(--secondary-color);
        }

        .price-tag small {
            font-size: 14px;
            color: #666;
            font-weight: 400;
        }

        .amenity-icon {
            width: 30px;
            height: 30px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 14px;
            color: var(--primary-color);
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 30px;
            color: var(--dark-text);
        }

        .search-stats {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .no-results i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 60px 0 40px;
            }
            
            .search-form {
                padding: 20px;
                margin-top: -20px;
            }
            
            .section-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold mb-4">Reservas Online</h1>
                    <p class="lead mb-4">Descubre nuestras habitaciones y reserva tu estancia perfecta en Hotel Tame</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Search Form -->
    <div class="container">
        <div class="search-form">
            <h3 class="text-center mb-4">Buscar Disponibilidad</h3>
            <form id="searchForm" method="GET" action="/Hotel_tame/reservas-online">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Fecha de Entrada</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                            <input type="text" class="form-control" id="checkin" name="checkin" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha de Salida</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                            <input type="text" class="form-control" id="checkout" name="checkout" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Adultos</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <select class="form-select" name="adults">
                                <option value="1">1</option>
                                <option value="2" selected>2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Niños</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-child"></i></span>
                            <select class="form-select" name="children">
                                <option value="0" selected>0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tipo</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-bed"></i></span>
                            <select class="form-select" name="room_type">
                                <option value="">Todos</option>
                                <option value="estandar">Estándar</option>
                                <option value="suite">Suite</option>
                                <option value="deluxe">Deluxe</option>
                                <option value="familiar">Familiar</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-2">
                        <label class="form-label">Precio Máximo</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                            <input type="number" class="form-control" name="max_price" placeholder="Sin límite">
                        </div>
                    </div>
                    <div class="col-md-10 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary-custom btn-lg w-100">
                            <i class="fas fa-search me-2"></i>Buscar Habitaciones
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Search Results -->
    <div class="container mt-5">
        <?php if ($searchPerformed): ?>
            <?php if (!empty($searchResults)): ?>
                <div class="search-stats">
                    <div class="row">
                        <div class="col-md-12">
                            <h4><i class="fas fa-check-circle me-2"></i>Se encontraron <?= count($searchResults) ?> habitaciones disponibles</h4>
                            <p class="mb-0">Para tu estancia del <?= date('d/m/Y', strtotime($_GET['checkin'])) ?> al <?= date('d/m/Y', strtotime($_GET['checkout'])) ?></p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <?php foreach ($searchResults as $room): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="room-card position-relative">
                                <div class="room-image">
                                    <i class="fas fa-bed"></i>
                                    <span class="room-badge"><?= ucfirst($room['habitacion_tipo']) ?></span>
                                </div>
                                <div class="card-body p-4">
                                    <h5 class="card-title fw-bold">Habitación <?= $room['habitacion_numero'] ?></h5>
                                    <p class="text-muted mb-3"><?= ucfirst($room['habitacion_tipo']) ?> - Capacidad: <?= $room['capacidad'] ?> personas</p>
                                    
                                    <div class="mb-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="amenity-icon">
                                                <i class="fas fa-wifi"></i>
                                            </div>
                                            <span>WiFi Gratis</span>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="amenity-icon">
                                                <i class="fas fa-tv"></i>
                                            </div>
                                            <span>TV Cable</span>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="amenity-icon">
                                                <i class="fas fa-snowflake"></i>
                                            </div>
                                            <span>Aire Acondicionado</span>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <div class="price-tag">
                                                $<?= number_format($room['total_price'], 0, ',', '.') ?>
                                                <small>/<?= $room['nights'] ?> noches</small>
                                            </div>
                                            <small class="text-muted">$<?= number_format($room['price_per_night'], 0, ',', '.') ?> por noche</small>
                                        </div>
                                    </div>

                                    <button class="btn btn-primary-custom w-100" onclick="selectRoom(<?= $room['habitacion_id'] ?>)">
                                        <i class="fas fa-check me-2"></i>Seleccionar Habitación
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>No se encontraron habitaciones disponibles</h3>
                    <p>Lo sentimos, no hay habitaciones disponibles para las fechas seleccionadas. Por favor, intenta con otras fechas o contacta directamente con nuestro hotel.</p>
                    <a href="/Hotel_tame/reservas-online" class="btn btn-outline-primary mt-3">
                        <i class="fas fa-arrow-left me-2"></i>Volver a buscar
                    </a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Mostrar habitaciones de ejemplo -->
            <div class="text-center py-5">
                <h2 class="section-title mb-4">Nuestras Habitaciones</h2>
                <p class="lead text-muted mb-4">Usa el formulario de búsqueda arriba para encontrar disponibilidad o explora nuestras habitaciones disponibles</p>
            </div>
            
            <div class="row">
                <?php foreach ($sampleRooms as $room): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="room-card position-relative">
                            <div class="room-image">
                                <i class="fas fa-bed"></i>
                                <span class="room-badge"><?= ucfirst($room['tipo']) ?></span>
                            </div>
                            <div class="card-body p-4">
                                <h5 class="card-title fw-bold">Habitación <?= $room['numero'] ?></h5>
                                <p class="text-muted mb-3"><?= ucfirst($room['tipo']) ?> - Capacidad: <?= $room['capacidad'] ?> personas</p>
                                <p class="text-muted mb-3"><?= htmlspecialchars($room['descripcion']) ?></p>
                                
                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="amenity-icon">
                                            <i class="fas fa-wifi"></i>
                                        </div>
                                        <span>WiFi Gratis</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="amenity-icon">
                                            <i class="fas fa-tv"></i>
                                        </div>
                                        <span>TV Cable</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="amenity-icon">
                                            <i class="fas fa-snowflake"></i>
                                        </div>
                                        <span>Aire Acondicionado</span>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <div class="price-tag">
                                            $<?= number_format($room['precio_noche'], 0, ',', '.') ?>
                                            <small>/noche</small>
                                        </div>
                                    </div>
                                </div>

                                <button class="btn btn-primary-custom w-100" onclick="selectRoom(<?= $room['id'] ?>)">
                                    <i class="fas fa-check me-2"></i>Seleccionar Habitación
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <div class="row mt-5">
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="amenity-icon mx-auto mb-3" style="width: 60px; height: 60px; font-size: 24px;">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h5>Reserva Segura</h5>
                            <p class="text-muted">Tu reserva está protegida con los más altos estándares de seguridad</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="amenity-icon mx-auto mb-3" style="width: 60px; height: 60px; font-size: 24px;">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <h5>Mejor Precio</h5>
                            <p class="text-muted">Garantizamos el mejor precio en todas nuestras habitaciones</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="amenity-icon mx-auto mb-3" style="width: 60px; height: 60px; font-size: 24px;">
                                <i class="fas fa-headset"></i>
                            </div>
                            <h5>Soporte 24/7</h5>
                            <p class="text-muted">Equipo de atención disponible para ayudarte en cualquier momento</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-hotel me-2"></i>Hotel Tame
                    </h5>
                    <p style="color: #ffffff !important;">Tu experiencia hotelera de lujo y confort. Donde cada detalle está pensado para tu bienestar.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white fs-5" style="color: #ffffff !important;"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white fs-5" style="color: #ffffff !important;"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white fs-5" style="color: #ffffff !important;"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white fs-5" style="color: #ffffff !important;"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3" style="color: #ffffff !important;">Enlaces Rápidos</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="/Hotel_tame/" style="color: #ffffff !important;" class="text-decoration-none">Inicio</a></li>
                        <li class="mb-2"><a href="#habitaciones" style="color: #ffffff !important;" class="text-decoration-none">Habitaciones</a></li>
                        <li class="mb-2"><a href="#servicios" style="color: #ffffff !important;" class="text-decoration-none">Servicios</a></li>
                        <li class="mb-2"><a href="/Hotel_tame/reservas-online" style="color: #ffffff !important;" class="text-decoration-none">Reservas</a></li>
                        <li class="mb-2"><a href="/Hotel_tame/portal-cliente" style="color: #ffffff !important;" class="text-decoration-none">Portal Cliente</a></li>
                    </ul>
                </div>
                
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3" style="color: #ffffff !important;">Newsletter</h5>
                    <p style="color: #ffffff !important; margin-bottom: 15px;">Suscríbete para recibir ofertas exclusivas y novedades</p>
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
                    <p style="color: #ffffff !important; margin-bottom: 0;"> 2024 Hotel Tame. Todos los derechos reservados.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" style="color: #ffffff !important;" class="me-3">Política de Privacidad</a>
                    <a href="#" style="color: #ffffff !important;" class="me-3">Términos y Condiciones</a>
                    <a href="#" style="color: #ffffff !important;">Política de Cancelación</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    
    <script>
        // Inicializar datepickers en español
        flatpickr.localize(flatpickr.l10ns.es);
        
        const checkinPicker = flatpickr("#checkin", {
            minDate: "today",
            dateFormat: "Y-m-d",
            onChange: function(selectedDates) {
                if (selectedDates.length > 0) {
                    checkoutPicker.set('minDate', selectedDates[0]);
                    // Auto-set checkout to next day if not set
                    if (!checkoutPicker.selectedDates.length) {
                        checkoutPicker.setDate(new Date(selectedDates[0].getTime() + 24 * 60 * 60 * 1000));
                    }
                }
            }
        });
        
        // Set default dates
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        
        const checkoutPicker = flatpickr("#checkout", {
            minDate: today,
            dateFormat: "Y-m-d"
        });
        
        checkinPicker.setDate(today);
        checkoutPicker.setDate(tomorrow);

        // Select room function
        function selectRoom(roomId) {
            // Get search parameters from form
            const formData = new FormData(document.getElementById('searchForm'));
            const searchParams = new URLSearchParams();
            
            // Add form data to URL parameters
            for (let [key, value] of formData.entries()) {
                searchParams.set(key, value);
            }
            
            // Add room ID
            searchParams.set('room_id', roomId);
            
            // Redirect to booking form
            window.location.href = '/Hotel_tame/reserva-form?' + searchParams.toString();
        }
    </script>
</body>
</html>
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
            <div class="booking-form">
                <h3 class="text-center mb-4 fw-bold">
                    <i class="fas fa-search me-2"></i>Buscar Disponibilidad
                </h3>
                <form id="searchForm" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Fecha de Entrada</label>
                        <input type="date" name="fecha_entrada" class="form-control form-control-custom" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha de Salida</label>
                        <input type="date" name="fecha_salida" class="form-control form-control-custom" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Huéspedes</label>
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
                            <i class="fas fa-search me-2"></i>Buscar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Available Rooms (Hidden by default) -->
            <div id="roomsSection" style="display: none;">
                <h3 class="fw-bold mb-4">
                    <i class="fas fa-bed me-2"></i>
                    Habitaciones Disponibles
                </h3>
                
                <div class="row g-4" id="roomsContainer">
                    <!-- Rooms will be loaded here via JavaScript -->
                </div>
            </div>

            <!-- Reservation Form (Hidden by default) -->
            <div id="reservationForm" style="display: none;">
                <h3 class="fw-bold mb-4">
                    <i class="fas fa-user-edit me-2"></i>Completar Reserva
                </h3>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="booking-form">
                            <form id="completeReservationForm">
                                <input type="hidden" id="selectedRoomId" name="habitacion_id">
                                <input type="hidden" id="selectedCheckIn" name="fecha_entrada">
                                <input type="hidden" id="selectedCheckOut" name="fecha_salida">
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nombre Completo *</label>
                                        <input type="text" name="cliente_nombre" class="form-control form-control-custom" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email *</label>
                                        <input type="email" name="cliente_email" class="form-control form-control-custom" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Teléfono</label>
                                        <input type="tel" name="cliente_telefono" class="form-control form-control-custom">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Documento de Identidad *</label>
                                        <input type="text" name="cliente_documento" class="form-control form-control-custom" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Observaciones</label>
                                        <textarea name="observaciones" class="form-control form-control-custom" rows="3" 
                                                  placeholder="Indica aquí cualquier solicitud especial..."></textarea>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="terminos" required>
                                            <label class="form-check-label" for="terminos">
                                                Acepto los términos y condiciones y la política de cancelación
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
                                <h6 class="fw-bold" id="summaryRoom">-</h6>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">Fechas</small>
                                <p class="mb-0" id="summaryDates">-</p>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">Noches</small>
                                <p class="mb-0 fw-bold" id="summaryNights">-</p>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">Precio por noche</small>
                                <p class="mb-0" id="summaryPrice">-</p>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-3">
                                <small class="text-muted">Total a pagar</small>
                                <h4 class="fw-bold text-primary" id="summaryTotal">-</h4>
                            </div>
                            
                            <div class="alert alert-info small">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Política de cancelación:</strong> Cancelación gratuita hasta 24 horas antes del check-in.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3" style="color: #ffffff !important;">
                        <i class="fas fa-hotel me-2"></i>Hotel Tame
                    </h5>
                    <p style="color: #ffffff !important;">Tu experiencia hotelera de lujo y confort. Donde cada detalle está pensado para tu bienestar.</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="text-muted small mb-0" style="color: #ffffff !important;"> 2024 Hotel Tame. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">¡Reserva Confirmada!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h4 id="successMessage">Tu reserva ha sido realizada con éxito</h4>
                        <p class="text-muted">Te hemos enviado un email de confirmación con todos los detalles.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="window.location.href='home.php'">Ir al Inicio</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sample room data (in production, this would come from the database)
        const sampleRooms = [
            {
                id: 1,
                tipo: 'Suite Deluxe',
                numero: '101',
                capacidad: 2,
                precio_noche: 150000,
                descripcion: 'Habitación elegante con vista a la ciudad, cama king size y baño de lujo'
            },
            {
                id: 2,
                tipo: 'Suite Junior',
                numero: '102',
                capacidad: 2,
                precio_noche: 120000,
                descripcion: 'Espaciosa habitación con zona de estar y trabajo, cama queen size'
            },
            {
                id: 3,
                tipo: 'Habitación Estándar',
                numero: '103',
                capacidad: 2,
                precio_noche: 89000,
                descripcion: 'Habitación confortable y funcional, perfecta para estancias cortas'
            },
            {
                id: 4,
                tipo: 'Suite Presidencial',
                numero: '201',
                capacidad: 4,
                precio_noche: 299000,
                descripcion: 'Lujo máximo con sala separada, dos habitaciones y baño de lujo'
            },
            {
                id: 5,
                tipo: 'Habitación Familiar',
                numero: '202',
                capacidad: 4,
                precio_noche: 180000,
                descripcion: 'Ideal para familias, con dos camas y área de juegos para niños'
            },
            {
                id: 6,
                tipo: 'Suite Ejecutiva',
                numero: '203',
                capacidad: 2,
                precio_noche: 200000,
                descripcion: 'Diseñada para ejecutivos con área de trabajo completa'
            }
        ];

        let selectedRoom = null;
        let searchDates = {};

        // Search form handler
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            searchDates = {
                entrada: formData.get('fecha_entrada'),
                salida: formData.get('fecha_salida'),
                huespedes: formData.get('huespedes')
            };
            
            displayRooms();
        });

        // Display available rooms
        function displayRooms() {
            const container = document.getElementById('roomsContainer');
            const section = document.getElementById('roomsSection');
            
            container.innerHTML = '';
            
            sampleRooms.forEach(room => {
                const nights = calculateNights(searchDates.entrada, searchDates.salida);
                const total = room.precio_noche * nights;
                
                const roomCard = `
                    <div class="col-md-6 col-lg-4">
                        <div class="room-card">
                            <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400&h=300&fit=crop" 
                                 class="room-image" alt="${room.tipo}">
                            
                            <div class="card-body">
                                <h5 class="card-title">${room.tipo} - ${room.numero}</h5>
                                <p class="card-text">${room.descripcion.substring(0, 80)}...</p>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <i class="fas fa-users text-muted me-1"></i>
                                        <small class="text-muted">${room.capacidad} personas</small>
                                    </div>
                                    <div class="price-tag">
                                        $${room.precio_noche.toLocaleString('es-CO')}/noche
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted">
                                        ${nights} noche(s) - Total: <strong>$${total.toLocaleString('es-CO')}</strong>
                                    </small>
                                </div>
                                
                                <button type="button" class="btn btn-primary-custom w-100" 
                                        onclick="selectRoom(${room.id})">
                                    <i class="fas fa-calendar-check me-2"></i>Seleccionar
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                container.innerHTML += roomCard;
            });
            
            section.style.display = 'block';
            section.scrollIntoView({ behavior: 'smooth' });
        }

        // Select a room
        function selectRoom(roomId) {
            selectedRoom = sampleRooms.find(r => r.id === roomId);
            
            if (selectedRoom) {
                document.getElementById('selectedRoomId').value = selectedRoom.id;
                document.getElementById('selectedCheckIn').value = searchDates.entrada;
                document.getElementById('selectedCheckOut').value = searchDates.salida;
                
                // Update summary
                const nights = calculateNights(searchDates.entrada, searchDates.salida);
                const total = selectedRoom.precio_noche * nights;
                
                document.getElementById('summaryRoom').textContent = `${selectedRoom.tipo} - ${selectedRoom.numero}`;
                document.getElementById('summaryDates').textContent = 
                    `${formatDate(searchDates.entrada)} - ${formatDate(searchDates.salida)}`;
                document.getElementById('summaryNights').textContent = `${nights} noche(s)`;
                document.getElementById('summaryPrice').textContent = `$${selectedRoom.precio_noche.toLocaleString('es-CO')}`;
                document.getElementById('summaryTotal').textContent = `$${total.toLocaleString('es-CO')}`;
                
                // Show reservation form
                document.getElementById('reservationForm').style.display = 'block';
                document.getElementById('reservationForm').scrollIntoView({ behavior: 'smooth' });
            }
        }

        // Complete reservation form handler
        document.getElementById('completeReservationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Simulate reservation processing
            const modal = new bootstrap.Modal(document.getElementById('successModal'));
            document.getElementById('successMessage').textContent = 
                `¡Reserva realizada con éxito! Tu número de reserva es: #${Math.floor(Math.random() * 10000) + 1000}`;
            modal.show();
            
            // Reset forms
            this.reset();
            document.getElementById('reservationForm').style.display = 'none';
            document.getElementById('roomsSection').style.display = 'none';
            document.getElementById('searchForm').reset();
        });

        // Utility functions
        function calculateNights(checkIn, checkOut) {
            const start = new Date(checkIn);
            const end = new Date(checkOut);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            return diffDays;
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('es-CO');
        }

        // Set minimum dates for booking and default values
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const tomorrowStr = tomorrow.toISOString().split('T')[0];
            
            const checkInInput = document.querySelector('input[name="fecha_entrada"]');
            const checkOutInput = document.querySelector('input[name="fecha_salida"]');
            
            if (checkInInput) {
                checkInInput.min = today;
                checkInInput.value = today; // Establecer fecha actual por defecto
            }
            
            if (checkOutInput) {
                checkOutInput.min = tomorrowStr;
                checkOutInput.value = tomorrowStr; // Establecer día siguiente por defecto
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
