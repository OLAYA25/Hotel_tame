<!-- Contenedor de Notificaciones -->
<div class="notification-system">
    <!-- Botón de Notificaciones -->
    <div class="notification-dropdown">
        <button class="btn btn-outline-primary position-relative" id="notification-btn">
            <i class="fas fa-bell"></i>
            <?php if ($unread_count > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notification-badge">
                    <?php echo $unread_count > 99 ? '99+' : $unread_count; ?>
                </span>
            <?php endif; ?>
        </button>
        
        <!-- Dropdown de Notificaciones -->
        <div class="notification-menu" id="notification-menu">
            <div class="notification-header">
                <h6 class="mb-0">Notificaciones</h6>
                <div class="notification-actions">
                    <button class="btn btn-sm btn-link" onclick="markAllNotificationsRead()">
                        <i class="fas fa-check-double"></i> Marcar todas leídas
                    </button>
                    <button class="btn btn-sm btn-link" onclick="clearNotifications()">
                        <i class="fas fa-trash"></i> Limpiar
                    </button>
                </div>
            </div>
            
            <div class="notification-list" id="notification-list">
                <?php if (empty($notifications)): ?>
                    <div class="notification-empty">
                        <i class="fas fa-bell-slash fa-2x mb-2"></i>
                        <p class="mb-0">No tienes notificaciones</p>
                    </div>
                <?php else: ?>
                    <?php 
                    $displayed_notifications = array_slice($notifications, -10); // Últimas 10
                    foreach (array_reverse($displayed_notifications) as $notification): 
                    ?>
                        <div class="notification-item <?php echo $notification['read'] ? 'read' : 'unread'; ?>" 
                             data-notification-id="<?php echo $notification['id']; ?>"
                             onclick="markNotificationRead('<?php echo $notification['id']; ?>')">
                            <div class="notification-icon">
                                <?php 
                                $icons = [
                                    'success' => '<i class="fas fa-check"></i>',
                                    'error' => '<i class="fas fa-exclamation"></i>',
                                    'warning' => '<i class="fas fa-exclamation-triangle"></i>',
                                    'info' => '<i class="fas fa-info"></i>'
                                ];
                                echo '<div class="notification-icon ' . $notification['type'] . '">' . ($icons[$notification['type']] ?? $icons['info']) . '</div>';
                                ?>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></div>
                                <div class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></div>
                                <div class="notification-time">
                                    <?php 
                                    $time = time() - $notification['timestamp'];
                                    if ($time < 60) {
                                        echo 'Ahora';
                                    } elseif ($time < 3600) {
                                        echo floor($time / 60) . ' min';
                                    } elseif ($time < 86400) {
                                        echo floor($time / 3600) . ' h';
                                    } else {
                                        echo date('d M', $notification['timestamp']);
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="notification-actions">
                                <?php if (!$notification['read']): ?>
                                    <span class="notification-dot"></span>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-link" onclick="event.stopPropagation(); removeNotification('<?php echo $notification['id']; ?>')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="notification-footer">
                <a href="test_notifications.php" class="btn btn-link">Ver todas las notificaciones</a>
            </div>
        </div>
    </div>
</div>

<!-- Contenedor de Notificaciones Flotantes -->
<div id="floating-notifications" class="floating-notifications"></div>

<style>
.notification-system {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1050;
}

.notification-dropdown {
    position: relative;
}

.notification-menu {
    position: absolute;
    top: 100%;
    right: 0;
    width: 400px;
    max-height: 500px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    border: 1px solid rgba(0,0,0,0.1);
    display: none;
    overflow: hidden;
    margin-top: 10px;
}

.notification-menu.show {
    display: block;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
}

.notification-actions {
    display: flex;
    gap: 10px;
}

.notification-list {
    max-height: 350px;
    overflow-y: auto;
}

.notification-item {
    display: flex;
    align-items: flex-start;
    padding: 15px 20px;
    border-bottom: 1px solid #f1f3f4;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
}

.notification-item:hover {
    background: #f8f9fa;
}

.notification-item.unread {
    background: #f0f7ff;
    border-left: 4px solid #007bff;
}

.notification-item.read {
    opacity: 0.7;
}

.notification-icon {
    margin-right: 15px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.notification-icon.success {
    background: #d4edda;
    color: #155724;
}

.notification-icon.error {
    background: #f8d7da;
    color: #721c24;
}

.notification-icon.warning {
    background: #fff3cd;
    color: #856404;
}

.notification-icon.info {
    background: #d1ecf1;
    color: #0c5460;
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-weight: 600;
    margin-bottom: 5px;
    color: #333;
}

.notification-message {
    font-size: 0.9rem;
    color: #666;
    line-height: 1.4;
    margin-bottom: 5px;
}

.notification-time {
    font-size: 0.8rem;
    color: #999;
}

.notification-dot {
    width: 8px;
    height: 8px;
    background: #007bff;
    border-radius: 50%;
    margin-right: 10px;
}

.notification-empty {
    text-align: center;
    padding: 40px 20px;
    color: #999;
}

.notification-footer {
    padding: 15px 20px;
    border-top: 1px solid #e9ecef;
    text-align: center;
}

.floating-notifications {
    position: fixed;
    top: 80px;
    right: 20px;
    z-index: 1051;
    max-width: 400px;
}

.floating-notification {
    background: white;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    border-left: 4px solid #007bff;
    animation: slideInRight 0.3s ease;
    position: relative;
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.floating-notification.success {
    border-left-color: #28a745;
}

.floating-notification.error {
    border-left-color: #dc3545;
}

.floating-notification.warning {
    border-left-color: #ffc107;
}

.floating-notification.info {
    border-left-color: #17a2b8;
}

.floating-notification.closing {
    animation: slideOutRight 0.3s ease;
}

@keyframes slideOutRight {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(100%);
    }
}

.floating-notification-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.floating-notification-title {
    font-weight: 600;
    color: #333;
}

.floating-notification-close {
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    padding: 0;
    font-size: 1.2rem;
}

.floating-notification-close:hover {
    color: #333;
}

.floating-notification-message {
    color: #666;
    line-height: 1.4;
}

/* Responsive */
@media (max-width: 768px) {
    .notification-system {
        top: 10px;
        right: 10px;
    }
    
    .notification-menu {
        width: 350px;
        right: -50px;
    }
    
    .floating-notifications {
        right: 10px;
        left: 10px;
        max-width: none;
    }
}
</style>

<script>
let notificationManager = {
    notifications: <?php echo json_encode($notifications); ?>,
    unreadCount: <?php echo $unread_count; ?>,
    
    init() {
        this.setupEventListeners();
        this.updateBadge();
        this.checkNewNotifications();
        
        // Verificar nuevas notificaciones cada 30 segundos
        setInterval(() => this.checkNewNotifications(), 30000);
    },
    
    setupEventListeners() {
        // Toggle dropdown
        const btn = document.getElementById('notification-btn');
        if (btn) {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleDropdown();
            });
        }
        
        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.notification-dropdown')) {
                this.closeDropdown();
            }
        });
    },
    
    toggleDropdown() {
        const menu = document.getElementById('notification-menu');
        if (menu) {
            menu.classList.toggle('show');
        }
    },
    
    closeDropdown() {
        const menu = document.getElementById('notification-menu');
        if (menu) {
            menu.classList.remove('show');
        }
    },
    
    updateBadge() {
        const badge = document.getElementById('notification-badge');
        if (badge) {
            if (this.unreadCount > 0) {
                badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    },
    
    showFloatingNotification(type, title, message, duration = 5000) {
        const container = document.getElementById('floating-notifications');
        if (!container) return;
        
        const notification = document.createElement('div');
        notification.className = `floating-notification ${type}`;
        
        notification.innerHTML = `
            <div class="floating-notification-header">
                <div class="floating-notification-title">${title}</div>
                <button class="floating-notification-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="floating-notification-message">${message}</div>
        `;
        
        container.appendChild(notification);
        
        // Auto cerrar
        setTimeout(() => {
            notification.classList.add('closing');
            setTimeout(() => notification.remove(), 300);
        }, duration);
    },
    
    checkNewNotifications() {
        // Verificar nuevas notificaciones vía AJAX si el endpoint existe
        fetch('api/endpoints/notifications.php?accion=verificar_nuevas')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.new_notifications.length > 0) {
                data.new_notifications.forEach(notif => {
                    this.showFloatingNotification(notif.type, notif.title, notif.message);
                    this.unreadCount++;
                });
                this.updateBadge();
            }
        })
        .catch(error => {
            // Silenciosamente ignorar errores de red
            console.log('Sistema de notificaciones: sin conexión');
        });
    }
};

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    notificationManager.init();
});

