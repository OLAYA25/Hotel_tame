<?php
require_once __DIR__ . '/../../../../backend/config/database.php';

// Verificar sesión de usuario (session ya iniciada en router)
if (!isset($_SESSION['usuario'])) {
    header('Location: /Hotel_tame/login');
    exit;
}

// Incluir middleware de autenticación y permisos
require_once __DIR__ . '/../../../../backend/includes/auth_middleware.php';

include __DIR__ . '/../../../../backend/includes/header_dashboard.php';
?>

<div class="main-content">
    <div id="notification-container"></div>
    
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Habitaciones</h1>
                <p class="text-muted mb-0">Gestiona las habitaciones del hotel</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalHabitacion" onclick="abrirModalNuevo()">
                <i class="fas fa-plus me-2"></i>Nueva Habitación
            </button>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="disponiblesCount">0</h4>
                            <small>Disponibles</small>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="ocupadasCount">0</h4>
                            <small>Ocupadas</small>
                        </div>
                        <i class="fas fa-bed fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="mantenimientoCount">0</h4>
                            <small>Mantenimiento</small>
                        </div>
                        <i class="fas fa-tools fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="totalCount">0</h4>
                            <small>Total</small>
                        </div>
                        <i class="fas fa-home fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="busquedaHabitacion" placeholder="Buscar habitación...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filtroTipo">
                        <option value="">Todos los tipos</option>
                        <option value="sencilla">Sencilla</option>
                        <option value="doble">Doble</option>
                        <option value="suite">Suite</option>
                        <option value="presidencial">Presidencial</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filtroEstado">
                        <option value="">Todos los estados</option>
                        <option value="disponible">Disponible</option>
                        <option value="ocupada">Ocupada</option>
                        <option value="mantenimiento">Mantenimiento</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary w-100" onclick="aplicarFiltros()">
                        <i class="fas fa-filter me-2"></i>Filtrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid de tarjetas en lugar de tabla -->
    <div class="row g-4" id="habitacionesGrid">
        <div class="col-12 text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="text-muted mt-2">Cargando habitaciones...</p>
        </div>
    </div>

    <!-- Modal para agregar/editar habitación -->
    <div class="modal fade" id="modalHabitacion" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nueva Habitación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formHabitacion">
                    <div class="modal-body">
                        <input type="hidden" id="habitacion_id">
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Número *</label>
                                <input type="text" class="form-control" id="numero" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Piso *</label>
                                <input type="number" class="form-control" id="piso" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tipo *</label>
                                <select class="form-select" id="tipo" required>
                                    <option value="">Seleccionar tipo</option>
                                    <option value="sencilla">Sencilla</option>
                                    <option value="doble">Doble</option>
                                    <option value="suite">Suite</option>
                                    <option value="presidencial">Presidencial</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Capacidad *</label>
                                <input type="number" class="form-control" id="capacidad" min="1" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Precio por Noche *</label>
                                <input type="number" class="form-control" id="precio_noche" min="0" step="0.01" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estado</label>
                                <select class="form-select" id="estado">
                                    <option value="disponible">Disponible</option>
                                    <option value="ocupada">Ocupada</option>
                                    <option value="mantenimiento">Mantenimiento</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Imagen</label>
                                <div class="mb-2">
                                    <input type="file" class="form-control" id="imagen" accept="image/*" onchange="previewImageHabitacion(this)">
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="openCameraHabitacion()">
                                        <i class="fas fa-camera me-1"></i> Cámara
                                    </button>
                                </div>
                                <div id="imagePreviewHabitacion" class="text-center"></div>
                                <input type="hidden" id="imagen_url">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    cargarHabitaciones();
});

