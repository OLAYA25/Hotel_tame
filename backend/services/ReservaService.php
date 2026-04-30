<?php
/**
 * Servicio de Reservas - Lógica de negocio
 */

require_once __DIR__ . '/../repositories/ReservaRepository.php';
require_once __DIR__ . '/../repositories/Repository.php';
require_once __DIR__ . '/../validators/ReservaValidator.php';
require_once __DIR__ . '/../middleware/LoggingMiddleware.php';

class ReservaService {
    private $reservaRepository;
    private $db;
    
    public function __construct() {
        $this->reservaRepository = new ReservaRepository();
        $this->db = Database::getInstance();
    }
    
    /**
     * Crear nueva reserva
     */
    public function create($data) {
        // Validar datos
        $errors = ReservaValidator::validate($data);
        if (!empty($errors)) {
            throw new ValidationException('Datos de reserva inválidos', $errors);
        }
        
        // Validar reglas de negocio
        $businessErrors = ReservaValidator::validateBusinessRules($data);
        if (!empty($businessErrors)) {
            throw new ValidationException('Reglas de negocio no cumplidas', $businessErrors);
        }
        
        // Verificar disponibilidad
        if (!$this->reservaRepository->checkAvailability(
            $data['habitacion_id'], 
            $data['fecha_entrada'], 
            $data['fecha_salida']
        )) {
            throw new BusinessException('La habitación no está disponible en las fechas seleccionadas');
        }
        
        $this->db->beginTransaction();
        
        try {
            // Calcular precio total
            $data['precio_total'] = $this->calculatePrice($data);
            
            // Crear reserva
            $reservaId = $this->reservaRepository->create([
                'habitacion_id' => $data['habitacion_id'],
                'fecha_entrada' => $data['fecha_entrada'],
                'fecha_salida' => $data['fecha_salida'],
                'precio_total' => $data['precio_total'],
                'estado' => 'pendiente',
                'observaciones' => $data['observaciones'] ?? null
            ]);
            
            // Asociar clientes
            $this->associateClients($reservaId, $data['clientes']);
            
            // Actualizar estado de habitación
            $this->updateRoomStatus($data['habitacion_id'], 'reservada');
            
            // Registrar log
            LoggingMiddleware::logCreate('reservas', $reservaId, $data);
            
            $this->db->commit();
            
            return $reservaId;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new BusinessException('Error al crear reserva: ' . $e->getMessage());
        }
    }
    
    /**
     * Actualizar reserva
     */
    public function update($id, $data) {
        // Obtener reserva actual
        $reservaActual = $this->reservaRepository->findById($id);
        if (!$reservaActual) {
            throw new NotFoundException('Reserva no encontrada');
        }
        
        // Validar que se pueda modificar
        if ($reservaActual['estado'] === 'finalizada' || $reservaActual['estado'] === 'cancelada') {
            throw new BusinessException('No se puede modificar una reserva finalizada o cancelada');
        }
        
        // Validar datos
        $errors = ReservaValidator::validate($data);
        if (!empty($errors)) {
            throw new ValidationException('Datos de reserva inválidos', $errors);
        }
        
        // Verificar disponibilidad si cambian fechas o habitación
        if ($data['habitacion_id'] !== $reservaActual['habitacion_id'] ||
            $data['fecha_entrada'] !== $reservaActual['fecha_entrada'] ||
            $data['fecha_salida'] !== $reservaActual['fecha_salida']) {
            
            if (!$this->reservaRepository->checkAvailability(
                $data['habitacion_id'], 
                $data['fecha_entrada'], 
                $data['fecha_salida'],
                $id
            )) {
                throw new BusinessException('La habitación no está disponible en las nuevas fechas');
            }
        }
        
        $this->db->beginTransaction();
        
        try {
            // Calcular nuevo precio
            $data['precio_total'] = $this->calculatePrice($data);
            
            // Actualizar reserva
            $this->reservaRepository->update($id, $data);
            
            // Actualizar estado de habitación si es necesario
            if ($data['habitacion_id'] !== $reservaActual['habitacion_id']) {
                $this->updateRoomStatus($reservaActual['habitacion_id'], 'disponible');
                $this->updateRoomStatus($data['habitacion_id'], 'reservada');
            }
            
            // Registrar log
            LoggingMiddleware::logUpdate('reservas', $id, $reservaActual, $data);
            
            $this->db->commit();
            
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new BusinessException('Error al actualizar reserva: ' . $e->getMessage());
        }
    }
    
    /**
     * Cancelar reserva
     */
    public function cancel($id, $motivo = '') {
        $reserva = $this->reservaRepository->findById($id);
        if (!$reserva) {
            throw new NotFoundException('Reserva no encontrada');
        }
        
        if ($reserva['estado'] === 'cancelada') {
            throw new BusinessException('La reserva ya está cancelada');
        }
        
        if ($reserva['estado'] === 'finalizada') {
            throw new BusinessException('No se puede cancelar una reserva finalizada');
        }
        
        $this->db->beginTransaction();
        
        try {
            // Actualizar estado
            $this->reservaRepository->changeStatus($id, 'cancelada');
            
            // Liberar habitación
            $this->updateRoomStatus($reserva['habitacion_id'], 'disponible');
            
            // Registrar log
            LoggingMiddleware::logStatusChange('reservas', $id, $reserva['estado'], 'cancelada');
            
            $this->db->commit();
            
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new BusinessException('Error al cancelar reserva: ' . $e->getMessage());
        }
    }
    
