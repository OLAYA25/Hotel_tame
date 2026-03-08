<?php
/**
 * Sistema de Dashboard Adaptativo por Rol
 * Hotel Tame - Sistema de Gestión Hotelera
 */

class RoleBasedDashboard {
    private $user_role;
    private $user_id;
    
    public function __construct($user_role, $user_id) {
        $this->user_role = $user_role;
        $this->user_id = $user_id;
    }
    
    /**
     * Obtiene los widgets específicos para el rol del usuario
     */
    public function getWidgets() {
        switch($this->user_role) {
            case 'admin':
                return $this->getAdminWidgets();
            case 'gerente':
                return $this->getGerenteWidgets();
            case 'recepcionista':
                return $this->getRecepcionistaWidgets();
            case 'Contador':
            case 'Auxiliar Contable':
                return $this->getContadorWidgets();
            case 'mantenimiento':
                return $this->getMantenimientoWidgets();
            case 'limpieza':
                return $this->getLimpiezaWidgets();
            default:
                return $this->getGeneralWidgets();
        }
    }
    
    /**
     * Obtiene las notificaciones específicas para el rol
     */
    public function getNotifications() {
        switch($this->user_role) {
            case 'admin':
                return [
                    'system_alerts' => 'Alertas del Sistema',
                    'security_issues' => 'Problemas de Seguridad',
                    'backup_status' => 'Estado de Backups',
                    'user_activity' => 'Actividad de Usuarios'
                ];
            case 'gerente':
                return [
                    'occupancy_alerts' => 'Alertas de Ocupación',
                    'staff_issues' => 'Problemas con Personal',
                    'revenue_alerts' => 'Alertas de Ingresos',
                    'guest_feedback' => 'Feedback de Huéspedes'
                ];
            case 'recepcionista':
                return [
                    'new_reservations' => 'Nuevas Reservas',
                    'checkin_pending' => 'Check-ins Pendientes',
                    'guest_messages' => 'Mensajes de Huéspedes',
                    'payment_alerts' => 'Alertas de Pago'
                ];
            case 'Contador':
            case 'Auxiliar Contable':
                return [
                    'payment_processed' => 'Pagos Procesados',
                    'invoice_due' => 'Facturas Vencidas',
                    'expense_reports' => 'Reportes de Gastos',
                    'financial_alerts' => 'Alertas Financieras'
                ];
            case 'mantenimiento':
                return [
                    'work_orders' => 'Órdenes de Trabajo',
                    'emergency_requests' => 'Solicitudes de Emergencia',
                    'equipment_status' => 'Estado de Equipos',
                    'maintenance_schedule' => 'Programa de Mantenimiento'
                ];
            case 'limpieza':
                return [
                    'room_assignments' => 'Asignaciones de Habitaciones',
                    'cleaning_schedule' => 'Programa de Limpieza',
                    'quality_checks' => 'Controles de Calidad',
                    'supervisor_requests' => 'Solicitudes del Supervisor'
                ];
            default:
                return [
                    'general_notifications' => 'Notificaciones Generales'
                ];
        }
    }
    
