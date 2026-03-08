<?php
require_once __DIR__ . '/../../../backend/config/database.php';

// Verificar sesión de usuario
if (!isset($_SESSION['usuario'])) {
    header('Location: /Hotel_tame/login');
    exit;
}

$usuario_actual = $_SESSION['usuario'];

include __DIR__ . '/../../../backend/includes/header.php';
include __DIR__ . '/../../../backend/includes/sidebar.php';
?>

<div class="main-content">
    <div id="notification-container"></div>
    
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Mis Actividades</h1>
                <p class="text-muted mb-0">Resumen de todas tus actividades en el sistema</p>
            </div>
            <div>
                <button class="btn btn-outline-primary" onclick="exportarExcel()">
                    <i class="fas fa-file-excel me-2"></i>Exportar Excel
                </button>
                <button class="btn btn-outline-danger" onclick="exportarPDF()">
                    <i class="fas fa-file-pdf me-2"></i>Exportar PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Información del Usuario -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="card-title mb-1">
                        <i class="fas fa-user-circle me-2"></i>
                        <?php echo htmlspecialchars($usuario_actual['nombre'] . ' ' . $usuario_actual['apellido']); ?>
                    </h5>
                    <p class="text-muted mb-0">
                        <span class="badge bg-<?php 
                            echo match($usuario_actual['rol']) {
                                'admin' => 'danger',
                                'gerente' => 'warning',
                                'recepcionista' => 'primary',
                                'limpieza' => 'info',
                                'Contador' => 'success',
                                'Auxiliar Contable' => 'secondary',
                                default => 'secondary'
                            }; 
                        ?>">
                            <?php echo ucfirst($usuario_actual['rol']); ?>
                        </span>
                        <span class="ms-2">
                            <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($usuario_actual['email']); ?>
                        </span>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="text-muted small">Período seleccionado</div>
                    <div class="fw-bold" id="periodoSeleccionado">Mes actual</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="filtroFechaInicio">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="filtroFechaFin">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tipo de Actividad</label>
                    <select class="form-select" id="filtroTipo">
                        <option value="">Todas las actividades</option>
                        <option value="turno">Turnos</option>
                        <option value="reserva">Reservas</option>
                        <option value="pedido">Pedidos</option>
                        <option value="tarea">Tareas</option>
                    </select>
                </div>
                <div class="col-12">
                    <button class="btn btn-outline-primary" onclick="aplicarFiltros()">
                        <i class="fas fa-filter me-2"></i>Aplicar Filtros
                    </button>
                    <button class="btn btn-outline-secondary" onclick="limpiarFiltros()">
                        <i class="fas fa-times me-2"></i>Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de Actividades -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Actividades</h6>
                            <h3 id="totalActividades">0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-tasks fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Completadas</h6>
                            <h3 id="actividadesCompletadas">0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Programadas</h6>
                            <h3 id="actividadesProgramadas">0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Horas Trabajadas</h6>
                            <h3 id="horasTrabajadas">0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-hourglass-half fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs de Actividades -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="actividadesTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="todas-tab" data-bs-toggle="tab" data-bs-target="#todas" type="button" role="tab">
                        <i class="fas fa-list me-2"></i>Todas las Actividades
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="turnos-tab" data-bs-toggle="tab" data-bs-target="#turnos" type="button" role="tab">
                        <i class="fas fa-calendar-alt me-2"></i>Turnos
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reservas-tab" data-bs-toggle="tab" data-bs-target="#reservas" type="button" role="tab">
                        <i class="fas fa-bed me-2"></i>Reservas
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pedidos-tab" data-bs-toggle="tab" data-bs-target="#pedidos" type="button" role="tab">
                        <i class="fas fa-utensils me-2"></i>Pedidos
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tareas-tab" data-bs-toggle="tab" data-bs-target="#tareas" type="button" role="tab">
                        <i class="fas fa-broom me-2"></i>Tareas
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="actividadesTabContent">
                <!-- Todas las Actividades -->
                <div class="tab-pane fade show active" id="todas" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-striped" id="todasTable">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th>Detalles</th>
                                </tr>
                            </thead>
                            <tbody id="todasTableBody">
                                <!-- Las actividades se cargarán dinámicamente aquí -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Turnos -->
                <div class="tab-pane fade" id="turnos" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-striped" id="turnosTable">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo Turno</th>
                                    <th>Horario</th>
                                    <th>Estado</th>
                                    <th>Notas</th>
                                </tr>
                            </thead>
                            <tbody id="turnosTableBody">
                                <!-- Los turnos se cargarán dinámicamente aquí -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Reservas -->
                <div class="tab-pane fade" id="reservas" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-striped" id="reservasTable">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Habitación</th>
                                    <th>Cliente</th>
                                    <th>Estado</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="reservasTableBody">
                                <!-- Las reservas se cargarán dinámicamente aquí -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pedidos -->
                <div class="tab-pane fade" id="pedidos" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-striped" id="pedidosTable">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Mesa</th>
                                    <th>Items</th>
                                    <th>Estado</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="pedidosTableBody">
                                <!-- Los pedidos se cargarán dinámicamente aquí -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Tareas -->
                <div class="tab-pane fade" id="tareas" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-striped" id="tareasTable">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tarea</th>
                                    <th>Habitación</th>
                                    <th>Estado</th>
                                    <th>Completado</th>
                                </tr>
                            </thead>
                            <tbody id="tareasTableBody">
                                <!-- Las tareas se cargarán dinámicamente aquí -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const usuarioId = <?php echo $usuario_actual['id']; ?>;
