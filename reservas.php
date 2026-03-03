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
                <h1>Reservas</h1>
                <p class="text-muted mb-0">Gestiona las reservas del hotel</p>
                <small class="text-muted" id="ultimaActualizacion">
                    <i class="fas fa-sync-alt me-1"></i>
                    Las reservas se actualizan automáticamente
                </small>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-info btn-sm" onclick="verificarActualizacionesAutomaticas()" title="Verificar actualizaciones automáticas">
                    <i class="fas fa-sync"></i> Verificar
                </button>
                <button class="btn btn-primary" onclick="abrirModalNuevo()">
                    <i class="fas fa-plus me-2"></i>Nueva Reserva
                </button>
            </div>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-search me-2"></i>Búsqueda y Filtros
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Buscar Cliente</label>
                            <input type="text" class="form-control" id="buscarCliente" placeholder="Nombre o apellido">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Habitación</label>
                            <input type="text" class="form-control" id="buscarHabitacion" placeholder="Número">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="filtroEstado">
                                <option value="">Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="confirmada">Confirmada</option>
                                <option value="cancelada">Cancelada</option>
                                <option value="completada">Completada</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fechaInicio">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fechaFin">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label d-block">&nbsp;</label>
                            <div class="btn-group w-100">
                                <button class="btn btn-primary" onclick="aplicarFiltros()">
                                    <i class="fas fa-search"></i>
                                </button>
                                <button class="btn btn-outline-secondary" onclick="limpiarFiltros()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Habitaciones Ocupadas Actualmente -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-warning bg-opacity-10 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Habitaciones Ocupadas Actualmente</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" id="habitacionesOcupadasTable">
                            <thead>
                                <tr>
                                    <th>Habitación</th>
                                    <th>Tipo</th>
                                    <th>Cliente</th>
                                    <th>Entrada</th>
                                    <th>Salida</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="habitacionesOcupadasList">
                                <!-- Las habitaciones ocupadas se cargarán aquí -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de tarjetas para reservas -->
    <div class="row">
        <div class="col-12">
            <!-- Controles de paginación -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <span class="text-muted">Mostrando <span id="inicioRegistros">0</span> - <span id="finRegistros">0</span> de <span id="totalRegistros">0</span> reservas</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 me-2">Registros por página:</label>
                    <select class="form-select form-select-sm" id="registrosPorPagina" style="width: auto;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
            
            <!-- Contenedor de reservas -->
            <div id="reservasList">
                <!-- Las tarjetas se cargarán dinámicamente aquí -->
            </div>
            
            <!-- Controles de paginación inferior -->
            <div class="d-flex justify-content-center mt-3">
                <nav aria-label="Paginación de reservas">
                    <ul class="pagination mb-0" id="paginacionReservas">
                        <!-- Los botones de página se generarán dinámicamente -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>

<!-- Modal -->
<div class="modal fade" id="modalReserva" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nueva Reserva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formReserva" onsubmit="guardarReserva(event)">
                <div class="modal-body">
                    <input type="hidden" id="reserva_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Entrada *</label>
                            <input type="date" class="form-control" id="fecha_entrada" required onchange="cargarHabitaciones(); calcularPrecio();">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Salida *</label>
                            <input type="date" class="form-control" id="fecha_salida" required onchange="cargarHabitaciones(); calcularPrecio();">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Habitación *</label>
                            <div class="d-flex align-items-center">
                                <select class="flex-grow-1" id="habitacion_id" required style="width: 100%;">
                                    <option value="">Seleccione una habitación...</option>
                                </select>
                            </div>
                            <div id="capacidadInfo" class="small text-muted mt-1" style="display: none;">
                                Capacidad máxima: <span id="capacidadMaxima">-</span> huéspedes
                            </div>
                            <div id="habitacionesDebug" class="small text-muted mt-1" style="white-space:pre-wrap;display:none;"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cliente *</label>
                            <div class="d-flex align-items-center">
                                <select class="flex-grow-1" id="cliente_id" required style="width: 100%;">
                                    <option value="">Seleccione un cliente...</option>
                                </select>
                                <button type="button" class="btn btn-outline-primary" onclick="abrirModalCliente()" title="Crear Nuevo Cliente">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Número de Huéspedes *</label>
                            <input type="number" class="form-control" id="numero_huespedes" required min="1" max="10" onchange="validarCapacidad()">
                            <small class="text-muted d-block">No debe exceder la capacidad de la habitación seleccionada</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Precio Total</label>
                            <input type="number" class="form-control" id="precio_total" step="0.01" placeholder="0.00">
                            <small class="text-muted d-block">Puedes modificar este precio para aplicar descuentos especiales</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Motivo de Viaje</label>
                            <select class="form-select" id="motivo_viaje">
                                <option value="">Seleccione un motivo...</option>
                                <option value="turismo">Turismo</option>
                                <option value="negocios">Negocios</option>
                                <option value="trabajo">Trabajo</option>
                                <option value="vacaciones">Vacaciones</option>
                                <option value="conferencia">Conferencia</option>
                                <option value="convencion">Convención</option>
                                <option value="visita_familiar">Visita Familiar</option>
                                <option value="tratamiento_medico">Tratamiento Médico</option>
                                <option value="estudio">Estudio</option>
                                <option value="deporte">Deporte</option>
                                <option value="otros">Otros</option>
                            </select>
                            <small class="text-muted d-block">Información específica de esta reserva</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Notas Adicionales</label>
                            <textarea class="form-control" id="notas_reserva" rows="3" placeholder="Observaciones especiales para esta reserva..."></textarea>
                        </div>
                    </div>

                    <hr>
                    
                    <!-- Sección de Acompañantes -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">
                                <i class="fas fa-users me-2"></i>Acompañantes de la Reserva
                                <span class="badge bg-secondary ms-2" id="contadorAcompanantes">0</span>
                            </h6>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="agregarAcompananteReserva()">
                                    <i class="fas fa-plus me-1"></i>Agregar Acompañante
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="abrirModalBusquedaPersonas()">
                                    <i class="fas fa-search me-1"></i>Buscar Persona
                                </button>
                            </div>
                        </div>
                        
                        <!-- Información de capacidad -->
                        <div class="alert alert-info py-2 mb-3" id="capacidadAlert">
                            <i class="fas fa-info-circle me-2"></i>
                            <span id="textoCapacidad">Seleccione una habitación para ver la capacidad</span>
                        </div>
                        
                        <!-- Lista de acompañantes -->
                        <div id="acompanantesReservaContainer" class="border rounded p-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                            <p class="text-muted mb-0 text-center">No hay acompañantes registrados</p>
                        </div>
                        
                        <div class="small text-muted mt-2">
                            <i class="fas fa-lightbulb"></i> 
                            Los acompañantes son personas registradas como clientes. Puede buscar personas existentes o crear nuevas.
                            La capacidad máxima de la habitación no debe ser excedida.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estado *</label>
                        <select class="form-select" id="estado" required>
                            <option value="pendiente">Pendiente</option>
                            <option value="confirmada">Confirmada</option>
                            <option value="cancelada">Cancelada</option>
                            <option value="completada">Completada</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Método de Pago *</label>
                        <select class="form-select" id="metodo_pago" required>
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta de crédito</option>
                            <option value="transferencia">Transferencia</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" rows="3"></textarea>
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

<!-- Modal Historial de Pedidos -->
<div class="modal fade" id="modalHistorialPedidos" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-shopping-cart me-2"></i>
                    Historial de Pedidos
                    <small class="text-muted ms-2" id="infoReserva"></small>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="historialPedidosContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando historial de pedidos...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cliente -->
<div class="modal fade" id="modalCliente" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCliente" onsubmit="guardarCliente(event)">
                    <input type="hidden" id="cliente_form_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Apellido *</label>
                            <input type="text" class="form-control" id="apellido" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo Documento *</label>
                            <select class="form-select" id="tipo_documento" required>
                                <option value="">Seleccione...</option>
                                <option value="DNI">DNI</option>
                                <option value="Pasaporte">Pasaporte</option>
                                <option value="Cedula">Cedula</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Número Documento *</label>
                            <input type="text" class="form-control" id="numero_documento" required>
                            <input type="hidden" id="numero_documento_full">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="toggleMostrarDocumento" onchange="toggleDocumentoVisibility()">
                                <label class="form-check-label small text-muted" for="toggleMostrarDocumento">Mostrar número</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono *</label>
                            <input type="tel" class="form-control" id="telefono" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Nacimiento *</label>
                            <input type="date" class="form-control" id="fecha_nacimiento" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ciudad *</label>
                            <input type="text" class="form-control" id="ciudad" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nacionalidad *</label>
                            <select class="form-control" id="nacionalidad" required>
                                <option value="">Seleccione un país...</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Motivo de Viaje</label>
                            <select class="form-select" id="motivo_viaje">
                                <option value="turismo">Turismo</option>
                                <option value="negocios">Negocios</option>
                                <option value="conferencia">Conferencia</option>
                                <option value="convencion">Convención</option>
                                <option value="visita_familiar">Visita Familiar</option>
                                <option value="tratamiento_medico">Tratamiento Médico</option>
                                <option value="estudio">Estudio</option>
                                <option value="deporte">Deporte</option>
                                <option value="otros">Otros</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dirección *</label>
                        <textarea class="form-control" id="direccion" rows="2" required></textarea>
                    </div>

                    <hr>
                    
                    <!-- Sección de Acompañantes -->
                    <!-- <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Acompañantes</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="agregarAcompanante()">
                                <i class="fas fa-plus me-1"></i>Agregar Acompañante
                            </button>
                        </div>
                        <div id="acompanantesContainer" class="border rounded p-3 bg-light">
                            <p class="text-muted mb-0 text-center">No hay acompañantes registrados</p>
                        </div>
                    </div> -->
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formCliente" class="btn btn-primary">Guardar Cliente</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Búsqueda de Personas -->
<div class="modal fade" id="modalBusquedaPersonas" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-search me-2"></i>
                    Buscar Persona para Acompañante
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Buscar Persona para Acompañante</label>
                    <select class="form-control" id="busquedaPersonaSelect" style="width: 100%;">
                        <option value="">Buscar por nombre, apellido o documento...</option>
                    </select>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Comience a escribir para buscar personas existentes en el sistema
                    </small>
                </div>
                
                <div id="infoPersonaSeleccionada" class="mt-3" style="display: none;">
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong id="nombrePersonaSeleccionada"></strong>
                                <div class="small text-muted" id="detallesPersonaSeleccionada"></div>
                            </div>
                            <button type="button" class="btn btn-success btn-sm" onclick="confirmarPersonaSeleccionada()">
                                <i class="fas fa-plus me-1"></i>Agregar como Acompañante
                            </button>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="text-center">
                    <button type="button" class="btn btn-outline-primary" onclick="crearNuevaPersonaAcompanante()">
                        <i class="fas fa-user-plus me-2"></i>
                        Crear Nueva Persona
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<script>
let habitaciones = [];
let reservas = [];

// Variables de paginación
let paginaActual = 1;
let registrosPorPagina = 10;
let totalReservas = 0;
let totalPaginas = 0;

// Variables de filtros
let todasLasReservas = [];
let reservasFiltradas = [];

// Variables para acompañantes
let acompanantesTemporales = [];
let capacidadMaximaHabitacion = 0;
let habitacionSeleccionada = null;
let modoAcompanante = false; // Para saber si el modal cliente se usa para acompañante

function verificarActualizacionesAutomaticas() {
    // Refrescar la página
    location.reload();
}

$(document).ready(function() {
    // Actualizar estados automáticamente al cargar la página
    $.get('api/endpoints/actualizar_estados_reservas.php', function(response) {
        console.log('Estados actualizados:', response);
    });
    
    // Verificar disponibilidad de Select2 al cargar la página
    console.log('=== VERIFICACIÓN INICIAL ===');
    console.log('jQuery cargado:', typeof $ !== 'undefined');
    console.log('Select2 disponible:', typeof $.fn.select2);
    
    // Cargar reservas primero para que la lógica de disponibilidad tenga datos
    cargarReservas(function() {
        cargarClientes();
        cargarHabitaciones();
    });
    
    // Event listener para el formulario de cliente
    $('#formCliente').on('submit', guardarCliente);
    
    // Event listener para cambio de registros por página
    $('#registrosPorPagina').on('change', cambiarRegistrosPorPagina);
    
    // Event listeners para búsqueda en tiempo real
    $('#buscarCliente').on('input', function() {
        if ($(this).val().length >= 2 || $(this).val().length === 0) {
            aplicarFiltros();
        }
    });
    
    $('#buscarHabitacion').on('input', function() {
        if ($(this).val().length >= 1 || $(this).val().length === 0) {
            aplicarFiltros();
        }
    });
    
    $('#filtroEstado, #fechaInicio, #fechaFin').on('change', function() {
        aplicarFiltros();
    });
    
    // Verificar cada 5 minutos si hay reservas que necesitan actualizarse
    setInterval(verificarActualizacionesAutomaticas, 300000); // 5 minutos = 300000 ms
});

function cargarReservas(callback) {
    console.log('Cargando reservas...');
    
    // Obtener registros por página del select
    registrosPorPagina = parseInt($('#registrosPorPagina').val()) || 10;
    
    $.get('api/endpoints/reservas.php', function(data) {
        console.log('Reservas recibidas:', data);
        const list = $('#reservasList');
        list.empty();

        const todasReservasData = Array.isArray(data) ? data : (data.records || []);
        console.log('Total de reservas procesadas:', todasReservasData.length);
        
        // Guardar todas las reservas para filtrado
        todasLasReservas = todasReservasData;
        reservas = todasReservasData;
        
        // Aplicar filtros si hay alguno activo
        const reservasFiltradasData = aplicarFiltrosInternos(todasLasReservas);
        
        totalReservas = reservasFiltradasData.length;
        totalPaginas = Math.ceil(totalReservas / registrosPorPagina);
        
        // Actualizar información de paginación
        actualizarInfoPaginacion();
        
        if (reservasFiltradasData.length === 0) {
            list.append('<div class="col-12"><div class="alert alert-info text-center">No se encontraron reservas con los filtros aplicados</div></div>');
            $('#paginacionReservas').empty();
            if (callback) callback();
            return;
        }
        
        // Obtener reservas de la página actual
        const inicio = (paginaActual - 1) * registrosPorPagina;
        const fin = Math.min(inicio + registrosPorPagina, totalReservas);
        const reservasPagina = reservasFiltradasData.slice(inicio, fin);
        
        console.log(`Mostrando página ${paginaActual} de ${totalPaginas} (${reservasPagina.length} reservas filtradas de ${totalReservas})`);
        
        // Renderizar reservas de la página actual
        renderizarReservasPagina(reservasPagina);
        
        // Generar controles de paginación
        generarPaginacion();
        
        if (typeof callback === 'function') callback();
        
        // Cargar habitaciones ocupadas
        cargarHabitacionesOcupadas();
    }).fail(function(error) {
        console.error('Error cargando reservas:', error);
        showNotification('Error al cargar las reservas', 'error');
    });
}

