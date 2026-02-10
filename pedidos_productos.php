<?php
require_once 'config/database.php';

// Verificar sesión de usuario
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div id="notification-container"></div>
    
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Pedidos de Productos</h1>
                <p class="text-muted mb-0">Gestiona los pedidos de comida y otros productos</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPedido" onclick="abrirModalNuevo()">
                <i class="fas fa-plus me-2"></i>Nuevo Pedido
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="filtroEstado" onchange="cargarPedidos()">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="en_preparacion">En Preparación</option>
                        <option value="entregado">Entregado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="filtroFecha" onchange="cargarPedidos()">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Búsqueda</label>
                    <input type="text" class="form-control" id="busqueda" placeholder="Buscar por habitación o cliente..." onkeyup="cargarPedidos()">
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de pedidos -->
    <div class="row" id="pedidosGrid">
        <!-- Los pedidos se cargarán dinámicamente aquí -->
    </div>
</div>

<!-- Modal Nuevo Pedido -->
<div class="modal fade" id="modalPedido" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPedido" onsubmit="guardarPedido(event)">
                <div class="modal-body">
                    <input type="hidden" id="pedido_id">
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Habitación *</label>
                            <select class="form-select" id="habitacion_id" required onchange="cargarClienteHabitacion()">
                                <option value="">Seleccione...</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Cliente *</label>
                            <select class="form-select" id="cliente_id" required>
                                <option value="">Seleccione habitación primero...</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Notas</label>
                            <textarea class="form-control" id="notas" rows="1"></textarea>
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
                        <div class="row mb-2 producto-item">
                            <div class="col-md-5">
                                <select class="form-select producto-select" required onchange="actualizarPrecio(this)">
                                    <option value="">Seleccione producto...</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control cantidad-input" placeholder="Cant." min="1" value="1" required onchange="calcularSubtotal(this)">
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control precio-input" placeholder="Precio" readonly>
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control subtotal-input" placeholder="Subtotal" readonly>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarProducto(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <strong>Total:</strong>
                                <strong id="totalPedido">$0</strong>
                            </div>
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
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
let productos = [];

$(document).ready(function() {
    cargarProductos();
    cargarClientesActivos();
});

function cargarClientesActivos() {
    // Cargar clientes que tienen reservaciones activas (confirmadas)
    $.get('api/endpoints/reservas.php?estado=confirmada', function(data) {
        const select = $('#cliente_id');
        select.empty().append('<option value="">Seleccione un cliente...</option>');
        
        // Agrupar clientes únicos
        const clientesUnicos = {};
        const reservasList = Array.isArray(data) ? data : (data.records || []);
        
        reservasList.forEach(function(reserva) {
            const clienteKey = `${reserva.cliente_id}-${reserva.cliente_nombre}`;
            if (!clientesUnicos[clienteKey]) {
                clientesUnicos[clienteKey] = {
                    id: reserva.cliente_id,
                    nombre: reserva.cliente_nombre,
                    apellido: reserva.cliente_apellido || '',
                    habitacion: `${reserva.habitacion_numero} (${reserva.habitacion_tipo})`
                };
            }
        });
        
        // Agregar opciones al select
        Object.values(clientesUnicos).forEach(function(cliente) {
            const textoOpcion = `${cliente.nombre} ${cliente.apellido} - Hab: ${cliente.habitacion}`;
            select.append(`<option value="${cliente.id}">${textoOpcion}</option>`);
        });
    });
}

