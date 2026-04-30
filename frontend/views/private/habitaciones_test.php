<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <h1>Habitaciones</h1>
    
    <!-- Modal Simplificado -->
    <div class="modal fade" id="modalHabitacion">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Habitación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Número de Habitación *</label>
                        <input type="text" class="form-control" id="numero" value="102">
                    </div>
                    
                    <!-- ÁREA DE IMAGEN DESTACADA -->
                    <div class="mb-3" style="background: #ffeb3b; padding: 20px; border: 3px solid #f44336; border-radius: 8px;">
                        <label class="form-label fw-bold" style="color: #d32f2f; font-size: 18px;">
                            🖼️ IMAGEN DE LA HABITACIÓN
                        </label>
                        <p class="text-muted mb-3">Puedes subir una foto desde archivo o tomarla con la cámara</p>
                        
                        <input type="file" class="form-control mb-3" id="imagen" accept="image/*" 
                               style="background: white; border: 3px solid #f44336;">
                        
                        <div class="d-flex gap-2 mb-3">
                            <button type="button" class="btn btn-danger flex-fill" onclick="document.getElementById('imagen').click()">
                                📁 Subir Archivo
                            </button>
                            <button type="button" class="btn btn-warning flex-fill" onclick="alert('Cámara')">
                                📷 Cámara
                            </button>
                        </div>
                        
                        <div id="imagePreviewHabitacion" class="text-center" 
                             style="background: white; border: 3px dashed #f44336; min-height: 120px; padding: 20px;">
                            <p class="text-muted mb-0">📸 No hay imagen seleccionada</p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tipo *</label>
                        <select class="form-select" id="tipo">
                            <option value="doble" selected>Doble</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Botón para abrir modal -->
    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#modalHabitacion">
        Abrir Modal de Prueba
    </button>
</div>

<script>
console.log('🔍 Página de habitaciones cargada');

// Test simple
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 DOM completamente cargado');
    
    const modal = document.getElementById('modalHabitacion');
    const imagenInput = document.getElementById('imagen');
    const preview = document.getElementById('imagePreviewHabitacion');
    
    console.log('🔍 Modal:', modal);
    console.log('🔍 Input imagen:', imagenInput);
    console.log('🔍 Preview:', preview);
    
    if (imagenInput) {
        imagenInput.style.border = '5px solid red !important';
        console.log('✅ Input de imagen encontrado y marcado');
    }
    
    if (preview) {
        preview.style.border = '5px solid green !important';
        console.log('✅ Preview encontrado y marcado');
    }
});
</script>

<?php include 'includes/footer.php'; ?>
