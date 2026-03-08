<?php
require_once __DIR__ . '/../../config/database.php';

// Verificar sesión de usuario
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}
include __DIR__ . '/../../includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div id="notification-container"></div>
    
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Informe de Ocupación Real</h1>
                <p class="text-muted mb-0">Análisis detallado de huéspedes y acompañantes</p>
            </div>
            <div class="btn-group">
                <button class="btn btn-outline-primary" onclick="exportarExcel()">
                    <i class="fas fa-file-excel me-2"></i>Exportar Excel
                </button>
                <button class="btn btn-outline-info" onclick="actualizarDatos()">
                    <i class="fas fa-sync me-2"></i>Actualizar
                </button>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="fechaInicio" onchange="cargarInforme()">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="fechaFin" onchange="cargarInforme()">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipo Habitación</label>
                    <select class="form-select" id="tipoHabitacion" onchange="cargarInforme()">
                        <option value="">Todas</option>
                        <option value="simple">Simple</option>
                        <option value="doble">Doble</option>
                        <option value="suite">Suite</option>
                        <option value="presidencial">Presidencial</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="estadoReserva" onchange="cargarInforme()">
                        <option value="">Todos</option>
                        <option value="confirmada">Confirmada</option>
                        <option value="completada">Completada</option>
                        <option value="pendiente">Pendiente</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen Estadístico -->
    <div class="row g-4 mb-4" id="resumenEstadistico">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Reservas</h6>
                            <h3 id="totalReservas">-</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-check fa-2x"></i>
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
                            <h6 class="card-title">Total Huéspedes</h6>
                            <h3 id="totalHuespedes">-</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
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
                            <h6 class="card-title">Menores</h6>
                            <h3 id="totalMenores">-</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-child fa-2x"></i>
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
                            <h6 class="card-title">Promedio/Habitación</h6>
                            <h3 id="promedioHabitacion">-</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Ocupación Detallada -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Detalle de Ocupación Real</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="tablaOcupacion">
                    <thead>
                        <tr>
                            <th>Habitación</th>
                            <th>Tipo</th>
                            <th>Capacidad</th>
                            <th>Cliente Principal</th>
                            <th>Documento</th>
                            <th>Huéspedes Reales</th>
                            <th>Adultos</th>
                            <th>Menores</th>
                            <th>Ocupación %</th>
                            <th>Estado</th>
                            <th>Fechas</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody id="tablaOcupacionBody">
                        <!-- Datos cargados dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row g-4 mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Distribución por Edad</h5>
                </div>
                <div class="card-body">
                    <canvas id="graficoEdad" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ocupación por Tipo de Habitación</h5>
                </div>
                <div class="card-body">
                    <canvas id="graficoHabitacion" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Establecer fechas por defecto (mes actual)
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    $('#fechaInicio').val(firstDay.toISOString().split('T')[0]);
    $('#fechaFin').val(lastDay.toISOString().split('T')[0]);
    
    // Cargar datos iniciales
    cargarInforme();
});

function cargarInforme() {
    const fechaInicio = $('#fechaInicio').val();
    const fechaFin = $('#fechaFin').val();
    const tipoHabitacion = $('#tipoHabitacion').val();
    const estadoReserva = $('#estadoReserva').val();
    
    // Mostrar loading
    showNotification('Cargando datos...', 'info');
    
    // Cargar estadísticas
    $.get(`api/endpoints/acompanantes.php?estadisticas=1&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`, function(estadisticas) {
        actualizarResumen(estadisticas);
    });
    
    // Cargar ocupación real
    $.get(`api/endpoints/acompanantes.php?ocupacion_real=1&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`, function(ocupacion) {
        actualizarTabla(ocupacion);
        actualizarGraficos(ocupacion);
    });
}

function actualizarResumen(estadisticas) {
    $('#totalReservas').text(estadisticas.reservas_con_acompanantes || 0);
    $('#totalHuespedes').text(estadisticas.total_acompanantes || 0);
    $('#totalMenores').text(estadisticas.total_menores || 0);
    $('#promedioHabitacion').text(estadisticas.edad_promedio ? parseFloat(estadisticas.edad_promedio).toFixed(1) : '0');
}

function actualizarTabla(ocupacion) {
    const tbody = $('#tablaOcupacionBody');
    tbody.empty();
    
    if (!ocupacion || ocupacion.length === 0) {
        tbody.html('<tr><td colspan="12" class="text-center">No hay datos disponibles</td></tr>');
        return;
    }
    
    ocupacion.forEach(function(item) {
        const porcentajeOcupacion = item.capacidad > 0 ? ((item.total_huespedes / item.capacidad) * 100).toFixed(1) : 0;
        const estadoOcupacion = porcentajeOcupacion > 100 ? 'table-danger' : porcentajeOcupacion === 100 ? 'table-warning' : '';
        
        const row = `
            <tr class="${estadoOcupacion}">
                <td><strong>${item.numero}</strong></td>
                <td><span class="badge bg-secondary">${item.tipo}</span></td>
                <td>${item.capacidad}</td>
                <td>${item.cliente_principal} ${item.cliente_principal_apellido || ''}</td>
                <td><small>${item.cliente_documento || 'N/A'}</small></td>
                <td><strong>${item.total_huespedes}</strong></td>
                <td>${item.total_adultos}</td>
                <td>${item.total_menores}</td>
                <td>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar ${porcentajeOcupacion > 100 ? 'bg-danger' : porcentajeOcupacion === 100 ? 'bg-warning' : 'bg-success'}" 
                             style="width: ${Math.min(porcentajeOcupacion, 100)}%">
                            ${porcentajeOcupacion}%
                        </div>
                    </div>
                </td>
                <td><span class="badge bg-info">${item.estado_reserva}</span></td>
                <td><small>${item.fecha_entrada} al ${item.fecha_salida}</small></td>
                <td><strong>$${parseFloat(item.total_reserva || 0).toLocaleString('es-CO')}</strong></td>
            </tr>
        `;
        tbody.append(row);
    });
}

function actualizarGraficos(ocupacion) {
    // Gráfico de distribución por edad
    const ctxEdad = document.getElementById('graficoEdad').getContext('2d');
    new Chart(ctxEdad, {
        type: 'doughnut',
        data: {
            labels: ['Adultos', 'Menores'],
            datasets: [{
                data: [
                    ocupacion.reduce((sum, item) => sum + parseInt(item.total_adultos || 0), 0),
                    ocupacion.reduce((sum, item) => sum + parseInt(item.total_menores || 0), 0)
                ],
                backgroundColor: ['#007bff', '#ffc107']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Gráfico de ocupación por tipo de habitación
    const ctxHabitacion = document.getElementById('graficoHabitacion').getContext('2d');
    const tipos = {};
    ocupacion.forEach(function(item) {
        if (!tipos[item.tipo]) {
            tipos[item.tipo] = { total: 0, count: 0 };
        }
        tipos[item.tipo].total += parseInt(item.total_huespedes || 0);
        tipos[item.tipo].count++;
    });
    
    new Chart(ctxHabitacion, {
        type: 'bar',
        data: {
            labels: Object.keys(tipos),
            datasets: [{
                label: 'Promedio Huéspedes',
                data: Object.values(tipos).map(t => (t.total / t.count).toFixed(1)),
                backgroundColor: '#28a745'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function exportarExcel() {
    // Implementar exportación a Excel
    showNotification('Función de exportación en desarrollo', 'info');
}

function actualizarDatos() {
    cargarInforme();
    showNotification('Datos actualizados', 'success');
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
