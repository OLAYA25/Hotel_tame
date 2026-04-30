<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../backend/config/database.php';

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
                        r.precio_total as valor,
                        DATEDIFF(r.fecha_salida, r.fecha_entrada) as dias_hpdj,
                        c.nombre as cliente_nombre,
                        c.apellido as cliente_apellido,
                        c.fecha_nacimiento,
                        c.pais as nacionalidad,
                        r.motivo_viaje,
                        r.num_huespedes as numero_huespedes,
                        r.notas
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
                        // Inicializar contadores
                        $adultos = 0;
                        $ninos = 0;
                        $pax = 0;
                        
                        // Contar cliente principal
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
                        
                        // Procesar acompañantes desde el campo notas
                        $notas = $row['notas'] ?? '';
                        $acompanantes = [];
                        
                        // Extraer JSON de acompañantes
                        if (strpos($notas, 'ACOMPANANTES:') !== false) {
                            $inicio = strpos($notas, 'ACOMPANANTES:');
                            $json_part = substr($notas, $inicio + 13);
                            $json_part = trim($json_part);
                            
                            // Intentar decodificar el JSON
                            $acompanantes_data = json_decode($json_part, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($acompanantes_data)) {
                                $acompanantes = $acompanantes_data;
                            }
                        }
                        
                        // Contar acompañantes
                        foreach ($acompanantes as $acompanante) {
                            $fecha_nac_acompanante = $acompanante['fecha_nacimiento'] ?? null;
                            
                            if ($fecha_nac_acompanante && $fecha_nac_acompanante !== '' && $fecha_nac_acompanante !== 'null') {
                                try {
                                    $fecha_nac = new DateTime($fecha_nac_acompanante);
                                    $hoy = new DateTime();
                                    $edad = $hoy->diff($fecha_nac)->y;
                                    
                                    if ($edad >= 18) {
                                        $adultos++;
                                    } else {
                                        $ninos++;
                                    }
                                } catch (Exception $e) {
                                    // Si hay error en la fecha, asumir adulto
                                    $adultos++;
                                }
                            } else {
                                // Si no hay fecha de nacimiento, asumir adulto
                                $adultos++;
                            }
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
                    
                case 'exportar_csv':
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
                    
                    // Configurar headers para descarga CSV
                    $nombre_mes = strtoupper(date('F', mktime(0, 0, 0, $mes, 1, $anio)));
                    $filename = "INFORME_HUESPEDES_" . $nombre_mes . "_" . $anio . ".csv";
                    
                    header('Content-Type: text/csv; charset=utf-8');
                    header('Content-Disposition: attachment; filename="' . $filename . '"');
                    
                    // Crear archivo CSV
                    $output = fopen('php://output', 'w');
                    
                    // Agregar BOM para caracteres especiales en Excel
                    fwrite($output, "\xEF\xBB\xBF");
                    
                    // Encabezados
                    $headers = ['ITEM', 'R.H.', 'VALOR', 'DIAS HPDJ.', 'ADULTOS', 'NIÑOS', 'PAX', 'NACIONALIDAD', 'MOTIVO DE VIAJE'];
                    fputcsv($output, $headers);
                    
                    // Datos
                    $total_valor = 0;
                    $total_dias = 0;
                    $total_adultos = 0;
                    $total_ninos = 0;
                    $total_pax = 0;
                    
                    while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        // Inicializar contadores
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
                        
                        // Procesar acompañantes desde el campo notas
                        $notas = $data['notas'] ?? '';
                        $acompanantes = [];
                        
                        // Extraer JSON de acompañantes
                        if (strpos($notas, 'ACOMPANANTES:') !== false) {
                            $inicio = strpos($notas, 'ACOMPANANTES:');
                            $json_part = substr($notas, $inicio + 13);
                            $json_part = trim($json_part);
                            
                            // Intentar decodificar el JSON
                            $acompanantes_data = json_decode($json_part, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($acompanantes_data)) {
                                $acompanantes = $acompanantes_data;
                            }
                        }
                        
                        // Contar acompañantes
                        foreach ($acompanantes as $acompanante) {
                            $fecha_nac_acompanante = $acompanante['fecha_nacimiento'] ?? null;
                            
                            if ($fecha_nac_acompanante && $fecha_nac_acompanante !== '' && $fecha_nac_acompanante !== 'null') {
                                try {
                                    $fecha_nac = new DateTime($fecha_nac_acompanante);
                                    $hoy = new DateTime();
                                    $edad = $hoy->diff($fecha_nac)->y;
                                    
                                    if ($edad >= 18) {
                                        $adultos++;
                                    } else {
                                        $ninos++;
                                    }
                                } catch (Exception $e) {
                                    // Si hay error en la fecha, asumir adulto
                                    $adultos++;
                                }
                            } else {
                                // Si no hay fecha de nacimiento, asumir adulto
                                $adultos++;
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
                        
                        $row = [
                            $row_number = $row_number ?? 1,
                            'RH-' . str_pad($data['reserva_id'], 4, '0', STR_PAD_LEFT),
                            number_format($data['valor'], 2, '.', ','),
                            $data['dias_hpdj'],
                            $adultos,
                            $ninos,
                            $pax,
                            $data['nacionalidad'] ?? 'No especificada',
                            $motivo_descriptivo
                        ];
                        
                        fputcsv($output, $row);
                        
                        // Acumular totales
                        $total_valor += $data['valor'];
                        $total_dias += $data['dias_hpdj'];
                        $total_adultos += $adultos;
                        $total_ninos += $ninos;
                        $total_pax += $pax;
                        
                        $row_number++;
                    }
                    
                    // Fila de totales
                    $totales = [
                        'TOTALES',
                        '',
                        number_format($total_valor, 2, '.', ','),
                        $total_dias,
                        $total_adultos,
                        $total_ninos,
                        $total_pax,
                        '',
                        ''
                    ];
                    fputcsv($output, $totales);
                    
                    fclose($output);
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
