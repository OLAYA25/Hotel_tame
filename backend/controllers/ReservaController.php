<?php
/**
 * Controlador de Reservas - API REST
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../services/ReservaService.php';

class ReservaController extends Controller {
    private $reservaService;
    
    public function __construct() {
        parent::__construct();
        $this->reservaService = new ReservaService();
    }
    
    /**
     * GET /api/reservas - Listar reservas
     */
    public function index() {
        $this->requirePermission('reservas', 'read');
        
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 20;
        $estado = $_GET['estado'] ?? null;
        $habitacionId = $_GET['habitacion_id'] ?? null;
        
        // Construir condiciones
        $conditions = [];
        if ($estado) {
            $conditions['estado'] = $estado;
        }
        if ($habitacionId) {
            $conditions['habitacion_id'] = $habitacionId;
        }
        
        $offset = ($page - 1) * $limit;
        
        try {
            $reservas = $this->reservaService->getWithDetails($conditions, $limit, $offset);
            $total = $this->reservaService->count($conditions);
            
            $this->successResponse('Reservas obtenidas exitosamente', [
                'reservas' => $reservas,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ]);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * GET /api/reservas/{id} - Obtener reserva
     */
    public function show($id) {
        $this->requirePermission('reservas', 'read');
        
        $id = $this->validateId($id);
        
        try {
            $reserva = $this->reservaService->getWithDetails(['id' => $id]);
            
            if (!$reserva) {
                $this->errorResponse('Reserva no encontrada', null, 404);
            }
            
            $this->successResponse('Reserva obtenida exitosamente', $reserva[0]);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * POST /api/reservas - Crear reserva
     */
    public function store() {
        $this->requirePermission('reservas', 'create');
        $this->verifyCSRF();
        
        $data = $this->getInput();
        
        // Validar datos básicos
        $this->validate($data, [
            'habitacion_id' => ['required', 'numeric'],
            'fecha_entrada' => ['required', 'date'],
            'fecha_salida' => ['required', 'date'],
            'clientes' => ['required']
        ]);
        
        try {
            $reservaId = $this->reservaService->create($data);
            
            $this->successResponse('Reserva creada exitosamente', [
                'reserva_id' => $reservaId
            ]);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * PUT /api/reservas/{id} - Actualizar reserva
     */
    public function update($id) {
        $this->requirePermission('reservas', 'update');
        $this->verifyCSRF();
        
        $id = $this->validateId($id);
        $data = $this->getInput();
        
        // Validar datos básicos
        $this->validate($data, [
            'fecha_entrada' => ['required', 'date'],
            'fecha_salida' => ['required', 'date']
        ]);
        
        try {
            $this->reservaService->update($id, $data);
            
            $this->successResponse('Reserva actualizada exitosamente');
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * DELETE /api/reservas/{id} - Cancelar reserva
     */
    public function destroy($id) {
        $this->requirePermission('reservas', 'delete');
        $this->verifyCSRF();
        
        $id = $this->validateId($id);
        $motivo = $this->getInput()['motivo'] ?? '';
        
        try {
            $this->reservaService->cancel($id, $motivo);
            
            $this->successResponse('Reserva cancelada exitosamente');
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * POST /api/reservas/{id}/confirm - Confirmar reserva
     */
    public function confirm($id) {
        $this->requirePermission('reservas', 'update');
        $this->verifyCSRF();
        
        $id = $this->validateId($id);
        
        try {
            $this->reservaService->confirm($id);
            
            $this->successResponse('Reserva confirmada exitosamente');
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * POST /api/reservas/{id}/checkin - Realizar check-in
     */
    public function checkIn($id) {
        $this->requirePermission('reservas', 'update');
        $this->verifyCSRF();
        
        $id = $this->validateId($id);
        
        try {
            $this->reservaService->checkIn($id, $this->user['id']);
            
            $this->successResponse('Check-in realizado exitosamente');
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * POST /api/reservas/{id}/checkout - Realizar check-out
     */
    public function checkOut($id) {
        $this->requirePermission('reservas', 'update');
        $this->verifyCSRF();
        
        $id = $this->validateId($id);
        
        try {
            $this->reservaService->checkOut($id, $this->user['id']);
            
            $this->successResponse('Check-out realizado exitosamente');
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * GET /api/reservas/availability - Verificar disponibilidad
     */
    public function availability() {
        $habitacionId = $_GET['habitacion_id'] ?? null;
        $fechaEntrada = $_GET['fecha_entrada'] ?? null;
        $fechaSalida = $_GET['fecha_salida'] ?? null;
        
        if (!$habitacionId || !$fechaEntrada || !$fechaSalida) {
            $this->errorResponse('Parámetros requeridos: habitacion_id, fecha_entrada, fecha_salida', null, 422);
        }
        
        try {
            $available = $this->reservaService->checkAvailability($habitacionId, $fechaEntrada, $fechaSalida);
            
            $this->successResponse('Disponibilidad verificada', [
                'available' => $available,
                'habitacion_id' => $habitacionId,
                'fecha_entrada' => $fechaEntrada,
                'fecha_salida' => $fechaSalida
            ]);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * GET /api/reservas/calendar - Calendario de ocupación
     */
    public function calendar() {
        $this->requirePermission('reservas', 'read');
        
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
        $habitacionId = $_GET['habitacion_id'] ?? null;
        
        try {
            $calendar = $this->reservaService->getOccupancyCalendar($fechaInicio, $fechaFin, $habitacionId);
            
            $this->successResponse('Calendario obtenido exitosamente', $calendar);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * GET /api/reservas/statistics - Estadísticas
     */
    public function statistics() {
        $this->requirePermission('reservas', 'read');
        
        $period = $_GET['period'] ?? 'month'; // day, week, month, year
        
        try {
            $stats = $this->reservaService->getStatistics($period);
            
            $this->successResponse('Estadísticas obtenidas exitosamente', $stats);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
}
