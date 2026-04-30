<?php
require_once __DIR__ . '/../../../backend/config/database.php';

// Verificar sesión de usuario
if (!isset($_SESSION['usuario'])) {
    require_once dirname(__DIR__, 3) . '/config/env.php';
    header('Location: ' . hotel_tame_url_path('login'));
    exit;
}

// Definir título y descripción de la página
$pageTitle = 'Configuración - Hotel Management System';
$pageDescription = 'Configuración general del sistema';

// Solo admin puede acceder a configuración
if ($_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: index.php?error=access_denied&module=settings.php');
    exit;
}

// Incluir middleware de autenticación y permisos
require_once __DIR__ . '/../../../backend/includes/auth_middleware.php';

// Obtener configuración actual del sistema
$settings = [
    'hotel_name' => 'Hotel Tame',
    'hotel_address' => 'Calle Principal #123',
    'hotel_phone' => '+57 1 234 5678',
    'hotel_email' => 'info@hoteltame.com',
    'currency' => 'COP',
    'timezone' => 'America/Bogota',
    'language' => 'es',
    'checkin_time' => '15:00',
    'checkout_time' => '12:00',
    'max_guests_per_room' => 4,
    'auto_backup' => true,
    'backup_frequency' => 'daily',
    'notification_email' => true,
    'maintenance_mode' => false
];

// Procesar formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Actualizar configuración
        foreach ($settings as $key => $value) {
            if (isset($_POST[$key])) {
                $settings[$key] = is_bool($value) ? (bool)$_POST[$key] : $_POST[$key];
            }
        }
        
        // Guardar en base de datos (tabla de configuración)
        $database = new Database();
        $db = $database->getConnection();
        
        // Crear tabla de configuración si no existe
        $db->exec("CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE,
            setting_value TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Actualizar cada configuración
        foreach ($settings as $key => $value) {
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) 
                                VALUES (:key, :value) 
                                ON DUPLICATE KEY UPDATE setting_value = :value");
            $stmt->bindParam(':key', $key);
            $stmt->bindParam(':value', $value);
            $stmt->execute();
        }
        
        $success_message = "Configuración actualizada correctamente";
    } catch (Exception $e) {
        $error_message = "Error al actualizar configuración: " . $e->getMessage();
    }
}

include __DIR__ . '/../../../backend/includes/header.php';
include __DIR__ . '/../../../backend/includes/sidebar.php';
?>

