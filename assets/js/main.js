// Funciones globales para el sistema
const API_BASE = 'api/endpoints/';

// Función para mostrar notificaciones
function showNotification(message, type = 'success') {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const alert = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${icon} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('#notification-container').html(alert);
    
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 3000);
}

// Función para confirmar eliminación
function confirmDelete(id, type, callback) {
    if (confirm('¿Está seguro de eliminar este registro?')) {
        callback(id);
    }
}

// Formatear fecha
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Formatear moneda
function formatCurrency(amount) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP'
    }).format(amount);
}

// Limpiar formulario
function clearForm(formId) {
    document.getElementById(formId).reset();
}

// Validar formulario
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return false;
    }
    return true;
}

// Toggle sidebar en móvil
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
}