function renderizarReservasPagina(reservasList) {
    const list = $('#reservasList');
    
    // Verificar si hay reservas que se actualizaron automáticamente
    const reservasCompletadas = reservasList.filter(r => r.estado === 'completada');
    if (reservasCompletadas.length > 0) {
        const hoy = new Date().toISOString().split('T')[0];
        const actualizadasHoy = reservasCompletadas.filter(r => r.fecha_salida < hoy);
        if (actualizadasHoy.length > 0) {
            showNotification(`${actualizadasHoy.length} reserva(s) se actualizaron automáticamente a "Completada"`, 'info');
        }
    }

                reservasList.forEach(reserva => {
        const estadoBadge = {
            'pendiente': 'warning',
            'confirmada': 'success',
            'cancelada': 'danger',
            'completada': 'secondary'
        }[reserva.estado] || 'secondary';

        const estadoTexto = reserva.estado.charAt(0).toUpperCase() + reserva.estado.slice(1);
        
        const fechaEntrada = new Date(reserva.fecha_entrada);
        const fechaSalida = new Date(reserva.fecha_salida);
        const dias = reserva.noches ?? Math.max(1, Math.ceil((fechaSalida - fechaEntrada) / (1000 * 60 * 60 * 24)));
        
        // Definir iconos y texto para método de pago
        const pagoIconos = {
            'efectivo': '<i class="fas fa-money-bill-wave"></i>',
            'tarjeta': '<i class="fas fa-credit-card"></i>',
            'transferencia': '<i class="fas fa-exchange-alt"></i>'
        };
        
        const pagoTextos = {
            'efectivo': 'Efectivo',
            'tarjeta': 'Tarjeta',
            'transferencia': 'Transferencia'
        };
        
        const pagoIcon = pagoIconos[reserva.metodo_pago] || '<i class="fas fa-money-bill-wave"></i>';
        const pagoTexto = pagoTextos[reserva.metodo_pago] || 'Efectivo';
        
        list.append(`
            <div class="col-12 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                                        <i class="fas fa-calendar-check fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="card-title mb-1">#${reserva.id} - ${reserva.cliente_nombre} ${reserva.cliente_apellido || ''}</h6>
                                        <span class="badge bg-${estadoBadge}">${estadoTexto}</span>
                                    </div>
                                </div>
                                <small class="text-muted d-block" id="acompanantes-info-${reserva.id}">
                                    <i class="fas fa-users me-1"></i> 
                                    <span class="acompanantes-count">Cargando...</span>
                                </small>
                            </div>
                            <div class="col-md-3">
                                <h5 class="text-primary mb-1">
                                    <i class="fas fa-door-open me-2"></i> ${reserva.habitacion_numero}
                                </h5>
                                <small class="text-muted d-block">${reserva.habitacion_tipo}</small>
                                <small class="text-muted d-block">
                                    <i class="fas fa-user-friends me-1"></i> 
                                    ${reserva.numero_huespedes || reserva.num_huespedes || 1} huésped${(reserva.numero_huespedes || reserva.num_huespedes || 1) > 1 ? 'es' : ''}
                                </small>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">
                                    <i class="fas fa-calendar me-1"></i>
                                    ${reserva.fecha_entrada} - ${reserva.fecha_salida}
                                    <span class="badge bg-secondary ms-1">${dias} noche${dias > 1 ? 's' : ''}</span>
                                </small>
                                <small class="text-muted d-block text-nowrap" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${pagoIcon} ${pagoTexto}</small>
                            </div>
                            <div class="col-md-2 text-end">
                                <h4 class="text-primary mb-2">$${parseFloat(reserva.total ?? reserva.precio_total ?? 0).toLocaleString('es-CO')}</h4>
                                <div class="d-flex gap-2 justify-content-end">
                                    <button class="btn btn-outline-primary btn-sm" onclick="editarReserva(${reserva.id})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="eliminarReserva(${reserva.id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `);
        
        // Cargar información de acompañantes para esta reserva
        cargarAcompanantesReservaInfo(reserva.id);
    });
}

function actualizarInfoPaginacion() {
    const inicio = (paginaActual - 1) * registrosPorPagina + 1;
    const fin = Math.min(paginaActual * registrosPorPagina, totalReservas);
    
    $('#inicioRegistros').text(totalReservas > 0 ? inicio : 0);
    $('#finRegistros').text(fin);
    $('#totalRegistros').text(totalReservas);
}

function generarPaginacion() {
    const paginacion = $('#paginacionReservas');
    paginacion.empty();
    
    if (totalPaginas <= 1) return;
    
    let html = '';
    
    // Botón anterior
    html += `<li class="page-item ${paginaActual === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="${paginaActual > 1 ? 'cambiarPagina(' + (paginaActual - 1) + ')' : 'return false'}">Anterior</a>
    </li>`;
    
    // Números de página
    const maxPaginasMostrar = 5;
    let inicioPagina = Math.max(1, paginaActual - Math.floor(maxPaginasMostrar / 2));
    let finPagina = Math.min(totalPaginas, inicioPagina + maxPaginasMostrar - 1);
    
    if (inicioPagina > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(1)">1</a></li>`;
        if (inicioPagina > 2) {
            html += `<li class="page-item disabled"><a class="page-link" href="#">...</a></li>`;
        }
    }
    
    for (let i = inicioPagina; i <= finPagina; i++) {
        html += `<li class="page-item ${i === paginaActual ? 'active' : ''}">
            <a class="page-link" href="#" onclick="cambiarPagina(${i})">${i}</a>
        </li>`;
    }
    
    if (finPagina < totalPaginas) {
        if (finPagina < totalPaginas - 1) {
            html += `<li class="page-item disabled"><a class="page-link" href="#">...</a></li>`;
        }
        html += `<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(${totalPaginas})">${totalPaginas}</a></li>`;
    }
    
    // Botón siguiente
    html += `<li class="page-item ${paginaActual === totalPaginas ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="${paginaActual < totalPaginas ? 'cambiarPagina(' + (paginaActual + 1) + ')' : 'return false'}">Siguiente</a>
    </li>`;
    
    paginacion.html(html);
}

function cambiarPagina(pagina) {
    paginaActual = pagina;
    cargarReservas();
}

function cambiarRegistrosPorPagina() {
    paginaActual = 1; // Resetear a primera página
    cargarReservas();
}

function cargarHabitacionesOcupadas() {
    const hoy = new Date().toISOString().split('T')[0];
    
    $.get(`api/endpoints/reservas.php?fecha_actual=${hoy}`, function(data) {
        const tbody = $('#habitacionesOcupadasList');
        tbody.empty();
        
        const reservasList = Array.isArray(data) ? data : (data.records || []);
        
        // Filtrar reservas activas (solo confirmadas) para hoy
        const reservasActivas = reservasList.filter(reserva => {
            const fechaEntrada = new Date(reserva.fecha_entrada);
            const fechaSalida = new Date(reserva.fecha_salida);
            const hoy = new Date();
            
            // Reserva está activa si está confirmada y hoy está entre check-in y antes de check-out
            // En hoteles, el día de salida la habitación se libera para el próximo huésped
            return reserva.estado === 'confirmada' &&
                   hoy >= fechaEntrada && hoy < fechaSalida;
        });
        
        if (reservasActivas.length === 0) {
            tbody.append('<tr><td colspan="7" class="text-center text-muted">No hay habitaciones ocupadas actualmente</td></tr>');
            return;
        }
        
        reservasActivas.forEach(reserva => {
            const fechaEntrada = new Date(reserva.fecha_entrada).toLocaleDateString('es-CO');
            const fechaSalida = new Date(reserva.fecha_salida).toLocaleDateString('es-CO');
            const estadoBadge = reserva.estado === 'confirmada' ? 'success' : 'warning';
            
            tbody.append(`
                <tr>
                    <td><span class="badge bg-info">${reserva.habitacion_numero}</span></td>
                    <td>${reserva.habitacion_tipo}</td>
                    <td>${reserva.cliente_nombre} ${reserva.cliente_apellido || ''}</td>
                    <td>${fechaEntrada}</td>
                    <td>${fechaSalida}</td>
                    <td><span class="badge bg-${estadoBadge}">${reserva.estado}</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="verHistorialPedidos(${reserva.habitacion_id}, '${reserva.habitacion_numero}', '${reserva.cliente_nombre} ${reserva.cliente_apellido || ''}')" title="Ver historial de pedidos">
                            <i class="fas fa-shopping-cart"></i>
                        </button>
                    </td>
                </tr>
            `);
        });
    });
}

function cargarClientes() {
    console.log('Cargando clientes...');
    const select = $('#cliente_id');
    
    if (select.length === 0) {
        console.error('No se encontró el elemento #cliente_id');
        return;
    }
    
    // Evitar recarga si ya está inicializado
    if (select.data('select2') && select.find('option').length > 1) {
        console.log('Select2 de clientes ya inicializado, evitando recarga');
        return;
    }
    
    // Usar endpoint final con la misma estructura que los endpoints funcionales
    $.get('api/endpoints/clientes_final.php', function(data) {
        console.log('Clientes recibidos:', data);
        
        select.empty().append('<option value="">Seleccione un cliente...</option>');
        const clientesList = Array.isArray(data) ? data : (data.records || []);
        console.log('Clientes procesados:', clientesList.length);
        
        // Guardar referencia global para usar en templates de Select2
        window.clientesDataList = clientesList;
        
        // Agregar todos los clientes al select
        clientesList.forEach(cliente => {
            const nombreCompleto = `${cliente.nombre || ''} ${cliente.apellido || ''}`.trim();
            const documento = cliente.documento || '';
            const email = cliente.email || '';
            
            // Crear option con atributos data para búsqueda mejorada
            const option = $(`<option value="${cliente.id}">${nombreCompleto}</option>`);
            option.attr('data-documento', documento);
            option.attr('data-email', email);
            option.attr('data-nombre', cliente.nombre || '');
            option.attr('data-apellido', cliente.apellido || '');
            
            select.append(option);
        });
        
        // Destruir Select2 si ya existe
        try {
            if (select.data('select2')) {
                select.select2('destroy');
                select.removeData('select2');
                select.removeClass('select2-hidden-accessible');
            }
        } catch (e) {
            console.log('Error al destruir Select2:', e);
        }
        
        // Inicializar Select2 con búsqueda mejorada
        try {
            select.select2({
                theme: 'bootstrap-5',
                placeholder: 'Escriba para buscar cliente...',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#modalReserva'),
                minimumInputLength: 0, // Mostrar todos los resultados al abrir
                language: {
                    noResults: function() {
                        return 'No se encontraron clientes';
                    },
                    searching: function() {
                        return 'Buscando...';
                    }
                },
                // Matcher personalizado para búsqueda en múltiples campos
                matcher: function(params, data) {
                    // Si no hay término de búsqueda, mostrar todos los resultados
                    if (!params.term || $.trim(params.term) === '') {
                        return data;
                    }
                    
                    const searchTerm = params.term.toLowerCase().trim();
                    const text = (data.text || '').toLowerCase();
                    
                    // Buscar en el texto del option
                    if (text.indexOf(searchTerm) > -1) {
                        return data;
                    }
                    
                    // Buscar en atributos data
                    const $element = $(data.element);
                    const documento = ($element.attr('data-documento') || '').toLowerCase();
                    const email = ($element.attr('data-email') || '').toLowerCase();
                    const nombre = ($element.attr('data-nombre') || '').toLowerCase();
                    const apellido = ($element.attr('data-apellido') || '').toLowerCase();
                    
                    // Buscar en documento, email, nombre o apellido
                    if (documento.indexOf(searchTerm) > -1 || 
                        email.indexOf(searchTerm) > -1 ||
                        nombre.indexOf(searchTerm) > -1 ||
                        apellido.indexOf(searchTerm) > -1) {
                        return data;
                    }
                    
                    return null;
                },
                // Template para mostrar resultados con más información
                templateResult: function(data) {
                    if (!data.id) {
                        return data.text;
                    }
                    
                    const clienteData = window.clientesDataList?.find(c => c.id == data.id);
                    if (clienteData) {
                        const nombreCompleto = `${clienteData.nombre || ''} ${clienteData.apellido || ''}`.trim();
                        return nombreCompleto;
                    }
                    
                    return data.text;
                },
                // Template para la selección (mostrar solo nombre)
                templateSelection: function(data) {
                    if (!data.id) {
                        return data.text;
                    }
                    
                    const clienteData = window.clientesDataList?.find(c => c.id == data.id);
                    if (clienteData) {
                        return `${clienteData.nombre || ''} ${clienteData.apellido || ''}`.trim();
                    }
                    
                    return data.text;
                }
            });
            
            console.log('Select2 inicializado correctamente');
            
            // Agregar evento change para actualizar información
            select.on('change', function() {
                const clienteId = $(this).val();
                if (clienteId) {
                    const cliente = window.clientesDataList?.find(c => c.id == clienteId);
                    if (cliente) {
                        console.log('Cliente seleccionado:', cliente);
                    }
                    
                    // Cerrar después de seleccionar
                    setTimeout(() => {
                        select.select2('close');
                    }, 100);
                }
            });
            
            // Abrir dropdown automáticamente al hacer clic en el contenedor
            setTimeout(() => {
                const select2Container = select.next('.select2-container');
                if (select2Container.length > 0) {
                    select2Container.on('click', function(e) {
                        // Solo abrir si no está ya abierto
                        if (!select2Container.hasClass('select2-container--open')) {
                            select.select2('open');
                        }
                    });
                }
            }, 200);
            
        } catch (e) {
            console.error('Error al inicializar Select2:', e);
        }
    }).fail(function(error) {
        console.error('Error al cargar clientes:', error);
        showNotification('Error al cargar la lista de clientes', 'error');
    });
}

function cargarHabitaciones() {
    // Pedir habitaciones disponibles desde el backend (más fiable)
    const start = $('#fecha_entrada').val() || '';
    const end = $('#fecha_salida').val() || '';
    
    // Evitar llamadas múltiples si las fechas son las mismas
    if (cargarHabitaciones.lastStart === start && cargarHabitaciones.lastEnd === end) {
        console.log('Mismas fechas, evitando recarga de habitaciones');
        return;
    }
    
    cargarHabitaciones.lastStart = start;
    cargarHabitaciones.lastEnd = end;
    
    $.get(`api/endpoints/habitaciones_disponibles.php?start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}`, function(data) {
        const habitacionesList = Array.isArray(data) ? data : (data.records || []);
        habitaciones = habitacionesList;
        console.log('cargarHabitaciones (disponibles): loaded', habitaciones.length, 'habitaciones');
        populateHabitacionesSelect(habitaciones);
    });
}