    /**
     * Obtiene las acciones rápidas para el rol
     */
    public function getQuickActions() {
        switch($this->user_role) {
            case 'admin':
                return [
                    ['icon' => 'fas fa-plus', 'text' => 'Nuevo Usuario', 'action' => 'usuarios.php?action=new'],
                    ['icon' => 'fas fa-cog', 'text' => 'Configuración', 'action' => 'settings.php'],
                    ['icon' => 'fas fa-download', 'text' => 'Generar Backup', 'action' => 'backup_manager.php?action=create'],
                    ['icon' => 'fas fa-chart-bar', 'text' => 'Ver Reportes', 'action' => 'reportes.php']
                ];
            case 'gerente':
                return [
                    ['icon' => 'fas fa-calendar-plus', 'text' => 'Nueva Reserva', 'action' => 'reservas.php?action=new'],
                    ['icon' => 'fas fa-users', 'text' => 'Gestionar Personal', 'action' => 'turnos.php'],
                    ['icon' => 'fas fa-chart-line', 'text' => 'Reportes', 'action' => 'reportes.php'],
                    ['icon' => 'fas fa-bed', 'text' => 'Estado Habitaciones', 'action' => 'habitaciones.php']
                ];
            case 'recepcionista':
                return [
                    ['icon' => 'fas fa-calendar-plus', 'text' => 'Nueva Reserva', 'action' => 'reservas.php?action=new'],
                    ['icon' => 'fas fa-sign-in-alt', 'text' => 'Check-in', 'action' => 'checkin.php'],
                    ['icon' => 'fas fa-sign-out-alt', 'text' => 'Check-out', 'action' => 'checkout.php'],
                    ['icon' => 'fas fa-user-plus', 'text' => 'Nuevo Cliente', 'action' => 'clientes.php?action=new']
                ];
            case 'Contador':
            case 'Auxiliar Contable':
                return [
                    ['icon' => 'fas fa-file-invoice', 'text' => 'Nueva Factura', 'action' => 'contabilidad.php?action=invoice'],
                    ['icon' => 'fas fa-calculator', 'text' => 'Reportes', 'action' => 'contabilidad.php?action=reports'],
                    ['icon' => 'fas fa-download', 'text' => 'Exportar Excel', 'action' => 'contabilidad.php?action=export'],
                    ['icon' => 'fas fa-file-pdf', 'text' => 'Generar PDF', 'action' => 'contabilidad.php?action=pdf']
                ];
            case 'mantenimiento':
                return [
                    ['icon' => 'fas fa-tools', 'text' => 'Nueva Orden', 'action' => 'mantenimiento.php?action=order'],
                    ['icon' => 'fas fa-clipboard-check', 'text' => 'Ver Tareas', 'action' => 'mantenimiento.php?action=tasks'],
                    ['icon' => 'fas fa-wrench', 'text' => 'Inventario', 'action' => 'mantenimiento.php?action=inventory'],
                    ['icon' => 'fas fa-exclamation-triangle', 'text' => 'Emergencias', 'action' => 'mantenimiento.php?action=emergency']
                ];
            case 'limpieza':
                return [
                    ['icon' => 'fas fa-broom', 'text' => 'Mis Tareas', 'action' => 'limpieza.php?action=tasks'],
                    ['icon' => 'fas fa-check-double', 'text' => 'Completar Limpieza', 'action' => 'limpieza.php?action=complete'],
                    ['icon' => 'fas fa-clipboard-list', 'text' => 'Checklist', 'action' => 'limpieza.php?action=checklist'],
                    ['icon' => 'fas fa-camera', 'text' => 'Subir Fotos', 'action' => 'limpieza.php?action=photos']
                ];
            default:
                return [
                    ['icon' => 'fas fa-home', 'text' => 'Inicio', 'action' => 'index.php']
                ];
        }
    }
    
    /**
     * Widgets para Administrador
     */
    private function getAdminWidgets() {
        return [
            [
                'id' => 'system_status',
                'title' => 'Estado del Sistema',
                'icon' => 'fas fa-server',
                'type' => 'status',
                'size' => 'col-md-6',
                'data' => $this->getSystemStatus()
            ],
            [
                'id' => 'user_activity',
                'title' => 'Actividad de Usuarios',
                'icon' => 'fas fa-users',
                'type' => 'chart',
                'size' => 'col-md-6',
                'data' => $this->getUserActivity()
            ],
            [
                'id' => 'revenue_overview',
                'title' => 'Resumen de Ingresos',
                'icon' => 'fas fa-dollar-sign',
                'type' => 'metric',
                'size' => 'col-md-4',
                'data' => $this->getRevenueOverview()
            ],
            [
                'id' => 'occupancy_rate',
                'title' => 'Tasa de Ocupación',
                'icon' => 'fas fa-bed',
                'type' => 'progress',
                'size' => 'col-md-4',
                'data' => $this->getOccupancyRate()
            ],
            [
                'id' => 'recent_reservations',
                'title' => 'Reservas Recientes',
                'icon' => 'fas fa-calendar-check',
                'type' => 'list',
                'size' => 'col-md-8',
                'data' => $this->getRecentReservations()
            ],
            [
                'id' => 'system_alerts',
                'title' => 'Alertas del Sistema',
                'icon' => 'fas fa-exclamation-triangle',
                'type' => 'alerts',
                'size' => 'col-md-4',
                'data' => $this->getSystemAlerts()
            ]
        ];
    }
    
