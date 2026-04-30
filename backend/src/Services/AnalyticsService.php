<?php

namespace App\Services;

use Database\Database;
use App\Services\AppLogger;
use Exception;

class AnalyticsService {
    
    /**
     * Get comprehensive dashboard analytics
     */
    public function getDashboardAnalytics(string $period = 'month'): array {
        $dateCondition = $this->getDateCondition($period);
        
        $analytics = [
            'occupancy' => $this->getOccupancyAnalytics($dateCondition),
            'revenue' => $this->getRevenueAnalytics($dateCondition),
            'guests' => $this->getGuestAnalytics($dateCondition),
            'rooms' => $this->getRoomAnalytics($dateCondition),
            'services' => $this->getServiceAnalytics($dateCondition),
            'housekeeping' => $this->getHousekeepingAnalytics($dateCondition),
            'maintenance' => $this->getMaintenanceAnalytics($dateCondition),
            'reviews' => $this->getReviewAnalytics($dateCondition)
        ];
        
        return $analytics;
    }
    
    /**
     * Get occupancy analytics
     */
    public function getOccupancyAnalytics(string $dateCondition): array {
        $sql = "SELECT 
                    COUNT(DISTINCT r.habitacion_id) as total_rooms,
                    COUNT(DISTINCT CASE WHEN r.estado IN ('confirmada', 'ocupada') THEN r.habitacion_id END) as occupied_rooms,
                    ROUND(COUNT(DISTINCT CASE WHEN r.estado IN ('confirmada', 'ocupada') THEN r.habitacion_id END) * 100.0 / COUNT(DISTINCT h.id), 2) as occupancy_rate,
                    AVG(r.precio_total) as avg_reservation_value,
                    COUNT(r.id) as total_reservations
                FROM habitaciones h
                LEFT JOIN reservas r ON h.id = r.habitacion_id 
                    AND r.estado IN ('confirmada', 'ocupada')
                    AND {$dateCondition}
                    AND r.deleted_at IS NULL
                WHERE h.deleted_at IS NULL";
        
        $occupancy = Database::fetch($sql);
        
        // Daily occupancy trend
        $dailySql = "SELECT 
                        DATE(fecha_entrada) as date,
                        COUNT(DISTINCT habitacion_id) as occupied_rooms,
                        ROUND(COUNT(DISTINCT habitacion_id) * 100.0 / (SELECT COUNT(*) FROM habitaciones WHERE deleted_at IS NULL), 2) as occupancy_rate
                    FROM reservas 
                    WHERE {$dateCondition} AND deleted_at IS NULL
                    GROUP BY DATE(fecha_entrada)
                    ORDER BY date";
        
        $occupancy['daily_trend'] = Database::fetchAll($dailySql);
        
        // Occupancy by room type
        $roomTypeSql = "SELECT 
                            h.tipo,
                            COUNT(DISTINCT h.id) as total_rooms,
                            COUNT(DISTINCT r.habitacion_id) as occupied_rooms,
                            ROUND(COUNT(DISTINCT r.habitacion_id) * 100.0 / COUNT(DISTINCT h.id), 2) as occupancy_rate
                        FROM habitaciones h
                        LEFT JOIN reservas r ON h.id = r.habitacion_id 
                            AND r.estado IN ('confirmada', 'ocupada')
                            AND {$dateCondition}
                            AND r.deleted_at IS NULL
                        WHERE h.deleted_at IS NULL
                        GROUP BY h.tipo
                        ORDER BY occupancy_rate DESC";
        
        $occupancy['by_room_type'] = Database::fetchAll($roomTypeSql);
        
        return $occupancy;
    }
    