function actualizarCapacidadMaxima() {
    const habitacionId = $('#habitacion_id').val();
    
    if (!habitacionId) {
        capacidadMaximaHabitacion = 0;
        habitacionSeleccionada = null;
        actualizarCapacidadInfo();
        $('#capacidadInfo').hide();
        $('#capacidadWarning').hide();
        return;
    }
    
    const habitacion = habitaciones.find(h => h.id == habitacionId);
    
    // Fallback: también permitir leer capacidad desde el option seleccionado
    const capFromHab = habitacion?.capacidad ?? habitacion?.capacidad_maxima ?? habitacion?.max_huespedes ?? null;
    const capFromOption = $('#habitacion_id option:selected').data('capacidad');
    const capacidad = parseInt(capFromHab ?? capFromOption);

    if (!isNaN(capacidad) && capacidad > 0) {
        // Actualizar variables globales
        capacidadMaximaHabitacion = capacidad;
        habitacionSeleccionada = habitacion;
        
        // Actualizar UI de capacidad
        $('#capacidadMaxima').text(capacidad);
        $('#capacidadInfo').show();
        
        // Actualizar el máximo del campo de huéspedes
        $('#numero_huespedes').attr('max', capacidad);
        
        // Validar si los acompañantes actuales exceden la capacidad
        if (acompanantesTemporales.length > capacidad - 1) {
            showNotification('La cantidad de acompañantes excede la capacidad máxima de la habitación', 'warning');
            // Remover acompañantes excedentes
            const maxPermitidos = capacidad - 1;
            if (maxPermitidos >= 0) {
                acompanantesTemporales = acompanantesTemporales.slice(0, maxPermitidos);
                actualizarListaAcompanantes();
            }
        }
        
        // Validar si el valor actual excede la capacidad
        const valorActual = parseInt($('#numero_huespedes').val());
        
        // Solo ajustar si el valor actual es mayor a la capacidad
        if (!isNaN(valorActual) && valorActual > 0 && valorActual > capacidad) {
            $('#numero_huespedes').val(capacidad);
            $('#capacidadWarning').show();
        } else {
            $('#capacidadWarning').hide();
        }
        
        // Actualizar información de acompañantes
        actualizarCapacidadInfo();
    } else {
        capacidadMaximaHabitacion = 0;
        habitacionSeleccionada = null;
        $('#capacidadInfo').hide();
        $('#capacidadWarning').hide();
        actualizarCapacidadInfo();
    }
}

// Rellena el select de habitaciones con la lista ya filtrada por el backend
function populateHabitacionesSelect(habitacionesList) {
    console.log('=== INICIALIZANDO SELECT2 HABITACIÓN ===');
    const select = $('#habitacion_id');
    console.log('Elemento habitación encontrado:', select.length > 0);
    
    // Si ya está inicializado y no hay cambios, solo actualizar opciones
    const isAlreadyInitialized = select.data('select2');
    const currentOptions = select.find('option').length;
    
    if (isAlreadyInitialized && currentOptions > 1) {
        console.log('Select2 ya inicializado, actualizando opciones...');
        select.empty().append('<option value="">Seleccione una habitación...</option>');
        let count = 0;
        habitacionesList.forEach(hab => {
            const precio = hab.precio ?? hab.precio_noche ?? 0;
            const capacidad = hab.capacidad ?? hab.capacidad_maxima ?? hab.max_huespedes ?? null;
            const capTxt = capacidad ? ` · cap ${capacidad}` : '';
            select.append(
                `<option value="${hab.id}" data-precio="${precio}" data-capacidad="${capacidad ?? ''}">Hab. ${hab.numero} - ${hab.tipo}${capTxt} ($${parseFloat(precio).toLocaleString('es-CO')}/noche)</option>`
            );
            count++;
        });
        
        if (count === 0) {
            select.append('<option value="" disabled>No hay habitaciones disponibles para las fechas seleccionadas</option>');
        }
        
        select.trigger('change');
        console.log('Opciones actualizadas, count:', count);
        return;
    }
    
    // Inicialización completa
    select.empty().append('<option value="">Seleccione una habitación...</option>');
    let count = 0;
    habitacionesList.forEach(hab => {
        const precio = hab.precio ?? hab.precio_noche ?? 0;
        const capacidad = hab.capacidad ?? hab.capacidad_maxima ?? hab.max_huespedes ?? null;
        const capTxt = capacidad ? ` · cap ${capacidad}` : '';
        select.append(
            `<option value="${hab.id}" data-precio="${precio}" data-capacidad="${capacidad ?? ''}">Hab. ${hab.numero} - ${hab.tipo}${capTxt} ($${parseFloat(precio).toLocaleString('es-CO')}/noche)</option>`
        );
        count++;
    });
    console.log('Habitaciones procesadas:', count);
    
    if (count === 0) {
        select.append('<option value="" disabled>No hay habitaciones disponibles para las fechas seleccionadas</option>');
    }
    
    // Destruir Select2 si ya existe
    try {
        if (select.data('select2')) {
            console.log('Destruyendo Select2 de habitación existente...');
            select.select2('destroy');
            select.removeData('select2');
            select.removeClass('select2-hidden-accessible');
        }
    } catch (e) {
        console.log('Error al destruir Select2 habitación:', e);
    }
    
    // Inicializar Select2 para habitaciones
    try {
        console.log('Inicializando Select2 para habitación...');
        select.select2({
            theme: 'bootstrap-5',
            placeholder: 'Seleccione una habitación...',
            allowClear: true,
            width: '100%',
            dropdownParent: '#modalReserva .modal-body',
            language: {
                noResults: function() {
                    return 'No se encontraron habitaciones';
                },
                searching: function() {
                    return 'Buscando...';
                },
                inputTooShort: function() {
                    return 'Por favor ingrese más caracteres';
                }
            }
        });
        
        console.log('Select2 de habitación inicializado correctamente');
        
        // Agregar evento change para mantener funcionalidad original
        select.on('change', function() {
            console.log('Habitación seleccionada:', $(this).val());
            calcularPrecio();
            actualizarCapacidadMaxima();
        });
        
        // Verificar campo de búsqueda
        setTimeout(() => {
            const select2Container = select.next('.select2-container');
            const select2Search = select2Container.find('.select2-search__field');
            
            console.log('Contenedor Select2 habitación encontrado:', select2Container.length > 0);
            console.log('Campo de búsqueda habitación encontrado:', select2Search.length > 0);
            
            if (select2Search.length > 0) {
                select2Search.prop('disabled', false);
                select2Search.prop('readonly', false);
                select2Search.css('pointer-events', 'auto');
                console.log('Campo de búsqueda habitación habilitado');
                
                select2Search.on('input', function() {
                    console.log('Escribiendo en Select2 habitación:', $(this).val());
                });
            }
        }, 200);
    } catch (e) {
        console.error('Error al inicializar Select2 habitación:', e);
    }
}

// Actualiza el select de habitaciones mostrando solo las disponibles para el rango dado
function updateHabitacionesSelect(fechaEntrada, fechaSalida, includeHabitacionId = null) {
    const select = $('#habitacion_id');
    select.empty().append('<option value="">Seleccione una habitación...</option>');

    const start = fechaEntrada ? new Date(fechaEntrada) : null;
    const end = fechaSalida ? new Date(fechaSalida) : null;

    console.log('updateHabitacionesSelect called with', {fechaEntrada, fechaSalida, includeHabitacionId});
    console.log('habitaciones.length=', habitaciones.length, 'reservas.length=', reservas.length);
    let count = 0;
    habitaciones.forEach(hab => {
        const precio = hab.precio ?? hab.precio_noche ?? 0;
        const capacidad = hab.capacidad ?? hab.capacidad_maxima ?? hab.max_huespedes ?? null;
        const capTxt = capacidad ? ` · cap ${capacidad}` : '';

        // Si no hay fechas, mostrar habitaciones cuyo estado sea 'disponible'
        if (!start || !end) {
            if (hab.estado === 'disponible') {
                select.append(`<option value="${hab.id}" data-precio="${precio}" data-capacidad="${capacidad ?? ''}">Hab. ${hab.numero} - ${hab.tipo}${capTxt} ($${parseFloat(precio).toLocaleString('es-CO')}/noche)</option>`);
                count++;
            }
            return;
        }

        // Verificar si existe alguna reserva que solape con el rango y que bloquee la habitación
        const bloqueada = reservas.some(r => {
            if (r.habitacion_id != hab.id) return false;
            // solo considerar estados que ocupan la habitación
            if (!['confirmada','pendiente'].includes(r.estado)) return false;
            const rStart = new Date(r.fecha_entrada);
            const rEnd = new Date(r.fecha_salida);
            // solapamiento: (rStart <= end && rEnd >= start)
            return (rStart <= end && rEnd >= start);
        });
        // Si estamos editando una reserva, permitir incluir su propia habitación aunque parezca bloqueada
        const isOwnIncluded = includeHabitacionId && includeHabitacionId == hab.id;

        if ((!bloqueada || isOwnIncluded) && hab.estado === 'disponible') {
            select.append(`<option value="${hab.id}" data-precio="${precio}" data-capacidad="${capacidad ?? ''}">Hab. ${hab.numero} - ${hab.tipo}${capTxt} ($${parseFloat(precio).toLocaleString('es-CO')}/noche)</option>`);
            count++;
        }
    });

    // Si no hay habitaciones disponibles, mostrar mensaje y deshabilitar select
    if (count === 0) {
        select.append('<option value="" disabled>No hay habitaciones disponibles para las fechas seleccionadas</option>');
        select.prop('disabled', true);
        console.log('updateHabitacionesSelect: no hay habitaciones disponibles — select disabled');
    } else {
        select.prop('disabled', false);
        console.log('updateHabitacionesSelect: found', count, 'habitaciones — select enabled');
    }
    // DEBUG: mostrar estado del select después de update
    try {
        const debug = $('#habitacionesDebug');
        const opts = Array.from(select.find('option')).map(o => ({ value: o.value, text: o.text, disabled: o.disabled }));
        debug.text(`options: ${opts.length}\nenabled: ${!select.prop('disabled')}\nfirstOptions: ${JSON.stringify(opts.slice(0,6), null, 2)}`);
        console.log('updateHabitacionesSelect debug options=', opts.slice(0,10));
    } catch (err) {
        console.error('updateHabitacionesSelect debug error', err);
    }
}

function calcularPrecio() {
    const habitacionId = $('#habitacion_id').val();
    const fechaEntrada = $('#fecha_entrada').val();
    const fechaSalida = $('#fecha_salida').val();
    
    if (habitacionId && fechaEntrada && fechaSalida) {
        const habitacion = habitaciones.find(h => h.id == habitacionId);
        const inicio = new Date(fechaEntrada);
        const fin = new Date(fechaSalida);
        const dias = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24));

        if (dias > 0 && habitacion) {
            const precioNoche = parseFloat(habitacion.precio ?? habitacion.precio_noche ?? 0);
            const precioTotal = dias * precioNoche;
            
            // Solo actualizar si el campo está vacío o es 0 (usuario no ha personalizado)
            const valorActual = parseFloat($('#precio_total').val()) || 0;
            if (valorActual === 0) {
                $('#precio_total').val(precioTotal.toFixed(2));
            }
        }
    }
}

function validarCapacidad() {
    const habitacionId = $('#habitacion_id').val();
    const numeroHuespedes = parseInt($('#numero_huespedes').val());
    
    // Limpiar advertencias anteriores
    $('#numero_huespedes').removeClass('border-danger bg-danger-subtle');
    $('#numero_huespedes').parent().find('.text-danger').remove();
    
    if (habitacionId && numeroHuespedes) {
        const habitacion = habitaciones.find(h => h.id == habitacionId);
        
        const capFromHab = habitacion?.capacidad ?? habitacion?.capacidad_maxima ?? habitacion?.max_huespedes ?? null;
        const capFromOption = $('#habitacion_id option:selected').data('capacidad');
        const capacidad = parseInt(capFromHab ?? capFromOption);
        
        if (!isNaN(capacidad) && capacidad > 0) {
            
            if (numeroHuespedes > capacidad) {
                // Mostrar advertencia
                $('#numero_huespedes').addClass('border-danger bg-danger-subtle');
                $('#numero_huespedes').parent().append('<div class="text-danger small mt-1">⚠️ Excede la capacidad máxima (' + capacidad + ' huéspedes)</div>');
                return false;
            } else {
                // Quitar advertencia
                $('#numero_huespedes').removeClass('border-danger bg-danger-subtle');
                $('#numero_huespedes').parent().find('.text-danger').remove();
                return true;
            }
        }
    }
    
    return true;
}

function abrirModalNuevo() {
    console.log('Abriendo modal de nueva reserva...');
    $('#modalTitle').text('Nueva Reserva');
    $('#formReserva')[0].reset();
    $('#reserva_id').val('');
    
    // Limpiar acompañantes temporales
    limpiarAcompanantesTemporales();
    
    // Destruir Select2 existente ANTES de establecer valores
    try {
        $('#cliente_id').select2('destroy');
        $('#habitacion_id').select2('destroy');
        console.log('Select2 destruidos correctamente');
    } catch (e) {
        console.log('No había Select2 para destruir:', e);
    }
    
    // Establecer fechas por defecto
    const today = new Date().toISOString().split('T')[0];
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const tomorrowStr = tomorrow.toISOString().split('T')[0];
    
    $('#fecha_entrada').val(today);
    $('#fecha_salida').val(tomorrowStr);
    $('#fecha_entrada').attr('min', today);
    $('#fecha_salida').attr('min', tomorrowStr);
    
    // valor por defecto para método de pago
    $('#metodo_pago').val('efectivo');
    // Limpiar información de capacidad
    $('#capacidadInfo').hide();
    $('#capacidadWarning').hide();
    
    // Limpiar cache para forzar recarga inicial
    cargarHabitaciones.lastStart = null;
    cargarHabitaciones.lastEnd = null;
    
    // Mostrar modal PRIMERO
    const modalElement = document.getElementById('modalReserva');
    const modal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: true
    });
    modal.show();
    
    // Esperar a que el modal esté completamente visible para inicializar Select2
    setTimeout(() => {
        console.log('Modal visible, inicializando Select2...');
        
        // Cargar habitaciones primero (con las fechas ya establecidas)
        cargarHabitaciones();
        
        // Luego cargar clientes
        setTimeout(() => {
            cargarClientes();
        }, 200);
    }, 300);
}

