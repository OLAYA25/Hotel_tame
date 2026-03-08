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
                <h1>Clientes</h1>
                <p class="text-muted mb-0">Gestiona los clientes del hotel</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCliente" onclick="abrirModalNuevo()">
                <i class="fas fa-plus me-2"></i>Nuevo Cliente
            </button>
        </div>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="busquedaCliente" placeholder="Buscar cliente...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filtroTipo">
                        <option value="">Todos los tipos</option>
                        <option value="regular">Regular</option>
                        <option value="vip">VIP</option>
                        <option value="corporativo">Corporativo</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filtroEstado">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
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
    <div class="row g-4" id="clientesGrid">
        <div class="col-12 text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="text-muted mt-2">Cargando clientes...</p>
        </div>
    </div>

    <!-- Modal para agregar/editar cliente -->
    <div class="modal fade" id="modalCliente" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nuevo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formCliente">
                    <div class="modal-body">
                        <input type="hidden" id="cliente_id">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellido *</label>
                                <input type="text" class="form-control" id="apellido" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Cliente</label>
                                <select class="form-select" id="tipo_cliente">
                                    <option value="regular">Regular</option>
                                    <option value="vip">VIP</option>
                                    <option value="corporativo">Corporativo</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Documento</label>
                                <input type="text" class="form-control" id="documento">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="direccion">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notas</label>
                                <textarea class="form-control" id="notas" rows="3"></textarea>
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
    cargarClientes();
});

