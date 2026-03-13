<?php

namespace App\Services;

use Database\Database;
use App\Services\AppLogger;
use Exception;

class DynamicPricingService {
    
    /**
     * Calculate dynamic price for room and dates
     */
    public function calculateDynamicPrice(int $habitacionId, string $fechaEntrada, string $fechaSalida): array {
        // Get base room info
        $roomSql = "SELECT * FROM habitaciones WHERE id = :id AND deleted_at IS NULL";
        $room = Database::fetch($roomSql, [':id' => $habitacionId]);
        
        if (!$room) {
            throw new Exception('Room not found');
        }
        
        $basePrice = $room['precio_base'];
        $roomType = $room['tipo'];
        
        // Get seasonal pricing
        $seasonalPrice = $this->getSeasonalPrice($roomType, $fechaEntrada, $fechaSalida);
        $currentPrice = $seasonalPrice ?: $basePrice;
        
        // Apply pricing rules
        $appliedRules = [];
        $finalPrice = $this->applyPricingRules($currentPrice, $habitacionId, $fechaEntrada, $fechaSalida, $appliedRules);
        
        // Calculate nights
        $nights = $this->calculateNights($fechaEntrada, $fechaSalida);
        $totalPrice = $finalPrice * $nights;
        
        // Record pricing history
        $this->recordPricingHistory($habitacionId, $fechaEntrada, $basePrice, $finalPrice, $appliedRules);
        
        return [
            'base_price' => $basePrice,
            'seasonal_price' => $seasonalPrice,
            'final_price' => $finalPrice,
            'total_price' => $totalPrice,
            'nights' => $nights,
            'applied_rules' => $appliedRules,
            'price_per_night' => $finalPrice
        ];
    }
    
    /**
     * Get seasonal pricing for date range
     */
    public function getSeasonalPrice(string $roomType, string $fechaEntrada, string $fechaSalida): ?float {
        $sql = "SELECT precio FROM tarifas 
                WHERE tipo_habitacion_id = :room_type
                AND fecha_inicio <= :fecha_entrada 
                AND fecha_fin >= :fecha_salida
                AND activa = 1 
                AND deleted_at IS NULL
                ORDER BY prioridad DESC, precio ASC
                LIMIT 1";
        
        $result = Database::fetch($sql, [
            ':room_type' => $roomType,
            ':fecha_entrada' => $fechaEntrada,
            ':fecha_salida' => $fechaSalida
        ]);
        
        return $result ? (float) $result['precio'] : null;
    }
    
    /**
     * Apply pricing rules to base price
     */
    public function applyPricingRules(float $basePrice, int $habitacionId, string $fechaEntrada, string $fechaSalida, array &$appliedRules): float {
        $finalPrice = $basePrice;
        
        // Get applicable pricing rules
        $rules = $this->getApplicableRules($habitacionId, $fechaEntrada, $fechaSalida);
        
        foreach ($rules as $rule) {
            $adjustment = $this->calculateRuleAdjustment($finalPrice, $rule, $fechaEntrada, $fechaSalida);
            
            if ($adjustment != 0) {
                $finalPrice += $adjustment;
                $appliedRules[] = [
                    'rule_name' => $rule['nombre'],
                    'rule_type' => $rule['tipo_regla'],
                    'adjustment' => $adjustment,
                    'adjustment_percentage' => ($adjustment / $basePrice) * 100
                ];
            }
        }
        
        // Ensure minimum price
        $minPrice = $basePrice * 0.5; // Minimum 50% of base price
        $finalPrice = max($finalPrice, $minPrice);
        
        return round($finalPrice, 2);
    }
    
    /**
     * Get applicable pricing rules
     */
    private function getApplicableRules(int $habitacionId, string $fechaEntrada, string $fechaSalida): array {
        // Get room type
        $roomSql = "SELECT tipo FROM habitaciones WHERE id = :id AND deleted_at IS NULL";
        $room = Database::fetch($roomSql, [':id' => $habitacionId]);
        $roomType = $room ? $room['tipo'] : null;
        
        // Calculate days until check-in
        $daysUntilCheckin = $this->calculateDaysUntil($fechaEntrada);
        
        // Get current occupancy for dates
        $occupancy = $this->getOccupancyForDates($fechaEntrada, $fechaSalida);
        
        $sql = "SELECT * FROM pricing_rules 
                WHERE activa = 1 
                AND deleted_at IS NULL
                AND (:room_type IS NULL OR tipo_habitacion_id IS NULL OR tipo_habitacion_id = :room_type)
                AND dias_anticipacion_min <= :days_until
                AND dias_anticipacion_max >= :days_until
                AND umbral_ocupacion_min <= :occupancy
                AND umbral_ocupacion_max >= :occupancy
                ORDER BY prioridad DESC";
        
        return Database::fetchAll($sql, [
            ':room_type' => $roomType,
            ':days_until' => $daysUntilCheckin,
            ':occupancy' => $occupancy
        ]);
    }
    
