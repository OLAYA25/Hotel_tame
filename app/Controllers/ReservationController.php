<?php
/**
 * Controlador de Reservas
 */

class ReservationController extends Controller {
    private $reservationModel;
    private $roomModel;
    private $clientModel;
    
    public function __construct() {
        parent::__construct();
        $this->reservationModel = new Reservation();
        $this->roomModel = new Room();
        $this->clientModel = new Client();
    }
    
    /**
     * Obtener todas las reservas
     */
    public function index() {
        $reservas = $this->reservationModel->getWithDetails();
        $this->jsonResponse($reservas);
    }
    
    /**
     * Crear nueva reserva
     */
    public function store() {
        $this->verifyCSRF();
        
        $data = $this->getPostData();
        
        // Validar datos
        $this->validate($data, [
            'habitacion_id' => ['required'],
            'fecha_entrada' => ['required'],
            'fecha_salida' => ['required'],
            'clientes' => ['required']
        ]);
        
        // Validar fechas
        if (strtotime($data['fecha_entrada']) >= strtotime($data['fecha_salida'])) {
            $this->jsonResponse(['error' => 'La fecha de entrada debe ser anterior a la de salida'], 422);
        }
        
        // Validar clientes
        if (empty($data['clientes']) || !is_array($data['clientes'])) {
            $this->jsonResponse(['error' => 'Debe especificar al menos un cliente'], 422);
        }
        
        // Verificar que haya un titular
        $titular = array_filter($data['clientes'], fn($c) => $c['rol'] === 'titular');
        if (empty($titular)) {
            $this->jsonResponse(['error' => 'Debe especificar un cliente titular'], 422);
        }
        
        try {
            $reservaId = $this->reservationModel->createWithValidation($data, $data['clientes']);
            
            AuditHelper::log(
                $this->user['id'], 
                'crear_reserva', 
                'reservas', 
                $reservaId, 
                null, 
                $data
            );
            
            $this->jsonResponse([
                'message' => 'Reserva creada exitosamente',
                'reserva_id' => $reservaId
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Obtener reserva por ID
     */
    public function show($id) {
        $reserva = $this->reservationModel->getWithDetails(['id' => $id]);
        
        if (!$reserva) {
            $this->jsonResponse(['error' => 'Reserva no encontrada'], 404);
        }
        
        // Obtener clientes asociados
        $sql = "SELECT rc.*, c.nombre, c.apellido, c.email, c.telefono
                FROM reserva_clientes rc
                LEFT JOIN clientes c ON rc.cliente_id = c.id
                WHERE rc.reserva_id = :reserva_id";
        
        $db = Database::getInstance();
        $clientes = $db->prepare($sql)
                       ->bind(':reserva_id', $id)
                       ->fetchAll();
        
        $reserva[0]['clientes'] = $clientes;
        
        $this->jsonResponse($reserva[0]);
    }
    
    /**
     * Actualizar reserva
     */
    public function update($id) {
        $this->verifyCSRF();
        
        $data = $this->getPostData();
        
        // Validar datos
        $this->validate($data, [
            'fecha_entrada' => ['required'],
            'fecha_salida' => ['required']
        ]);
        
        try {
            $this->reservationModel->update($id, $data);
            
            AuditHelper::log(
                $this->user['id'], 
                'actualizar_reserva', 
                'reservas', 
                $id, 
                null, 
                $data
            );
            
            $this->jsonResponse(['message' => 'Reserva actualizada exitosamente']);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Cancelar reserva
     */
    public function cancel($id) {
        $this->verifyCSRF();
        
        try {
            $this->reservationModel->cancel($id);
            
            $this->jsonResponse(['message' => 'Reserva cancelada exitosamente']);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Verificar disponibilidad
     */
    public function checkAvailability() {
        $habitacionId = $_GET['habitacion_id'] ?? null;
        $fechaEntrada = $_GET['fecha_entrada'] ?? null;
        $fechaSalida = $_GET['fecha_salida'] ?? null;
        
        if (!$habitacionId || !$fechaEntrada || !$fechaSalida) {
            $this->jsonResponse(['error' => 'Faltan parámetros requeridos'], 422);
        }
        
        $available = $this->reservationModel->checkAvailability(
            $habitacionId, 
            $fechaEntrada, 
            $fechaSalida
        );
        
        $this->jsonResponse(['available' => $available]);
    }
    
    /**
     * Obtener reservas del día
     */
    public function today() {
        $reservas = $this->reservationModel->getTodayReservations();
        $this->jsonResponse($reservas);
    }
    
    /**
     * Obtener estadísticas
     */
    public function statistics() {
        $period = $_GET['period'] ?? 'month';
        $stats = $this->reservationModel->getStatistics($period);
        $this->jsonResponse($stats);
    }
}
