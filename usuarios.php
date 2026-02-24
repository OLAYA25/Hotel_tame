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
            <button class="btn btn-primary" onclick="abrirModalNuevo()">
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

                    <div class="mb-3">
                        <label class="form-label">Nacionalidad</label>
                        <select class="form-control" id="nacionalidad">
                            <option value="">Seleccione un país...</option>
                        </select>
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
    console.log('Abriendo modal de nuevo usuario...');
    $('#modalTitle').text('Nuevo Usuario');
    $('#formUsuario')[0].reset();
    $('#usuario_id').val('');
    $('#password').prop('required', true);
    $('#passwordField').show();
    $('#password').attr('placeholder', 'Contraseña'); // Placeholder para nuevo usuario
    
    // Abrir el modal con la API de Bootstrap 5
    const modalElement = document.getElementById('modalUsuario');
    const modal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: true
    });
    modal.show();
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
        $('#nacionalidad').val(usuario.nacionalidad || '').trigger('change');
        
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
        
        const modalElement = document.getElementById('modalUsuario');
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: true
        });
        modal.show();
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
        nacionalidad: $('#nacionalidad').val(),
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

// Lista de países con banderas
const paises = [
    { id: 'AF', text: '🇦🇫 Afganistán' },
    { id: 'AL', text: '🇦🇱 Albania' },
    { id: 'DE', text: '🇩🇪 Alemania' },
    { id: 'AD', text: '🇦🇩 Andorra' },
    { id: 'AO', text: '🇦🇴 Angola' },
    { id: 'AI', text: '🇦🇮 Anguila' },
    { id: 'AQ', text: '🇦🇶 Antártida' },
    { id: 'AG', text: '🇦🇬 Antigua y Barbuda' },
    { id: 'SA', text: '🇸🇦 Arabia Saudita' },
    { id: 'DZ', text: '🇩🇿 Argelia' },
    { id: 'AR', text: '🇦🇷 Argentina' },
    { id: 'AM', text: '🇦🇲 Armenia' },
    { id: 'AW', text: '🇦🇼 Aruba' },
    { id: 'AU', text: '🇦🇺 Australia' },
    { id: 'AT', text: '🇦🇹 Austria' },
    { id: 'AZ', text: '🇦🇿 Azerbaiyán' },
    { id: 'BS', text: '🇧🇸 Bahamas' },
    { id: 'BD', text: '🇧🇩 Bangladés' },
    { id: 'BB', text: '🇧🇧 Barbados' },
    { id: 'BH', text: '🇧🇭 Baréin' },
    { id: 'BE', text: '🇧🇪 Bélgica' },
    { id: 'BZ', text: '🇧🇿 Belice' },
    { id: 'BJ', text: '🇧🇯 Benín' },
    { id: 'BM', text: '🇧🇲 Bermudas' },
    { id: 'BY', text: '🇧🇾 Bielorrusia' },
    { id: 'BO', text: '🇧🇴 Bolivia' },
    { id: 'BA', text: '🇧🇦 Bosnia y Herzegovina' },
    { id: 'BW', text: '🇧🇼 Botsuana' },
    { id: 'BR', text: '🇧🇷 Brasil' },
    { id: 'BN', text: '🇧🇳 Brunéi' },
    { id: 'BG', text: '🇧🇬 Bulgaria' },
    { id: 'BF', text: '🇧🇫 Burkina Faso' },
    { id: 'BI', text: '🇧🇮 Burundi' },
    { id: 'BT', text: '🇧🇹 Bután' },
    { id: 'CV', text: '🇨🇻 Cabo Verde' },
    { id: 'KH', text: '🇰🇭 Camboya' },
    { id: 'CM', text: '🇨🇲 Camerún' },
    { id: 'CA', text: '🇨🇦 Canadá' },
    { id: 'BQ', text: '🇧🇶 Caribe Neerlandés' },
    { id: 'QA', text: '🇶🇦 Catar' },
    { id: 'TD', text: '🇹🇩 Chad' },
    { id: 'CZ', text: '🇨🇿 Chequia' },
    { id: 'CL', text: '🇨🇱 Chile' },
    { id: 'CN', text: '🇨🇳 China' },
    { id: 'CY', text: '🇨🇾 Chipre' },
    { id: 'VA', text: '🇻🇦 Ciudad del Vaticano' },
    { id: 'CO', text: '🇨🇴 Colombia' },
    { id: 'KM', text: '🇰🇲 Comoras' },
    { id: 'CG', text: '🇨🇬 Congo' },
    { id: 'CD', text: '🇨🇩 Congo Democrático' },
    { id: 'KP', text: '🇰🇵 Corea del Norte' },
    { id: 'KR', text: '🇰🇷 Corea del Sur' },
    { id: 'CI', text: '🇨🇮 Costa de Marfil' },
    { id: 'CR', text: '🇨🇷 Costa Rica' },
    { id: 'HR', text: '🇭🇷 Croacia' },
    { id: 'CU', text: '🇨🇺 Cuba' },
    { id: 'DK', text: '🇩🇰 Dinamarca' },
    { id: 'DM', text: '🇩🇲 Dominica' },
    { id: 'EC', text: '🇪🇨 Ecuador' },
    { id: 'EG', text: '🇪🇬 Egipto' },
    { id: 'SV', text: '🇸🇻 El Salvador' },
    { id: 'AE', text: '🇦🇪 Emiratos Árabes Unidos' },
    { id: 'ER', text: '🇪🇷 Eritrea' },
    { id: 'SK', text: '🇸🇰 Eslovaquia' },
    { id: 'SI', text: '🇸🇮 Eslovenia' },
    { id: 'ES', text: '🇪🇸 España' },
    { id: 'US', text: '🇺🇸 Estados Unidos' },
    { id: 'EE', text: '🇪🇪 Estonia' },
    { id: 'SZ', text: '🇸🇿 Esuatini' },
    { id: 'ET', text: '🇪🇹 Etiopía' },
    { id: 'PH', text: '🇵🇭 Filipinas' },
    { id: 'FI', text: '🇫🇮 Finlandia' },
    { id: 'FJ', text: '🇫🇯 Fiyi' },
    { id: 'FR', text: '🇫🇷 Francia' },
    { id: 'GA', text: '🇬🇦 Gabón' },
    { id: 'GM', text: '🇬🇲 Gambia' },
    { id: 'GE', text: '🇬🇪 Georgia' },
    { id: 'GH', text: '🇬🇭 Ghana' },
    { id: 'GI', text: '🇬🇮 Gibraltar' },
    { id: 'GD', text: '🇬🇩 Granada' },
    { id: 'GR', text: '🇬🇷 Grecia' },
    { id: 'GL', text: '🇬🇱 Groenlandia' },
    { id: 'GP', text: '🇬🇵 Guadalupe' },
    { id: 'GU', text: '🇬🇺 Guam' },
    { id: 'GT', text: '🇬🇹 Guatemala' },
    { id: 'GF', text: '🇬🇫 Guayana Francesa' },
    { id: 'GG', text: '🇬🇬 Guernsey' },
    { id: 'GN', text: '🇬🇳 Guinea' },
    { id: 'GQ', text: '🇬🇶 Guinea Ecuatorial' },
    { id: 'GW', text: '🇬🇼 Guinea-Bisáu' },
    { id: 'GY', text: '🇬🇾 Guyana' },
    { id: 'HT', text: '🇭🇹 Haití' },
    { id: 'HN', text: '🇭🇳 Honduras' },
    { id: 'HK', text: '🇭🇰 Hong Kong' },
    { id: 'HU', text: '🇭🇺 Hungría' },
    { id: 'IN', text: '🇮🇳 India' },
    { id: 'ID', text: '🇮🇩 Indonesia' },
    { id: 'IQ', text: '🇮🇶 Irak' },
    { id: 'IR', text: '🇮🇷 Irán' },
    { id: 'IE', text: '🇮🇪 Irlanda' },
    { id: 'IM', text: '🇮🇲 Isla de Man' },
    { id: 'IS', text: '🇮🇸 Islandia' },
    { id: 'IL', text: '🇮🇱 Israel' },
    { id: 'IT', text: '🇮🇹 Italia' },
    { id: 'JM', text: '🇯🇲 Jamaica' },
    { id: 'JP', text: '🇯🇵 Japón' },
    { id: 'JE', text: '🇯🇪 Jersey' },
    { id: 'JO', text: '🇯🇴 Jordania' },
    { id: 'KZ', text: '🇰🇿 Kazajistán' },
    { id: 'KE', text: '🇰🇪 Kenia' },
    { id: 'KG', text: '🇰🇬 Kirguistán' },
    { id: 'KI', text: '🇰🇮 Kiribati' },
    { id: 'KW', text: '🇰🇼 Kuwait' },
    { id: 'LA', text: '🇱🇦 Laos' },
    { id: 'LS', text: '🇱🇸 Lesoto' },
    { id: 'LV', text: '🇱🇻 Letonia' },
    { id: 'LB', text: '🇱🇧 Líbano' },
    { id: 'LR', text: '🇱🇷 Liberia' },
    { id: 'LY', text: '🇱🇾 Libia' },
    { id: 'LI', text: '🇱🇮 Liechtenstein' },
    { id: 'LT', text: '🇱🇹 Lituania' },
    { id: 'LU', text: '🇱🇺 Luxemburgo' },
    { id: 'MO', text: '🇲🇴 Macao' },
    { id: 'MK', text: '🇲🇰 Macedonia del Norte' },
    { id: 'MG', text: '🇲🇬 Madagascar' },
    { id: 'MY', text: '🇲🇾 Malasia' },
    { id: 'MW', text: '🇲🇼 Malaui' },
    { id: 'MV', text: '🇲🇻 Maldivas' },
    { id: 'ML', text: '🇲🇱 Malí' },
    { id: 'MT', text: '🇲🇹 Malta' },
    { id: 'MA', text: '🇲🇦 Marruecos' },
    { id: 'MQ', text: '🇲🇶 Martinica' },
    { id: 'MU', text: '🇲🇺 Mauricio' },
    { id: 'MR', text: '🇲🇷 Mauritania' },
    { id: 'YT', text: '🇾🇹 Mayotte' },
    { id: 'MX', text: '🇲🇽 México' },
    { id: 'FM', text: '🇫🇲 Micronesia' },
    { id: 'MD', text: '🇲🇩 Moldavia' },
    { id: 'MC', text: '🇲🇨 Mónaco' },
    { id: 'MN', text: '🇲🇳 Mongolia' },
    { id: 'ME', text: '🇲🇪 Montenegro' },
    { id: 'MS', text: '🇲🇸 Montserrat' },
    { id: 'MZ', text: '🇲🇿 Mozambique' },
    { id: 'MM', text: '🇲🇲 Myanmar' },
    { id: 'NA', text: '🇳🇦 Namibia' },
    { id: 'NR', text: '🇳🇷 Nauru' },
    { id: 'NP', text: '🇳🇵 Nepal' },
    { id: 'NI', text: '🇳🇮 Nicaragua' },
    { id: 'NE', text: '🇳🇪 Níger' },
    { id: 'NG', text: '🇳🇬 Nigeria' },
    { id: 'NU', text: '🇳🇺 Niue' },
    { id: 'NO', text: '🇳🇴 Noruega' },
    { id: 'NC', text: '🇳🇨 Nueva Caledonia' },
    { id: 'NZ', text: '🇳🇿 Nueva Zelanda' },
    { id: 'OM', text: '🇴🇲 Omán' },
    { id: 'NL', text: '🇳🇱 Países Bajos' },
    { id: 'PK', text: '🇵🇰 Pakistán' },
    { id: 'PW', text: '🇵🇼 Palaos' },
    { id: 'PA', text: '🇵🇦 Panamá' },
    { id: 'PG', text: '🇵🇬 Papúa Nueva Guinea' },
    { id: 'PY', text: '🇵🇾 Paraguay' },
    { id: 'PE', text: '🇵🇪 Perú' },
    { id: 'PF', text: '🇵🇫 Polinesia Francesa' },
    { id: 'PL', text: '🇵🇱 Polonia' },
    { id: 'PT', text: '🇵🇹 Portugal' },
    { id: 'PR', text: '🇵🇷 Puerto Rico' },
    { id: 'GB', text: '🇬🇧 Reino Unido' },
    { id: 'CF', text: '🇨🇫 República Centroafricana' },
    { id: 'DO', text: '🇩🇴 República Dominicana' },
    { id: 'RE', text: '🇷🇪 Reunión' },
    { id: 'RW', text: '🇷🇼 Ruanda' },
    { id: 'RO', text: '🇷🇴 Rumanía' },
    { id: 'RU', text: '🇷🇺 Rusia' },
    { id: 'EH', text: '🇪🇭 Sáhara Occidental' },
    { id: 'BL', text: '🇧🇱 San Bartolomé' },
    { id: 'KN', text: '🇰🇳 San Cristóbal y Nieves' },
    { id: 'SM', text: '🇸🇲 San Marino' },
    { id: 'MF', text: '🇲🇫 San Martín' },
    { id: 'PM', text: '🇵🇲 San Pedro y Miquelón' },
    { id: 'VC', text: '🇻🇨 San Vicente y las Granadinas' },
    { id: 'WS', text: '🇼🇸 Samoa' },
    { id: 'AS', text: '🇦🇸 Samoa Americana' },
    { id: 'LC', text: '🇱🇨 Santa Lucía' },
    { id: 'ST', text: '🇸🇹 Santo Tomé y Príncipe' },
    { id: 'SN', text: '🇸🇳 Senegal' },
    { id: 'RS', text: '🇷🇸 Serbia' },
    { id: 'SC', text: '🇸🇨 Seychelles' },
    { id: 'SL', text: '🇸🇱 Sierra Leona' },
    { id: 'SG', text: '🇸🇬 Singapur' },
    { id: 'SX', text: '🇸🇽 Sint Maarten' },
    { id: 'SY', text: '🇸🇾 Siria' },
    { id: 'SO', text: '🇸🇴 Somalia' },
    { id: 'LK', text: '🇱🇰 Sri Lanka' },
    { id: 'ZA', text: '🇿🇦 Sudáfrica' },
    { id: 'SD', text: '🇸🇩 Sudán' },
    { id: 'SS', text: '🇸🇸 Sudán del Sur' },
    { id: 'SE', text: '🇸🇪 Suecia' },
    { id: 'CH', text: '🇨🇭 Suiza' },
    { id: 'SR', text: '🇸🇷 Surinam' },
    { id: 'SJ', text: '🇸🇯 Svalbard y Jan Mayen' },
    { id: 'TH', text: '🇹🇭 Tailandia' },
    { id: 'TW', text: '🇹🇼 Taiwán' },
    { id: 'TZ', text: '🇹🇿 Tanzania' },
    { id: 'TJ', text: '🇹🇯 Tayikistán' },
    { id: 'IO', text: '🇮🇴 Territorio Británico del Océano Índico' },
    { id: 'TF', text: '🇹🇫 Territorios Australes Franceses' },
    { id: 'TL', text: '🇹🇱 Timor-Leste' },
    { id: 'TG', text: '🇹🇬 Togo' },
    { id: 'TK', text: '🇹🇰 Tokelau' },
    { id: 'TO', text: '🇹🇴 Tonga' },
    { id: 'TT', text: '🇹🇹 Trinidad y Tobago' },
    { id: 'TN', text: '🇹🇳 Túnez' },
    { id: 'TM', text: '🇹🇲 Turkmenistán' },
    { id: 'TR', text: '🇹🇷 Turquía' },
    { id: 'TV', text: '🇹🇻 Tuvalu' },
    { id: 'UA', text: '🇺🇦 Ucrania' },
    { id: 'UG', text: '🇺🇬 Uganda' },
    { id: 'UY', text: '🇺🇾 Uruguay' },
    { id: 'UZ', text: '🇺🇿 Uzbekistán' },
    { id: 'VU', text: '🇻🇺 Vanuatu' },
    { id: 'VE', text: '🇻🇪 Venezuela' },
    { id: 'VN', text: '🇻🇳 Vietnam' },
    { id: 'WF', text: '🇼🇫 Wallis y Futuna' },
    { id: 'YE', text: '🇾🇪 Yemen' },
    { id: 'DJ', text: '🇩🇯 Yibuti' },
    { id: 'ZM', text: '🇿🇲 Zambia' },
    { id: 'ZW', text: '🇿🇼 Zimbabue' }
];

