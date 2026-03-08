<?php
require_once __DIR__ . '/../../../backend/config/database.php';

// Verificar sesión de usuario
if (!isset($_SESSION['usuario'])) {
    header('Location: /Hotel_tame/login');
    exit;
}

// Incluir middleware de autenticación y permisos
require_once __DIR__ . '/../../../backend/includes/auth_middleware.php';

include __DIR__ . '/../../../backend/includes/header.php';
include __DIR__ . '/../../../backend/includes/sidebar.php';
?>

<div class="main-content">
    <div id="notification-container"></div>
    
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Módulo Contable</h1>
                <p class="text-muted mb-0">Gestión financiera y contable del hotel</p>
            </div>
            <div class="btn-group">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTransaccion" onclick="abrirModalNuevaTransaccion()">
                    <i class="fas fa-plus me-2"></i>Nueva Transacción
                </button>
                <button class="btn btn-outline-info" onclick="generarReporte()">
                    <i class="fas fa-file-excel me-2"></i>Exportar Reporte
                </button>
                <button class="btn btn-outline-success" onclick="window.location.href='/Hotel_tame/informe-huespedes'">
                    <i class="fas fa-users me-2"></i>Informe Huéspedes
                </button>
            </div>
        </div>
    </div>

    <!-- Resumen Financiero -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Ingresos del Mes</h6>
                            <h3 id="totalIngresos">$0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-arrow-up fa-2x"></i>
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
                            <h6 class="card-title">Egresos del Mes</h6>
                            <h3 id="totalEgresos">$0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-arrow-down fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Balance Neto</h6>
                            <h3 id="balanceNeto">$0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-balance-scale fa-2x"></i>
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
                            <h6 class="card-title">Transacciones</h6>
                            <h3 id="totalTransacciones">0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exchange-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informe de Huéspedes Resumido -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Resumen de Huéspedes del Mes</h5>
                <button class="btn btn-sm btn-outline-primary" onclick="cargarResumenHuespedes()">
                    <i class="fas fa-sync me-1"></i>Actualizar
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3" id="resumenHuespedes">
                <div class="col-md-2">
                    <div class="text-center">
                        <h4 class="text-primary" id="resumenTotalReservas">0</h4>
                        <small class="text-muted">Reservas</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center">
                        <h4 class="text-success" id="resumenTotalIngresos">$0</h4>
                        <small class="text-muted">Ingresos</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center">
                        <h4 class="text-info" id="resumenTotalPax">0</h4>
                        <small class="text-muted">Total Pax</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center">
                        <h4 class="text-warning" id="resumenAdultos">0</h4>
                        <small class="text-muted">Adultos</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center">
                        <h4 class="text-danger" id="resumenNinos">0</h4>
                        <small class="text-muted">Niños</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center">
                        <h4 class="text-secondary" id="resumenDias">0</h4>
                        <small class="text-muted">Días HPDJ</small>
                    </div>
                </div>
            </div>
            
            <!-- Gráfico de Motivos de Viaje -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <h6>Distribución por Motivo de Viaje</h6>
                    <canvas id="graficoMotivos" width="400" height="200"></canvas>
                </div>
                <div class="col-md-6">
                    <h6>Distribución por Nacionalidad</h6>
                    <canvas id="graficoNacionalidades" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="fechaInicio" value="<?php echo date('Y-m-01'); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="fechaFin" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tipo</label>
                    <select class="form-select" id="filtroTipo">
                        <option value="">Todos</option>
                        <option value="ingreso">Ingresos</option>
                        <option value="egreso">Egresos</option>
                        <option value="traspaso">Traspasos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="filtroEstado">
                        <option value="">Todos</option>
                        <option value="confirmada">Confirmadas</option>
                        <option value="borrador">Borrador</option>
                        <option value="anulada">Anuladas</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-primary w-100" onclick="cargarTransacciones()">
                        <i class="fas fa-search me-2"></i>Filtrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Transacciones -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Transacciones Contables</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="transaccionesTable">
                    <thead>
                        <tr>
                            <th>Comprobante</th>
                            <th>Fecha</th>
                            <th>Descripción</th>
                            <th>Tipo</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="transaccionesList">
                        <!-- Las transacciones se cargarán aquí -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nueva Transacción -->
