<?php
require_once __DIR__ . '/../../../backend/config/database.php';
require_once __DIR__ . '/../../../lib/RoleBasedDashboard.php';

// Verificar sesión de usuario
if (!isset($_SESSION['usuario'])) {
    require_once dirname(__DIR__, 3) . '/config/env.php';
    header('Location: ' . hotel_tame_url_path('login'));
    exit;
}

$usuario_actual = $_SESSION['usuario'];

// Crear instancia del dashboard adaptativo
$dashboard = new RoleBasedDashboard($usuario_actual['rol'], $usuario_actual['id']);

include __DIR__ . '/../../../backend/includes/header.php';
include __DIR__ . '/../../../backend/includes/sidebar.php';
?>

<div class="main-content">
    <div id="notification-container"></div>
    
    <?php echo $dashboard->renderDashboard(); ?>
</div>

<?php include __DIR__ . '/../../../backend/includes/footer.php'; ?>

<script>
// Sistema de notificaciones en tiempo real
class NotificationSystem {
    constructor() {
        this.userId = <?php echo $usuario_actual['id']; ?>;
        this.userRole = '<?php echo $usuario_actual['rol']; ?>';
        this.debugMode = false; // Cambiar a true para depuración
        this.init();
    }
    
    init() {
        this.loadNotifications();
        this.setupRealTimeUpdates();
        
        // Depuración: verificar qué elementos de notificación existen
        if (this.debugMode) {
            const notificationElements = document.querySelectorAll('[data-notification-type]');
            console.log('Notification elements found:', notificationElements.length);
            notificationElements.forEach(el => {
                console.log('Element:', el.dataset.notificationType, el);
            });
        }
        this.setupNotificationHandlers();
    }
    
    loadNotifications() {
        const notifications = this.getNotificationsByRole();
        this.renderNotifications(notifications);
    }
    
    getNotificationsByRole() {
        const roleNotifications = {
            admin: ['system_alerts', 'security_issues', 'backup_status', 'user_activity'],
            gerente: ['occupancy_alerts', 'staff_issues', 'revenue_alerts', 'guest_feedback'],
            recepcionista: ['new_reservations', 'checkin_pending', 'guest_messages', 'payment_alerts'],
            contador: ['payment_processed', 'invoice_due', 'expense_reports', 'financial_alerts'],
            mantenimiento: ['work_orders', 'emergency_requests', 'equipment_status', 'maintenance_schedule'],
            limpieza: ['room_assignments', 'cleaning_schedule', 'quality_checks', 'supervisor_requests']
        };
        
        return roleNotifications[this.userRole] || ['general_notifications'];
    }
    
    renderNotifications(notifications) {
        const container = document.querySelector('.dashboard-notifications');
        if (!container) return;
        
        notifications.forEach(type => {
            this.updateNotificationCount(type);
        });
    }
    
    updateNotificationCount(type) {
        const element = document.querySelector(`[data-notification-type="${type}"]`);
        if (!element) return;
        
        const countElement = element.querySelector('small');
        const iconElement = element.querySelector('.notification-icon i');
        
        if (!countElement || !iconElement) return;
        
        fetch(`api/endpoints/notifications.php?type=${type}&user_id=${this.userId}`, {
            credentials: 'include' // Incluir cookies para sesión
        })
            .then(response => {
                if (!response.ok) {
                    console.warn(`Failed to load notifications for ${type}`);
                    return { count: 0 };
                }
                return response.json();
            })
            .then(data => {
                const count = data.count || 0;
                countElement.textContent = count > 0 ? `${count} nuevos` : '0 nuevos';
                
                if (count > 0) {
                    iconElement.className = 'fas fa-circle text-danger';
                    element.classList.add('notification-important');
                } else {
                    iconElement.className = 'fas fa-circle text-warning';
                    element.classList.remove('notification-important');
                }
            })
            .catch(error => {
                console.error(`Error loading notifications for ${type}:`, error);
                // Establecer valores por defecto en caso de error
                countElement.textContent = '0 nuevos';
                iconElement.className = 'fas fa-circle text-warning';
                element.classList.remove('notification-important');
            });
    }
    