<div class="main-content">
    <div id="notification-container"></div>
    
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-cog me-2"></i>Configuración del Sistema</h1>
                <p class="text-muted mb-0">Gestiona la configuración general del hotel</p>
            </div>
            <div>
                <button type="button" class="btn btn-outline-secondary" onclick="exportSettings()">
                    <i class="fas fa-download me-2"></i>Exportar Configuración
                </button>
                <button type="button" class="btn btn-outline-primary" onclick="importSettings()">
                    <i class="fas fa-upload me-2"></i>Importar Configuración
                </button>
            </div>
        </div>
    </div>

    <!-- Mensajes de éxito/error -->
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" id="settingsForm">
        <div class="row">
            <!-- Configuración General -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-hotel me-2"></i>Información del Hotel
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="hotel_name" class="form-label">Nombre del Hotel</label>
                            <input type="text" class="form-control" id="hotel_name" name="hotel_name" 
                                   value="<?php echo htmlspecialchars($settings['hotel_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="hotel_address" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="hotel_address" name="hotel_address" 
                                   value="<?php echo htmlspecialchars($settings['hotel_address']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="hotel_phone" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="hotel_phone" name="hotel_phone" 
                                   value="<?php echo htmlspecialchars($settings['hotel_phone']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="hotel_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="hotel_email" name="hotel_email" 
                                   value="<?php echo htmlspecialchars($settings['hotel_email']); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuración Regional -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-globe me-2"></i>Configuración Regional
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="currency" class="form-label">Moneda</label>
                            <select class="form-select" id="currency" name="currency">
                                <option value="COP" <?php echo $settings['currency'] === 'COP' ? 'selected' : ''; ?>>COP - Peso Colombiano</option>
                                <option value="USD" <?php echo $settings['currency'] === 'USD' ? 'selected' : ''; ?>>USD - Dólar Americano</option>
                                <option value="EUR" <?php echo $settings['currency'] === 'EUR' ? 'selected' : ''; ?>>EUR - Euro</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="timezone" class="form-label">Zona Horaria</label>
                            <select class="form-select" id="timezone" name="timezone">
                                <option value="America/Bogota" <?php echo $settings['timezone'] === 'America/Bogota' ? 'selected' : ''; ?>>America/Bogota</option>
                                <option value="America/Mexico_City" <?php echo $settings['timezone'] === 'America/Mexico_City' ? 'selected' : ''; ?>>America/Mexico_City</option>
                                <option value="America/Argentina/Buenos_Aires" <?php echo $settings['timezone'] === 'America/Argentina/Buenos_Aires' ? 'selected' : ''; ?>>America/Argentina/Buenos_Aires</option>
                                <option value="Europe/Madrid" <?php echo $settings['timezone'] === 'Europe/Madrid' ? 'selected' : ''; ?>>Europe/Madrid</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="language" class="form-label">Idioma</label>
                            <select class="form-select" id="language" name="language">
                                <option value="es" <?php echo $settings['language'] === 'es' ? 'selected' : ''; ?>>Español</option>
                                <option value="en" <?php echo $settings['language'] === 'en' ? 'selected' : ''; ?>>English</option>
                                <option value="pt" <?php echo $settings['language'] === 'pt' ? 'selected' : ''; ?>>Português</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Configuración de Operaciones -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clock me-2"></i>Operaciones del Hotel
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="checkin_time" class="form-label">Hora de Check-in</label>
                            <input type="time" class="form-control" id="checkin_time" name="checkin_time" 
                                   value="<?php echo htmlspecialchars($settings['checkin_time']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="checkout_time" class="form-label">Hora de Check-out</label>
                            <input type="time" class="form-control" id="checkout_time" name="checkout_time" 
                                   value="<?php echo htmlspecialchars($settings['checkout_time']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="max_guests_per_room" class="form-label">Máximo de Huéspedes por Habitación</label>
                            <input type="number" class="form-control" id="max_guests_per_room" name="max_guests_per_room" 
                                   value="<?php echo htmlspecialchars($settings['max_guests_per_room']); ?>" min="1" max="10">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuración del Sistema -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-server me-2"></i>Configuración del Sistema
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="auto_backup" name="auto_backup" 
                                       <?php echo $settings['auto_backup'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="auto_backup">
                                    Backup Automático
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="backup_frequency" class="form-label">Frecuencia de Backup</label>
                            <select class="form-select" id="backup_frequency" name="backup_frequency">
                                <option value="daily" <?php echo $settings['backup_frequency'] === 'daily' ? 'selected' : ''; ?>>Diario</option>
                                <option value="weekly" <?php echo $settings['backup_frequency'] === 'weekly' ? 'selected' : ''; ?>>Semanal</option>
                                <option value="monthly" <?php echo $settings['backup_frequency'] === 'monthly' ? 'selected' : ''; ?>>Mensual</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notification_email" name="notification_email" 
                                       <?php echo $settings['notification_email'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="notification_email">
                                    Notificaciones por Email
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" 
                                       <?php echo $settings['maintenance_mode'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="maintenance_mode">
                                    Modo Mantenimiento
                                    <small class="text-muted d-block">El sistema estará en modo mantenimiento</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="button" class="btn btn-outline-warning" onclick="resetToDefaults()">
                                    <i class="fas fa-undo me-2"></i>Restaurar Valores por Defecto
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="clearCache()">
                                    <i class="fas fa-trash me-2"></i>Limpiar Caché del Sistema
                                </button>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="testSettings()">
                                    <i class="fas fa-vial me-2"></i>Probar Configuración
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Guardar Cambios
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../../backend/includes/footer.php'; ?>

<script>
// Funciones de configuración
function exportSettings() {
    fetch('api/endpoints/settings.php?action=export')
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'hotel_settings_' + new Date().toISOString().split('T')[0] + '.json';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            showNotification('Configuración exportada correctamente', 'success');
        })
        .catch(error => {
            console.error('Error exportando configuración:', error);
            showNotification('Error al exportar configuración', 'error');
        });
}

function importSettings() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.json';
    input.onchange = function(e) {
        const file = e.target.files[0];
        const reader = new FileReader();
        reader.onload = function(event) {
            try {
                const settings = JSON.parse(event.target.result);
                
                // Llenar el formulario con los datos importados
                Object.keys(settings).forEach(key => {
                    const element = document.querySelector(`[name="${key}"]`);
                    if (element) {
                        if (element.type === 'checkbox') {
                            element.checked = settings[key];
                        } else {
                            element.value = settings[key];
                        }
                    }
                });
                
                showNotification('Configuración importada correctamente. Revise los cambios y guarde.', 'success');
            } catch (error) {
                showNotification('Error al importar configuración: formato inválido', 'error');
            }
        };
        reader.readAsText(file);
    };
    input.click();
}

