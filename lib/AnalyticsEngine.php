<?php
/**
 * AnalyticsEngine - Motor de análisis avanzado para el hotel
 * Proporciona insights predictivos y KPIs inteligentes
 */
class AnalyticsEngine {
    private $db;
    private $cache = [];
    private $cache_ttl = 300; // 5 minutos
    
    public function __construct($database) {
        $this->db = $database->getConnection();
    }
    
    /**
     * Obtener KPIs avanzados del dashboard
     */
    public function getAdvancedKPIs() {
        $cache_key = 'advanced_kpis_' . date('Y-m-d-H');
        
        if (isset($this->cache[$cache_key])) {
            return $this->cache[$cache_key];
        }
        
        $kpis = [
            'ocupacion' => $this->getOcupacionTendencia(),
            'revenue' => $this->getRevenueAnalytics(),
            'predictivo' => $this->getPredictiveAnalytics(),
            'satisfaccion' => $this->getSatisfactionMetrics(),
            'operacional' => $this->getOperationalMetrics()
        ];
        
        $this->cache[$cache_key] = $kpis;
        return $kpis;
    }
    
    /**
     * Análisis de ocupación con tendencias
     */
    private function getOcupacionTendencia() {
        // Ocupación actual
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_habitaciones,
                SUM(CASE WHEN estado = 'ocupada' THEN 1 ELSE 0 END) as ocupadas,
                SUM(CASE WHEN estado = 'disponible' THEN 1 ELSE 0 END) as disponibles,
                SUM(CASE WHEN estado = 'mantenimiento' THEN 1 ELSE 0 END) as mantenimiento
            FROM habitaciones 
            WHERE deleted_at IS NULL
        ");
        $actual = $stmt->fetch();
        
        // Tendencia última semana
        $stmt = $this->db->query("
            SELECT 
                DATE(fecha_entrada) as fecha,
                COUNT(*) as reservas,
                SUM(precio_total) as revenue
            FROM reservas 
            WHERE fecha_entrada >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
            AND deleted_at IS NULL
            GROUP BY DATE(fecha_entrada)
            ORDER BY fecha DESC
        ");
        $tendencia = $stmt->fetchAll();
        
        // Predicción próxima semana
        $prediccion = $this->predecirOcupacion($tendencia);
        
        return [
            'actual' => [
                'total' => (int)$actual['total_habitaciones'],
                'ocupadas' => (int)$actual['ocupadas'],
                'disponibles' => (int)$actual['disponibles'],
                'mantenimiento' => (int)$actual['mantenimiento'],
                'porcentaje' => $actual['total_habitaciones'] > 0 ? 
                    round(($actual['ocupadas'] / $actual['total_habitaciones']) * 100, 1) : 0
            ],
            'tendencia' => $tendencia,
            'prediccion' => $prediccion,
            'recomendacion' => $this->generarRecomendacionOcupacion($actual, $prediccion)
        ];
    }
    
    /**
     * Análisis de revenue con métricas avanzadas
     */
    private function getRevenueAnalytics() {
        // Revenue mensual comparado
        $stmt = $this->db->query("
            SELECT 
                MONTH(fecha_entrada) as mes,
                YEAR(fecha_entrada) as anio,
                SUM(precio_total) as revenue,
                COUNT(*) as reservas,
                AVG(precio_total) as ticket_promedio
            FROM reservas 
            WHERE deleted_at IS NULL 
            AND estado = 'confirmada'
            AND fecha_entrada >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH)
            GROUP BY YEAR(fecha_entrada), MONTH(fecha_entrada)
            ORDER BY anio DESC, mes DESC
            LIMIT 12
        ");
        $mensual = $stmt->fetchAll();
        
        // Revenue por tipo de habitación
        $stmt = $this->db->query("
            SELECT 
                h.tipo,
                COUNT(*) as reservas,
                SUM(r.precio_total) as revenue,
                AVG(r.precio_total) as avg_price
            FROM reservas r
            JOIN habitaciones h ON r.habitacion_id = h.id
            WHERE r.deleted_at IS NULL 
            AND r.estado = 'confirmada'
            AND r.fecha_entrada >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
            GROUP BY h.tipo
            ORDER BY revenue DESC
        ");
        $por_tipo = $stmt->fetchAll();
        
        // Métricas de rendimiento
        $stmt = $this->db->query("
            SELECT 
                SUM(precio_total) / COUNT(DISTINCT habitacion_id) as revenue_por_habitacion,
                COUNT(*) / COUNT(DISTINCT habitacion_id) as ocupancia_promedio,
                AVG(DATEDIFF(fecha_salida, fecha_entrada)) as estadia_promedio
            FROM reservas 
            WHERE deleted_at IS NULL 
            AND estado = 'confirmada'
            AND fecha_entrada >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
        ");
        $rendimiento = $stmt->fetch();
        
        return [
            'mensual' => $mensual,
            'por_tipo' => $por_tipo,
            'rendimiento' => [
                'revenue_por_habitacion' => round($rendimiento['revenue_por_habitacion'], 2),
                'ocupancia_promedio' => round($rendimiento['ocupancia_promedio'], 1),
                'estadia_promedio' => round($rendimiento['estadia_promedio'], 1)
            ],
            'crecimiento' => $this->calcularCrecimientoRevenue($mensual)
        ];
    }
    
    /**
     * Análisis predictivo avanzado
     */
    private function getPredictiveAnalytics() {
        // Predicción de demanda
        $stmt = $this->db->query("
            SELECT 
                DAYOFWEEK(fecha_entrada) as dia_semana,
                MONTH(fecha_entrada) as mes,
                COUNT(*) as demanda
            FROM reservas 
            WHERE deleted_at IS NULL
            AND fecha_entrada >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
            GROUP BY DAYOFWEEK(fecha_entrada), MONTH(fecha_entrada)
            ORDER BY demanda DESC
            LIMIT 10
        ");
        $patrones = $stmt->fetchAll();
        
        // Predicción de cancelaciones
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_reservas,
                SUM(CASE WHEN estado = 'cancelada' THEN 1 ELSE 0 END) as canceladas
            FROM reservas 
            WHERE deleted_at IS NULL
            AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
        ");
        $cancelacion = $stmt->fetch();
        $tasa_cancelacion = $cancelacion['total_reservas'] > 0 ? 
            ($cancelacion['canceladas'] / $cancelacion['total_reservas']) * 100 : 0;
        
        return [
            'patrones_demanda' => $patrones,
            'tasa_cancelacion' => round($tasa_cancelacion, 1),
            'prediccion_cancelaciones' => $this->predecirCancelaciones($tasa_cancelacion),
            'recomendaciones' => $this->generarRecomendacionesPredictivas($patrones, $tasa_cancelacion)
        ];
    }
    
    /**
     * Métricas de satisfacción (simuladas basadas en comportamiento)
     */
    private function getSatisfactionMetrics() {
        // Métricas basadas en patrones de comportamiento
        $stmt = $this->db->query("
            SELECT 
                AVG(DATEDIFF(fecha_salida, fecha_entrada)) as estadia_promedio,
                COUNT(*) as total_reservas,
                SUM(CASE WHEN motivo_viaje = 'turismo' THEN 1 ELSE 0 END) as turismo,
                SUM(CASE WHEN motivo_viaje = 'negocios' THEN 1 ELSE 0 END) as negocios
            FROM reservas r
            WHERE r.deleted_at IS NULL 
            AND r.estado = 'confirmada'
            AND r.fecha_entrada >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
        ");
        $datos = $stmt->fetch();
        
        // Calcular índice de satisfacción simulado
        $satisfaccion = $this->calcularSatisfactionScore($datos);
        
        return [
            'score_general' => $satisfaccion['general'],
            'estadia_promedio' => round($datos['estadia_promedio'], 1),
            'tasa_retorno' => $satisfaccion['tasa_retorno'],
            'segmentacion' => [
                'turismo' => $datos['turismo'],
                'negocios' => $datos['negocios'],
                'otros' => $datos['total_reservas'] - $datos['turismo'] - $datos['negocios']
            ],
            'recomendaciones' => $satisfaccion['recomendaciones']
        ];
    }
    
    /**
     * Métricas operativas
     */
    private function getOperationalMetrics() {
        // Eficiencia del personal
        $stmt = $this->db->query("
            SELECT 
                COUNT(DISTINCT u.id) as total_personal,
                COUNT(DISTINCT r.usuario_id) as personal_activo
            FROM usuarios u
            LEFT JOIN reservas r ON u.id = r.usuario_id 
            AND r.created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
            WHERE u.deleted_at IS NULL
        ");
        $personal = $stmt->fetch();
        
        // Tiempo de respuesta (simulado)
        $stmt = $this->db->query("
            SELECT 
                AVG(TIMESTAMPDIFF(MINUTE, created_at, 
                    CASE 
                        WHEN estado = 'confirmada' THEN updated_at 
                        ELSE created_at 
                    END
                )) as tiempo_respuesta
            FROM reservas 
            WHERE deleted_at IS NULL
            AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
        ");
        $tiempo = $stmt->fetch();
        
        return [
            'eficiencia_personal' => [
                'total' => (int)$personal['total_personal'],
                'activo' => (int)$personal['personal_activo'],
                'porcentaje_activo' => $personal['total_personal'] > 0 ? 
                    round(($personal['personal_activo'] / $personal['total_personal']) * 100, 1) : 0
            ],
            'tiempo_respuesta' => round($tiempo['tiempo_respuesta'], 1),
            'productividad' => $this->calcularProductividad($personal, $tiempo)
        ];
    }
    
    /**
     * Algoritmo de predicción simple
     */
    private function predecirOcupacion($tendencia) {
        if (count($tendencia) < 3) return [];
        
        // Calcular tendencia lineal simple
        $n = count($tendencia);
        $suma_x = 0;
        $suma_y = 0;
        $suma_xy = 0;
        $suma_x2 = 0;
        
        foreach ($tendencia as $i => $dato) {
            $x = $i;
            $y = $dato['reservas'];
            $suma_x += $x;
            $suma_y += $y;
            $suma_xy += $x * $y;
            $suma_x2 += $x * $x;
        }
        
        $pendiente = ($n * $suma_xy - $suma_x * $suma_y) / ($n * $suma_x2 - $suma_x * $suma_x);
        $intercepto = ($suma_y - $pendiente * $suma_x) / $n;
        
        // Predecir próximos 7 días
        $prediccion = [];
        for ($i = 1; $i <= 7; $i++) {
            $x_pred = $n + $i;
            $y_pred = $pendiente * $x_pred + $intercepto;
            $prediccion[] = [
                'fecha' => date('Y-m-d', strtotime("+$i days")),
                'prediccion' => max(0, round($y_pred)),
                'confianza' => max(0, min(100, 100 - ($i * 10))) // Confianza decreciente
            ];
        }
        
        return $prediccion;
    }
    
    /**
     * Calcular crecimiento de revenue
     */
    private function calcularCrecimientoRevenue($mensual) {
        if (count($mensual) < 2) return 0;
        
        $ultimo = $mensual[0]['revenue'];
        $anterior = $mensual[1]['revenue'];
        
        return $anterior > 0 ? round((($ultimo - $anterior) / $anterior) * 100, 1) : 0;
    }
    
    /**
     * Calcular score de satisfacción
     */
    private function calcularSatisfactionScore($datos) {
        // Score basado en múltiples factores
        $score_estadia = $datos['estadia_promedio'] > 2 ? 85 : 70;
        $score_turismo = $datos['turismo'] > $datos['negocios'] ? 90 : 75;
        $score_general = round(($score_estadia + $score_turismo) / 2);
        
        return [
            'general' => $score_general,
            'tasa_retorno' => round(85 + ($score_general - 70) * 0.3, 1),
            'recomendaciones' => [
                'Mejorar experiencia de estancia larga' => $datos['estadia_promedio'] < 2,
                'Promover paquetes de negocios' => $datos['negocios'] > $datos['turismo'],
                'Optimizar para turismo' => $datos['turismo'] > $datos['negocios']
            ]
        ];
    }
    
    /**
     * Generar recomendaciones inteligentes
     */
    private function generarRecomendacionOcupacion($actual, $prediccion) {
        $recomendaciones = [];
        
        if ($actual['porcentaje'] > 90) {
            $recomendaciones[] = "Alta ocupación detectada. Considere aumentar precios.";
        }
        
        if ($actual['porcentaje'] < 50) {
            $recomendaciones[] = "Baja ocupación. Recomendamos campañas promocionales.";
        }
        
        if (!empty($prediccion)) {
            $promedio_prediccion = array_sum(array_column($prediccion, 'prediccion')) / count($prediccion);
            if ($promedio_prediccion > $actual['ocupadas']) {
                $recomendaciones[] = "Se espera aumento de demanda. Prepare personal adicional.";
            }
        }
        
        return $recomendaciones;
    }
    
    /**
     * Predecir cancelaciones
     */
    private function predecirCancelaciones($tasa_actual) {
        return [
            'tasa_actual' => $tasa_actual,
            'prediccion_semana' => round($tasa_actual * 1.1, 1), // 10% más que el actual
            'factor_riesgo' => $tasa_actual > 15 ? 'Alto' : ($tasa_actual > 10 ? 'Medio' : 'Bajo')
        ];
    }
    
    /**
     * Generar recomendaciones predictivas
     */
    private function generarRecomendacionesPredictivas($patrones, $tasa_cancelacion) {
        $recomendaciones = [];
        
        if ($tasa_cancelacion > 15) {
            $recomendaciones[] = "Alta tasa de cancelación. Considere políticas más flexibles.";
        }
        
        if (!empty($patrones)) {
            $mejor_dia = $patrones[0]['dia_semana'];
            $recomendaciones[] = "Mayor demanda los días $mejor_dia. Ajuste precios dinámicamente.";
        }
        
        return $recomendaciones;
    }
    
    /**
     * Calcular productividad
     */
    private function calcularProductividad($personal, $tiempo) {
        $eficiencia = $personal['total_personal'] > 0 ? 
            ($personal['personal_activo'] / $personal['total_personal']) * 100 : 0;
        
        $velocidad = $tiempo['tiempo_respuesta'] > 0 ? 
            max(0, 100 - $tiempo['tiempo_respuesta']) : 100;
        
        return [
            'eficiencia' => round($eficiencia, 1),
            'velocidad' => round($velocidad, 1),
            'general' => round(($eficiencia + $velocidad) / 2, 1)
        ];
    }
}
?>