    /**
     * Get revenue analytics
     */
    public function getRevenueAnalytics(string $dateCondition): array {
        $sql = "SELECT 
                    SUM(r.precio_total) as total_revenue,
                    AVG(r.precio_total) as avg_reservation_value,
                    COUNT(r.id) as total_reservations,
                    SUM(CASE WHEN r.estado = 'confirmada' THEN r.precio_total ELSE 0 END) as confirmed_revenue,
                    SUM(CASE WHEN r.estado = 'finalizada' THEN r.precio_total ELSE 0 END) as collected_revenue
                FROM reservas r
                WHERE {$dateCondition} AND deleted_at IS NULL";
        
        $revenue = Database::fetch($sql);
        
        // Revenue trend
        $trendSql = "SELECT 
                        DATE(fecha_entrada) as date,
                        SUM(precio_total) as daily_revenue,
                        COUNT(*) as daily_reservations
                    FROM reservas 
                    WHERE {$dateCondition} AND deleted_at IS NULL
                    GROUP BY DATE(fecha_entrada)
                    ORDER BY date";
        
        $revenue['daily_trend'] = Database::fetchAll($trendSql);
        
        // Revenue by room type
        $roomTypeSql = "SELECT 
                            h.tipo,
                            SUM(r.precio_total) as revenue,
                            COUNT(r.id) as reservations,
                            AVG(r.precio_total) as avg_price
                        FROM reservas r
                        JOIN habitaciones h ON r.habitacion_id = h.id
                        WHERE {$dateCondition} AND r.deleted_at IS NULL AND h.deleted_at IS NULL
                        GROUP BY h.tipo
                        ORDER BY revenue DESC";
        
        $revenue['by_room_type'] = Database::fetchAll($roomTypeSql);
        
        // Service revenue
        $serviceSql = "SELECT 
                          SUM(rs.total) as service_revenue,
                          COUNT(rs.id) as service_orders,
                          s.nombre as service_name
                       FROM reserva_servicios rs
                       JOIN servicios s ON rs.servicio_id = s.id
                       JOIN reservas r ON rs.reserva_id = r.id
                       WHERE {$dateCondition} AND rs.deleted_at IS NULL AND r.deleted_at IS NULL
                       GROUP BY s.id, s.nombre
                       ORDER BY service_revenue DESC
                       LIMIT 10";
        
        $revenue['top_services'] = Database::fetchAll($serviceSql);
        
        return $revenue;
    }
    
    /**
     * Get guest analytics
     */
    public function getGuestAnalytics(string $dateCondition): array {
        $sql = "SELECT 
                    COUNT(DISTINCT rc.cliente_id) as unique_guests,
                    COUNT(DISTINCT CASE WHEN c.fecha_creacion >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN rc.cliente_id END) as new_guests,
                    AVG(DATEDIFF(r.fecha_salida, r.fecha_entrada)) as avg_stay_length,
                    COUNT(CASE WHEN r.fecha_entrada = CURDATE() THEN 1 END) as checkins_today,
                    COUNT(CASE WHEN r.fecha_salida = CURDATE() THEN 1 END) as checkouts_today
                FROM reserva_clientes rc
                JOIN reservas r ON rc.reserva_id = r.id
                JOIN clientes c ON rc.cliente_id = c.id
                WHERE {$dateCondition} AND r.deleted_at IS NULL AND c.deleted_at IS NULL";
        
        $guests = Database::fetch($sql);
        
        // Top guests by revenue
        $topGuestsSql = "SELECT 
                            c.id, c.nombre, c.apellido, c.email,
                            SUM(r.precio_total) as total_spent,
                            COUNT(r.id) as total_stays,
                            AVG(r.precio_total) as avg_spend
                        FROM clientes c
                        JOIN reserva_clientes rc ON c.id = rc.cliente_id
                        JOIN reservas r ON rc.reserva_id = r.id
                        WHERE {$dateCondition} AND r.deleted_at IS NULL AND c.deleted_at IS NULL
                        GROUP BY c.id, c.nombre, c.apellido, c.email
                        ORDER BY total_spent DESC
                        LIMIT 10";
        
        $guests['top_spenders'] = Database::fetchAll($topGuestsSql);
        
        // Guest nationality distribution
        $nationalitySql = "SELECT 
                              c.pais,
                              COUNT(DISTINCT c.id) as guest_count,
                              COUNT(r.id) as reservations
                          FROM clientes c
                          JOIN reserva_clientes rc ON c.id = rc.cliente_id
                          JOIN reservas r ON rc.reserva_id = r.id
                          WHERE {$dateCondition} AND r.deleted_at IS NULL AND c.deleted_at IS NULL
                          GROUP BY c.pais
                          ORDER BY guest_count DESC
                          LIMIT 10";
        
        $guests['by_nationality'] = Database::fetchAll($nationalitySql);
        
        return $guests;
    }
    
