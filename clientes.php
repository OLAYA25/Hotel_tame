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

    <!-- Grid de tarjetas para clientes -->
    <div class="row g-4" id="clientesGrid">
        <!-- Las tarjetas se cargarán dinámicamente aquí -->
    </div>
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
                            <label class="form-label">País *</label>
                            <input type="text" class="form-control" id="pais" value="COLOMBIA">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Motivo de Viaje</label>
                            <select class="form-select" id="motivo_viaje">
                                <option value="turismo">Turismo</option>
                                <option value="negocios">Negocios</option>
                                <option value="conferencia">Conferencia</option>
                                <option value="convencion">Convención</option>
                                <option value="visita_familiar">Visita Familiar</option>
                                <option value="tratamiento_medico">Tratamiento Médico</option>
                                <option value="estudio">Estudio</option>
                                <option value="deporte">Deporte</option>
                                <option value="otros">Otros</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
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
$(document).ready(function() {
    cargarClientes();
});

function cargarClientes() {
    $.get('api/endpoints/clientes.php', function(data) {
        const grid = $('#clientesGrid');
        grid.empty();

        // La API puede devolver { records: [...] } o directamente un array
        const clientesList = Array.isArray(data) ? data : (data.records || []);

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
    });
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
        $('#pais').val(cliente.pais);
        $('#direccion').val(cliente.direccion);
        $('#modalCliente').modal('show');
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
        pais: $('#pais').val(),
        motivo_viaje: $('#motivo_viaje').val(),
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
                msg = json.message || json.error || xhr.responseText;
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
</script>

<?php include 'includes/footer.php'; ?>
