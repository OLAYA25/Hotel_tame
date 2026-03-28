<?php
session_start();
require_once __DIR__ . '/../../../backend/config/database.php';

// Verificar sesión de usuario
if (!isset($_SESSION['usuario'])) {
    header('Location: /Hotel_tame/login');
    exit;
}

$pageTitle = 'Pedidos - Hotel Management System';
$pageDescription = 'Gestiona los pedidos de productos';
$version = time(); // Cache busting
include __DIR__ . '/../../../backend/includes/header.php';
include __DIR__ . '/../../../backend/includes/sidebar.php';
?>

<!-- Versión: <?php echo $version; ?> -->

<div class="main-content">
    <div id="notification-container"></div>
    
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Pedidos de Productos</h1>
                <p class="text-muted mb-0">Gestiona pedidos de huéspedes y ventas directas</p>
            </div>
            <button class="btn btn-primary" onclick="abrirModalNuevo()">
                <i class="fas fa-plus me-2"></i>Nuevo Pedido / Venta Directa
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tipo</label>
                    <select class="form-select" id="filtroTipo" onchange="cargarPedidos()">
                        <option value="">Todos los tipos</option>
                        <option value="habitacion">Pedidos de Habitación</option>
                        <option value="directa">Ventas Directas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="filtroEstado" onchange="cargarPedidos()">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="en_preparacion">En Preparación</option>
                        <option value="entregado">Entregado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="filtroFecha" onchange="cargarPedidos()">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Búsqueda</label>
                    <input type="text" class="form-control" id="busqueda" placeholder="Buscar por habitación, cliente o referencia..." onkeyup="handleSearch(event)">
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de pedidos -->
    <div class="row" id="pedidosGrid">
        <!-- Los pedidos se cargarán dinámicamente aquí -->
    </div>
    
    <!-- Paginación -->
    <div class="d-flex justify-content-center mt-4">
        <nav>
            <ul class="pagination" id="paginationContainer">
                <!-- La paginación se cargará dinámicamente aquí -->
            </ul>
        </nav>
    </div>
</div>

<!-- Modal Nuevo Pedido -->
<div class="modal fade" id="modalPedido" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPedido">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tipo de Pedido</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="tipo_pedido" id="tipo_habitacion" value="habitacion" checked>
                                <label class="form-check-label" for="tipo_habitacion">Pedido de Habitación</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="tipo_pedido" id="tipo_directa" value="directa">
                                <label class="form-check-label" for="tipo_directa">Venta Directa</label>
                            </div>
                        </div>
                    </div>

                    <div id="seccionHabitacion">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Habitación</label>
                                <select class="form-select" id="habitacion_id" name="habitacion_id" required>
                                    <option value="">Seleccione una habitación...</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="seccionDirecta" style="display: none;">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Referencia de Venta</label>
                                <input type="text" class="form-control" id="referencia_venta" name="referencia_venta" readonly>
                                <small class="text-muted">Se generará automáticamente</small>
                            </div>
                        </div>
                    </div>

                    <hr>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6>Productos</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="agregarProducto()">
                            <i class="fas fa-plus me-1"></i>Agregar Producto
                        </button>
                    </div>
                    
                    <div id="productosContainer">
                        <!-- Los productos se agregarán dinámicamente aquí -->
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-8">
                            <label class="form-label">Notas</label>
                            <textarea class="form-control" id="notas" name="notas" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total</label>
                            <div class="form-control" id="totalPedido" readonly style="background-color: #f8f9fa; font-weight: bold;">$0</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Pedido</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Pedido -->
<div class="modal fade" id="modalVerPedido" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles del Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detallesPedido">
                    <!-- Los detalles se cargarán dinámicamente aquí -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Variables globales
let productos = [];
let currentPage = 1;
let searchTimeout;
let isLoadingPedidos = false;
let currentFilters = {
    tipo: '',
    estado: '',
    fecha: '',
    busqueda: ''
};

