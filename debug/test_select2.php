<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test Select2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h2>Test Select2</h2>
        <select class="form-select" id="test-select" style="width: 100%;">
            <option value="">Seleccione...</option>
            <option value="1">Opción 1</option>
            <option value="2">Opción 2</option>
            <option value="3">Opción 3</option>
        </select>
    </div>
    
    <script>
    $(document).ready(function() {
        console.log('jQuery cargado:', typeof $);
        console.log('Select2 disponible:', typeof $.fn.select2);
        
        $('#test-select').select2({
            placeholder: 'Seleccione una opción...',
            allowClear: true,
            width: '100%'
        });
        
        console.log('Select2 inicializado');
    });
    </script>
</body>
</html>
