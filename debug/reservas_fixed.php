<?php
// Archivo temporal para probar sin errores de sintaxis
session_start();
require_once 'config/database.php';

// Aquí va el contenido original sin los errores de sintaxis
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas - Sistema de Gestión Hotelera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .cliente-option {
            border-left: 3px solid #007bff;
        }
        .hover-bg-light:hover {
            background-color: #f8f9fa !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">
                        <i class="fas fa-calendar-check me-2"></i>Gestión de Reservas
                    </h1>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-info btn-sm" onclick="verificarActualizacionesAutomaticas()" title="Verificar actualizaciones automáticas">
                            <i class="fas fa-sync"></i> Verificar
                        </button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalReserva" onclick="abrirModalNuevo()">
                            <i class="fas fa-plus me-2"></i>Nueva Reserva
                        </button>
                    </div>
                </div>
                
                <div id="reservasList" class="row">
                    <!-- Las reservas se cargarán aquí -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Reserva -->
    <div class="modal fade" id="modalReserva" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nueva Reserva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formReserva" onsubmit="guardarReserva(event)">
                        <input type="hidden" id="reserva_id">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cliente *</label>
                                <div class="d-flex gap-2">
                                    <select class="flex-grow-1" id="cliente_id" required style="width: 100%;">
                                        <option value="">Seleccione un cliente...</option>
                                    </select>
                                    <button type="button" class="btn btn-outline-primary" onclick="abrirModalCliente()" title="Crear Nuevo Cliente">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Habitación *</label>
                                <select class="form-control" id="habitacion_id" required>
                                    <option value="">Seleccione una habitación...</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Entrada *</label>
                                <input type="date" class="form-control" id="fecha_entrada" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Salida *</label>
                                <input type="date" class="form-control" id="fecha_salida" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Método de Pago</label>
                                <select class="form-control" id="metodo_pago">
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta</option>
                                    <option value="transferencia">Transferencia</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estado</label>
                                <select class="form-control" id="estado">
                                    <option value="confirmada">Confirmada</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="cancelada">Cancelada</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formReserva" class="btn btn-primary">Guardar Reserva</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        // Variables globales
        let reservas = [];
        let habitaciones = [];
        
        $(document).ready(function() {
            cargarReservas();
        });
        
        function cargarReservas() {
            $.get('api/endpoints/reservas.php', function(data) {
                const list = $('#reservasList');
                list.empty();
                const reservasList = Array.isArray(data) ? data : (data.records || []);
                reservas = reservasList;
                
                if (reservasList.length === 0) {
                    list.append('<div class="col-12"><div class="alert alert-info text-center">No hay reservas registradas</div></div>');
                    return;
                }
                
                reservasList.forEach((reserva) => {
                    list.append(`
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0">Reserva #${reserva.id}</h6>
                                        <span class="badge bg-${getEstadoColor(reserva.estado)}">${reserva.estado}</span>
                                    </div>
                                    <div class="text-muted small">
                                        <div><i class="fas fa-calendar me-1"></i> ${reserva.fecha_entrada} - ${reserva.fecha_salida}</div>
                                        <div><i class="fas fa-door-open me-1"></i> Hab. ${reserva.habitacion_numero}</div>
                                        <div><i class="fas fa-user me-1"></i> ${reserva.numero_huespedes || 1} huésped(es)</div>
                                    </div>
                                    <div class="d-flex gap-1 mt-2">
                                        <button class="btn btn-sm btn-outline-primary" onclick="editarReserva(${reserva.id})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarReserva(${reserva.id})">
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
        
        function getEstadoColor(estado) {
            const colores = {
                'confirmada': 'success',
                'pendiente': 'warning',
                'cancelada': 'danger'
            };
            return colores[estado] || 'secondary';
        }
        
        function abrirModalNuevo() {
            $('#modalTitle').text('Nueva Reserva');
            $('#formReserva')[0].reset();
            $('#reserva_id').val('');
            const today = new Date().toISOString().split('T')[0];
            $('#fecha_entrada').val(today);
            $('#fecha_entrada').attr('min', today);
            $('#fecha_salida').attr('min', today);
            cargarHabitaciones();
            $('#metodo_pago').val('efectivo');
            
            $('#modalReserva').modal('show');
            setTimeout(() => {
                cargarClientes();
            }, 300);
        }
        
        function cargarClientes() {
            console.log('Cargando clientes...');
            $.get('api/endpoints/clientes_final.php', function(data) {
                console.log('Clientes recibidos:', data);
                const select = $('#cliente_id');
                
                if (select.length === 0) {
                    console.error('No se encontró el elemento #cliente_id');
                    return;
                }
                
                select.empty().append('<option value="">Seleccione un cliente...</option>');
                const clientesList = Array.isArray(data) ? data : (data.records || []);
                console.log('Clientes procesados:', clientesList.length);
                
                window.clientesDataList = clientesList;
                
                clientesList.forEach(cliente => {
                    const nombreCompleto = `${cliente.nombre || ''} ${cliente.apellido || ''}`.trim();
                    const option = $(`<option value="${cliente.id}">${nombreCompleto}</option>`);
                    select.append(option);
                });
                
                // Inicializar Select2
                try {
                    select.select2({
                        theme: 'bootstrap-5',
                        placeholder: 'Buscar cliente...',
                        allowClear: true,
                        width: '100%',
                        dropdownParent: '#modalReserva .modal-body',
                        minimumInputLength: 0,
                        language: {
                            noResults: function() {
                                return 'No se encontraron clientes';
                            },
                            searching: function() {
                                return 'Buscando...';
                            }
                        }
                    });
                    console.log('Select2 inicializado correctamente');
                } catch (e) {
                    console.error('Error al inicializar Select2:', e);
                }
            }).fail(function(error) {
                console.error('Error al cargar clientes:', error);
            });
        }
        
        function cargarHabitaciones() {
            const start = $('#fecha_entrada').val() || '';
            const end = $('#fecha_salida').val() || '';
            $.get(`api/endpoints/habitaciones_disponibles.php?start=${start}&end=${end}`, function(data) {
                const select = $('#habitacion_id');
                select.empty().append('<option value="">Seleccione una habitación...</option>');
                const habitacionesList = Array.isArray(data) ? data : (data.records || []);
                habitaciones = habitacionesList;
                
                habitacionesList.forEach(hab => {
                    select.append(`<option value="${hab.id}">${hab.numero} - ${hab.tipo}</option>`);
                });
            });
        }
        
        function editarReserva(id) {
            // Implementar edición de reserva
            console.log('Editar reserva:', id);
        }
        
        function eliminarReserva(id) {
            if (confirm('¿Está seguro de eliminar esta reserva?')) {
                // Implementar eliminación de reserva
                console.log('Eliminar reserva:', id);
            }
        }
        
        function guardarReserva(e) {
            e.preventDefault();
            // Implementar guardado de reserva
            console.log('Guardar reserva');
        }
        
        function abrirModalCliente() {
            // Implementar modal de cliente
            console.log('Abrir modal cliente');
        }
        
        function verificarActualizacionesAutomaticas() {
            // Implementar verificación
            console.log('Verificando actualizaciones');
        }
    </script>
</body>
</html>