    /**
     * Calculate rule adjustment
     */
    private function calculateRuleAdjustment(float $currentPrice, array $rule, string $fechaEntrada, string $fechaSalida): float {
        $adjustment = 0;
        
        switch ($rule['tipo_regla']) {
            case 'aumento_porcentaje':
                $adjustment = $currentPrice * ($rule['valor'] / 100);
                break;
                
            case 'disminucion_porcentaje':
                $adjustment = -($currentPrice * (abs($rule['valor']) / 100));
                break;
                
            case 'monto_fijo':
                $adjustment = $rule['valor'];
                break;
                
            case 'ultimo_minuto':
                $daysUntil = $this->calculateDaysUntil($fechaEntrada);
                if ($daysUntil <= 3) {
                    $adjustment = $currentPrice * ($rule['valor'] / 100);
                }
                break;
                
            case 'anticipado':
                $daysUntil = $this->calculateDaysUntil($fechaEntrada);
                if ($daysUntil >= 30) {
                    $adjustment = -($currentPrice * (abs($rule['valor']) / 100));
                }
                break;
                
            case 'ocupacion_alta':
                $occupancy = $this->getOccupancyForDates($fechaEntrada, $fechaSalida);
                if ($occupancy >= $rule['umbral_ocupacion_min']) {
                    $adjustment = $currentPrice * ($rule['valor'] / 100);
                }
                break;
                
            case 'ocupacion_baja':
                $occupancy = $this->getOccupancyForDates($fechaEntrada, $fechaSalida);
                if ($occupancy <= $rule['umbral_ocupacion_max']) {
                    $adjustment = -($currentPrice * (abs($rule['valor']) / 100));
                }
                break;
        }
        
        return $adjustment;
    }
    
    /**
     * Get occupancy for date range
     */
    private function getOccupancyForDates(string $fechaEntrada, string $fechaSalida): float {
        $totalRoomsSql = "SELECT COUNT(*) as total FROM habitaciones WHERE deleted_at IS NULL";
        $totalRooms = Database::fetch($totalRoomsSql)['total'];
        
        if ($totalRooms == 0) {
            return 0;
        }
        
        $occupiedRoomsSql = "SELECT COUNT(DISTINCT habitacion_id) as occupied
                            FROM reservas r
                            WHERE r.estado IN ('confirmada', 'ocupada')
                            AND r.deleted_at IS NULL
                            AND (
                                (r.fecha_entrada <= :fecha_salida AND r.fecha_salida > :fecha_entrada)
                            )";
        
        $occupiedRooms = Database::fetch($occupiedRoomsSql, [
            ':fecha_entrada' => $fechaEntrada,
            ':fecha_salida' => $fechaSalida
        ])['occupied'];
        
        return ($occupiedRooms / $totalRooms) * 100;
    }
    
    /**
     * Calculate days until date
     */
    private function calculateDaysUntil(string $date): int {
        $targetDate = new \DateTime($date);
        $today = new \DateTime();
        return $today->diff($targetDate)->days;
    }
    
    /**
     * Calculate nights between dates
     */
    private function calculateNights(string $checkin, string $checkout): int {
        $checkinDate = new \DateTime($checkin);
        $checkoutDate = new \DateTime($checkout);
        return $checkinDate->diff($checkoutDate)->days;
    }
    
    /**
     * Record pricing history
     */
    private function recordPricingHistory(int $habitacionId, string $fecha, float $basePrice, float $finalPrice, array $appliedRules): void {
        $occupancy = $this->getOccupancyForDates($fecha, $fecha);
        $reservationsCount = $this->getReservationsCount($fecha);
        
        $sql = "INSERT INTO pricing_history 
                (habitacion_id, fecha, precio_base, precio_final, reglas_aplicadas, ocupacion_diaria, reservas_diarias, hotel_id) 
                VALUES (:habitacion_id, :fecha, :precio_base, :precio_final, :reglas_aplicadas, :ocupacion_diaria, :reservas_diarias, :hotel_id)
                ON DUPLICATE KEY UPDATE 
                    precio_base = VALUES(precio_base),
                    precio_final = VALUES(precio_final),
                    reglas_aplicadas = VALUES(reglas_aplicadas),
                    ocupacion_diaria = VALUES(ocupacion_diaria),
                    reservas_diarias = VALUES(reservas_diarias)";
        
        Database::execute($sql, [
            ':habitacion_id' => $habitacionId,
            ':fecha' => $fecha,
            ':precio_base' => $basePrice,
            ':precio_final' => $finalPrice,
            ':reglas_aplicadas' => json_encode($appliedRules),
            ':ocupacion_diaria' => $occupancy,
            ':reservas_diarias' => $reservationsCount,
            ':hotel_id' => 1
        ]);
    }
    