    /**
     * Widgets para Gerente
     */
    private function getGerenteWidgets() {
        return [
            [
                'id' => 'occupancy_dashboard',
                'title' => 'Dashboard de Ocupación',
                'icon' => 'fas fa-bed',
                'type' => 'chart',
                'size' => 'col-md-8',
                'data' => $this->getOccupancyDashboard()
            ],
            [
                'id' => 'staff_performance',
                'title' => 'Rendimiento del Personal',
                'icon' => 'fas fa-users',
                'type' => 'table',
                'size' => 'col-md-4',
                'data' => $this->getStaffPerformance()
            ],
            [
                'id' => 'revenue_metrics',
                'title' => 'Métricas de Ingresos',
                'icon' => 'fas fa-chart-line',
                'type' => 'metrics',
                'size' => 'col-md-6',
                'data' => $this->getRevenueMetrics()
            ],
            [
                'id' => 'guest_satisfaction',
                'title' => 'Satisfacción de Huéspedes',
                'icon' => 'fas fa-star',
                'type' => 'rating',
                'size' => 'col-md-6',
                'data' => $this->getGuestSatisfaction()
            ],
            [
                'id' => 'maintenance_status',
                'title' => 'Estado de Mantenimiento',
                'icon' => 'fas fa-tools',
                'type' => 'status',
                'size' => 'col-md-12',
                'data' => $this->getMaintenanceStatus()
            ]
        ];
    }
    
    /**
     * Widgets para Recepcionista
     */
    private function getRecepcionistaWidgets() {
        return [
            [
                'id' => 'today_reservations',
                'title' => 'Reservas de Hoy',
                'icon' => 'fas fa-calendar-day',
                'type' => 'list',
                'size' => 'col-md-8',
                'data' => $this->getTodayReservations()
            ],
            [
                'id' => 'pending_checkins',
                'title' => 'Check-ins Pendientes',
                'icon' => 'fas fa-sign-in-alt',
                'type' => 'cards',
                'size' => 'col-md-4',
                'data' => $this->getPendingCheckins()
            ],
            [
                'id' => 'room_status',
                'title' => 'Estado de Habitaciones',
                'icon' => 'fas fa-bed',
                'type' => 'grid',
                'size' => 'col-md-12',
                'data' => $this->getRoomStatus()
            ],
            [
                'id' => 'guest_messages',
                'title' => 'Mensajes de Huéspedes',
                'icon' => 'fas fa-comments',
                'type' => 'chat',
                'size' => 'col-md-6',
                'data' => $this->getGuestMessages()
            ],
            [
                'id' => 'quick_actions',
                'title' => 'Acciones Rápidas',
                'icon' => 'fas fa-bolt',
                'type' => 'actions',
                'size' => 'col-md-6',
                'data' => $this->getQuickActions()
            ]
        ];
    }
    
