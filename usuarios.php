<?php
require_once 'config/database.php';

// Verificar sesión de usuario
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Solo admin puede gestionar usuarios
if ($_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: index.php');
    exit;
}
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div id="notification-container"></div>
    
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Usuarios</h1>
                <p class="text-muted mb-0">Gestiona los usuarios del sistema</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUsuario" onclick="abrirModalNuevo()">
                <i class="fas fa-plus me-2"></i>Nuevo Usuario
            </button>
        </div>
    </div>

    <!-- Grid de tarjetas para usuarios -->
    <div class="row g-4" id="usuariosGrid">
        <!-- Las tarjetas se cargarán dinámicamente aquí -->
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formUsuario" onsubmit="guardarUsuario(event)">
                <div class="modal-body">
                    <input type="hidden" id="usuario_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo *</label>
                        <input type="text" class="form-control" id="nombre" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Apellido *</label>
                        <input type="text" class="form-control" id="apellido" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="telefono" placeholder="Ej: +34123456789">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <small class="text-muted d-block">Este se pedirá para iniciar sesión (Usuario)</small>
                        <input type="email" class="form-control" id="email" required>
                    </div>

                    <div class="mb-3" id="passwordField">
                        <label class="form-label">Contraseña *</label>
                        <input type="password" class="form-control" id="password" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rol *</label>
                        <select class="form-select" id="rol" required>
                            <option value="">Seleccione...</option>
                            <option value="admin">Administrador</option>
                            <option value="auxiliar">Auxiliar</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estado *</label>
                        <select class="form-select" id="estado" required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
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
    cargarUsuarios();
});

function cargarUsuarios() {
    $.get('api/endpoints/usuarios.php', function(data) {
        const grid = $('#usuariosGrid');
        grid.empty();

        // La API devuelve { records: [...] } o directamente un array
        const usuariosList = Array.isArray(data) ? data : (data.records || []);

        usuariosList.forEach(usuario => {
            const rolBadge = {
                'admin': 'danger',
                'auxiliar': 'primary'
            }[usuario.rol] || 'secondary';

            const rolTexto = {
                'admin': 'Administrador',
                'auxiliar': 'Auxiliar'
            }[usuario.rol] || usuario.rol;
            
            grid.append(`
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-start mb-3">
                                <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                                    <i class="fas fa-user fs-4"></i>
                                </div>
                                <div class="flex-grow-1">
                                                            <h5 class="card-title mb-1">${usuario.nombre} ${usuario.apellido || ''}</h5>
                                    <span class="badge bg-${rolBadge}">${rolTexto}</span>
                                </div>
                            </div>
                            
                            <div class="info-list">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-envelope text-muted me-2"></i>
                                    <span class="text-muted small">${usuario.email}</span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-phone text-muted me-2"></i>
                                    <span class="text-muted small">${usuario.telefono || 'No especificado'}</span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-user-tag text-muted me-2"></i>
                                    <span class="text-muted small">Rol: ${rolTexto}</span>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2 mt-3">
                                <button class="btn btn-outline-primary btn-sm flex-fill" onclick="editarUsuario(${usuario.id})">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="eliminarUsuario(${usuario.id})">
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
    $('#modalTitle').text('Nuevo Usuario');
    $('#formUsuario')[0].reset();
    $('#usuario_id').val('');
    $('#password').prop('required', true);
    $('#passwordField').show();
    $('#password').attr('placeholder', 'Contraseña'); // Placeholder para nuevo usuario
}

function editarUsuario(id) {
    console.log('Editing user:', id);
    $.get(`api/endpoints/usuarios.php?id=${id}`, function(usuario) {
        console.log('User data received:', usuario);
        $('#modalTitle').text('Editar Usuario');
        $('#usuario_id').val(usuario.id);
        $('#nombre').val(usuario.nombre);
        $('#apellido').val(usuario.apellido || '');
        $('#email').val(usuario.email);
        $('#telefono').val(usuario.telefono || '');
        
        // Limpiar y establecer el rol
        $('#rol').val('');
        setTimeout(() => {
            $('#rol').val(usuario.rol);
            console.log('Role set to:', usuario.rol, 'Current value:', $('#rol').val());
        }, 100);
        
        // El backend envía `activo` (1/0 o true/false)
        $('#estado').val(usuario.activo ? 'activo' : 'inactivo');
        $('#password').prop('required', false);
        $('#password').val(''); // Limpiar campo de contraseña
        $('#passwordField').show(); // Mostrar campo para poder editar contraseña
        
        // Agregar texto indicativo
        $('#password').attr('placeholder', 'Dejar en blanco para mantener la contraseña actual');
        
        $('#modalUsuario').modal('show');
    }).fail(function(xhr, status, error) {
        console.error('Error loading user:', xhr.responseText);
        showNotification('Error al cargar datos del usuario', 'error');
    });
}

function guardarUsuario(e) {
    e.preventDefault();
    
    const id = $('#usuario_id').val();
    const data = {
        nombre: $('#nombre').val(),
        apellido: $('#apellido').val(),
        telefono: $('#telefono').val(),
        email: $('#email').val(),
        rol: $('#rol').val(),
        // Convertir estado legible a activo (0/1)
        activo: ($('#estado').val() === 'activo') ? 1 : 0
    };
    
    if (!id && !$('#password').val()) {
        showNotification('La contraseña es obligatoria para crear un usuario', 'error');
        return;
    }

    if (!id && !$('#apellido').val()) {
        showNotification('El apellido es obligatorio para crear un usuario', 'error');
        return;
    }

    if (!id || $('#password').val()) {
        data.password = $('#password').val();
    }
    if (id) data.id = parseInt(id);

    const url = 'api/endpoints/usuarios.php';
    const method = id ? 'PUT' : 'POST';
    
    $.ajax({
        url: url,
        type: method,
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            $('#modalUsuario').modal('hide');
            showNotification(response.message || 'Usuario guardado exitosamente', 'success');
            cargarUsuarios();
        },
        error: function(xhr) {
            // Mostrar respuesta completa para depuración
            let msg = 'Error al guardar';
            try {
                const json = JSON.parse(xhr.responseText);
                msg = json.message || json.error || xhr.responseText;
            } catch (e) {
                msg = xhr.responseText || msg;
            }
            console.error('Save user error:', xhr.responseText || xhr);
            showNotification(msg, 'error');
        }
    });
}

function eliminarUsuario(id) {
    if (confirm('¿Está seguro de eliminar este usuario?')) {
        $.ajax({
            url: 'api/endpoints/usuarios.php',
            type: 'DELETE',
            contentType: 'application/json',
            data: JSON.stringify({ id: id }),
            success: function(response) {
                showNotification(response.message || 'Usuario eliminado', 'success');
                cargarUsuarios();
            },
            error: function(xhr) {
                showNotification(xhr.responseJSON?.message || 'Error al eliminar', 'error');
            }
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>
