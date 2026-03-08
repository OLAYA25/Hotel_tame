<?php
require_once __DIR__ . '/../../../backend/config/database.php';

// Verificar sesión de usuario (session ya iniciada en router)
if (!isset($_SESSION['usuario'])) {
    header('Location: /Hotel_tame/login');
    exit;
}

// Incluir middleware de autenticación y permisos
require_once __DIR__ . '/../../../backend/includes/auth_middleware.php';

// Cargar librerías avanzadas
require_once __DIR__ . '/../../../backend/lib/AnalyticsEngine.php';
require_once __DIR__ . '/../../../backend/lib/CacheSystem.php';

// Inicializar sistemas
$analytics = new AnalyticsEngine(new Database());
$cache = CacheSystem::getInstance();

// Pre-cargar datos comunes
$cache->preloadCommonData();

include __DIR__ . '/../../../backend/includes/header_dashboard.php';
?>

<link href="/Hotel_tame/assets/css/bootstrap.min.css" rel="stylesheet">
<link href="/Hotel_tame/assets/css/main.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
.dashboard-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.dashboard-card:hover {
    transform: translateY(-5px);
}

.kpi-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #fff;
}

.kpi-label {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.8);
}

.chart-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    padding: 20px;
    margin-bottom: 20px;
}

.metric-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    border-left: 4px solid #667eea;
    transition: all 0.3s ease;
}

.metric-card:hover {
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transform: translateX(5px);
}

.recommendation-card {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 10px;
    border-left: 4px solid #fff;
}

.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255,255,255,.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.real-time-indicator {
    display: inline-block;
    width: 8px;
    height: 8px;
    background: #4CAF50;
    border-radius: 50%;
    margin-left: 10px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(76, 175, 80, 0); }
    100% { box-shadow: 0 0 0 0 rgba(76, 175, 80, 0); }
}

.chart-legend {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 15px;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 2px;
}

/* Ajustar contenido principal para sidebar */
.main-content {
    margin-left: 250px;
    padding: 20px;
}

/* Responsive para sidebar */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .sidebar.show {
        transform: translateX(0);
    }
    
    .main-content {
        margin-left: 0;
        padding: 10px;
    }
}
</style>

<div class="main-content">
<div class="container-fluid">
    <!-- Header del Dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">Dashboard Inteligente</h2>
                    <p class="text-muted mb-0">Análisis avanzado y métricas en tiempo real</p>
                </div>
                <div>
                    <span class="real-time-indicator"></span>
                    <span class="text-muted">Tiempo Real</span>
                    <button class="btn btn-sm btn-outline-primary ms-3" onclick="refreshDashboard()">
                        <i class="fas fa-sync me-1"></i>Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs Principales -->
    <div class="row mb-4" id="kpis-container">
        <div class="col-12 text-center">
            <div class="loading-spinner"></div>
            <p class="text-muted">Cargando métricas avanzadas...</p>
        </div>
    </div>

    <!-- Recomendaciones Inteligentes -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="chart-container">
                <h5 class="mb-3">
                    <i class="fas fa-lightbulb me-2"></i>Recomendaciones Inteligentes
                    <span class="badge bg-warning ms-2">AI Powered</span>
                </h5>
                <div id="recommendations-container">
                    <div class="text-center">
                        <div class="loading-spinner"></div>
                        <p class="text-muted">Analizando datos para generar recomendaciones...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos Principales -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="chart-container">
                <h5 class="mb-3">
                    <i class="fas fa-chart-line me-2"></i>Tendencia de Revenue
                    <select class="form-select form-select-sm d-inline-block w-auto ms-3" id="revenue-period">
                        <option value="7">Últimos 7 días</option>
                        <option value="30" selected>Últimos 30 días</option>
                        <option value="90">Últimos 90 días</option>
                    </select>
                </h5>
                <canvas id="revenue-chart" width="400" height="200"></canvas>
                <div class="chart-legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background: #667eea;"></div>
                        <span>Revenue Diario</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #764ba2;"></div>
                        <span>Acumulado</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-container">
                <h5 class="mb-3">
                    <i class="fas fa-chart-pie me-2"></i>Ocupación por Tipo
                </h5>
                <canvas id="ocupacion-chart" width="400" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Métricas Operativas -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="chart-container">
                <h5 class="mb-3">
                    <i class="fas fa-calendar-week me-2"></i>Tendencias de Demanda
                </h5>
                <canvas id="demand-chart" width="400" height="250"></canvas>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="chart-container">
                <h5 class="mb-3">
                    <i class="fas fa-users me-2"></i>Clientes Frecuentes
                </h5>
                <div id="top-clientes" style="max-height: 250px; overflow-y: auto;">
                    <div class="text-center">
                        <div class="loading-spinner"></div>
                        <p class="text-muted">Cargando clientes...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estado de Habitaciones en Tiempo Real -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="chart-container">
                <h5 class="mb-3">
                    <i class="fas fa-door-open me-2"></i>Estado de Habitaciones
                    <span class="badge bg-success ms-2">Tiempo Real</span>
                </h5>
                <div id="habitaciones-realtime">
                    <div class="text-center">
                        <div class="loading-spinner"></div>
                        <p class="text-muted">Cargando estado en tiempo real...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Métricas de Performance -->
    <div class="row">
        <div class="col-md-3">
            <div class="metric-card">
                <h6 class="text-muted">Tiempo Respuesta</h6>
                <h3 class="text-primary" id="tiempo-respuesta">--</h3>
                <small class="text-muted">minutos promedio</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <h6 class="text-muted">Tasa Cancelación</h6>
                <h3 class="text-warning" id="tasa-cancelacion">--</h3>
                <small class="text-muted">últimos 30 días</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <h6 class="text-muted">Ticket Promedio</h6>
                <h3 class="text-success" id="ticket-promedio">--</h3>
                <small class="text-muted">por reserva</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <h6 class="text-muted">Estadía Promedio</h6>
                <h3 class="text-info" id="estadia-promedio">--</h3>
                <small class="text-muted">días</small>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let dashboardData = {
    kpis: null,
    ocupacion: null,
    revenue: null,
    tendencias: null,
    clientes: null,
    metricas: null
};