function editarReserva(id) {
    console.log('Editando reserva:', id);
    
    $.get(`api/endpoints/reservas.php?id=${id}`, function(reserva) {
        console.log('Datos de reserva recibidos:', reserva);
        
        $('#modalTitle').text('Editar Reserva');
        $('#reserva_id').val(reserva.id);
        
        // Guardar los valores para establecerlos después de inicializar Select2
        const clienteId = reserva.cliente_id;
        const habitacionId = reserva.habitacion_id;
        
        $('#fecha_entrada').val(reserva.fecha_entrada);
        $('#fecha_salida').val(reserva.fecha_salida);
        $('#precio_total').val(reserva.total ?? reserva.precio_total ?? 0);
        $('#estado').val(reserva.estado);
        $('#metodo_pago').val(reserva.metodo_pago ?? 'efectivo');
        $('#motivo_viaje').val(reserva.motivo_viaje || '');
        
        // Separar observaciones del usuario del JSON de acompañantes
        let observacionesUsuario = reserva.observaciones || '';
        let notasReserva = '';
        
        if (observacionesUsuario) {
            // Separar el JSON de acompañantes del resto de observaciones
            const acompanantesMatch = observacionesUsuario.match(/(.*?)(ACOMPANANTES:\s*\[.*?\])/s);
            if (acompanantesMatch) {
                // Mantener solo las observaciones de usuario (sin JSON)
                observacionesUsuario = acompanantesMatch[1].trim();
            } else {
                // Si no hay JSON, limpiar cualquier residuo
                observacionesUsuario = observacionesUsuario.replace(/ACOMPANANTES:\s*\[.*?\]/gs, '').trim();
            }
            
            // Intentar separar las notas específicas de la reserva de las observaciones generales
            // Por ahora, pondremos todo en notas_reserva ya que es más específico
            notasReserva = observacionesUsuario;
            observacionesUsuario = '';
        }
        
        $('#observaciones').val(observacionesUsuario);
        $('#notas_reserva').val(notasReserva);
        
        // Guardar el valor correcto para establecerlo después de que todo cargue
        const valorHuespedesCorrecto = reserva.numero_huespedes ?? reserva.num_huespedes ?? 1;
        
        // Abrir el modal PRIMERO
        console.log('Abriendo modal de edición...');
        const modalElement = document.getElementById('modalReserva');
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: true
        });
        modal.show();
        
        // Esperar a que el modal esté visible para cargar datos
        setTimeout(() => {
            console.log('Modal visible, cargando datos...');
            
            // Pedir habitaciones disponibles para el rango y también forzar incluir la habitación actual
            $.get(`api/endpoints/habitaciones_disponibles.php?start=${encodeURIComponent(reserva.fecha_entrada)}&end=${encodeURIComponent(reserva.fecha_salida)}&include_id=${encodeURIComponent(reserva.habitacion_id)}`, function(data) {
                const habs = Array.isArray(data) ? data : (data.records || []);
                habitaciones = habs;
                populateHabitacionesSelect(habs);
                
                // Establecer valores DESPUÉS de inicializar Select2
                setTimeout(() => {
                    $('#habitacion_id').val(habitacionId).trigger('change');
                    $('#cliente_id').val(clienteId).trigger('change');
                    $('#numero_huespedes').val(valorHuespedesCorrecto);
                    
                    // Actualizar capacidad
                    actualizarCapacidadMaxima();
                    
                    // Cargar acompañantes existentes
                    setTimeout(() => {
                        cargarAcompanantesReserva(reserva.id);
                    }, 300);
                }, 200);
            }).fail(function(xhr, status, error) {
                console.error('Error cargando habitaciones:', xhr.responseText);
                showNotification('Error al cargar habitaciones disponibles', 'error');
            });
        }, 300);
        
    }).fail(function(xhr, status, error) {
        console.error('Error cargando reserva:', xhr.responseText);
        showNotification('Error al cargar datos de la reserva', 'error');
    });
}

// Reset y carga de acompañantes en el formulario de la reserva
function resetAcompanantesReservaForm() {
    acompananteReservaCount = 0;
    const container = document.getElementById('acompanantesReservaContainer');
    if (container) {
        container.innerHTML = '<p class="text-muted mb-0 text-center">No hay acompañantes registrados</p>';
    }
    // Esto también sincroniza el campo numero_huespedes
    try {
        actualizarTotalHuespedes();
    } catch (e) {
        // si el DOM aún no está listo para actualizar, ignorar
    }
}

function cargarAcompanantesReservaEnFormulario(reservaId) {
    // Limpiar primero
    resetAcompanantesReservaForm();

    $.get(`api/endpoints/acompanantes.php?reserva_id=${encodeURIComponent(reservaId)}`, function(data) {
        const lista = Array.isArray(data) ? data : (data.records || data.acompanantes || []);
        if (!lista || lista.length === 0) {
            return;
        }

        const container = document.getElementById('acompanantesReservaContainer');
        if (!container) return;

        // Preparar contenedor para pintar acompañantes
        container.innerHTML = '';
        acompananteReservaCount = 0;

        lista.forEach((a, idx) => {
            agregarAcompananteReserva();
            const n = idx + 1;

            $(`input[name="acompanante_reserva_nombre_${n}"]`).val(a.nombre || '');
            $(`input[name="acompanante_reserva_apellido_${n}"]`).val(a.apellido || '');
            $(`select[name="acompanante_reserva_parentesco_${n}"]`).val(a.parentesco || 'Otro');
            $(`input[name="acompanante_reserva_fn_${n}"]`).val(a.fecha_nacimiento || a.fecha_nac || '');
            $(`select[name="acompanante_reserva_tipo_doc_${n}"]`).val(a.tipo_documento || a.tipo_doc || '');
            $(`input[name="acompanante_reserva_num_doc_${n}"]`).val(a.numero_documento || a.num_doc || '');

            // Calcular edad si hay fecha
            try {
                calcularEdadReserva(n);
            } catch (e) {}
        });

        // Recalcular totales y validación
        actualizarTotalHuespedes();
    }).fail(function() {
        // Fallback: si no existe la tabla `acompanantes`, los guardamos en `reservas.observaciones`
        $.get(`api/endpoints/reservas.php?id=${encodeURIComponent(reservaId)}`, function(reserva) {
            const obs = reserva?.observaciones || '';
            let acompanantesGuardados = [];
            try {
                const match = obs.match(/ACOMPANANTES:\n([\s\S]*?)(?=\n\n|$)/);
                if (match && match[1]) {
                    acompanantesGuardados = JSON.parse(match[1]);
                }
            } catch (e) {
                acompanantesGuardados = [];
            }

            if (!acompanantesGuardados || acompanantesGuardados.length === 0) {
                return;
            }

            const container = document.getElementById('acompanantesReservaContainer');
            if (!container) return;

            container.innerHTML = '';
            acompananteReservaCount = 0;

            acompanantesGuardados.forEach((a, idx) => {
                agregarAcompananteReserva();
                const n = idx + 1;

                $(`input[name="acompanante_reserva_nombre_${n}"]`).val(a.nombre || '');
                $(`input[name="acompanante_reserva_apellido_${n}"]`).val(a.apellido || '');
                $(`select[name="acompanante_reserva_parentesco_${n}"]`).val(a.parentesco || 'Otro');
                $(`input[name="acompanante_reserva_fn_${n}"]`).val(a.fecha_nacimiento || a.fecha_nac || '');
                $(`select[name="acompanante_reserva_tipo_doc_${n}"]`).val(a.tipo_documento || a.tipo_doc || '');
                $(`input[name="acompanante_reserva_num_doc_${n}"]`).val(a.numero_documento || a.num_doc || '');

                try { calcularEdadReserva(n); } catch (e) {}
            });

            actualizarTotalHuespedes();
        });
    });
}

function guardarReserva(e) {
    e.preventDefault();
    
    // Validar capacidad antes de guardar
    if (!validarCapacidad()) {
        showNotification('El número de huéspedes excede la capacidad máxima de la habitación', 'error');
        return;
    }
    
    const id = $('#reserva_id').val();

    const clienteId = $('#cliente_id').val();
    const habitacionId = $('#habitacion_id').val();
    const fechaEntrada = $('#fecha_entrada').val();
    const fechaSalida = $('#fecha_salida').val();
    const numeroHuespedes = $('#numero_huespedes').val();
    const motivoViaje = $('#motivo_viaje').val();
    const notasReserva = $('#notas_reserva').val();

    console.log('Datos del formulario:');
    console.log('cliente_id:', clienteId);
    console.log('habitacion_id:', habitacionId);
    console.log('fecha_entrada:', fechaEntrada);
    console.log('fecha_salida:', fechaSalida);
    console.log('numero_huespedes:', numeroHuespedes);
    console.log('motivo_viaje:', motivoViaje);
    console.log('notas_reserva:', notasReserva);

    // Validar campos requeridos
    if (!clienteId || !habitacionId || !fechaEntrada || !fechaSalida) {
        showNotification('Por favor complete todos los campos obligatorios', 'error');
        return;
    }

    // calcular noches y total (si es posible)
    let noches = 0;
    let total = parseFloat($('#precio_total').val()) || 0;
    
    // Si el precio total es 0 o está vacío, calcular automáticamente
    if (total === 0 && fechaEntrada && fechaSalida && habitacionId) {
        const inicio = new Date(fechaEntrada);
        const fin = new Date(fechaSalida);
        noches = Math.max(1, Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24)));
        const habitacion = habitaciones.find(h => h.id == habitacionId);
        const precioNoche = parseFloat(habitacion?.precio ?? habitacion?.precio_noche ?? 0);
        total = noches * precioNoche;
        // Actualizar el campo con el cálculo automático
        $('#precio_total').val(total.toFixed(2));
    }

    // Obtener acompañantes del formulario
    const acompanantes = obtenerAcompanantesReserva();

    // Preservar JSON de acompañantes existente en las observaciones
    let observacionesLimpias = $('#observaciones').val() || '';
    
    // Combinar las notas de la reserva con las observaciones existentes
    if (notasReserva) {
        if (observacionesLimpias) {
            observacionesLimpias = notasReserva + '\n\n' + observacionesLimpias;
        } else {
            observacionesLimpias = notasReserva;
        }
    }
    
    if (observacionesLimpias) {
        // Separar el JSON de acompañantes del resto de observaciones
        const acompanantesMatch = observacionesLimpias.match(/(.*?)(ACOMPANANTES:\s*\[.*?\])/s);
        if (acompanantesMatch) {
            // Mantener solo las observaciones de usuario (sin JSON)
            observacionesLimpias = acompanantesMatch[1].trim();
        } else {
            // Si no hay JSON, limpiar cualquier residuo
            observacionesLimpias = observacionesLimpias.replace(/ACOMPANANTES:\s*\[.*?\]/gs, '').trim();
        }
    }

    const data = {
        cliente_id: clienteId,
        habitacion_id: habitacionId,
        fecha_entrada: fechaEntrada,
        fecha_salida: fechaSalida,
        numero_huespedes: numeroHuespedes || 1,
        total: total,
        estado: $('#estado').val() || 'pendiente',
        metodo_pago: $('#metodo_pago').val() || 'efectivo',
        motivo_viaje: motivoViaje,
        observaciones: observacionesLimpias,
        noches: noches,
        acompanantes: acompanantes // Agregar acompañantes al envío
    };
    
    if (id) data.id = parseInt(id);

    console.log('Datos a enviar:', JSON.stringify(data, null, 2));

    const url = 'api/endpoints/reservas.php';
    const method = id ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        type: method,
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            console.log('Respuesta del servidor:', response);
            $('#modalReserva').modal('hide');
            showNotification(response.message || 'Reserva guardada exitosamente', 'success');
            cargarReservas();
        },
        error: function(xhr) {
            console.error('Error completo:', xhr);
            console.error('Status:', xhr.status);
            console.error('Status Text:', xhr.statusText);
            console.error('Response Text:', xhr.responseText);
            console.error('Response JSON:', xhr.responseJSON);
            
            let errorMessage = 'Error al guardar reserva';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.responseText) {
                try {
                    const errorData = JSON.parse(xhr.responseText);
                    errorMessage = errorData.message || errorData.error || errorMessage;
                } catch (e) {
                    errorMessage = xhr.responseText;
                }
            }
            
            showNotification(errorMessage, 'error');
        }
    });
}

function eliminarReserva(id) {
    if (confirm('¿Está seguro de eliminar esta reserva?')) {
        $.ajax({
            url: 'api/endpoints/reservas.php',
            type: 'DELETE',
            contentType: 'application/json',
            data: JSON.stringify({ id: id }),
            success: function(response) {
                showNotification(response.message || 'Reserva eliminada', 'success');
                cargarReservas();
            },
            error: function(xhr) {
                showNotification(xhr.responseJSON?.message || xhr.responseText || 'Error al eliminar', 'error');
            }
        });
    }
}

// Funciones para el modal de cliente
function abrirModalCliente() {
    // Restablecer modo acompañante
    modoAcompanante = false;
    $('#modalTitle').text('Nuevo Cliente');
    $('#formCliente')[0].reset();
    $('#cliente_form_id').val('');
    
    // Restaurar texto del botón
    $('button[form="formCliente"]').html('Guardar Cliente');
    
    const modalElement = document.getElementById('modalCliente');
    const modal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: true
    });
    modal.show();
}

function guardarCliente(e) {
    e.preventDefault();
    
    // Verificar si estamos en modo acompañante
    if (modoAcompanante) {
        guardarComoAcompanante();
        return;
    }
    
    // Obtener acompañantes del formulario
    const acompanantes = obtenerAcompanantes();
    
    const data = {
        nombre: $('#nombre').val(),
        apellido: $('#apellido').val(),
        tipo_documento: $('#tipo_documento').val(),
        numero_documento: $('#numero_documento').val(),
        email: $('#email').val(),
        telefono: $('#telefono').val(),
        fecha_nacimiento: $('#fecha_nacimiento').val(),
        ciudad: $('#ciudad').val(),
        nacionalidad: $('#nacionalidad').val(),
        motivo_viaje: $('#motivo_viaje').val(),
        direccion: $('#direccion').val(),
        acompanantes: acompanantes // Agregar acompañantes al envío
    };
    
    $.ajax({
        url: 'api/endpoints/clientes_simple_post.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            $('#modalCliente').modal('hide');
            showNotification('Cliente creado exitosamente', 'success');
            
            // Recargar la lista de clientes y seleccionar el nuevo cliente
            cargarClientes();
            
            // Si el response tiene el ID del nuevo cliente, seleccionarlo automáticamente
            if (response.id) {
                setTimeout(() => {
                    $('#cliente_id').val(response.id);
                }, 500);
            }
        },
        error: function(xhr) {
            showNotification(xhr.responseJSON?.message || 'Error al crear cliente', 'error');
        }
    });
}

function guardarComoAcompanante() {
    // Validar capacidad antes de agregar
    const totalActual = 1 + acompanantesTemporales.length;
    if (totalActual >= capacidadMaximaHabitacion) {
        showNotification('Ha alcanzado la capacidad máxima de la habitación', 'warning');
        return;
    }
    
    // Crear cliente usando el endpoint funcional
    const data = {
        nombre: $('#nombre').val(),
        apellido: $('#apellido').val(),
        documento: $('#numero_documento').val(), // clientes_final usa 'documento'
        email: $('#email').val(),
        telefono: $('#telefono').val(),
        direccion: $('#direccion').val() || '',
        ciudad: $('#ciudad').val() || '',
        pais: $('#pais').val() || ''
    };
    
    $.ajax({
        url: 'api/endpoints/clientes_final.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            // Agregar como acompañante
            const acompanante = {
                index: Date.now(),
                persona_id: response.id,
                nombre: data.nombre,
                apellido: data.apellido,
                tipo_documento: $('#tipo_documento').val(), // Valor del formulario
                numero_documento: data.documento, // Usar el documento que se guardó
                fecha_nacimiento: $('#fecha_nacimiento').val(),
                parentesco: '',
                email: data.email,
                telefono: data.telefono,
                es_menor: calcularEdad($('#fecha_nacimiento').val()) < 18
            };
            
            acompanantesTemporales.push(acompanante);
            
            // Actualizar interfaz
            actualizarListaAcompanantes();
            actualizarCapacidadInfo();
            
            // Restablecer modo y cerrar modal
            modoAcompanante = false;
            $('#modalCliente').modal('hide');
            
            // Restaurar texto del botón
            $('button[form="formCliente"]').html('<i class="fas fa-save me-2"></i>Guardar Cliente');
            
            showNotification('Acompañante creado y agregado correctamente', 'success');
        },
        error: function(xhr) {
            console.error('Error creando acompañante:', xhr.responseText);
            const response = JSON.parse(xhr.responseText);
            
            if (xhr.status === 409 && response.error === 'duplicate_document') {
                showNotification('Ya existe una persona con este documento. Busca la persona existente en lugar de crear una nueva.', 'warning');
            } else {
                showNotification('Error al crear acompañante: ' + (response.message || 'Error desconocido'), 'error');
            }
        }
    });
}

