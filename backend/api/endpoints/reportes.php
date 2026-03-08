<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/database.php';

// Verificar sesión de usuario
// // session_start(); // Ya iniciada en router; // Ya iniciada en router
if (!isset($_SESSION['usuario'])) {
    http_response_code(401); echo json_encode(['error' => 'No autorizado']); exit;
}

// Incluir middleware de autenticación y permisos
require_once 'includes/auth_middleware.php';



?>

<div class="main-content">
    <div id="notification-container"></div>
    
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Reportes Financieros</h1>
                <p class="text-muted mb-0">Análisis y reportes contables del hotel</p>
            </div>
            <div class="btn-group">
                <button class="btn btn-success" onclick="exportarExcel()">
                    <i class="fas fa-file-excel me-2"></i>Exportar Excel
                </button>
                <button class="btn btn-danger" onclick="exportarPDF()">
                    <i class="fas fa-file-pdf me-2"></i>Exportar PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Filtros de Reporte -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Filtros del Reporte</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tipo de Reporte</label>
                    <select class="form-select" id="tipoReporte" onchange="cambiarTipoReporte()">
                        <option value="resumen">Resumen Financiero</option>
                        <option value="balance">Balance de Comprobación</option>
                        <option value="ingresos_egresos">Estado de Resultados</option>
                        <option value="cuentas">Movimientos por Cuenta</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="fechaInicio" value="<?php echo date('Y-m-01'); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="fechaFin" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Cuenta (Opcional)</label>
                    <select class="form-select" id="cuentaFiltro">
                        <option value="">Todas las cuentas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="btn-group w-100">
                        <button class="btn btn-primary" onclick="generarReporte()">
                            <i class="fas fa-chart-line me-2"></i>Generar
                        </button>
                        <button class="btn btn-outline-secondary" onclick="limpiarFiltros()">
                            <i class="fas fa-eraser me-2"></i>Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido del Reporte -->
    <div id="reporteContent">
        <!-- Resumen Financiero por defecto -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-arrow-up fa-3x mb-2"></i>
                        <h6 class="card-title">Total Ingresos</h6>
                        <h3 id="reporteTotalIngresos">$0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-arrow-down fa-3x mb-2"></i>
                        <h6 class="card-title">Total Egresos</h6>
                        <h3 id="reporteTotalEgresos">$0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-balance-scale fa-3x mb-2"></i>
                        <h6 class="card-title">Utilidad Neta</h6>
                        <h3 id="reporteUtilidad">$0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-exchange-alt fa-3x mb-2"></i>
                        <h6 class="card-title">Transacciones</h6>
                        <h3 id="reporteTransacciones">0</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row g-4 mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Evolución Mensual</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="graficoEvolucion" height="100"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Distribución por Tipo</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="graficoDistribucion" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Detalles -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Detalle de Transacciones</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="tablaReporte">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Comprobante</th>
                                <th>Descripción</th>
                                <th>Tipo</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody id="tablaReporteBody">
                            <!-- Los datos se cargarán aquí -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let cuentas = [];
let chartEvolucion = null;
let chartDistribucion = null;

$(document).ready(function() {
    cargarCuentas();
    
    // Esperar a que el DOM esté completamente cargado antes de inicializar gráficos
    setTimeout(function() {
        inicializarGraficos();
        generarReporte();
    }, 500);
});

function cargarCuentas() {
    $.get('api/endpoints/cuentas_contables.php', function(data) {
        cuentas = Array.isArray(data) ? data : [];
        
        const select = $('#cuentaFiltro');
        cuentas.forEach(cuenta => {
            select.append(`<option value="${cuenta.id}">${cuenta.codigo} - ${cuenta.nombre}</option>`);
        });
    });
}

function cambiarTipoReporte() {
    const tipo = $('#tipoReporte').val();
    
    // Mostrar/ocultar filtro de cuenta según el tipo
    if (tipo === 'cuentas') {
        $('#cuentaFiltro').closest('.col-md-2').show();
    } else {
        $('#cuentaFiltro').closest('.col-md-2').hide();
    }
}

function generarReporte() {
    const tipo = $('#tipoReporte').val();
    const fechaInicio = $('#fechaInicio').val();
    const fechaFin = $('#fechaFin').val();
    const cuentaId = $('#cuentaFiltro').val();
    
    switch(tipo) {
        case 'resumen':
            generarResumenFinanciero(fechaInicio, fechaFin);
            break;
        case 'balance':
            generarBalanceComprobacion(fechaInicio, fechaFin);
            break;
        case 'ingresos_egresos':
            generarEstadoResultados(fechaInicio, fechaFin);
            break;
        case 'cuentas':
            generarMovimientosCuenta(fechaInicio, fechaFin, cuentaId);
            break;
    }
}

