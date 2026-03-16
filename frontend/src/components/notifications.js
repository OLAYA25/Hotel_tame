// Sistema centralizado de notificaciones para toda la aplicación
class NotificationSystem {
    static show(message, type = 'info', duration = 5000) {
        // Asegurar que el mensaje sea un string
        const messageStr = typeof message === 'object' ? JSON.stringify(message) : String(message);
        
        // Crear contenedor si no existe
        if (!document.getElementById('notification-container')) {
            const container = document.createElement('div');
            container.id = 'notification-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                max-width: 400px;
            `;
            document.body.appendChild(container);
        }
        
        const container = document.getElementById('notification-container');
        
        // Definir clases según el tipo
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';
        
        // Crear notificación
        const notification = document.createElement('div');
        notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
        notification.style.cssText = `
            margin-bottom: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            animation: slideInRight 0.3s ease-out;
        `;
        notification.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="flex: 1;">
                    ${messageStr}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" style="margin-left: 10px;"></button>
            </div>
        `;
        
        // Agregar al contenedor
        container.appendChild(notification);
        
        // Auto-eliminar después del tiempo especificado
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.animation = 'slideOutRight 0.3s ease-in';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }
        }, duration);
        
        // Manejar cierre manual
        const closeBtn = notification.querySelector('.btn-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                notification.style.animation = 'slideOutRight 0.3s ease-in';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            });
        }
    }
    
    static success(message, duration = 5000) {
        this.show(message, 'success', duration);
    }
    
    static error(message, duration = 7000) {
        this.show(message, 'error', duration);
    }
    
    static warning(message, duration = 6000) {
        this.show(message, 'warning', duration);
    }
    
    static info(message, duration = 5000) {
        this.show(message, 'info', duration);
    }
}

// Función global para compatibilidad con código existente
function showNotification(message, type = 'info', duration = 5000) {
    NotificationSystem.show(message, type, duration);
}

// Agregar animaciones CSS si no existen
if (!document.getElementById('notification-styles')) {
    const style = document.createElement('style');
    style.id = 'notification-styles';
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}