// Inicializar Select2 para nacionalidad
$(document).ready(function() {
    $('#nacionalidad').select2({
        data: paises,
        placeholder: 'Seleccione un país...',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#modalUsuario'),
        minimumInputLength: 0,
        language: {
            noResults: function() {
                return 'No se encontraron países';
            },
            searching: function() {
                return 'Buscando...';
            }
        },
        dropdownPosition: 'below',
        // Forzar que el dropdown se abra siempre hacia abajo
        dropdownCssClass: 'select2-dropdown-below'
    });
    
    // Corregir posición del dropdown cuando el modal tiene scroll
    $('#nacionalidad').on('select2:open', function(e) {
        const modal = $('#modalUsuario');
        const modalScrollTop = modal.scrollTop();
        const select2Dropdown = $('.select2-dropdown');
        
        // Esperar a que el dropdown se renderice
        setTimeout(function() {
            const dropdownTop = select2Dropdown.offset().top;
            const selectTop = $('#nacionalidad').offset().top;
            
            // Si el dropdown está arriba del select, corregir posición
            if (dropdownTop < selectTop) {
                const newTop = selectTop - modal.offset().top + $('#nacionalidad').outerHeight() + modalScrollTop;
                select2Dropdown.css({
                    'top': newTop + 'px',
                    'position': 'absolute'
                });
            }
        }, 10);
    });
});
</script>

<style>
/* Estilos para Select2 en el modal de usuarios */
#modalUsuario .select2-container {
    width: 100% !important;
}

#modalUsuario .select2-container--open .select2-dropdown {
    z-index: 1055;
}

#modalUsuario .select2-search--dropdown .select2-search__field {
    width: 100%;
    padding: 8px;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}

#modalUsuario .select2-results__option {
    padding: 8px;
}

#modalUsuario .select2-results__option--highlighted {
    background-color: #0d6efd;
    color: white;
}

/* Forzar que el dropdown siempre aparezca hacia abajo */
#modalUsuario .select2-dropdown.select2-dropdown-below {
    top: auto !important;
    bottom: auto !important;
    margin-top: 1px !important;
}

#modalUsuario .select2-dropdown {
    position: absolute !important;
    z-index: 1055 !important;
}
</style>

<?php include 'includes/footer.php'; ?>
