<?php
require_once __DIR__ . '/../../../backend/config/database.php';

// Verificar sesión de usuario
if (!isset($_SESSION['usuario'])) {
    header('Location: /Hotel_tame/login');
    exit;
}
include __DIR__ . '/../../../backend/includes/header.php';
include __DIR__ . '/../../../backend/includes/sidebar.php';
?>

<div class="main-content">
    <div id="notification-container"></div>
    
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Productos</h1>
                <p class="text-muted mb-0">Gestiona los productos disponibles para los clientes</p>
            </div>
            <button class="btn btn-primary" onclick="abrirModalNuevo()">
                <i class="fas fa-plus me-2"></i>Nuevo Producto
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Categoría</label>
                    <select class="form-select" id="filtroCategoria" onchange="cargarProductos()">
                        <option value="">Todas las categorías</option>
                        <option value="comida">Comida</option>
                        <option value="bebida">Bebida</option>
                        <option value="snack">Snack</option>
                        <option value="higiene">Higiene</option>
                        <option value="otros">Otros</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="filtroEstado" onchange="cargarProductos()">
                        <option value="">Todos</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Búsqueda</label>
                    <input type="text" class="form-control" id="busqueda" placeholder="Buscar producto..." onkeyup="cargarProductos()">
                </div>
            </div>
        </div>
    </div>

    <!-- Grid de productos -->
    <div class="row g-4" id="productosGrid">
        <!-- Las tarjetas se cargarán dinámicamente aquí -->
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalProducto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formProducto" onsubmit="guardarProducto(event)">
                <div class="modal-body">
                    <input type="hidden" id="producto_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Categoría *</label>
                            <select class="form-select" id="categoria" required>
                                <option value="">Seleccione...</option>
                                <option value="comida">Comida</option>
                                <option value="bebida">Bebida</option>
                                <option value="snack">Snack</option>
                                <option value="higiene">Higiene</option>
                                <option value="otros">Otros</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Precio *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="precio" required min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stock</label>
                            <input type="number" class="form-control" id="stock" min="0" value="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Imagen del Producto</label>
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

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="activo" checked>
                        <label class="form-check-label" for="activo">
                            Producto activo
                        </label>
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
$(document).ready(function() {
    cargarProductos();
});

