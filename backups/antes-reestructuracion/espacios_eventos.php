<?php 
require_once 'config/database.php';

// Verificar sesión de usuario
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Gestión de Espacios de Eventos</h2>
                <button class="btn btn-primary" onclick="abrirModalNuevo()">
                    <i class="fas fa-plus me-2"></i>Nuevo Espacio
                </button>
            </div>
            
            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="filtroTipo" class="form-label">Tipo de Espacio</label>
                            <select class="form-select" id="filtroTipo">
                                <option value="">Todos los tipos</option>
                                <option value="Salón">Salón</option>
                                <option value="Jardín">Jardín</option>
                                <option value="Terraza">Terraza</option>
                                <option value="Sala">Sala</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filtroEstado" class="form-label">Estado</label>
                            <select class="form-select" id="filtroEstado">
                                <option value="">Todos los estados</option>
                                <option value="disponible">Disponible</option>
                                <option value="ocupado">Ocupado</option>
                                <option value="mantenimiento">Mantenimiento</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="busqueda" class="form-label">Búsqueda</label>
                            <input type="text" class="form-control" id="busqueda" placeholder="Buscar espacio...">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-outline-primary w-100" onclick="cargarEspacios()">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Lista de Espacios -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="espaciosTable">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Capacidad</th>
                                    <th>Precio/Hora</th>
                                    <th>Ubicación</th>
                                    <th>Estado</th>
                                    <th>Activo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="espaciosList">
                                <!-- Los espacios se cargarán aquí -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Espacio -->
<div class="modal fade" id="modalEspacio" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Espacio de Evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEspacio">
                    <input type="hidden" id="espacio_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre del Espacio</label>
                                <input type="text" class="form-control" id="nombre" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tipo_espacio" class="form-label">Tipo de Espacio</label>
                                <select class="form-select" id="tipo_espacio" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Salón">Salón</option>
                                    <option value="Jardín">Jardín</option>
                                    <option value="Terraza">Terraza</option>
                                    <option value="Sala">Sala</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="capacidad_maxima" class="form-label">Capacidad Máxima</label>
                                <input type="number" class="form-control" id="capacidad_maxima" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="precio_hora" class="form-label">Precio por Hora</label>
                                <input type="number" class="form-control" id="precio_hora" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="precio_completo" class="form-label">Precio Completo</label>
                                <input type="number" class="form-control" id="precio_completo" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" required>
                                    <option value="disponible">Disponible</option>
                                    <option value="ocupado">Ocupado</option>
                                    <option value="mantenimiento">Mantenimiento</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="ubicacion" class="form-label">Ubicación</label>
                        <input type="text" class="form-control" id="ubicacion" placeholder="Ej: Planta Baja, Azotea, Exterior">
                    </div>
                    
                    <div class="mb-3">
                        <label for="caracteristicas" class="form-label">Características</label>
                        <textarea class="form-control" id="caracteristicas" rows="3" placeholder="Ej: Aire acondicionado, sistema de sonido, iluminación profesional..."></textarea>
                    </div>
                    
                    <!-- Imagen del Espacio -->
                    <div class="mb-3">
                        <label class="form-label">Imagen del Espacio</label>
                        <div>
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
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="activo" checked>
                        <label class="form-check-label" for="activo">
                            Espacio Activo
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formEspacio" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    cargarEspacios();
});

