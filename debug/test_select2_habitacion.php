<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test Select2 Habitación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h2>Test Select2 Habitación</h2>
        <select class="form-control" id="habitacion_test" style="width: 100%;">
            <option value="">Seleccione una habitación...</option>
            <option value="1">Hab. 101 - Simple ($50,000/noche)</option>
            <option value="2">Hab. 102 - Doble ($250,000/noche)</option>
            <option value="3">Hab. 103 - Suite ($500,000/noche)</option>
        </select>
    </div>
    
    <script>
    $(document).ready(function() {
        console.log('jQuery cargado:', typeof $ !== 'undefined');
        console.log('Select2 disponible:', typeof $.fn.select2);
        
        $('#habitacion_test').select2({
            placeholder: 'Seleccione una habitación...',
            allowClear: true,
            width: '100%'
        });
        
        console.log('Select2 de habitación inicializado');
        
        $('#habitacion_test').on('change', function() {
            console.log('Habitación seleccionada:', $(this).val());
        });
    });
    </script>
</body>
</html>
