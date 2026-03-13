<?php

namespace App\Services;

use Database\Database;
use App\Services\AppLogger;
use Exception;

class ReportService {
    
    /**
     * Generate occupancy report
     */
    public function generateOccupancyReport(string $startDate, string $endDate, string $format = 'pdf'): array {
        $sql = "SELECT 
                    DATE(r.fecha_entrada) as date,
                    COUNT(DISTINCT r.habitacion_id) as occupied_rooms,
                    COUNT(DISTINCT h.id) as total_rooms,
                    ROUND(COUNT(DISTINCT r.habitacion_id) * 100.0 / COUNT(DISTINCT h.id), 2) as occupancy_rate,
                    COUNT(r.id) as total_reservations,
                    SUM(r.precio_total) as daily_revenue
                FROM reservas r
                CROSS JOIN habitaciones h
                WHERE r.fecha_entrada BETWEEN :start_date AND :end_date
                AND r.estado IN ('confirmada', 'ocupada')
                AND r.deleted_at IS NULL
                AND h.deleted_at IS NULL
                GROUP BY DATE(r.fecha_entrada)
                ORDER BY date";
        
        $data = Database::fetchAll($sql, [
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        
        $report = [
            'title' => 'Reporte de Ocupación',
            'period' => "$startDate a $endDate",
            'data' => $data,
            'summary' => $this->calculateOccupancySummary($data),
            'format' => $format
        ];
        
        // Generate file
        $filePath = $this->generateReportFile($report, $format);
        
        return [
            'report_data' => $report,
            'file_path' => $filePath,
            'file_size' => file_exists($filePath) ? filesize($filePath) : 0
        ];
    }
    
    /**
     * Generate revenue report
     */
    public function generateRevenueReport(string $startDate, string $endDate, string $format = 'pdf'): array {
        $sql = "SELECT 
                    DATE(r.fecha_entrada) as date,
                    SUM(r.precio_total) as revenue,
                    COUNT(r.id) as reservations,
                    AVG(r.precio_total) as avg_reservation_value,
                    h.tipo as room_type,
                    SUM(CASE WHEN r.estado = 'pagada' THEN r.precio_total ELSE 0 END) as collected_revenue
                FROM reservas r
                JOIN habitaciones h ON r.habitacion_id = h.id
                WHERE r.fecha_entrada BETWEEN :start_date AND :end_date
                AND r.deleted_at IS NULL
                AND h.deleted_at IS NULL
                GROUP BY DATE(r.fecha_entrada), h.tipo
                ORDER BY date, h.tipo";
        
        $data = Database::fetchAll($sql, [
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        
        // Service revenue
        $serviceSql = "SELECT 
                          DATE(rs.fecha_consumo) as date,
                          s.nombre as service_name,
                          SUM(rs.total) as service_revenue,
                          COUNT(rs.id) as service_orders
                       FROM reserva_servicios rs
                       JOIN servicios s ON rs.servicio_id = s.id
                       JOIN reservas r ON rs.reserva_id = r.id
                       WHERE r.fecha_entrada BETWEEN :start_date AND :end_date
                       AND rs.deleted_at IS NULL
                       AND r.deleted_at IS NULL
                       GROUP BY DATE(rs.fecha_consumo), s.nombre
                       ORDER BY date, service_revenue DESC";
        
        $serviceData = Database::fetchAll($serviceSql, [
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        
        $report = [
            'title' => 'Reporte de Ingresos',
            'period' => "$startDate a $endDate",
            'room_revenue' => $data,
            'service_revenue' => $serviceData,
            'summary' => $this->calculateRevenueSummary($data, $serviceData),
            'format' => $format
        ];
        
        $filePath = $this->generateReportFile($report, $format);
        
        return [
            'report_data' => $report,
            'file_path' => $filePath,
            'file_size' => file_exists($filePath) ? filesize($filePath) : 0
        ];
    }
    
    /**
     * Generate guest report
     */
    public function generateGuestReport(string $startDate, string $endDate, string $format = 'pdf'): array {
        $sql = "SELECT 
                    c.id, c.nombre, c.apellido, c.email, c.pais,
                    COUNT(DISTINCT r.id) as total_stays,
                    SUM(DATEDIFF(r.fecha_salida, r.fecha_entrada)) as total_nights,
                    SUM(r.precio_total) as total_spent,
                    AVG(r.precio_total) as avg_spent,
                    MAX(r.fecha_entrada) as last_stay,
                    cl.nivel as loyalty_level
                FROM clientes c
                JOIN reserva_clientes rc ON c.id = rc.cliente_id
                JOIN reservas r ON rc.reserva_id = r.id
                LEFT JOIN clientes_loyalty cl ON c.id = cl.cliente_id
                WHERE r.fecha_entrada BETWEEN :start_date AND :end_date
                AND r.deleted_at IS NULL
                AND c.deleted_at IS NULL
                GROUP BY c.id, c.nombre, c.apellido, c.email, c.pais, cl.nivel
                ORDER BY total_spent DESC";
        
        $data = Database::fetchAll($sql, [
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        
        // Guest nationality distribution
        $nationalitySql = "SELECT 
                             c.pais,
                             COUNT(DISTINCT c.id) as unique_guests,
                             COUNT(r.id) as total_reservations
                         FROM clientes c
                         JOIN reserva_clientes rc ON c.id = rc.cliente_id
                         JOIN reservas r ON rc.reserva_id = r.id
                         WHERE r.fecha_entrada BETWEEN :start_date AND :end_date
                         AND r.deleted_at IS NULL
                         AND c.deleted_at IS NULL
                         GROUP BY c.pais
                         ORDER BY unique_guests DESC";
        
        $nationalityData = Database::fetchAll($nationalitySql, [
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        
        $report = [
            'title' => 'Reporte de Huéspedes',
            'period' => "$startDate a $endDate",
            'guest_details' => $data,
            'nationality_distribution' => $nationalityData,
            'summary' => $this->calculateGuestSummary($data, $nationalityData),
            'format' => $format
        ];
        
        $filePath = $this->generateReportFile($report, $format);
        
        return [
            'report_data' => $report,
            'file_path' => $filePath,
            'file_size' => file_exists($filePath) ? filesize($filePath) : 0
        ];
    }
    
    /**
     * Generate service report
     */
    public function generateServiceReport(string $startDate, string $endDate, string $format = 'pdf'): array {
        $sql = "SELECT 
                    s.nombre as service_name,
                    s.tipo as service_type,
                    COUNT(rs.id) as total_orders,
                    SUM(rs.total) as total_revenue,
                    AVG(rs.total) as avg_price,
                    COUNT(DISTINCT rs.reserva_id) as unique_reservations,
                    SUM(rs.cantidad) as total_quantity
                FROM reserva_servicios rs
                JOIN servicios s ON rs.servicio_id = s.id
                JOIN reservas r ON rs.reserva_id = r.id
                WHERE r.fecha_entrada BETWEEN :start_date AND :end_date
                AND rs.deleted_at IS NULL
                AND r.deleted_at IS NULL
                AND s.deleted_at IS NULL
                GROUP BY s.id, s.nombre, s.tipo
                ORDER BY total_revenue DESC";
        
        $data = Database::fetchAll($sql, [
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        
        // Service usage trend
        $trendSql = "SELECT 
                        DATE(rs.fecha_consumo) as date,
                        s.nombre as service_name,
                        COUNT(rs.id) as daily_orders,
                        SUM(rs.total) as daily_revenue
                     FROM reserva_servicios rs
                     JOIN servicios s ON rs.servicio_id = s.id
                     JOIN reservas r ON rs.reserva_id = r.id
                     WHERE r.fecha_entrada BETWEEN :start_date AND :end_date
                     AND rs.deleted_at IS NULL
                     AND r.deleted_at IS NULL
                     GROUP BY DATE(rs.fecha_consumo), s.nombre
                     ORDER BY date, daily_revenue DESC";
        
        $trendData = Database::fetchAll($trendSql, [
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        
        $report = [
            'title' => 'Reporte de Servicios',
            'period' => "$startDate a $endDate",
            'service_summary' => $data,
            'usage_trend' => $trendData,
            'summary' => $this->calculateServiceSummary($data),
            'format' => $format
        ];
        
        $filePath = $this->generateReportFile($report, $format);
        
        return [
            'report_data' => $report,
            'file_path' => $filePath,
            'file_size' => file_exists($filePath) ? filesize($filePath) : 0
        ];
    }
    
    /**
     * Generate housekeeping report
     */
    public function generateHousekeepingReport(string $startDate, string $endDate, string $format = 'pdf'): array {
        $sql = "SELECT 
                    ht.tipo_limpieza,
                    COUNT(*) as total_tasks,
                    COUNT(CASE WHEN ht.estado = 'completada' THEN 1 END) as completed_tasks,
                    COUNT(CASE WHEN ht.estado = 'pendiente' THEN 1 END) as pending_tasks,
                    AVG(ht.tiempo_real_minutes) as avg_completion_time,
                    u.nombre as staff_name,
                    u.apellido as staff_lastname
                FROM housekeeping_tasks ht
                LEFT JOIN usuarios u ON ht.asignado_a = u.id
                WHERE ht.fecha_programada BETWEEN :start_date AND :end_date
                AND ht.deleted_at IS NULL
                GROUP BY ht.tipo_limpieza, u.id, u.nombre, u.apellido
                ORDER BY completed_tasks DESC";
        
        $data = Database::fetchAll($sql, [
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        
        $report = [
            'title' => 'Reporte de Housekeeping',
            'period' => "$startDate a $endDate",
            'task_summary' => $data,
            'summary' => $this->calculateHousekeepingSummary($data),
            'format' => $format
        ];
        
        $filePath = $this->generateReportFile($report, $format);
        
        return [
            'report_data' => $report,
            'file_path' => $filePath,
            'file_size' => file_exists($filePath) ? filesize($filePath) : 0
        ];
    }
    
    /**
     * Generate report file (PDF or Excel)
     */
    private function generateReportFile(array $report, string $format): string {
        $filename = $this->generateFilename($report['title'], $format);
        $filePath = __DIR__ . "/../../../storage/reports/$filename";
        
        // Create directory if it doesn't exist
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        switch ($format) {
            case 'pdf':
                $this->generatePDF($report, $filePath);
                break;
            case 'excel':
                $this->generateExcel($report, $filePath);
                break;
            case 'csv':
                $this->generateCSV($report, $filePath);
                break;
            default:
                throw new Exception("Unsupported format: $format");
        }
        
        AppLogger::business('Report generated', [
            'title' => $report['title'],
            'format' => $format,
            'file_path' => $filePath
        ]);
        
        return $filename;
    }
    
    /**
     * Generate PDF report
     */
    private function generatePDF(array $report, string $filePath): void {
        // Mock PDF generation - in real implementation, use TCPDF or similar
        $content = $this->formatReportAsText($report);
        file_put_contents(str_replace('.pdf', '.txt', $filePath), $content);
    }
    
    /**
     * Generate Excel report
     */
    private function generateExcel(array $report, string $filePath): void {
        // Mock Excel generation - in real implementation, use PhpSpreadsheet
        $content = $this->formatReportAsCSV($report);
        file_put_contents(str_replace('.xlsx', '.csv', $filePath), $content);
    }
    
    /**
     * Generate CSV report
     */
    private function generateCSV(array $report, string $filePath): void {
        $content = $this->formatReportAsCSV($report);
        file_put_contents($filePath, $content);
    }
    
    /**
     * Format report as text
     */
    private function formatReportAsText(array $report): string {
        $content = "{$report['title']}\n";
        $content .= "Período: {$report['period']}\n";
        $content .= str_repeat("=", 50) . "\n\n";
        
        foreach ($report['data'] as $row) {
            $content .= implode(" | ", $row) . "\n";
        }
        
        return $content;
    }
    
    /**
     * Format report as CSV
     */
    private function formatReportAsCSV(array $report): string {
        $content = "{$report['title']} - {$report['period']}\n";
        
        if (!empty($report['data'])) {
            $headers = array_keys($report['data'][0]);
            $content .= implode(",", $headers) . "\n";
            
            foreach ($report['data'] as $row) {
                $content .= implode(",", array_values($row)) . "\n";
            }
        }
        
        return $content;
    }
    
    /**
     * Generate filename
     */
    private function generateFilename(string $title, string $format): string {
        $sanitizedTitle = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $title));
        $date = date('Y-m-d_H-i-s');
        return "{$sanitizedTitle}_{$date}.{$format}";
    }
    
    /**
     * Calculate occupancy summary
     */
    private function calculateOccupancySummary(array $data): array {
        $summary = [
            'total_days' => count($data),
            'avg_occupancy_rate' => 0,
            'total_revenue' => 0,
            'total_reservations' => 0
        ];
        
        if (!empty($data)) {
            $totalOccupancy = 0;
            foreach ($data as $row) {
                $totalOccupancy += $row['occupancy_rate'];
                $summary['total_revenue'] += $row['daily_revenue'];
                $summary['total_reservations'] += $row['total_reservations'];
            }
            
            $summary['avg_occupancy_rate'] = round($totalOccupancy / count($data), 2);
        }
        
        return $summary;
    }
    
    /**
     * Calculate revenue summary
     */
    private function calculateRevenueSummary(array $roomData, array $serviceData): array {
        $summary = [
            'total_room_revenue' => 0,
            'total_service_revenue' => 0,
            'total_revenue' => 0,
            'total_reservations' => 0,
            'avg_reservation_value' => 0
        ];
        
        foreach ($roomData as $row) {
            $summary['total_room_revenue'] += $row['revenue'];
            $summary['total_reservations'] += $row['reservations'];
        }
        
        foreach ($serviceData as $row) {
            $summary['total_service_revenue'] += $row['service_revenue'];
        }
        
        $summary['total_revenue'] = $summary['total_room_revenue'] + $summary['total_service_revenue'];
        $summary['avg_reservation_value'] = $summary['total_reservations'] > 0 ? 
            round($summary['total_room_revenue'] / $summary['total_reservations'], 2) : 0;
        
        return $summary;
    }
    
    /**
     * Calculate guest summary
     */
    private function calculateGuestSummary(array $guestData, array $nationalityData): array {
        $summary = [
            'total_unique_guests' => count($guestData),
            'total_countries' => count($nationalityData),
            'total_revenue' => 0,
            'avg_guest_value' => 0,
            'repeat_guests' => 0
        ];
        
        foreach ($guestData as $guest) {
            $summary['total_revenue'] += $guest['total_spent'];
            if ($guest['total_stays'] > 1) {
                $summary['repeat_guests']++;
            }
        }
        
        $summary['avg_guest_value'] = $summary['total_unique_guests'] > 0 ? 
            round($summary['total_revenue'] / $summary['total_unique_guests'], 2) : 0;
        
        return $summary;
    }
    
    /**
     * Calculate service summary
     */
    private function calculateServiceSummary(array $serviceData): array {
        $summary = [
            'total_services' => count($serviceData),
            'total_orders' => 0,
            'total_revenue' => 0,
            'avg_order_value' => 0
        ];
        
        foreach ($serviceData as $service) {
            $summary['total_orders'] += $service['total_orders'];
            $summary['total_revenue'] += $service['total_revenue'];
        }
        
        $summary['avg_order_value'] = $summary['total_orders'] > 0 ? 
            round($summary['total_revenue'] / $summary['total_orders'], 2) : 0;
        
        return $summary;
    }
    
    /**
     * Calculate housekeeping summary
     */
    private function calculateHousekeepingSummary(array $taskData): array {
        $summary = [
            'total_tasks' => 0,
            'completed_tasks' => 0,
            'pending_tasks' => 0,
            'completion_rate' => 0,
            'avg_completion_time' => 0
        ];
        
        $totalTime = 0;
        $timeCount = 0;
        
        foreach ($taskData as $task) {
            $summary['total_tasks'] += $task['total_tasks'];
            $summary['completed_tasks'] += $task['completed_tasks'];
            $summary['pending_tasks'] += $task['pending_tasks'];
            
            if ($task['avg_completion_time']) {
                $totalTime += $task['avg_completion_time'];
                $timeCount++;
            }
        }
        
        $summary['completion_rate'] = $summary['total_tasks'] > 0 ? 
            round(($summary['completed_tasks'] / $summary['total_tasks']) * 100, 2) : 0;
        
        $summary['avg_completion_time'] = $timeCount > 0 ? 
            round($totalTime / $timeCount, 2) : 0;
        
        return $summary;
    }
    
    /**
     * Get available reports
     */
    public function getAvailableReports(): array {
        return [
            'occupancy' => [
                'name' => 'Reporte de Ocupación',
                'description' => 'Análisis de ocupación por período',
                'formats' => ['pdf', 'excel', 'csv']
            ],
            'revenue' => [
                'name' => 'Reporte de Ingresos',
                'description' => 'Ingresos por habitaciones y servicios',
                'formats' => ['pdf', 'excel', 'csv']
            ],
            'guests' => [
                'name' => 'Reporte de Huéspedes',
                'description' => 'Estadísticas y demografía de huéspedes',
                'formats' => ['pdf', 'excel', 'csv']
            ],
            'services' => [
                'name' => 'Reporte de Servicios',
                'description' => 'Consumo de servicios adicionales',
                'formats' => ['pdf', 'excel', 'csv']
            ],
            'housekeeping' => [
                'name' => 'Reporte de Housekeeping',
                'description' => 'Tareas de limpieza y productividad',
                'formats' => ['pdf', 'excel', 'csv']
            ]
        ];
    }
    
    /**
     * Get generated reports list
     */
    public function getGeneratedReports(): array {
        $reportsDir = __DIR__ . "/../../../storage/reports";
        $reports = [];
        
        if (is_dir($reportsDir)) {
            $files = scandir($reportsDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $filePath = $reportsDir . '/' . $file;
                    $reports[] = [
                        'filename' => $file,
                        'size' => filesize($filePath),
                        'created_at' => date('Y-m-d H:i:s', filemtime($filePath)),
                        'download_url' => "/storage/reports/$file"
                    ];
                }
            }
        }
        
        // Sort by creation date descending
        usort($reports, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $reports;
    }
}