    /**
     * Get reservations count for date
     */
    private function getReservationsCount(string $date): int {
        $sql = "SELECT COUNT(*) as count FROM reservas 
                WHERE estado IN ('confirmada', 'ocupada')
                AND deleted_at IS NULL
                AND fecha_entrada <= :date AND fecha_salida > :date";
        
        return Database::fetch($sql, [':date' => $date])['count'];
    }
    
    /**
     * Create pricing rule
     */
    public function createPricingRule(array $ruleData): int {
        $sql = "INSERT INTO pricing_rules 
                (nombre, descripcion, tipo_regla, valor, condiciones, tipo_habitacion_id, 
                 dias_anticipacion_min, dias_anticipacion_max, umbral_ocupacion_min, umbral_ocupacion_max, 
                 activa, prioridad, hotel_id) 
                VALUES (:nombre, :descripcion, :tipo_regla, :valor, :condiciones, :tipo_habitacion_id,
                        :dias_anticipacion_min, :dias_anticipacion_max, :umbral_ocupacion_min, :umbral_ocupacion_max,
                        :activa, :prioridad, :hotel_id)";
        
        Database::execute($sql, $ruleData);
        $ruleId = Database::lastInsertId();
        
        AppLogger::business('Pricing rule created', [
            'rule_id' => $ruleId,
            'rule_name' => $ruleData['nombre'],
            'rule_type' => $ruleData['tipo_regla']
        ]);
        
        return $ruleId;
    }
    
    /**
     * Get pricing analytics
     */
    public function getPricingAnalytics(string $period = 'month'): array {
        $dateCondition = match($period) {
            'day' => 'DATE(ph.fecha) = CURDATE()',
            'week' => 'WEEK(ph.fecha) = WEEK(NOW())',
            'month' => 'MONTH(ph.fecha) = MONTH(NOW()) AND YEAR(ph.fecha) = YEAR(NOW())',
            'year' => 'YEAR(ph.fecha) = YEAR(NOW())',
            default => '1=1'
        };
        
        $sql = "SELECT 
                    AVG(ph.precio_base) as avg_base_price,
                    AVG(ph.precio_final) as avg_final_price,
                    AVG(ph.ocupacion_diaria) as avg_occupancy,
                    SUM(ph.reservas_diarias) as total_reservations,
                    COUNT(*) as days_analyzed,
                    AVG(ph.precio_final - ph.precio_base) as avg_price_adjustment
                FROM pricing_history ph
                WHERE {$dateCondition}";
        
        $analytics = Database::fetch($sql);
        
        // Get most applied rules
        $rulesSql = "SELECT 
                        JSON_UNQUOTE(JSON_EXTRACT(reglas_aplicadas, '$[0].rule_name')) as rule_name,
                        COUNT(*) as application_count
                     FROM pricing_history ph
                     WHERE {$dateCondition} AND reglas_aplicadas IS NOT NULL
                     GROUP BY rule_name
                     ORDER BY application_count DESC
                     LIMIT 5";
        
        $analytics['top_rules'] = Database::fetchAll($rulesSql);
        
        // Get revenue trends
        $revenueSql = "SELECT 
                        DATE(ph.fecha) as date,
                        SUM(ph.precio_final * ph.reservas_diarias) as daily_revenue,
                        AVG(ph.ocupacion_diaria) as daily_occupancy
                      FROM pricing_history ph
                      WHERE {$dateCondition}
                      GROUP BY DATE(ph.fecha)
                      ORDER BY date";
        
        $analytics['revenue_trends'] = Database::fetchAll($revenueSql);
        
        return $analytics;
    }
    
    /**
     * Update competitor pricing
     */
    public function updateCompetitorPricing(array $competitorData): int {
        $sql = "INSERT INTO competitor_pricing 
                (competidor, tipo_habitacion, precio, fecha_registro, fuente, hotel_id) 
                VALUES (:competidor, :tipo_habitacion, :precio, :fecha_registro, :fuente, :hotel_id)";
        
        Database::execute($sql, $competitorData);
        $pricingId = Database::lastInsertId();
        
        AppLogger::business('Competitor pricing updated', [
            'competitor' => $competitorData['competidor'],
            'room_type' => $competitorData['tipo_habitacion'],
            'price' => $competitorData['precio']
        ]);
        
        return $pricingId;
    }
}
