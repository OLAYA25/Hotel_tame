<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../backend/config/database.php';
include_once '../../lib/SimpleSpreadsheet.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['accion'])) {
            switch($_GET['accion']) {
                case 'exportar_xlsx':
                    $mes = $_GET['mes'] ?? date('m');
                    $anio = $_GET['anio'] ?? date('Y');
                    
                    // Obtener datos del informe
                    $query = "SELECT 
                        r.id as item,
                        r.id as reserva_id,
                        r.precio_total as valor,
                        DATEDIFF(r.fecha_salida, r.fecha_entrada) as dias_hpdj,
                        c.nombre as cliente_nombre,
                        c.apellido as cliente_apellido,
                        c.fecha_nacimiento,
                        c.pais as nacionalidad,
                        r.motivo_viaje,
                        r.num_huespedes as numero_huespedes,
                        r.notas,
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
                    
                    // Generar archivo XLSX
                    $spreadsheet = new SimpleSpreadsheet('xlsx');
                    
                    // Configurar metadata
                    $nombre_mes = strtoupper(date('F', mktime(0, 0, 0, $mes, 1, $anio)));
                    $filename = "INFORME_HUESPEDES_" . $nombre_mes . "_" . $anio . ".xlsx";
                    
                    $spreadsheet->setMetadata([
                        'title' => 'Informe de Huéspedes',
                        'subject' => 'Reporte mensual de huéspedes del hotel',
                        'creator' => 'Hotel Management System',
                        'description' => "Informe de huéspedes correspondiente a $nombre_mes $anio"
                    ]);
                    
                    // Agregar hoja principal
                    $sheet_name = "Informe $nombre_mes $anio";
                    $spreadsheet->addSheet($sheet_name);
                    
                    // Encabezados
                    $headers = ['ITEM', 'R.H.', 'VALOR', 'DIAS HPDJ.', 'ADULTOS', 'NIÑOS', 'PAX', 'NACIONALIDAD', 'MOTIVO DE VIAJE', 'CLIENTE', 'FECHA ENTRADA', 'FECHA SALIDA'];
                    $spreadsheet->addRow($headers);
                    
                    // Procesar y agregar datos
                    $total_valor = 0;
                    $total_dias = 0;
                    $total_adultos = 0;
                    $total_ninos = 0;
                    $total_pax = 0;
                    $row_num = 1;
                    
                    while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        // Calcular adultos y niños (misma lógica que CSV)
                        $adultos = 0;
                        $ninos = 0;
                        $pax = 0;
                        
                        // Contar cliente principal
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
                        
                        // Procesar acompañantes
                        $notas = $data['notas'] ?? '';
                        if (strpos($notas, 'ACOMPANANTES:') !== false) {
                            $inicio = strpos($notas, 'ACOMPANANTES:');
                            $json_part = substr($notas, $inicio + 13);
                            $json_part = trim($json_part);
                            
                            $acompanantes_data = json_decode($json_part, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($acompanantes_data)) {
                                foreach ($acompanantes_data as $acompanante) {
                                    $fecha_nac_acompanante = $acompanante['fecha_nacimiento'] ?? null;
                                    
                                    if ($fecha_nac_acompanante && $fecha_nac_acompanante !== '' && $fecha_nac_acompanante !== 'null') {
                                        try {
                                            $fecha_nac = new DateTime($fecha_nac_acompanante);
                                            $edad = $hoy->diff($fecha_nac)->y;
                                            
                                            if ($edad >= 18) {
                                                $adultos++;
                                            } else {
                                                $ninos++;
                                            }
                                        } catch (Exception $e) {
                                            $adultos++;
                                        }
                                    } else {
                                        $adultos++;
                                    }
                                }
                            }
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
                        
                        // Formatear fila de datos
                        $row = [
                            $row_num++,
                            'RH-' . str_pad($data['reserva_id'], 4, '0', STR_PAD_LEFT),
                            $data['valor'],
                            $data['dias_hpdj'],
                            $adultos,
                            $ninos,
                            $pax,
                            $data['nacionalidad'] ?? 'No especificada',
                            $motivo_descriptivo,
                            trim(($data['cliente_nombre'] ?? '') . ' ' . ($data['cliente_apellido'] ?? '')),
                            $data['fecha_entrada'] ?? '',
                            $data['fecha_salida'] ?? ''
                        ];
                        
                        $spreadsheet->addRow($row);
                        
                        // Acumular totales
                        $total_valor += $data['valor'];
                        $total_dias += $data['dias_hpdj'];
                        $total_adultos += $adultos;
                        $total_ninos += $ninos;
                        $total_pax += $pax;
                    }
                    
                    // Agregar fila de totales
                    $totales = [
                        'TOTALES',
                        '',
                        $total_valor,
                        $total_dias,
                        $total_adultos,
                        $total_ninos,
                        $total_pax,
                        '',
                        '',
                        '',
                        '',
                        ''
                    ];
                    
                    $spreadsheet->addRow($totales);
                    
                    // Descargar archivo
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment; filename="' . $filename . '"');
                    header('Cache-Control: max-age=0');
                    
                    echo $spreadsheet->saveToString();
                    exit;
                    break;
                    
                case 'exportar_ods':
                    $mes = $_GET['mes'] ?? date('m');
                    $anio = $_GET['anio'] ?? date('Y');
                    
                    // Obtener los mismos datos que XLSX
                    $query = "SELECT 
                        r.id as item,
                        r.id as reserva_id,
                        r.precio_total as valor,
                        DATEDIFF(r.fecha_salida, r.fecha_entrada) as dias_hpdj,
                        c.nombre as cliente_nombre,
                        c.apellido as cliente_apellido,
                        c.fecha_nacimiento,
                        c.pais as nacionalidad,
                        r.motivo_viaje,
                        r.num_huespedes as numero_huespedes,
                        r.notas,
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
                    
                    // Generar archivo ODS
                    $spreadsheet = new SimpleSpreadsheet('ods');
                    
                    // Configurar metadata
                    $nombre_mes = strtoupper(date('F', mktime(0, 0, 0, $mes, 1, $anio)));
                    $filename = "INFORME_HUESPEDES_" . $nombre_mes . "_" . $anio . ".ods";
                    
                    $spreadsheet->setMetadata([
                        'title' => 'Informe de Huéspedes',
                        'subject' => 'Reporte mensual de huéspedes del hotel',
                        'creator' => 'Hotel Management System',
                        'description' => "Informe de huéspedes correspondiente a $nombre_mes $anio"
                    ]);
                    
                    // Agregar hoja principal
                    $sheet_name = "Informe $nombre_mes $anio";
                    $spreadsheet->addSheet($sheet_name);
                    
                    // Encabezados (misma estructura que XLSX)
                    $headers = ['ITEM', 'R.H.', 'VALOR', 'DIAS HPDJ.', 'ADULTOS', 'NIÑOS', 'PAX', 'NACIONALIDAD', 'MOTIVO DE VIAJE', 'CLIENTE', 'FECHA ENTRADA', 'FECHA SALIDA'];
                    $spreadsheet->addRow($headers);
                    
                    // Procesar datos (misma lógica que XLSX)
                    $total_valor = 0;
                    $total_dias = 0;
                    $total_adultos = 0;
                    $total_ninos = 0;
                    $total_pax = 0;
                    $row_num = 1;
                    
                    while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        // [Misma lógica de cálculo que en XLSX]
                        $adultos = 0;
                        $ninos = 0;
                        $pax = 0;
                        
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
                        
                        $notas = $data['notas'] ?? '';
                        if (strpos($notas, 'ACOMPANANTES:') !== false) {
                            $inicio = strpos($notas, 'ACOMPANANTES:');
                            $json_part = substr($notas, $inicio + 13);
                            $json_part = trim($json_part);
                            
                            $acompanantes_data = json_decode($json_part, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($acompanantes_data)) {
                                foreach ($acompanantes_data as $acompanante) {
                                    $fecha_nac_acompanante = $acompanante['fecha_nacimiento'] ?? null;
                                    
                                    if ($fecha_nac_acompanante && $fecha_nac_acompanante !== '' && $fecha_nac_acompanante !== 'null') {
                                        try {
                                            $fecha_nac = new DateTime($fecha_nac_acompanante);
                                            $edad = $hoy->diff($fecha_nac)->y;
                                            
                                            if ($edad >= 18) {
                                                $adultos++;
                                            } else {
                                                $ninos++;
                                            }
                                        } catch (Exception $e) {
                                            $adultos++;
                                        }
                                    } else {
                                        $adultos++;
                                    }
                                }
                            }
                        }
                        
                        $pax = $adultos + $ninos;
                        
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
                        
                        $row = [
                            $row_num++,
                            'RH-' . str_pad($data['reserva_id'], 4, '0', STR_PAD_LEFT),
                            $data['valor'],
                            $data['dias_hpdj'],
                            $adultos,
                            $ninos,
                            $pax,
                            $data['nacionalidad'] ?? 'No especificada',
                            $motivo_descriptivo,
                            trim(($data['cliente_nombre'] ?? '') . ' ' . ($data['cliente_apellido'] ?? '')),
                            $data['fecha_entrada'] ?? '',
                            $data['fecha_salida'] ?? ''
                        ];
                        
                        $spreadsheet->addRow($row);
                        
                        $total_valor += $data['valor'];
                        $total_dias += $data['dias_hpdj'];
                        $total_adultos += $adultos;
                        $total_ninos += $ninos;
                        $total_pax += $pax;
                    }
                    
                    // Agregar fila de totales
                    $totales = [
                        'TOTALES',
                        '',
                        $total_valor,
                        $total_dias,
                        $total_adultos,
                        $total_ninos,
                        $total_pax,
                        '',
                        '',
                        '',
                        '',
                        ''
                    ];
                    
                    $spreadsheet->addRow($totales);
                    
                    // Descargar archivo
                    header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
                    header('Content-Disposition: attachment; filename="' . $filename . '"');
                    header('Cache-Control: max-age=0');
                    
                    echo $spreadsheet->saveToString();
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