    /**
     * Get room analytics
     */
    public function getRoomAnalytics(string $dateCondition): array {
        $sql = "SELECT 
                    COUNT(*) as total_rooms,
                    COUNT(CASE WHEN estado = 'disponible' THEN 1 END) as available_rooms,
                    COUNT(CASE WHEN estado = 'reservada' THEN 1 END) as reserved_rooms,
                    COUNT(CASE WHEN estado = 'ocupada' THEN 1 END) as occupied_rooms,
                    COUNT(CASE WHEN estado = 'limpieza' THEN 1 END) as cleaning_rooms,
                    COUNT(CASE WHEN estado = 'mantenimiento' THEN 1 END) as maintenance_rooms,
                    AVG(precio_base) as avg_room_price
                FROM habitaciones
                WHERE deleted_at IS NULL";
        
        $rooms = Database::fetch($sql);
        
        // Most occupied rooms
        $occupiedSql = "SELECT 
                            h.numero, h.tipo,
                            COUNT(r.id) as total_occupancies,
                            SUM(DATEDIFF(r.fecha_salida, r.fecha_entrada)) as total_nights,
                            SUM(r.precio_total) as total_revenue
                        FROM habitaciones h
                        JOIN reservas r ON h.id = r.habitacion_id
                        WHERE {$dateCondition} AND r.deleted_at IS NULL AND h.deleted_at IS NULL
                        GROUP BY h.id, h.numero, h.tipo
                        ORDER BY total_occupancies DESC
                        LIMIT 10";
        
        $rooms['most_occupied'] = Database::fetchAll($occupiedSql);
        
        // Room performance
        $performanceSql = "SELECT 
                               h.numero, h.tipo, h.precio_base,
                               COUNT(r.id) as reservations,
                               SUM(r.precio_total) as revenue,
                               AVG(r.precio_total) as avg_price,
                               ROUND(SUM(r.precio_total) / COUNT(r.id) / h.precio_base * 100, 2) as price_performance
                           FROM habitaciones h
                           LEFT JOIN reservas r ON h.id = r.habitacion_id AND {$dateCondition} AND r.deleted_at IS NULL
                           WHERE h.deleted_at IS NULL
                           GROUP BY h.id, h.numero, h.tipo, h.precio_base
                           ORDER BY revenue DESC";
        
        $rooms['performance'] = Database::fetchAll($performanceSql);
        
        return $rooms;
    }
    
    /**
     * Get service analytics
     */
    public function getServiceAnalytics(string $dateCondition): array {
        $sql = "SELECT 
                    COUNT(rs.id) as total_service_orders,
                    SUM(rs.total) as total_service_revenue,
                    AVG(rs.total) as avg_service_price,
                    COUNT(DISTINCT rs.servicio_id) as services_used
                FROM reserva_servicios rs
                JOIN reservas r ON rs.reserva_id = r.id
                WHERE {$dateCondition} AND rs.deleted_at IS NULL AND r.deleted_at IS NULL";
        
        $services = Database::fetch($sql);
        
        // Top services
        $topServicesSql = "SELECT 
                              s.nombre,
                              COUNT(rs.id) as order_count,
                              SUM(rs.total) as revenue,
                              AVG(rs.total) as avg_price
                          FROM reserva_servicios rs
                          JOIN servicios s ON rs.servicio_id = s.id
                          JOIN reservas r ON rs.reserva_id = r.id
                          WHERE {$dateCondition} AND rs.deleted_at IS NULL AND r.deleted_at IS NULL
                          GROUP BY s.id, s.nombre
                          ORDER BY order_count DESC
                          LIMIT 10";
        
        $services['top_services'] = Database::fetchAll($topServicesSql);
        
        // Service usage trend
        $trendSql = "SELECT 
                        DATE(rs.fecha_consumo) as date,
                        COUNT(rs.id) as daily_orders,
                        SUM(rs.total) as daily_revenue
                    FROM reserva_servicios rs
                    JOIN reservas r ON rs.reserva_id = r.id
                    WHERE {$dateCondition} AND rs.deleted_at IS NULL AND r.deleted_at IS NULL
                    GROUP BY DATE(rs.fecha_consumo)
                    ORDER BY date";
        
        $services['daily_trend'] = Database::fetchAll($trendSql);
        
        return $services;
    }
    