    /**
     * Widgets para Contador
     */
    private function getContadorWidgets() {
        return [
            [
                'id' => 'financial_overview',
                'title' => 'Resumen Financiero',
                'icon' => 'fas fa-calculator',
                'type' => 'metrics',
                'size' => 'col-md-12',
                'data' => $this->getFinancialOverview()
            ],
            [
                'id' => 'pending_invoices',
                'title' => 'Facturas Pendientes',
                'icon' => 'fas fa-file-invoice',
                'type' => 'list',
                'size' => 'col-md-6',
                'data' => $this->getPendingInvoices()
            ],
            [
                'id' => 'expense_report',
                'title' => 'Reporte de Gastos',
                'icon' => 'fas fa-receipt',
                'type' => 'chart',
                'size' => 'col-md-6',
                'data' => $this->getExpenseReport()
            ],
            [
                'id' => 'payment_status',
                'title' => 'Estado de Pagos',
                'icon' => 'fas fa-credit-card',
                'type' => 'status',
                'size' => 'col-md-4',
                'data' => $this->getPaymentStatus()
            ],
            [
                'id' => 'tax_summary',
                'title' => 'Resumen de Impuestos',
                'icon' => 'fas fa-percentage',
                'type' => 'table',
                'size' => 'col-md-8',
                'data' => $this->getTaxSummary()
            ]
        ];
    }
    
    /**
     * Widgets para Mantenimiento
     */
    private function getMantenimientoWidgets() {
        return [
            [
                'id' => 'work_orders',
                'title' => 'Órdenes de Trabajo',
                'icon' => 'fas fa-clipboard-list',
                'type' => 'kanban',
                'size' => 'col-md-12',
                'data' => $this->getWorkOrders()
            ],
            [
                'id' => 'equipment_status',
                'title' => 'Estado de Equipos',
                'icon' => 'fas fa-tools',
                'type' => 'grid',
                'size' => 'col-md-6',
                'data' => $this->getEquipmentStatus()
            ],
            [
                'id' => 'maintenance_schedule',
                'title' => 'Programa de Mantenimiento',
                'icon' => 'fas fa-calendar-alt',
                'type' => 'calendar',
                'size' => 'col-md-6',
                'data' => $this->getMaintenanceSchedule()
            ],
            [
                'id' => 'emergency_requests',
                'title' => 'Solicitudes de Emergencia',
                'icon' => 'fas fa-exclamation-triangle',
                'type' => 'alerts',
                'size' => 'col-md-12',
                'data' => $this->getEmergencyRequests()
            ]
        ];
    }
    
    /**
     * Widgets para Limpieza
     */
    private function getLimpiezaWidgets() {
        return [
            [
                'id' => 'room_assignments',
                'title' => 'Asignaciones de Hoy',
                'icon' => 'fas fa-broom',
                'type' => 'cards',
                'size' => 'col-md-8',
                'data' => $this->getRoomAssignments()
            ],
            [
                'id' => 'cleaning_progress',
                'title' => 'Progreso de Limpieza',
                'icon' => 'fas fa-tasks',
                'type' => 'progress',
                'size' => 'col-md-4',
                'data' => $this->getCleaningProgress()
            ],
            [
                'id' => 'quality_checks',
                'title' => 'Controles de Calidad',
                'icon' => 'fas fa-check-double',
                'type' => 'checklist',
                'size' => 'col-md-6',
                'data' => $this->getQualityChecks()
            ],
            [
                'id' => 'supervisor_notes',
                'title' => 'Notas del Supervisor',
                'icon' => 'fas fa-sticky-note',
                'type' => 'notes',
                'size' => 'col-md-6',
                'data' => $this->getSupervisorNotes()
            ]
        ];
    }
    
    /**
     * Widgets generales para roles no especificados
     */
    private function getGeneralWidgets() {
        return [
            [
                'id' => 'welcome_widget',
                'title' => 'Bienvenido',
                'icon' => 'fas fa-home',
                'type' => 'welcome',
                'size' => 'col-md-12',
                'data' => ['message' => 'Bienvenido al Sistema Hotel Tame']
            ]
        ];
    }
    
    // Métodos para obtener datos de los widgets
    // Estos métodos se conectarían a la base de datos real
    
    private function getSystemStatus() {
        return [
            'database' => 'Online',
            'server' => 'Online',
            'backups' => 'Actualizado',
            'last_update' => date('Y-m-d H:i:s')
        ];
    }
    
    private function getUserActivity() {
        return [
            'active_users' => 15,
            'total_logins' => 145,
            'peak_hour' => '14:00'
        ];
    }
    
