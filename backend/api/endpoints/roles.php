<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/database.php';

// Verificar sesión de usuario
// // session_start(); // Ya iniciada en router; // Ya iniciada en router
if (!isset($_SESSION['usuario'])) {
    http_response_code(401); echo json_encode(['error' => 'No autorizado']); exit;
}

// Solo admin puede gestionar roles
if ($_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: index.php');
    exit;
}



?>

<div class="main-content">
    <div id="notification-container"></div>
    
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Gestión de Roles y Permisos</h1>
                <p class="text-muted mb-0">Administra los roles y permisos del sistema</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRol" onclick="abrirModalNuevo()">
                <i class="fas fa-plus me-2"></i>Nuevo Rol
            </button>
        </div>
    </div>

    <!-- Lista de Roles -->
    <div class="row">
        <div class="col-12">
            <div id="rolesList">
                <!-- Los roles se cargarán dinámicamente aquí -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Rol -->
<div class="modal fade" id="modalRol" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formRol" onsubmit="guardarRol(event)">
                <div class="modal-body">
                    <input type="hidden" id="rol_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre del Rol *</label>
                            <input type="text" class="form-control" id="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nivel de Acceso *</label>
                            <select class="form-select" id="nivel_acceso" required>
                                <option value="1">Básico (1)</option>
                                <option value="10">Intermedio (10)</option>
                                <option value="50">Avanzado (50)</option>
                                <option value="100">Administrador (100)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="activo" checked>
                            <label class="form-check-label" for="activo">
                                Rol Activo
                            </label>
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-3">Permisos del Rol</h6>
                    <div id="permisosContainer">
                        <!-- Los permisos se cargarán dinámicamente aquí -->
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
let roles = [];
let permisosDisponibles = [];

$(document).ready(function() {
    cargarRoles();
    cargarPermisos();
});

