<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../models/CuentaContable.php';
include_once '../models/TransaccionContable.php';

$database = new Database();
$db = $database->getConnection();

$cuenta = new CuentaContable($db);
$transaccion = new TransaccionContable($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['accion'])) {
            switch($_GET['accion']) {
                case 'balance_comprobacion':
                    $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
                    $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
                    
                    $stmt = $cuenta->getBalanceComprobacion($fecha_inicio, $fecha_fin);
                    $balance = [];
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $balance[] = $row;
                    }
                    
                    http_response_code(200);
                    echo json_encode($balance);
                    break;
                    
                case 'resumen_financiero':
                    $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
                    $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
                    
                    $resumen = $transaccion->getResumenFinanciero($fecha_inicio, $fecha_fin);
                    
                    http_response_code(200);
                    echo json_encode($resumen);
                    break;
                    
                case 'exportar_excel':
                    $tipo = $_GET['tipo'] ?? 'resumen';
                    $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
                    $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
                    
                    exportarExcel($tipo, $fecha_inicio, $fecha_fin, $transaccion, $cuenta);
                    break;
                    
                case 'exportar_pdf':
                    $tipo = $_GET['tipo'] ?? 'resumen';
                    $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
                    $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
                    
                    exportarPDF($tipo, $fecha_inicio, $fecha_fin, $transaccion, $cuenta);
                    break;
                    
                case 'cuentas_por_tipo':
                    $tipo = $_GET['tipo'] ?? 'activo';
                    
                    $stmt = $cuenta->getByTipo($tipo);
                    $cuentas = [];
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $cuentas[] = $row;
                    }
                    
                    http_response_code(200);
                    echo json_encode($cuentas);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(array("message" => "Acción no válida"));
                    break;
            }
        } else if(isset($_GET['id'])) {
            // Obtener transacción específica con detalles
            $transaccion->id = $_GET['id'];
            
            $stmt = $transaccion->getDetalles();
            $detalles = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $detalles[] = $row;
            }
            
            http_response_code(200);
            echo json_encode($detalles);
        } else {
            // Obtener todas las transacciones con filtros
            $fecha_inicio = $_GET['fecha_inicio'] ?? null;
            $fecha_fin = $_GET['fecha_fin'] ?? null;
            $tipo = $_GET['tipo'] ?? null;
            $estado = $_GET['estado'] ?? null;
            
            $stmt = $transaccion->getAll($fecha_inicio, $fecha_fin, $tipo, $estado);
            $transacciones = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $transacciones[] = $row;
            }
            
            http_response_code(200);
            echo json_encode($transacciones);
        }
        break;
        
    case 'POST':
        // Crear nueva transacción
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->descripcion) && !empty($data->detalles) && count($data->detalles) >= 2) {
            $transaccion->numero_comprobante = $data->numero_comprobante ?? null;
            $transaccion->fecha = $data->fecha ?? date('Y-m-d');
            $transaccion->descripcion = $data->descripcion;
            $transaccion->tipo_transaccion = $data->tipo_transaccion ?? 'ingreso';
            $transaccion->monto_total = $data->monto_total ?? 0;
            $transaccion->usuario_id = $_SESSION['usuario']['id'];
            $transaccion->referencia_tipo = $data->referencia_tipo ?? null;
            $transaccion->referencia_id = $data->referencia_id ?? null;
            $transaccion->estado = $data->estado ?? 'borrador';
            
            // Validar que la suma de debe = suma de haber (partida doble)
            $total_debe = 0;
            $total_haber = 0;
            
            foreach ($data->detalles as $detalle) {
                if ($detalle['tipo_movimiento'] === 'debe') {
                    $total_debe += $detalle['monto'];
                } else {
                    $total_haber += $detalle['monto'];
                }
            }
            
            if (abs($total_debe - $total_haber) > 0.01) {
                http_response_code(400);
                echo json_encode(array("message" => "La suma del debe debe ser igual a la suma del haber"));
                break;
            }
            
            if($transaccion->createWithDetalles($data->detalles)) {
                http_response_code(201);
                echo json_encode(array("message" => "Transacción creada exitosamente.", "id" => $transaccion->id));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo crear la transacción."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos. Se requiere descripción y al menos 2 detalles."));
        }
        break;
        
    case 'PUT':
        // Confirmar o actualizar transacción
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $transaccion->id = $data->id;
            
            if (isset($data->accion) && $data->accion === 'confirmar') {
                if($transaccion->confirmar()) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Transacción confirmada exitosamente."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "No se pudo confirmar la transacción."));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Acción no válida"));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos. Se requiere ID."));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Método no permitido."));
        break;
}

