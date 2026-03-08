<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas Online - Hotel Tame</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --gradient-primary: linear-gradient(135deg, var(--primary-color) 0%, #764ba2 100%);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #f8fafc;
        }

        .navbar-web {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
        }

        .hero-booking {
            background: var(--gradient-primary);
            color: white;
            padding: 80px 0 60px;
            text-align: center;
        }

        .section {
            padding: 80px 0;
        }

        .booking-form {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            margin-top: -50px;
            position: relative;
            z-index: 10;
        }

        .form-control-custom {
            border: 2px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 12px 16px;
        }

        .form-control-custom:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
        }

        .btn-primary-custom {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            color: white;
        }

        .room-card {
            background: white;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .room-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .price-tag {
            background: var(--gradient-primary);
            color: white;
            padding: 8px 16px;
            border-radius: 25px;
            font-weight: 700;
        }
    </style>
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
