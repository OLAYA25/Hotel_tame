<?php
require_once __DIR__ . '/../../../backend/config/database.php';

// Verificar sesión de usuario
if (!isset($_SESSION['usuario'])) {
    header('Location: /Hotel_tame/login');
    exit;
}

// Solo admin puede gestionar turnos
if ($_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: /Hotel_tame/dashboard');
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
                <h1>Gestión de Turnos</h1>
                <p class="text-muted mb-0">Administra los turnos de los empleados</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTurno" onclick="abrirModalNuevo()">
                <i class="fas fa-plus me-2"></i>Nuevo Turno
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="filtroFechaInicio">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="filtroFechaFin">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Empleado</label>
                    <select class="form-select" id="filtroEmpleado">
                        <option value="">Todos los empleados</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="filtroEstado">
                        <option value="">Todos los estados</option>
                        <option value="programado">Programado</option>
                        <option value="en_curso">En Curso</option>
                        <option value="completado">Completado</option>
                        <option value="ausente">Ausente</option>
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

    <!-- Estadísticas -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Turnos</h6>
                            <h3 id="totalTurnos">0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-alt fa-2x"></i>
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
                            <h6 class="card-title">Completados</h6>
                            <h3 id="turnosCompletados">0</h3>
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
                            <h6 class="card-title">Programados</h6>
                            <h3 id="turnosProgramados">0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Ausencias</h6>
                            <h3 id="ausencias">0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-times fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Turnos -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Lista de Turnos</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="turnosTable">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Tipo Turno</th>
                            <th>Fecha</th>
                            <th>Horario</th>
                            <th>Estado</th>
                            <th>Notas</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="turnosTableBody">
                        <!-- Los turnos se cargarán dinámicamente aquí -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Turno -->
<div class="modal fade" id="modalTurno" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Turno</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTurno" onsubmit="guardarTurno(event)">
                <div class="modal-body">
                    <input type="hidden" id="turno_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Empleado *</label>
                        <select class="form-select" id="usuario_id" required>
                            <option value="">Seleccione...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipo de Turno *</label>
                        <select class="form-select" id="tipo_turno_id" required>
                            <option value="">Seleccione...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fecha *</label>
                        <input type="date" class="form-control" id="fecha" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hora Entrada *</label>
                            <input type="time" class="form-control" id="hora_inicio" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hora Salida *</label>
                            <input type="time" class="form-control" id="hora_fin" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estado *</label>
                        <select class="form-select" id="estado" required>
                            <option value="programado">Programado</option>
                            <option value="en_curso">En Curso</option>
                            <option value="completado">Completado</option>
                            <option value="ausente">Ausente</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notas</label>
                        <textarea class="form-control" id="notas" rows="3"></textarea>
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

<script>
let turnos = [];
let empleados = [];
let tiposTurno = [];

$(document).ready(function() {
    cargarEmpleados();
    cargarTiposTurno();
    cargarTurnos();
    cargarEstadisticas();
});

function cargarEmpleados() {
    $.get('api/endpoints/usuarios.php', function(data) {
        empleados = Array.isArray(data) ? data : (data.records || []);
        
        const select = $('#usuario_id');
        const filtroSelect = $('#filtroEmpleado');
        
        empleados.forEach(empleado => {
            const option = `<option value="${empleado.id}">${empleado.nombre} ${empleado.apellido}</option>`;
            select.append(option);
            filtroSelect.append(option);
        });
    });
}

function cargarTiposTurno() {
    $.get('api/endpoints/turnos.php?accion=tipos_turno', function(data) {
        tiposTurno = Array.isArray(data) ? data : [];
        
        const select = $('#tipo_turno_id');
        tiposTurno.forEach(tipo => {
            select.append(`<option value="${tipo.id}">${tipo.nombre} (${tipo.hora_inicio} - ${tipo.hora_fin})</option>`);
        });
    });
}

function cargarTurnos() {
    const fechaInicio = $('#filtroFechaInicio').val();
    const fechaFin = $('#filtroFechaFin').val();
    const empleadoId = $('#filtroEmpleado').val();
    const estado = $('#filtroEstado').val();
    
    let url = 'api/endpoints/turnos.php?';
    const params = [];
    
    if (fechaInicio) params.push(`fecha_inicio=${fechaInicio}`);
    if (fechaFin) params.push(`fecha_fin=${fechaFin}`);
    if (empleadoId) params.push(`usuario_id=${empleadoId}`);
    if (estado) params.push(`estado=${estado}`);
    
    url += params.join('&');
    
    $.get(url, function(data) {
        turnos = Array.isArray(data) ? data : [];
        renderizarTurnos();
    });
}

function renderizarTurnos() {
    const tbody = $('#turnosTableBody');
    tbody.empty();
    
    turnos.forEach(turno => {
        const estadoBadge = {
            'programado': 'warning',
            'en_curso': 'info',
            'completado': 'success',
            'ausente': 'danger'
        }[turno.estado] || 'secondary';
        
        const row = `
            <tr>
                <td>${turno.usuario_nombre} ${turno.usuario_apellido}</td>
                <td>
                    <span class="badge" style="background-color: ${turno.color || '#6c757d'}">
                        ${turno.tipo_turno_nombre}
                    </span>
                </td>
                <td>${turno.fecha}</td>
                <td>${turno.hora_inicio} - ${turno.hora_fin}</td>
                <td><span class="badge bg-${estadoBadge}">${turno.estado}</span></td>
                <td>${turno.notas || '-'}</td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-outline-primary btn-sm" onclick="editarTurno(${turno.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="eliminarTurno(${turno.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function cargarEstadisticas() {
    const fechaInicio = $('#filtroFechaInicio').val() || new Date().toISOString().split('T')[0];
    const fechaFin = $('#filtroFechaFin').val() || new Date().toISOString().split('T')[0];
    
    $.get(`api/endpoints/turnos.php?accion=estadisticas&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`, function(data) {
        $('#totalTurnos').text(data.total_turnos || 0);
        $('#turnosCompletados').text(data.turnos_completados || 0);
        $('#turnosProgramados').text(data.turnos_programados || 0);
        $('#ausencias').text(data.ausencias || 0);
    });
}

function abrirModalNuevo() {
    $('#modalTitle').text('Nuevo Turno');
    $('#formTurno')[0].reset();
    $('#turno_id').val('');
    $('#fecha').val(new Date().toISOString().split('T')[0]);
}

function editarTurno(id) {
    const turno = turnos.find(t => t.id === id);
    if (!turno) return;
    
    $('#modalTitle').text('Editar Turno');
    $('#turno_id').val(turno.id);
    $('#usuario_id').val(turno.usuario_id);
    $('#tipo_turno_id').val(turno.tipo_turno_id);
    $('#fecha').val(turno.fecha);
    $('#hora_inicio').val(turno.hora_inicio);
    $('#hora_fin').val(turno.hora_fin);
    $('#estado').val(turno.estado);
    $('#notas').val(turno.notas || '');
    
    $('#modalTurno').modal('show');
}

function guardarTurno(event) {
    event.preventDefault();
    
    const id = $('#turno_id').val();
    const data = {
        usuario_id: $('#usuario_id').val(),
        tipo_turno_id: $('#tipo_turno_id').val(),
        fecha: $('#fecha').val(),
        hora_inicio: $('#hora_inicio').val(),
        hora_fin: $('#hora_fin').val(),
        estado: $('#estado').val(),
        notas: $('#notas').val()
    };
    
    if (id) {
        data.id = id;
        $.ajax({
            url: 'api/endpoints/turnos.php',
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function() {
                showNotification('Turno actualizado exitosamente', 'success');
                $('#modalTurno').modal('hide');
                cargarTurnos();
                cargarEstadisticas();
            },
            error: function() {
                showNotification('Error al actualizar turno', 'error');
            }
        });
    } else {
        $.ajax({
            url: 'api/endpoints/turnos.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function() {
                showNotification('Turno creado exitosamente', 'success');
                $('#modalTurno').modal('hide');
                cargarTurnos();
                cargarEstadisticas();
            },
            error: function() {
                showNotification('Error al crear turno', 'error');
            }
        });
    }
}

function eliminarTurno(id) {
    if (!confirm('¿Está seguro de eliminar este turno?')) return;
    
    $.ajax({
        url: `api/endpoints/turnos.php?id=${id}`,
        method: 'DELETE',
        success: function() {
            showNotification('Turno eliminado exitosamente', 'success');
            cargarTurnos();
            cargarEstadisticas();
        },
        error: function() {
            showNotification('Error al eliminar turno', 'error');
        }
    });
}

function aplicarFiltros() {
    cargarTurnos();
    cargarEstadisticas();
}

function limpiarFiltros() {
    $('#filtroFechaInicio').val('');
    $('#filtroFechaFin').val('');
    $('#filtroEmpleado').val('');
    $('#filtroEstado').val('');
    cargarTurnos();
    cargarEstadisticas();
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

<?php include __DIR__ . '/../../../backend/includes/footer.php'; ?>