function cargarProductos() {
    const categoria = $('#filtroCategoria').val();
    const estado = $('#filtroEstado').val();
    const busqueda = $('#busqueda').val();
    
    let url = 'api/endpoints/productos.php';
    const params = [];
    
    if (categoria) params.push(`categoria=${categoria}`);
    if (estado !== '') params.push(`all=true`); // Para incluir inactivos
    if (busqueda) params.push(`busqueda=${busqueda}`);
    
    if (params.length > 0) {
        url += '?' + params.join('&');
    }
    
    $.get(url, function(data) {
        const grid = $('#productosGrid');
        grid.empty();
        
        const productosList = Array.isArray(data) ? data : (data.records || []);
        
        // Filtrar por estado si es necesario
        let productosFiltrados = productosList;
        if (estado !== '') {
            productosFiltrados = productosList.filter(p => p.activo == estado);
        }
        
        if (busqueda) {
            productosFiltrados = productosFiltrados.filter(p => 
                p.nombre.toLowerCase().includes(busqueda.toLowerCase()) ||
                p.descripcion.toLowerCase().includes(busqueda.toLowerCase())
            );
        }

        if (productosFiltrados.length === 0) {
            grid.append(`
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No se encontraron productos</h5>
                    </div>
                </div>
            `);
            return;
        }

        productosFiltrados.forEach(producto => {
            const categoriaIcon = {
                'comida': 'fa-utensils',
                'bebida': 'fa-coffee',
                'snack': 'fa-cookie',
                'higiene': 'fa-soap',
                'otros': 'fa-box'
            }[producto.categoria] || 'fa-box';
            
            const categoriaColor = {
                'comida': 'success',
                'bebida': 'info',
                'snack': 'warning',
                'higiene': 'primary',
                'otros': 'secondary'
            }[producto.categoria] || 'secondary';
            
            const estadoBadge = producto.activo ? 
                '<span class="badge bg-success">Activo</span>' : 
                '<span class="badge bg-danger">Inactivo</span>';
            
            const stockBadge = producto.stock > 10 ? 
                '<span class="badge bg-success">Disponible</span>' : 
                producto.stock > 0 ? 
                '<span class="badge bg-warning">Bajo stock</span>' : 
                '<span class="badge bg-danger">Sin stock</span>';
            
            grid.append(`
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-${categoriaColor} bg-opacity-10 text-${categoriaColor} rounded p-2 me-3">
                                        <i class="fas ${categoriaIcon} fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-0">${producto.nombre}</h5>
                                        <small class="text-muted">${producto.categoria.charAt(0).toUpperCase() + producto.categoria.slice(1)}</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    ${estadoBadge}
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <p class="mb-2"><strong>Precio:</strong> <span class="text-primary fs-5 fw-bold">$${parseFloat(producto.precio).toLocaleString('es-CO')}</span></p>
                                <p class="mb-2"><strong>Stock:</strong> ${stockBadge} (${producto.stock} unidades)</p>
                            </div>
                            
                            ${producto.descripcion ? `<p class="text-muted small">${producto.descripcion}</p>` : ''}
                            
                            ${producto.imagen_url ? `
                                <div class="mb-3">
                                    <img src="${producto.imagen_url}" alt="${producto.nombre}" class="img-fluid rounded" style="max-height: 150px; width: 100%; object-fit: cover;">
                                </div>
                            ` : ''}
                            
                            <div class="d-flex gap-2 mt-3">
                                <button class="btn btn-outline-primary btn-sm flex-fill" onclick="editarProducto(${producto.id})">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="eliminarProducto(${producto.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });
    });
}

function abrirModalNuevo() {
    console.log('Abriendo modal de nuevo producto...');
    $('#modalTitle').text('Nuevo Producto');
    $('#formProducto')[0].reset();
    $('#producto_id').val('');
    $('#imagePreview').html('');
    $('#imagen_url').val('');
    $('#activo').prop('checked', true);
    
    // Abrir el modal con la API de Bootstrap 5
    const modalElement = document.getElementById('modalProducto');
    const modal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: true
    });
    modal.show();
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
                title.textContent = 'Tomar Foto';
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
                        const file = new File([blob], 'camera_photo.jpg', { type: 'image/jpeg' });
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

function editarProducto(id) {
    $.get(`api/endpoints/productos.php?id=${id}`, function(producto) {
        $('#modalTitle').text('Editar Producto');
        $('#producto_id').val(producto.id);
        $('#nombre').val(producto.nombre);
        $('#descripcion').val(producto.descripcion);
        $('#categoria').val(producto.categoria);
        $('#precio').val(producto.precio);
        $('#imagen_url').val(producto.imagen_url);
        $('#stock').val(producto.stock);
        $('#activo').prop('checked', producto.activo);
        $('#modalProducto').modal('show');
    });
}

function guardarProducto(e) {
    e.preventDefault();
    
    // Evitar múltiples envíos
    if ($(this).data('submitting')) {
        return false;
    }
    
    const id = $('#producto_id').val();
    const formData = new FormData();
    
    // Debug: mostrar los valores que se están enviando
    console.log('Datos del formulario:');
    console.log('nombre:', $('#nombre').val());
    console.log('categoria:', $('#categoria').val());
    console.log('precio:', $('#precio').val());
    console.log('descripcion:', $('#descripcion').val());
    console.log('stock:', $('#stock').val());
    console.log('activo:', $('#activo').is(':checked'));
    
    // Agregar todos los campos del formulario manualmente
    formData.append('nombre', $('#nombre').val());
    formData.append('descripcion', $('#descripcion').val());
    formData.append('categoria', $('#categoria').val());
    formData.append('precio', $('#precio').val());
    formData.append('imagen_url', $('#imagen_url').val());
    formData.append('stock', $('#stock').val());
    formData.append('activo', $('#activo').is(':checked') ? '1' : '0');
    
    if (id) {
        formData.append('id', id);
    }
    
    // Agregar archivo si existe
    const fileInput = document.getElementById('imagen');
    if (fileInput.files && fileInput.files[0]) {
        console.log('Archivo encontrado:', fileInput.files[0].name);
        formData.append('imagen', fileInput.files[0]);
    } else {
        console.log('No hay archivo');
    }
    
    // Debug: mostrar FormData contents
    for (let [key, value] of formData.entries()) {
        console.log(`${key}:`, value);
    }
    
    // Marcar formulario como enviando
    $('#formProducto').data('submitting', true);
    $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
    
    $.ajax({
        url: id ? `api/endpoints/productos.php?id=${id}` : 'api/endpoints/productos.php',
        type: id ? 'PUT' : 'POST', // Usar PUT cuando hay ID
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            console.log('Respuesta exitosa:', response);
            $('#modalProducto').modal('hide');
            showNotification(response.message || 'Producto guardado exitosamente', 'success');
            cargarProductos();
        },
        error: function(xhr) {
            console.log('Error completo:', xhr);
            console.log('Response text:', xhr.responseText);
            console.log('Status:', xhr.status);
            console.log('Response JSON:', xhr.responseJSON);
            const errorMsg = xhr.responseJSON?.message || 'Error al guardar producto';
            showNotification(errorMsg, 'error');
        },
        complete: function() {
            // Restaurar botón
            $('#formProducto').data('submitting', false);
            $('button[type="submit"]').prop('disabled', false).html('Guardar');
        }
    });
}

function eliminarProducto(id) {
    if (confirm('¿Está seguro de eliminar este producto?')) {
        $.ajax({
            url: 'api/endpoints/productos.php',
            type: 'DELETE',
            contentType: 'application/json',
            data: JSON.stringify({ id: id }),
            success: function(response) {
                showNotification(response.message || 'Producto eliminado', 'success');
                cargarProductos();
            },
            error: function(xhr) {
                showNotification(xhr.responseJSON?.message || 'Error al eliminar', 'error');
            }
        });
    }
}
</script>

<?php include __DIR__ . '/../../../backend/includes/footer.php'; ?>
