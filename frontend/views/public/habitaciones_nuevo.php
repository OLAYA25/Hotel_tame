<?php
require_once dirname(__DIR__, 3) . '/config/env.php';
hotel_tame_define_web_constants();
$WB = HOTEL_TAME_WEB_BASE;

require_once __DIR__ . '/../../../backend/config/database.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ' . hotel_tame_url_path('login'));
    exit;
}

// Obtener categorías desde la base de datos
$categorias = [];
try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Verificar si existe la tabla de categorías
    $stmt = $db->query("SHOW TABLES LIKE 'categorias'");
    if ($stmt->rowCount() == 0) {
        // Crear tabla de categorías si no existe
        $db->exec("CREATE TABLE IF NOT EXISTS categorias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            icono VARCHAR(50) DEFAULT 'fas fa-box',
            color VARCHAR(20) DEFAULT '#007bff',
            activo BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Insertar categorías por defecto
        $categorias_default = [
            ['comida', 'fas fa-utensils', '#28a745'],
            ['bebida', 'fas fa-coffee', '#17a2b8'],
            ['snack', 'fas fa-cookie', '#ffc107'],
            ['higiene', 'fas fa-soap', '#6f42c1'],
            ['otros', 'fas fa-box', '#6c757d']
        ];
        
        foreach ($categorias_default as $cat) {
            $stmt = $db->prepare("INSERT INTO categorias (nombre, icono, color) VALUES (?, ?, ?)");
            $stmt->execute($cat);
        }
    }
    
    // Cargar categorías activas
    $stmt = $db->query("SELECT * FROM categorias WHERE activo = TRUE ORDER BY nombre");
    $categorias = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Error al cargar categorías: " . $e->getMessage());
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
                    <div class="input-group">
                        <select class="form-select" id="filtroCategoria" onchange="cargarProductos()">
                            <option value="">Todas las categorías</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria['nombre'] ?>"><?= ucfirst($categoria['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-outline-primary" type="button" onclick="abrirModalCategoria()" title="Nueva Categoría">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
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
                            <div class="input-group">
                                <select class="form-select" id="categoria" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?= $categoria['nombre'] ?>"><?= ucfirst($categoria['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-outline-primary" type="button" onclick="abrirModalCategoria()" title="Nueva Categoría">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
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

<!-- Modal Categoría -->
<div class="modal fade" id="modalCategoria" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCategoria" onsubmit="guardarCategoria(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Categoría *</label>
                        <input type="text" class="form-control" id="catNombre" required placeholder="Ej: Postres">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Icono</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="catIcono" placeholder="fas fa-ice-cream">
                            <button class="btn btn-outline-secondary" type="button" onclick="abrirSelectorIconos()">
                                <i class="fas fa-icons"></i> Seleccionar
                            </button>
                        </div>
                        <small class="text-muted">Clases de Font Awesome (ej: fas fa-ice-cream)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="catColor" value="#007bff">
                            <input type="text" class="form-control" id="catColorText" value="#007bff">
                        </div>
                        <small class="text-muted">Color para identificar la categoría</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Vista Previa</label>
                        <div class="border rounded p-3 text-center">
                            <div id="categoriaPreview" style="display: inline-block; padding: 8px 16px; border-radius: 20px; background-color: #007bff; color: white;">
                                <i class="fas fa-box me-2"></i>
                                <span id="previewNombre">Nueva Categoría</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Selector de Iconos -->
<div class="modal fade" id="modalIconos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar Icono</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row" id="iconosGrid">
                    <!-- Iconos se cargarán dinámicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="seleccionarIcono()">Seleccionar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Función global para lazy loading
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
        
        // Observar todas las imágenes con clase lazy-image
        document.querySelectorAll('.lazy-image').forEach(img => {
            imageObserver.observe(img);
        });
    } else {
        // Fallback para navegadores que no soportan IntersectionObserver
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
    cargarProductos();
    
    // Inicializar lazy loading al cargar la página
    initializeLazyLoading();
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
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'%3E%3Crect width='400' height='300' fill='%23f8f9fa'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='sans-serif' font-size='14' fill='%236c757d'%3ECargando...%3C/text%3E%3C/svg%3E" 
                                         data-src="${producto.imagen_url}" 
                                         alt="${producto.nombre}" 
                                         class="img-fluid rounded lazy-image" 
                                         style="max-height: 150px; width: 100%; object-fit: cover; transition: opacity 0.3s ease-in-out;"
                                         loading="lazy">
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
        
        // Inicializar lazy loading para las nuevas imágenes
        initializeLazyLoading();
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
        const file = input.files[0];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            // Mostrar vista previa con información del archivo
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

// Función para manejar imágenes de cámara
function previewCameraImage(imageData) {
    const preview = document.getElementById('imagePreview');
    
    if (imageData) {
        preview.innerHTML = `
            <div class="border rounded p-2 bg-light">
                <div class="mb-2">
                    <small class="text-muted d-block">Imagen de cámara</small>
                    <small class="text-muted d-block">Fecha: ${new Date().toLocaleString()}</small>
                </div>
                <img src="${imageData}" alt="Vista previa de cámara" 
                     class="img-fluid rounded border" 
                     style="max-height: 200px; width: 100%; object-fit: cover;">
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage()">
                        <i class="fas fa-times"></i> Quitar imagen
                    </button>
                </div>
            </div>
        `;
    }
}

// Función para quitar imagen seleccionada
function removeImage() {
    const preview = document.getElementById('imagePreview');
    const fileInput = document.getElementById('imagen');
    
    preview.innerHTML = '';
    fileInput.value = '';
    
    // Si estamos editando, mostrar la imagen original nuevamente
    const productId = $('#producto_id').val();
    if (productId) {
        const currentImageUrl = $('#imagen_url').val();
        if (currentImageUrl) {
            preview.innerHTML = `
                <div class="mt-3">
                    <p class="text-muted small mb-2">Imagen actual:</p>
                    <img src="${currentImageUrl}" alt="Imagen actual" 
                         class="img-fluid rounded border" 
                         style="max-height: 200px; width: 100%; object-fit: cover;">
                </div>
            `;
        }
    }
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
    // Asegurar que el mensaje sea un string
    const messageStr = typeof message === 'object' ? JSON.stringify(message) : String(message);
    
    // Simple notification system
    const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
    const notification = $(`
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 1050;">
            ${messageStr}
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
        
        // Mostrar imagen actual si existe
        if (producto.imagen_url) {
            $('#imagePreview').html(`
                <div class="mt-3">
                    <p class="text-muted small mb-2">Imagen actual:</p>
                    <img src="${producto.imagen_url}" alt="${producto.nombre}" 
                         class="img-fluid rounded border" 
                         style="max-height: 200px; width: 100%; object-fit: cover;">
                </div>
            `);
        } else {
            $('#imagePreview').html('');
        }
        
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
        formData.append('_method', 'PUT'); // Método override para PUT con FormData
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
        type: 'POST', // Siempre usar POST con _method override
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

// Funciones para manejo de categorías
let iconoSeleccionado = '';

function abrirModalCategoria() {
    $('#formCategoria')[0].reset();
    $('#catNombre').val('');
    $('#catIcono').val('fas fa-box');
    $('#catColor').val('#007bff');
    $('#catColorText').val('#007bff');
    actualizarPreview();
    
    const modal = new bootstrap.Modal(document.getElementById('modalCategoria'));
    modal.show();
}

function abrirSelectorIconos() {
    cargarIconos();
    const modal = new bootstrap.Modal(document.getElementById('modalIconos'));
    modal.show();
}

function cargarIconos() {
    const iconos = [
        'fas fa-utensils', 'fas fa-coffee', 'fas fa-cookie', 'fas fa-ice-cream', 'fas fa-pizza-slice',
        'fas fa-hamburger', 'fas fa-hotdog', 'fas fa-drumstick-bite', 'fas fa-fish', 'fas fa-carrot',
        'fas fa-apple-alt', 'fas fa-lemon', 'fas fa-pepper-hot', 'fas fa-cheese', 'fas fa-bacon',
        'fas fa-glass-martini', 'fas fa-wine-glass', 'fas fa-beer', 'fas fa-cocktail', 'fas fa-mug-hot',
        'fas fa-bread-slice', 'fas fa-birthday-cake', 'fas fa-candy-cane', 'fas fa-cookie-bite',
        'fas fa-soap', 'fas fa-pump-soap', 'fas fa-bath', 'fas fa-shower', 'fas fa-toilet-paper',
        'fas fa-box', 'fas fa-boxes', 'fas fa-archive', 'fas fa-inventory', 'fas fa-warehouse',
        'fas fa-shopping-bag', 'fas fa-shopping-cart', 'fas fa-shopping-basket', 'fas fa-store',
        'fas fa-tshirt', 'fas fa-socks', 'fas fa-shoe-prints', 'fas fa-hat-cowboy', 'fas fa-glasses',
        'fas fa-book', 'fas fa-bookmark', 'fas fa-newspaper', 'fas fa-palette', 'fas fa-pen',
        'fas fa-gamepad', 'fas fa-dice', 'fas fa-chess', 'fas fa-puzzle-piece', 'fas fa-dragon',
        'fas fa-heart', 'fas fa-star', 'fas fa-gem', 'fas fa-crown', 'fas fa-trophy',
        'fas fa-home', 'fas fa-building', 'fas fa-store-alt', 'fas fa-warehouse', 'fas fa-garage',
        'fas fa-car', 'fas fa-truck', 'fas fa-motorcycle', 'fas fa-bicycle', 'fas fa-plane',
        'fas fa-tree', 'fas fa-leaf', 'fas fa-seedling', 'fas fa-spa', 'fas fa-flower',
        'fas fa-sun', 'fas fa-moon', 'fas fa-cloud', 'fas fa-cloud-sun', 'fas fa-snowflake',
        'fas fa-bolt', 'fas fa-fire', 'fas fa-water', 'fas fa-wind', 'fas fa-rainbow'
    ];
    
    const grid = $('#iconosGrid');
    grid.empty();
    
    iconos.forEach(icono => {
        const col = $(`
            <div class="col-md-2 col-sm-3 col-4 mb-3">
                <div class="icon-option text-center p-3 border rounded cursor-pointer" data-icono="${icono}">
                    <i class="${icono} fa-2x mb-2"></i>
                    <div class="small">${icono.replace('fas fa-', '')}</div>
                </div>
            </div>
        `);
        grid.append(col);
    });
    
    // Evento de selección
    $('.icon-option').click(function() {
        $('.icon-option').removeClass('border-primary bg-light');
        $(this).addClass('border-primary bg-light');
        iconoSeleccionado = $(this).data('icono');
    });
}

function seleccionarIcono() {
    if (iconoSeleccionado) {
        $('#catIcono').val(iconoSeleccionado);
        actualizarPreview();
        bootstrap.Modal.getInstance(document.getElementById('modalIconos')).hide();
    }
}

function actualizarPreview() {
    const nombre = $('#catNombre').val() || 'Nueva Categoría';
    const icono = $('#catIcono').val() || 'fas fa-box';
    const color = $('#catColor').val() || '#007bff';
    
    $('#previewNombre').text(nombre);
    $('#categoriaPreview').css('background-color', color);
    $('#categoriaPreview').html(`<i class="${icono} me-2"></i><span id="previewNombre">${nombre}</span>`);
}

function guardarCategoria(e) {
    e.preventDefault();
    
    const categoria = {
        nombre: $('#catNombre').val(),
        icono: $('#catIcono').val(),
        color: $('#catColor').val()
    };
    
    console.log('Enviando categoría:', categoria);
    
    $.ajax({
        url: <?php echo json_encode($WB . '/api/endpoints/categorias.php'); ?>,
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(categoria),
        success: function(response) {
            console.log('Respuesta exitosa:', response);
            showNotification('Categoría guardada exitosamente', 'success');
            
            // Cerrar modal de categoría
            bootstrap.Modal.getInstance(document.getElementById('modalCategoria')).hide();
            
            // Actualizar categorías y seleccionar la nueva categoría
            actualizarCategoriasYSeleccionar(categoria.nombre);
        },
        error: function(xhr) {
            console.error('Error completo:', xhr);
            console.error('Status:', xhr.status);
            console.error('Response Text:', xhr.responseText);
            
            let errorMessage = 'Error al guardar categoría';
            
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = response.message || errorMessage;
            } catch (e) {
                console.error('Error parsing JSON:', e);
                errorMessage = xhr.responseText || errorMessage;
            }
            
            showNotification(errorMessage, 'error');
        }
    });
}

function actualizarCategoriasYSeleccionar(nuevaCategoria = null) {
    // Cargar categorías actualizadas desde la API
    $.get(<?php echo json_encode($WB . '/api/endpoints/categorias.php'); ?>, function(response) {
        if (response.success && response.data) {
            // Actualizar el select del filtro
            const filtroSelect = $('#filtroCategoria');
            const filtroCurrentValue = filtroSelect.val();
            
            filtroSelect.find('option:not(:first)').remove();
            
            response.data.forEach(categoria => {
                const option = $('<option></option>')
                    .val(categoria.nombre)
                    .text(ucfirst(categoria.nombre));
                filtroSelect.append(option);
            });
            
            // Restaurar la selección anterior
            filtroSelect.val(filtroCurrentValue);
            
            // Actualizar el select del modal de producto
            const modalSelect = $('#categoria');
            const modalCurrentValue = modalSelect.val();
            
            // Guardar el HTML actual del select
            const modalHTML = modalSelect.html();
            
            // Limpiar y actualizar opciones
            modalSelect.find('option:not(:first)').remove();
            
            response.data.forEach(categoria => {
                const option = $('<option></option>')
                    .val(categoria.nombre)
                    .text(ucfirst(categoria.nombre));
                modalSelect.append(option);
            });
            
            // Si se especificó una nueva categoría, seleccionarla
            if (nuevaCategoria) {
                modalSelect.val(nuevaCategoria);
                showNotification(`Categoría "${nuevaCategoria}" seleccionada`, 'success');
            } else {
                // Restaurar la selección anterior si existe
                if (modalCurrentValue && response.data.find(c => c.nombre === modalCurrentValue)) {
                    modalSelect.val(modalCurrentValue);
                }
            }
            
            // Forzar la actualización del select en Bootstrap
            modalSelect.trigger('change');
            
            console.log('Categorías actualizadas en el modal:', response.data.map(c => c.nombre));
        }
    }).fail(function() {
        showNotification('Error al actualizar categorías', 'error');
    });
}

function actualizarCategorias() {
    actualizarCategoriasYSeleccionar();
}

function recargarCategorias() {
    // Ya no recarga la página, solo actualiza las categorías
    actualizarCategorias();
}

// Event listeners para actualización en tiempo real del preview
$(document).ready(function() {
    $('#catNombre').on('input', actualizarPreview);
    $('#catIcono').on('input', actualizarPreview);
    $('#catColor').on('input', function() {
        $('#catColorText').val($(this).val());
        actualizarPreview();
    });
    $('#catColorText').on('input', function() {
        $('#catColor').val($(this).val());
        actualizarPreview();
    });
});
</script>

<?php include __DIR__ . '/../../../backend/includes/footer.php'; ?>
