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
    <div class="page-header">
        <div>
            <h1>Documentación del Sistema</h1>
            <p class="text-muted mb-0">Accede a toda la documentación, manuales y recursos de ayuda</p>
        </div>
    </div>

    <!-- Sección de Documentos Principales -->
    <div class="row g-4 mb-5">
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 shadow-sm border-0 hover-card">
                <div class="card-body text-center">
                    <div class="icon-box bg-primary bg-opacity-10 text-primary rounded p-3 mx-auto mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-book fs-1"></i>
                    </div>
                    <h5 class="card-title">Manual de Usuario</h5>
                    <p class="text-muted small mb-3">Guía completa para usuarios del sistema</p>
                    <p class="text-muted small mb-3">2.5 MB</p>
                    <button class="btn btn-primary btn-sm" onclick="descargarDocumento('manual-usuario')">
                        <i class="fas fa-download me-2"></i>Descargar
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card h-100 shadow-sm border-0 hover-card">
                <div class="card-body text-center">
                    <div class="icon-box bg-success bg-opacity-10 text-success rounded p-3 mx-auto mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-file-alt fs-1"></i>
                    </div>
                    <h5 class="card-title">Guía de Instalación</h5>
                    <p class="text-muted small mb-3">Instrucciones paso a paso para instalar el sistema</p>
                    <p class="text-muted small mb-3">1.8 MB</p>
                    <button class="btn btn-primary btn-sm" onclick="descargarDocumento('guia-instalacion')">
                        <i class="fas fa-download me-2"></i>Descargar
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card h-100 shadow-sm border-0 hover-card">
                <div class="card-body text-center">
                    <div class="icon-box bg-info bg-opacity-10 text-info rounded p-3 mx-auto mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-code fs-1"></i>
                    </div>
                    <h5 class="card-title">Documentación Técnica</h5>
                    <p class="text-muted small mb-3">Arquitectura, APIs y detalles técnicos</p>
                    <p class="text-muted small mb-3">3.2 MB</p>
                    <button class="btn btn-primary btn-sm" onclick="descargarDocumento('doc-tecnica')">
                        <i class="fas fa-download me-2"></i>Descargar
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card h-100 shadow-sm border-0 hover-card">
                <div class="card-body text-center">
                    <div class="icon-box bg-warning bg-opacity-10 text-warning rounded p-3 mx-auto mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-video fs-1"></i>
                    </div>
                    <h5 class="card-title">Tutoriales en Video</h5>
                    <p class="text-muted small mb-3">Serie de videos explicativos del sistema</p>
                    <p class="text-muted small mb-3">Ver en línea</p>
                    <button class="btn btn-primary btn-sm" onclick="verTutoriales()">
                        <i class="fas fa-play me-2"></i>Ver Videos
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Ejemplos Prácticos -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex align-items-center">
                <i class="fas fa-lightbulb text-warning me-2 fs-4"></i>
                <div>
                    <h5 class="mb-0">Ejemplos Prácticos</h5>
                    <small class="text-muted">Casos de uso comunes y ejemplos de implementación</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                    <div>
                        <h6 class="mb-1">Ejemplo 1: Registro de nueva reserva con cliente nuevo</h6>
                        <small class="text-muted">Aprende a crear reservas y registrar clientes simultáneamente</small>
                    </div>
                    <button class="btn btn-outline-primary btn-sm" onclick="verEjemplo(1)">Ver ejemplo</button>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                    <div>
                        <h6 class="mb-1">Ejemplo 2: Check-in rápido con pre-registro</h6>
                        <small class="text-muted">Proceso optimizado para check-in de huéspedes registrados</small>
                    </div>
                    <button class="btn btn-outline-primary btn-sm" onclick="verEjemplo(2)">Ver ejemplo</button>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                    <div>
                        <h6 class="mb-1">Ejemplo 3: Gestión de pagos y facturación</h6>
                        <small class="text-muted">Cómo procesar pagos y generar facturas automáticamente</small>
                    </div>
                    <button class="btn btn-outline-primary btn-sm" onclick="verEjemplo(3)">Ver ejemplo</button>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                    <div>
                        <h6 class="mb-1">Ejemplo 4: Reportes personalizados de ocupación</h6>
                        <small class="text-muted">Genera reportes detallados de ocupación por período</small>
                    </div>
                    <button class="btn btn-outline-primary btn-sm" onclick="verEjemplo(4)">Ver ejemplo</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Ayuda Rápida -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex align-items-center">
                <i class="fas fa-question-circle text-info me-2 fs-4"></i>
                <div>
                    <h5 class="mb-0">Ayuda Rápida</h5>
                    <small class="text-muted">Preguntas frecuentes y soluciones rápidas</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item border-0">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            ¿Cómo crear una nueva reserva?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Ve a la sección "Reservas" y haz clic en "Nueva Reserva". Selecciona el cliente, la habitación disponible, las fechas de entrada y salida, y confirma los datos.
                        </div>
                    </div>
                </div>
                <div class="accordion-item border-0">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            ¿Cómo cambiar el estado de una habitación?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            En la sección "Habitaciones", haz clic en "Editar" en la habitación deseada y cambia el estado a "Disponible", "Ocupada" o "Mantenimiento".
                        </div>
                    </div>
                </div>
                <div class="accordion-item border-0">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            ¿Cómo generar reportes?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Accede al Dashboard principal donde encontrarás estadísticas en tiempo real. Para reportes más detallados, usa la opción "Exportar" en cada módulo.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function descargarDocumento(tipo) {
    showNotification('Descargando documento...', 'info');
    // Simular descarga
    setTimeout(() => {
        showNotification('Documento descargado exitosamente', 'success');
    }, 1500);
}

function verTutoriales() {
    showNotification('Abriendo galería de tutoriales...', 'info');
}

function verEjemplo(numero) {
    showNotification(`Cargando ejemplo ${numero}...`, 'info');
}
</script>

<style>
.hover-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
}

.icon-box {
    display: flex;
    align-items: center;
    justify-content: center;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}
</style>

<?php include 'includes/footer.php'; ?>