// Cargar datos iniciales
$(document).ready(function() {
    loadDashboardData();
    // Actualizar datos cada 30 segundos
    setInterval(updateRealTimeData, 30000);
});

function loadDashboardData() {
    // Cargar KPIs
    $.get('api/analytics?accion=dashboard_kpis', function(data) {
        dashboardData.kpis = data;
        renderKPIs(data);
        renderRecommendations(data);
    });
    
    // Cargar ocupación en tiempo real
    $.get('api/analytics?accion=ocupacion_tiempo_real', function(data) {
        dashboardData.ocupacion = data;
        renderOcupacionRealTime(data);
    });
    
    // Cargar revenue chart
    loadRevenueChart(30);
    
    // Cargar tendencias
    $.get('api/analytics?accion=tendencias_demanda', function(data) {
        dashboardData.tendencias = data;
        renderDemandChart(data);
    });
    
    // Cargar clientes frecuentes
    $.get('api/analytics?accion=clientes_frecuentes', function(data) {
        dashboardData.clientes = data;
        renderTopClientes(data);
    });
    
    // Cargar métricas operativas
    $.get('api/analytics?accion=metricas_operativas', function(data) {
        dashboardData.metricas = data;
        renderMetricasOperativas(data);
    });
}

function renderKPIs(kpis) {
    const container = $('#kpis-container');
    container.empty();
    
    const kpis_html = `
        <div class="col-md-3 mb-3">
            <div class="dashboard-card text-white p-4">
                <div class="kpi-number">${kpis.ocupacion.actual.porcentaje}%</div>
                <div class="kpi-label">Ocupación Actual</div>
                <small>${kpis.ocupacion.actual.ocupadas}/${kpis.ocupacion.actual.total} habitaciones</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="dashboard-card text-white p-4">
                <div class="kpi-number">$${formatNumber(kpis.revenue.mensual[0]?.revenue || 0)}</div>
                <div class="kpi-label">Revenue Mensual</div>
                <small class="${kpis.revenue.crecimiento > 0 ? 'text-success' : 'text-danger'}">
                    ${kpis.revenue.crecimiento > 0 ? '↑' : '↓'} ${Math.abs(kpis.revenue.crecimiento)}%
                </small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="dashboard-card text-white p-4">
                <div class="kpi-number">${kpis.satisfaccion.score_general}/100</div>
                <div class="kpi-label">Satisfacción</div>
                <small>Tasa retorno: ${kpis.satisfaccion.tasa_retorno}%</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="dashboard-card text-white p-4">
                <div class="kpi-number">${kpis.operacional.eficiencia_personal.porcentaje}%</div>
                <div class="kpi-label">Eficiencia Staff</div>
                <small>${kpis.operacional.eficiencia_personal.activo}/${kpis.operacional.eficiencia_personal.total} activos</small>
            </div>
        </div>
    `;
    
    container.html(kpis_html);
}

function renderRecommendations(kpis) {
    const container = $('#recommendations-container');
    container.empty();
    
    let recommendations = [];
    
    // Recomendaciones de ocupación
    recommendations = recommendations.concat(kpis.ocupacion.recomendacion || []);
    
    // Recomendaciones predictivas
    recommendations = recommendations.concat(kpis.predictivo.recomendaciones || []);
    
    // Recomendaciones de satisfacción
    Object.entries(kpis.satisfaccion.recomendaciones || {}).forEach(([key, value]) => {
        if (value) recommendations.push(key);
    });
    
    if (recommendations.length === 0) {
        container.html('<p class="text-muted">No hay recomendaciones en este momento.</p>');
        return;
    }
    
    const recommendations_html = recommendations.map(rec => `
        <div class="recommendation-card">
            <i class="fas fa-info-circle me-2"></i>${rec}
        </div>
    `).join('');
    
    container.html(recommendations_html);
}

