<?php
require_once __DIR__ . '/../../../backend/config/database.php';

// Definir título y descripción de la página
$pageTitle = 'Reservas Online - Hotel Management System';
$pageDescription = 'Sistema de reservas en línea para clientes';

include __DIR__ . '/../../../backend/includes/header_public.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-calendar-check me-2"></i>
                        Reservas Online
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Sistema de reservas en línea - Seleccione sus fechas para buscar habitaciones disponibles.
                    </div>
                    
                    <form id="searchForm" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Check-in</label>
                                <input type="date" class="form-control" id="checkin" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Check-out</label>
                                <input type="date" class="form-control" id="checkout" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Adultos</label>
                                <select class="form-control" id="adults">
                                    <option value="1">1</option>
                                    <option value="2" selected>2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Niños</label>
                                <select class="form-control" id="children">
                                    <option value="0" selected>0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <div id="searchResults">
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Seleccione fechas para buscar habitaciones disponibles</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Establecer fechas mínimas
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);
    
    const todayStr = today.toISOString().split('T')[0];
    const tomorrowStr = tomorrow.toISOString().split('T')[0];
    
    $('#checkin').attr('min', todayStr);
    $('#checkout').attr('min', tomorrowStr);
    
    // Cambiar fecha mínima de checkout cuando cambia checkin
    $('#checkin').change(function() {
        const checkinDate = new Date($(this).val());
        const minCheckout = new Date(checkinDate);
        minCheckout.setDate(checkinDate.getDate() + 1);
        const minCheckoutStr = minCheckout.toISOString().split('T')[0];
        $('#checkout').attr('min', minCheckoutStr);
        
        if ($('#checkout').val() && $('#checkout').val() <= $(this).val()) {
            $('#checkout').val(minCheckoutStr);
        }
    });
    
    // Manejar búsqueda
    $('#searchForm').submit(function(e) {
        e.preventDefault();
        searchRooms();
    });
});

function searchRooms() {
    const checkin = $('#checkin').val();
    const checkout = $('#checkout').val();
    const adults = $('#adults').val();
    const children = $('#children').val();
    
    if (!checkin || !checkout) {
        alert('Por favor seleccione las fechas de check-in y check-out');
        return;
    }
    
    // Mostrar loading
    $('#searchResults').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Buscando...</span>
            </div>
            <p class="mt-3">Buscando habitaciones disponibles...</p>
        </div>
    `);
    
    // Buscar habitaciones disponibles
    $.get('/Hotel_tame/api/endpoints/habitaciones.php', function(data) {
        const habitaciones = data.records || data || [];
        
        if (habitaciones.length === 0) {
            $('#searchResults').html(`
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    No hay habitaciones disponibles para las fechas seleccionadas.
                </div>
            `);
            return;
        }
        
        // Filtrar solo habitaciones disponibles
        const habitacionesDisponibles = habitaciones.filter(h => h.estado === 'disponible');
        
        if (habitacionesDisponibles.length === 0) {
            $('#searchResults').html(`
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    No hay habitaciones disponibles en este momento.
                </div>
            `);
            return;
        }
        
        let html = '<div class="row">';
        habitacionesDisponibles.forEach(habitacion => {
            const precio = parseFloat(habitacion.precio || habitacion.precio_noche || 0);
            const tipoCapitalizado = habitacion.tipo ? habitacion.tipo.charAt(0).toUpperCase() + habitacion.tipo.slice(1) : 'Estándar';
            
            html += `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title">Hab. ${habitacion.numero}</h5>
                                    <small class="text-muted">${tipoCapitalizado} - Piso ${habitacion.piso}</small>
                                </div>
                                <span class="badge bg-success">Disponible</span>
                            </div>
                            
                            <div class="mb-3">
                                <p class="mb-1"><strong>Tipo:</strong> ${tipoCapitalizado}</p>
                                <p class="mb-1"><strong>Capacidad:</strong> <i class="fas fa-user ms-1"></i> ${habitacion.capacidad || 2} personas</p>
                                <p class="mb-1"><strong>Precio:</strong> <span class="text-primary fw-bold">$${precio.toLocaleString('es-CO')}</span>/noche</p>
                            </div>
                            
                            ${habitacion.descripcion ? `<p class="text-muted small">${habitacion.descripcion}</p>` : ''}
                            
                            <button class="btn btn-primary w-100" onclick="selectRoom(${habitacion.id}, '${habitacion.numero}', '${tipoCapitalizado}')">
                                <i class="fas fa-calendar-plus me-1"></i>
                                Seleccionar
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        
        $('#searchResults').html(html);
    }).fail(function() {
        $('#searchResults').html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                Error al buscar habitaciones. Intente nuevamente.
            </div>
        `);
    });
}

function selectRoom(roomId, roomNumber, roomType) {
    if (confirm(`¿Seleccionar la habitación ${roomNumber} (${roomType}) para tu reserva?`)) {
        alert('Función de reserva próximamente disponible. Por favor contacte directamente al hotel.');
    }
}
</script>

<?php include __DIR__ . '/../../../backend/includes/footer_public.php'; ?>
