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

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 40px;
        }

        .loading-spinner.active {
            display: block;
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
            <form id="searchForm" method="GET" action="reservas_online.php">
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
                    <a href="reservas_online.php" class="btn btn-outline-primary mt-3">
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

    <!-- Loading Spinner -->
    <div class="loading-spinner" id="loadingSpinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Buscando...</span>
        </div>
        <p class="mt-3">Buscando habitaciones disponibles...</p>
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
        
        const checkoutPicker = flatpickr("#checkout", {
            minDate: "tomorrow",
            dateFormat: "Y-m-d"
        });

        // Set default dates
        const today = new Date();
        const tomorrow = new Date(today.getTime() + 24 * 60 * 60 * 1000);
        checkinPicker.setDate(today);
        checkoutPicker.setDate(tomorrow);

        // Form submission
        document.getElementById('searchForm').addEventListener('submit', function() {
            document.getElementById('loadingSpinner').classList.add('active');
        });

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
            window.location.href = 'reserva_form_simple.php?' + searchParams.toString();
        }

        // Auto-search if URL parameters exist
        window.addEventListener('load', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('checkin') && urlParams.has('checkout')) {
                // Form is already submitted via GET
                document.getElementById('loadingSpinner').classList.remove('active');
            }
        });
    </script>
</body>
</html>