function cargarPedidos() {
    const estado = $('#filtroEstado').val();
    const fecha = $('#filtroFecha').val();
    const busqueda = $('#busqueda').val();
    
    let url = 'api/endpoints/pedidos_productos.php';
    const params = [];
    
    if (estado) params.push(`estado=${estado}`);
    if (fecha) params.push(`fecha=${fecha}`);
    if (busqueda) params.push(`busqueda=${busqueda}`);
    
    if (params.length > 0) {
        url += '?' + params.join('&');
    }
    
    $.get(url, function(data) {
        const grid = $('#pedidosGrid');
        grid.empty();
        
        const pedidosList = Array.isArray(data) ? data : (data.records || []);
        
        if (pedidosList.length === 0) {
            grid.append(`
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No se encontraron pedidos</h5>
                    </div>
                </div>
            `);
            return;
        }

        pedidosList.forEach(pedido => {
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
            
            grid.append(`
                <div class="col-12 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <strong>Pedido #${pedido.id}</strong>
                                    <br>
                                    <small class="text-muted">${new Date(pedido.fecha_pedido).toLocaleString()}</small>
                                </div>
                                <div class="col-md-2">
                                    <i class="fas fa-bed me-1"></i> Hab. ${pedido.habitacion_numero}
                                    <br>
                                    <small class="text-muted">${pedido.cliente_nombre || 'Sin cliente'}</small>
                                </div>
                                <div class="col-md-2">
                                    <span class="badge bg-${estadoBadge}">
                                        <i class="fas ${estadoIcon} me-1"></i>
                                        ${pedido.estado.charAt(0).toUpperCase() + pedido.estado.slice(1).replace('_', ' ')}
                                    </span>
                                </div>
                                <div class="col-md-2">
                                    <strong>$${parseFloat(pedido.total).toLocaleString('es-CO')}</strong>
                                </div>
                                <div class="col-md-4 text-end">
                                    <button class="btn btn-sm btn-outline-primary" onclick="verPedido(${pedido.id})">
                                        <i class="fas fa-eye me-1"></i> Ver
                                    </button>
                                    ${pedido.estado === 'pendiente' ? `
                                        <button class="btn btn-sm btn-outline-info" onclick="actualizarEstado(${pedido.id}, 'en_preparacion')">
                                            <i class="fas fa-spinner me-1"></i> Preparar
                                        </button>
                                    ` : ''}
                                    ${pedido.estado === 'en_preparacion' ? `
                                        <button class="btn btn-sm btn-outline-success" onclick="actualizarEstado(${pedido.id}, 'entregado')">
                                            <i class="fas fa-check me-1"></i> Entregar
                                        </button>
                                    ` : ''}
                                    ${pedido.estado !== 'entregado' ? `
                                        <button class="btn btn-sm btn-outline-danger" onclick="actualizarEstado(${pedido.id}, 'cancelado')">
                                            <i class="fas fa-times me-1"></i> Cancelar
                                        </button>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });
    });
}

function cargarProductos() {
    $.get('api/endpoints/productos.php', function(data) {
        productos = Array.isArray(data) ? data : (data.records || []);
        
        // Actualizar selects de productos
        $('.producto-select').each(function() {
            const select = $(this);
            const currentValue = select.val();
            select.empty().append('<option value="">Seleccione producto...</option>');
            
            productos.forEach(producto => {
                if (producto.activo && producto.stock > 0) {
                    select.append(`<option value="${producto.id}" data-precio="${producto.precio}" data-stock="${producto.stock}">${producto.nombre} - $${parseFloat(producto.precio).toLocaleString('es-CO')} (Stock: ${producto.stock})</option>`);
                }
            });
            
            if (currentValue) {
                select.val(currentValue);
            }
        });
    });
}

function cargarHabitaciones() {
    const select = $('#habitacion_id');
    select.empty().append('<option value="">Cargando...</option>');
    
    // Obtener fecha actual para validar reservas activas
    const hoy = new Date().toISOString().split('T')[0];
    
    // Cargar habitaciones que tienen reservas confirmadas y activas
    $.get(`api/endpoints/reservas.php?estado=confirmada`, function(reservasData) {
        const reservas = Array.isArray(reservasData) ? reservasData : (reservasData.records || []);
        
        // Filtrar reservas que están activas hoy
        const reservasActivas = reservas.filter(reserva => {
            return reserva.fecha_entrada <= hoy && reserva.fecha_salida >= hoy;
        });
        
        if (reservasActivas.length === 0) {
            select.empty().append('<option value="">No hay habitaciones ocupadas actualmente</option>');
            console.log('No hay reservas activas para hoy:', hoy);
            return;
        }
        
        // Obtener IDs de habitaciones con reservas activas
        const habitacionesConReservas = new Set();
        reservasActivas.forEach(reserva => {
            habitacionesConReservas.add(reserva.habitacion_id);
        });
        
        // Ahora cargar los detalles de esas habitaciones específicas
        $.get('api/endpoints/habitaciones.php', function(habitacionesData) {
            const habitaciones = Array.isArray(habitacionesData) ? habitacionesData : (habitacionesData.records || []);
            
            select.empty().append('<option value="">Seleccione...</option>');
            
            let habitacionesEncontradas = 0;
            habitaciones.forEach(habitacion => {
                if (habitacionesConReservas.has(habitacion.id)) {
                    select.append(`<option value="${habitacion.id}">${habitacion.numero} - ${habitacion.tipo}</option>`);
                    habitacionesEncontradas++;
                }
            });
            
            if (habitacionesEncontradas === 0) {
                select.empty().append('<option value="">No se encontraron habitaciones ocupadas</option>');
            }
            
            console.log(`Habitaciones ocupadas encontradas: ${habitacionesEncontradas}`);
            console.log('Habitaciones con reservas activas:', Array.from(habitacionesConReservas));
            console.log('Reservas activas:', reservasActivas.length);
            
        }).fail(function() {
            console.error('Error al cargar detalles de habitaciones');
            select.empty().append('<option value="">Error al cargar habitaciones</option>');
        });
        
    }).fail(function() {
        console.error('Error al cargar reservas confirmadas');
        select.empty().append('<option value="">Error al cargar reservas</option>');
    });
}

function cargarClienteHabitacion() {
    const habitacionId = $('#habitacion_id').val();
    const clienteSelect = $('#cliente_id');
    
    if (!habitacionId) {
        clienteSelect.empty().append('<option value="">Seleccione habitación primero...</option>');
        return;
    }
    
    clienteSelect.empty().append('<option value="">Cargando...</option>');
    
    // Buscar reserva activa para esta habitación
    $.get(`api/endpoints/reservas.php?habitacion_id=${habitacionId}&estado=confirmada&limit=1`, function(reservaData) {
        const reservas = Array.isArray(reservaData) ? reservaData : (reservaData.records || []);
        
        if (reservas.length > 0) {
            const reserva = reservas[0];
            clienteSelect.empty().append('<option value="">Seleccione cliente...</option>');
            
            // Agregar cliente principal
            clienteSelect.append(`<option value="cliente_${reserva.cliente_id}">${reserva.cliente_nombre} (Principal)</option>`);
            
            // Intentar cargar acompañantes desde observaciones
            if (reserva.observaciones) {
                try {
                    const match = reserva.observaciones.match(/ACOMPANANTES:\n([\s\S]*?)(?=\n\n|\n$|$)/);
                    if (match && match[1]) {
                        const acompanantes = JSON.parse(match[1]);
                        acompanantes.forEach((acompanante, index) => {
                            const nombreCompleto = `${acompanante.nombre} ${acompanante.apellido}`;
                            clienteSelect.append(`<option value="acompanante_${index}">${nombreCompleto} (Acompañante)</option>`);
                        });
                        console.log('Acompañantes cargados:', acompanantes.length);
                    }
                } catch (e) {
                    console.log('Error al parsear acompañantes:', e);
                }
            }
            
            // Guardar información de la reserva para uso posterior
            clienteSelect.data('reserva', reserva);
            console.log('Clientes cargados para habitación:', habitacionId);
            
        } else {
            // Buscar última reserva de esta habitación
            $.get(`api/endpoints/reservas.php?habitacion_id=${habitacionId}&limit=1`, function(historialData) {
                const historial = Array.isArray(historialData) ? historialData : (historialData.records || []);
                
                clienteSelect.empty().append('<option value="">Seleccione cliente...</option>');
                
                if (historial.length > 0) {
                    const ultimaReserva = historial[0];
                    clienteSelect.append(`<option value="cliente_${ultimaReserva.cliente_id}">${ultimaReserva.cliente_nombre} (Principal - Última reserva)</option>`);
                    
                    // Intentar cargar acompañantes desde observaciones
                    if (ultimaReserva.observaciones) {
                        try {
                            const match = ultimaReserva.observaciones.match(/ACOMPANANTES:\n([\s\S]*?)(?=\n\n|\n$|$)/);
                            if (match && match[1]) {
                                const acompanantes = JSON.parse(match[1]);
                                acompanantes.forEach((acompanante, index) => {
                                    const nombreCompleto = `${acompanante.nombre} ${acompanante.apellido}`;
                                    clienteSelect.append(`<option value="acompanante_${index}">${nombreCompleto} (Acompañante)</option>`);
                                });
                            }
                        } catch (e) {
                            console.log('Error al parsear acompañantes:', e);
                        }
                    }
                    
                    clienteSelect.data('reserva', ultimaReserva);
                    console.log('Cliente cargado desde historial:', ultimaReserva.cliente_nombre);
                    
                } else {
                    clienteSelect.empty().append('<option value="">Sin clientes encontrados</option>');
                    console.log('No se encontró cliente para esta habitación');
                }
            }).fail(function() {
                clienteSelect.empty().append('<option value="">Error al cargar clientes</option>');
                console.error('Error al cargar historial de reservas');
            });
        }
    }).fail(function() {
        clienteSelect.empty().append('<option value="">Error al cargar clientes</option>');
        console.error('Error al cargar reserva activa');
    });
}

function abrirModalNuevo() {
    $('#modalTitle').text('Nuevo Pedido');
    $('#formPedido')[0].reset();
    $('#pedido_id').val('');
    
    // Resetear el select de cliente
    $('#cliente_id').empty().append('<option value="">Seleccione habitación primero...</option>');
    
    // Cargar habitaciones disponibles
    cargarHabitaciones();
    
    // Reset productos container
    $('#productosContainer').html(`
        <div class="row mb-2 producto-item">
            <div class="col-md-5">
                <select class="form-select producto-select" required onchange="actualizarPrecio(this)">
                    <option value="">Seleccione producto...</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control cantidad-input" placeholder="Cant." min="1" value="1" required onchange="calcularSubtotal(this)">
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control precio-input" placeholder="Precio" readonly>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control subtotal-input" placeholder="Subtotal" readonly>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarProducto(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `);
    
    cargarProductos();
    calcularTotal();
}

function agregarProducto() {
    const productoHtml = `
        <div class="row mb-2 producto-item">
            <div class="col-md-5">
                <select class="form-select producto-select" required onchange="actualizarPrecio(this)">
                    <option value="">Seleccione producto...</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control cantidad-input" placeholder="Cant." min="1" value="1" required onchange="calcularSubtotal(this)">
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control precio-input" placeholder="Precio" readonly>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control subtotal-input" placeholder="Subtotal" readonly>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarProducto(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    $('#productosContainer').append(productoHtml);
    cargarProductos();
}

function eliminarProducto(button) {
    $(button).closest('.producto-item').remove();
    calcularTotal();
}

function actualizarPrecio(select) {
    const $select = $(select);
    const option = $select.find('option:selected');
    const precio = option.data('precio') || 0;
    const stock = option.data('stock') || 0;
    const precioInput = $select.closest('.producto-item').find('.precio-input');
    const cantidadInput = $select.closest('.producto-item').find('.cantidad-input');
    
    precioInput.val(precio);
    
    // Validar stock
    const cantidad = parseInt(cantidadInput.val()) || 0;
    if (cantidad > stock) {
        cantidadInput.val(stock);
        showNotification(`Solo hay ${stock} unidades disponibles`, 'warning');
    }
    
    calcularSubtotal(cantidadInput);
}

function calcularSubtotal(input) {
    const row = $(input).closest('.producto-item');
    const cantidad = parseInt(input.val()) || 0;
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
    $('#totalPedido').text(`$${total.toLocaleString('es-CO')}`);
}

function guardarPedido(e) {
    e.preventDefault();
    
    const habitacionId = $('#habitacion_id').val();
    let clienteId = $('#cliente_id').val() || null;
    const notas = $('#notas').val();
    
    // Extraer solo el número del cliente_id (viene como "cliente_4" o "acompanante_0")
    if (clienteId && clienteId.startsWith('cliente_')) {
        clienteId = parseInt(clienteId.replace('cliente_', ''));
    } else if (clienteId && clienteId.startsWith('acompanante_')) {
        // Para acompañantes, usamos el cliente_id de la reserva
        const reserva = $('#cliente_id').data('reserva');
        if (reserva) {
            clienteId = reserva.cliente_id;
        } else {
            clienteId = null; // Si no hay reserva, no guardamos cliente_id
        }
    } else {
        clienteId = null;
    }
    
    console.log('Guardando pedido:', { habitacionId, clienteId, notas });
    
    // Recolectar productos
    const detalles = [];
    let valido = true;
    
    $('.producto-item').each(function() {
        const productoId = $(this).find('.producto-select').val();
        const cantidad = parseInt($(this).find('.cantidad-input').val()) || 0;
        const precio = parseFloat($(this).find('.precio-input').val()) || 0;
        
        console.log('Producto encontrado:', { productoId, cantidad, precio });
        
        if (!productoId || cantidad <= 0 || precio <= 0) {
            valido = false;
            console.log('Producto inválido:', { productoId, cantidad, precio });
            return false;
        }
        
        detalles.push({
            producto_id: parseInt(productoId),
            cantidad: cantidad,
            precio_unitario: precio
        });
    });
    
    console.log('Detalles finales:', detalles);
    console.log('Válido:', valido, 'Length:', detalles.length);
    
    if (!valido || detalles.length === 0) {
        showNotification('Por favor, complete todos los productos correctamente', 'error');
        return;
    }
    
    const data = {
        habitacion_id: parseInt(habitacionId),
        cliente_id: clienteId,
        usuario_id: <?php echo $_SESSION['usuario']['id']; ?>,
        notas: notas,
        detalles: detalles
    };
    
    console.log('Enviando data:', data);
    
    $.ajax({
        url: 'api/endpoints/pedidos_productos.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            console.log('Respuesta exitosa:', response);
            $('#modalPedido').modal('hide');
            showNotification(response.message || 'Pedido creado exitosamente', 'success');
            cargarPedidos();
        },
        error: function(xhr) {
            console.error('Error al guardar pedido:', xhr);
            console.error('Respuesta:', xhr.responseJSON);
            showNotification(xhr.responseJSON?.message || 'Error al guardar pedido', 'error');
        }
    });
}

function verPedido(id) {
    $.get(`api/endpoints/pedidos_productos.php?id=${id}`, function(pedido) {
        let detallesHtml = `
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Pedido #${pedido.id}</strong><br>
                    <small class="text-muted">Fecha: ${new Date(pedido.fecha_pedido).toLocaleString()}</small>
                </div>
                <div class="col-md-6 text-end">
                    <span class="badge bg-info">${pedido.estado.charAt(0).toUpperCase() + pedido.estado.slice(1).replace('_', ' ')}</span>
                </div>
            </div>
            <hr>
            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>Habitación:</strong> ${pedido.habitacion_numero}<br>
                    <strong>Cliente:</strong> ${pedido.cliente_nombre || 'No especificado'}
                </div>
                <div class="col-md-8">
                    <strong>Notas:</strong> ${pedido.notas || 'Sin notas'}
                </div>
            </div>
            <hr>
            <h6>Productos:</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        pedido.detalles.forEach(detalle => {
            detallesHtml += `
                <tr>
                    <td>${detalle.producto_nombre}</td>
                    <td>${detalle.categoria}</td>
                    <td>${detalle.cantidad}</td>
                    <td>$${parseFloat(detalle.precio_unitario).toLocaleString('es-CO')}</td>
                    <td>$${parseFloat(detalle.subtotal).toLocaleString('es-CO')}</td>
                </tr>
            `;
        });
        
        detallesHtml += `
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4">Total:</th>
                            <th>$${parseFloat(pedido.total).toLocaleString('es-CO')}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;
        
        $('#detallesPedido').html(detallesHtml);
        $('#modalVerPedido').modal('show');
    });
}

function actualizarEstado(id, nuevoEstado) {
    $.ajax({
        url: 'api/endpoints/pedidos_productos.php',
        type: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify({ id: id, estado: nuevoEstado }),
        success: function(response) {
            showNotification(response.message || 'Estado actualizado', 'success');
            cargarPedidos();
        },
        error: function(xhr) {
            showNotification(xhr.responseJSON?.message || 'Error al actualizar estado', 'error');
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