function cargarHabitaciones() {
    $.get('/Hotel_tame/api/habitaciones?estado_real=1', function(data) {
        const grid = $('#habitacionesGrid');
        grid.empty();
        
        const habitacionesList = Array.isArray(data) ? data : (data.records || []);
        
        // Actualizar estadísticas
        actualizarEstadisticas(habitacionesList);

        if (habitacionesList.length === 0) {
            grid.html(`
                <div class="col-12 text-center">
                    <div class="text-muted">
                        <i class="fas fa-bed fa-3x mb-3"></i>
                        <h5>No hay habitaciones registradas</h5>
                        <p>Agrega tu primera habitación para comenzar</p>
                    </div>
                </div>
            `);
            return;
        }

        habitacionesList.forEach(habitacion => {
            const estadoClass = {
                'disponible': 'success',
                'ocupada': 'danger',
                'mantenimiento': 'warning'
            }[habitacion.estado];
            
            const estadoTexto = {
                'disponible': 'Disponible',
                'ocupada': 'Ocupada',
                'mantenimiento': 'Mantenimiento'
            }[habitacion.estado];

            const tipoCapitalizado = habitacion.tipo ? (habitacion.tipo.charAt(0).toUpperCase() + habitacion.tipo.slice(1)) : '';
            const precioValue = habitacion.precio ?? habitacion.precio_noche ?? 0;
            
            grid.append(`
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-${estadoClass} bg-opacity-10 text-${estadoClass} rounded p-2 me-3">
                                        <i class="fas fa-bed fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-0">Hab. ${habitacion.numero}</h5>
                                        <small class="text-muted">Piso ${habitacion.piso}</small>
                                    </div>
                                </div>
                                <span class="badge bg-${estadoClass}">${estadoTexto}</span>
                            </div>
                            
                            <div class="mb-3">
                                <p class="mb-2"><strong>Tipo:</strong> ${tipoCapitalizado}</p>
                                <p class="mb-2"><strong>Precio:</strong> <span class="text-primary fs-5 fw-bold">$${parseFloat(precioValue).toLocaleString('es-CO')}</span></p>
                                <p class="mb-2">
                                    <strong>Capacidad:</strong> 
                                    <i class="fas fa-user ms-2"></i> ${habitacion.capacidad}
                                </p>
                            </div>
                            
                            ${habitacion.descripcion ? `<p class="text-muted small">${habitacion.descripcion}</p>` : ''}
                            
                            <div class="d-flex gap-2 mt-3">
                                <button class="btn btn-outline-primary btn-sm flex-fill" onclick="editarHabitacion(${habitacion.id})">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="eliminarHabitacion(${habitacion.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });
    }).fail(function() {
        $('#habitacionesGrid').html(`
            <div class="col-12 text-center">
                <div class="text-danger">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <h5>Error al cargar habitaciones</h5>
                    <p>Por favor, intenta nuevamente</p>
                </div>
            </div>
        `);
    });
}

function actualizarEstadisticas(habitaciones) {
    const stats = {
        disponible: 0,
        ocupada: 0,
        mantenimiento: 0,
        total: habitaciones.length
    };
    
    habitaciones.forEach(h => {
        if (stats.hasOwnProperty(h.estado)) {
            stats[h.estado]++;
        }
    });
    
    $('#disponiblesCount').text(stats.disponible);
    $('#ocupadasCount').text(stats.ocupada);
    $('#mantenimientoCount').text(stats.mantenimiento);
    $('#totalCount').text(stats.total);
}

function abrirModalNuevo() {
    $('#modalTitle').text('Nueva Habitación');
    $('#formHabitacion')[0].reset();
    $('#habitacion_id').val('');
    $('#imagePreviewHabitacion').html('');
    $('#imagen_url').val('');
}

function editarHabitacion(id) {
    $.get(`/Hotel_tame/api/habitaciones?id=${id}`, function(habitacion) {
        $('#modalTitle').text('Editar Habitación');
        $('#habitacion_id').val(habitacion.id);
        $('#numero').val(habitacion.numero);
        $('#piso').val(habitacion.piso);
        $('#tipo').val(habitacion.tipo);
        $('#capacidad').val(habitacion.capacidad);
        $('#precio_noche').val(habitacion.precio_noche);
        $('#estado').val(habitacion.estado);
        $('#descripcion').val(habitacion.descripcion || '');
        
        if (habitacion.imagen) {
            $('#imagePreviewHabitacion').html(`
                <img src="${habitacion.imagen}" class="img-thumbnail" style="max-height: 200px;">
            `);
            $('#imagen_url').val(habitacion.imagen);
        }
        
        $('#modalHabitacion').modal('show');
    });
}

function eliminarHabitacion(id) {
    if (confirm('¿Estás seguro de que deseas eliminar esta habitación?')) {
        $.ajax({
            url: `/Hotel_tame/api/habitaciones?id=${id}`,
            type: 'DELETE',
            success: function() {
                cargarHabitaciones();
                mostrarNotificacion('Habitación eliminada correctamente', 'success');
            },
            error: function() {
                mostrarNotificacion('Error al eliminar habitación', 'error');
            }
        });
    }
}

function aplicarFiltros() {
    const busqueda = $('#busquedaHabitacion').val();
    const tipo = $('#filtroTipo').val();
    const estado = $('#filtroEstado').val();
    
    let params = new URLSearchParams();
    if (busqueda) params.append('busqueda', busqueda);
    if (tipo) params.append('tipo', tipo);
    if (estado) params.append('estado', estado);
    
    $.get(`/Hotel_tame/api/habitaciones?${params.toString()}`, function(data) {
        const habitacionesList = Array.isArray(data) ? data : (data.records || []);
        const grid = $('#habitacionesGrid');
        grid.empty();
        
        actualizarEstadisticas(habitacionesList);
        
        if (habitacionesList.length === 0) {
            grid.html(`
                <div class="col-12 text-center">
                    <div class="text-muted">
                        <i class="fas fa-search fa-3x mb-3"></i>
                        <h5>No se encontraron habitaciones</h5>
                        <p>Intenta con otros filtros de búsqueda</p>
                    </div>
                </div>
            `);
            return;
        }
        
        // Renderizar habitaciones filtradas (mismo código que en cargarHabitaciones)
        habitacionesList.forEach(habitacion => {
            const estadoClass = {
                'disponible': 'success',
                'ocupada': 'danger',
                'mantenimiento': 'warning'
            }[habitacion.estado];
            
            const estadoTexto = {
                'disponible': 'Disponible',
                'ocupada': 'Ocupada',
                'mantenimiento': 'Mantenimiento'
            }[habitacion.estado];

            const tipoCapitalizado = habitacion.tipo ? (habitacion.tipo.charAt(0).toUpperCase() + habitacion.tipo.slice(1)) : '';
            const precioValue = habitacion.precio ?? habitacion.precio_noche ?? 0;
            
            grid.append(`
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-${estadoClass} bg-opacity-10 text-${estadoClass} rounded p-2 me-3">
                                        <i class="fas fa-bed fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-0">Hab. ${habitacion.numero}</h5>
                                        <small class="text-muted">Piso ${habitacion.piso}</small>
                                    </div>
                                </div>
                                <span class="badge bg-${estadoClass}">${estadoTexto}</span>
                            </div>
                            
                            <div class="mb-3">
                                <p class="mb-2"><strong>Tipo:</strong> ${tipoCapitalizado}</p>
                                <p class="mb-2"><strong>Precio:</strong> <span class="text-primary fs-5 fw-bold">$${parseFloat(precioValue).toLocaleString('es-CO')}</span></p>
                                <p class="mb-2">
                                    <strong>Capacidad:</strong> 
                                    <i class="fas fa-user ms-2"></i> ${habitacion.capacidad}
                                </p>
                            </div>
                            
                            ${habitacion.descripcion ? `<p class="text-muted small">${habitacion.descripcion}</p>` : ''}
                            
                            <div class="d-flex gap-2 mt-3">
                                <button class="btn btn-outline-primary btn-sm flex-fill" onclick="editarHabitacion(${habitacion.id})">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="eliminarHabitacion(${habitacion.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });
    });
}

function previewImageHabitacion(input) {
    const preview = document.getElementById('imagePreviewHabitacion');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" class="img-thumbnail" style="max-height: 200px;">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function openCameraHabitacion() {
    // Implementar cámara si es necesario
    alert('Función de cámara en desarrollo');
}

function mostrarNotificacion(mensaje, tipo) {
    const alertClass = tipo === 'success' ? 'alert-success' : 'alert-danger';
    const icon = tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
    
    const notification = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${icon} me-2"></i>
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('#notification-container').html(notification);
    
    setTimeout(() => {
        $('#notification-container .alert').alert('close');
    }, 5000);
}

// Manejar envío del formulario
$('#formHabitacion').on('submit', function(e) {
    e.preventDefault();
    
    const habitacionId = $('#habitacion_id').val();
    const habitacionData = {
        numero: $('#numero').val(),
        piso: parseInt($('#piso').val()),
        tipo: $('#tipo').val(),
        capacidad: parseInt($('#capacidad').val()),
        precio_noche: parseFloat($('#precio_noche').val()),
        estado: $('#estado').val(),
        descripcion: $('#descripcion').val()
    };
    
    const url = habitacionId ? `/Hotel_tame/api/habitaciones?id=${habitacionId}` : '/Hotel_tame/api/habitaciones';
    const method = habitacionId ? 'PUT' : 'POST';
    
    $.ajax({
        url: url,
        type: method,
        data: JSON.stringify(habitacionData),
        contentType: 'application/json',
        success: function() {
            $('#modalHabitacion').modal('hide');
            cargarHabitaciones();
            mostrarNotificacion('Habitación guardada correctamente', 'success');
        },
        error: function() {
            mostrarNotificacion('Error al guardar habitación', 'error');
        }
    });
});
</script>

<?php include __DIR__ . '/../../../../backend/includes/footer.php'; ?>

</div>
</body>
</html>