    setupRealTimeUpdates() {
        // Simular actualizaciones en tiempo real con WebSocket o polling
        setInterval(() => {
            this.loadNotifications();
        }, 30000); // Actualizar cada 30 segundos
    }
    
    setupNotificationHandlers() {
        // Manejar clics en notificaciones
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', () => {
                const type = item.dataset.notificationType;
                this.handleNotificationClick(type);
            });
        });
    }
    
    handleNotificationClick(type) {
        // Redirigir a la página correspondiente según el tipo de notificación
        const routes = {
            new_reservations: 'reservas.php',
            checkin_pending: 'reservas.php?filter=checkin',
            guest_messages: 'messages.php',
            payment_alerts: 'payments.php',
            work_orders: 'mantenimiento.php',
            emergency_requests: 'mantenimiento.php?filter=emergency',
            room_assignments: 'limpieza.php',
            cleaning_schedule: 'limpieza.php?filter=schedule',
            system_alerts: 'admin.php?filter=alerts',
            user_activity: 'usuarios.php?filter=activity'
        };
        
        const route = routes[type] || '#';
        window.location.href = route;
    }
}

// Sistema de widgets dinámicos
class WidgetManager {
    constructor() {
        this.widgets = new Map();
        this.init();
    }
    
    init() {
        this.loadAllWidgets();
        this.setupWidgetRefresh();
    }
    
    loadAllWidgets() {
        document.querySelectorAll('.widget-content').forEach(widget => {
            const widgetId = widget.closest('.widget-card').dataset.widgetId;
            const widgetType = widget.dataset.widgetType;
            
            this.loadWidget(widgetId, widgetType, widget);
        });
    }
    
    loadWidget(widgetId, widgetType, container) {
        // Mostrar loading
        container.innerHTML = `
            <div class="widget-loading">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                Cargando ${widgetType}...
            </div>
        `;
        
        // Cargar datos del widget
        fetch(`api/endpoints/widgets.php?id=${widgetId}&type=${widgetType}`, {
            credentials: 'include' // Incluir cookies para sesión
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                this.renderWidget(widgetType, data, container);
                this.widgets.set(widgetId, { type: widgetType, data: data });
            })
            .catch(error => {
                console.error('Error loading widget:', error);
                container.innerHTML = `
                    <div class="widget-error">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error al cargar ${widgetType}: ${error.message}
                    </div>
                `;
            });
    }
    
    renderWidget(type, data, container) {
        const renderers = {
            status: this.renderStatusWidget,
            chart: this.renderChartWidget,
            metric: this.renderMetricWidget,
            list: this.renderListWidget,
            table: this.renderTableWidget,
            cards: this.renderCardsWidget,
            grid: this.renderGridWidget,
            alerts: this.renderAlertsWidget,
            kanban: this.renderKanbanWidget,
            calendar: this.renderCalendarWidget,
            progress: this.renderProgressWidget,
            chat: this.renderChatWidget,
            actions: this.renderActionsWidget,
            welcome: this.renderWelcomeWidget
        };
        
        const renderer = renderers[type];
        if (renderer) {
            container.innerHTML = renderer.call(this, data);
        } else {
            container.innerHTML = `<div class="widget-unknown">Tipo de widget desconocido: ${type}</div>`;
        }
    }
    
    renderStatusWidget(data) {
        return `
            <div class="status-widget">
                ${Object.entries(data).map(([key, value]) => `
                    <div class="status-item">
                        <span class="status-label">${this.formatLabel(key)}:</span>
                        <span class="status-value ${this.getStatusClass(value)}">${value}</span>
                    </div>
                `).join('')}
            </div>
        `;
    }
    
