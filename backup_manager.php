<?php
require_once 'config/database.php';

// Verificar sesión de usuario
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Incluir middleware de autenticación y permisos
require_once 'includes/auth_middleware.php';

// Cargar librerías
require_once 'lib/BackupSystem.php';

include 'includes/header.php';
?>

<style>
.backup-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.backup-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.backup-item {
    background: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 15px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    border-left: 4px solid #667eea;
    transition: all 0.3s ease;
}

.backup-item:hover {
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transform: translateX(5px);
}

.backup-size {
    font-size: 0.9rem;
    color: #6c757d;
}

.backup-date {
    font-size: 0.8rem;
    color: #adb5bd;
}

.status-indicator {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 8px;
}

.status-success { background: #28a745; }
.status-warning { background: #ffc107; }
.status-danger { background: #dc3545; }
.status-info { background: #17a2b8; }

.config-section {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}

.progress-ring {
    transform: rotate(-90deg);
}

.progress-ring-circle {
    transition: stroke-dashoffset 0.35s;
    stroke: #667eea;
    stroke-width: 4;
    fill: transparent;
}

.progress-ring-bg {
    stroke: #e9ecef;
    stroke-width: 4;
    fill: transparent;
}

.loading-backup {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}

.loading-backup.active {
    display: flex;
}

.backup-progress {
    background: white;
    border-radius: 10px;
    padding: 30px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}
</style>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">Gestión de Backups</h2>
                    <p class="text-muted mb-0">Sistema automatizado de respaldo y restauración</p>
                </div>
                <div>
                    <button class="btn btn-success" onclick="createBackup('full')">
                        <i class="fas fa-download me-2"></i>Crear Backup Completo
                    </button>
                    <button class="btn btn-primary" onclick="scheduleBackup()">
                        <i class="fas fa-clock me-2"></i>Programar Backup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Estado del Sistema -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="backup-card text-white p-4 text-center">
                <h4 class="mb-3">Total Backups</h4>
                <h2 class="mb-0" id="total-backups">--</h2>
                <small class="d-block">Archivos almacenados</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="backup-card text-white p-4 text-center">
                <h4 class="mb-3">Espacio Usado</h4>
                <h2 class="mb-0" id="total-size">--</h2>
                <small class="d-block">Total almacenado</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="backup-card text-white p-4 text-center">
                <h4 class="mb-3">Último Backup</h4>
                <h2 class="mb-0" id="last-backup">--</h2>
                <small class="d-block" id="last-backup-time">--</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="backup-card text-white p-4 text-center">
                <h4 class="mb-3">Próximo Backup</h4>
                <h2 class="mb-0" id="next-backup">--</h2>
                <small class="d-block" id="next-backup-time">--</small>
            </div>
        </div>
    </div>

    <!-- Configuración -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="config-section">
                <h5 class="mb-4">
                    <i class="fas fa-cog me-2"></i>Configuración Automática
                </h5>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="auto-backup" checked>
                            <label class="form-check-label" for="auto-backup">
                                Backup Automático
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Frecuencia</label>
                        <select class="form-select" id="backup-frequency">
                            <option value="daily" selected>Diario</option>
                            <option value="weekly">Semanal</option>
                            <option value="monthly">Mensual</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Máximo Backups</label>
                        <input type="number" class="form-control" id="max-backups" value="30" min="1" max="100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Notificación Email</label>
                        <input type="email" class="form-control" id="notification-email" value="admin@hotel.com">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 text-end">
                        <button class="btn btn-primary" onclick="updateConfig()">
                            <i class="fas fa-save me-2"></i>Guardar Configuración
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Backups -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>Historial de Backups
                        <div class="btn-group float-end">
                            <button class="btn btn-sm btn-outline-primary" onclick="loadBackups()">
                                <i class="fas fa-sync me-1"></i>Actualizar
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="cleanupBackups()">
                                <i class="fas fa-trash me-1"></i>Limpiar Antiguos
                            </button>
                        </div>
                    </h5>
                </div>
                <div class="card-body">
                    <div id="backups-list">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="text-muted mt-2">Cargando lista de backups...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-backup" id="loading-backup">
    <div class="backup-progress">
        <h4 class="mb-3">Creando Backup...</h4>
        <div class="spinner-border text-primary mb-3" role="status">
            <span class="visually-hidden">Procesando...</span>
        </div>
        <p class="mb-0">Por favor espere, esto puede tomar varios minutos...</p>
    </div>
</div>

<script>
let backupData = {
    backups: [],
    config: {},
    stats: {}
};

// Cargar datos iniciales
$(document).ready(function() {
    loadBackups();
    loadConfig();
    loadStats();
    
    // Actualizar cada 30 segundos
    setInterval(loadStats, 30000);
});

function loadBackups() {
    $.get('api/endpoints/backup.php?accion=listar', function(data) {
        if (data.success) {
            backupData.backups = data.backups;
            renderBackups(data.backups);
        }
    });
}

function loadConfig() {
    $.get('api/endpoints/backup.php?accion=configuracion', function(data) {
        if (data.success) {
            backupData.config = data.config;
            renderConfig(data.config);
        }
    });
}

function loadStats() {
    $.get('api/endpoints/backup.php?accion=estadisticas', function(data) {
        if (data.success) {
            backupData.stats = data.estadisticas;
            renderStats(data.estadisticas);
        }
    });
}

function renderBackups(backups) {
    const container = $('#backups-list');
    container.empty();
    
    if (backups.length === 0) {
        container.html(`
            <div class="text-center text-muted py-5">
                <i class="fas fa-inbox fa-3x mb-3"></i>
                <h5>No hay backups disponibles</h5>
                <p>Crea tu primer backup usando el botón superior</p>
            </div>
        `);
        return;
    }
    
    const backups_html = backups.map(backup => `
        <div class="backup-item">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <span class="status-indicator ${getBackupStatus(backup)}"></span>
                        <div>
                            <h6 class="mb-1">${backup.name}</h6>
                            <small class="backup-date">
                                <i class="fas fa-calendar me-1"></i>${formatDate(backup.created_at)}
                            </small>
                            <small class="backup-size d-block">
                                <i class="fas fa-database me-1"></i>${formatBytes(backup.size)}
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary" onclick="downloadBackup('${backup.name}')">
                            <i class="fas fa-download"></i> Descargar
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="restoreBackup('${backup.name}')">
                            <i class="fas fa-upload"></i> Restaurar
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteBackup('${backup.name}')">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
    
    container.html(backups_html);
}

function renderConfig(config) {
    $('#auto-backup').prop('checked', config.auto_backup);
    $('#backup-frequency').val(config.backup_interval);
    $('#max-backups').val(config.max_backups);
    $('#notification-email').val(config.notification_email);
    $('#last-backup').text(config.last_backup ? formatTime(config.last_backup) : 'Nunca');
    $('#last-backup-time').text(config.last_backup ? `Hace ${getTimeAgo(config.last_backup)}` : '');
    $('#next-backup').text(config.next_backup ? formatTime(config.next_backup) : 'No programado');
    $('#next-backup-time').text(config.next_backup ? `En ${getTimeTo(config.next_backup)}` : '');
}

function renderStats(stats) {
    $('#total-backups').text(stats.total_backups);
    $('#total-size').text(formatBytes(stats.total_size));
}

function createBackup(type) {
    if (!confirm('¿Estás seguro de crear un backup ' + type + '?\n\nEsta operación puede tomar varios minutos.')) {
        return;
    }
    
    showLoading();
    
    $.get(`api/endpoints/backup.php?accion=crear&tipo=${type}`, function(data) {
        hideLoading();
        
        if (data.success) {
            showNotification('Backup creado exitosamente', 'success');
            loadBackups();
            loadStats();
        } else {
            showNotification('Error al crear backup: ' + data.message, 'error');
        }
    }).fail(function() {
        hideLoading();
        showNotification('Error de conexión al crear backup', 'error');
    });
}

function downloadBackup(backupName) {
    window.open(`api/endpoints/backup.php?accion=descargar&backup=${backupName}`, '_blank');
}

function restoreBackup(backupName) {
    if (!confirm('¿Estás seguro de restaurar el backup ' + backupName + '?\n\n⚠️ ESTA ACCIÓN SOBREESCRIBIRÁ DATOS ACTUALES\n\nSe recomienda crear un backup antes de continuar.')) {
        return;
    }
    
    $.get(`api/endpoints/backup.php?accion=restaurar&backup=${backupName}`, function(data) {
        if (data.success) {
            showNotification('Backup restaurado exitosamente', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showNotification('Error al restaurar backup: ' + data.message, 'error');
        }
    }).fail(function() {
        showNotification('Error de conexión al restaurar backup', 'error');
    });
}

function deleteBackup(backupName) {
    if (!confirm('¿Estás seguro de eliminar el backup ' + backupName + '?\n\nEsta acción no se puede deshacer.')) {
        return;
    }
    
    $.get(`api/endpoints/backup.php?accion=eliminar&backup=${backupName}`, function(data) {
        if (data.success) {
            showNotification('Backup eliminado exitosamente', 'success');
            loadBackups();
            loadStats();
        } else {
            showNotification('Error al eliminar backup: ' + data.message, 'error');
        }
    }).fail(function() {
        showNotification('Error de conexión al eliminar backup', 'error');
    });
}

function scheduleBackup() {
    $.post('api/endpoints/backup.php', JSON.stringify({
        accion: 'programar'
    }), function(data) {
        if (data.success) {
            showNotification(data.message, 'success');
            loadConfig();
        } else {
            showNotification('Error al programar backup', 'error');
        }
    });
}

function updateConfig() {
    const config = {
        accion: 'actualizar_config',
        config: {
            auto_backup: $('#auto-backup').is(':checked'),
            backup_interval: $('#backup-frequency').val(),
            max_backups: parseInt($('#max-backups').val()),
            notification_email: $('#notification-email').val()
        }
    };
    
    $.post('api/endpoints/backup.php', JSON.stringify(config), function(data) {
        if (data.success) {
            showNotification('Configuración actualizada exitosamente', 'success');
            loadConfig();
        } else {
            showNotification('Error al actualizar configuración', 'error');
        }
    });
}

function cleanupBackups() {
    if (!confirm('¿Estás seguro de eliminar backups antiguos?\n\nSe mantendrán los backups más recientes según el límite configurado.')) {
        return;
    }
    
    showNotification('Función de limpieza en desarrollo', 'info');
}

function showLoading() {
    $('#loading-backup').addClass('active');
}

function hideLoading() {
    $('#loading-backup').removeClass('active');
}

function getBackupStatus(backup) {
    const age = (Date.now() - new Date(backup.created_at).getTime()) / (1000 * 60 * 60 * 24);
    
    if (age < 1) return 'status-success';
    if (age < 7) return 'status-info';
    if (age < 30) return 'status-warning';
    return 'status-danger';
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('es-CO', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatTime(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleTimeString('es-CO', { 
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getTimeAgo(dateStr) {
    const date = new Date(dateStr);
    const now = new Date();
    const diff = now - date;
    const hours = Math.floor(diff / (1000 * 60 * 60));
    const days = Math.floor(hours / 24);
    
    if (days > 0) return `${days} día${days > 1 ? 's' : ''}`;
    if (hours > 0) return `${hours} hora${hours > 1 ? 's' : ''}`;
    return 'menos de 1 hora';
}

function getTimeTo(dateStr) {
    const date = new Date(dateStr);
    const now = new Date();
    const diff = date - now;
    const hours = Math.floor(diff / (1000 * 60 * 60));
    const days = Math.floor(hours / 24);
    
    if (days > 0) return `${days} día${days > 1 ? 's' : ''}`;
    if (hours > 0) return `${hours} hora${hours > 1 ? 's' : ''}`;
    return 'menos de 1 hora';
}

function formatBytes(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function showNotification(message, type) {
    // Usar sistema de notificaciones existente
    if (typeof showNotification === 'function') {
        showNotification(message, type);
    } else {
        alert(message);
    }
}
</script>

<?php include 'includes/footer.php'; ?>
