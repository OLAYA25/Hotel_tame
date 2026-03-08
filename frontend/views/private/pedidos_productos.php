<?php
require_once __DIR__ . '/../../../backend/config/database.php';

// Verificar sesión de usuario
if (!isset($_SESSION['usuario'])) {
    header('Location: /Hotel_tame/login');
    exit;
}
include __DIR__ . '/../../../backend/includes/header.php';
include __DIR__ . '/../../../backend/includes/sidebar.php';
?>

<div class="main-content">
    <div id="notification-container"></div>
    
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Pedidos de Productos</h1>
                <p class="text-muted mb-0">Gestiona los pedidos de comida y otros productos</p>
            </div>
            <button class="btn btn-primary" onclick="abrirModalNuevo()">
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
                    <input type="text" class="form-control" id="busqueda" placeholder="Buscar por habitación o cliente..." onkeyup="handleSearch(event)">
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de pedidos -->
    <div class="row" id="pedidosGrid">
        <!-- Los pedidos se cargarán dinámicamente aquí -->
    </div>
    
    <!-- Paginación -->
    <nav aria-label="Paginación de pedidos" class="mt-4" id="paginationContainer">
        <div class="d-flex justify-content-center">
            <!-- La paginación se cargará dinámicamente aquí -->
        </div>
    </nav>
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
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Habitación *</label>
                            <select class="form-select" id="habitacion_id" required onchange="cargarClienteHabitacion()">
                                <option value="">Seleccione...</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
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
                            <div class="col-md-3">
                                <select class="form-select producto-select" required onchange="actualizarPrecio(this)">
                                    <option value="">Seleccione producto...</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select cliente-select" required>
                                    <option value="">Seleccione cliente...</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control cantidad-input" placeholder="Cant." min="1" value="1" required oninput="calcularSubtotal(this)">
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control precio-input" placeholder="Precio" readonly>
                            </div>
                            <div class="col-md-1">
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
let reservaActual = null; // Variable global para almacenar la reserva actual

$(document).ready(function() {
    cargarProductos();
    cargarClientesActivos();
    
    // Event listeners adicionales para cálculo en tiempo real
    $(document).on('input', '.cantidad-input', function() {
        calcularSubtotal(this);
    });
    
    $(document).on('change', '.cantidad-input', function() {
        calcularSubtotal(this);
    });
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
        Object.values(clientesUnicos).forEach(cliente => {
            select.append(`<option value="${cliente.id}">${cliente.nombre} ${cliente.apellido} - ${cliente.habitacion}</option>`);
        });
    });
}

// Variables globales para paginación
let currentPage = 1;
let searchTimeout;
let currentFilters = {
    estado: '',
    fecha: '',
    busqueda: ''
};

$(document).ready(function() {
    cargarPedidos();
});