let actividades = [];
let resumen = {};

$(document).ready(function() {
    // Establecer fechas por defecto (mes actual)
    const fechaActual = new Date();
    const primerDia = new Date(fechaActual.getFullYear(), fechaActual.getMonth(), 1);
    const ultimoDia = new Date(fechaActual.getFullYear(), fechaActual.getMonth() + 1, 0);
    
    $('#filtroFechaInicio').val(primerDia.toISOString().split('T')[0]);
    $('#filtroFechaFin').val(ultimoDia.toISOString().split('T')[0]);
    
    cargarResumen();
    cargarActividades();
});

function cargarResumen() {
    const fechaInicio = $('#filtroFechaInicio').val();
    const fechaFin = $('#filtroFechaFin').val();
    
    $.get(`api/endpoints/turnos.php?accion=resumen_usuario&usuario_id=${usuarioId}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`, function(data) {
        resumen = data;
        
        $('#totalActividades').text(data.total_actividades || 0);
        $('#actividadesCompletadas').text(data.actividades_completadas || 0);
        $('#actividadesProgramadas').text(data.actividades_programadas || 0);
        $('#horasTrabajadas').text(Math.round(data.horas_trabajadas || 0));
        
        // Actualizar período seleccionado
        const inicio = new Date(fechaInicio).toLocaleDateString('es-ES');
        const fin = new Date(fechaFin).toLocaleDateString('es-ES');
        $('#periodoSeleccionado').text(`${inicio} - ${fin}`);
    });
}

function cargarActividades() {
    const fechaInicio = $('#filtroFechaInicio').val();
    const fechaFin = $('#filtroFechaFin').val();
    const tipo = $('#filtroTipo').val();
    
    $.get(`api/endpoints/turnos.php?accion=actividades_usuario&usuario_id=${usuarioId}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`, function(data) {
        actividades = Array.isArray(data) ? data : [];
        
        // Filtrar por tipo si es necesario
        if (tipo) {
            actividades = actividades.filter(a => a.tipo === tipo);
        }
        
        renderizarActividades();
    });
}

function renderizarActividades() {
    renderizarTodasActividades();
    renderizarTurnos();
    renderizarReservas();
    renderizarPedidos();
    renderizarTareas();
}