function generarResumenFinanciero(fechaInicio, fechaFin) {
    // Obtener resumen financiero
    $.get(`api/endpoints/contabilidad.php?accion=resumen_financiero&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`, function(data) {
        $('#reporteTotalIngresos').text('$' + parseFloat(data.total_ingresos || 0).toLocaleString('es-CO'));
        $('#reporteTotalEgresos').text('$' + parseFloat(data.total_egresos || 0).toLocaleString('es-CO'));
        
        const utilidad = (parseFloat(data.total_ingresos || 0) - parseFloat(data.total_egresos || 0));
        $('#reporteUtilidad').text('$' + utilidad.toLocaleString('es-CO'));
        $('#reporteTransacciones').text(data.transacciones_confirmadas || 0);
    });
    
    // Obtener transacciones para la tabla
    cargarTransaccionesTabla(fechaInicio, fechaFin);
    
    // Actualizar gráficos
    actualizarGraficos(fechaInicio, fechaFin);
}

function generarBalanceComprobacion(fechaInicio, fechaFin) {
    $.get(`api/endpoints/contabilidad.php?accion=balance_comprobacion&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`, function(data) {
        let tablaHtml = `
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Balance de Comprobación</h5>
                    <small class="text-muted">Del ${new Date(fechaInicio).toLocaleDateString('es-CO')} al ${new Date(fechaFin).toLocaleDateString('es-CO')}</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre de Cuenta</th>
                                    <th>Total Debe</th>
                                    <th>Total Haber</th>
                                    <th>Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
        `;
        
        let totalDebe = 0;
        let totalHaber = 0;
        
        data.forEach(cuenta => {
            const debe = parseFloat(cuenta.total_debe || 0);
            const haber = parseFloat(cuenta.total_haber || 0);
            const saldo = debe - haber;
            
            totalDebe += debe;
            totalHaber += haber;
            
            tablaHtml += `
                <tr>
                    <td>${cuenta.codigo}</td>
                    <td>${cuenta.nombre}</td>
                    <td class="text-end">$${debe.toLocaleString('es-CO')}</td>
                    <td class="text-end">$${haber.toLocaleString('es-CO')}</td>
                    <td class="text-end ${saldo >= 0 ? 'text-success' : 'text-danger'}">
                        $${Math.abs(saldo).toLocaleString('es-CO')}
                    </td>
                </tr>
            `;
        });
        
        tablaHtml += `
                            </tbody>
                            <tfoot class="table-dark">
                                <tr>
                                    <th colspan="2">TOTALES</th>
                                    <th class="text-end">$${totalDebe.toLocaleString('es-CO')}</th>
                                    <th class="text-end">$${totalHaber.toLocaleString('es-CO')}</th>
                                    <th class="text-end">$${Math.abs(totalDebe - totalHaber).toLocaleString('es-CO')}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        `;
        
        $('#reporteContent').html(tablaHtml);
    });
}

function generarEstadoResultados(fechaInicio, fechaFin) {
    // Implementar estado de resultados
    generarResumenFinanciero(fechaInicio, fechaFin);
}

function generarMovimientosCuenta(fechaInicio, fechaFin, cuentaId) {
    if (!cuentaId) {
        showNotification('Seleccione una cuenta para ver sus movimientos', 'error');
        return;
    }
    
    // Implementar movimientos por cuenta
    generarResumenFinanciero(fechaInicio, fechaFin);
}

function cargarTransaccionesTabla(fechaInicio, fechaFin) {
    $.get(`api/endpoints/contabilidad.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&estado=confirmada`, function(data) {
        const tbody = $('#tablaReporteBody');
        tbody.empty();
        
        const transacciones = Array.isArray(data) ? data : [];
        
        transacciones.forEach(transaccion => {
            const tipoBadge = transaccion.tipo_transaccion === 'ingreso' ? 'success' : 
                             transaccion.tipo_transaccion === 'egreso' ? 'danger' : 'info';
            
            tbody.append(`
                <tr>
                    <td>${new Date(transaccion.fecha).toLocaleDateString('es-CO')}</td>
                    <td><span class="badge bg-secondary">${transaccion.numero_comprobante}</span></td>
                    <td>${transaccion.descripcion}</td>
                    <td><span class="badge bg-${tipoBadge}">${transaccion.tipo_transaccion}</span></td>
                    <td><strong>$${parseFloat(transaccion.monto_total).toLocaleString('es-CO')}</strong></td>
                    <td><span class="badge bg-success">${transaccion.estado}</span></td>
                    <td>${transaccion.usuario_nombre} ${transaccion.usuario_apellido}</td>
                </tr>
            `);
        });
        
        if (transacciones.length === 0) {
            tbody.append('<tr><td colspan="7" class="text-center text-muted">No hay transacciones en el período seleccionado</td></tr>');
        }
    });
}