function toggleDocumentoVisibility() {
    const checkbox = document.getElementById('toggleMostrarDocumento');
    const input = document.getElementById('numero_documento');
    const hiddenInput = document.getElementById('numero_documento_full');
    
    if (checkbox.checked) {
        // Mostrar el número completo
        if (hiddenInput.value) {
            input.value = hiddenInput.value;
        }
    } else {
        // Ocultar parte del número (mostrar solo últimos 4 dígitos)
        const fullValue = input.value;
        hiddenInput.value = fullValue;
        if (fullValue.length > 4) {
            input.value = '*'.repeat(fullValue.length - 4) + fullValue.slice(-4);
        }
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

// Funciones para manejar acompañantes en reservas
let acompananteReservaCount = 0;

function agregarAcompananteReserva() {
    acompananteReservaCount++;
    console.log('agregarAcompananteReserva() llamada, count:', acompananteReservaCount);
    const container = document.getElementById('acompanantesReservaContainer');
    console.log('Container encontrado:', !!container);
    
    // Limpiar mensaje inicial si existe
    if (acompananteReservaCount === 1) {
        container.innerHTML = '';
    }
    
    const acompananteHtml = `
        <div class="acompanante-reserva-item border rounded p-3 mb-2 bg-white" id="acompanante-reserva-${acompananteReservaCount}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Acompañante ${acompananteReservaCount}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarAcompananteReserva(${acompananteReservaCount})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label">Nombre *</label>
                    <input type="text" class="form-control" name="acompanante_reserva_nombre_${acompananteReservaCount}" required onchange="actualizarTotalHuespedes()">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Apellido *</label>
                    <input type="text" class="form-control" name="acompanante_reserva_apellido_${acompananteReservaCount}" required onchange="actualizarTotalHuespedes()">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">Parentesco</label>
                    <select class="form-select" name="acompanante_reserva_parentesco_${acompananteReservaCount}">
                        <option value="Hijo(a)">Hijo(a)</option>
                        <option value="Cónyuge">Cónyuge</option>
                        <option value="Hermano(a)">Hermano(a)</option>
                        <option value="Amigo(a)">Amigo(a)</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">Fecha Nac.</label>
                    <input type="date" class="form-control" name="acompanante_reserva_fn_${acompananteReservaCount}" onchange="calcularEdadReserva(${acompananteReservaCount}); actualizarTotalHuespedes()">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">Edad</label>
                    <input type="number" class="form-control" name="acompanante_reserva_edad_${acompananteReservaCount}" readonly placeholder="Auto">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label">Tipo Doc. *</label>
                    <select class="form-select" name="acompanante_reserva_tipo_doc_${acompananteReservaCount}" required>
                        <option value="">Seleccione...</option>
                        <option value="DNI">DNI</option>
                        <option value="Pasaporte">Pasaporte</option>
                        <option value="Cedula">Cedula</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Número Doc. *</label>
                    <input type="text" class="form-control" name="acompanante_reserva_num_doc_${acompananteReservaCount}" required>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', acompananteHtml);
    console.log('HTML insertado, elementos en container:', container.children.length);
    actualizarTotalHuespedes();
}

function eliminarAcompananteReserva(id) {
    const element = document.getElementById(`acompanante-reserva-${id}`);
    if (element) {
        element.remove();
    }
    
    // Si no hay más acompañantes, mostrar mensaje inicial
    const container = document.getElementById('acompanantesReservaContainer');
    if (container.children.length === 0) {
        container.innerHTML = '<p class="text-muted mb-0 text-center">No hay acompañantes registrados</p>';
    }
    
    actualizarTotalHuespedes();
}

function calcularEdadReserva(id) {
    const fechaNac = document.querySelector(`input[name="acompanante_reserva_fn_${id}"]`).value;
    const edadInput = document.querySelector(`input[name="acompanante_reserva_edad_${id}"]`);
    
    if (fechaNac) {
        const hoy = new Date();
        const nacimiento = new Date(fechaNac);
        let edad = hoy.getFullYear() - nacimiento.getFullYear();
        const mes = hoy.getMonth() - nacimiento.getMonth();
        
        if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
            edad--;
        }
        
        edadInput.value = edad;
    } else {
        edadInput.value = '';
    }
}

function cargarAcompanantesEdicion(reserva) {
    // Solución temporal: intentar cargar desde observaciones o crear formularios vacíos
    const numHuespedes = reserva.num_huespedes || reserva.numero_huespedes || 1;
    const numAcompanantes = numHuespedes - 1;
    
    console.log('Cargando acompañantes para edición:', numHuespedes, 'huéspedes,', numAcompanantes, 'acompañantes');
    
    // Limpiar acompañantes existentes
    $('#acompanantesReservaContainer').empty();
    acompananteReservaCount = 0; // Resetear contador
    
    // Intentar cargar datos desde observaciones
    let acompanantesGuardados = [];
    if (reserva.observaciones) {
        try {
            // Buscar datos de acompañantes en observaciones
            const match = reserva.observaciones.match(/ACOMPANANTES:\n([\s\S]*?)(?=\n\n|\n$|$)/);
            if (match && match[1]) {
                acompanantesGuardados = JSON.parse(match[1]);
                console.log('Acompañantes encontrados en observaciones:', acompanantesGuardados);
            }
        } catch (e) {
            console.log('Error al parsear acompañantes desde observaciones:', e);
        }
    }
    
    if (numAcompanantes > 0) {
        // Agregar acompañantes
        for (let i = 0; i < numAcompanantes; i++) {
            console.log('Agregando acompañante #' + (i + 1));
            agregarAcompananteReserva();
            
            // Si hay datos guardados, llenar el formulario
            if (acompanantesGuardados[i]) {
                const acompanante = acompanantesGuardados[i];
                setTimeout(() => {
                    // Llenar campos del formulario
                    $(`input[name="acompanante_reserva_nombre_${i + 1}"]`).val(acompanante.nombre || '');
                    $(`input[name="acompanante_reserva_apellido_${i + 1}"]`).val(acompanante.apellido || '');
                    $(`select[name="acompanante_reserva_parentesco_${i + 1}"]`).val(acompanante.parentesco || 'Otro');
                    $(`input[name="acompanante_reserva_fecha_nacimiento_${i + 1}"]`).val(acompanante.fecha_nacimiento || '');
                    $(`select[name="acompanante_reserva_tipo_doc_${i + 1}"]`).val(acompanante.tipo_documento || 'DNI');
                    $(`input[name="acompanante_reserva_num_doc_${i + 1}"]`).val(acompanante.numero_documento || '');
                    
                    console.log('Formulario de acompañante #' + (i + 1) + ' llenado con datos:', acompanante);
                }, 100);
            }
        }
        
        if (acompanantesGuardados.length > 0) {
            showNotification(`Se cargaron ${acompanantesGuardados.length} acompañante(s) guardados anteriormente.`, 'success');
        } else {
            showNotification(`Se crearon ${numAcompanantes} formulario(s) de acompañante. Por favor, ingrese los datos.`, 'info');
        }
    }
    
    // Actualizar el contador de huéspedes
    actualizarTotalHuespedes();
}

function cargarAcompanantesReservaInfo(reservaId) {
    // Como la tabla acompanantes no existe, mostramos información básica
    const infoElement = $(`#acompanantes-info-${reservaId} .acompanantes-count`);
    
    // Buscar la reserva en la lista actual para obtener el número de huéspedes
    const reserva = reservas.find(r => r.id == reservaId);
    if (reserva) {
        const numHuespedes = reserva.num_huespedes || reserva.numero_huespedes || 1;
        if (numHuespedes > 1) {
            infoElement.text(`${numHuespedes} huéspedes`);
        } else {
            infoElement.text('1 huésped');
        }
    } else {
        infoElement.text('1 huésped'); // Valor por defecto
    }
    
    // Comentamos la llamada al API que falla
    /*
    $.get(`api/endpoints/acompanantes.php?reserva_id=${reservaId}`, function(acompanantes) {
        const infoElement = $(`#acompanantes-info-${reservaId} .acompanantes-count`);
        
        if (acompanantes && acompanantes.length > 0) {
            const adultos = acompanantes.filter(a => !a.es_menor).length;
            const menores = acompanantes.filter(a => a.es_menor).length;
            let texto = `${acompanantes.length} acompañante${acompanantes.length > 1 ? 's' : ''}`;
            
            if (adultos > 0 || menores > 0) {
                texto += ` (`;
                if (adultos > 0) texto += `${adultos} adulto${adultos > 1 ? 's' : ''}`;
                if (adultos > 0 && menores > 0) texto += `, `;
                if (menores > 0) texto += `${menores} menor${menores > 1 ? 'es' : ''}`;
            texto += `)`;
            }
            
            infoElement.text(texto);
        } else {
            infoElement.text('Sin acompañantes');
        }
    }).fail(function() {
        $(`#acompanantes-info-${reservaId} .acompanantes-count`).text('Error al cargar');
    });
    */
}

function actualizarTotalHuespedes() {
    const acompanantes = document.querySelectorAll('.acompanante-reserva-item');
    let totalHuespedes = 1; // Cliente principal
    let adultos = 1; // Cliente principal (asumimos adulto)
    let menores = 0;
    
    acompanantes.forEach(function(item) {
        const nombre = item.querySelector('[name^="acompanante_reserva_nombre_"]').value;
        const apellido = item.querySelector('[name^="acompanante_reserva_apellido_"]').value;
        
        if (nombre && apellido) {
            totalHuespedes++;
            const edadInput = item.querySelector('[name^="acompanante_reserva_edad_"]');
            const edad = parseInt(edadInput.value) || 0;
            
            if (edad < 18) {
                menores++;
            } else {
                adultos++;
            }
        }
    });
    
    // Actualizar el campo de número de huéspedes
    document.getElementById('numero_huespedes').value = totalHuespedes;
    
    // Actualizar información visual (si existe)
    const totalHuespedesCount = document.getElementById('totalHuespedesCount');
    if (totalHuespedesCount) {
        document.getElementById('totalHuespedesCount').textContent = totalHuespedes;
        document.getElementById('adultosCount').textContent = adultos;
        document.getElementById('menoresCount').textContent = menores;
        document.getElementById('totalHuespedesInfo').style.display = 'block';
    }
    
    // Validar capacidad
    validarCapacidad();
}

function cargarAcompanantesPendientes() {
    const clienteId = $('#cliente_id').val();
    
    if (!clienteId) {
        showNotification('Por favor seleccione un cliente primero', 'warning');
        return;
    }
    
    $.get(`api/endpoints/clientes.php?id=${clienteId}`, function(cliente) {
        // Aquí podríamos cargar acompañantes pendientes del cliente
        // Por ahora, mostramos un mensaje
        showNotification('Función para cargar acompañantes del cliente en desarrollo', 'info');
    }).fail(function() {
        showNotification('Error al cargar datos del cliente', 'error');
    });
}

function obtenerAcompanantesReserva() {
    const acompanantes = [];
    const items = document.querySelectorAll('.acompanante-reserva-item');
    
    items.forEach(function(item) {
        const id = item.id.replace('acompanante-reserva-', '');
        const nombre = document.querySelector(`input[name="acompanante_reserva_nombre_${id}"]`).value;
        const apellido = document.querySelector(`input[name="acompanante_reserva_apellido_${id}"]`).value;
        const parentesco = document.querySelector(`select[name="acompanante_reserva_parentesco_${id}"]`).value;
        const fechaNac = document.querySelector(`input[name="acompanante_reserva_fn_${id}"]`).value;
        const tipoDoc = document.querySelector(`select[name="acompanante_reserva_tipo_doc_${id}"]`).value;
        const numDoc = document.querySelector(`input[name="acompanante_reserva_num_doc_${id}"]`).value;
        
        if (nombre && apellido && tipoDoc && numDoc) {
            acompanantes.push({
                nombre: nombre,
                apellido: apellido,
                parentesco: parentesco,
                fecha_nacimiento: fechaNac,
                tipo_documento: tipoDoc,
                numero_documento: numDoc
            });
        }
    });
    
    return acompanantes;
}

// Funciones para manejar acompañantes en el modal de cliente
let acompananteCount = 0;

function agregarAcompanante() {
    acompananteCount++;
    const container = document.getElementById('acompanantesContainer');
    
    // Limpiar mensaje inicial si existe
    if (acompananteCount === 1) {
        container.innerHTML = '';
    }
    
    const acompananteHtml = `
        <div class="acompanante-item border rounded p-3 mb-2 bg-white" id="acompanante-${acompananteCount}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Acompañante ${acompananteCount}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarAcompanante(${acompananteCount})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label">Nombre *</label>
                    <input type="text" class="form-control" name="acompanante_nombre_${acompananteCount}" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Apellido *</label>
                    <input type="text" class="form-control" name="acompanante_apellido_${acompananteCount}" required>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">Parentesco</label>
                    <select class="form-select" name="acompanante_parentesco_${acompananteCount}">
                        <option value="Hijo(a)">Hijo(a)</option>
                        <option value="Cónyuge">Cónyuge</option>
                        <option value="Hermano(a)">Hermano(a)</option>
                        <option value="Amigo(a)">Amigo(a)</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">Fecha Nac.</label>
                    <input type="date" class="form-control" name="acompanante_fn_${acompananteCount}" onchange="calcularEdad(${acompananteCount})">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">Edad</label>
                    <input type="number" class="form-control" name="acompanante_edad_${acompananteCount}" readonly placeholder="Auto">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label">Tipo Doc. *</label>
                    <select class="form-select" name="acompanante_tipo_doc_${acompananteCount}" required>
                        <option value="">Seleccione...</option>
                        <option value="DNI">DNI</option>
                        <option value="Pasaporte">Pasaporte</option>
                        <option value="Cedula">Cedula</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Número Doc. *</label>
                    <input type="text" class="form-control" name="acompanante_num_doc_${acompananteCount}" required>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', acompananteHtml);
}

function eliminarAcompanante(id) {
    const element = document.getElementById(`acompanante-${id}`);
    if (element) {
        element.remove();
    }
    
    // Si no hay más acompañantes, mostrar mensaje inicial
    const container = document.getElementById('acompanantesContainer');
    if (container.children.length === 0) {
        container.innerHTML = '<p class="text-muted mb-0 text-center">No hay acompañantes registrados</p>';
    }
}

function calcularEdad(id) {
    const fechaNac = document.querySelector(`input[name="acompanante_fn_${id}"]`).value;
    const edadInput = document.querySelector(`input[name="acompanante_edad_${id}"]`);
    
    if (fechaNac) {
        const hoy = new Date();
        const nacimiento = new Date(fechaNac);
        let edad = hoy.getFullYear() - nacimiento.getFullYear();
        const mes = hoy.getMonth() - nacimiento.getMonth();
        
        if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
            edad--;
        }
        
        edadInput.value = edad;
    } else {
        edadInput.value = '';
    }
}

function obtenerAcompanantes() {
    const acompanantes = [];
    const items = document.querySelectorAll('.acompanante-item');
    
    items.forEach((item, index) => {
        const id = item.id.replace('acompanante-', '');
        const nombre = document.querySelector(`input[name="acompanante_nombre_${id}"]`).value;
        const apellido = document.querySelector(`input[name="acompanante_apellido_${id}"]`).value;
        const parentesco = document.querySelector(`select[name="acompanante_parentesco_${id}"]`).value;
        const fechaNac = document.querySelector(`input[name="acompanante_fn_${id}"]`).value;
        const tipoDoc = document.querySelector(`select[name="acompanante_tipo_doc_${id}"]`).value;
        const numDoc = document.querySelector(`input[name="acompanante_num_doc_${id}"]`).value;
        
        if (nombre && apellido && tipoDoc && numDoc) {
            acompanantes.push({
                nombre: nombre,
                apellido: apellido,
                parentesco: parentesco,
                fecha_nacimiento: fechaNac,
                tipo_documento: tipoDoc,
                numero_documento: numDoc
            });
        }
    });
    
    return acompanantes;
}

// Funciones de filtrado y búsqueda
function aplicarFiltrosInternos(reservasData) {
    const buscarCliente = $('#buscarCliente').val().toLowerCase().trim();
    const buscarHabitacion = $('#buscarHabitacion').val().trim();
    const filtroEstado = $('#filtroEstado').val();
    const fechaInicio = $('#fechaInicio').val();
    const fechaFin = $('#fechaFin').val();
    
    return reservasData.filter(reserva => {
        // Filtrar por cliente
        if (buscarCliente) {
            const clienteCompleto = `${reserva.cliente_nombre} ${reserva.cliente_apellido || ''}`.toLowerCase();
            if (!clienteCompleto.includes(buscarCliente)) {
                return false;
            }
        }
        
        // Filtrar por habitación
        if (buscarHabitacion) {
            if (!reserva.habitacion_numero.toString().includes(buscarHabitacion)) {
                return false;
            }
        }
        
        // Filtrar por estado
        if (filtroEstado) {
            if (reserva.estado !== filtroEstado) {
                return false;
            }
        }
        
        // Filtrar por rango de fechas
        if (fechaInicio) {
            if (reserva.fecha_entrada < fechaInicio) {
                return false;
            }
        }
        
        if (fechaFin) {
            if (reserva.fecha_salida > fechaFin) {
                return false;
            }
        }
        
        return true;
    });
}

function aplicarFiltros() {
    paginaActual = 1; // Resetear a primera página
    cargarReservas();
}

function limpiarFiltros() {
    $('#buscarCliente').val('');
    $('#buscarHabitacion').val('');
    $('#filtroEstado').val('');
    $('#fechaInicio').val('');
    $('#fechaFin').val('');
    paginaActual = 1;
    cargarReservas();
}

// =============================================
// FUNCIONES PARA GESTIÓN DE ACOMPAÑANTES
// =============================================

function agregarAcompananteReserva() {
    // Validar que haya una habitación seleccionada
    if (!habitacionSeleccionada) {
        showNotification('Seleccione primero una habitación', 'warning');
        return;
    }
    
    // Validar capacidad disponible
    const totalActual = 1 + acompanantesTemporales.length; // 1 = cliente principal
    if (totalActual >= capacidadMaximaHabitacion) {
        showNotification('Ha alcanzado la capacidad máxima de la habitación', 'warning');
        return;
    }
    
    // Abrir modal de búsqueda
    abrirModalBusquedaPersonas();
}

function abrirModalBusquedaPersonas() {
    // Limpiar selección anterior
    $('#busquedaPersonaSelect').val('').trigger('change');
    $('#infoPersonaSeleccionada').hide();
    const modalElement = document.getElementById('modalBusquedaPersonas');
    const modal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: true
    });
    modal.show();
    
    // Cargar personas e inicializar Select2
    cargarPersonasParaSelect2();
}

function cargarPersonasParaSelect2() {
    const select = $('#busquedaPersonaSelect');
    
    if (select.length === 0) {
        console.error('No se encontró el elemento #busquedaPersonaSelect');
        return;
    }
    
    // Cargar personas desde el endpoint de clientes (que es el que funciona)
    $.get('api/endpoints/clientes_final.php', function(data) {
        console.log('Personas recibidas:', data);
        
        select.empty().append('<option value="">Buscar por nombre, apellido o documento...</option>');
        const personasList = Array.isArray(data) ? data : (data.results || data.records || []);
        console.log('Personas procesadas:', personasList.length);
        
        // Guardar referencia global para usar en templates de Select2
        window.personasDataList = personasList;
        
        // Agregar todas las personas al select
        personasList.forEach(persona => {
            const nombreCompleto = `${persona.nombre || ''} ${persona.apellido || ''}`.trim();
            const documento = persona.documento || ''; // Clientes usa 'documento' no 'numero_documento'
            const email = persona.email || '';
            
            // Crear option con atributos data para búsqueda mejorada
            const option = $(`<option value="${persona.id}">${nombreCompleto}</option>`);
            option.attr('data-documento', documento);
            option.attr('data-email', email);
            option.attr('data-nombre', persona.nombre || '');
            option.attr('data-apellido', persona.apellido || '');
            
            select.append(option);
        });
        
        // Destruir Select2 si ya existe
        try {
            if (select.data('select2')) {
                select.select2('destroy');
                select.removeData('select2');
                select.removeClass('select2-hidden-accessible');
            }
        } catch (e) {
            console.log('Error al destruir Select2:', e);
        }
        
        // Inicializar Select2 con búsqueda mejorada (EXACTAMENTE igual que el campo de cliente)
        try {
            select.select2({
                theme: 'bootstrap-5',
                placeholder: 'Buscar por nombre, apellido o documento...',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#modalBusquedaPersonas'),
                minimumInputLength: 0, // Mostrar todos los resultados al abrir
                language: {
                    noResults: function() {
                        return 'No se encontraron personas';
                    },
                    searching: function() {
                        return 'Buscando...';
                    }
                },
                // Matcher personalizado para búsqueda en múltiples campos (igual que clientes)
                matcher: function(params, data) {
                    // Si no hay término de búsqueda, mostrar todos los resultados
                    if (!params.term || $.trim(params.term) === '') {
                        return data;
                    }
                    
                    const searchTerm = params.term.toLowerCase().trim();
                    const text = (data.text || '').toLowerCase();
                    
                    // Buscar en el texto del option
                    if (text.indexOf(searchTerm) > -1) {
                        return data;
                    }
                    
                    // Buscar en atributos data (igual que clientes)
                    const $element = $(data.element);
                    const documento = ($element.attr('data-documento') || '').toLowerCase();
                    const email = ($element.attr('data-email') || '').toLowerCase();
                    const nombre = ($element.attr('data-nombre') || '').toLowerCase();
                    const apellido = ($element.attr('data-apellido') || '').toLowerCase();
                    
                    // Buscar en documento, email, nombre o apellido
                    if (documento.indexOf(searchTerm) > -1 || 
                        email.indexOf(searchTerm) > -1 ||
                        nombre.indexOf(searchTerm) > -1 ||
                        apellido.indexOf(searchTerm) > -1) {
                        return data;
                    }
                    
                    return null;
                },
                // Template para mostrar resultados con más información
                templateResult: function(data) {
                    if (!data.id) {
                        return data.text;
                    }
                    
                    const personaData = window.personasDataList?.find(p => p.id == data.id);
                    if (personaData) {
                        const nombreCompleto = `${personaData.nombre || ''} ${personaData.apellido || ''}`.trim();
                        const documento = personaData.documento || '';
                        const email = personaData.email || '';
                        
                        return $(`
                            <div class="cliente-option p-2">
                                <div class="fw-bold text-primary">${nombreCompleto}</div>
                                ${documento ? `<div class="text-muted small"><i class="fas fa-id-card me-1"></i>${documento}</div>` : ''}
                                ${email ? `<div class="text-muted small"><i class="fas fa-envelope me-1"></i>${email}</div>` : ''}
                            </div>
                        `);
                    }
                    
                    return data.text;
                },
                templateSelection: function(data) {
                    if (!data.id) {
                        return data.text;
                    }
                    
                    const personaData = window.personasDataList?.find(p => p.id == data.id);
                    if (personaData) {
                        const nombreCompleto = `${personaData.nombre || ''} ${personaData.apellido || ''}`.trim();
                        return nombreCompleto;
                    }
                    
                    return data.text;
                }
            });
            
            console.log('Select2 de búsqueda de personas inicializado correctamente');
            
            // Eventos EXACTAMENTE iguales a los del campo de cliente
            
            // Asegurar que el campo de búsqueda esté siempre visible y funcional
            select.on('select2:open', function() {
                setTimeout(() => {
                    const searchField = $('.select2-search__field');
                    if (searchField.length > 0) {
                        searchField.focus();
                        // Limpiar el campo de búsqueda para mostrar todos los resultados
                        searchField.val('');
                        // Disparar evento input para actualizar resultados
                        searchField.trigger('input');
                    }
                }, 100);
            });
            
            // Abrir automáticamente el dropdown al hacer clic en el campo
            select.on('select2:select', function() {
                // Cerrar después de seleccionar
                setTimeout(() => {
                    select.select2('close');
                }, 100);
            });
            
            // Abrir dropdown automáticamente al hacer clic en el contenedor
            setTimeout(() => {
                const select2Container = select.next('.select2-container');
                if (select2Container.length > 0) {
                    select2Container.on('click', function(e) {
                        // Solo abrir si no está ya abierto
                        if (!select2Container.hasClass('select2-container--open')) {
                            select.select2('open');
                        }
                    });
                }
            }, 200);
            
            // Evento change para mostrar información de la persona seleccionada
            select.on('change', function() {
                const selectedId = $(this).val();
                
                if (selectedId) {
                    const personaData = window.personasDataList?.find(p => p.id == selectedId);
                    if (personaData) {
                        // Agregar directamente como acompañante sin confirmación
                        agregarPersonaComoAcompananteDirecto(personaData);
                    } else {
                        $('#infoPersonaSeleccionada').hide();
                    }
                } else {
                    $('#infoPersonaSeleccionada').hide();
                }
            });
        } catch (e) {
            console.error('Error al inicializar Select2:', e);
        }
    }).fail(function(xhr) {
        console.error('Error al cargar personas:', xhr);
        showNotification('Error al cargar personas', 'error');
    });
}

function agregarPersonaComoAcompananteDirecto(persona) {
    // Validar capacidad antes de agregar
    const totalActual = 1 + acompanantesTemporales.length;
    if (totalActual >= capacidadMaximaHabitacion) {
        showNotification('Ha alcanzado la capacidad máxima de la habitación', 'warning');
        return;
    }
    
    // Verificar si ya está agregado
    const yaExiste = acompanantesTemporales.find(a => a.persona_id == persona.id);
    if (yaExiste) {
        showNotification('Esta persona ya está agregada como acompañante', 'warning');
        return;
    }
    
    // EVITAR QUE EL CLIENTE PRINCIPAL SE AGREGUE COMO ACOMPAÑANTE
    const clientePrincipalId = $('#cliente_id').val();
    if (persona.id == clientePrincipalId) {
        showNotification('Esta persona es el cliente principal, no se puede agregar como acompañante', 'warning');
        return;
    }
    
    // Agregar a la lista
    const acompanante = {
        index: Date.now(),
        persona_id: persona.id,
        nombre: persona.nombre,
        apellido: persona.apellido,
        tipo_documento: 'CC', // Valor por defecto ya que clientes no tiene tipo_documento
        numero_documento: persona.documento, // Clientes usa 'documento'
        fecha_nacimiento: null, // Clientes no tiene fecha_nacimiento
        parentesco: '',
        email: persona.email,
        telefono: persona.telefono || '',
        es_menor: false // Por defecto ya que no podemos calcular edad
    };
    
    acompanantesTemporales.push(acompanante);
    
    // Actualizar interfaz
    actualizarListaAcompanantes();
    actualizarCapacidadInfo();
    
    // Cerrar modal
    $('#modalBusquedaPersonas').modal('hide');
    
    showNotification('Acompañante agregado correctamente', 'success');
}

function mostrarInfoPersonaSeleccionada(persona) {
    const nombreCompleto = `${persona.nombre || ''} ${persona.apellido || ''}`.trim();
    const documento = persona.documento || '';
    const email = persona.email || '';
    
    $('#nombrePersonaSeleccionada').html(nombreCompleto);
    $('#detallesPersonaSeleccionada').html(`
        ${documento ? `Documento: ${documento}` : ''}
        ${email ? `• ${email}` : ''}
    `);
    
    // Guardar datos de la persona para usarlos después
    $('#infoPersonaSeleccionada').data('persona', persona);
    $('#infoPersonaSeleccionada').show();
}

function confirmarPersonaSeleccionada() {
    const persona = $('#infoPersonaSeleccionada').data('persona');
    
    if (!persona) {
        showNotification('Seleccione una persona primero', 'warning');
        return;
    }
    
    // Validar capacidad antes de agregar
    const totalActual = 1 + acompanantesTemporales.length;
    if (totalActual >= capacidadMaximaHabitacion) {
        showNotification('Ha alcanzado la capacidad máxima de la habitación', 'warning');
        return;
    }
    
    // Verificar si ya está agregado
    const yaExiste = acompanantesTemporales.find(a => a.persona_id == persona.id);
    if (yaExiste) {
        showNotification('Esta persona ya está agregada como acompañante', 'warning');
        return;
    }
    
    // EVITAR QUE EL CLIENTE PRINCIPAL SE AGREGUE COMO ACOMPAÑANTE
    const clientePrincipalId = $('#cliente_id').val();
    if (persona.id == clientePrincipalId) {
        showNotification('Esta persona es el cliente principal, no se puede agregar como acompañante', 'warning');
        return;
    }
    
    // Agregar a la lista
    const acompanante = {
        index: Date.now(),
        persona_id: persona.id,
        nombre: persona.nombre,
        apellido: persona.apellido,
        tipo_documento: 'CC', // Valor por defecto ya que clientes no tiene tipo_documento
        numero_documento: persona.documento, // Clientes usa 'documento'
        fecha_nacimiento: null, // Clientes no tiene fecha_nacimiento
        parentesco: '',
        email: persona.email,
        telefono: persona.telefono || '',
        es_menor: false // Por defecto ya que no podemos calcular edad
    };
    
    acompanantesTemporales.push(acompanante);
    
    // Actualizar interfaz
    actualizarListaAcompanantes();
    actualizarCapacidadInfo();
    
    // Cerrar modal
    $('#modalBusquedaPersonas').modal('hide');
    
    showNotification('Acompañante agregado correctamente', 'success');
}

function crearNuevaPersonaAcompanante() {
    // Cerrar modal de búsqueda
    $('#modalBusquedaPersonas').modal('hide');
    
    // Abrir modal de cliente en modo acompañante
    modoAcompanante = true;
    $('#modalTitle').text('Nuevo Acompañante');
    $('#formCliente')[0].reset();
    $('#cliente_form_id').val('');
    
    // Cambiar texto del botón de guardar
    $('button[form="formCliente"]').html('<i class="fas fa-plus me-2"></i>Agregar como Acompañante');
    
    // Abrir modal de cliente
    const modalElement = document.getElementById('modalCliente');
    const modal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: true
    });
    modal.show();
}

function actualizarListaAcompanantes() {
    const container = $('#acompanantesReservaContainer');
    const contador = $('#contadorAcompanantes');
    
    contador.text(acompanantesTemporales.length);
    
    if (acompanantesTemporales.length === 0) {
        container.html('<p class="text-muted mb-0 text-center">No hay acompañantes registrados</p>');
        return;
    }
    
    let html = '';
    acompanantesTemporales.forEach((acompanante, index) => {
        const edad = acompanante.fecha_nacimiento ? calcularEdad(acompanante.fecha_nacimiento) : 'N/A';
        const menorBadge = acompanante.es_menor ? '<span class="badge bg-warning ms-1">Menor</span>' : '';
        
        html += `
            <div class="d-flex justify-content-between align-items-center p-2 border-bottom acompanante-item">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center">
                        <strong>${acompanante.nombre} ${acompanante.apellido}</strong>
                        ${menorBadge}
                    </div>
                    <small class="text-muted d-block">
                        ${acompanante.tipo_documento}: ${acompanante.numero_documento}
                        ${acompanante.parentesco ? `• ${acompanante.parentesco}` : ''}
                        ${edad !== 'N/A' ? `• ${edad} años` : ''}
                    </small>
                </div>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-danger" onclick="eliminarAcompanante(${acompanante.index})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    container.html(html);
}

function eliminarAcompanante(index) {
    if (confirm('¿Está seguro de eliminar este acompañante?')) {
        acompanantesTemporales = acompanantesTemporales.filter(a => a.index != index);
        actualizarListaAcompanantes();
        actualizarCapacidadInfo();
        showNotification('Acompañante eliminado', 'info');
    }
}

function calcularEdad(fechaNacimiento) {
    if (!fechaNacimiento) return 'N/A';
    
    const hoy = new Date();
    const nacimiento = new Date(fechaNacimiento);
    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const mes = hoy.getMonth() - nacimiento.getMonth();
    
    if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
        edad--;
    }
    
    return edad;
}

function actualizarCapacidadInfo() {
    const totalHuéspedes = 1 + acompanantesTemporales.length; // 1 = cliente principal
    const disponibles = capacidadMaximaHabitacion - totalHuéspedes;
    
    const alert = $('#capacidadAlert');
    const texto = $('#textoCapacidad');
    
    if (capacidadMaximaHabitacion === 0) {
        texto.text('Seleccione una habitación para ver la capacidad');
        alert.removeClass('alert-success alert-warning alert-danger').addClass('alert-info');
    } else if (disponibles > 0) {
        texto.html(`Ocupación: ${totalHuéspedes}/${capacidadMaximaHabitacion} huéspedes • ${disponibles} disponible(s)`);
        alert.removeClass('alert-info alert-warning alert-danger').addClass('alert-success');
    } else if (disponibles === 0) {
        texto.html(`Ocupación: ${totalHuéspedes}/${capacidadMaximaHabitacion} huéspedes • Capacidad completa`);
        alert.removeClass('alert-info alert-success alert-danger').addClass('alert-warning');
    } else {
        texto.html(`Ocupación: ${totalHuéspedes}/${capacidadMaximaHabitacion} huéspedes • <strong>Excede capacidad</strong>`);
        alert.removeClass('alert-info alert-success alert-warning').addClass('alert-danger');
    }
    
    // Actualizar número de huéspedes en el formulario
    $('#numero_huespedes').val(totalHuéspedes);
}

function obtenerAcompanantesReserva() {
    return acompanantesTemporales.map(a => ({
        persona_id: a.persona_id,
        nombre: a.nombre,
        apellido: a.apellido,
        tipo_documento: a.tipo_documento,
        numero_documento: a.numero_documento,
        fecha_nacimiento: a.fecha_nacimiento,
        parentesco: a.parentesco,
        email: a.email,
        telefono: a.telefono
    }));
}

function limpiarAcompanantesTemporales() {
    acompanantesTemporales = [];
    actualizarListaAcompanantes();
    actualizarCapacidadInfo();
}

function cargarAcompanantesReserva(reservaId) {
    // Como la tabla reserva_huespedes no existe, 
    // intentamos cargar desde las observaciones de la reserva
    console.log(`Intentando cargar acompañantes para reserva ${reservaId} desde observaciones`);
    
    // Limpiar acompañantes temporales
    acompanantesTemporales = [];
    
    // Intentar obtener los datos de la reserva actual
    $.get(`api/endpoints/reservas.php?id=${reservaId}`)
        .done(function(reserva) {
            console.log('Datos de reserva recibidos:', reserva);
            console.log('Observaciones de la reserva:', reserva.observaciones);
            
            if (reserva.observaciones) {
                try {
                    // Buscar el JSON de acompañantes en las observaciones
                    const obsText = reserva.observaciones;
                    const acompanantesMatch = obsText.match(/ACOMPANANTES:\s*(\[.*?\])/s);
                    
                    if (acompanantesMatch && acompanantesMatch[1]) {
                        const acompanantesJSON = acompanantesMatch[1];
                        const acompanantesData = JSON.parse(acompanantesJSON);
                        
                        console.log('Acompañantes encontrados en observaciones:', acompanantesData);
                        
                        // Obtener el ID del cliente principal para excluirlo
                        const clientePrincipalId = reserva.cliente_id;
                        
                        // Agregar solo los que no sean el cliente principal
                        acompanantesData.forEach((acompanante, index) => {
                            if (acompanante.persona_id != clientePrincipalId) {
                                acompanantesTemporales.push({
                                    index: Date.now() + index,
                                    persona_id: acompanante.persona_id,
                                    nombre: acompanante.nombre,
                                    apellido: acompanante.apellido,
                                    tipo_documento: acompanante.tipo_documento,
                                    numero_documento: acompanante.numero_documento,
                                    fecha_nacimiento: acompanante.fecha_nacimiento,
                                    parentesco: acompanante.parentesco,
                                    email: acompanante.email,
                                    telefono: acompanante.telefono,
                                    es_menor: acompanante.es_menor || false
                                });
                            }
                        });
                        
                        console.log('Acompañantes cargados:', acompanantesTemporales.length);
                    } else {
                        console.log('No se encontró JSON de acompañantes en las observaciones');
                    }
                } catch (e) {
                    console.error('Error parsing acompañantes desde observaciones:', e);
                }
            } else {
                console.log('La reserva no tiene observaciones');
            }
            
            // Actualizar la interfaz
            actualizarListaAcompanantes();
            actualizarCapacidadInfo();
        })
        .fail(function(xhr) {
            console.error('Error cargando reserva:', xhr);
            // Actualizar interfaz vacía
            actualizarListaAcompanantes();
            actualizarCapacidadInfo();
        });
}

function showNotification(message, type) {
    // Implementación de la función showNotification
}

function verHistorialPedidos(habitacionId, habitacionNumero, clienteNombre) {
    // Actualizar información en el modal
    $('#infoReserva').text(`Habitación ${habitacionNumero} - ${clienteNombre}`);
    
    // Mostrar modal
    const modalElement = document.getElementById('modalHistorialPedidos');
    const modal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: true
    });
    modal.show();
    
    // Cargar historial de pedidos
    $.get(`api/endpoints/pedidos_productos.php?reserva_id=${habitacionId}`, function(data) {
        const pedidos = Array.isArray(data) ? data : (data.records || []);
        
        if (pedidos.length === 0) {
            $('#historialPedidosContent').html(`
                <div class="text-center py-4">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay pedidos registrados</h5>
                    <p class="text-muted">Esta reserva aún no tiene pedidos de productos.</p>
                </div>
            `);
            return;
        }
        
        let html = '<div class="row">';
        
        pedidos.forEach((pedido, index) => {
            const fechaPedido = new Date(pedido.fecha_pedido).toLocaleString('es-CO');
            const detallesHtml = pedido.detalles ? pedido.detalles.map(detalle => 
                `<tr>
                    <td>${detalle.producto_nombre}</td>
                    <td>${detalle.categoria || ''}</td>
                    <td>${detalle.cliente_nombre ? detalle.cliente_nombre + ' ' + (detalle.cliente_apellido || '') : 'No especificado'}</td>
                    <td>${detalle.cantidad}</td>
                    <td>$${parseFloat(detalle.precio_unitario).toLocaleString('es-CO')}</td>
                    <td>$${parseFloat(detalle.subtotal).toLocaleString('es-CO')}</td>
                </tr>`
            ).join('') : '';
            
            html += `
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-receipt me-2"></i>Pedido #${pedido.id}
                            </h6>
                            <span class="badge bg-primary">$${parseFloat(pedido.total || 0).toLocaleString('es-CO')}</span>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>${fechaPedido}
                                </small>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Categoría</th>
                                            <th>Cliente</th>
                                            <th>Cant.</th>
                                            <th>Precio</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${detallesHtml}
                                    </tbody>
                                </table>
                            </div>
                            ${pedido.notas ? `
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <strong>Notas:</strong> ${pedido.notas}
                                    </small>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        
        // Agregar resumen total
        const totalGeneral = pedidos.reduce((sum, pedido) => sum + parseFloat(pedido.total || 0), 0);
        html += `
            <div class="row mt-3">
                <div class="col-12">
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-chart-line me-2"></i>
                                <strong>Resumen General</strong><br>
                                <small class="text-muted">
                                    ${pedidos.length} pedido(s) • Total consumido: $${totalGeneral.toLocaleString('es-CO')}
                                </small>
                            </div>
                            <div class="text-end">
                                <h4 class="mb-0">$${totalGeneral.toLocaleString('es-CO')}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#historialPedidosContent').html(html);
    }).fail(function() {
        $('#historialPedidosContent').html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Error al cargar el historial de pedidos
            </div>
        `);
    });
}