<div class="modal fade" id="modalTransaccion" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nueva Transacción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTransaccion" onsubmit="guardarTransaccion(event)">
                <div class="modal-body">
                    <input type="hidden" id="transaccion_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha *</label>
                            <input type="date" class="form-control" id="fecha" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Transacción *</label>
                            <select class="form-select" id="tipo_transaccion" required>
                                <option value="">Seleccione...</option>
                                <option value="ingreso">Ingreso</option>
                                <option value="egreso">Egreso</option>
                                <option value="traspaso">Traspaso</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción *</label>
                        <textarea class="form-control" id="descripcion" rows="2" required placeholder="Descripción detallada de la transacción"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Referencia (Opcional)</label>
                            <select class="form-select" id="referencia_tipo">
                                <option value="">Sin referencia</option>
                                <option value="reserva">Reserva</option>
                                <option value="pedido">Pedido</option>
                                <option value="evento">Evento</option>
                                <option value="gasto">Gasto</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ID Referencia</label>
                            <input type="number" class="form-control" id="referencia_id" placeholder="ID del documento de referencia">
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-3">Detalles de la Transacción (Partida Doble)</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm" id="detallesTable">
                            <thead>
                                <tr>
                                    <th>Cuenta</th>
                                    <th>Tipo</th>
                                    <th>Monto</th>
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="detallesList">
                                <!-- Los detalles se agregarán aquí -->
                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <strong>Total Debe:</strong> $<span id="totalDebe">0.00</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-warning">
                                <strong>Total Haber:</strong> $<span id="totalHaber">0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="button" class="btn btn-outline-primary" onclick="agregarDetalle()">
                            <i class="fas fa-plus me-2"></i>Agregar Detalle
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Transacción</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detalles Transacción -->
<div class="modal fade" id="modalDetalles" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de Transacción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detallesTransaccionContent">
                    <!-- Los detalles se cargarán aquí -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
let transacciones = [];
let cuentas = [];
let detallesTransaccion = [];

$(document).ready(function() {
    cargarResumenFinanciero();
    cargarTransacciones();
    cargarCuentas();
    cargarResumenHuespedes(); // Cargar resumen de huéspedes
    // Establecer fecha actual por defecto
    $('#fecha').val(new Date().toISOString().split('T')[0]);
});

function cargarResumenFinanciero() {
    const fechaInicio = $('#fechaInicio').val();
    const fechaFin = $('#fechaFin').val();
    
    $.get(`api/endpoints/contabilidad.php?accion=resumen_financiero&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`, function(data) {
        $('#totalIngresos').text('$' + parseFloat(data.total_ingresos || 0).toLocaleString('es-CO'));
        $('#totalEgresos').text('$' + parseFloat(data.total_egresos || 0).toLocaleString('es-CO'));
        
        const balanceNeto = (parseFloat(data.total_ingresos || 0) - parseFloat(data.total_egresos || 0));
        $('#balanceNeto').text('$' + balanceNeto.toLocaleString('es-CO'));
        $('#totalTransacciones').text(data.transacciones_confirmadas || 0);
    });
}

