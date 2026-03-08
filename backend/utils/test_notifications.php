<?php
require_once __DIR__ . '/../../config/database.php';

// Verificar sesión de usuario
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Incluir middleware de autenticación y permisos
require_once 'includes/auth_middleware.php';

// Cargar sistema de notificaciones
require_once 'lib/NotificationManager.php';

$notificationManager = NotificationManager::getInstance();
$notificationManager->loadFromSession();

include __DIR__ . '/../../includes/header.php';
?>

<style>
.test-section {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}

.notification-demo {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.demo-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.demo-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.demo-card.success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.demo-card.warning {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
}

.demo-card.error {
    background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
}

.demo-card.info {
    background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    text-align: center;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #667eea;
}

.stat-label {
    color: #6c757d;
    margin-top: 5px;
}
</style>

<div class="container-fluid">
    <div class="page-header">
        <h1>Sistema de Notificaciones</h1>
        <p class="text-muted">Prueba y visualización del sistema de notificaciones en tiempo real</p>
    </div>

    <!-- Estadísticas de Notificaciones -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number" id="total-notifications"><?php echo count($notificationManager->getAllNotifications()); ?></div>
            <div class="stat-label">Total Notificaciones</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="unread-notifications"><?php echo $notificationManager->getUnreadCount(); ?></div>
            <div class="stat-label">No Leídas</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="persistent-notifications">
                <?php 
                $persistent = array_filter($notificationManager->getAllNotifications(), function($n) { return $n['persistent']; });
                echo count($persistent);
                ?>
            </div>
            <div class="stat-label">Persistentes</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="temporal-notifications">
                <?php 
                $temporal = array_filter($notificationManager->getAllNotifications(), function($n) { return !$n['persistent']; });
                echo count($temporal);
                ?>
            </div>
            <div class="stat-label">Temporales</div>
        </div>
    </div>

    <!-- Generar Notificaciones de Prueba -->
    <div class="test-section">
        <h5><i class="fas fa-flask me-2"></i>Generar Notificaciones de Prueba</h5>
        <div class="notification-demo">
            <div class="demo-card success" onclick="createTestNotification('success')">
                <i class="fas fa-check fa-2x mb-2"></i>
                <h6>Éxito</h6>
                <small>Notificación de éxito</small>
            </div>
            <div class="demo-card warning" onclick="createTestNotification('warning')">
                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                <h6>Advertencia</h6>
                <small>Notificación de advertencia</small>
            </div>
            <div class="demo-card error" onclick="createTestNotification('error')">
                <i class="fas fa-times fa-2x mb-2"></i>
                <h6>Error</h6>
                <small>Notificación de error</small>
            </div>
            <div class="demo-card info" onclick="createTestNotification('info')">
                <i class="fas fa-info fa-2x mb-2"></i>
                <h6>Información</h6>
                <small>Notificación informativa</small>
            </div>
        </div>
    </div>

    <!-- Acciones del Sistema -->
    <div class="test-section">
        <h5><i class="fas fa-cog me-2"></i>Acciones del Sistema</h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <button class="btn btn-primary w-100" onclick="generateSystemNotifications()">
                    <i class="fas fa-sync me-2"></i>Generar Notificaciones del Sistema
                </button>
            </div>
            <div class="col-md-6 mb-3">
                <button class="btn btn-success w-100" onclick="markAllAsRead()">
                    <i class="fas fa-check-double me-2"></i>Marcar Todas como Leídas
                </button>
            </div>
            <div class="col-md-6 mb-3">
                <button class="btn btn-warning w-100" onclick="clearTemporaryNotifications()">
                    <i class="fas fa-broom me-2"></i>Limpiar Temporales
                </button>
            </div>
            <div class="col-md-6 mb-3">
                <button class="btn btn-danger w-100" onclick="clearAllNotifications()">
                    <i class="fas fa-trash me-2"></i>Limpiar Todas
                </button>
            </div>
        </div>
    </div>

    <!-- Lista de Notificaciones Actuales -->
    <div class="test-section">
        <h5><i class="fas fa-list me-2"></i>Notificaciones Actuales</h5>
        <div id="current-notifications">
            <?php 
            $notifications = $notificationManager->getAllNotifications();
            if (empty($notifications)): ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-bell-slash fa-3x mb-3"></i>
                    <h5>No hay notificaciones</h5>
                    <p>Genera algunas notificaciones de prueba para ver cómo funciona el sistema</p>
                </div>
            <?php else: ?>
                <?php foreach (array_reverse($notifications) as $notification): ?>
                    <div class="alert alert-<?php echo $notification['type'] === 'error' ? 'danger' : $notification['type']; ?> d-flex align-items-center">
                        <div class="me-3">
                            <?php 
                            $icons = [
                                'success' => 'fa-check-circle',
                                'error' => 'fa-exclamation-circle',
                                'warning' => 'fa-exclamation-triangle',
                                'info' => 'fa-info-circle'
                            ];
                            ?>
                            <i class="fas <?php echo $icons[$notification['type']] ?? 'fa-info-circle'; ?> fa-2x"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1"><?php echo htmlspecialchars($notification['title']); ?></h6>
                            <p class="mb-1"><?php echo htmlspecialchars($notification['message']); ?></p>
                            <small class="text-muted">
                                <?php echo date('d/m/Y H:i:s', $notification['timestamp']); ?>
                                <?php if ($notification['persistent']): ?>
                                    <span class="badge bg-primary ms-2">Persistente</span>
                                <?php endif; ?>
                                <?php if (!$notification['read']): ?>
                                    <span class="badge bg-success ms-2">No leída</span>
                                <?php endif; ?>
                            </small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary" onclick="markAsRead('<?php echo $notification['id']; ?>')">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
let notificationCount = <?php echo $notificationManager->getUnreadCount(); ?>;

function createTestNotification(type) {
    const messages = {
        success: {
            title: 'Operación Exitosa',
            message: 'La prueba de notificación de éxito se completó correctamente.'
        },
        warning: {
            title: 'Advertencia del Sistema',
            message: 'Esta es una notificación de advertencia de prueba.'
        },
        error: {
            title: 'Error Detectado',
            message: 'Se ha producido un error en el sistema (prueba).'
        },
        info: {
            title: 'Información Importante',
            message: 'Esta es una notificación informativa de prueba.'
        }
    };
    
    const msg = messages[type];
    
    fetch('api/endpoints/notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            accion: 'crear',
            tipo: type,
            titulo: msg.title,
            mensaje: msg.message,
            persistente: false
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar notificación flotante
            if (window.notificationManager) {
                notificationManager.showFloatingNotification(type, msg.title, msg.message);
            }
            
            // Actualizar contador
            notificationCount++;
            updateNotificationBadge();
            
            // Recargar página después de un momento
            setTimeout(() => {
                location.reload();
            }, 1000);
        }
    })
    .catch(error => {
        console.error('Error creando notificación:', error);
        alert('Error al crear notificación de prueba');
    });
}