function cargarRoles() {
    $.get('api/endpoints/roles.php', function(data) {
        const list = $('#rolesList');
        list.empty();

        const rolesList = Array.isArray(data) ? data : (data.records || []);
        roles = rolesList;

        rolesList.forEach((rol, index) => {
            const estadoBadge = rol.activo ? 'success' : 'secondary';
            const estadoTexto = rol.activo ? 'Activo' : 'Inactivo';
            
            const rolCard = `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">${rol.nombre}</h5>
                            <span class="badge bg-${estadoBadge}">${estadoTexto}</span>
                        </div>
                        <div class="card-body">
                            <p class="card-text">${rol.descripcion || 'Sin descripción'}</p>
                            <div class="mb-2">
                                <small class="text-muted">Nivel de acceso: ${rol.nivel_acceso}</small>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Usuarios asignados: ${rol.usuarios_count}</small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="editarRol(${rol.id})">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="verPermisos(${rol.id})">
                                    <i class="fas fa-key"></i> Permisos
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarRol(${rol.id})">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            list.append(rolCard);
        });

        if (rolesList.length === 0) {
            list.append('<div class="col-12"><div class="alert alert-info">No hay roles registrados.</div></div>');
        }
    });
}

function cargarPermisos() {
    $.get('api/endpoints/permisos.php', function(data) {
        permisosDisponibles = data;
    });
}

function abrirModalNuevo() {
    $('#modalTitle').text('Nuevo Rol');
    $('#formRol')[0].reset();
    $('#rol_id').val('');
    renderizarPermisos([]);
    $('#modalRol').modal('show');
}

function editarRol(id) {
    console.log('Editing role:', id);
    console.log('Available roles:', roles);
    
    const rol = roles.find(r => r.id === id);
    if (!rol) {
        console.error('Role not found with id:', id);
        return;
    }
    
    console.log('Role found:', rol);
    
    $('#modalTitle').text('Editar Rol');
    $('#rol_id').val(rol.id);
    $('#nombre').val(rol.nombre);
    $('#descripcion').val(rol.descripcion || '');
    $('#nivel_acceso').val(rol.nivel_acceso || 1);
    $('#activo').prop('checked', rol.activo);

    // Cargar permisos del rol
    console.log('Loading permissions for role:', id);
    $.get(`api/endpoints/roles.php?id=${id}`, function(data) {
        console.log('Permissions data received:', data);
        renderizarPermisos(data.permisos || []);
        $('#modalRol').modal('show');
    }).fail(function(xhr, status, error) {
        console.error('Error loading permissions:', xhr.responseText);
        showNotification('Error al cargar permisos del rol', 'error');
    });
}

function renderizarPermisos(permisosAsignados) {
    const container = $('#permisosContainer');
    container.empty();

    const permisosAsignadosIds = permisosAsignados.map(p => p.id);

    permisosDisponibles.forEach(modulo => {
        const moduloHtml = `
            <div class="mb-4">
                <h6 class="text-primary">
                    <i class="${modulo.icono} me-2"></i>${modulo.nombre}
                </h6>
                <div class="row">
                    ${modulo.permisos.map(permiso => `
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input permiso-checkbox" 
                                       type="checkbox" 
                                       id="permiso_${permiso.id}" 
                                       value="${permiso.id}"
                                       ${permisosAsignadosIds.includes(permiso.id) ? 'checked' : ''}>
                                <label class="form-check-label" for="permiso_${permiso.id}">
                                    ${permiso.nombre}
                                </label>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        container.append(moduloHtml);
    });
}

function guardarRol(e) {
    e.preventDefault();
    
    const id = $('#rol_id').val();
    const nombre = $('#nombre').val();
    const descripcion = $('#descripcion').val();
    const nivel_acceso = $('#nivel_acceso').val();
    const activo = $('#activo').is(':checked');
    
    // Obtener permisos seleccionados
    const permisos_ids = [];
    $('.permiso-checkbox:checked').each(function() {
        permisos_ids.push(parseInt($(this).val()));
    });

    const data = {
        nombre: nombre,
        descripcion: descripcion,
        nivel_acceso: parseInt(nivel_acceso),
        activo: activo,
        permisos_ids: permisos_ids
    };

    if (id) {
        data.id = parseInt(id);
    }

    const url = 'api/endpoints/roles.php';
    const method = id ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        type: method,
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            $('#modalRol').modal('hide');
            showNotification(response.message || 'Rol guardado exitosamente', 'success');
            cargarRoles();
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Error al guardar rol';
            showNotification(errorMsg, 'error');
        }
    });
}

function verPermisos(id) {
    console.log('Viewing permissions for role:', id);
    console.log('Available roles:', roles);
    
    const rol = roles.find(r => r.id === id);
    if (!rol) {
        console.error('Role not found with id:', id);
        return;
    }
    
    console.log('Role found:', rol);
    
    $.get(`api/endpoints/roles.php?id=${id}`, function(data) {
        console.log('Permissions data received:', data);
        
        let permisosHtml = `<h6>Permisos del rol: ${rol.nombre}</h6>`;
        
        if (data.permisos && data.permisos.length > 0) {
            permisosHtml += '<ul class="list-group">';
            data.permisos.forEach(permiso => {
                permisosHtml += `<li class="list-group-item">${permiso.nombre}</li>`;
            });
            permisosHtml += '</ul>';
        } else {
            permisosHtml += '<p class="text-muted">Este rol no tiene permisos asignados.</p>';
        }

        console.log('Showing modal with content:', permisosHtml);
        showModal('Permisos del Rol', permisosHtml);
    }).fail(function(xhr, status, error) {
        console.error('Error loading permissions:', xhr.responseText);
        showNotification('Error al cargar permisos del rol', 'error');
    });
}

function eliminarRol(id) {
    if (!confirm('¿Está seguro de eliminar este rol? Esta acción no se puede deshacer.')) {
        return;
    }

    $.ajax({
        url: 'api/endpoints/roles.php',
        type: 'DELETE',
        contentType: 'application/json',
        data: JSON.stringify({id: id}),
        success: function(response) {
            showNotification(response.message || 'Rol eliminado exitosamente', 'success');
            cargarRoles();
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Error al eliminar rol';
            showNotification(errorMsg, 'error');
        }
    });
}

function showModal(title, content) {
    const modalHtml = `
        <div class="modal fade" id="modalPermisos" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        ${content}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Eliminar modal existente si hay uno
    $('#modalPermisos').remove();
    
    // Agregar nuevo modal y mostrarlo
    $('body').append(modalHtml);
    $('#modalPermisos').modal('show');
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

function showModal(title, content) {
    console.log('Showing modal:', title);
    
    // Eliminar modal anterior si existe
    $('#modalPermisosView').modal('dispose');
    $('#modalPermisosView').remove();
    
    // Crear un modal dinámico
    const modalHtml = `
        <div class="modal fade" id="modalPermisosView" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        ${content}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Agregar el nuevo modal al body
    $('body').append(modalHtml);
    
    // Mostrar el modal
    setTimeout(() => {
        const modalElement = document.getElementById('modalPermisosView');
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
        
        // Limpiar cuando se cierra
        modalElement.addEventListener('hidden.bs.modal', function () {
            $(modalElement).remove();
        });
    }, 100);
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
