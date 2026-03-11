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
                <h1>Informe de Huéspedes</h1>
                <p class="text-muted mb-0">Reporte detallado de huéspedes por período</p>
            </div>
            <div class="btn-group">
                <button class="btn btn-success" onclick="generarInforme()">
                    <i class="fas fa-chart-bar me-2"></i>Generar Informe
                </button>
                <button class="btn btn-primary" onclick="exportarCSV()">
                    <i class="fas fa-file-csv me-2"></i>Exportar CSV
                </button>
                <button class="btn btn-success" onclick="exportarXLSX()">
                    <i class="fas fa-file-excel me-2"></i>Exportar XLSX
                </button>
                <button class="btn btn-warning" onclick="exportarODS()">
                    <i class="fas fa-file-alt me-2"></i>Exportar ODS
                </button>
            </div>
        </div>
    </div>

    <!-- Filtros del Informe -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Filtros del Informe</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Mes</label>
                    <select class="form-select" id="mesFiltro">
                        <option value="1">Enero</option>
                        <option value="2">Febrero</option>
                        <option value="3">Marzo</option>
                        <option value="4">Abril</option>
                        <option value="5">Mayo</option>
                        <option value="6">Junio</option>
                        <option value="7">Julio</option>
                        <option value="8">Agosto</option>
                        <option value="9">Septiembre</option>
                        <option value="10">Octubre</option>
                        <option value="11">Noviembre</option>
                        <option value="12">Diciembre</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Año</label>
                    <select class="form-select" id="anioFiltro">
                        <!-- Se llenará dinámicamente -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Motivo de Viaje</label>
                    <select class="form-select" id="motivoFiltro">
                        <option value="">Todos</option>
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
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nacionalidad</label>
                    <select class="form-select" id="nacionalidadFiltro">
                        <option value="">Todas</option>
                        <!-- Se llenará dinámicamente -->
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen del Informe -->
    <div class="row g-4 mb-4" id="resumenCards" style="display: none;">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Reservas</h6>
                            <h3 id="totalReservas">0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-bed fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Ingresos</h6>
                            <h3 id="totalIngresos">$0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Pax</h6>
                            <h3 id="totalPax">0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Adultos</h6>
                            <h3 id="totalAdultos">0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Niños</h6>
                            <h3 id="totalNinos">0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-child fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Días HPDJ</h6>
                            <h3 id="totalDias">0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Informe -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Detalle del Informe</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="tablaInforme">
                    <thead class="table-dark">
                        <tr>
                            <th>ITEM</th>
                            <th>R.H.</th>
                            <th>VALOR</th>
                            <th>DIAS HPDJ.</th>
                            <th>ADULTOS</th>
                            <th>NIÑOS</th>
                            <th>PAX</th>
                            <th>NACIONALIDAD</th>
                            <th>MOTIVO DE VIAJE</th>
                            <th>CLIENTE</th>
                            <th>FECHA ENTRADA</th>
                            <th>FECHA SALIDA</th>
                        </tr>
                    </thead>
                    <tbody id="tablaInformeBody">
                        <!-- Se llenará dinámicamente -->
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <nav aria-label="Paginación del informe">
                <ul class="pagination justify-content-center" id="paginacionInforme">
                    <!-- Se llenará dinámicamente -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Llenar select de años
    const anioActual = new Date().getFullYear();
    const anioFiltro = $('#anioFiltro');
    
    for (let i = anioActual; i >= anioActual - 5; i--) {
        anioFiltro.append(`<option value="${i}">${i}</option>`);
    }
    
    // Establecer mes y año actual
    $('#mesFiltro').val(new Date().getMonth() + 1);
    $('#anioFiltro').val(anioActual);
    
    // Cargar nacionalidades disponibles
    cargarNacionalidades();
    
    // Generar informe automáticamente al cargar
    setTimeout(() => generarInforme(), 500);
});

function cargarNacionalidades() {
    $.get('api/endpoints/informe_huespedes_csv.php?accion=generar_informe&mes=' + $('#mesFiltro').val() + '&anio=' + $('#anioFiltro').val(), function(data) {
        const nacionalidades = [...new Set(data.informe.map(item => item.NACIONALIDAD))].filter(n => n && n !== 'No especificada');
        
        const nacionalidadFiltro = $('#nacionalidadFiltro');
        nacionalidadFiltro.find('option:not(:first)').remove();
        
        nacionalidades.sort().forEach(nacionalidad => {
            nacionalidadFiltro.append(`<option value="${nacionalidad}">${nacionalidad}</option>`);
        });
    });
}