function cargarTransacciones() {
    const fechaInicio = $('#fechaInicio').val();
    const fechaFin = $('#fechaFin').val();
    const tipo = $('#filtroTipo').val();
    const estado = $('#filtroEstado').val();
    
    let url = 'api/endpoints/contabilidad.php?';
    const params = [];
    
    if (fechaInicio) params.push(`fecha_inicio=${fechaInicio}`);
    if (fechaFin) params.push(`fecha_fin=${fechaFin}`);
    if (tipo) params.push(`tipo=${tipo}`);
    if (estado) params.push(`estado=${estado}`);
    
    url += params.join('&');
    
    $.get(url, function(data) {
        const tbody = $('#transaccionesList');
        tbody.empty();
        
        transacciones = Array.isArray(data) ? data : [];
        
        transacciones.forEach(transaccion => {
            const tipoBadge = transaccion.tipo_transaccion === 'ingreso' ? 'success' : 
                             transaccion.tipo_transaccion === 'egreso' ? 'danger' : 'info';
            const estadoBadge = transaccion.estado === 'confirmada' ? 'success' : 
                              transaccion.estado === 'borrador' ? 'warning' : 'secondary';
            
            tbody.append(`
                <tr>
                    <td><span class="badge bg-secondary">${transaccion.numero_comprobante}</span></td>
                    <td>${new Date(transaccion.fecha).toLocaleDateString('es-CO')}</td>
                    <td>${transaccion.descripcion}</td>
                    <td><span class="badge bg-${tipoBadge}">${transaccion.tipo_transaccion}</span></td>
                    <td><strong>$${parseFloat(transaccion.monto_total).toLocaleString('es-CO')}</strong></td>
                    <td><span class="badge bg-${estadoBadge}">${transaccion.estado}</span></td>
                    <td>${transaccion.usuario_nombre} ${transaccion.usuario_apellido}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-info" onclick="verDetalles(${transaccion.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                            ${transaccion.estado === 'borrador' ? `
                            <button type="button" class="btn btn-outline-success" onclick="confirmarTransaccion(${transaccion.id})">
                                <i class="fas fa-check"></i>
                            </button>` : ''}
                        </div>
                    </td>
                </tr>
            `);
        });
        
        if (transacciones.length === 0) {
            tbody.append('<tr><td colspan="8" class="text-center text-muted">No hay transacciones registradas</td></tr>');
        }
    });
}

function cargarCuentas() {
    $.get('api/endpoints/cuentas_contables.php', function(data) {
        cuentas = Array.isArray(data) ? data : [];
    });
}

function abrirModalNuevaTransaccion() {
    $('#modalTitle').text('Nueva Transacción');
    $('#formTransaccion')[0].reset();
    $('#transaccion_id').val('');
    detallesTransaccion = [];
    actualizarDetallesTable();
    $('#fecha').val(new Date().toISOString().split('T')[0]);
    $('#modalTransaccion').modal('show');
}

function agregarDetalle() {
    const detalle = {
        cuenta_id: '',
        tipo_movimiento: 'debe',
        monto: 0,
        descripcion: ''
    };
    
    detallesTransaccion.push(detalle);
    actualizarDetallesTable();
}

function actualizarDetallesTable() {
    const tbody = $('#detallesList');
    tbody.empty();
    
    let totalDebe = 0;
    let totalHaber = 0;
    
    detallesTransaccion.forEach((detalle, index) => {
        const cuentasOptions = cuentas.map(cuenta => 
            `<option value="${cuenta.id}" ${detalle.cuenta_id == cuenta.id ? 'selected' : ''}>
                ${cuenta.codigo} - ${cuenta.nombre}
            </option>`
        ).join('');
        
        tbody.append(`
            <tr>
                <td>
                    <select class="form-select form-select-sm" onchange="actualizarDetalle(${index}, 'cuenta_id', this.value)">
                        <option value="">Seleccione cuenta...</option>
                        ${cuentasOptions}
                    </select>
                </td>
                <td>
                    <select class="form-select form-select-sm" onchange="actualizarDetalle(${index}, 'tipo_movimiento', this.value)">
                        <option value="debe" ${detalle.tipo_movimiento === 'debe' ? 'selected' : ''}>Debe</option>
                        <option value="haber" ${detalle.tipo_movimiento === 'haber' ? 'selected' : ''}>Haber</option>
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" step="0.01" min="0" 
                           value="${detalle.monto}" onchange="actualizarDetalle(${index}, 'monto', this.value)">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" value="${detalle.descripcion}" 
                           onchange="actualizarDetalle(${index}, 'descripcion', this.value)">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarDetalle(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `);
        
        if (detalle.tipo_movimiento === 'debe') {
            totalDebe += parseFloat(detalle.monto || 0);
        } else {
            totalHaber += parseFloat(detalle.monto || 0);
        }
    });
    
    $('#totalDebe').text(totalDebe.toFixed(2));
    $('#totalHaber').text(totalHaber.toFixed(2));
}

function actualizarDetalle(index, campo, valor) {
    detallesTransaccion[index][campo] = valor;
    actualizarDetallesTable();
}

