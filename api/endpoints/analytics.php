<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../lib/AnalyticsEngine.php';
include_once '../../lib/CacheSystem.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['accion'])) {
            switch($_GET['accion']) {
                case 'dashboard_kpis':
                    // Usar caché para KPIs
                    $cache = CacheSystem::getInstance();
                    $kpis = $cache->get('dashboard_kpis');
                    
                    if (!$kpis) {
                        $analytics = new AnalyticsEngine($database);
                        $kpis = $analytics->getAdvancedKPIs();
                        $cache->set('dashboard_kpis', $kpis, 300); // 5 minutos
                    }
                    
                    http_response_code(200);
                    echo json_encode($kpis);
                    break;
                    
                case 'ocupacion_tiempo_real':
                    $cache = CacheSystem::getInstance();
                    $ocupacion = $cache->get('ocupacion_realtime');
                    
                    if (!$ocupacion) {
                        $stmt = $db->query("
                            SELECT 
                                h.id,
                                h.numero,
                                h.tipo,
                                h.estado,
                                CASE 
                                    WHEN r.estado = 'confirmada' AND r.fecha_entrada <= CURRENT_DATE AND r.fecha_salida > CURRENT_DATE THEN 'ocupada'
                                    WHEN h.estado = 'mantenimiento' THEN 'mantenimiento'
                                    ELSE h.estado
                                END as estado_real,
                                COALESCE(r.precio_total, 0) as revenue_actual,
                                COALESCE(c.nombre, 'Sin asignar') as cliente_actual
                            FROM habitaciones h
                            LEFT JOIN reservas r ON h.id = r.habitacion_id 
                                AND r.estado = 'confirmada' 
                                AND r.fecha_entrada <= CURRENT_DATE 
                                AND r.fecha_salida > CURRENT_DATE
                            LEFT JOIN clientes c ON r.cliente_id = c.id
                            WHERE h.deleted_at IS NULL
                            ORDER BY h.numero
                        ");
                        $habitaciones = $stmt->fetchAll();
                        
                        $ocupacion = [
                            'habitaciones' => $habitaciones,
                            'totales' => [
                                'total' => count($habitaciones),
                                'disponibles' => count(array_filter($habitaciones, fn($h) => $h['estado_real'] === 'disponible')),
                                'ocupadas' => count(array_filter($habitaciones, fn($h) => $h['estado_real'] === 'ocupada')),
                                'mantenimiento' => count(array_filter($habitaciones, fn($h) => $h['estado_real'] === 'mantenimiento')),
                                'porcentaje' => 0
                            ],
                            'timestamp' => date('Y-m-d H:i:s')
                        ];
                        
                        $ocupacion['totales']['porcentaje'] = $ocupacion['totales']['total'] > 0 ? 
                            round(($ocupacion['totales']['ocupadas'] / $ocupacion['totales']['total']) * 100, 1) : 0;
                        
                        $cache->set('ocupacion_realtime', $ocupacion, 60); // 1 minuto
                    }
                    
                    http_response_code(200);
                    echo json_encode($ocupacion);
                    break;
                    
                case 'revenue_chart':
                    $periodo = $_GET['periodo'] ?? '30'; // días
                    
                    $cache = CacheSystem::getInstance();
                    $cache_key = "revenue_chart_{$periodo}";
                    $revenue_data = $cache->get($cache_key);
                    
                    if (!$revenue_data) {
                        $stmt = $db->prepare("
                            SELECT 
                                DATE(fecha_entrada) as fecha,
                                SUM(precio_total) as revenue,
                                COUNT(*) as reservas,
                                AVG(precio_total) as avg_ticket
                            FROM reservas 
                            WHERE deleted_at IS NULL 
                            AND estado = 'confirmada'
                            AND fecha_entrada >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
                            GROUP BY DATE(fecha_entrada)
                            ORDER BY fecha ASC
                        ");
                        $stmt->execute([$periodo]);
                        $data = $stmt->fetchAll();
                        
                        // Procesar datos para gráfico
                        $chart_data = [];
                        $running_total = 0;
                        
                        foreach ($data as $row) {
                            $running_total += $row['revenue'];
                            $chart_data[] = [
                                'fecha' => $row['fecha'],
                                'revenue' => (float)$row['revenue'],
                                'reservas' => (int)$row['reservas'],
                                'avg_ticket' => round($row['avg_ticket'], 2),
                                'acumulado' => $running_total
                            ];
                        }
                        
                        $revenue_data = [
                            'data' => $chart_data,
                            'periodo' => $periodo,
                            'totales' => [
                                'revenue' => $running_total,
                                'reservas' => array_sum(array_column($data, 'reservas')),
                                'avg_ticket' => count($data) > 0 ? round($running_total / array_sum(array_column($data, 'reservas')), 2) : 0
                            ]
                        ];
                        
                        $cache->set($cache_key, $revenue_data, 1800); // 30 minutos
                    }
                    
                    http_response_code(200);
                    echo json_encode($revenue_data);
                    break;
                    
                case 'top_habitaciones':
                    $cache = CacheSystem::getInstance();
                    $top_habs = $cache->get('top_habitaciones');
                    
                    if (!$top_habs) {
                        $stmt = $db->query("
                            SELECT 
                                h.tipo,
                                COUNT(*) as total_reservas,
                                SUM(r.precio_total) as total_revenue,
                                AVG(r.precio_total) as avg_revenue,
                                AVG(DATEDIFF(r.fecha_salida, r.fecha_entrada)) as avg_estadia,
                                COUNT(DISTINCT r.cliente_id) as clientes_unicos
                            FROM reservas r
                            JOIN habitaciones h ON r.habitacion_id = h.id
                            WHERE r.deleted_at IS NULL 
                            AND r.estado = 'confirmada'
                            AND r.fecha_entrada >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                            GROUP BY h.tipo
                            ORDER BY total_revenue DESC
                        ");
                        $data = $stmt->fetchAll();
                        
                        $top_habs = [
                            'data' => $data,
                            'periodo' => '30 días',
                            'timestamp' => date('Y-m-d H:i:s')
                        ];
                        
                        $cache->set('top_habitaciones', $top_habs, 3600); // 1 hora
                    }
                    
                    http_response_code(200);
                    echo json_encode($top_habs);
                    break;
                    
                case 'clientes_frecuentes':
                    $cache = CacheSystem::getInstance();
                    $clientes_freq = $cache->get('clientes_frecuentes');
                    
                    if (!$clientes_freq) {
                        $stmt = $db->query("
                            SELECT 
                                c.id,
                                c.nombre,
                                c.apellido,
                                c.email,
                                c.pais,
                                COUNT(r.id) as total_reservas,
                                SUM(r.precio_total) as total_gastado,
                                AVG(r.precio_total) as avg_gasto,
                                MAX(r.fecha_entrada) as ultima_visita,
                                AVG(DATEDIFF(r.fecha_salida, r.fecha_entrada)) as avg_estadia
                            FROM clientes c
                            JOIN reservas r ON c.id = r.cliente_id
                            WHERE r.deleted_at IS NULL 
                            AND r.estado = 'confirmada'
                            AND r.fecha_entrada >= DATE_SUB(CURRENT_DATE, INTERVAL 180 DAY)
                            GROUP BY c.id
                            HAVING total_reservas >= 2
                            ORDER BY total_reservas DESC, total_gastado DESC
                            LIMIT 20
                        ");
                        $data = $stmt->fetchAll();
                        
                        $clientes_freq = [
                            'data' => $data,
                            'periodo' => '6 meses',
                            'timestamp' => date('Y-m-d H:i:s')
                        ];
                        
                        $cache->set('clientes_frecuentes', $clientes_freq, 7200); // 2 horas
                    }
                    
                    http_response_code(200);
                    echo json_encode($clientes_freq);
                    break;
                    
                case 'tendencias_demanda':
                    $cache = CacheSystem::getInstance();
                    $tendencias = $cache->get('tendencias_demanda');
                    
                    if (!$tendencias) {
                        // Análisis por día de semana
                        $stmt = $db->query("
                            SELECT 
                                DAYOFWEEK(fecha_entrada) as dia_semana,
                                DAYNAME(fecha_entrada) as nombre_dia,
                                COUNT(*) as reservas,
                                SUM(precio_total) as revenue,
                                AVG(precio_total) as avg_price
                            FROM reservas 
                            WHERE deleted_at IS NULL 
                            AND estado = 'confirmada'
                            AND fecha_entrada >= DATE_SUB(CURRENT_DATE, INTERVAL 90 DAY)
                            GROUP BY DAYOFWEEK(fecha_entrada), DAYNAME(fecha_entrada)
                            ORDER BY dia_semana
                        ");
                        $por_dia = $stmt->fetchAll();
                        
                        // Análisis por mes
                        $stmt = $db->query("
                            SELECT 
                                MONTH(fecha_entrada) as mes,
                                MONTHNAME(fecha_entrada) as nombre_mes,
                                COUNT(*) as reservas,
                                SUM(precio_total) as revenue,
                                AVG(precio_total) as avg_price
                            FROM reservas 
                            WHERE deleted_at IS NULL 
                            AND estado = 'confirmada'
                            AND fecha_entrada >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH)
                            GROUP BY MONTH(fecha_entrada), MONTHNAME(fecha_entrada)
                            ORDER BY mes
                        ");
                        $por_mes = $stmt->fetchAll();
                        
                        // Análisis por motivo de viaje
                        $stmt = $db->query("
                            SELECT 
                                motivo_viaje,
                                COUNT(*) as reservas,
                                SUM(precio_total) as revenue,
                                AVG(precio_total) as avg_price,
                                ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER(), 1) as porcentaje
                            FROM reservas 
                            WHERE deleted_at IS NULL 
                            AND estado = 'confirmada'
                            AND motivo_viaje IS NOT NULL
                            AND fecha_entrada >= DATE_SUB(CURRENT_DATE, INTERVAL 90 DAY)
                            GROUP BY motivo_viaje
                            ORDER BY reservas DESC
                        ");
                        $por_motivo = $stmt->fetchAll();
                        
                        $tendencias = [
                            'por_dia_semana' => $por_dia,
                            'por_mes' => $por_mes,
                            'por_motivo_viaje' => $por_motivo,
                            'periodo_analizado' => '90 días',
                            'timestamp' => date('Y-m-d H:i:s')
                        ];
                        
                        $cache->set('tendencias_demanda', $tendencias, 5400); // 1.5 horas
                    }
                    
                    http_response_code(200);
                    echo json_encode($tendencias);
                    break;
                    
                case 'metricas_operativas':
                    $cache = CacheSystem::getInstance();
                    $metricas = $cache->get('metricas_operativas');
                    
                    if (!$metricas) {
                        // Tiempo promedio de respuesta
                        $stmt = $db->query("
                            SELECT 
                                AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as tiempo_respuesta,
                                COUNT(*) as total_reservas
                            FROM reservas 
                            WHERE deleted_at IS NULL
                            AND estado IN ('confirmada', 'cancelada')
                            AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
                        ");
                        $tiempo_respuesta = $stmt->fetch();
                        
                        // Tasa de cancelación
                        $stmt = $db->query("
                            SELECT 
                                COUNT(*) as total,
                                SUM(CASE WHEN estado = 'cancelada' THEN 1 ELSE 0 END) as canceladas
                            FROM reservas 
                            WHERE deleted_at IS NULL
                            AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                        ");
                        $cancelacion = $stmt->fetch();
                        $tasa_cancelacion = $cancelacion['total'] > 0 ? 
                            ($cancelacion['canceladas'] / $cancelacion['total']) * 100 : 0;
                        
                        // Ocupación por tipo de habitación
                        $stmt = $db->query("
                            SELECT 
                                h.tipo,
                                COUNT(*) as total_habitaciones,
                                SUM(CASE WHEN r.estado = 'confirmada' 
                                    AND r.fecha_entrada <= CURRENT_DATE 
                                    AND r.fecha_salida > CURRENT_DATE THEN 1 ELSE 0 END) as ocupadas
                            FROM habitaciones h
                            LEFT JOIN reservas r ON h.id = r.habitacion_id 
                                AND r.estado = 'confirmada' 
                                AND r.fecha_entrada <= CURRENT_DATE 
                                AND r.fecha_salida > CURRENT_DATE
                            WHERE h.deleted_at IS NULL
                            GROUP BY h.tipo
                        ");
                        $ocupacion_tipo = $stmt->fetchAll();
                        
                        $metricas = [
                            'tiempo_respuesta' => [
                                'promedio_minutos' => round($tiempo_respuesta['tiempo_respuesta'], 1),
                                'total_reservas' => (int)$tiempo_respuesta['total_reservas']
                            ],
                            'tasa_cancelacion' => [
                                'porcentaje' => round($tasa_cancelacion, 1),
                                'total' => (int)$cancelacion['total'],
                                'canceladas' => (int)$cancelacion['canceladas']
                            ],
                            'ocupacion_tipo' => array_map(function($row) {
                                return [
                                    'tipo' => $row['tipo'],
                                    'total' => (int)$row['total_habitaciones'],
                                    'ocupadas' => (int)$row['ocupadas'],
                                    'porcentaje' => $row['total_habitaciones'] > 0 ? 
                                        round(($row['ocupadas'] / $row['total_habitaciones']) * 100, 1) : 0
                                ];
                            }, $ocupacion_tipo),
                            'timestamp' => date('Y-m-d H:i:s')
                        ];
                        
                        $cache->set('metricas_operativas', $metricas, 2400); // 40 minutos
                    }
                    
                    http_response_code(200);
                    echo json_encode($metricas);
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