// Lista de países con banderas
const paises = [
    { id: 'AF', text: '🇦🇫 Afganistán' },
    { id: 'AL', text: '🇦🇱 Albania' },
    { id: 'DE', text: '🇩🇪 Alemania' },
    { id: 'AD', text: '🇦🇩 Andorra' },
    { id: 'AO', text: '🇦🇴 Angola' },
    { id: 'AI', text: '🇦🇮 Anguila' },
    { id: 'AQ', text: '🇦🇶 Antártida' },
    { id: 'AG', text: '🇦🇬 Antigua y Barbuda' },
    { id: 'SA', text: '🇸🇦 Arabia Saudita' },
    { id: 'DZ', text: '🇩🇿 Argelia' },
    { id: 'AR', text: '🇦🇷 Argentina' },
    { id: 'AM', text: '🇦🇲 Armenia' },
    { id: 'AW', text: '🇦🇼 Aruba' },
    { id: 'AU', text: '🇦🇺 Australia' },
    { id: 'AT', text: '🇦🇹 Austria' },
    { id: 'AZ', text: '🇦🇿 Azerbaiyán' },
    { id: 'BS', text: '🇧🇸 Bahamas' },
    { id: 'BD', text: '🇧🇩 Bangladés' },
    { id: 'BB', text: '🇧🇧 Barbados' },
    { id: 'BH', text: '🇧🇭 Baréin' },
    { id: 'BE', text: '🇧🇪 Bélgica' },
    { id: 'BZ', text: '🇧🇿 Belice' },
    { id: 'BJ', text: '🇧🇯 Benín' },
    { id: 'BM', text: '🇧🇲 Bermudas' },
    { id: 'BY', text: '🇧🇾 Bielorrusia' },
    { id: 'BO', text: '🇧🇴 Bolivia' },
    { id: 'BA', text: '🇧🇦 Bosnia y Herzegovina' },
    { id: 'BW', text: '🇧🇼 Botsuana' },
    { id: 'BR', text: '🇧🇷 Brasil' },
    { id: 'BN', text: '🇧🇳 Brunéi' },
    { id: 'BG', text: '🇧🇬 Bulgaria' },
    { id: 'BF', text: '🇧🇫 Burkina Faso' },
    { id: 'BI', text: '🇧🇮 Burundi' },
    { id: 'BT', text: '🇧🇹 Bután' },
    { id: 'CV', text: '🇨🇻 Cabo Verde' },
    { id: 'KH', text: '🇰🇭 Camboya' },
    { id: 'CM', text: '🇨🇲 Camerún' },
    { id: 'CA', text: '🇨🇦 Canadá' },
    { id: 'BQ', text: '🇧🇶 Caribe Neerlandés' },
    { id: 'QA', text: '🇶🇦 Catar' },
    { id: 'TD', text: '🇹🇩 Chad' },
    { id: 'CZ', text: '🇨🇿 Chequia' },
    { id: 'CL', text: '🇨🇱 Chile' },
    { id: 'CN', text: '🇨🇳 China' },
    { id: 'CY', text: '🇨🇾 Chipre' },
    { id: 'VA', text: '🇻🇦 Ciudad del Vaticano' },
    { id: 'CO', text: '🇨🇴 Colombia' },
    { id: 'KM', text: '🇰🇲 Comoras' },
    { id: 'CG', text: '🇨🇬 Congo' },
    { id: 'CD', text: '🇨🇩 Congo Democrático' },
    { id: 'KP', text: '🇰🇵 Corea del Norte' },
    { id: 'KR', text: '🇰🇷 Corea del Sur' },
    { id: 'CI', text: '🇨🇮 Costa de Marfil' },
    { id: 'CR', text: '🇨🇷 Costa Rica' },
    { id: 'HR', text: '🇭🇷 Croacia' },
    { id: 'CU', text: '🇨🇺 Cuba' },
    { id: 'DK', text: '🇩🇰 Dinamarca' },
    { id: 'DM', text: '🇩🇲 Dominica' },
    { id: 'EC', text: '🇪🇨 Ecuador' },
    { id: 'EG', text: '🇪🇬 Egipto' },
    { id: 'SV', text: '🇸🇻 El Salvador' },
    { id: 'AE', text: '🇦🇪 Emiratos Árabes Unidos' },
    { id: 'ER', text: '🇪🇷 Eritrea' },
    { id: 'SK', text: '🇸🇰 Eslovaquia' },
    { id: 'SI', text: '🇸🇮 Eslovenia' },
    { id: 'ES', text: '🇪🇸 España' },
    { id: 'US', text: '🇺🇸 Estados Unidos' },
    { id: 'EE', text: '🇪🇪 Estonia' },
    { id: 'SZ', text: '🇸🇿 Esuatini' },
    { id: 'ET', text: '🇪🇹 Etiopía' },
    { id: 'PH', text: '🇵🇭 Filipinas' },
    { id: 'FI', text: '🇫🇮 Finlandia' },
    { id: 'FJ', text: '🇫🇯 Fiyi' },
    { id: 'FR', text: '🇫🇷 Francia' },
    { id: 'GA', text: '🇬🇦 Gabón' },
    { id: 'GM', text: '🇬🇲 Gambia' },
    { id: 'GE', text: '🇬🇪 Georgia' },
    { id: 'GH', text: '🇬🇭 Ghana' },
    { id: 'GI', text: '🇬🇮 Gibraltar' },
    { id: 'GD', text: '🇬🇩 Granada' },
    { id: 'GR', text: '🇬🇷 Grecia' },
    { id: 'GL', text: '🇬🇱 Groenlandia' },
    { id: 'GP', text: '🇬🇵 Guadalupe' },
    { id: 'GU', text: '🇬🇺 Guam' },
    { id: 'GT', text: '🇬🇹 Guatemala' },
    { id: 'GF', text: '🇬🇫 Guayana Francesa' },
    { id: 'GG', text: '🇬🇬 Guernsey' },
    { id: 'GN', text: '🇬🇳 Guinea' },
    { id: 'GQ', text: '🇬🇶 Guinea Ecuatorial' },
    { id: 'GW', text: '🇬🇼 Guinea-Bisáu' },
    { id: 'GY', text: '🇬🇾 Guyana' },
    { id: 'HT', text: '🇭🇹 Haití' },
    { id: 'HN', text: '🇭🇳 Honduras' },
    { id: 'HK', text: '🇭🇰 Hong Kong' },
    { id: 'HU', text: '🇭🇺 Hungría' },
    { id: 'IN', text: '🇮🇳 India' },
    { id: 'ID', text: '🇮🇩 Indonesia' },
    { id: 'IQ', text: '🇮🇶 Irak' },
    { id: 'IR', text: '🇮🇷 Irán' },
    { id: 'IE', text: '🇮🇪 Irlanda' },
    { id: 'IM', text: '🇮🇲 Isla de Man' },
    { id: 'IS', text: '🇮🇸 Islandia' },
    { id: 'IL', text: '🇮🇱 Israel' },
    { id: 'IT', text: '🇮🇹 Italia' },
    { id: 'JM', text: '🇯🇲 Jamaica' },
    { id: 'JP', text: '🇯🇵 Japón' },
    { id: 'JE', text: '🇯🇪 Jersey' },
    { id: 'JO', text: '🇯🇴 Jordania' },
    { id: 'KZ', text: '🇰🇿 Kazajistán' },
    { id: 'KE', text: '🇰🇪 Kenia' },
    { id: 'KG', text: '🇰🇬 Kirguistán' },
    { id: 'KI', text: '🇰🇮 Kiribati' },
    { id: 'KW', text: '🇰🇼 Kuwait' },
    { id: 'LA', text: '🇱🇦 Laos' },
    { id: 'LS', text: '🇱🇸 Lesoto' },
    { id: 'LV', text: '🇱🇻 Letonia' },
    { id: 'LB', text: '🇱🇧 Líbano' },
    { id: 'LR', text: '🇱🇷 Liberia' },
    { id: 'LY', text: '🇱🇾 Libia' },
    { id: 'LI', text: '🇱🇮 Liechtenstein' },
    { id: 'LT', text: '🇱🇹 Lituania' },
    { id: 'LU', text: '🇱🇺 Luxemburgo' },
    { id: 'MO', text: '🇲🇴 Macao' },
    { id: 'MK', text: '🇲🇰 Macedonia del Norte' },
    { id: 'MG', text: '🇲🇬 Madagascar' },
    { id: 'MY', text: '🇲🇾 Malasia' },
    { id: 'MW', text: '🇲🇼 Malaui' },
    { id: 'MV', text: '🇲🇻 Maldivas' },
    { id: 'ML', text: '🇲🇱 Malí' },
    { id: 'MT', text: '🇲🇹 Malta' },
    { id: 'MA', text: '🇲🇦 Marruecos' },
    { id: 'MQ', text: '🇲🇶 Martinica' },
    { id: 'MU', text: '🇲🇺 Mauricio' },
    { id: 'MR', text: '🇲🇷 Mauritania' },
    { id: 'YT', text: '🇾🇹 Mayotte' },
    { id: 'MX', text: '🇲🇽 México' },
    { id: 'FM', text: '🇫🇲 Micronesia' },
    { id: 'MD', text: '🇲🇩 Moldavia' },
    { id: 'MC', text: '🇲🇨 Mónaco' },
    { id: 'MN', text: '🇲🇳 Mongolia' },
    { id: 'ME', text: '🇲🇪 Montenegro' },
    { id: 'MS', text: '🇲🇸 Montserrat' },
    { id: 'MZ', text: '🇲🇿 Mozambique' },
    { id: 'MM', text: '🇲🇲 Myanmar' },
    { id: 'NA', text: '🇳🇦 Namibia' },
    { id: 'NR', text: '🇳🇷 Nauru' },
    { id: 'NP', text: '🇳🇵 Nepal' },
    { id: 'NI', text: '🇳🇮 Nicaragua' },
    { id: 'NE', text: '🇳🇪 Níger' },
    { id: 'NG', text: '🇳🇬 Nigeria' },
    { id: 'NU', text: '🇳🇺 Niue' },
    { id: 'NO', text: '🇳🇴 Noruega' },
    { id: 'NC', text: '🇳🇨 Nueva Caledonia' },
    { id: 'NZ', text: '🇳🇿 Nueva Zelanda' },
    { id: 'OM', text: '🇴🇲 Omán' },
    { id: 'NL', text: '🇳🇱 Países Bajos' },
    { id: 'PK', text: '🇵🇰 Pakistán' },
    { id: 'PW', text: '🇵🇼 Palaos' },
    { id: 'PA', text: '🇵🇦 Panamá' },
    { id: 'PG', text: '🇵🇬 Papúa Nueva Guinea' },
    { id: 'PY', text: '🇵🇾 Paraguay' },
    { id: 'PE', text: '🇵🇪 Perú' },
    { id: 'PF', text: '🇵🇫 Polinesia Francesa' },
    { id: 'PL', text: '🇵🇱 Polonia' },
    { id: 'PT', text: '🇵🇹 Portugal' },
    { id: 'PR', text: '🇵🇷 Puerto Rico' },
    { id: 'GB', text: '🇬🇧 Reino Unido' },
    { id: 'CF', text: '🇨🇫 República Centroafricana' },
    { id: 'DO', text: '🇩🇴 República Dominicana' },
    { id: 'RE', text: '🇷🇪 Reunión' },
    { id: 'RW', text: '🇷🇼 Ruanda' },
    { id: 'RO', text: '🇷🇴 Rumanía' },
    { id: 'RU', text: '🇷🇺 Rusia' },
    { id: 'EH', text: '🇪🇭 Sáhara Occidental' },
    { id: 'BL', text: '🇧🇱 San Bartolomé' },
    { id: 'KN', text: '🇰🇳 San Cristóbal y Nieves' },
    { id: 'SM', text: '🇸🇲 San Marino' },
    { id: 'MF', text: '🇲🇫 San Martín' },
    { id: 'PM', text: '🇵🇲 San Pedro y Miquelón' },
    { id: 'VC', text: '🇻🇨 San Vicente y las Granadinas' },
    { id: 'WS', text: '🇼🇸 Samoa' },
    { id: 'AS', text: '🇦🇸 Samoa Americana' },
    { id: 'LC', text: '🇱🇨 Santa Lucía' },
    { id: 'ST', text: '🇸🇹 Santo Tomé y Príncipe' },
    { id: 'SN', text: '🇸🇳 Senegal' },
    { id: 'RS', text: '🇷🇸 Serbia' },
    { id: 'SC', text: '🇸🇨 Seychelles' },
    { id: 'SL', text: '🇸🇱 Sierra Leona' },
    { id: 'SG', text: '🇸🇬 Singapur' },
    { id: 'SX', text: '🇸🇽 Sint Maarten' },
    { id: 'SY', text: '🇸🇾 Siria' },
    { id: 'SO', text: '🇸🇴 Somalia' },
    { id: 'LK', text: '🇱🇰 Sri Lanka' },
    { id: 'ZA', text: '🇿🇦 Sudáfrica' },
    { id: 'SD', text: '🇸🇩 Sudán' },
    { id: 'SS', text: '🇸🇸 Sudán del Sur' },
    { id: 'SE', text: '🇸🇪 Suecia' },
    { id: 'CH', text: '🇨🇭 Suiza' },
    { id: 'SR', text: '🇸🇷 Surinam' },
    { id: 'SJ', text: '🇸🇯 Svalbard y Jan Mayen' },
    { id: 'TH', text: '🇹🇭 Tailandia' },
    { id: 'TW', text: '🇹🇼 Taiwán' },
    { id: 'TZ', text: '🇹🇿 Tanzania' },
    { id: 'TJ', text: '🇹🇯 Tayikistán' },
    { id: 'IO', text: '🇮🇴 Territorio Británico del Océano Índico' },
    { id: 'TF', text: '🇹🇫 Territorios Australes Franceses' },
    { id: 'TL', text: '🇹🇱 Timor-Leste' },
    { id: 'TG', text: '🇹🇬 Togo' },
    { id: 'TK', text: '🇹🇰 Tokelau' },
    { id: 'TO', text: '🇹🇴 Tonga' },
    { id: 'TT', text: '🇹🇹 Trinidad y Tobago' },
    { id: 'TN', text: '🇹🇳 Túnez' },
    { id: 'TM', text: '🇹🇲 Turkmenistán' },
    { id: 'TR', text: '🇹🇷 Turquía' },
    { id: 'TV', text: '🇹🇻 Tuvalu' },
    { id: 'UA', text: '🇺🇦 Ucrania' },
    { id: 'UG', text: '🇺🇬 Uganda' },
    { id: 'UY', text: '🇺🇾 Uruguay' },
    { id: 'UZ', text: '🇺🇿 Uzbekistán' },
    { id: 'VU', text: '🇻🇺 Vanuatu' },
    { id: 'VE', text: '🇻🇪 Venezuela' },
    { id: 'VN', text: '🇻🇳 Vietnam' },
    { id: 'WF', text: '🇼🇫 Wallis y Futuna' },
    { id: 'YE', text: '🇾🇪 Yemen' },
    { id: 'DJ', text: '🇩🇯 Yibuti' },
    { id: 'ZM', text: '🇿🇲 Zambia' },
    { id: 'ZW', text: '🇿🇼 Zimbabue' }
];