function eliminarDetalle(index) {
    detallesTransaccion.splice(index, 1);
    actualizarDetallesTable();
}

function guardarTransaccion(e) {
    e.preventDefault();
    
    // Validar que haya al menos 2 detalles
    if (detallesTransaccion.length < 2) {
        showNotification('Se requieren al menos 2 detalles para la partida doble', 'error');
        return;
    }
    
    // Validar suma de debe = suma de haber
    let totalDebe = 0;
    let totalHaber = 0;
    
    detallesTransaccion.forEach(detalle => {
        if (detalle.tipo_movimiento === 'debe') {
            totalDebe += parseFloat(detalle.monto || 0);
        } else {
            totalHaber += parseFloat(detalle.monto || 0);
        }
    });
    
    if (Math.abs(totalDebe - totalHaber) > 0.01) {
        showNotification('La suma del debe debe ser igual a la suma del haber', 'error');
        return;
    }
    
    const data = {
        fecha: $('#fecha').val(),
        descripcion: $('#descripcion').val(),
        tipo_transaccion: $('#tipo_transaccion').val(),
        referencia_tipo: $('#referencia_tipo').val() || null,
        referencia_id: $('#referencia_id').val() || null,
        detalles: detallesTransaccion
    };
    
    $.ajax({
        url: 'api/endpoints/contabilidad.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            $('#modalTransaccion').modal('hide');
            showNotification(response.message || 'Transacción creada exitosamente', 'success');
            cargarTransacciones();
            cargarResumenFinanciero();
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Error al guardar transacción';
            showNotification(errorMsg, 'error');
        }
    });
}

function verDetalles(id) {
    $.get(`api/endpoints/contabilidad.php?id=${id}`, function(data) {
        let detallesHtml = '<h6>Detalles de la Transacción</h6><table class="table table-sm">';
        detallesHtml += '<thead><tr><th>Cuenta</th><th>Tipo</th><th>Monto</th><th>Descripción</th></tr></thead><tbody>';
        
        data.forEach(detalle => {
            const tipoBadge = detalle.tipo_movimiento === 'debe' ? 'success' : 'warning';
            detallesHtml += `
                <tr>
                    <td>${detalle.codigo} - ${detalle.cuenta_nombre}</td>
                    <td><span class="badge bg-${tipoBadge}">${detalle.tipo_movimiento}</span></td>
                    <td><strong>$${parseFloat(detalle.monto).toLocaleString('es-CO')}</strong></td>
                    <td>${detalle.descripcion || '-'}</td>
                </tr>
            `;
        });
        
        detallesHtml += '</tbody></table>';
        $('#detallesTransaccionContent').html(detallesHtml);
        $('#modalDetalles').modal('show');
    });
}

function confirmarTransaccion(id) {
    if (!confirm('¿Está seguro de confirmar esta transacción? Esta acción no se puede deshacer.')) {
        return;
    }
    
    $.ajax({
        url: 'api/endpoints/contabilidad.php',
        type: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify({id: id, accion: 'confirmar'}),
        success: function(response) {
            showNotification(response.message || 'Transacción confirmada exitosamente', 'success');
            cargarTransacciones();
            cargarResumenFinanciero();
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Error al confirmar transacción';
            showNotification(errorMsg, 'error');
        }
    });
}

function generarReporte() {
    const fechaInicio = $('#fechaInicio').val();
    const fechaFin = $('#fechaFin').val();
    
    window.open(`api/endpoints/contabilidad.php?accion=exportar_excel&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`, '_blank');
}

// Funciones para el resumen de huéspedes
function cargarResumenHuespedes() {
    const mes = new Date().getMonth() + 1;
    const anio = new Date().getFullYear();
    
    $.get(`api/endpoints/informe_huespedes_csv.php?accion=generar_informe&mes=${mes}&anio=${anio}`, function(data) {
        console.log('Resumen de huéspedes:', data);
        
        // Actualizar contadores
        $('#resumenTotalReservas').text(data.totales.total_reservas);
        $('#resumenTotalIngresos').text('$' + data.totales.total_valor);
        $('#resumenTotalPax').text(data.totales.total_pax);
        $('#resumenAdultos').text(data.totales.total_adultos);
        $('#resumenNinos').text(data.totales.total_ninos);
        $('#resumenDias').text(data.totales.total_dias);
        
        // Generar gráficos
        generarGraficos(data.informe);
    }).fail(function(xhr, status, error) {
        console.error('Error cargando resumen de huéspedes:', xhr.responseText);
    });
}

