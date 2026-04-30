<?php
require_once __DIR__ . '/../../../backend/config/database.php';

// Verificar sesión de usuario
if (!isset($_SESSION['usuario'])) {
    require_once dirname(__DIR__, 3) . '/config/env.php';
    header('Location: ' . hotel_tame_url_path('login'));
    exit;
}

// Verificar rol permitido para backups
$allowed_roles = ['admin', 'Contador', 'Auxiliar Contable'];
if (!in_array($_SESSION['usuario']['rol'], $allowed_roles)) {
    header('Location: index.php?error=access_denied&module=backup_manager.php');
    exit;
}

// Cargar librerías
require_once 'lib/BackupSystem.php';

include __DIR__ . '/../../../backend/includes/header.php';
include __DIR__ . '/../../../backend/includes/sidebar.php';
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
    color: #6c757d;
}

.backup-status {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.backup-status.success {
    background-color: #d4edda;
    color: #155724;
}

.backup-status.error {
    background-color: #f8d7da;
    color: #721c24;
}

.backup-status.pending {
    background-color: #fff3cd;
    color: #856404;
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
</style>

<div class="main-content">
    <div id="notification-container"></div>
    
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-database me-2"></i>Gestión de Backups</h1>
                <p class="text-muted mb-0">Administra las copias de seguridad del sistema</p>
            </div>
            <div>
                <button type="button" class="btn btn-success" onclick="createBackup()">
                    <i class="fas fa-plus me-2"></i>Crear Backup
                </button>
                <button type="button" class="btn btn-outline-primary" onclick="scheduleBackup()">
                    <i class="fas fa-clock me-2"></i>Programar Backup
                </button>
            </div>
        </div>
    </div>

    <!-- Estadísticas de Backups -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="backup-card text-white p-4">
                <div class="d-flex align-items-center">
                    <div>
                        <h3 class="mb-0" id="totalBackups">0</h3>
                        <p class="mb-0">Total Backups</p>
                    </div>
                    <div class="ms-auto">
                        <i class="fas fa-archive fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="backup-card text-white p-4">
                <div class="d-flex align-items-center">
                    <div>
                        <h3 class="mb-0" id="lastBackup">-</h3>
                        <p class="mb-0">Último Backup</p>
                    </div>
                    <div class="ms-auto">
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="backup-card text-white p-4">
                <div class="d-flex align-items-center">
                    <div>
                        <h3 class="mb-0" id="totalSize">0 MB</h3>
                        <p class="mb-0">Espacio Usado</p>
                    </div>
                    <div class="ms-auto">
                        <i class="fas fa-hdd fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="backup-card text-white p-4">
                <div class="d-flex align-items-center">
                    <div>
                        <h3 class="mb-0" id="nextBackup">-</h3>
                        <p class="mb-0">Próximo Backup</p>
                    </div>
                    <div class="ms-auto">
                        <i class="fas fa-calendar fa-2x opacity-75"></i>
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
                    </h5>
                </div>
                <div class="card-body">
                    <div id="backupsList">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2 text-muted">Cargando historial de backups...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../backend/includes/footer.php'; ?>

<script>
// Sistema de gestión de backups
class BackupManager {
    constructor() {
        this.init();
    }

    init() {
        this.loadBackups();
        this.loadStats();
        this.setupEventListeners();
    }

    loadBackups() {
        fetch('api/endpoints/backup_manager.php?action=list')
            .then(response => response.json())
            .then(data => {
                this.renderBackups(data.backups || []);
            })
            .catch(error => {
                console.error('Error cargando backups:', error);
                this.showError('Error al cargar el historial de backups');
            });
    }

    loadStats() {
        fetch('api/endpoints/backup_manager.php?action=stats')
            .then(response => response.json())
            .then(data => {
                this.updateStats(data);
            })
            .catch(error => {
                console.error('Error cargando estadísticas:', error);
            });
    }

    renderBackups(backups) {
        const container = document.getElementById('backupsList');
        
        if (backups.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-archive fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay backups disponibles</h5>
                    <p class="text-muted">Crea tu primer backup para proteger los datos del sistema.</p>
                    <button class="btn btn-primary" onclick="createBackup()">
                        <i class="fas fa-plus me-2"></i>Crear Primer Backup
                    </button>
                </div>
            `;
            return;
        }

        const html = backups.map(backup => `
            <div class="backup-item">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-database me-3 text-primary"></i>
                            <div>
                                <h6 class="mb-1">${backup.filename}</h6>
                                <small class="backup-date">${backup.created_at}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <span class="backup-size">${backup.size}</span>
                    </div>
                    <div class="col-md-2">
                        <span class="backup-status ${backup.status}">${backup.status_text}</span>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-sm btn-outline-primary me-2" onclick="downloadBackup('${backup.id}')">
                            <i class="fas fa-download"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning me-2" onclick="restoreBackup('${backup.id}')">
                            <i class="fas fa-undo"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteBackup('${backup.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');

        container.innerHTML = html;
    }

    updateStats(stats) {
        document.getElementById('totalBackups').textContent = stats.total || 0;
        document.getElementById('lastBackup').textContent = stats.last_backup || '-';
        document.getElementById('totalSize').textContent = stats.total_size || '0 MB';
        document.getElementById('nextBackup').textContent = stats.next_backup || '-';
    }

    setupEventListeners() {
        // Auto-refresh cada 30 segundos
        setInterval(() => {
            this.loadStats();
        }, 30000);
    }

    showError(message) {
        const container = document.getElementById('notification-container');
        container.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    }
}

// Funciones globales
function createBackup() {
    if (confirm('¿Está seguro de crear un nuevo backup? Esta operación puede tardar varios minutos.')) {
        showNotification('Iniciando creación de backup...', 'info');
        
        fetch('api/endpoints/backup_manager.php?action=create', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Backup creado correctamente', 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showNotification('Error al crear backup: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error creando backup:', error);
                showNotification('Error al crear backup', 'error');
            });
    }
}

function downloadBackup(backupId) {
    window.open(`api/endpoints/backup_manager.php?action=download&id=${backupId}`, '_blank');
}

function restoreBackup(backupId) {
    if (confirm('¿Está seguro de restaurar este backup? Esta acción reemplazará todos los datos actuales y no se puede deshacer.')) {
        showNotification('Iniciando restauración de backup...', 'warning');
        
        fetch('api/endpoints/backup_manager.php?action=restore', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ backup_id: backupId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Backup restaurado correctamente', 'success');
                setTimeout(() => location.reload(), 3000);
            } else {
                showNotification('Error al restaurar backup: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error restaurando backup:', error);
            showNotification('Error al restaurar backup', 'error');
        });
    }
}

function deleteBackup(backupId) {
    if (confirm('¿Está seguro de eliminar este backup? Esta acción no se puede deshacer.')) {
        fetch('api/endpoints/backup_manager.php?action=delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ backup_id: backupId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Backup eliminado correctamente', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('Error al eliminar backup: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error eliminando backup:', error);
            showNotification('Error al eliminar backup', 'error');
        });
    }
}

function scheduleBackup() {
    // TODO: Implementar programación de backups
    showNotification('Función de programación en desarrollo', 'info');
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    new BackupManager();
});

function showNotification(message, type = 'info') {
    const container = document.getElementById('notification-container');
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';
    
    const icon = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-triangle',
        'warning': 'fa-exclamation-circle',
        'info': 'fa-info-circle'
    }[type] || 'fa-info-circle';
    
    container.innerHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${icon} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
}
</script>
