<?php 
require_once __DIR__ . '/../../../backend/config/database.php';

// Verificar sesión de usuario
if (!isset($_SESSION['usuario'])) {
    require_once dirname(__DIR__, 3) . '/config/env.php';
    header('Location: ' . hotel_tame_url_path('login'));
    exit;
}

include __DIR__ . '/../../../backend/includes/header.php';
include __DIR__ . '/../../../backend/includes/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Reservas de Eventos</h2>
                <button class="btn btn-primary" onclick="abrirModalNuevo()">
                    <i class="fas fa-plus me-2"></i>Nueva Reserva
                </button>
            </div>
            
            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="filtroEvento" class="form-label">Evento</label>
                            <select class="form-select" id="filtroEvento">
                                <option value="">Todos los eventos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filtroEstado" class="form-label">Estado</label>
                            <select class="form-select" id="filtroEstado">
                                <option value="">Todos los estados</option>
                                <option value="confirmada">Confirmada</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="busqueda" class="form-label">Búsqueda</label>
                            <input type="text" class="form-control" id="busqueda" placeholder="Buscar reserva...">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-outline-primary w-100" onclick="cargarReservas()">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Lista de Reservas -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="reservasTable">
                            <thead>
                                <tr>
                                    <th>Evento</th>
                                    <th>Cliente</th>
                                    <th>Fecha Reserva</th>
                                    <th>Personas</th>
                                    <th>Precio Total</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="reservasList">
                                <!-- Las reservas se cargarán aquí -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reserva -->
<div class="modal fade" id="modalReserva" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nueva Reserva de Evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formReserva">
                    <input type="hidden" id="reserva_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="evento_id" class="form-label">Evento</label>
                                <select class="form-select" id="evento_id" required onchange="calcularTotal()">
                                    <option value="">Seleccione un evento...</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cliente_id" class="form-label">Cliente</label>
                                <select class="form-select" id="cliente_id" required>
                                    <option value="">Seleccione un cliente...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cantidad_personas" class="form-label">Cantidad de Personas</label>
                                <input type="number" class="form-control" id="cantidad_personas" min="1" required onchange="calcularTotal()">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="precio_unitario" class="form-label">Precio por Persona</label>
                                <input type="number" class="form-control" id="precio_unitario" step="0.01" min="0" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="precio_total" class="form-label">Precio Total</label>
                                <input type="number" class="form-control" id="precio_total" step="0.01" min="0" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" required>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="confirmada">Confirmada</option>
                                    <option value="cancelada">Cancelada</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="metodo_pago" class="form-label">Método de Pago</label>
                                <select class="form-select" id="metodo_pago">
                                    <option value="">Seleccione...</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta de Crédito</option>
                                    <option value="transferencia">Transferencia Bancaria</option>
                                    <option value="paypal">PayPal</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notas" class="form-label">Notas</label>
                        <textarea class="form-control" id="notas" rows="3" placeholder="Notas adicionales sobre la reserva..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formReserva" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    cargarEventos();
    cargarClientes();
    cargarReservas();
});

function cargarEventos() {
    $.get('api/endpoints/eventos.php?disponible=true', function(data) {
        let html = '<option value="">Seleccione un evento...</option>';
        if (data.records && data.records.length > 0) {
            data.records.forEach(function(evento) {
                html += `<option value="${evento.id}" data-precio="${evento.precio_por_persona}">${evento.nombre} - $${evento.precio_por_persona}/persona</option>`;
            });
        }
        $('#evento_id').html(html);
        
        // También cargar para el filtro
        let filtroHtml = '<option value="">Todos los eventos</option>';
        if (data.records && data.records.length > 0) {
            data.records.forEach(function(evento) {
                filtroHtml += `<option value="${evento.id}">${evento.nombre}</option>`;
            });
        }
        $('#filtroEvento').html(filtroHtml);
    });
}

function cargarClientes() {
    $.get('api/endpoints/clientes.php', function(data) {
        let html = '<option value="">Seleccione un cliente...</option>';
        if (data.records && data.records.length > 0) {
            data.records.forEach(function(cliente) {
                html += `<option value="${cliente.id}">${cliente.nombre} ${cliente.apellido}</option>`;
            });
        }
        $('#cliente_id').html(html);
    });
}