// Función para exportar a Excel
function exportarExcel($tipo, $fecha_inicio, $fecha_fin, $transaccion, $cuenta) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="reporte_' . $tipo . '_' . date('Y-m-d') . '.xls"');
    
    echo "<table border='1'>";
    echo "<tr><th colspan='4' style='background:#f0f0f0;'>Reporte de " . ucfirst($tipo) . "</th></tr>";
    echo "<tr><th colspan='4'>Período: " . $fecha_inicio . " a " . $fecha_fin . "</th></tr>";
    
    if ($tipo === 'resumen') {
        $resumen = $transaccion->getResumenFinanciero($fecha_inicio, $fecha_fin);
        
        echo "<tr><th>Tipo</th><th>Monto</th><th>Transacciones</th><th>Fecha</th></tr>";
        
        if ($resumen) {
            echo "<tr><td>Ingresos</td><td>$" . number_format($resumen['total_ingresos'], 2) . "</td><td>" . $resumen['count_ingresos'] . "</td><td>" . date('Y-m-d H:i:s') . "</td></tr>";
            echo "<tr><td>Egresos</td><td>$" . number_format($resumen['total_egresos'], 2) . "</td><td>" . $resumen['count_egresos'] . "</td><td>" . date('Y-m-d H:i:s') . "</td></tr>";
            echo "<tr><td><strong>Balance</strong></td><td><strong>$" . number_format($resumen['balance'], 2) . "</strong></td><td>" . ($resumen['count_ingresos'] + $resumen['count_egresos']) . "</td><td>" . date('Y-m-d H:i:s') . "</td></tr>";
        }
    } elseif ($tipo === 'balance') {
        $stmt = $cuenta->getBalanceComprobacion($fecha_inicio, $fecha_fin);
        
        echo "<tr><th>Cuenta</th><th>Descripción</th><th>Debe</th><th>Haber</th></tr>";
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['codigo']) . "</td>";
            echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
            echo "<td>$" . number_format($row['total_debe'], 2) . "</td>";
            echo "<td>$" . number_format($row['total_haber'], 2) . "</td>";
            echo "</tr>";
        }
    }
    
    echo "</table>";
    exit;
}

// Función para exportar a PDF
function exportarPDF($tipo, $fecha_inicio, $fecha_fin, $transaccion, $cuenta) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="reporte_' . $tipo . '_' . date('Y-m-d') . '.pdf"');
    
    // Crear contenido HTML para PDF
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de ' . ucfirst($tipo) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; background-color: #f9f9f9; }
    </style>
</head>
<body>
    <h1>Reporte de ' . ucfirst($tipo) . '</h1>
    <p><strong>Período:</strong> ' . $fecha_inicio . ' a ' . $fecha_fin . '</p>
    <p><strong>Fecha de generación:</strong> ' . date('Y-m-d H:i:s') . '</p>';
    
    if ($tipo === 'resumen') {
        $resumen = $transaccion->getResumenFinanciero($fecha_inicio, $fecha_fin);
        
        $html .= '<table>
            <tr>
                <th>Concepto</th>
                <th>Monto</th>
                <th>Cantidad</th>
            </tr>';
        
        if ($resumen) {
            $html .= '<tr>
                <td>Ingresos</td>
                <td>$' . number_format($resumen['total_ingresos'], 2) . '</td>
                <td>' . $resumen['count_ingresos'] . '</td>
            </tr>';
            $html .= '<tr>
                <td>Egresos</td>
                <td>$' . number_format($resumen['total_egresos'], 2) . '</td>
                <td>' . $resumen['count_egresos'] . '</td>
            </tr>';
            $html .= '<tr class="total">
                <td><strong>Balance</strong></td>
                <td><strong>$' . number_format($resumen['balance'], 2) . '</strong></td>
                <td><strong>' . ($resumen['count_ingresos'] + $resumen['count_egresos']) . '</strong></td>
            </tr>';
        }
    } elseif ($tipo === 'balance') {
        $stmt = $cuenta->getBalanceComprobacion($fecha_inicio, $fecha_fin);
        
        $html .= '<table>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Debe</th>
                <th>Haber</th>
            </tr>';
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $html .= '<tr>
                <td>' . htmlspecialchars($row['codigo']) . '</td>
                <td>' . htmlspecialchars($row['nombre']) . '</td>
                <td>$' . number_format($row['total_debe'], 2) . '</td>
                <td>$' . number_format($row['total_haber'], 2) . '</td>
            </tr>';
        }
    }
    
    $html .= '</table>
</body>
</html>';
    
    echo $html;
    exit;
}
?>