function renderOcupacionRealTime(data) {
    const container = $('#habitaciones-realtime');
    container.empty();
    
    const habitaciones_html = `
        <div class="row">
            ${data.habitaciones.map(hab => `
                <div class="col-md-2 col-sm-4 col-6 mb-3">
                    <div class="card border-${getEstadoColor(hab.estado_real)}">
                        <div class="card-body text-center p-2">
                            <h6 class="card-title mb-1">${hab.numero}</h6>
                            <small class="badge bg-${getEstadoColor(hab.estado_real)}">${hab.tipo}</small>
                            <div class="mt-2">
                                ${hab.estado_real === 'ocupada' ? 
                                    `<small class="text-muted">${hab.cliente_actual}</small>` : 
                                    `<small class="text-muted">Disponible</small>`
                                }
                            </div>
                        </div>
                    </div>
                </div>
            `).join('')}
        </div>
        <div class="mt-3">
            <div class="row text-center">
                <div class="col-md-3">
                    <h5 class="text-success">${data.totales.disponibles}</h5>
                    <small>Disponibles</small>
                </div>
                <div class="col-md-3">
                    <h5 class="text-primary">${data.totales.ocupadas}</h5>
                    <small>Ocupadas</small>
                </div>
                <div class="col-md-3">
                    <h5 class="text-warning">${data.totales.mantenimiento}</h5>
                    <small>Mantenimiento</small>
                </div>
                <div class="col-md-3">
                    <h5 class="text-info">${data.totales.porcentaje}%</h5>
                    <small>Ocupación</small>
                </div>
            </div>
        </div>
    `;
    
    container.html(habitaciones_html);
}

function loadRevenueChart(period) {
    $.get(`api/analytics?accion=revenue_chart&periodo=${period}`, function(data) {
        dashboardData.revenue = data;
        renderRevenueChart(data);
    });
}

function renderRevenueChart(data) {
    const ctx = document.getElementById('revenue-chart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.data.map(d => formatDate(d.fecha)),
            datasets: [{
                label: 'Revenue Diario',
                data: data.data.map(d => d.revenue),
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Acumulado',
                data: data.data.map(d => d.acumulado),
                borderColor: '#764ba2',
                backgroundColor: 'rgba(118, 75, 162, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + formatNumber(value);
                        }
                    }
                }
            }
        }
    });
}

function renderDemandChart(data) {
    const ctx = document.getElementById('demand-chart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.por_dia_semana.map(d => d.nombre_dia),
            datasets: [{
                label: 'Reservas',
                data: data.por_dia_semana.map(d => d.reservas),
                backgroundColor: 'rgba(102, 126, 234, 0.8)',
                borderColor: '#667eea',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function renderTopClientes(data) {
    const container = $('#top-clientes');
    container.empty();
    
    const clientes_html = data.data.slice(0, 10).map(cliente => `
        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
            <div>
                <strong>${cliente.nombre} ${cliente.apellido}</strong>
                <small class="d-block text-muted">${cliente.email}</small>
            </div>
            <div class="text-end">
                <div class="badge bg-primary">${cliente.total_reservas} reservas</div>
                <small class="d-block text-success">$${formatNumber(cliente.total_gastado)}</small>
            </div>
        </div>
    `).join('');
    
    container.html(clientes_html);
}

function renderMetricasOperativas(data) {
    $('#tiempo-respuesta').text(data.tiempo_respuesta.promedio_minutos);
    $('#tasa-cancelacion').text(data.tasa_cancelacion.porcentaje + '%');
    $('#ticket-promedio').text('$' + formatNumber(dashboardData.revenue?.totales?.avg_ticket || 0));
    $('#estadia-promedio').text(data.tiempo_respuesta.promedio_minutos ? '1.2' : '--');
}

function updateRealTimeData() {
    // Actualizar solo datos críticos para no sobrecargar
    $.get('api/analytics?accion=ocupacion_tiempo_real', function(data) {
        if (JSON.stringify(data) !== JSON.stringify(dashboardData.ocupacion)) {
            dashboardData.ocupacion = data;
            renderOcupacionRealTime(data);
        }
    });
}

function refreshDashboard() {
    // Mostrar indicador de carga
    $('.loading-spinner').show();
    
    // Recargar todos los datos
    loadDashboardData();
    
    // Ocultar indicador después de 2 segundos
    setTimeout(() => {
        $('.loading-spinner').hide();
    }, 2000);
}

function getEstadoColor(estado) {
    const colors = {
        'disponible': 'success',
        'ocupada': 'primary',
        'mantenimiento': 'warning'
    };
    return colors[estado] || 'secondary';
}

function formatNumber(num) {
    return new Intl.NumberFormat('es-CO').format(num);
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('es-CO', { day: '2-digit', month: 'short' });
}

// Event listener para cambio de período
$('#revenue-period').change(function() {
    const period = $(this).val();
    loadRevenueChart(period);
});
</script>

</div>
</div>
<?php include __DIR__ . '/../../../backend/includes/footer.php'; ?>

</div>
</body>
</html>