function generarGraficos(datos) {
    // Gráfico de motivos de viaje
    const motivos = {};
    datos.forEach(item => {
        const motivo = item['MOTIVO DE VIAJE'];
        motivos[motivo] = (motivos[motivo] || 0) + 1;
    });
    
    const canvasMotivos = document.getElementById('graficoMotivos');
    if (canvasMotivos) {
        const ctx = canvasMotivos.getContext('2d');
        
        // Limpiar canvas
        ctx.clearRect(0, 0, canvasMotivos.width, canvasMotivos.height);
        
        // Dibujar gráfico de barras simple
        const motivosArray = Object.entries(motivos);
        const maxCount = Math.max(...motivosArray.map(([_, count]) => count));
        const barWidth = canvasMotivos.width / motivosArray.length;
        const barHeight = canvasMotivos.height - 40;
        
        motivosArray.forEach(([motivo, count], index) => {
            const barHeightPercent = (count / maxCount) * barHeight;
            const x = index * barWidth + 10;
            const y = canvasMotivos.height - barHeightPercent - 20;
            
            // Dibujar barra
            ctx.fillStyle = `hsl(${index * 360 / motivosArray.length}, 70%, 50%)`;
            ctx.fillRect(x, y, barWidth - 20, barHeightPercent);
            
            // Dibujar texto
            ctx.fillStyle = '#333';
            ctx.font = '10px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(motivo.substring(0, 8), x + (barWidth - 20) / 2, canvasMotivos.height - 5);
            ctx.fillText(count, x + (barWidth - 20) / 2, y - 5);
        });
    }
    
    // Gráfico de nacionalidades (top 5)
    const nacionalidades = {};
    datos.forEach(item => {
        const nacionalidad = item.NACIONALIDAD;
        if (nacionalidad && nacionalidad !== 'No especificada') {
            nacionalidades[nacionalidad] = (nacionalidades[nacionalidad] || 0) + 1;
        }
    });
    
    const topNacionalidades = Object.entries(nacionalidades)
        .sort(([,a], [,b]) => b - a)
        .slice(0, 5);
    
    const canvasNacionalidades = document.getElementById('graficoNacionalidades');
    if (canvasNacionalidades) {
        const ctx = canvasNacionalidades.getContext('2d');
        
        // Limpiar canvas
        ctx.clearRect(0, 0, canvasNacionalidades.width, canvasNacionalidades.height);
        
        // Dibujar gráfico de pastel simple
        const total = topNacionalidades.reduce((sum, [, count]) => sum + count, 0);
        let currentAngle = 0;
        const centerX = canvasNacionalidades.width / 2;
        const centerY = canvasNacionalidades.height / 2;
        const radius = Math.min(centerX, centerY) - 20;
        
        topNacionalidades.forEach(([nacionalidad, count], index) => {
            const sliceAngle = (count / total) * 2 * Math.PI;
            
            // Dibujar porción
            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            ctx.arc(centerX, centerY, radius, currentAngle, currentAngle + sliceAngle);
            ctx.closePath();
            ctx.fillStyle = `hsl(${index * 360 / topNacionalidades.length}, 70%, 50%)`;
            ctx.fill();
            
            // Dibujar etiqueta
            const labelAngle = currentAngle + sliceAngle / 2;
            const labelX = centerX + Math.cos(labelAngle) * (radius * 0.7);
            const labelY = centerY + Math.sin(labelAngle) * (radius * 0.7);
            
            ctx.fillStyle = '#fff';
            ctx.font = 'bold 10px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(nacionalidad.substring(0, 3), labelX, labelY);
            
            currentAngle += sliceAngle;
        });
    }
}

function showNotification(message, type) {
    const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
    const notification = $(`
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 1050;">
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