function cargarEspacios() {
    const tipo = $('#filtroTipo').val();
    const estado = $('#filtroEstado').val();
    const busqueda = $('#busqueda').val();
    
    let url = 'api/endpoints/espacios_eventos.php';
    const params = [];
    
    if (tipo) params.push(`tipo=${tipo}`);
    if (estado) params.push(`estado=${estado}`);
    if (busqueda) params.push(`busqueda=${busqueda}`);
    
    if (params.length > 0) {
        url += '?' + params.join('&');
    }
    
    $.get(url, function(data) {
        let html = '';
        if (data.records && data.records.length > 0) {
            data.records.forEach(function(espacio) {
                html += `
                    <tr>
                        <td>${espacio.nombre}</td>
                        <td><span class="badge bg-info">${espacio.tipo_espacio}</span></td>
                        <td>${espacio.capacidad_maxima}</td>
                        <td>$${parseFloat(espacio.precio_hora).toFixed(2)}</td>
                        <td>${espacio.ubicacion || 'N/A'}</td>
                        <td><span class="badge bg-${getEstadoBadgeClass(espacio.estado)}">${espacio.estado}</span></td>
                        <td>
                            <span class="badge bg-${espacio.activo ? 'success' : 'secondary'}">
                                ${espacio.activo ? 'Activo' : 'Inactivo'}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editarEspacio(${espacio.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="eliminarEspacio(${espacio.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        } else {
            html = '<tr><td colspan="8" class="text-center">No se encontraron espacios</td></tr>';
        }
        
        $('#espaciosList').html(html);
    });
}

function getEstadoBadgeClass(estado) {
    switch(estado) {
        case 'disponible': return 'success';
        case 'ocupado': return 'warning';
        case 'mantenimiento': return 'danger';
        default: return 'secondary';
    }
}

function abrirModalNuevo() {
    $('#modalTitle').text('Nuevo Espacio de Evento');
    $('#formEspacio')[0].reset();
    $('#espacio_id').val('');
    $('#imagePreview').html('');
    $('#imagen_url').val('');
    $('#activo').prop('checked', true);
    $('#modalEspacio').modal('show');
}

function editarEspacio(id) {
    $.get(`api/endpoints/espacios_eventos.php?id=${id}`, function(espacio) {
        $('#modalTitle').text('Editar Espacio de Evento');
        $('#espacio_id').val(espacio.id);
        $('#nombre').val(espacio.nombre);
        $('#descripcion').val(espacio.descripcion);
        $('#tipo_espacio').val(espacio.tipo_espacio);
        $('#capacidad_maxima').val(espacio.capacidad_maxima);
        $('#precio_hora').val(espacio.precio_hora);
        $('#precio_completo').val(espacio.precio_completo);
        $('#ubicacion').val(espacio.ubicacion);
        $('#caracteristicas').val(espacio.caracteristicas);
        $('#estado').val(espacio.estado);
        $('#imagen_url').val(espacio.imagen_url);
        $('#activo').prop('checked', espacio.activo);
        $('#modalEspacio').modal('show');
    });
}

function guardarEspacio(e) {
    e.preventDefault();
    
    // Evitar múltiples envíos
    if ($(this).data('submitting')) {
        return false;
    }
    
    const id = $('#espacio_id').val();
    const formData = new FormData();
    
    // Agregar todos los campos del formulario manualmente
    formData.append('nombre', $('#nombre').val());
    formData.append('descripcion', $('#descripcion').val());
    formData.append('tipo_espacio', $('#tipo_espacio').val());
    formData.append('capacidad_maxima', $('#capacidad_maxima').val());
    formData.append('precio_hora', $('#precio_hora').val());
    formData.append('precio_completo', $('#precio_completo').val());
    formData.append('ubicacion', $('#ubicacion').val());
    formData.append('caracteristicas', $('#caracteristicas').val());
    formData.append('estado', $('#estado').val());
    formData.append('imagen_url', $('#imagen_url').val());
    formData.append('activo', $('#activo').is(':checked') ? '1' : '0');
    
    if (id) {
        formData.append('id', id);
    }
    
    // Agregar archivo si existe
    const fileInput = document.getElementById('imagen');
    if (fileInput.files && fileInput.files[0]) {
        formData.append('imagen', fileInput.files[0]);
    }
    
    // Marcar formulario como enviando
    $('#formEspacio').data('submitting', true);
    $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
    
    $.ajax({
        url: id ? `api/endpoints/espacios_eventos.php?id=${id}` : 'api/endpoints/espacios_eventos.php',
        type: id ? 'PUT' : 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            $('#modalEspacio').modal('hide');
            showNotification(response.message || 'Espacio guardado exitosamente', 'success');
            cargarEspacios();
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Error al guardar espacio';
            showNotification(errorMsg, 'error');
        },
        complete: function() {
            // Restaurar botón
            $('#formEspacio').data('submitting', false);
            $('button[type="submit"]').prop('disabled', false).html('Guardar');
        }
    });
}

function eliminarEspacio(id) {
    if (confirm('¿Está seguro de eliminar este espacio?')) {
        $.ajax({
            url: 'api/endpoints/espacios_eventos.php',
            type: 'DELETE',
            contentType: 'application/json',
            data: JSON.stringify({ id: id }),
            success: function(response) {
                showNotification(response.message || 'Espacio eliminado', 'success');
                cargarEspacios();
            },
            error: function(xhr) {
                showNotification(xhr.responseJSON?.message || 'Error al eliminar', 'error');
            }
        });
    }
}

function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = `
                <img src="${e.target.result}" alt="Vista previa" class="img-fluid rounded" style="max-height: 200px; max-width: 100%;">
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage()">
                        <i class="fas fa-times"></i> Quitar imagen
                    </button>
                </div>
            `;
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage() {
    document.getElementById('imagen').value = '';
    document.getElementById('imagePreview').innerHTML = '';
    $('#imagen_url').val('');
}

function openCamera() {
    // Verificar si estamos en HTTPS o localhost
    const isSecure = location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
    
    if (!isSecure) {
        showNotification('La cámara requiere HTTPS o localhost. Intenta usando 127.0.0.1 en lugar de localhost', 'error');
        return;
    }
    
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        // Solicitar permisos con opciones específicas
        const constraints = {
            video: {
                width: { ideal: 1280 },
                height: { ideal: 720 },
                facingMode: 'environment' // Cámara trasera en móviles
            }
        };
        
        navigator.mediaDevices.getUserMedia(constraints)
            .then(function(stream) {
                const video = document.createElement('video');
                video.srcObject = stream;
                video.autoplay = true;
                video.style.width = '100%';
                video.style.maxHeight = '300px';
                
                const modal = document.createElement('div');
                modal.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.8);
                    z-index: 9999;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                `;
                
                const container = document.createElement('div');
                container.style.cssText = `
                    background: white;
                    padding: 20px;
                    border-radius: 8px;
                    text-align: center;
                    max-width: 90%;
                `;
                
                const title = document.createElement('h5');
                title.textContent = 'Tomar Foto del Espacio';
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
                        const file = new File([blob], 'espacio_photo.jpg', { type: 'image/jpeg' });
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
                
                modal.onclick = function(e) {
                    if (e.target === modal) {
                        stream.getTracks().forEach(track => track.stop());
                        document.body.removeChild(modal);
                    }
                };
            })
            .catch(function(err) {
                console.error('Error accessing camera:', err);
                let errorMessage = 'No se pudo acceder a la cámara';
                
                switch(err.name) {
                    case 'NotAllowedError':
                        errorMessage = 'Permiso de cámara denegado. Por favor permite el acceso a la cámara en tu navegador.';
                        break;
                    case 'NotFoundError':
                        errorMessage = 'No se encontró ninguna cámara en el dispositivo.';
                        break;
                    case 'NotSupportedError':
                        errorMessage = 'Tu navegador no soporta acceso a la cámara.';
                        break;
                    case 'NotReadableError':
                        errorMessage = 'La cámara está siendo usada por otra aplicación.';
                        break;
                    case 'OverconstrainedError':
                        errorMessage = 'Las restricciones de la cámara no son compatibles.';
                        break;
                    default:
                        errorMessage = 'Error al acceder a la cámara: ' + err.message;
                }
                
                showNotification(errorMessage, 'error');
            });
    } else {
        showNotification('Tu navegador no soporta acceso a la cámara', 'error');
    }
}

function showNotification(message, type) {
    // Simple notification system
    const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
    const notification = $(`
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 1050;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('body').append(notification);
    
    // Auto-remove after 3 seconds
    setTimeout(function() {
        notification.fadeOut(300, function() {
            $(this).remove();
        });
    }, 3000);
}

// Event listeners
$('#formEspacio').on('submit', guardarEspacio);

// Auto-calcular precio completo
$('#precio_hora').on('input', function() {
    const precioHora = parseFloat($('#precio_hora').val()) || 0;
    const precioCompleto = precioHora * 8; // 8 horas por defecto
    $('#precio_completo').val(precioCompleto.toFixed(2));
});
</script>

<?php include 'includes/footer.php'; ?>
