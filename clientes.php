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
    <div id="notification-container"></div>
    
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Clientes</h1>
                <p class="text-muted mb-0">Gestiona los clientes del hotel</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCliente" onclick="abrirModalNuevo()">
                <i class="fas fa-plus me-2"></i>Nuevo Cliente
            </button>
        </div>
    </div>

    <!-- Barra de búsqueda y paginación -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" class="form-control" id="searchInput" placeholder="Buscar por nombre, email, documento..." onkeyup="handleSearch(event)">
            </div>
        </div>
        <div class="col-md-6 text-end">
            <small class="text-muted" id="totalCountInfo">Cargando...</small>
        </div>
    </div>

    <!-- Grid de tarjetas para clientes -->
    <div class="row g-4" id="clientesGrid">
        <!-- Las tarjetas se cargarán dinámicamente aquí -->
    </div>
    
    <!-- Paginación -->
    <nav aria-label="Paginación de clientes" class="mt-4" id="paginationContainer">
        <!-- La paginación se cargará dinámicamente aquí -->
    </nav>
</div>

<!-- Modal -->
<div class="modal fade" id="modalCliente" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCliente" onsubmit="guardarCliente(event)">
                <div class="modal-body">
                    <input type="hidden" id="cliente_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Apellido *</label>
                            <input type="text" class="form-control" id="apellido" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo Documento *</label>
                            <select class="form-select" id="tipo_documento" required>
                                <option value="">Seleccione...</option>
                                <option value="DNI">DNI</option>
                                <option value="Pasaporte">Pasaporte</option>
                                <option value="Cedula">Cedula</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Número Documento *</label>
                            <input type="text" class="form-control" id="numero_documento" required>
                            <input type="hidden" id="numero_documento_full">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="toggleMostrarDocumento" onchange="toggleDocumentoVisibility()">
                                <label class="form-check-label small text-muted" for="toggleMostrarDocumento">Mostrar número</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono *</label>
                            <input type="tel" class="form-control" id="telefono" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Nacimiento *</label>
                            <input type="date" class="form-control" id="fecha_nacimiento" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ciudad *</label>
                            <input type="text" class="form-control" id="ciudad" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nacionalidad *</label>
                            <select class="form-control" id="nacionalidad" required>
                                <option value="">Seleccione un país...</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <label class="form-label">Dirección *</label>
                        <textarea class="form-control" id="direccion" rows="2" required></textarea>
                    </div>

                    <hr>
                    
                    <!-- Sección de Acompañantes -->
                    <!-- <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Acompañantes</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="agregarAcompanante()">
                                <i class="fas fa-plus me-1"></i>Agregar Acompañante
                            </button>
                        </div>
                        <div id="acompanantesContainer" class="border rounded p-3 bg-light">
                            <p class="text-muted mb-0 text-center">No hay acompañantes registrados</p>
                        </div>
                    </div> -->
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
// Variables globales para paginación
let currentPage = 1;
let searchTimeout;
let currentSearch = '';

$(document).ready(function() {
    cargarClientes();
});

