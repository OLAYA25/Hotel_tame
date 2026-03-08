<?php
// Habilitar visualización de errores para diagnóstico
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

// Verificar sesión de usuario
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Incluir middleware de autenticación y permisos
require_once 'includes/auth_middleware.php';

include 'includes/header.php';
include 'includes/sidebar.php';
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
                        <tr>
                            <td colspan="8" class="text-center text-muted">Cargando transacciones...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
let transacciones = [];
let cuentas = [];

$(document).ready(function() {
    console.log('Página contabilidad cargada');
    cargarResumenFinanciero();
    cargarTransacciones();
    cargarCuentas();
    
    // Establecer fecha actual por defecto
    $('#fecha').val(new Date().toISOString().split('T')[0]);
});

function cargarResumenFinanciero() {
    console.log('Cargando resumen financiero...');
    const fechaInicio = $('#fechaInicio').val();
    const fechaFin = $('#fechaFin').val();
    
    $.get(`api/endpoints/contabilidad.php?accion=resumen_financiero&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`)
        .done(function(data) {
            console.log('Resumen financiero:', data);
            $('#totalIngresos').text('$' + parseFloat(data.total_ingresos || 0).toLocaleString('es-CO'));
            $('#totalEgresos').text('$' + parseFloat(data.total_egresos || 0).toLocaleString('es-CO'));
            
            const balanceNeto = (parseFloat(data.total_ingresos || 0) - parseFloat(data.total_egresos || 0));
            $('#balanceNeto').text('$' + balanceNeto.toLocaleString('es-CO'));
            $('#totalTransacciones').text(data.transacciones_confirmadas || 0);
        })
        .fail(function(xhr) {
            console.error('Error cargando resumen financiero:', xhr.responseText);
            showNotification('Error cargando resumen financiero', 'error');
        });
}

function cargarTransacciones() {
    console.log('Cargando transacciones...');
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
    
    $.get(url)
        .done(function(data) {
            console.log('Transacciones cargadas:', data);
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
                        <td><span class="badge bg-secondary">${transaccion.numero_comprobante || 'N/A'}</span></td>
                        <td>${transaccion.fecha ? new Date(transaccion.fecha).toLocaleDateString('es-CO') : 'N/A'}</td>
                        <td>${transaccion.descripcion || 'Sin descripción'}</td>
                        <td><span class="badge bg-${tipoBadge}">${transaccion.tipo_transaccion || 'N/A'}</span></td>
                        <td><strong>$${parseFloat(transaccion.monto_total || 0).toLocaleString('es-CO')}</strong></td>
                        <td><span class="badge bg-${estadoBadge}">${transaccion.estado || 'N/A'}</span></td>
                        <td>${transaccion.usuario_nombre || 'N/A'} ${transaccion.usuario_apellido || ''}</td>
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
        })
        .fail(function(xhr) {
            console.error('Error cargando transacciones:', xhr.responseText);
            $('#transaccionesList').html('<tr><td colspan="8" class="text-center text-danger">Error cargando transacciones</td></tr>');
            showNotification('Error cargando transacciones', 'error');
        });
}

function cargarCuentas() {
    console.log('Cargando cuentas...');
    $.get('api/endpoints/cuentas_contables.php')
        .done(function(data) {
            console.log('Cuentas cargadas:', data);
            cuentas = Array.isArray(data) ? data : [];
        })
        .fail(function(xhr) {
            console.error('Error cargando cuentas:', xhr.responseText);
        });
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

function verDetalles(id) {
    console.log('Ver detalles de transacción:', id);
    showNotification('Función de detalles en desarrollo', 'info');
}

function confirmarTransaccion(id) {
    console.log('Confirmar transacción:', id);
    showNotification('Función de confirmación en desarrollo', 'info');
}

function generarReporte() {
    console.log('Generar reporte');
    showNotification('Función de reportes en desarrollo', 'info');
}
</script>

<?php include 'includes/footer.php'; ?>
