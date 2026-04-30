<?php
header("Access-Control-Allow-Origin: *");;
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['accion'])) {
            switch($_GET['accion']) {
                case 'generar_informe':
                    $mes = $_GET['mes'] ?? date('m');
                    $anio = $_GET['anio'] ?? date('Y');
                    
                    // Obtener datos de reservas del mes específico
                    $query = "SELECT 
                        r.id as item,
                        r.id as reserva_id,
                        r.total as valor,
                        DATEDIFF(r.fecha_salida, r.fecha_entrada) as dias_hpdj,
                        c.nombre as cliente_nombre,
                        c.apellido as cliente_apellido,
                        c.fecha_nacimiento,
                        c.nacionalidad,
                        r.motivo_viaje,
                        r.numero_huespedes
                    FROM reservas r
                    LEFT JOIN clientes c ON r.cliente_id = c.id
                    WHERE MONTH(r.fecha_entrada) = ? 
                    AND YEAR(r.fecha_entrada) = ?
                    AND r.deleted_at IS NULL
                    ORDER BY r.fecha_entrada, r.id";
                    
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(1, $mes);
                    $stmt->bindParam(2, $anio);
                    $stmt->execute();
                    
                    $informe = [];
                    $item = 1;
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        // Calcular adultos y niños basado en fecha de nacimiento
                        $adultos = 0;
                        $ninos = 0;
                        $pax = 0;
                        
                        if ($row['fecha_nacimiento']) {
                            $fecha_nacimiento = new DateTime($row['fecha_nacimiento']);
                            $hoy = new DateTime();
                            $edad = $hoy->diff($fecha_nacimiento)->y;
                            
                            if ($edad >= 18) {
                                $adultos = 1;
                            } else {
                                $ninos = 1;
                            }
                        } else {
                            // Si no hay fecha de nacimiento, asumir adulto
                            $adultos = 1;
                        }
                        
                        $pax = $adultos + $ninos;
                        
                        // Mapear motivo de viaje a nombres más descriptivos
                        $motivo_viaje_map = [
                            'turismo' => 'Turismo',
                            'negocios' => 'Negocios',
                            'trabajo' => 'Trabajo',
                            'vacaciones' => 'Vacaciones',
                            'conferencia' => 'Conferencia',
                            'convencion' => 'Convención',
                            'visita_familiar' => 'Visita Familiar',
                            'tratamiento_medico' => 'Tratamiento Médico',
                            'estudio' => 'Estudio',
                            'deporte' => 'Deporte',
                            'otros' => 'Otros'
                        ];
                        
                        $motivo_descriptivo = $motivo_viaje_map[$row['motivo_viaje']] ?? $row['motivo_viaje'] ?? 'No especificado';
                        
                        $informe[] = [
                            'ITEM' => $item++,
                            'R.H.' => 'RH-' . str_pad($row['reserva_id'], 4, '0', STR_PAD_LEFT),
                            'VALOR' => number_format($row['valor'], 2, '.', ','),
                            'DIAS HPDJ.' => $row['dias_hpdj'],
                            'ADULTOS' => $adultos,
                            'NIÑOS' => $ninos,
                            'PAX' => $pax,
                            'NACIONALIDAD' => $row['nacionalidad'] ?? 'No especificada',
                            'MOTIVO DE VIAJE' => $motivo_descriptivo,
                            'CLIENTE' => trim(($row['cliente_nombre'] ?? '') . ' ' . ($row['cliente_apellido'] ?? '')),
                            'FECHA_ENTRADA' => $row['fecha_entrada'] ?? '',
                            'FECHA_SALIDA' => $row['fecha_salida'] ?? ''
                        ];
                    }
                    
                    // Calcular totales
                    $total_valor = 0;
                    $total_dias = 0;
                    $total_adultos = 0;
                    $total_ninos = 0;
                    $total_pax = 0;
                    
                    foreach ($informe as $row) {
                        $total_valor += floatval(str_replace(',', '', $row['VALOR']));
                        $total_dias += $row['DIAS HPDJ.'];
                        $total_adultos += $row['ADULTOS'];
                        $total_ninos += $row['NIÑOS'];
                        $total_pax += $row['PAX'];
                    }
                    
                    $resumen = [
                        'informe' => $informe,
                        'totales' => [
                            'total_valor' => number_format($total_valor, 2, '.', ','),
                            'total_dias' => $total_dias,
                            'total_adultos' => $total_adultos,
                            'total_ninos' => $total_ninos,
                            'total_pax' => $total_pax,
                            'total_reservas' => count($informe)
                        ],
                        'periodo' => [
                            'mes' => $mes,
                            'anio' => $anio,
                            'nombre_mes' => date('F', mktime(0, 0, 0, $mes, 1, $anio))
                        ]
                    ];
                    
                    http_response_code(200);
                    echo json_encode($resumen);
                    break;
                    
                case 'exportar_excel':
                    require_once '../../vendor/autoload.php';
                    
                    $mes = $_GET['mes'] ?? date('m');
                    $anio = $_GET['anio'] ?? date('Y');
                    
                    // Obtener datos del informe
                    $query = "SELECT 
                        r.id as item,
                        r.id as reserva_id,
                        r.total as valor,
                        DATEDIFF(r.fecha_salida, r.fecha_entrada) as dias_hpdj,
                        c.nombre as cliente_nombre,
                        c.apellido as cliente_apellido,
                        c.fecha_nacimiento,
                        c.nacionalidad,
                        r.motivo_viaje,
                        r.numero_huespedes,
                        r.fecha_entrada,
                        r.fecha_salida
                    FROM reservas r
                    LEFT JOIN clientes c ON r.cliente_id = c.id
                    WHERE MONTH(r.fecha_entrada) = ? 
                    AND YEAR(r.fecha_entrada) = ?
                    AND r.deleted_at IS NULL
                    ORDER BY r.fecha_entrada, r.id";
                    
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(1, $mes);
                    $stmt->bindParam(2, $anio);
                    $stmt->execute();
                    
                    // Crear archivo Excel
                    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();
                    
                    // Configurar encabezados
                    $headers = ['ITEM', 'R.H.', 'VALOR', 'DIAS HPDJ.', 'ADULTOS', 'NIÑOS', 'PAX', 'NACIONALIDAD', 'MOTIVO DE VIAJE'];
                    $sheet->fromArray($headers, null, 'A1');
                    
                    // Estilo para encabezados
                    $headerStyle = [
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
                    ];
                    $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
                    
                    // Llenar datos
                    $row = 2;
                    $total_valor = 0;
                    $total_dias = 0;
                    $total_adultos = 0;
                    $total_ninos = 0;
                    $total_pax = 0;
                    
                    while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        // Calcular adultos y niños
                        $adultos = 0;
                        $ninos = 0;
                        
                        if ($data['fecha_nacimiento']) {
                            $fecha_nacimiento = new DateTime($data['fecha_nacimiento']);
                            $hoy = new DateTime();
                            $edad = $hoy->diff($fecha_nacimiento)->y;
                            
                            if ($edad >= 18) {
                                $adultos = 1;
                            } else {
                                $ninos = 1;
                            }
                        } else {
                            $adultos = 1;
                        }
                        
                        $pax = $adultos + $ninos;
                        
                        // Mapear motivo de viaje
                        $motivo_viaje_map = [
                            'turismo' => 'Turismo',
                            'negocios' => 'Negocios',
                            'trabajo' => 'Trabajo',
                            'vacaciones' => 'Vacaciones',
                            'conferencia' => 'Conferencia',
                            'convencion' => 'Convención',
                            'visita_familiar' => 'Visita Familiar',
                            'tratamiento_medico' => 'Tratamiento Médico',
                            'estudio' => 'Estudio',
                            'deporte' => 'Deporte',
                            'otros' => 'Otros'
                        ];
                        
                        $motivo_descriptivo = $motivo_viaje_map[$data['motivo_viaje']] ?? $data['motivo_viaje'] ?? 'No especificado';
                        
                        $sheet->setCellValue('A' . $row, $row - 1);
                        $sheet->setCellValue('B' . $row, 'RH-' . str_pad($data['reserva_id'], 4, '0', STR_PAD_LEFT));
                        $sheet->setCellValue('C' . $row, $data['valor']);
                        $sheet->setCellValue('D' . $row, $data['dias_hpdj']);
                        $sheet->setCellValue('E' . $row, $adultos);
                        $sheet->setCellValue('F' . $row, $ninos);
                        $sheet->setCellValue('G' . $row, $pax);
                        $sheet->setCellValue('H' . $row, $data['nacionalidad'] ?? 'No especificada');
                        $sheet->setCellValue('I' . $row, $motivo_descriptivo);
                        
                        // Acumular totales
                        $total_valor += $data['valor'];
                        $total_dias += $data['dias_hpdj'];
                        $total_adultos += $adultos;
                        $total_ninos += $ninos;
                        $total_pax += $pax;
                        
                        $row++;
                    }
                    
                    // Agregar fila de totales
                    $totalRow = $row;
                    $sheet->setCellValue('A' . $totalRow, 'TOTALES');
                    $sheet->setCellValue('C' . $totalRow, $total_valor);
                    $sheet->setCellValue('D' . $totalRow, $total_dias);
                    $sheet->setCellValue('E' . $totalRow, $total_adultos);
                    $sheet->setCellValue('F' . $totalRow, $total_ninos);
                    $sheet->setCellValue('G' . $totalRow, $total_pax);
                    
                    // Estilo para totales
                    $totalStyle = [
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']]
                    ];
                    $sheet->getStyle('A' . $totalRow . ':I' . $totalRow)->applyFromArray($totalStyle);
                    
                    // Auto-ajustar columnas
                    foreach (range('A', 'I') as $columnID) {
                        $sheet->getColumnDimension($columnID)->setAutoSize(true);
                    }
                    
                    // Configurar título del archivo
                    $nombre_mes = date('F', mktime(0, 0, 0, $mes, 1, $anio));
                    $filename = "INFORME_HUESPEDES_" . strtoupper($nombre_mes) . "_" . $anio . ".xlsx";
                    
                    // Descargar archivo
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="' . $filename . '"');
                    header('Cache-Control: max-age=0');
                    
                    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
                    $writer->save('php://output');
                    exit;
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(array("message" => "Acción no válida"));
                    break;
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Acción no especificada"));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Método no permitido"));
        break;
}
?>