function generateSystemNotifications() {
    fetch('api/endpoints/notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            accion: 'generar_sistema'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Notificaciones del sistema generadas');
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error generando notificaciones:', error);
        alert('Error al generar notificaciones del sistema');
    });
}

function markAllAsRead() {
    fetch('api/endpoints/notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            accion: 'marcar_todas_leidas'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            notificationCount = 0;
            updateNotificationBadge();
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error marcando todas como leídas:', error);
        alert('Error al marcar todas como leídas');
    });
}

function clearTemporaryNotifications() {
    if (confirm('¿Estás seguro de limpiar todas las notificaciones temporales?')) {
        location.reload();
    }
}

function clearAllNotifications() {
    if (confirm('¿Estás seguro de limpiar todas las notificaciones?')) {
        fetch('api/endpoints/notifications.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                accion: 'limpiar'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                notificationCount = 0;
                updateNotificationBadge();
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error limpiando notificaciones:', error);
            alert('Error al limpiar notificaciones');
        });
    }
}

function markAsRead(notificationId) {
    fetch('api/endpoints/notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            accion: 'marcar_leida',
            notification_id: notificationId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (notificationCount > 0) {
                notificationCount--;
                updateNotificationBadge();
            }
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error marcando como leída:', error);
        alert('Error al marcar notificación como leída');
    });
}

function updateNotificationBadge() {
    const badge = document.getElementById('notification-badge');
    if (badge) {
        if (notificationCount > 0) {
            badge.textContent = notificationCount > 99 ? '99+' : notificationCount;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }
}

// Actualizar estadísticas cada 5 segundos
setInterval(() => {
    fetch('api/endpoints/notifications.php?accion=contador')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('total-notifications').textContent = data.unread_count + (parseInt(document.getElementById('total-notifications').textContent) - data.unread_count);
            document.getElementById('unread-notifications').textContent = data.unread_count;
            notificationCount = data.unread_count;
            updateNotificationBadge();
        }
    });
}, 5000);
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