// Función principal para abrir modal
function abrirModalNuevo() {
    console.log('Abriendo modal de nuevo pedido...');
    $('#modalTitle').text('Nuevo Pedido');
    $('#formPedido')[0].reset();
    $('#pedido_id').val('');
    
    // Reset tipo de pedido a "habitacion" por defecto
    $('#tipo_habitacion').prop('checked', true);
    $('#seccionHabitacion').show();
    $('#seccionDirecta').hide();
    
    // Reset productos container con el layout inicial para pedido de habitación
    $('#productosContainer').html('<div class="row mb-2 producto-item">' +
        '<div class="col-md-3">' +
            '<select class="form-select producto-select" name="producto_id[]" required onchange="actualizarPrecio(this)">' +
                '<option value="">Seleccione producto...</option>' +
            '</select>' +
        '</div>' +
        '<div class="col-md-3">' +
            '<select class="form-select cliente-select" name="cliente_id[]" required>' +
                '<option value="">Seleccione habitación primero...</option>' +
            '</select>' +
        '</div>' +
        '<div class="col-md-2">' +
            '<input type="number" class="form-control cantidad-input" name="cantidad[]" placeholder="Cant." min="1" value="1" required oninput="calcularSubtotal(this)">' +
        '</div>' +
        '<div class="col-md-2">' +
            '<input type="number" class="form-control precio-input" name="precio[]" placeholder="Precio" readonly>' +
        '</div>' +
        '<div class="col-md-1">' +
            '<input type="number" class="form-control subtotal-input" name="subtotal[]" placeholder="Subtotal" readonly>' +
        '</div>' +
        '<div class="col-md-1">' +
            '<button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarProducto(this)">' +
                '<i class="fas fa-trash"></i>' +
            '</button>' +
        '</div>' +
    '</div>');
    
    cargarProductos();
    cargarHabitaciones();
    calcularTotal();
    
    // Deshabilitar campos de cliente inicialmente (se habilitarán si se cambia a pedido de habitación)
    $('.cliente-select').prop('required', false).prop('disabled', true);
    
    // Abrir el modal
    const modalElement = document.getElementById('modalPedido');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

// Manejar cambio de tipo de pedido
$('input[name="tipo_pedido"]').change(function() {
    const tipoPedido = $(this).val();
    
    if (tipoPedido === 'directa') {
        $('#seccionHabitacion').hide();
        $('#habitacion_id').prop('required', false).prop('disabled', true);
        $('#seccionDirecta').show();
        
        // Generar referencia automática
        $.get('api/endpoints/pedidos_productos.php?action=generar_referencia', function(response) {
            $('#referencia_venta').val(response.referencia);
        });
        
        // Ocultar y deshabilitar selects de cliente en productos existentes
        $('.cliente-select').each(function() {
            $(this).prop('required', false).prop('disabled', true);
            $(this).parent().hide();
        });
    } else {
        $('#seccionHabitacion').show();
        $('#habitacion_id').prop('required', true).prop('disabled', false);
        $('#seccionDirecta').hide();
        
        // Mostrar y habilitar selects de cliente en productos existentes
        $('.cliente-select').each(function() {
            $(this).prop('required', true).prop('disabled', false);
            $(this).parent().show();
        });
        
        // Recargar habitaciones si se vuelve a pedido de habitación
        cargarHabitaciones();
    }
});

// Funciones auxiliares
function agregarProducto() {
    const tipoPedido = $('input[name="tipo_pedido"]:checked').val();
    let productoHtml;
    
    if (tipoPedido === 'directa') {
        // Para ventas directas, no mostrar campo de cliente
        productoHtml = '<div class="row mb-2 producto-item">' +
            '<div class="col-md-4">' +
                '<select class="form-select producto-select" name="producto_id[]" required onchange="actualizarPrecio(this)">' +
                    '<option value="">Seleccione producto...</option>' +
                '</select>' +
            '</div>' +
            '<div class="col-md-2">' +
                '<input type="number" class="form-control cantidad-input" name="cantidad[]" placeholder="Cant." min="1" value="1" required oninput="calcularSubtotal(this)">' +
            '</div>' +
            '<div class="col-md-2">' +
                '<input type="number" class="form-control precio-input" name="precio[]" placeholder="Precio" readonly>' +
            '</div>' +
            '<div class="col-md-2">' +
                '<input type="number" class="form-control subtotal-input" name="subtotal[]" placeholder="Subtotal" readonly>' +
            '</div>' +
            '<div class="col-md-2">' +
                '<button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarProducto(this)">' +
                    '<i class="fas fa-trash"></i>' +
                '</button>' +
            '</div>' +
        '</div>';
    } else {
        // Para pedidos de habitación, incluir campo de cliente
        productoHtml = '<div class="row mb-2 producto-item">' +
            '<div class="col-md-3">' +
                '<select class="form-select producto-select" name="producto_id[]" required onchange="actualizarPrecio(this)">' +
                    '<option value="">Seleccione producto...</option>' +
                '</select>' +
            '</div>' +
            '<div class="col-md-3">' +
                '<select class="form-select cliente-select" name="cliente_id[]" required>' +
                    '<option value="">Seleccione habitación primero...</option>' +
                '</select>' +
            '</div>' +
            '<div class="col-md-2">' +
                '<input type="number" class="form-control cantidad-input" name="cantidad[]" placeholder="Cant." min="1" value="1" required oninput="calcularSubtotal(this)">' +
            '</div>' +
            '<div class="col-md-2">' +
                '<input type="number" class="form-control precio-input" name="precio[]" placeholder="Precio" readonly>' +
            '</div>' +
            '<div class="col-md-1">' +
                '<input type="number" class="form-control subtotal-input" name="subtotal[]" placeholder="Subtotal" readonly>' +
            '</div>' +
            '<div class="col-md-1">' +
                '<button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarProducto(this)">' +
                    '<i class="fas fa-trash"></i>' +
                '</button>' +
            '</div>' +
        '</div>';
    }
    
    $('#productosContainer').append(productoHtml);
    cargarProductos();
    
    // Si es venta directa, deshabilitar campos de cliente
    if (tipoPedido === 'directa') {
        $('.producto-item:last .cliente-select').prop('required', false).prop('disabled', true);
    }
}

function eliminarProducto(button) {
    $(button).closest('.producto-item').remove();
    calcularTotal();
}

function calcularSubtotal(input) {
    const row = $(input).closest('.producto-item');
    const cantidad = parseInt(row.find('.cantidad-input').val()) || 0;
    const precio = parseFloat(row.find('.precio-input').val()) || 0;
    const subtotal = cantidad * precio;
    
    row.find('.subtotal-input').val(subtotal);
    calcularTotal();
}

function calcularTotal() {
    let total = 0;
    $('.subtotal-input').each(function() {
        total += parseFloat($(this).val()) || 0;
    });
    $('#totalPedido').text('$' + total.toLocaleString('es-CO'));
}

function cargarHabitaciones() {
    const select = $('#habitacion_id');
    select.html('<option value="">Cargando...</option>');
    
    $.get('api/endpoints/reservas.php?estado=confirmada', function(reservasData) {
        const reservas = Array.isArray(reservasData) ? reservasData : (reservasData.records || []);
        
        // Extraer habitaciones únicas de las reservas
        const habitacionesMap = {};
        reservas.forEach(function(reserva) {
            if (reserva.habitacion_id && !habitacionesMap[reserva.habitacion_id]) {
                habitacionesMap[reserva.habitacion_id] = {
                    id: reserva.habitacion_id,
                    numero: reserva.habitacion_numero || ('Hab ' + reserva.habitacion_id),
                    tipo: reserva.habitacion_tipo || 'Standard',
                    reserva_id: reserva.id
                };
            }
        });
        
        let html = '<option value="">Seleccione una habitación...</option>';
        
        Object.values(habitacionesMap).forEach(function(habitacion) {
            html += '<option value="' + habitacion.id + '" data-reserva-id="' + habitacion.reserva_id + '">' + 
                habitacion.numero + ' - ' + habitacion.tipo + '</option>';
        });
        
        select.html(html);
        select.trigger('change');
    });
}

// Manejar cambio de habitación para cargar clientes
$(document).on('change', '#habitacion_id', function() {
    const habitacionId = $(this).val();
    const reservaId = $(this).find(':selected').data('reserva-id');
    
    console.log('DEBUG: reservaId:', reservaId);
    
    if (habitacionId && reservaId) {
        console.log('DEBUG: Cargando huéspedes para reserva:', reservaId);
        // Buscar reserva activa para esta habitación
        $.get('api/endpoints/reserva_huespedes.php?reserva_id=' + reservaId, function(huespedesData) {
            console.log('DEBUG: Respuesta huéspedes:', huespedesData);
            console.log('DEBUG: Respuesta huéspedes:', huespedesData);
            const huespedes = Array.isArray(huespedesData) ? huespedesData : (huespedesData.records || []);
            
            $('.cliente-select').each(function() {
                const select = $(this);
                select.empty().append('<option value="">Seleccione cliente...</option>');
                select.prop('disabled', false).prop('required', true);
                
                huespedes.forEach(function(huesped) {
                    const esTitular = huesped.es_titular ? ' (Titular)' : ' (Acompañante)';
                    select.append('<option value="' + huesped.cliente_id + '">' + huesped.nombre + ' ' + (huesped.apellido || '') + esTitular + '</option>');
                });
            });
        }).fail(function(xhr, status, error) {
            console.error('ERROR cargando huéspedes:', status, error);
        });
    } else {
        $('.cliente-select').each(function() {
            const select = $(this);
            select.empty().append('<option value="">Seleccione habitación primero...</option>');
        });
    }
});

function cargarProductos() {
    $.get('api/endpoints/productos.php', function(data) {
        productos = Array.isArray(data) ? data : (data.records || []);
        
        $('.producto-select').each(function() {
            const select = $(this);
            const currentValue = select.val();
            
            let html = '<option value="">Seleccione un producto...</option>';
            
            productos.forEach(function(producto) {
                if (producto.activo && producto.stock > 0) {
                    html += '<option value="' + producto.id + '" data-precio="' + producto.precio + '" data-stock="' + producto.stock + '">' + 
                        producto.nombre + ' - $' + parseFloat(producto.precio).toLocaleString('es-CO') + ' (Stock: ' + producto.stock + ')' +
                    '</option>';
                }
            });
            
            select.html(html);
            
            if (currentValue) {
                select.val(currentValue);
            }
        });
    });
}

function actualizarPrecio(select) {
    const selectedOption = $(select).find('option:selected');
    const precio = selectedOption.data('precio') || 0;
    const stock = selectedOption.data('stock') || 0;
    const row = $(select).closest('.producto-item');
    const precioInput = row.find('.precio-input');
    const cantidadInput = row.find('.cantidad-input');
    
    precioInput.val(precio);
    
    const cantidad = parseInt(cantidadInput.val()) || 0;
    if (cantidad > stock && stock > 0) {
        cantidadInput.val(stock);
        showNotification('Solo hay ' + stock + ' unidades disponibles', 'warning');
    }
    
    setTimeout(function() {
        calcularSubtotal(cantidadInput[0]);
    }, 10);
}

function handleSearch(event) {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    searchTimeout = setTimeout(function() {
        cargarPedidos(1);
    }, 500);
}

function cargarPedidos(page) {
    // Prevenir llamadas simultáneas (race condition)
    if (isLoadingPedidos) {
        console.log('DEBUG: cargarPedidos already in progress, skipping');
        return;
    }
    
    // Asegurar que page tenga un valor válido
    page = page || 1;
    
    isLoadingPedidos = true;
    console.log('DEBUG: cargarPedidos starting, page:', page);
    
    currentPage = page;
    
    currentFilters.tipo = $('#filtroTipo').val();
    currentFilters.estado = $('#filtroEstado').val();
    currentFilters.fecha = $('#filtroFecha').val();
    currentFilters.busqueda = $('#busqueda').val();
    
    let url = 'api/endpoints/pedidos_productos.php?page=' + page + '&limit=10';
    const params = [];
    
    if (currentFilters.tipo) params.push('tipo=' + currentFilters.tipo);
    if (currentFilters.estado) params.push('estado=' + currentFilters.estado);
    if (currentFilters.fecha) params.push('fecha=' + currentFilters.fecha);
    if (currentFilters.busqueda) params.push('busqueda=' + currentFilters.busqueda);
    
    if (params.length > 0) {
        url += '&' + params.join('&');
    }
    
    $.get(url, function(data) {
        console.log('DEBUG: Response data:', data);
        console.log('DEBUG: data.records:', data.records);
        console.log('DEBUG: Array.isArray(data):', Array.isArray(data));
        
        const grid = $('#pedidosGrid');
        const paginationContainer = $('#paginationContainer');
        grid.empty();
        
        const pedidosList = Array.isArray(data) ? data : (data.records || []);
        const pagination = data.pagination || {};
        
        console.log('DEBUG: pedidosList length:', pedidosList.length);
        
        if (pedidosList.length === 0) {
            grid.append('<div class="col-12"><div class="text-center py-5"><i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i><h5 class="text-muted">No se encontraron pedidos</h5></div></div>');
            paginationContainer.empty();
            return;
        }

        pedidosList.forEach(function(pedido) {
            const estadoBadge = {
                'pendiente': 'warning',
                'en_preparacion': 'info',
                'entregado': 'success',
                'cancelado': 'danger'
            }[pedido.estado] || 'secondary';
            
            const estadoIcon = {
                'pendiente': 'fa-clock',
                'en_preparacion': 'fa-spinner',
                'entregado': 'fa-check-circle',
                'cancelado': 'fa-times-circle'
            }[pedido.estado] || 'fa-question-circle';
            
            grid.append('<div class="col-12 mb-3">' +
                '<div class="card shadow-sm">' +
                    '<div class="card-body">' +
                        '<div class="row align-items-center">' +
                            '<div class="col-md-2">' +
                                '<strong>Pedido #' + pedido.id + '</strong><br>' +
                                '<small class="text-muted">' + new Date(pedido.fecha_pedido).toLocaleString() + '</small>' +
                            '</div>' +
                            '<div class="col-md-3">' +
                                (pedido.tipo_pedido === 'directa' ? 
                                    '<i class="fas fa-shopping-cart me-1"></i> Venta Directa<br><small class="text-muted">Ref: ' + (pedido.referencia_venta || 'N/A') + '</small>' :
                                    '<i class="fas fa-bed me-1"></i> Hab. ' + pedido.habitacion_numero + '<br><small class="text-muted">' + (pedido.cliente_nombre || 'Sin cliente') + '</small>'
                                ) +
                            '</div>' +
                            '<div class="col-md-2">' +
                                '<span class="badge bg-' + (pedido.tipo_pedido === 'directa' ? 'info' : 'primary') + ' me-1">' +
                                    '<i class="fas fa-' + (pedido.tipo_pedido === 'directa' ? 'shopping-cart' : 'bed') + ' me-1"></i>' +
                                    (pedido.tipo_pedido === 'directa' ? 'Venta Directa' : 'Pedido') +
                                '</span><br>' +
                                '<span class="badge bg-' + estadoBadge + '">' +
                                    '<i class="fas ' + estadoIcon + ' me-1"></i>' +
                                    pedido.estado.charAt(0).toUpperCase() + pedido.estado.slice(1).replace('_', ' ') +
                                '</span>' +
                            '</div>' +
                            '<div class="col-md-2">' +
                                '<strong>$' + parseFloat(pedido.total).toLocaleString('es-CO') + '</strong>' +
                            '</div>' +
                            '<div class="col-md-3 text-end">' +
                                '<button class="btn btn-sm btn-outline-primary" onclick="verPedido(' + pedido.id + ')">' +
                                    '<i class="fas fa-eye me-1"></i> Ver' +
                                '</button>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>');
        });
        
        renderPagination(pagination);
        
        // Liberar bandera de carga
        isLoadingPedidos = false;
        console.log('DEBUG: cargarPedidos completed successfully');
    }).fail(function(xhr, status, error) {
        // Liberar bandera incluso en error
        isLoadingPedidos = false;
        console.log('DEBUG: cargarPedidos failed:', error);
    });
}

function renderPagination(pagination) {
    const container = $('#paginationContainer');
    
    if (!pagination || pagination.pages <= 1) {
        container.empty();
        return;
    }
    
    let html = '<ul class="pagination justify-content-center">';
    
    // Botón anterior
    if (pagination.has_prev) {
        html += '<li class="page-item"><a class="page-link" href="#" onclick="cargarPedidos(' + (pagination.page - 1) + '); return false;"><i class="fas fa-chevron-left"></i></a></li>';
    } else {
        html += '<li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-left"></i></span></li>';
    }
    
    // Páginas
    for (let i = 1; i <= pagination.pages; i++) {
        if (i === pagination.page) {
            html += '<li class="page-item active"><span class="page-link">' + i + '</span></li>';
        } else {
            html += '<li class="page-item"><a class="page-link" href="#" onclick="cargarPedidos(' + i + '); return false;">' + i + '</a></li>';
        }
    }
    
    // Botón siguiente
    if (pagination.has_next) {
        html += '<li class="page-item"><a class="page-link" href="#" onclick="cargarPedidos(' + (pagination.page + 1) + '); return false;"><i class="fas fa-chevron-right"></i></a></li>';
    } else {
        html += '<li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-right"></i></span></li>';
    }
    
    html += '</ul>';
    container.html(html);
}

function verPedido(id) {
    $.get('api/endpoints/pedidos_productos.php?id=' + id, function(pedido) {
        var detallesHtml = '<div class="row mb-3">' +
            '<div class="col-md-6">' +
                '<strong>Pedido #' + pedido.id + '</strong><br>' +
                '<small class="text-muted">Fecha: ' + new Date(pedido.fecha_pedido).toLocaleString() + '</small>' +
            '</div>' +
            '<div class="col-md-6 text-end">' +
                '<span class="badge bg-info">' + pedido.estado + '</span>' +
            '</div>' +
        '</div>' +
        '<hr>' +
        '<div class="row mb-3">' +
            '<div class="col-md-4">';
        
        if (pedido.tipo_pedido === 'directa') {
            detallesHtml += '<strong>Tipo:</strong> Venta Directa<br><strong>Ref:</strong> ' + (pedido.referencia_venta || 'N/A');
        } else {
            detallesHtml += '<strong>Habitación:</strong> ' + pedido.habitacion_numero + '<br><strong>Cliente:</strong> ' + (pedido.cliente_nombre || 'N/A');
        }
        
        detallesHtml += '</div>' +
            '<div class="col-md-8">' +
                '<strong>Notas:</strong> ' + (pedido.notas || 'Sin notas') +
            '</div>' +
        '</div>' +
        '<hr>' +
        '<h6>Productos:</h6>' +
        '<div class="table-responsive">' +
            '<table class="table table-sm">' +
                '<thead>' +
                    '<tr>' +
                        '<th>Producto</th>' +
                        '<th>Categoría</th>' +
                        '<th>Cantidad</th>' +
                        '<th>Precio</th>' +
                        '<th>Subtotal</th>' +
                    '</tr>' +
                '</thead>' +
                '<tbody>';
        
        pedido.detalles.forEach(function(detalle) {
            detallesHtml += '<tr>' +
                '<td>' + detalle.producto_nombre + '</td>' +
                '<td>' + detalle.categoria + '</td>' +
                '<td>' + detalle.cantidad + '</td>' +
                '<td>$' + parseFloat(detalle.precio_unitario).toLocaleString('es-CO') + '</td>' +
                '<td>$' + parseFloat(detalle.subtotal).toLocaleString('es-CO') + '</td>' +
            '</tr>';
        });
        
        detallesHtml += '</tbody>' +
                '<tfoot>' +
                    '<tr>' +
                        '<th colspan="4">Total:</th>' +
                        '<th>$' + parseFloat(pedido.total).toLocaleString('es-CO') + '</th>' +
                    '</tr>' +
                '</tfoot>' +
            '</table>' +
        '</div>';
        
        $('#detallesPedido').html(detallesHtml);
        
        var modalElement = document.getElementById('modalVerPedido');
        var modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: true
        });
        modal.show();
    });
}