    /**
     * Confirmar reserva
     */
    public function confirm($id) {
        $reserva = $this->reservaRepository->findById($id);
        if (!$reserva) {
            throw new NotFoundException('Reserva no encontrada');
        }
        
        if ($reserva['estado'] !== 'pendiente') {
            throw new BusinessException('Solo se pueden confirmar reservas pendientes');
        }
        
        $this->reservaRepository->changeStatus($id, 'confirmada');
        LoggingMiddleware::logStatusChange('reservas', $id, 'pendiente', 'confirmada');
        
        return true;
    }
    
    /**
     * Realizar check-in
     */
    public function checkIn($id, $usuarioId) {
        $reserva = $this->reservaRepository->findById($id);
        if (!$reserva) {
            throw new NotFoundException('Reserva no encontrada');
        }
        
        if ($reserva['estado'] !== 'confirmada') {
            throw new BusinessException('Solo se puede hacer check-in de reservas confirmadas');
        }
        
        if (date('Y-m-d') < $reserva['fecha_entrada']) {
            throw new BusinessException('No se puede hacer check-in antes de la fecha de entrada');
        }
        
        $this->db->beginTransaction();
        
        try {
            // Actualizar estado de reserva
            $this->reservaRepository->changeStatus($id, 'ocupada');
            
            // Actualizar estado de habitación
            $this->updateRoomStatus($reserva['habitacion_id'], 'ocupada');
            
            // Registrar check-in
            $this->registerCheckIn($id, $usuarioId);
            
            // Registrar log
            LoggingMiddleware::logCheckin($id, $usuarioId);
            
            $this->db->commit();
            
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new BusinessException('Error al realizar check-in: ' . $e->getMessage());
        }
    }
    
    /**
     * Realizar check-out
     */
    public function checkOut($id, $usuarioId) {
        $reserva = $this->reservaRepository->findById($id);
        if (!$reserva) {
            throw new NotFoundException('Reserva no encontrada');
        }
        
        if ($reserva['estado'] !== 'ocupada') {
            throw new BusinessException('Solo se puede hacer check-out de reservas ocupadas');
        }
        
        $this->db->beginTransaction();
        
        try {
            // Actualizar estado de reserva
            $this->reservaRepository->changeStatus($id, 'finalizada');
            
            // Actualizar estado de habitación
            $this->updateRoomStatus($reserva['habitacion_id'], 'limpieza');
            
            // Registrar check-out
            $this->registerCheckOut($id, $usuarioId);
            
            // Registrar log
            LoggingMiddleware::logCheckout($id, $usuarioId);
            
            $this->db->commit();
            
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new BusinessException('Error al realizar check-out: ' . $e->getMessage());
        }
    }
    
    /**
     * Asociar clientes a reserva
     */
    private function associateClients($reservaId, $clientes) {
        $sql = "INSERT INTO reserva_clientes (reserva_id, cliente_id, rol) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        foreach ($clientes as $cliente) {
            $stmt->execute([$reservaId, $cliente['id'], $cliente['rol']]);
        }
    }
    
    /**
     * Calcular precio total
     */
    private function calculatePrice($data) {
        // Obtener precio base de habitación
        $sql = "SELECT precio_base FROM habitaciones WHERE id = :id AND deleted_at IS NULL";
        $habitacion = $this->db->prepare($sql)->bind(':id', $data['habitacion_id'])->fetch();
        
        if (!$habitacion) {
            throw new NotFoundException('Habitación no encontrada');
        }
        
        $precioBase = $habitacion['precio_base'];
        
        // Calcular noches
        $entrada = new DateTime($data['fecha_entrada']);
        $salida = new DateTime($data['fecha_salida']);
        $noches = $entrada->diff($salida)->days;
        
        return $precioBase * $noches;
    }
    
    /**
     * Actualizar estado de habitación
     */
    private function updateRoomStatus($habitacionId, $estado) {
        $sql = "UPDATE habitaciones SET estado = :estado WHERE id = :id";
        return $this->db->prepare($sql)
                       ->bind(':estado', $estado)
                       ->bind(':id', $habitacionId)
                       ->execute();
    }
    
    /**
     * Registrar check-in
     */
    private function registerCheckIn($reservaId, $usuarioId) {
        $sql = "INSERT INTO checkin_checkout (reserva_id, fecha_checkin, usuario_checkin) 
                VALUES (:reserva_id, NOW(), :usuario_id)
                ON DUPLICATE KEY UPDATE fecha_checkin = NOW(), usuario_checkin = :usuario_id";
        
        return $this->db->prepare($sql)
                       ->bind(':reserva_id', $reservaId)
                       ->bind(':usuario_id', $usuarioId)
                       ->execute();
    }
    
    /**
     * Registrar check-out
     */
    private function registerCheckOut($reservaId, $usuarioId) {
        $sql = "UPDATE checkin_checkout 
                SET fecha_checkout = NOW(), usuario_checkout = :usuario_id 
                WHERE reserva_id = :reserva_id";
        
        return $this->db->prepare($sql)
                       ->bind(':reserva_id', $reservaId)
                       ->bind(':usuario_id', $usuarioId)
                       ->execute();
    }
}
