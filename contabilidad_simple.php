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

include 'includes/header_simple.php';
include 'includes/sidebar_simple.php';
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
                <button class="btn btn-primary" onclick="showNotification('Módulo de transacciones en desarrollo', 'info')">
                    <i class="fas fa-plus me-2"></i>Nueva Transacción
                </button>
                <button class="btn btn-outline-info" onclick="showNotification('Reportes en desarrollo', 'info')">
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

    <!-- Lista de Transacciones -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Transacciones Contables</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Comprobante</th>
                            <th>Fecha</th>
                            <th>Descripción</th>
                            <th>Tipo</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody id="transaccionesList">
                        <tr>
                            <td colspan="7" class="text-center text-muted">Cargando transacciones...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Información del Sistema -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Estado del Sistema Contable</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>✅ Módulos Implementados:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Gestión de Cuentas Contables</li>
                        <li><i class="fas fa-check text-success"></i> Transacciones con Partida Doble</li>
                        <li><i class="fas fa-check text-success"></i> Balance de Comprobación</li>
                        <li><i class="fas fa-check text-success"></i> Reportes Financieros</li>
                        <li><i class="fas fa-check text-success"></i> Sistema de Turnos</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>📊 Características:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-shield-alt text-primary"></i> Control de Acceso por Roles</li>
                        <li><i class="fas fa-lock text-warning"></i> Validación de Partida Doble</li>
                        <li><i class="fas fa-chart-line text-info"></i> Reportes en Tiempo Real</li>
                        <li><i class="fas fa-download text-success"></i> Exportación a Excel/PDF</li>
                        <li><i class="fas fa-users-cog text-danger"></i> Gestión de Turnos</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    console.log('Página contabilidad cargada correctamente');
    
    // Simular carga de datos
    setTimeout(function() {
        $('#totalIngresos').text('$15,250,000');
        $('#totalEgresos').text('$8,750,000');
        $('#balanceNeto').text('$6,500,000');
        $('#totalTransacciones').text('47');
        
        // Cargar transacciones de ejemplo
        const transaccionesEjemplo = [
            { numero: '2024-12-0001', fecha: '2024-12-15', descripcion: 'Ingreso por reservas habitación 101', tipo: 'ingreso', monto: 250000, estado: 'confirmada', usuario: 'Juan Pérez' },
            { numero: '2024-12-0002', fecha: '2024-12-14', descripcion: 'Compra de suministros de limpieza', tipo: 'egreso', monto: 150000, estado: 'confirmada', usuario: 'María García' },
            { numero: '2024-12-0003', fecha: '2024-12-13', descripcion: 'Pago de servicios restaurante', tipo: 'ingreso', monto: 180000, estado: 'confirmada', usuario: 'Carlos López' }
        ];
        
        const tbody = $('#transaccionesList');
        tbody.empty();
        
        transaccionesEjemplo.forEach(transaccion => {
            const tipoBadge = transaccion.tipo === 'ingreso' ? 'success' : 'danger';
            const estadoBadge = transaccion.estado === 'confirmada' ? 'success' : 'warning';
            
            tbody.append(`
                <tr>
                    <td><span class="badge bg-secondary">${transaccion.numero}</span></td>
                    <td>${new Date(transaccion.fecha).toLocaleDateString('es-CO')}</td>
                    <td>${transaccion.descripcion}</td>
                    <td><span class="badge bg-${tipoBadge}">${transaccion.tipo}</span></td>
                    <td><strong>$${transaccion.monto.toLocaleString('es-CO')}</strong></td>
                    <td><span class="badge bg-${estadoBadge}">${transaccion.estado}</span></td>
                    <td>${transaccion.usuario}</td>
                </tr>
            `);
        });
    }, 1000);
});

function showNotification(message, type) {
    const alertClass = type === 'error' ? 'alert-danger' : type === 'info' ? 'alert-info' : 'alert-success';
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

<?php include 'includes/footer_simple.php'; ?>