function showNotification(message, type) {
    const notification = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
        message +
        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
    '</div>';
    
    $('#notification-container').append(notification);
    
    setTimeout(function() {
        $('.alert').fadeOut(function() {
            $(this).remove();
        });
    }, 5000);
}

// Inicialización
$(document).ready(function() {
    cargarPedidos(1);
});

// Manejar envío del formulario
$('#formPedido').on('submit', function(e) {
    e.preventDefault();
    
    const tipoPedido = $('input[name="tipo_pedido"]:checked').val();
    const habitacionId = tipoPedido === 'habitacion' ? $('#habitacion_id').val() : null;
    const referenciaVenta = tipoPedido === 'directa' ? $('#referencia_venta').val() : null;
    const notas = $('#notas').val();
    
    // Validar productos
    const productos = [];
    let valido = true;
    
    $('.producto-item').each(function() {
        const productoSelect = $(this).find('.producto-select');
        const cantidadInput = $(this).find('.cantidad-input');
        
        if (productoSelect.val() && cantidadInput.val()) {
            productos.push({
                producto_id: parseInt(productoSelect.val()),
                cantidad: parseInt(cantidadInput.val()),
                precio_unitario: parseFloat($(this).find('.precio-input').val())
            });
        } else {
            valido = false;
        }
    });
    
    if (!valido || productos.length === 0) {
        showNotification('Por favor, complete todos los productos', 'error');
        return;
    }
    
    const data = {
        tipo_pedido: tipoPedido,
        habitacion_id: habitacionId ? parseInt(habitacionId) : null,
        cliente_id: null,
        referencia_venta: referenciaVenta,
        usuario_id: <?php echo $_SESSION['usuario']['id']; ?>,
        notas: notas,
        detalles: productos
    };
    
    $.ajax({
        url: 'api/endpoints/pedidos_productos.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            $('#modalPedido').modal('hide');
            showNotification(response.message || 'Pedido creado exitosamente', 'success');
            cargarPedidos();
        },
        error: function(xhr) {
            showNotification(xhr.responseJSON?.message || 'Error al guardar pedido', 'error');
        }
    }).fail(function(xhr, status, error) { console.error('ERROR:', status, error); });
});
</script>

<?php include __DIR__ . '/../../../backend/includes/footer.php'; ?>