// Inicializar Select2 para nacionalidad en el modal de cliente
$(document).ready(function() {
    $('#nacionalidad').select2({
        data: paises,
        placeholder: 'Seleccione un país...',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#modalCliente'),
        minimumInputLength: 0,
        language: {
            noResults: function() {
                return 'No se encontraron países';
            },
            searching: function() {
                return 'Buscando...';
            }
        },
        dropdownPosition: 'below',
        // Forzar que el dropdown se abra siempre hacia abajo
        dropdownCssClass: 'select2-dropdown-below'
    });
    
    // Corregir posición del dropdown cuando el modal tiene scroll
    $('#nacionalidad').on('select2:open', function(e) {
        const modal = $('#modalCliente');
        const modalScrollTop = modal.scrollTop();
        const select2Dropdown = $('.select2-dropdown');
        
        // Esperar a que el dropdown se renderice
        setTimeout(function() {
            const dropdownTop = select2Dropdown.offset().top;
            const selectTop = $('#nacionalidad').offset().top;
            
            // Si el dropdown está arriba del select, corregir posición
            if (dropdownTop < selectTop) {
                const newTop = selectTop - modal.offset().top + $('#nacionalidad').outerHeight() + modalScrollTop;
                select2Dropdown.css({
                    'top': newTop + 'px',
                    'position': 'absolute'
                });
            }
        }, 10);
    });
});

