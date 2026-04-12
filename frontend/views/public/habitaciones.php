<?php
require_once dirname(__DIR__, 3) . '/config/env.php';
hotel_tame_define_web_constants();
$WB = HOTEL_TAME_WEB_BASE;

require_once __DIR__ . '/../../../backend/config/database.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ' . hotel_tame_url_path('login'));
    exit;
}

// Definir título y descripción de la página
$pageTitle = 'Habitaciones - Hotel Management System';
$pageDescription = 'Gestiona las habitaciones del hotel';

include __DIR__ . '/../../../backend/includes/header.php';
include __DIR__ . '/../../../backend/includes/sidebar.php';
?>
<script>const HT_BASE = <?php echo json_encode($WB); ?>;</script>

<div class="main-content">
    <div id="notification-container"></div>
    
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Habitaciones</h1>
                <p class="text-muted mb-0">Gestiona las habitaciones del hotel</p>
            </div>
            <button class="btn btn-primary" onclick="abrirModalNuevo()">
                <i class="fas fa-plus me-2"></i>Nueva Habitación
            </button>
        </div>
    </div>

    <!-- Grid de habitaciones -->
    <div class="row g-4" id="habitacionesGrid">
        <!-- Las tarjetas se cargarán dinámicamente aquí -->
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalHabitacion" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nueva Habitación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formHabitacion" onsubmit="guardarHabitacion(event)">
                <div class="modal-body">
                    <input type="hidden" id="habitacion_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Número *</label>
                            <input type="text" class="form-control" id="numero" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo *</label>
                            <select class="form-select" id="tipo" required>
                                <option value="">Seleccione...</option>
                                <option value="simple">Simple</option>
                                <option value="doble">Doble</option>
                                <option value="suite">Suite</option>
                                <option value="presidencial">Presidencial</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Piso *</label>
                            <input type="number" class="form-control" id="piso" required min="1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Precio por Noche *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="precio_noche" required min="0" step="0.01">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Capacidad *</label>
                            <input type="number" class="form-control" id="capacidad" required min="1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado *</label>
                            <select class="form-select" id="estado" required>
                                <option value="disponible">Disponible</option>
                                <option value="ocupada">Ocupada</option>
                                <option value="mantenimiento">Mantenimiento</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Imagen de la Habitación</label>
                        <div class="border rounded p-3 bg-light">
                            <input type="file" class="form-control mb-2" id="imagen" accept="image/*" onchange="previewImage(this)">
                            <div class="d-flex gap-2 mb-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('imagen').click()">
                                    <i class="fas fa-upload me-1"></i> Subir Archivo
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="openCamera()">
                                    <i class="fas fa-camera me-1"></i> Cámara
                                </button>
                            </div>
                            <div id="imagePreview" class="text-center"></div>
                            <input type="hidden" id="imagen_url">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Función global para lazy loading - IGUAL QUE EN PRODUCTOS
function initializeLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    const src = img.dataset.src;
                    
                    if (src) {
                        img.src = src;
                        img.onload = () => {
                            img.style.opacity = '1';
                            img.classList.remove('lazy-image');
                        };
                        img.onerror = () => {
                            img.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300"%3E%3Crect width="400" height="300" fill="%23dc3545"/%3E%3Ctext x="50%25" y="50%25" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="14" fill="white"%3EError imagen%3C/text%3E%3C/svg%3E';
                            img.style.opacity = '1';
                        };
                        
                        observer.unobserve(img);
                    }
                }
            });
        });
        
        document.querySelectorAll('.lazy-image').forEach(img => {
            imageObserver.observe(img);
        });
    } else {
        document.querySelectorAll('.lazy-image').forEach(img => {
            const src = img.dataset.src;
            if (src) {
                img.src = src;
                img.onload = () => {
                    img.style.opacity = '1';
                    img.classList.remove('lazy-image');
                };
            }
        });
    }
}

