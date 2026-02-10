<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    // Cargar el archivo Excel
    $spreadsheet = IOFactory::load('INFORME HUESPEDES DICIEMBRE  2025 (1).xlsx');
    $worksheet = $spreadsheet->getActiveSheet();
    
    echo "<h2>Análisis del Informe de Huéspedes - Diciembre 2025</h2>";
    
    // Obtener todas las filas
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    
    echo "<h3>Estructura del Informe:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    
    // Mostrar encabezados
    echo "<tr style='background-color: #f0f0f0;'>";
    for ($col = 'A'; $col <= $highestColumn; $col++) {
        $cellValue = $worksheet->getCell($col . '1')->getValue();
        echo "<th style='padding: 8px; text-align: left;'>{$cellValue}</th>";
    }
    echo "</tr>";
    
    // Mostrar primeras 5 filas de datos
    for ($row = 2; $row <= min(6, $highestRow); $row++) {
        echo "<tr>";
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $cellValue = $worksheet->getCell($col . $row)->getValue();
            echo "<td style='padding: 8px;'>" . ($cellValue ?? '') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Análisis de Campos:</h3>";
    echo "<ul>";
    
    // Analizar cada columna
    for ($col = 'A'; $col <= $highestColumn; $col++) {
        $header = $worksheet->getCell($col . '1')->getValue();
        echo "<li><strong>{$header}</strong>: ";
        
        // Analizar valores en esta columna
        $values = [];
        for ($row = 2; $row <= min(10, $highestRow); $row++) {
            $value = $worksheet->getCell($col . $row)->getValue();
            if ($value) {
                $values[] = $value;
            }
        }
        
        if (!empty($values)) {
            echo "Ejemplos: " . implode(', ', array_slice($values, 0, 3));
        } else {
            echo "Sin datos visibles";
        }
        echo "</li>";
    }
    echo "</ul>";
    
    echo "<h3>Estadísticas:</h3>";
    echo "<p>Total de filas: " . ($highestRow - 1) . "</p>";
    echo "<p>Total de columnas: " . (ord($highestColumn) - ord('A') + 1) . "</p>";
    
} catch (Exception $e) {
    echo "<p>Error al leer el archivo: " . $e->getMessage() . "</p>";
}
?>
