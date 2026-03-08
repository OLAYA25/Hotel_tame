<?php
// Script simple para analizar el archivo Excel sin dependencias externas

echo "<h2>Análisis del Informe de Huéspedes - Diciembre 2025</h2>";

$archivo = 'INFORME HUESPEDES DICIEMBRE  2025 (1).xlsx';

if (!file_exists($archivo)) {
    echo "<p>Error: No se encuentra el archivo $archivo</p>";
    exit;
}

// Intentar leer como CSV si es posible
echo "<h3>Información del archivo:</h3>";
echo "<ul>";
echo "<li>Nombre: " . basename($archivo) . "</li>";
echo "<li>Tamaño: " . filesize($archivo) . " bytes</li>";
echo "<li>Tipo MIME: " . mime_content_type($archivo) . "</li>";
echo "<li>Última modificación: " . date('Y-m-d H:i:s', filemtime($archivo)) . "</li>";
echo "</ul>";

// Intentar leer el archivo en modo binario para ver estructura
$handle = fopen($archivo, 'rb');
if ($handle) {
    echo "<h3>Vista previa del contenido (primeros 1000 bytes):</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; overflow: auto; max-height: 300px;'>";
    $data = fread($handle, 1000);
    // Convertir a texto legible
    $texto = bin2hex($data);
    echo chunk_split($texto, 2, ' ');
    echo "</pre>";
    fclose($handle);
}

// Sugerir estructura basada en informes típicos de hotel
echo "<h3>Estructura Sugerida para Acompañantes:</h3>";
echo "<p>Basado en informes típicos de hotel, el sistema debería incluir:</p>";
echo "<ul>";
echo "<li><strong>Huésped Principal:</strong> Cliente que hace la reserva</li>";
echo "<li><strong>Acompañantes:</strong> Personas adicionales en la misma habitación</li>";
echo "<li><strong>Relación:</strong> Parentesco o relación con el huésped principal</li>";
echo "<li><strong>Documento:</strong> Identificación de cada acompañante</li>";
echo "<li><strong>Edad:</strong> Para determinar tarifas infantiles/adultos</li>";
echo "</ul>";

// Mostrar estructura de base de datos actual
echo "<h3>Estructura Actual de la Base de Datos:</h3>";
echo "<h4>Tabla clientes:</h4>";
echo "<pre>
- id (INT, PRIMARY KEY)
- nombre (VARCHAR)
- apellido (VARCHAR) 
- tipo_documento (VARCHAR)
- numero_documento (VARCHAR)
- email (VARCHAR)
- telefono (VARCHAR)
- fecha_nacimiento (DATE)
- direccion (TEXT)
- ciudad (VARCHAR)
- pais (VARCHAR)
</pre>";

echo "<h4>Tabla reservas:</h4>";
echo "<pre>
- id (INT, PRIMARY KEY)
- cliente_id (INT, FOREIGN KEY)
- habitacion_id (INT, FOREIGN KEY)
- fecha_entrada (DATE)
- fecha_salida (DATE)
- estado (ENUM)
- total (DECIMAL)
- metodo_pago (VARCHAR)
- noches (INT)
- observaciones (TEXT)
</pre>";

echo "<h3>Propuesta de Mejora:</h3>";
echo "<p>Para implementar el registro de acompañantes, se necesita:</p>";
echo "<ol>";
echo "<li>Crear tabla <strong>acompañantes</strong></li>";
echo "<li>Modificar formulario de registro de clientes</li>";
echo "<li>Actualizar sistema de reservas</li>";
echo "<li>Agregar informes de ocupación real</li>";
echo "</ol>";
?>