// Funciones globales para compatibilidad
function markNotificationRead(id) {
    fetch('api/endpoints/notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            accion: 'marcar_leida',
            notification_id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = document.querySelector(`[data-notification-id="${id}"]`);
            if (item) {
                item.classList.remove('unread');
                item.classList.add('read');
                const dot = item.querySelector('.notification-dot');
                if (dot) dot.remove();
            }
            
            notificationManager.unreadCount = Math.max(0, notificationManager.unreadCount - 1);
            notificationManager.updateBadge();
        }
    })
    .catch(error => console.log('Error marcando notificación como leída'));
}

function markAllNotificationsRead() {
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
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
                item.classList.add('read');
                const dot = item.querySelector('.notification-dot');
                if (dot) dot.remove();
            });
            
            notificationManager.unreadCount = 0;
            notificationManager.updateBadge();
        }
    })
    .catch(error => console.log('Error marcando todas como leídas'));
}

function clearNotifications() {
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
                location.reload();
            }
        })
        .catch(error => console.log('Error limpiando notificaciones'));
    }
}

function removeNotification(id) {
    fetch('api/endpoints/notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            accion: 'eliminar',
            notification_id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = document.querySelector(`[data-notification-id="${id}"]`);
            if (item) item.remove();
        }
    })
    .catch(error => console.log('Error eliminando notificación'));
}
</script>