function inicializarGraficos() {
    console.log('Intentando inicializar gráficos...');
    
    // Verificar que los elementos existan antes de inicializar los gráficos
    const canvasEvolucion = document.getElementById('graficoEvolucion');
    const canvasDistribucion = document.getElementById('graficoDistribucion');
    
    console.log('Canvas evolución encontrado:', !!canvasEvolucion);
    console.log('Canvas distribución encontrado:', !!canvasDistribucion);
    
    if (!canvasEvolucion) {
        console.error('No se encontró el elemento canvas graficoEvolucion');
        return;
    }
    
    if (!canvasDistribucion) {
        console.error('No se encontró el elemento canvas graficoDistribucion');
        return;
    }
    
    // Destruir gráficos existentes si ya están inicializados
    if (chartEvolucion) {
        console.log('Destruyendo gráfico evolución existente...');
        chartEvolucion.destroy();
        chartEvolucion = null;
    }
    
    if (chartDistribucion) {
        console.log('Destruyendo gráfico distribución existente...');
        chartDistribucion.destroy();
        chartDistribucion = null;
    }
    
    try {
        console.log('Creando gráfico de evolución...');
        // Gráfico de evolución
        const ctxEvolucion = canvasEvolucion.getContext('2d');
        chartEvolucion = new Chart(ctxEvolucion, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Ingresos',
                    data: [],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }, {
                    label: 'Egresos',
                    data: [],
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        console.log('Gráfico de evolución creado exitosamente');
        
        console.log('Creando gráfico de distribución...');
        // Gráfico de distribución
        const ctxDistribucion = canvasDistribucion.getContext('2d');
        chartDistribucion = new Chart(ctxDistribucion, {
            type: 'doughnut',
            data: {
                labels: ['Ingresos', 'Egresos'],
                datasets: [{
                    data: [0, 0],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 99, 132, 0.8)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
        console.log('Gráfico de distribución creado exitosamente');
        
    } catch (error) {
        console.error('Error al crear gráficos:', error);
    }
}

function actualizarGraficos(fechaInicio, fechaFin) {
    // Verificar que los gráficos estén inicializados
    if (!chartEvolucion || !chartDistribucion) {
        console.warn('Los gráficos no están inicializados. Omitiendo actualización.');
        return;
    }
    
    // Simular datos para los gráficos (en producción, obtener datos reales)
    const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio'];
    const ingresosAleatorios = meses.map(() => Math.floor(Math.random() * 10000000) + 5000000);
    const egresosAleatorios = meses.map(() => Math.floor(Math.random() * 8000000) + 3000000);
    
    chartEvolucion.data.labels = meses;
    chartEvolucion.data.datasets[0].data = ingresosAleatorios;
    chartEvolucion.data.datasets[1].data = egresosAleatorios;
    chartEvolucion.update();
    
    const totalIngresos = ingresosAleatorios.reduce((a, b) => a + b, 0);
    const totalEgresos = egresosAleatorios.reduce((a, b) => a + b, 0);
    
    chartDistribucion.data.datasets[0].data = [totalIngresos, totalEgresos];
    chartDistribucion.update();
}

function limpiarFiltros() {
    $('#fechaInicio').val(date('Y-m-01'));
    $('#fechaFin').val(date('Y-m-d'));
    $('#cuentaFiltro').val('');
    $('#tipoReporte').val('resumen');
    generarReporte();
}

function exportarExcel() {
    const fechaInicio = $('#fechaInicio').val();
    const fechaFin = $('#fechaFin').val();
    const tipo = $('#tipoReporte').val();
    
    window.open(`api/endpoints/contabilidad.php?accion=exportar_excel&tipo=${tipo}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`, '_blank');
}

function exportarPDF() {
    const fechaInicio = $('#fechaInicio').val();
    const fechaFin = $('#fechaFin').val();
    const tipo = $('#tipoReporte').val();
    
    window.open(`api/endpoints/contabilidad.php?accion=exportar_pdf&tipo=${tipo}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`, '_blank');
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

<!-- Incluir Chart.js para gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