function cargarClientes() {
    $.get('/Hotel_tame/api/clientes', function(data) {
        const grid = $('#clientesGrid');
        grid.empty();
        
        const clientesList = Array.isArray(data) ? data : (data.records || []);

        if (clientesList.length === 0) {
            grid.html(`
                <div class="col-12 text-center">
                    <div class="text-muted">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <h5>No hay clientes registrados</h5>
                        <p>Agrega tu primer cliente para comenzar</p>
                    </div>
                </div>
            `);
            return;
        }

        clientesList.forEach(cliente => {
            const tipoClass = {
                'regular': 'primary',
                'vip': 'warning',
                'corporativo': 'success'
            }[cliente.tipo_cliente] || 'secondary';
            
            const tipoIcon = {
                'regular': 'fa-user',
                'vip': 'fa-crown',
                'corporativo': 'fa-building'
            }[cliente.tipo_cliente] || 'fa-user';

            grid.append(`
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-${tipoClass} bg-opacity-10 text-${tipoClass} rounded p-2 me-3">
                                        <i class="fas ${tipoIcon} fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-0">${cliente.nombre} ${cliente.apellido}</h5>
                                        <small class="text-muted">${cliente.email}</small>
                                    </div>
                                </div>
                                <span class="badge bg-${tipoClass}">${cliente.tipo_cliente || 'regular'}</span>
                            </div>
                            
                            <div class="mb-3">
                                ${cliente.telefono ? `<p class="mb-2"><i class="fas fa-phone me-2"></i> ${cliente.telefono}</p>` : ''}
                                ${cliente.documento ? `<p class="mb-2"><i class="fas fa-id-card me-2"></i> ${cliente.documento}</p>` : ''}
                                ${cliente.direccion ? `<p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> ${cliente.direccion}</p>` : ''}
                            </div>
                            
                            ${cliente.notas ? `<p class="text-muted small">${cliente.notas}</p>` : ''}
                            
                            <div class="d-flex gap-2 mt-3">
                                <button class="btn btn-outline-primary btn-sm flex-fill" onclick="editarCliente(${cliente.id})">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="eliminarCliente(${cliente.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });
    }).fail(function() {
        $('#clientesGrid').html(`
            <div class="col-12 text-center">
                <div class="text-danger">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <h5>Error al cargar clientes</h5>
                    <p>Por favor, intenta nuevamente</p>
                </div>
            </div>
        `);
    });
}

function abrirModalNuevo() {
    $('#modalTitle').text('Nuevo Cliente');
    $('#formCliente')[0].reset();
    $('#cliente_id').val('');
}

function editarCliente(id) {
    $.get(`/Hotel_tame/api/clientes?id=${id}`, function(cliente) {
        $('#modalTitle').text('Editar Cliente');
        $('#cliente_id').val(cliente.id);
        $('#nombre').val(cliente.nombre);
        $('#apellido').val(cliente.apellido);
        $('#email').val(cliente.email);
        $('#telefono').val(cliente.telefono || '');
        $('#tipo_cliente').val(cliente.tipo_cliente || 'regular');
        $('#documento').val(cliente.documento || '');
        $('#direccion').val(cliente.direccion || '');
        $('#notas').val(cliente.notas || '');
        
        $('#modalCliente').modal('show');
    });
}

function eliminarCliente(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este cliente?')) {
        $.ajax({
            url: `/Hotel_tame/api/clientes?id=${id}`,
            type: 'DELETE',
            success: function() {
                cargarClientes();
                mostrarNotificacion('Cliente eliminado correctamente', 'success');
            },
            error: function() {
                mostrarNotificacion('Error al eliminar cliente', 'error');
            }
        });
    }
}

function aplicarFiltros() {
    const busqueda = $('#busquedaCliente').val();
    const tipo = $('#filtroTipo').val();
    const estado = $('#filtroEstado').val();
    
    let params = new URLSearchParams();
    if (busqueda) params.append('busqueda', busqueda);
    if (tipo) params.append('tipo', tipo);
    if (estado) params.append('estado', estado);
    
    $.get(`/Hotel_tame/api/clientes?${params.toString()}`, function(data) {
        // Recargar clientes con filtros aplicados
        const clientesList = Array.isArray(data) ? data : (data.records || []);
        const grid = $('#clientesGrid');
        grid.empty();
        
        if (clientesList.length === 0) {
            grid.html(`
                <div class="col-12 text-center">
                    <div class="text-muted">
                        <i class="fas fa-search fa-3x mb-3"></i>
                        <h5>No se encontraron clientes</h5>
                        <p>Intenta con otros filtros de búsqueda</p>
                    </div>
                </div>
            `);
            return;
        }
        
        // Renderizar clientes filtrados (mismo código que en cargarClientes)
        clientesList.forEach(cliente => {
            const tipoClass = {
                'regular': 'primary',
                'vip': 'warning',
                'corporativo': 'success'
            }[cliente.tipo_cliente] || 'secondary';
            
            const tipoIcon = {
                'regular': 'fa-user',
                'vip': 'fa-crown',
                'corporativo': 'fa-building'
            }[cliente.tipo_cliente] || 'fa-user';

            grid.append(`
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-${tipoClass} bg-opacity-10 text-${tipoClass} rounded p-2 me-3">
                                        <i class="fas ${tipoIcon} fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-0">${cliente.nombre} ${cliente.apellido}</h5>
                                        <small class="text-muted">${cliente.email}</small>
                                    </div>
                                </div>
                                <span class="badge bg-${tipoClass}">${cliente.tipo_cliente || 'regular'}</span>
                            </div>
                            
                            <div class="mb-3">
                                ${cliente.telefono ? `<p class="mb-2"><i class="fas fa-phone me-2"></i> ${cliente.telefono}</p>` : ''}
                                ${cliente.documento ? `<p class="mb-2"><i class="fas fa-id-card me-2"></i> ${cliente.documento}</p>` : ''}
                                ${cliente.direccion ? `<p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> ${cliente.direccion}</p>` : ''}
                            </div>
                            
                            ${cliente.notas ? `<p class="text-muted small">${cliente.notas}</p>` : ''}
                            
                            <div class="d-flex gap-2 mt-3">
                                <button class="btn btn-outline-primary btn-sm flex-fill" onclick="editarCliente(${cliente.id})">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="eliminarCliente(${cliente.id})">
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
$('#formCliente').on('submit', function(e) {
    e.preventDefault();
    
    const clienteId = $('#cliente_id').val();
    const clienteData = {
        nombre: $('#nombre').val(),
        apellido: $('#apellido').val(),
        email: $('#email').val(),
        telefono: $('#telefono').val(),
        tipo_cliente: $('#tipo_cliente').val(),
        documento: $('#documento').val(),
        direccion: $('#direccion').val(),
        notas: $('#notas').val()
    };
    
    const url = clienteId ? `/Hotel_tame/api/clientes?id=${clienteId}` : '/Hotel_tame/api/clientes';
    const method = clienteId ? 'PUT' : 'POST';
    
    $.ajax({
        url: url,
        type: method,
        data: JSON.stringify(clienteData),
        contentType: 'application/json',
        success: function() {
            $('#modalCliente').modal('hide');
            cargarClientes();
            mostrarNotificacion('Cliente guardado correctamente', 'success');
        },
        error: function() {
            mostrarNotificacion('Error al guardar cliente', 'error');
        }
    });
});
</script>

<?php include __DIR__ . '/../../../../backend/includes/footer.php'; ?>

</div>
</body>
</html>