    private function getRevenueOverview() {
        return [
            'today' => '$2,450',
            'week' => '$15,230',
            'month' => '$58,900',
            'growth' => '+12.5%'
        ];
    }
    
    private function getOccupancyRate() {
        return [
            'current' => 75,
            'target' => 85,
            'status' => 'good'
        ];
    }
    
    // Métodos faltantes implementados con datos de ejemplo
    private function getRecentReservations() {
        return [
            ['title' => 'Juan Pérez', 'subtitle' => 'Habitación 101', 'badge' => 'success'],
            ['title' => 'María García', 'subtitle' => 'Habitación 205', 'badge' => 'warning'],
            ['title' => 'Carlos López', 'subtitle' => 'Habitación 302', 'badge' => 'info']
        ];
    }
    
    private function getSystemAlerts() {
        return [
            ['type' => 'warning', 'icon' => 'exclamation-triangle', 'message' => 'Backup programado en 2 horas'],
            ['type' => 'info', 'icon' => 'info-circle', 'message' => 'Sistema funcionando normalmente']
        ];
    }
    
    private function getOccupancyDashboard() {
        return [
            'labels' => ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
            'data' => [65, 70, 75, 80, 85, 90, 95]
        ];
    }
    
    private function getStaffPerformance() {
        return [
            ['name' => 'Ana López', 'performance' => 95, 'status' => 'Excelente'],
            ['name' => 'Carlos Ruiz', 'performance' => 87, 'status' => 'Bueno'],
            ['name' => 'María Torres', 'performance' => 92, 'status' => 'Excelente']
        ];
    }
    
    private function getRevenueMetrics() {
        return [
            'daily' => '$2,450',
            'weekly' => '$15,230',
            'monthly' => '$58,900',
            'growth_rate' => '+12.5%'
        ];
    }
    
    private function getGuestSatisfaction() {
        return [
            'overall' => 4.5,
            'service' => 4.7,
            'cleanliness' => 4.6,
            'facilities' => 4.3
        ];
    }
    
    private function getMaintenanceStatus() {
        return [
            'pending' => 3,
            'in_progress' => 2,
            'completed' => 15,
            'overdue' => 1
        ];
    }
    
    private function getTodayReservations() {
        return [
            ['title' => 'Check-in: 14:00', 'subtitle' => 'Juan Pérez - Hab. 101', 'badge' => 'primary'],
            ['title' => 'Check-out: 11:00', 'subtitle' => 'María García - Hab. 205', 'badge' => 'secondary'],
            ['title' => 'Check-in: 16:00', 'subtitle' => 'Carlos López - Hab. 302', 'badge' => 'primary']
        ];
    }
    
    private function getPendingCheckins() {
        return [
            ['title' => 'Juan Pérez', 'description' => 'Habitación 101', 'action' => 'checkin.php?id=123'],
            ['title' => 'Ana Martínez', 'description' => 'Habitación 304', 'action' => 'checkin.php?id=124']
        ];
    }
    
    private function getRoomStatus() {
        return [
            ['room' => '101', 'status' => 'Disponible', 'type' => 'success'],
            ['room' => '102', 'status' => 'Ocupada', 'type' => 'danger'],
            ['room' => '103', 'status' => 'Limpieza', 'type' => 'warning'],
            ['room' => '104', 'status' => 'Mantenimiento', 'type' => 'info']
        ];
    }
    
    private function getGuestMessages() {
        return [
            ['title' => 'Necesito toallas adicionales', 'subtitle' => 'Hab. 101 - Juan Pérez', 'time' => '10:30 AM'],
            ['title' => '¿Hay servicio a la habitación?', 'subtitle' => 'Hab. 205 - María García', 'time' => '09:45 AM']
        ];
    }
    
    private function getFinancialOverview() {
        return [
            'total_revenue' => '$58,900',
            'total_expenses' => '$23,450',
            'net_profit' => '$35,450',
            'profit_margin' => '60.2%'
        ];
    }
    