function resetToDefaults() {
    if (confirm('¿Está seguro de restaurar todos los valores a su configuración por defecto? Esta acción no se puede deshacer.')) {
        fetch('api/endpoints/settings.php?action=reset', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showNotification('Error al restaurar configuración', 'error');
                }
            })
            .catch(error => {
                console.error('Error restaurando configuración:', error);
                showNotification('Error al restaurar configuración', 'error');
            });
    }
}

function clearCache() {
    if (confirm('¿Está seguro de limpiar toda la caché del sistema? Esto puede afectar temporalmente el rendimiento.')) {
        fetch('api/endpoints/settings.php?action=clear_cache', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Caché del sistema limpiada correctamente', 'success');
                } else {
                    showNotification('Error al limpiar caché', 'error');
                }
            })
            .catch(error => {
                console.error('Error limpiando caché:', error);
                showNotification('Error al limpiar caché', 'error');
            });
    }
}

function testSettings() {
    const formData = new FormData(document.getElementById('settingsForm'));
    const settings = {};
    
    for (let [key, value] of formData.entries()) {
        const element = document.querySelector(`[name="${key}"]`);
        if (element && element.type === 'checkbox') {
            settings[key] = element.checked;
        } else {
            settings[key] = value;
        }
    }
    
    fetch('api/endpoints/settings.php?action=test', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(settings)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Configuración validada correctamente', 'success');
        } else {
            showNotification('Errores en la configuración: ' + data.errors.join(', '), 'error');
        }
    })
    .catch(error => {
        console.error('Error probando configuración:', error);
        showNotification('Error al probar configuración', 'error');
    });
}

// Validación del formulario
document.getElementById('settingsForm').addEventListener('submit', function(e) {
    const hotelName = document.getElementById('hotel_name').value.trim();
    const hotelEmail = document.getElementById('hotel_email').value.trim();
    
    if (!hotelName) {
        e.preventDefault();
        showNotification('El nombre del hotel es obligatorio', 'error');
        return;
    }
    
    if (hotelEmail && !isValidEmail(hotelEmail)) {
        e.preventDefault();
        showNotification('El email del hotel no es válido', 'error');
        return;
    }
    
    // Mostrar indicador de carga
    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';
});

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Modo mantenimiento
document.getElementById('maintenance_mode').addEventListener('change', function() {
    if (this.checked) {
        if (!confirm('¿Activar modo mantenimiento? El sistema estará fuera de servicio para los usuarios regulares.')) {
            this.checked = false;
        }
    }
});

// Backup automático
document.getElementById('auto_backup').addEventListener('change', function() {
    const backupFrequency = document.getElementById('backup_frequency');
    backupFrequency.disabled = !this.checked;
});

// Inicializar estado de los campos dependientes
document.addEventListener('DOMContentLoaded', function() {
    const autoBackup = document.getElementById('auto_backup');
    const backupFrequency = document.getElementById('backup_frequency');
    backupFrequency.disabled = !autoBackup.checked;
});
</script>

<style>
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.settings-section {
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 20px;
    margin-bottom: 20px;
}

.settings-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.form-label {
    font-weight: 500;
    color: #495057;
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card:hover {
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
}
</style>