function cargarClientes(page = 1, search = '') {
    currentPage = page;
    currentSearch = search;
    
    const url = `api/endpoints/clientes.php?page=${page}&limit=12${search ? '&search=' + encodeURIComponent(search) : ''}`;
    
    $.get(url, function(data) {
        const grid = $('#clientesGrid');
        const paginationContainer = $('#paginationContainer');
        const totalCountInfo = $('#totalCountInfo');
        
        grid.empty();
        
        // La API puede devolver { records: [...] } o directamente un array
        const clientesList = Array.isArray(data) ? data : (data.records || []);
        const pagination = data.pagination || {};
        
        clientesList.forEach(cliente => {
            grid.append(`
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-start mb-3">
                                <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                                    <i class="fas fa-user-circle fs-4"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">${cliente.nombre} ${cliente.apellido}</h5>
                                </div>
                            </div>
                            
                            <div class="info-list">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-envelope text-muted me-2"></i>
                                    <span class="text-muted small">${cliente.email}</span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-phone text-muted me-2"></i>
                                    <span class="text-muted small">${cliente.telefono}</span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-id-card text-muted me-2"></i>
                                    <span class="text-muted small">${cliente.tipo_documento} ${maskDocument(cliente.documento || cliente.numero_documento)}</span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                    <span class="text-muted small">${cliente.direccion || 'N/A'}, ${cliente.ciudad || 'N/A'}, ${cliente.pais || 'N/A'}</span>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2 mt-3">
                                <button class="btn btn-outline-primary btn-sm flex-fill" onclick="editarCliente(${cliente.id})">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="eliminarCliente(${cliente.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });
        
        // Actualizar información de paginación
        if (pagination.total !== undefined) {
            totalCountInfo.text(`Mostrando ${clientesList.length} de ${pagination.total} clientes`);
            renderPagination(pagination);
        } else {
            totalCountInfo.text(`${clientesList.length} clientes`);
            paginationContainer.empty();
        }
    });
}

function renderPagination(pagination) {
    const container = $('#paginationContainer');
    
    if (!pagination || pagination.pages <= 1) {
        container.empty();
        return;
    }
    
    let html = '<ul class="pagination justify-content-center">';
    
    // Botón anterior
    if (pagination.has_prev) {
        html += `<li class="page-item">
                    <a class="page-link" href="#" onclick="cargarClientes(${pagination.page - 1}, '${currentSearch}'); return false;">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                 </li>`;
    } else {
        html += `<li class="page-item disabled">
                    <span class="page-link">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                 </li>`;
    }
    
    // Páginas
    for (let i = 1; i <= pagination.pages; i++) {
        if (i === pagination.page) {
            html += `<li class="page-item active">
                        <span class="page-link">${i}</span>
                     </li>`;
        } else {
            html += `<li class="page-item">
                        <a class="page-link" href="#" onclick="cargarClientes(${i}, '${currentSearch}'); return false;">${i}</a>
                     </li>`;
        }
    }
    
    // Botón siguiente
    if (pagination.has_next) {
        html += `<li class="page-item">
                    <a class="page-link" href="#" onclick="cargarClientes(${pagination.page + 1}, '${currentSearch}'); return false;">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                 </li>`;
    } else {
        html += `<li class="page-item disabled">
                    <span class="page-link">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                 </li>`;
    }
    
    html += '</ul>';
    container.html(html);
}

function handleSearch(event) {
    // Limpiar timeout anterior
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    // Esperar 500ms después de que el usuario deje de escribir
    searchTimeout = setTimeout(() => {
        const searchTerm = $('#searchInput').val().trim();
        cargarClientes(1, searchTerm);
    }, 500);
}

function abrirModalNuevo() {
    $('#modalTitle').text('Nuevo Cliente');
    $('#formCliente')[0].reset();
    $('#cliente_id').val('');
}

function editarCliente(id) {
    $.get(`api/endpoints/clientes_final.php?id=${id}`, function(cliente) {
        $('#modalTitle').text('Editar Cliente');
        $('#cliente_id').val(cliente.id);
        $('#nombre').val(cliente.nombre);
        $('#apellido').val(cliente.apellido);
        $('#fecha_nacimiento').val(cliente.fecha_nacimiento || '');
        $('#tipo_documento').val(cliente.tipo_documento);
        // Guardar el número completo en campo oculto y mostrar versión enmascarada
        $('#numero_documento_full').val(cliente.documento || cliente.numero_documento || '');
        $('#numero_documento').val(maskDocument(cliente.documento || cliente.numero_documento || ''));
        $('#toggleMostrarDocumento').prop('checked', false);
        $('#email').val(cliente.email);
        $('#telefono').val(cliente.telefono);
        $('#ciudad').val(cliente.ciudad);
        $('#nacionalidad').val(cliente.nacionalidad || cliente.pais || '').trigger('change');
        $('#direccion').val(cliente.direccion);
        const modalElement = document.getElementById('modalCliente');
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: true
        });
        modal.show();
    });
}

function guardarCliente(e) {
    e.preventDefault();
    
    const id = $('#cliente_id').val();
    
    // Obtener acompañantes del formulario
    const acompanantes = obtenerAcompanantes();
    
    const data = {
        nombre: $('#nombre').val(),
        apellido: $('#apellido').val(),
        fecha_nacimiento: $('#fecha_nacimiento').val(),
        tipo_documento: $('#tipo_documento').val(),
        // Si el input visible está enmascarado (contiene '*'), usar el valor real del campo oculto
        numero_documento: ($('#numero_documento').val().indexOf('*') !== -1) ? $('#numero_documento_full').val() : $('#numero_documento').val(),
        email: $('#email').val(),
        telefono: $('#telefono').val(),
        ciudad: $('#ciudad').val(),
        nacionalidad: $('#nacionalidad').val(),
        direccion: $('#direccion').val(),
        acompanantes: acompanantes // Agregar acompañantes al envío
    };
    
    if (id) data.id = parseInt(id);

    const url = 'api/endpoints/clientes_simple_post.php';
    const method = id ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        type: method,
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            $('#modalCliente').modal('hide');
            showNotification(response.message || 'Cliente guardado exitosamente', 'success');
            cargarClientes();
        },
        error: function(xhr) {
            let msg = 'Error al guardar';
            try {
                const json = JSON.parse(xhr.responseText);
                
                // Manejar específicamente el error de documento duplicado
                if (xhr.status === 409 && json.error === 'duplicate_document') {
                    msg = 'Ya existe un cliente con este documento. Por favor, verifique los datos.';
                } else {
                    msg = json.message || json.error || xhr.responseText;
                }
            } catch (e) {
                msg = xhr.responseText || msg;
            }
            console.error('Save client error:', xhr.responseText || xhr);
            showNotification(msg, 'error');
        }
    });
}

function eliminarCliente(id) {
    if (confirm('¿Está seguro de eliminar este cliente?')) {
        $.ajax({
            url: `api/endpoints/clientes_final.php?id=${id}`,
            type: 'DELETE',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify({ id: id }),
            success: function(response) {
                showNotification(response.message || 'Cliente eliminado', 'success');
                cargarClientes();
            },
            error: function(xhr) {
                let msg = 'Error al eliminar';
                try {
                    const json = JSON.parse(xhr.responseText);
                    msg = json.message || json.error || xhr.responseText;
                } catch (e) {
                    msg = xhr.responseText || msg;
                }
                console.error('Delete client error:', xhr.responseText || xhr);
                showNotification(msg, 'error');
            }
        });
    }
}

// Funciones para manejar acompañantes
let acompananteCount = 0;

function agregarAcompanante() {
    acompananteCount++;
    const container = document.getElementById('acompanantesContainer');
    
    // Limpiar mensaje inicial si existe
    if (acompananteCount === 1) {
        container.innerHTML = '';
    }
    
    const acompananteHtml = `
        <div class="acompanante-item border rounded p-3 mb-2 bg-white" id="acompanante-${acompananteCount}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Acompañante ${acompananteCount}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarAcompanante(${acompananteCount})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label">Nombre *</label>
                    <input type="text" class="form-control" name="acompanante_nombre_${acompananteCount}" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Apellido *</label>
                    <input type="text" class="form-control" name="acompanante_apellido_${acompananteCount}" required>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">Parentesco</label>
                    <select class="form-select" name="acompanante_parentesco_${acompananteCount}">
                        <option value="Hijo(a)">Hijo(a)</option>
                        <option value="Cónyuge">Cónyuge</option>
                        <option value="Hermano(a)">Hermano(a)</option>
                        <option value="Amigo(a)">Amigo(a)</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">Fecha Nac.</label>
                    <input type="date" class="form-control" name="acompanante_fn_${acompananteCount}" onchange="calcularEdad(${acompananteCount})">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">Edad</label>
                    <input type="number" class="form-control" name="acompanante_edad_${acompananteCount}" readonly placeholder="Auto">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label">Tipo Doc. *</label>
                    <select class="form-select" name="acompanante_tipo_doc_${acompananteCount}" required>
                        <option value="">Seleccione...</option>
                        <option value="DNI">DNI</option>
                        <option value="Pasaporte">Pasaporte</option>
                        <option value="Cedula">Cedula</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Número Doc. *</label>
                    <input type="text" class="form-control" name="acompanante_num_doc_${acompananteCount}" required>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', acompananteHtml);
}

function eliminarAcompanante(id) {
    const element = document.getElementById(`acompanante-${id}`);
    if (element) {
        element.remove();
    }
    
    // Si no hay más acompañantes, mostrar mensaje inicial
    const container = document.getElementById('acompanantesContainer');
    if (container.children.length === 0) {
        container.innerHTML = '<p class="text-muted mb-0 text-center">No hay acompañantes registrados</p>';
    }
}

function calcularEdad(id) {
    const fechaNac = document.querySelector(`input[name="acompanante_fn_${id}"]`).value;
    const edadInput = document.querySelector(`input[name="acompanante_edad_${id}"]`);
    
    if (fechaNac) {
        const hoy = new Date();
        const nacimiento = new Date(fechaNac);
        let edad = hoy.getFullYear() - nacimiento.getFullYear();
        const mes = hoy.getMonth() - nacimiento.getMonth();
        
        if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
            edad--;
        }
        
        edadInput.value = edad;
    } else {
        edadInput.value = '';
    }
}

function obtenerAcompanantes() {
    const acompanantes = [];
    const items = document.querySelectorAll('.acompanante-item');
    
    items.forEach((item, index) => {
        const id = item.id.replace('acompanante-', '');
        const nombre = document.querySelector(`input[name="acompanante_nombre_${id}"]`).value;
        const apellido = document.querySelector(`input[name="acompanante_apellido_${id}"]`).value;
        const parentesco = document.querySelector(`select[name="acompanante_parentesco_${id}"]`).value;
        const fechaNac = document.querySelector(`input[name="acompanante_fn_${id}"]`).value;
        const tipoDoc = document.querySelector(`select[name="acompanante_tipo_doc_${id}"]`).value;
        const numDoc = document.querySelector(`input[name="acompanante_num_doc_${id}"]`).value;
        
        if (nombre && apellido && tipoDoc && numDoc) {
            acompanantes.push({
                nombre: nombre,
                apellido: apellido,
                parentesco: parentesco,
                fecha_nacimiento: fechaNac,
                tipo_documento: tipoDoc,
                numero_documento: numDoc
            });
        }
    });
    
    return acompanantes;
}

// Utility: enmascara un número mostrando sólo los últimos 4 caracteres
function maskDocument(doc) {
    if (!doc) return '';
    const s = String(doc);
    if (s.length <= 4) return '****' + s;
    return '****' + s.slice(-4);
}

// Toggle para mostrar/ocultar documento en el modal
function toggleDocumentoVisibility() {
    const checked = $('#toggleMostrarDocumento').is(':checked');
    if (checked) {
        // revelar
        $('#numero_documento').val($('#numero_documento_full').val());
    } else {
        // enmascarar
        $('#numero_documento').val(maskDocument($('#numero_documento_full').val()));
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
    { id: 'QA', text: '🇶🇦 Catar' },
    { id: 'GB', text: '🇬🇧 Reino Unido' },
    { id: 'CF', text: '🇨🇫 República Centroafricana' },
    { id: 'CZ', text: '🇨🇿 República Checa' },
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
        dropdownParent: $('#modalCliente'),
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
        const modal = $('#modalCliente');
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
/* Estilos para Select2 en el modal de clientes */
#modalCliente .select2-container {
    width: 100% !important;
}

#modalCliente .select2-container--open .select2-dropdown {
    z-index: 1055;
}

#modalCliente .select2-search--dropdown .select2-search__field {
    width: 100%;
    padding: 8px;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}

#modalCliente .select2-results__option {
    padding: 8px;
}

#modalCliente .select2-results__option--highlighted {
    background-color: #0d6efd;
    color: white;
}

#modalCliente .cliente-option {
    cursor: pointer;
}

#modalCliente .cliente-option:hover {
    background-color: #f8f9fa;
}

/* Forzar que el dropdown siempre aparezca hacia abajo */
#modalCliente .select2-dropdown.select2-dropdown-below {
    top: auto !important;
    bottom: auto !important;
    margin-top: 1px !important;
}

#modalCliente .select2-dropdown {
    position: absolute !important;
    z-index: 1055 !important;
}
</style>

<?php include 'includes/footer.php'; ?>