function generarInforme() {
    const mes = $('#mesFiltro').val();
    const anio = $('#anioFiltro').val();
    const motivo = $('#motivoFiltro').val();
    const nacionalidad = $('#nacionalidadFiltro').val();
    
    showNotification('Generando informe...', 'info');
    
    $.get(`api/endpoints/informe_huespedes_csv.php?accion=generar_informe&mes=${mes}&anio=${anio}`, function(data) {
        console.log('Datos del informe:', data);
        
        // Filtrar datos si es necesario
        let informeFiltrado = data.informe;
        
        if (motivo) {
            informeFiltrado = informeFiltrado.filter(item => item['MOTIVO DE VIAJE'].toLowerCase().includes(motivo.toLowerCase()));
        }
        
        if (nacionalidad) {
            informeFiltrado = informeFiltrado.filter(item => item.NACIONALIDAD === nacionalidad);
        }
        
        // Actualizar resumen
        actualizarResumen(data.totales, informeFiltrado.length);
        
        // Llenar tabla
        llenarTabla(informeFiltrado);
        
        // Mostrar resumen
        $('#resumenCards').show();
        
        showNotification('Informe generado exitosamente', 'success');
    }).fail(function(xhr, status, error) {
        console.error('Error generando informe:', xhr.responseText);
        showNotification('Error al generar el informe', 'error');
    });
}

function actualizarResumen(totales, reservasFiltradas) {
    $('#totalReservas').text(reservasFiltradas);
    $('#totalIngresos').text('$' + totales.total_valor);
    $('#totalPax').text(totales.total_pax);
    $('#totalAdultos').text(totales.total_adultos);
    $('#totalNinos').text(totales.total_ninos);
    $('#totalDias').text(totales.total_dias);
}

function llenarTabla(datos) {
    const tbody = $('#tablaInformeBody');
    tbody.empty();
    
    if (datos.length === 0) {
        tbody.append('<tr><td colspan="12" class="text-center">No se encontraron datos para el período seleccionado</td></tr>');
        return;
    }
    
    datos.forEach(item => {
        const row = `
            <tr>
                <td>${item.ITEM}</td>
                <td><span class="badge bg-primary">${item['R.H.']}</span></td>
                <td><strong>$${item.VALOR}</strong></td>
                <td>${item['DIAS HPDJ.']}</td>
                <td><span class="badge bg-success">${item.ADULTOS}</span></td>
                <td><span class="badge bg-warning">${item.NIÑOS}</span></td>
                <td><span class="badge bg-info">${item.PAX}</span></td>
                <td>${item.NACIONALIDAD}</td>
                <td><span class="badge bg-secondary">${item['MOTIVO DE VIAJE']}</span></td>
                <td>${item.CLIENTE}</td>
                <td>${formatDate(item.FECHA_ENTRADA)}</td>
                <td>${formatDate(item.FECHA_SALIDA)}</td>
            </tr>
        `;
        tbody.append(row);
    });
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function exportarCSV() {
    const mes = $('#mesFiltro').val();
    const anio = $('#anioFiltro').val();
    
    window.open(`api/endpoints/informe_huespedes_csv.php?accion=exportar_csv&mes=${mes}&anio=${anio}`, '_blank');
    showNotification('Descargando archivo CSV...', 'info');
}

function exportarXLSX() {
    const mes = $('#mesFiltro').val();
    const anio = $('#anioFiltro').val();
    
    window.open(`api/endpoints/informe_huespedes_xlsx.php?accion=exportar_xlsx&mes=${mes}&anio=${anio}`, '_blank');
    showNotification('Generando archivo XLSX (Excel)...', 'info');
}

function exportarODS() {
    const mes = $('#mesFiltro').val();
    const anio = $('#anioFiltro').val();
    
    window.open(`api/endpoints/informe_huespedes_xlsx.php?accion=exportar_ods&mes=${mes}&anio=${anio}`, '_blank');
    showNotification('Generando archivo ODS (OpenDocument)...', 'info');
}

// Event listeners para filtros
$('#mesFiltro, #anioFiltro').change(function() {
    generarInforme();
});

$('#motivoFiltro, #nacionalidadFiltro').change(function() {
    // Volver a filtrar los datos ya cargados
    generarInforme();
});
</script>

<?php include __DIR__ . '/../../../backend/includes/footer.php'; ?>