    /**
     * Get housekeeping analytics
     */
    public function getHousekeepingAnalytics(string $dateCondition): array {
        $sql = "SELECT 
                    COUNT(*) as total_tasks,
                    COUNT(CASE WHEN estado = 'completada' THEN 1 END) as completed_tasks,
                    COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pending_tasks,
                    COUNT(CASE WHEN estado = 'en_progreso' THEN 1 END) as in_progress_tasks,
                    AVG(tiempo_real_minutes) as avg_completion_time,
                    COUNT(CASE WHEN tipo_limpieza = 'checkout' THEN 1 END) as checkout_cleanings
                FROM housekeeping_tasks ht
                WHERE {$dateCondition} AND ht.deleted_at IS NULL";
        
        $housekeeping = Database::fetch($sql);
        
        // Staff productivity
        $staffSql = "SELECT 
                        u.nombre, u.apellido,
                        COUNT(ht.id) as tasks_completed,
                        AVG(ht.tiempo_real_minutes) as avg_time,
                        COUNT(CASE WHEN ht.estado = 'completada' THEN 1 END) as completed_count
                    FROM usuarios u
                    LEFT JOIN housekeeping_tasks ht ON u.id = ht.asignado_a AND {$dateCondition} AND ht.deleted_at IS NULL
                    WHERE u.rol = 'limpieza' AND u.deleted_at IS NULL
                    GROUP BY u.id, u.nombre, u.apellido
                    ORDER BY completed_count DESC";
        
        $housekeeping['staff_productivity'] = Database::fetchAll($staffSql);
        
        return $housekeeping;
    }
    
    /**
     * Get maintenance analytics
     */
    public function getMaintenanceAnalytics(string $dateCondition): array {
        $sql = "SELECT 
                    COUNT(*) as total_requests,
                    COUNT(CASE WHEN estado = 'resuelto' THEN 1 END) as resolved_requests,
                    COUNT(CASE WHEN estado = 'abierto' THEN 1 END) as open_requests,
                    COUNT(CASE WHEN prioridad = 'urgente' THEN 1 END) as urgent_requests,
                    AVG(costo_real) as avg_cost,
                    SUM(costo_real) as total_cost
                FROM mantenimiento_habitaciones mh
                WHERE {$dateCondition} AND mh.deleted_at IS NULL";
        
        $maintenance = Database::fetch($sql);
        
        // Requests by category
        $categorySql = "SELECT 
                          categoria,
                          COUNT(*) as request_count,
                          AVG(costo_real) as avg_cost,
                          COUNT(CASE WHEN estado = 'resuelto' THEN 1 END) as resolved_count
                      FROM mantenimiento_habitaciones
                      WHERE {$dateCondition} AND deleted_at IS NULL AND categoria IS NOT NULL
                      GROUP BY categoria
                      ORDER BY request_count DESC";
        
        $maintenance['by_category'] = Database::fetchAll($categorySql);
        
        return $maintenance;
    }
    