$(document).ready(function() {
    cargarHabitaciones();
    initializeLazyLoading();
});

function cargarHabitaciones() {
    $.get('api/endpoints/habitaciones.php?estado_real=1', function(data) {
        const grid = $('#habitacionesGrid');
        grid.empty();
        
        const habitacionesList = Array.isArray(data) ? data : (data.records || []);

        if (habitacionesList.length === 0) {
            grid.append(`
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-bed fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No se encontraron habitaciones</h5>
                    </div>
                </div>
            `);
            return;
        }

        habitacionesList.forEach(habitacion => {
            const estadoClass = {
                'disponible': 'success',
                'ocupada': 'danger',
                'mantenimiento': 'warning'
            }[habitacion.estado];
            
            const estadoTexto = {
                'disponible': 'Disponible',
                'ocupada': 'Ocupada',
                'mantenimiento': 'Mantenimiento'
            }[habitacion.estado];

            const tipoCapitalizado = habitacion.tipo ? (habitacion.tipo.charAt(0).toUpperCase() + habitacion.tipo.slice(1)) : '';
            const precioValue = habitacion.precio ?? habitacion.precio_noche ?? 0;
            
            grid.append(`
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                        <i class="fas fa-bed fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-0">Hab. ${habitacion.numero}</h5>
                                        <small class="text-muted">${tipoCapitalizado} - Piso ${habitacion.piso}</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-${estadoClass}">${estadoTexto}</span>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <p class="mb-2"><strong>Precio:</strong> <span class="text-primary fs-5 fw-bold">$${parseFloat(precioValue).toLocaleString('es-CO')}</span></p>
                                <p class="mb-2"><strong>Capacidad:</strong> <i class="fas fa-user ms-1"></i> ${habitacion.capacidad}</p>
                            </div>
                            
                            ${habitacion.descripcion ? `<p class="text-muted small">${habitacion.descripcion}</p>` : ''}
                            
                            ${habitacion.imagen_url ? `
                                <div class="mb-3">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'%3E%3Crect width='400' height='300' fill='%23f8f9fa'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='sans-serif' font-size='14' fill='%236c757d'%3ECargando...%3C/text%3E%3C/svg%3E" 
                                         data-src="${HT_BASE}/${habitacion.imagen_url}" 
                                         alt="Habitación ${habitacion.numero}" 
                                         class="img-fluid rounded lazy-image" 
                                         style="max-height: 150px; width: 100%; object-fit: cover; transition: opacity 0.3s ease-in-out;"
                                         loading="lazy">
                                </div>
                            ` : ''}
                            
                            <div class="d-flex gap-2 mt-3">
                                <button class="btn btn-outline-primary btn-sm flex-fill" onclick="editarHabitacion(${habitacion.id})">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="eliminarHabitacion(${habitacion.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });
        
        initializeLazyLoading();
    });
}

function abrirModalNuevo() {
    console.log('Abriendo modal de nueva habitación...');
    $('#modalTitle').text('Nueva Habitación');
    $('#formHabitacion')[0].reset();
    $('#habitacion_id').val('');
    $('#imagePreview').html('');
    $('#imagen_url').val('');
    
    const modalElement = document.getElementById('modalHabitacion');
    const modal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: true
    });
    modal.show();
}

function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const fileSize = (file.size / 1024).toFixed(2) + ' KB';
            const fileName = file.name;
            
            preview.innerHTML = `
                <div class="border rounded p-2 bg-light">
                    <div class="mb-2">
                        <small class="text-muted d-block">Archivo: ${fileName}</small>
                        <small class="text-muted d-block">Tamaño: ${fileSize}</small>
                    </div>
                    <img src="${e.target.result}" alt="Vista previa" 
                         class="img-fluid rounded border" 
                         style="max-height: 200px; width: 100%; object-fit: cover;">
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage()">
                            <i class="fas fa-times"></i> Quitar imagen
                        </button>
                    </div>
                </div>
            `;
        };
        
        reader.onerror = function() {
            preview.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Error al leer el archivo. Intenta nuevamente.
                </div>
            `;
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage() {
    const preview = document.getElementById('imagePreview');
    const fileInput = document.getElementById('imagen');
    
    preview.innerHTML = '';
    fileInput.value = '';
    
    const habitacionId = $('#habitacion_id').val();
    if (habitacionId) {
        const currentImageUrl = $('#imagen_url').val();
        if (currentImageUrl) {
            preview.innerHTML = `
                <div class="mt-3">
                    <p class="text-muted small mb-2">Imagen actual:</p>
                    <img src="${HT_BASE}/${currentImageUrl}" alt="Imagen actual" 
                         class="img-fluid rounded border" 
                         style="max-height: 200px; width: 100%; object-fit: cover;">
                </div>
            `;
        }
    }
}

function openCamera() {
    const isSecure = location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
    
    if (!isSecure) {
        showNotification('La cámara requiere HTTPS o localhost', 'error');
        return;
    }
    
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }})
            .then(function(stream) {
                const video = document.createElement('video');
                video.srcObject = stream;
                video.autoplay = true;
                video.style.width = '100%';
                video.style.maxHeight = '300px';
                
                const modal = document.createElement('div');
                modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; display: flex; align-items: center; justify-content: center;';
                
                const container = document.createElement('div');
                container.style.cssText = 'background: white; padding: 20px; border-radius: 8px; text-align: center; max-width: 90%;';
                
                const title = document.createElement('h5');
                title.textContent = 'Tomar Foto de Habitación';
                title.style.marginBottom = '15px';
                
                const videoContainer = document.createElement('div');
                videoContainer.appendChild(video);
                
                const buttonContainer = document.createElement('div');
                buttonContainer.style.marginTop = '15px';
                
                const captureBtn = document.createElement('button');
                captureBtn.textContent = 'Capturar';
                captureBtn.className = 'btn btn-primary me-2';
                captureBtn.onclick = function() {
                    const canvas = document.createElement('canvas');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0);
                    
                    canvas.toBlob(function(blob) {
                        const file = new File([blob], 'habitacion_camera.jpg', { type: 'image/jpeg' });
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        
                        const fileInput = document.getElementById('imagen');
                        fileInput.files = dataTransfer.files;
                        
                        previewImage({ target: { files: [file] } });
                        
                        stream.getTracks().forEach(track => track.stop());
                        document.body.removeChild(modal);
                    }, 'image/jpeg', 0.8);
                };
                
                const cancelBtn = document.createElement('button');
                cancelBtn.textContent = 'Cancelar';
                cancelBtn.className = 'btn btn-secondary';
                cancelBtn.onclick = function() {
                    stream.getTracks().forEach(track => track.stop());
                    document.body.removeChild(modal);
                };
                
                buttonContainer.appendChild(captureBtn);
                buttonContainer.appendChild(cancelBtn);
                
                container.appendChild(title);
                container.appendChild(videoContainer);
                container.appendChild(buttonContainer);
                modal.appendChild(container);
                document.body.appendChild(modal);
            })
            .catch(function(err) {
                console.error('Error accessing camera:', err);
                showNotification('No se pudo acceder a la cámara', 'error');
            });
    } else {
        showNotification('Tu navegador no soporta acceso a la cámara', 'error');
    }
}

function showNotification(message, type) {
    const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
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

function editarHabitacion(id) {
    $.get(`api/endpoints/habitaciones.php?id=${id}`, function(habitacion) {
        $('#modalTitle').text('Editar Habitación');
        $('#habitacion_id').val(habitacion.id);
        $('#numero').val(habitacion.numero);
        $('#tipo').val(habitacion.tipo);
        $('#piso').val(habitacion.piso);
        $('#precio_noche').val(habitacion.precio ?? habitacion.precio_noche);
        $('#capacidad').val(habitacion.capacidad);
        $('#estado').val(habitacion.estado);
        $('#descripcion').val(habitacion.descripcion);
        $('#imagen_url').val(habitacion.imagen_url || '');
        
        const preview = document.getElementById('imagePreview');
        if (habitacion.imagen_url) {
            preview.innerHTML = `
                <div class="mt-3">
                    <p class="text-muted small mb-2">Imagen actual:</p>
                    <img src="${HT_BASE}/${habitacion.imagen_url}" alt="Imagen actual" 
                         class="img-fluid rounded border" 
                         style="max-height: 200px; width: 100%; object-fit: cover;">
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage()">
                            <i class="fas fa-times"></i> Quitar imagen
                        </button>
                    </div>
                </div>
            `;
        } else {
            preview.innerHTML = '';
        }
        
        const modalElement = document.getElementById('modalHabitacion');
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }).fail(function(xhr) {
        showNotification('Error al cargar los datos de la habitación', 'error');
    });
}

function guardarHabitacion(e) {
    e.preventDefault();
    
    if ($(this).data('submitting')) {
        return false;
    }
    
    const id = $('#habitacion_id').val();
    const formData = new FormData();
    
    console.log('Datos del formulario:');
    console.log('numero:', $('#numero').val());
    console.log('tipo:', $('#tipo').val());
    console.log('piso:', $('#piso').val());
    console.log('precio_noche:', $('#precio_noche').val());
    console.log('capacidad:', $('#capacidad').val());
    console.log('estado:', $('#estado').val());
    console.log('descripcion:', $('#descripcion').val());
    console.log('imagen_url:', $('#imagen_url').val());
    
    formData.append('numero', $('#numero').val());
    formData.append('tipo', $('#tipo').val());
    formData.append('piso', $('#piso').val());
    formData.append('precio_noche', $('#precio_noche').val());
    formData.append('precio', $('#precio_noche').val());
    formData.append('capacidad', $('#capacidad').val());
    formData.append('estado', $('#estado').val());
    formData.append('descripcion', $('#descripcion').val());
    formData.append('imagen_url', $('#imagen_url').val());
    
    if (id) {
        formData.append('id', id);
        formData.append('_method', 'PUT');
    }
    
    const fileInput = document.getElementById('imagen');
    if (fileInput.files && fileInput.files[0]) {
        console.log('Archivo encontrado:', fileInput.files[0].name);
        formData.append('imagen', fileInput.files[0]);
    } else {
        console.log('No hay archivo');
    }
    
    console.log('FormData contents:');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}:`, value);
    }
    
    $('#formHabitacion').data('submitting', true);
    $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
    
    $.ajax({
        url: id ? `api/endpoints/habitaciones.php?id=${id}` : 'api/endpoints/habitaciones.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            console.log('Respuesta exitosa:', response);
            $('#modalHabitacion').modal('hide');
            showNotification(response.message || 'Habitación guardada exitosamente', 'success');
            cargarHabitaciones();
        },
        error: function(xhr) {
            console.log('Error:', xhr);
            const errorMsg = xhr.responseJSON?.message || 'Error al guardar habitación';
            showNotification(errorMsg, 'error');
        },
        complete: function() {
            $('#formHabitacion').data('submitting', false);
            $('button[type="submit"]').prop('disabled', false).html('Guardar');
        }
    });
}

function eliminarHabitacion(id) {
    if (confirm('¿Está seguro de eliminar esta habitación?')) {
        $.ajax({
            url: 'api/endpoints/habitaciones.php',
            type: 'DELETE',
            contentType: 'application/json',
            data: JSON.stringify({ id: id }),
            success: function(response) {
                showNotification(response.message || 'Habitación eliminada', 'success');
                cargarHabitaciones();
            },
            error: function(xhr) {
                showNotification(xhr.responseJSON?.message || 'Error al eliminar', 'error');
            }
        });
    }
}
</script>

<?php include __DIR__ . '/../../../backend/includes/footer.php'; ?>