    private function getPendingInvoices() {
        return [
            ['title' => 'Factura #1234', 'subtitle' => 'Juan Pérez - $1,200', 'due_date' => '2024-01-15'],
            ['title' => 'Factura #1235', 'subtitle' => 'María García - $800', 'due_date' => '2024-01-16']
        ];
    }
    
    private function getExpenseReport() {
        return [
            'category' => ['Salarios', 'Suministros', 'Mantenimiento', 'Marketing'],
            'amounts' => [15000, 3500, 2800, 2150]
        ];
    }
    
    private function getPaymentStatus() {
        return [
            'pending' => 5,
            'processing' => 3,
            'completed' => 45,
            'failed' => 1
        ];
    }
    
    private function getTaxSummary() {
        return [
            ['tax' => 'IVA', 'amount' => '$8,900', 'rate' => '19%'],
            ['tax' => 'Retención', 'amount' => '$3,200', 'rate' => '10%'],
            ['tax' => 'ICA', 'amount' => '$1,100', 'rate' => '0.5%']
        ];
    }
    
    private function getWorkOrders() {
        return [
            ['title' => 'Reparar aire acondicionado', 'room' => '101', 'priority' => 'Alta'],
            ['title' => 'Cambiar cerradura', 'room' => '205', 'priority' => 'Media'],
            ['title' => 'Revisar plomería', 'room' => '302', 'priority' => 'Baja']
        ];
    }
    
    private function getEquipmentStatus() {
        return [
            ['equipment' => 'Aire Acondicionado', 'status' => 'Operativo', 'maintenance' => '2024-02-01'],
            ['equipment' => 'Calentador', 'status' => 'Requiere revisión', 'maintenance' => '2024-01-10'],
            ['equipment' => 'Cerradura electrónica', 'status' => 'Operativo', 'maintenance' => '2024-03-01']
        ];
    }
    
    private function getMaintenanceSchedule() {
        return [
            ['date' => '2024-01-10', 'task' => 'Revisión general', 'assigned' => 'Carlos Ruiz'],
            ['date' => '2024-01-15', 'task' => 'Mantenimiento aire', 'assigned' => 'Ana López'],
            ['date' => '2024-01-20', 'task' => 'Inspección seguridad', 'assigned' => 'María Torres']
        ];
    }
    
    private function getEmergencyRequests() {
        return [
            ['title' => 'Fuga de agua - Hab. 101', 'time' => '10:30 AM', 'priority' => 'Alta'],
            ['title' => 'Sin electricidad - Hab. 205', 'time' => '09:15 AM', 'priority' => 'Alta']
        ];
    }
    
    private function getRoomAssignments() {
        return [
            ['room' => '101', 'status' => 'Pendiente', 'assigned_to' => 'María López'],
            ['room' => '102', 'status' => 'En progreso', 'assigned_to' => 'Ana Martínez'],
            ['room' => '103', 'status' => 'Completada', 'assigned_to' => 'Carlos Ruiz']
        ];
    }
    
    private function getCleaningProgress() {
        return [
            'completed' => 8,
            'pending' => 3,
            'in_progress' => 2,
            'total' => 13
        ];
    }
    
    private function getQualityChecks() {
        return [
            ['room' => '101', 'score' => 95, 'inspector' => 'Ana López'],
            ['room' => '102', 'score' => 88, 'inspector' => 'Carlos Ruiz'],
            ['room' => '103', 'score' => 92, 'inspector' => 'María Torres']
        ];
    }
    
    private function getSupervisorNotes() {
        return [
            ['date' => '2024-01-09', 'note' => 'Recordar revisar habitaciones del piso 2', 'priority' => 'Media'],
            ['date' => '2024-01-08', 'note' => 'Entregar suministros nuevos', 'priority' => 'Alta']
        ];
    }
    