function cargarPedidos(page = 1) {
    currentPage = page;
    
    // Actualizar filtros actuales
    currentFilters.estado = $('#filtroEstado').val();
    currentFilters.fecha = $('#filtroFecha').val();
    currentFilters.busqueda = $('#busqueda').val();
    
    let url = `api/endpoints/pedidos_productos.php?page=${page}&limit=10`;
    const params = [];
    
    if (currentFilters.estado) params.push(`estado=${currentFilters.estado}`);
    if (currentFilters.fecha) params.push(`fecha=${currentFilters.fecha}`);
    if (currentFilters.busqueda) params.push(`busqueda=${currentFilters.busqueda}`);
    
    if (params.length > 0) {
        url += '&' + params.join('&');
    }
    
    $.get(url, function(data) {
        const grid = $('#pedidosGrid');
        const paginationContainer = $('#paginationContainer');
        grid.empty();
        
        const pedidosList = Array.isArray(data) ? data : (data.records || []);
        const pagination = data.pagination || {};
        
        if (pedidosList.length === 0) {
            grid.append(`
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No se encontraron pedidos</h5>
                    </div>
                </div>
            `);
            paginationContainer.empty();
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
        
        // Renderizar paginación
        renderPagination(pagination);
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
        html += `<li class="page-item">
                    <a class="page-link" href="#" onclick="cargarPedidos(${pagination.page - 1}); return false;">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                 </li>`;
    } else {
        html += `<li class="page-item disabled">
                    <span class="page-link">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                 </li>`;
    }
    
    // Páginas
    for (let i = 1; i <= pagination.pages; i++) {
        if (i === pagination.page) {
            html += `<li class="page-item active">
                        <span class="page-link">${i}</span>
                     </li>`;
        } else {
            html += `<li class="page-item">
                        <a class="page-link" href="#" onclick="cargarPedidos(${i}); return false;">${i}</a>
                     </li>`;
        }
    }
    
    // Botón siguiente
    if (pagination.has_next) {
        html += `<li class="page-item">
                    <a class="page-link" href="#" onclick="cargarPedidos(${pagination.page + 1}); return false;">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                 </li>`;
    } else {
        html += `<li class="page-item disabled">
                    <span class="page-link">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                 </li>`;
    }
    
    html += '</ul>';
    container.html(html);
}

function cargarProductos() {
    $.get('api/endpoints/productos.php', function(data) {
        productos = Array.isArray(data) ? data : (data.records || []);
        
        // Actualizar selects de productos con Select2
        $('.producto-select').each(function() {
            const select = $(this);
            const currentValue = select.val();
            
            // Inicializar Select2 si no está inicializado
            if (!select.hasClass('select2-hidden-accessible')) {
                select.select2({
                    placeholder: 'Seleccione un producto...',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: '#modalPedido .modal-body',
                    minimumInputLength: 0,
                    language: {
                        noResults: function() {
                            return 'No se encontraron productos';
                        },
                        searching: function() {
                            return 'Buscando...';
                        },
                        inputTooShort: function() {
                            return 'Ingrese 0 o más caracteres';
                        }
                    }
                });
            }
            
            // Actualizar opciones
            select.empty().append('<option value="">Seleccione un producto...</option>');
            
            productos.forEach(producto => {
                if (producto.activo && producto.stock > 0) {
                    const option = new Option(
                        `${producto.nombre} - $${parseFloat(producto.precio).toLocaleString('es-CO')} (Stock: ${producto.stock})`,
                        producto.id,
                        false,
                        false
                    );
                    // Agregar datos personalizados
                    $(option).data('precio', producto.precio);
                    $(option).data('stock', producto.stock);
                    select.append(option);
                }
            });
            
            // Restaurar valor anterior si existe
            if (currentValue) {
                select.val(currentValue);
            }
            
            // Disparar evento change para actualizar Select2
            select.trigger('change');
        });
    });
}

function cargarHabitaciones() {
    const select = $('#habitacion_id');
    select.empty().append('<option value="">Cargando...</option>');
    
    // Inicializar Select2 para el campo de habitación
    select.select2({
        placeholder: 'Seleccione una habitación...',
        allowClear: true,
        width: '100%',
        dropdownParent: '#modalPedido .modal-body',
        minimumInputLength: 0, // Mostrar todos los resultados al abrir
        language: {
            noResults: function() {
                return 'No se encontraron habitaciones';
            },
            searching: function() {
                return 'Buscando...';
            },
            inputTooShort: function() {
                return 'Ingrese 0 o más caracteres';
            }
        }
    });
    
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
            
            // Filtrar solo las habitaciones que tienen reservas activas
            const habitacionesActivas = habitaciones.filter(h => habitacionesConReservas.has(h.id));
            
            select.empty().append('<option value="">Seleccione una habitación...</option>');
            
            habitacionesActivas.forEach(habitacion => {
                const option = new Option(`${habitacion.numero} - ${habitacion.tipo}`, habitacion.id, false, false);
                select.append(option);
            });
            
            // Disparar el evento change para actualizar Select2
            select.trigger('change');
            
            console.log('Habitaciones ocupadas encontradas:', habitacionesActivas.length);
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
    
    if (!habitacionId) {
        // Limpiar todos los selects de cliente de los productos
        $('.cliente-select').each(function() {
            $(this).empty().append('<option value="">Seleccione habitación primero...</option>');
        });
        return;
    }
    
    // Guardar las selecciones actuales de clientes antes de actualizar
    const seleccionesActuales = [];
    $('.cliente-select').each(function() {
        const valorActual = $(this).val();
        if (valorActual) {
            seleccionesActuales.push(valorActual);
        }
    });
    
    // Mostrar loading en todos los selects
    $('.cliente-select').each(function() {
        $(this).empty().append('<option value="">Cargando...</option>');
    });
    
    // Buscar reserva activa para esta habitación (solo confirmadas, no canceladas)
    $.get(`api/endpoints/reservas.php?habitacion_id=${habitacionId}&estado=confirmada&limit=1`, function(reservaData) {
        console.log('Reservas encontradas para habitación', habitacionId, ':', reservaData); // Debug
        
        const reservas = Array.isArray(reservaData) ? reservaData : (reservaData.records || []);
        
        if (reservas.length > 0) {
            const reserva = reservas[0];
            console.log('Reserva seleccionada:', reserva); // Debug
            
            // Almacenar la reserva en variable global
            reservaActual = reserva;
            
            // Actualizar todos los selects de cliente de los productos
            $('.cliente-select').each(function(index) {
                const select = $(this);
                select.empty().append('<option value="">Seleccione cliente...</option>');
                
                // Agregar cliente principal
                select.append(`<option value="cliente_${reserva.cliente_id}">${reserva.cliente_nombre} ${reserva.cliente_apellido || ''} (Principal)</option>`);
                
                // Intentar cargar acompañantes desde observaciones (usando el mismo patrón que reservas.php)
                if (reserva.observaciones) {
                    console.log('Observaciones de la reserva:', reserva.observaciones); // Debug
                    try {
                        // Buscar el JSON de acompañantes en las observaciones (mismo patrón que reservas.php)
                        const obsText = reserva.observaciones;
                        const acompanantesMatch = obsText.match(/ACOMPANANTES:\n([\s\S]*?)(?=\n\n|\n$|$)/);
                        
                        console.log('Match de acompañantes:', acompanantesMatch); // Debug
                        
                        if (acompanantesMatch && acompanantesMatch[1]) {
                            const acompanantesJSON = acompanantesMatch[1];
                            const acompanantes = JSON.parse(acompanantesJSON);
                            
                            console.log('Acompañantes parseados:', acompanantes); // Debug
                            
                            acompanantes.forEach((acompanante, index) => {
                                const nombreCompleto = `${acompanante.nombre} ${acompanante.apellido}`;
                                select.append(`<option value="acompanante_${index}">${nombreCompleto} (Acompañante)</option>`);
                            });
                            console.log('Acompañantes cargados:', acompanantes.length);
                        } else {
                            console.log('No se encontró JSON de acompañantes en las observaciones');
                        }
                    } catch (e) {
                        console.log('Error al parsear acompañantes:', e);
                    }
                } else {
                    console.log('La reserva no tiene observaciones');
                }
                
                // Guardar información de la reserva para uso posterior
                select.data('reserva', reserva);
                
                // Restaurar la selección anterior si existe y es válida
                if (seleccionesActuales[index]) {
                    const valorAnterior = seleccionesActuales[index];
                    // Verificar si el valor anterior todavía existe en las opciones
                    if (select.find(`option[value="${valorAnterior}"]`).length > 0) {
                        select.val(valorAnterior);
                        console.log('Restaurada selección anterior:', valorAnterior);
                    }
                }
            });
            
            console.log('Clientes cargados para habitación:', habitacionId);
            
        } else {
            // Buscar última reserva de esta habitación (incluyendo canceladas para mostrar historial)
            $.get(`api/endpoints/reservas.php?habitacion_id=${habitacionId}&limit=1`, function(historialData) {
                const historial = Array.isArray(historialData) ? historialData : (historialData.records || []);
                
                $('.cliente-select').each(function(index) {
                    const select = $(this);
                    select.empty().append('<option value="">Seleccione cliente...</option>');
                    
                    if (historial.length > 0) {
                        const ultimaReserva = historial[0];
                        const estadoTexto = ultimaReserva.estado === 'cancelada' ? '(Cancelada)' : '(Última reserva)';
                        select.append(`<option value="cliente_${ultimaReserva.cliente_id}">${ultimaReserva.cliente_nombre} ${ultimaReserva.cliente_apellido || ''} (Principal - ${estadoTexto})</option>`);
                        
                        // Almacenar la reserva en variable global
                        reservaActual = ultimaReserva;
                        
                        // Intentar cargar acompañantes desde observaciones
                        if (ultimaReserva.observaciones) {
                            console.log('Observaciones de última reserva:', ultimaReserva.observaciones); // Debug
                            try {
                                const obsText = ultimaReserva.observaciones;
                                const acompanantesMatch = obsText.match(/ACOMPANANTES:\n([\s\S]*?)(?=\n\n|\n$|$)/);
                                
                                console.log('Match de acompañantes (historial):', acompanantesMatch); // Debug
                                
                                if (acompanantesMatch && acompanantesMatch[1]) {
                                    const acompanantesJSON = acompanantesMatch[1];
                                    const acompanantes = JSON.parse(acompanantesJSON);
                                    
                                    console.log('Acompañantes parseados (historial):', acompanantes); // Debug
                                    
                                    acompanantes.forEach((acompanante, index) => {
                                        const nombreCompleto = `${acompanante.nombre} ${acompanante.apellido}`;
                                        select.append(`<option value="acompanante_${index}">${nombreCompleto} (Acompañante)</option>`);
                                    });
                                } else {
                                    console.log('No se encontró JSON de acompañantes en observaciones del historial');
                                }
                            } catch (e) {
                                console.log('Error al parsear acompañantes (historial):', e);
                            }
                        } else {
                            console.log('La última reserva no tiene observaciones');
                        }
                        
                        // Guardar información de la reserva
                        select.data('reserva', ultimaReserva);
                        
                        // Restaurar la selección anterior si existe y es válida
                        if (seleccionesActuales[index]) {
                            const valorAnterior = seleccionesActuales[index];
                            if (select.find(`option[value="${valorAnterior}"]`).length > 0) {
                                select.val(valorAnterior);
                                console.log('Restaurada selección anterior (historial):', valorAnterior);
                            }
                        }
                    } else {
                        select.empty().append('<option value="">No hay reservas para esta habitación</option>');
                    }
                });
            }).fail(function() {
                $('.cliente-select').each(function() {
                    $(this).empty().append('<option value="">Error al cargar historial</option>');
                });
            });
        }
    }).fail(function() {
        $('.cliente-select').each(function() {
            $(this).empty().append('<option value="">Error al cargar reserva activa</option>');
        });
    });
}

function abrirModalNuevo() {
    console.log('Abriendo modal de nuevo pedido...');
    $('#modalTitle').text('Nuevo Pedido');
    $('#formPedido')[0].reset();
    $('#pedido_id').val('');
    
    // Cargar habitaciones disponibles
    cargarHabitaciones();
    
    // Reset productos container
    $('#productosContainer').html(`
        <div class="row mb-2 producto-item">
            <div class="col-md-3">
                <select class="form-select producto-select" required onchange="actualizarPrecio(this)">
                    <option value="">Seleccione producto...</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select cliente-select" required>
                    <option value="">Seleccione habitación primero...</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control cantidad-input" placeholder="Cant." min="1" value="1" required oninput="calcularSubtotal(this)">
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control precio-input" placeholder="Precio" readonly>
            </div>
            <div class="col-md-1">
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
    
    // Abrir el modal con la API de Bootstrap 5
    const modalElement = document.getElementById('modalPedido');
    const modal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: true
    });
    modal.show();
}

function agregarProducto() {
    const productoHtml = `
        <div class="row mb-2 producto-item">
            <div class="col-md-3">
                <select class="form-select producto-select" required onchange="actualizarPrecio(this)">
                    <option value="">Seleccione producto...</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select cliente-select" required>
                    <option value="">Seleccione habitación primero...</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control cantidad-input" placeholder="Cant." min="1" value="1" required oninput="calcularSubtotal(this)">
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control precio-input" placeholder="Precio" readonly>
            </div>
            <div class="col-md-1">
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
    
    // Si ya hay una habitación seleccionada, cargar los clientes para el nuevo producto
    const habitacionId = $('#habitacion_id').val();
    if (habitacionId) {
        console.log('Cargando clientes para nuevo producto, habitación:', habitacionId);
        
        // Guardar las selecciones actuales antes de cargar
        const seleccionesActuales = [];
        $('.cliente-select').each(function() {
            const valorActual = $(this).val();
            if (valorActual) {
                seleccionesActuales.push(valorActual);
            }
        });
        
        cargarClienteHabitacion();
        
        // Después de cargar los clientes, establecer el cliente por defecto para el nuevo producto
        setTimeout(() => {
            const nuevoSelect = $('.cliente-select').last();
            
            // Si hay un solo cliente (solo el principal sin acompañantes), seleccionarlo por defecto
            const opciones = nuevoSelect.find('option');
            if (opciones.length === 2) { // Solo "Seleccione cliente..." y una opción de cliente
                const clienteOption = opciones.eq(1); // La segunda opción
                if (clienteOption.val() && clienteOption.val().startsWith('cliente_')) {
                    nuevoSelect.val(clienteOption.val());
                    console.log('Cliente por defecto seleccionado:', clienteOption.val());
                }
            }
            // Si hay múltiples clientes pero todos los productos anteriores usan el mismo, usar ese
            else if (seleccionesActuales.length > 0 && seleccionesActuales.every(val => val === seleccionesActuales[0])) {
                const clienteComun = seleccionesActuales[0];
                if (nuevoSelect.find(`option[value="${clienteComun}"]`).length > 0) {
                    nuevoSelect.val(clienteComun);
                    console.log('Cliente común seleccionado por defecto:', clienteComun);
                }
            }
        }, 100);
    }
}

function eliminarProducto(button) {
    $(button).closest('.producto-item').remove();
    calcularTotal();
}

function actualizarPrecio(select) {
    const $select = $(select);
    
    // Para Select2, obtener la opción seleccionada de manera diferente
    let precio = 0;
    let stock = 0;
    
    if ($select.hasClass('select2-hidden-accessible')) {
        // Es un Select2, obtener datos de la opción seleccionada
        const selectedOption = $select.find('option:selected');
        if (selectedOption.length > 0) {
            precio = selectedOption.data('precio') || 0;
            stock = selectedOption.data('stock') || 0;
        }
    } else {
        // Es un select normal
        const option = $select.find('option:selected');
        precio = option.data('precio') || 0;
        stock = option.data('stock') || 0;
    }
    
    const precioInput = $select.closest('.producto-item').find('.precio-input');
    const cantidadInput = $select.closest('.producto-item').find('.cantidad-input');
    
    console.log('Actualizando precio:', { precio, stock }); // Debug
    
    precioInput.val(precio);
    
    // Validar stock
    const cantidad = parseInt(cantidadInput.val()) || 0;
    if (cantidad > stock && stock > 0) {
        cantidadInput.val(stock);
        showNotification(`Solo hay ${stock} unidades disponibles`, 'warning');
    }
    
    // Forzar el cálculo del subtotal
    setTimeout(() => {
        calcularSubtotal(cantidadInput[0]);
    }, 10);
}

function calcularSubtotal(input) {
    const row = $(input).closest('.producto-item');
    const cantidad = parseInt($(input).val()) || 0;
    const precio = parseFloat(row.find('.precio-input').val()) || 0;
    const subtotal = cantidad * precio;
    
    // Solo debug si hay cambios significativos
    if (cantidad > 0 && precio > 0) {
        console.log('Calculando subtotal:', { cantidad, precio, subtotal });
    }
    
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
    const notas = $('#notas').val();
    
    console.log('Guardando pedido:', { habitacionId, notas });
    
    // Recolectar productos con sus clientes individuales
    const detalles = [];
    let valido = true;
    
    $('.producto-item').each(function() {
        const productoId = $(this).find('.producto-select').val();
        const cantidad = parseInt($(this).find('.cantidad-input').val()) || 0;
        const precio = parseFloat($(this).find('.precio-input').val()) || 0;
        let clienteProductoId = $(this).find('.cliente-select').val();
        
        console.log('Producto encontrado:', { productoId, cantidad, precio, clienteProductoId });
        
        if (!productoId || cantidad <= 0 || precio <= 0 || !clienteProductoId) {
            valido = false;
            console.log('Producto inválido:', { productoId, cantidad, precio, clienteProductoId });
            return false;
        }
        
        // Extraer solo el número del cliente_id (viene como "cliente_4" o "acompanante_0")
        let clienteIdFinal = null;
        if (clienteProductoId.startsWith('cliente_')) {
            clienteIdFinal = parseInt(clienteProductoId.replace('cliente_', ''));
        } else if (clienteProductoId.startsWith('acompanante_')) {
            // Para acompañantes, necesitamos obtener el cliente_id correspondiente
            const acompananteIndex = parseInt(clienteProductoId.replace('acompanante_', ''));
            
            if (reservaActual && reservaActual.observaciones) {
                try {
                    // Parsear los acompañantes desde las observaciones
                    const obsText = reservaActual.observaciones;
                    const acompanantesMatch = obsText.match(/ACOMPANANTES:\n([\s\S]*?)(?=\n\n|\n$|$)/);
                    
                    if (acompanantesMatch && acompanantesMatch[1]) {
                        const acompanantes = JSON.parse(acompanantesMatch[1]);
                        
                        if (acompanantes[acompananteIndex]) {
                            const acompanante = acompanantes[acompananteIndex];
                            const nombreAcompanante = acompanante.nombre;
                            const apellidoAcompanante = acompanante.apellido;
                            
                            // Buscar el cliente_id correspondiente en la base de datos
                            console.log('Buscando cliente para:', nombreAcompanante, apellidoAcompanante);
                            $.ajax({
                                url: `api/endpoints/clientes.php?search=${encodeURIComponent(nombreAcompanante)}`,
                                type: 'GET',
                                async: false, // Síncrono para obtener el dato antes de continuar
                                success: function(clienteData) {
                                    console.log('Respuesta de búsqueda de cliente:', clienteData);
                                    const clientes = Array.isArray(clienteData) ? clienteData : (clienteData.records || []);
                                    console.log('Clientes encontrados:', clientes.length, clientes);
                                    
                                    // Filtrar por apellido si hay múltiples resultados
                                    let clienteEncontrado = null;
                                    if (clientes.length > 0) {
                                        if (clientes.length === 1) {
                                            clienteEncontrado = clientes[0];
                                        } else {
                                            // Buscar el que coincide con el apellido
                                            clienteEncontrado = clientes.find(c => 
                                                c.apellido && c.apellido.toLowerCase() === apellidoAcompanante.toLowerCase()
                                            );
                                            if (!clienteEncontrado) {
                                                clienteEncontrado = clientes[0]; // Fallback al primero
                                            }
                                        }
                                        
                                        clienteIdFinal = clienteEncontrado.id;
                                        console.log('Cliente de acompañante encontrado:', clienteIdFinal, 'para:', nombreAcompanante, apellidoAcompanante);
                                    } else {
                                        console.log('Cliente no encontrado para acompañante:', nombreAcompanante, apellidoAcompanante);
                                        // Si no existe, crearlo
                                        console.log('Intentando crear cliente para:', nombreAcompanante, apellidoAcompanante);
                                        $.ajax({
                                            url: 'api/endpoints/clientes_final.php',
                                            type: 'POST',
                                            contentType: 'application/json',
                                            data: JSON.stringify({
                                                nombre: nombreAcompanante,
                                                apellido: apellidoAcompanante,
                                                tipo_documento: acompanante.tipo_documento,
                                                numero_documento: acompanante.numero_documento,
                                                email: acompanante.email,
                                                telefono: acompanante.telefono
                                            }),
                                            async: false,
                                            success: function(response) {
                                                clienteIdFinal = response.cliente_id;
                                                console.log('Cliente creado para acompañante:', clienteIdFinal);
                                            },
                                            error: function(xhr) {
                                                console.log('Error al crear cliente para acompañante:', xhr.responseJSON);
                                                clienteIdFinal = reservaActual.cliente_id; // Fallback
                                            }
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    console.log('Error al buscar cliente para acompañante:', xhr.responseJSON);
                                    clienteIdFinal = reservaActual.cliente_id; // Fallback
                                }
                            });
                        } else {
                            console.log('Índice de acompañante no encontrado:', acompananteIndex);
                            clienteIdFinal = reservaActual.cliente_id; // Fallback al cliente principal
                        }
                    } else {
                        console.log('No se encontraron acompañantes en observaciones');
                        clienteIdFinal = reservaActual.cliente_id; // Fallback al cliente principal
                    }
                } catch (e) {
                    console.log('Error al parsear acompañantes:', e);
                    clienteIdFinal = reservaActual.cliente_id; // Fallback al cliente principal
                }
            } else {
                clienteIdFinal = reservaActual ? reservaActual.cliente_id : null;
                console.log('No hay reserva global para acompañante, usando fallback');
            }
        }
        
        detalles.push({
            producto_id: parseInt(productoId),
            cantidad: cantidad,
            precio_unitario: precio,
            cliente_id: clienteIdFinal // Cada producto tiene su propio cliente
        });
    });
    
    console.log('Detalles finales:', detalles);
    console.log('Válido:', valido, 'Length:', detalles.length);
    
    if (!valido || detalles.length === 0) {
        showNotification('Por favor, complete todos los productos y seleccione cliente para cada uno', 'error');
        return;
    }
    
    const data = {
        habitacion_id: parseInt(habitacionId),
        cliente_id: null, // No hay cliente único para el pedido, cada producto tiene el suyo
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
                    <strong>Cliente(s):</strong> 
                    <span id="clientesResumen">Cargando...</span>
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
                            <th>Cliente</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        // Procesar clientes únicos para el resumen
        const clientesUnicos = new Map();
        pedido.detalles.forEach(detalle => {
            if (detalle.cliente_id) {
                const clienteKey = detalle.cliente_id;
                if (!clientesUnicos.has(clienteKey)) {
                    clientesUnicos.set(clienteKey, {
                        id: detalle.cliente_id,
                        nombre: detalle.cliente_nombre || 'Cliente ' + detalle.cliente_id,
                        apellido: detalle.cliente_apellido || ''
                    });
                }
            }
        });
        
        // Mostrar resumen de clientes
        let clientesResumenHtml = '';
        if (clientesUnicos.size > 0) {
            const clientesArray = Array.from(clientesUnicos.values());
            clientesResumenHtml = clientesArray.map(cliente => 
                `${cliente.nombre} ${cliente.apellido || ''}`
            ).join(', ');
        } else {
            clientesResumenHtml = 'No especificado';
        }
        
        pedido.detalles.forEach(detalle => {
            const clienteNombre = detalle.cliente_nombre ? 
                `${detalle.cliente_nombre} ${detalle.cliente_apellido || ''}` : 
                'No especificado';
            
            detallesHtml += `
                <tr>
                    <td>${detalle.producto_nombre}</td>
                    <td>${detalle.categoria}</td>
                    <td>${clienteNombre}</td>
                    <td>${detalle.cantidad}</td>
                    <td>$${parseFloat(detalle.precio_unitario).toLocaleString('es-CO')}</td>
                    <td>$${parseFloat(detalle.subtotal).toLocaleString('es-CO')}</td>
                </tr>
            `;
        });
        
        // Actualizar el resumen de clientes después de procesar
        setTimeout(() => {
            $('#clientesResumen').html(clientesResumenHtml);
        }, 10);
        
        detallesHtml += `
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5">Total:</th>
                            <th>$${parseFloat(pedido.total).toLocaleString('es-CO')}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;
        
        $('#detallesPedido').html(detallesHtml);
        
        // Abrir el modal con la API de Bootstrap 5
        const modalElement = document.getElementById('modalVerPedido');
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: true
        });
        modal.show();
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

function handleSearch(event) {
    // Limpiar timeout anterior
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    // Esperar 500ms después de que el usuario deje de escribir
    searchTimeout = setTimeout(() => {
        cargarPedidos(1); // Volver a la primera página al buscar
    }, 500);
}
</script>

<style>
/* Estilos para Select2 en el modal de pedidos */
#modalPedido .select2-container {
    width: 100% !important;
}

#modalPedido .select2-container--open .select2-dropdown {
    z-index: 1055;
}

#modalPedido .select2-search--dropdown .select2-search__field {
    width: 100%;
    padding: 8px;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}

#modalPedido .select2-results__option {
    padding: 8px;
}

#modalPedido .select2-results__option--highlighted {
    background-color: #0d6efd;
    color: white;
}

#modalPedido .select2-selection {
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    min-height: 38px;
}

#modalPedido .select2-selection__rendered {
    padding: 8px 12px;
    line-height: 1.5;
}

#modalPedido .select2-selection__placeholder {
    color: #6c757d;
}
</style>

<?php include __DIR__ . '/../../../backend/includes/footer.php'; ?>