    renderMetricWidget(data) {
        return `
            <div class="metric-widget">
                ${Object.entries(data).map(([key, value]) => `
                    <div class="metric-item">
                        <div class="metric-value">${value}</div>
                        <div class="metric-label">${this.formatLabel(key)}</div>
                    </div>
                `).join('')}
            </div>
        `;
    }
    
    renderListWidget(data) {
        if (!data || data.length === 0) {
            return '<div class="text-muted">No hay datos disponibles</div>';
        }
        
        return `
            <div class="list-widget">
                ${data.map(item => `
                    <div class="list-item">
                        <div class="list-item-content">
                            <div class="list-item-title">${item.title || item.name}</div>
                            <div class="list-item-subtitle">${item.subtitle || item.description}</div>
                        </div>
                        <div class="list-item-meta">
                            <span class="badge bg-${item.badge || 'secondary'}">${item.status || 'Activo'}</span>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }
    
    renderCardsWidget(data) {
        if (!data || data.length === 0) {
            return '<div class="text-muted">No hay elementos disponibles</div>';
        }
        
        return `
            <div class="cards-widget">
                <div class="row">
                    ${data.map(item => `
                        <div class="col-md-6 mb-3">
                            <div class="card card-body">
                                <h6 class="card-title">${item.title || item.name}</h6>
                                <p class="card-text">${item.description || item.content}</p>
                                ${item.action ? `<a href="${item.action}" class="btn btn-sm btn-primary">Ver más</a>` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    renderProgressWidget(data) {
        return `
            <div class="progress-widget">
                <div class="progress-item">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Progreso Actual</span>
                        <span>${data.current}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" style="width: ${data.current}%"></div>
                    </div>
                    <small class="text-muted">Objetivo: ${data.target}%</small>
                </div>
            </div>
        `;
    }
    
    renderAlertsWidget(data) {
        if (!data || data.length === 0) {
            return '<div class="text-muted">No hay alertas</div>';
        }
        
        return `
            <div class="alerts-widget">
                ${data.map(alert => `
                    <div class="alert alert-${alert.type || 'warning'} alert-dismissible fade show" role="alert">
                        <i class="fas fa-${alert.icon || 'exclamation-triangle'} me-2"></i>
                        ${alert.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `).join('')}
            </div>
        `;
    }
    
    renderActionsWidget(data) {
        return `
            <div class="actions-widget">
                <div class="row">
                    ${data.map(action => `
                        <div class="col-md-6 mb-2">
                            <a href="${action.action}" class="btn btn-outline-primary w-100">
                                <i class="${action.icon} me-2"></i>
                                ${action.text}
                            </a>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    renderWelcomeWidget(data) {
        return `
            <div class="welcome-widget text-center">
                <i class="fas fa-home fa-3x text-primary mb-3"></i>
                <h4>${data.message}</h4>
                <p class="text-muted">Explora las funciones disponibles en el menú lateral</p>
            </div>
        `;
    }
    
    // Renderers adicionales para otros tipos de widgets...
    
    formatLabel(key) {
        return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }
    
    getStatusClass(value) {
        const statusMap = {
            'Online': 'text-success',
            'Offline': 'text-danger',
            'Actualizado': 'text-success',
            'Pendiente': 'text-warning',
            'Error': 'text-danger'
        };
        
        return statusMap[value] || 'text-secondary';
    }
    
    setupWidgetRefresh() {
        // Actualizar widgets automáticamente
        setInterval(() => {
            this.widgets.forEach((widget, widgetId) => {
                const container = document.querySelector(`[data-widget-id="${widgetId}"] .widget-content`);
                if (container) {
                    this.loadWidget(widgetId, widget.type, container);
                }
            });
        }, 60000); // Actualizar cada minuto
    }
}

// Función segura para actualizar textContent
function safeSetTextContent(selector, text) {
    try {
        const element = document.querySelector(selector);
        if (element) {
            element.textContent = text;
            return true;
        }
        return false;
    } catch (error) {
        console.warn('Error al actualizar elemento:', selector, error.message);
        return false;
    }
}

// Función segura para actualizar className
function safeSetClassName(selector, className) {
    try {
        const element = document.querySelector(selector);
        if (element) {
            element.className = className;
            return true;
        }
        return false;
    } catch (error) {
        console.warn('Error al actualizar className:', selector, error.message);
        return false;
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Agregar manejador de errores global para capturar TypeError
    window.addEventListener('error', function(event) {
        if (event.error instanceof TypeError && event.error.message.includes('textContent')) {
            console.warn('TypeError capturado y manejado:', event.error.message);
            event.preventDefault(); // Prevenir que el error se muestre en consola
            return true;
        }
    });
    
    // Esperar un poco más para asegurar que el dashboard esté completamente renderizado
    setTimeout(() => {
        new NotificationSystem();
        new WidgetManager();
        setupResponsiveDashboard();
    }, 100);
});

function setupResponsiveDashboard() {
    // Ajustar layout para dispositivos móviles
    if (window.innerWidth < 768) {
        document.querySelectorAll('.widget-card').forEach(card => {
            card.classList.remove('col-md-6', 'col-md-4', 'col-md-8', 'col-md-12');
            card.classList.add('col-12');
        });
    }
    
    // Manejar redimensionamiento
    window.addEventListener('resize', () => {
        if (window.innerWidth < 768) {
            document.querySelectorAll('.widget-card').forEach(card => {
                card.classList.remove('col-md-6', 'col-md-4', 'col-md-8', 'col-md-12');
                card.classList.add('col-12');
            });
        }
    });
}

// Sistema de atajos de teclado
document.addEventListener('keydown', function(e) {
    // Ctrl + N: Nueva reserva
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        window.location.href = 'reservas.php?action=new';
    }
    
    // Ctrl + U: Usuarios (solo admin)
    if (e.ctrlKey && e.key === 'u' && '<?php echo $usuario_actual['rol']; ?>' === 'admin') {
        e.preventDefault();
        window.location.href = 'usuarios.php';
    }
    
    // Ctrl + R: Reportes
    if (e.ctrlKey && e.key === 'r') {
        e.preventDefault();
        window.location.href = 'reportes.php';
    }
});
</script>

<style>
/* Estilos mejorados para el dashboard */
.widget-loading {
    text-align: center;
    padding: 20px;
    color: #6c757d;
}

.widget-error {
    text-align: center;
    padding: 20px;
    color: #dc3545;
}

.status-widget {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.status-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
}

.status-item:last-child {
    border-bottom: none;
}

.status-label {
    font-weight: 500;
    color: #495057;
}

.status-value {
    font-weight: 600;
}

.metric-widget {
    display: flex;
    justify-content: space-around;
    text-align: center;
}

.metric-item {
    flex: 1;
}

.metric-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
}

.metric-label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 5px;
}

.list-widget {
    max-height: 300px;
    overflow-y: auto;
}

.list-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #e9ecef;
}

.list-item:last-child {
    border-bottom: none;
}

.list-item-title {
    font-weight: 500;
    color: #2c3e50;
}

.list-item-subtitle {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 2px;
}

.notification-important {
    background-color: #fff3cd;
    border-color: #ffeaa7;
}

.notification-important:hover {
    background-color: #ffeaa7;
}

/* Estilos responsivos */
@media (max-width: 768px) {
    .dashboard-header .row {
        flex-direction: column;
        gap: 15px;
    }
    
    .dashboard-header .text-end {
        text-align: left !important;
    }
    
    .btn-group {
        width: 100%;
    }
    
    .btn-group .btn {
        flex: 1;
    }
    
    .metric-widget {
        flex-direction: column;
        gap: 15px;
    }
    
    .metric-item {
        padding: 10px;
        border: 1px solid #e9ecef;
        border-radius: 5px;
    }
}

/* Animaciones */
.widget-card {
    transition: all 0.3s ease;
}

.widget-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.notification-item {
    transition: all 0.2s ease;
    cursor: pointer;
}

.notification-item:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}

/* Indicadores de carga */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Estados vacíos */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.5;
}
</style>