    /**
     * Get review analytics
     */
    public function getReviewAnalytics(string $dateCondition): array {
        $sql = "SELECT 
                    COUNT(*) as total_reviews,
                    AVG(rating) as avg_rating,
                    AVG(limpieza_rating) as avg_cleanliness,
                    AVG(servicio_rating) as avg_service,
                    AVG(comodidad_rating) as avg_comfort,
                    AVG(ubicacion_rating) as avg_location,
                    AVG(precio_rating) as avg_price,
                    COUNT(CASE WHEN rating >= 4 THEN 1 END) as positive_reviews,
                    COUNT(CASE WHEN rating <= 2 THEN 1 END) as negative_reviews
                FROM habitacion_reviews hr
                JOIN reservas r ON hr.reserva_id = r.id
                WHERE {$dateCondition} AND hr.deleted_at IS NULL AND r.deleted_at IS NULL";
        
        $reviews = Database::fetch($sql);
        
        // Rating distribution
        $distributionSql = "SELECT 
                              rating,
                              COUNT(*) as count,
                              ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM habitacion_reviews hr JOIN reservas r ON hr.reserva_id = r.id WHERE {$dateCondition} AND hr.deleted_at IS NULL AND r.deleted_at IS NULL), 2) as percentage
                          FROM habitacion_reviews hr
                          JOIN reservas r ON hr.reserva_id = r.id
                          WHERE {$dateCondition} AND hr.deleted_at IS NULL AND r.deleted_at IS NULL
                          GROUP BY rating
                          ORDER BY rating";
        
        $reviews['rating_distribution'] = Database::fetchAll($distributionSql);
        
        return $reviews;
    }
    
    /**
     * Get date condition for SQL queries
     */
    private function getDateCondition(string $period): string {
        return match($period) {
            'day' => 'DATE(r.fecha_entrada) = CURDATE()',
            'week' => 'WEEK(r.fecha_entrada) = WEEK(NOW())',
            'month' => 'MONTH(r.fecha_entrada) = MONTH(NOW()) AND YEAR(r.fecha_entrada) = YEAR(NOW())',
            'year' => 'YEAR(r.fecha_entrada) = YEAR(NOW())',
            default => '1=1'
        };
    }
    
    /**
     * Get real-time metrics
     */
    public function getRealTimeMetrics(): array {
        $today = date('Y-m-d');
        
        $metrics = [
            'today_occupancy' => $this->getTodayOccupancy(),
            'today_checkins' => $this->getTodayCheckins(),
            'today_checkouts' => $this->getTodayCheckouts(),
            'today_revenue' => $this->getTodayRevenue(),
            'pending_tasks' => $this->getPendingTasks(),
            'urgent_maintenance' => $this->getUrgentMaintenance(),
            'available_rooms' => $this->getAvailableRooms()
        ];
        
        return $metrics;
    }
    
    private function getTodayOccupancy(): float {
        $sql = "SELECT 
                    ROUND(COUNT(DISTINCT r.habitacion_id) * 100.0 / (SELECT COUNT(*) FROM habitaciones WHERE deleted_at IS NULL), 2) as occupancy
                FROM reservas r
                WHERE r.estado IN ('confirmada', 'ocupada')
                AND r.fecha_entrada <= CURDATE() AND r.fecha_salida > CURDATE()
                AND r.deleted_at IS NULL";
        
        return Database::fetch($sql)['occupancy'] ?? 0;
    }
    
    private function getTodayCheckins(): int {
        $sql = "SELECT COUNT(*) as count FROM reservas 
                WHERE fecha_entrada = CURDATE() AND deleted_at IS NULL";
        return Database::fetch($sql)['count'] ?? 0;
    }
    
    private function getTodayCheckouts(): int {
        $sql = "SELECT COUNT(*) as count FROM reservas 
                WHERE fecha_salida = CURDATE() AND deleted_at IS NULL";
        return Database::fetch($sql)['count'] ?? 0;
    }
    
    private function getTodayRevenue(): float {
        $sql = "SELECT SUM(precio_total) as revenue FROM reservas 
                WHERE fecha_entrada = CURDATE() AND deleted_at IS NULL";
        return Database::fetch($sql)['revenue'] ?? 0;
    }
    
    private function getPendingTasks(): int {
        $sql = "SELECT COUNT(*) as count FROM housekeeping_tasks 
                WHERE estado IN ('pendiente', 'en_progreso') AND deleted_at IS NULL";
        return Database::fetch($sql)['count'] ?? 0;
    }
    
    private function getUrgentMaintenance(): int {
        $sql = "SELECT COUNT(*) as count FROM mantenimiento_habitaciones 
                WHERE prioridad = 'urgente' AND estado != 'resuelto' AND deleted_at IS NULL";
        return Database::fetch($sql)['count'] ?? 0;
    }
    
    private function getAvailableRooms(): int {
        $sql = "SELECT COUNT(*) as count FROM habitaciones 
                WHERE estado = 'disponible' AND deleted_at IS NULL";
        return Database::fetch($sql)['count'] ?? 0;
    }
}