</script>

<style>
/* Estilos para mejorar Select2 en el modal */
#modalReserva .select2-container {
    width: 100% !important;
}

#modalReserva .select2-container--open .select2-dropdown {
    z-index: 1055;
}

#modalReserva .select2-search--dropdown .select2-search__field {
    width: 100%;
    padding: 8px;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}

#modalReserva .select2-results__option {
    padding: 8px;
}

#modalReserva .select2-results__option--highlighted {
    background-color: #0d6efd;
    color: white;
}

#modalReserva .cliente-option {
    cursor: pointer;
}

#modalReserva .cliente-option:hover {
    background-color: #f8f9fa;
}

/* Estilos específicos para Select2 en el modal de clientes */
#modalCliente .select2-container {
    width: 100% !important;
}

#modalCliente .select2-container--open .select2-dropdown {
    z-index: 1055;
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
}

#modalCliente .select2-search--dropdown .select2-search__field {
    width: 100%;
    padding: 8px;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}

#modalCliente .select2-results__option {
    padding: 8px;
}

#modalCliente .select2-results__option--highlighted {
    background-color: #0d6efd;
    color: white;
}

/* Forzar que el dropdown siempre aparezca hacia abajo */
#modalCliente .select2-dropdown.select2-dropdown-below {
    top: auto !important;
    bottom: auto !important;
    margin-top: 1px !important;
}

#modalCliente .select2-dropdown {
    position: absolute !important;
    z-index: 1055 !important;
}

/* Estilos para Select2 en modal de búsqueda de personas */
#modalBusquedaPersonas .select2-container {
    width: 100% !important;
}

#modalBusquedaPersonas .select2-container--open .select2-dropdown {
    z-index: 1055;
}

#modalBusquedaPersonas .select2-search--dropdown .select2-search__field {
    width: 100%;
    padding: 8px;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}

#modalBusquedaPersonas .select2-results__option {
    padding: 8px;
}

#modalBusquedaPersonas .select2-results__option--highlighted {
    background-color: #0d6efd;
    color: white;
}

/* Estilos para gestión de acompañantes */
.cursor-pointer {
    cursor: pointer;
}

.acompanante-item {
    transition: background-color 0.2s ease;
}

.acompanante-item:hover {
    background-color: #f8f9fa;
}

.badge {
    font-size: 0.75em;
}

.alert {
    border: none;
    border-radius: 0.5rem;
}

.alert-info {
    background-color: #e7f3ff;
    color: #0c5460;
}

.alert-success {
    background-color: #d1e7dd;
    color: #0f5132;
}

.alert-warning {
    background-color: #fff3cd;
    color: #664d03;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
}

/* Scroll para lista de acompañantes */
#acompanantesReservaContainer {
    scrollbar-width: thin;
    scrollbar-color: #dee2e6 #f8f9fa;
}

#acompanantesReservaContainer::-webkit-scrollbar {
    width: 6px;
}

#acompanantesReservaContainer::-webkit-scrollbar-track {
    background: #f8f9fa;
}

#acompanantesReservaContainer::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 3px;
}

#acompanantesReservaContainer::-webkit-scrollbar-thumb:hover {
    background: #adb5bd;
}
</style>

<?php include 'includes/footer.php'; ?>