function cargarReservas() {
    const eventoId = $('#filtroEvento').val();
    const estado = $('#filtroEstado').val();
    const busqueda = $('#busqueda').val();
    
    let url = 'api/endpoints/reservas_eventos.php';
    const params = [];
    
    if (eventoId) params.push(`evento_id=${eventoId}`);
    if (estado) params.push(`estado=${estado}`);
    if (busqueda) params.push(`busqueda=${busqueda}`);
    
    if (params.length > 0) {
        url += '?' + params.join('&');
    }
    
    $.get(url, function(data) {
        let html = '';
        if (data.records && data.records.length > 0) {
            data.records.forEach(function(reserva) {
                html += `
                    <tr>
                        <td>${reserva.nombre_evento}</td>
                        <td>${reserva.nombre_cliente} ${reserva.apellido_cliente}</td>
                        <td>${new Date(reserva.fecha_reserva).toLocaleDateString()}</td>
                        <td>${reserva.cantidad_personas}</td>
                        <td>$${parseFloat(reserva.precio_total).toFixed(2)}</td>
                        <td><span class="badge bg-${getEstadoBadgeClass(reserva.estado)}">${reserva.estado}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editarReserva(${reserva.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="eliminarReserva(${reserva.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        } else {
            html = '<tr><td colspan="7" class="text-center">No se encontraron reservas</td></tr>';
        }
        
        $('#reservasList').html(html);
    });
}

function getEstadoBadgeClass(estado) {
    switch(estado) {
        case 'confirmada': return 'success';
        case 'pendiente': return 'warning';
        case 'cancelada': return 'danger';
        default: return 'secondary';
    }
}

function abrirModalNuevo() {
    $('#modalTitle').text('Nueva Reserva de Evento');
    $('#formReserva')[0].reset();
    $('#reserva_id').val('');
    $('#modalReserva').modal('show');
}

function editarReserva(id) {
    $.get(`api/endpoints/reservas_eventos.php?id=${id}`, function(reserva) {
        $('#modalTitle').text('Editar Reserva de Evento');
        $('#reserva_id').val(reserva.id);
        $('#evento_id').val(reserva.evento_id);
        $('#cliente_id').val(reserva.cliente_id);
        $('#cantidad_personas').val(reserva.cantidad_personas);
        $('#precio_unitario').val(reserva.precio_unitario);
        $('#precio_total').val(reserva.precio_total);
        $('#estado').val(reserva.estado);
        $('#metodo_pago').val(reserva.metodo_pago);
        $('#notas').val(reserva.notas);
        $('#modalReserva').modal('show');
    });
}

function calcularTotal() {
    const eventoId = $('#evento_id').val();
    const cantidadPersonas = parseInt($('#cantidad_personas').val()) || 0;
    
    if (eventoId && cantidadPersonas > 0) {
        const selectedOption = $('#evento_id option:selected');
        const precioPorPersona = parseFloat(selectedOption.data('precio')) || 0;
        const precioTotal = precioPorPersona * cantidadPersonas;
        
        $('#precio_unitario').val(precioPorPersona.toFixed(2));
        $('#precio_total').val(precioTotal.toFixed(2));
    } else {
        $('#precio_unitario').val('');
        $('#precio_total').val('');
    }
}

function guardarReserva(e) {
    e.preventDefault();
    
    // Evitar múltiples envíos
    if ($(this).data('submitting')) {
        return false;
    }
    
    const id = $('#reserva_id').val();
    
    const data = {
        evento_id: $('#evento_id').val(),
        cliente_id: $('#cliente_id').val(),
        cantidad_personas: parseInt($('#cantidad_personas').val()),
        precio_unitario: parseFloat($('#precio_unitario').val()),
        precio_total: parseFloat($('#precio_total').val()),
        estado: $('#estado').val(),
        metodo_pago: $('#metodo_pago').val(),
        notas: $('#notas').val()
    };
    
    if (id) {
        data.id = id;
    }
    
    // Marcar formulario como enviando
    $('#formReserva').data('submitting', true);
    $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
    
    $.ajax({
        url: id ? 'api/endpoints/reservas_eventos.php' : 'api/endpoints/reservas_eventos.php',
        type: id ? 'PUT' : 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            $('#modalReserva').modal('hide');
            showNotification(response.message || 'Reserva guardada exitosamente', 'success');
            cargarReservas();
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Error al guardar reserva';
            showNotification(errorMsg, 'error');
        },
        complete: function() {
            // Restaurar botón
            $('#formReserva').data('submitting', false);
            $('button[type="submit"]').prop('disabled', false).html('Guardar');
        }
    });
}

function eliminarReserva(id) {
    if (confirm('¿Está seguro de eliminar esta reserva?')) {
        $.ajax({
            url: 'api/endpoints/reservas_eventos.php',
            type: 'DELETE',
            contentType: 'application/json',
            data: JSON.stringify({ id: id }),
            success: function(response) {
                showNotification(response.message || 'Reserva eliminada', 'success');
                cargarReservas();
            },
            error: function(xhr) {
                showNotification(xhr.responseJSON?.message || 'Error al eliminar', 'error');
            }
        });
    }
}

function showNotification(message, type) {
    // Simple notification system
    const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
    const notification = $(`
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 1050;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('body').append(notification);
    
    // Auto-remove after 3 seconds
    setTimeout(function() {
        notification.fadeOut(300, function() {
            $(this).remove();
        });
    }, 3000);
}

// Event listeners
$('#formReserva').on('submit', guardarReserva);
</script>

<?php include __DIR__ . '/../../../backend/includes/footer.php'; ?>