function renderizarTodasActividades() {
    const tbody = $('#todasTableBody');
    tbody.empty();
    
    if (actividades.length === 0) {
        tbody.append('<tr><td colspan="5" class="text-center text-muted">No hay actividades en el período seleccionado</td></tr>');
        return;
    }
    
    actividades.forEach(actividad => {
        const estadoBadge = {
            'completado': 'success',
            'programado': 'warning',
            'en_curso': 'info',
            'ausente': 'danger'
        }[actividad.estado] || 'secondary';
        
        const tipoIcon = {
            'turno': 'fa-calendar-alt',
            'reserva': 'fa-bed',
            'pedido': 'fa-utensils',
            'tarea': 'fa-broom'
        }[actividad.tipo] || 'fa-tasks';
        
        const row = `
            <tr>
                <td>${actividad.fecha_actividad || actividad.fecha}</td>
                <td>
                    <span class="badge bg-secondary">
                        <i class="fas ${tipoIcon} me-1"></i>
                        ${actividad.tipo}
                    </span>
                </td>
                <td>${actividad.descripcion}</td>
                <td><span class="badge bg-${estadoBadge}">${actividad.estado}</span></td>
                <td>${actividad.detalles || actividad.notas || '-'}</td>
            </tr>
        `;
        tbody.append(row);
    });
}

function renderizarTurnos() {
    const tbody = $('#turnosTableBody');
    tbody.empty();
    
    const turnos = actividades.filter(a => a.tipo === 'turno');
    
    if (turnos.length === 0) {
        tbody.append('<tr><td colspan="5" class="text-center text-muted">No hay turnos en el período seleccionado</td></tr>');
        return;
    }
    
    turnos.forEach(turno => {
        const estadoBadge = {
            'completado': 'success',
            'programado': 'warning',
            'en_curso': 'info',
            'ausente': 'danger'
        }[turno.estado] || 'secondary';
        
        const row = `
            <tr>
                <td>${turno.fecha_actividad || turno.fecha}</td>
                <td>${turno.descripcion}</td>
                <td>${turno.hora_inicio} - ${turno.hora_fin}</td>
                <td><span class="badge bg-${estadoBadge}">${turno.estado}</span></td>
                <td>${turno.detalles || turno.notas || '-'}</td>
            </tr>
        `;
        tbody.append(row);
    });
}

function renderizarReservas() {
    const tbody = $('#reservasTableBody');
    tbody.empty();
    
    // Por ahora mostramos un mensaje de que no hay datos
    tbody.append('<tr><td colspan="5" class="text-center text-muted">Las reservas estarán disponibles próximamente</td></tr>');
}

function renderizarPedidos() {
    const tbody = $('#pedidosTableBody');
    tbody.empty();
    
    // Por ahora mostramos un mensaje de que no hay datos
    tbody.append('<tr><td colspan="5" class="text-center text-muted">Los pedidos estarán disponibles próximamente</td></tr>');
}

function renderizarTareas() {
    const tbody = $('#tareasTableBody');
    tbody.empty();
    
    // Por ahora mostramos un mensaje de que no hay datos
    tbody.append('<tr><td colspan="5" class="text-center text-muted">Las tareas estarán disponibles próximamente</td></tr>');
}

function aplicarFiltros() {
    cargarResumen();
    cargarActividades();
}

function limpiarFiltros() {
    const fechaActual = new Date();
    const primerDia = new Date(fechaActual.getFullYear(), fechaActual.getMonth(), 1);
    const ultimoDia = new Date(fechaActual.getFullYear(), fechaActual.getMonth() + 1, 0);
    
    $('#filtroFechaInicio').val(primerDia.toISOString().split('T')[0]);
    $('#filtroFechaFin').val(ultimoDia.toISOString().split('T')[0]);
    $('#filtroTipo').val('');
    
    cargarResumen();
    cargarActividades();
}

function exportarExcel() {
    const fechaInicio = $('#filtroFechaInicio').val();
    const fechaFin = $('#filtroFechaFin').val();
    
    window.open(`api/endpoints/actividades.php?accion=exportar_excel&usuario_id=${usuarioId}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`);
}

function exportarPDF() {
    const fechaInicio = $('#filtroFechaInicio').val();
    const fechaFin = $('#filtroFechaFin').val();
    
    window.open(`api/endpoints/actividades.php?accion=exportar_pdf&usuario_id=${usuarioId}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`);
}

function showNotification(message, type) {
    const notification = $(`
        <div class="alert alert-${type} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('body').append(notification);
    
    setTimeout(function() {
        notification.fadeOut(300, function() {
            $(this).remove();
        });
    }, 3000);
}
</script>

<?php include 'includes/footer.php'; ?>