    /**
     * Genera el HTML del dashboard
     */
    public function renderDashboard() {
        $widgets = $this->getWidgets();
        $quick_actions = $this->getQuickActions();
        $notifications = $this->getNotifications();
        
        ob_start();
        ?>
        <div class="dashboard-container">
            <!-- Header del Dashboard -->
            <div class="dashboard-header mb-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h2 class="dashboard-title">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Dashboard - <?php echo ucfirst($this->user_role); ?>
                        </h2>
                        <p class="text-muted mb-0">
                            <?php echo date('d/m/Y H:i'); ?>
                        </p>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="btn-group">
                            <?php foreach ($quick_actions as $action): ?>
                                <a href="<?php echo $action['action']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="<?php echo $action['icon']; ?> me-1"></i>
                                    <?php echo $action['text']; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Widgets del Dashboard -->
            <div class="dashboard-widgets">
                <div class="row">
                    <?php foreach ($widgets as $widget): ?>
                        <div class="<?php echo $widget['size']; ?> mb-4">
                            <div class="card widget-card" data-widget-id="<?php echo $widget['id']; ?>">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="<?php echo $widget['icon']; ?> me-2"></i>
                                        <?php echo $widget['title']; ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="widget-content" data-widget-type="<?php echo $widget['type']; ?>">
                                        <!-- El contenido se cargará dinámicamente -->
                                        <div class="widget-placeholder">
                                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                            Cargando...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Panel de Notificaciones -->
            <div class="dashboard-notifications">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-bell me-2"></i>
                                    Notificaciones
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($notifications as $key => $label): ?>
                                        <div class="col-md-3 mb-3">
                                            <div class="notification-item" data-notification-type="<?php echo $key; ?>">
                                                <div class="d-flex align-items-center">
                                                    <div class="notification-icon me-2">
                                                        <i class="fas fa-circle text-warning"></i>
                                                    </div>
                                                    <div class="notification-content">
                                                        <h6 class="mb-0"><?php echo $label; ?></h6>
                                                        <small class="text-muted">0 nuevos</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        // JavaScript para cargar dinámicamente los widgets
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardWidgets();
            loadNotifications();
        });
        
        function loadDashboardWidgets() {
            const widgets = document.querySelectorAll('.widget-content');
            widgets.forEach(widget => {
                const widgetType = widget.dataset.widgetType;
                const widgetId = widget.closest('.widget-card').dataset.widgetId;
                
                // Simular carga de datos
                setTimeout(() => {
                    widget.innerHTML = '<div class="text-muted">Widget ' + widgetType + ' cargado</div>';
                }, 1000);
            });
        }
        
        function loadNotifications() {
            const notifications = document.querySelectorAll('.notification-item');
            notifications.forEach(notification => {
                const type = notification.dataset.notificationType;
                
                // Simular carga de notificaciones
                setTimeout(() => {
                    const count = Math.floor(Math.random() * 10);
                    const countElement = notification.querySelector('small');
                    const iconElement = notification.querySelector('.notification-icon i');
                    
                    // Usar funciones seguras para actualizar elementos
                    try {
                        if (countElement) {
                            countElement.textContent = count + ' nuevos';
                        }
                        
                        if (count > 0 && iconElement) {
                            iconElement.className = 'fas fa-circle text-danger';
                        }
                    } catch (error) {
                        console.warn('Error actualizando notificación:', error.message);
                        // No mostrar error al usuario, continuar con otras notificaciones
                    }
                }, 1500);
            });
        }
        
        // Función segura para actualizar elementos
        function safeUpdateElement(element, property, value) {
            try {
                if (element) {
                    element[property] = value;
                    return true;
                }
            } catch (error) {
                console.warn('Error actualizando elemento:', error.message);
            }
            return false;
        }
        </script>
        
        <style>
        .dashboard-container {
            padding: 20px;
        }
        
        .widget-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .widget-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .widget-placeholder {
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }
        
        .notification-item {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #e9ecef;
            transition: background-color 0.2s;
        }
        
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        
        .dashboard-title {
            color: #2c3e50;
            font-weight: 600;
        }
        
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        </style>
        <?php
        return ob_get_clean();
    }
}
?>
