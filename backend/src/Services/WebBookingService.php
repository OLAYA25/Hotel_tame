<?php

namespace App\Services;

use Database\Database;
use App\Services\AppLogger;
use Exception;

class WebBookingService {
    
    /**
     * Search available rooms
     */
    public function searchAvailableRooms(array $searchParams): array {
        $checkin = $searchParams['checkin'];
        $checkout = $searchParams['checkout'];
        $adults = $searchParams['adults'] ?? 2;
        $children = $searchParams['children'] ?? 0;
        $roomType = $searchParams['room_type'] ?? null;
        $maxPrice = $searchParams['max_price'] ?? null;
        
        // Validate dates
        if (strtotime($checkin) >= strtotime($checkout)) {
            throw new Exception('Check-out date must be after check-in date');
        }
        
        if (strtotime($checkin) < strtotime(date('Y-m-d'))) {
            throw new Exception('Check-in date cannot be in the past');
        }
        
        $sql = "SELECT h.*, 
                       (SELECT AVG(rating) FROM habitacion_reviews hr WHERE hr.habitacion_id = h.id AND hr.deleted_at IS NULL) as rating,
                       (SELECT COUNT(*) FROM habitacion_reviews hr WHERE hr.habitacion_id = h.id AND hr.deleted_at IS NULL) as review_count
                FROM habitaciones h
                WHERE h.estado = 'disponible'
                AND h.capacidad >= :total_guests
                AND h.deleted_at IS NULL";
        
        $params = [':total_guests' => $adults + $children];
        
        if ($roomType) {
            $sql .= " AND h.tipo = :room_type";
            $params[':room_type'] = $roomType;
        }
        
        if ($maxPrice) {
            $sql .= " AND h.precio_base <= :max_price";
            $params[':max_price'] = $maxPrice;
        }
        
        // Check availability for the date range
        $sql .= " AND h.id NOT IN (
                    SELECT DISTINCT r.habitacion_id 
                    FROM reservas r 
                    WHERE r.estado IN ('confirmada', 'ocupada')
                    AND r.deleted_at IS NULL
                    AND (
                        (r.fecha_entrada <= :checkout AND r.fecha_salida > :checkin)
                    )
                )";
        
        $params[':checkin'] = $checkin;
        $params[':checkout'] = $checkout;
        
        $sql .= " ORDER BY h.precio_base ASC, h.numero ASC";
        
        $rooms = Database::fetchAll($sql, $params);
        
        // Calculate total price for each room
        foreach ($rooms as &$room) {
            $nights = $this->calculateNights($checkin, $checkout);
            $room['total_price'] = $this->calculateRoomPrice($room['id'], $checkin, $checkout);
            $room['nights'] = $nights;
            $room['price_per_night'] = $room['precio_base'];
        }
        
        return $rooms;
    }
    
    /**
     * Create web booking
     */
    public function createWebBooking(array $bookingData): array {
        Database::beginTransaction();
        
        try {
            // Create or find client
            $clientId = $this->createClient($bookingData['client']);
            
            // Create reservation
            $reservationData = [
                'habitacion_id' => $bookingData['room_id'],
                'fecha_entrada' => $bookingData['checkin'],
                'fecha_salida' => $bookingData['checkout'],
                'precio_total' => $bookingData['total_price'],
                'estado' => 'pendiente',
                'observaciones' => 'Reserva web - Cliente: ' . $bookingData['client']['email'],
                'origen' => 'web',
                'hotel_id' => 1
            ];
            
            $sql = "INSERT INTO reservas 
                    (habitacion_id, fecha_entrada, fecha_salida, precio_total, estado, observaciones, hotel_id) 
                    VALUES (:habitacion_id, :fecha_entrada, :fecha_salida, :precio_total, :estado, :observaciones, :hotel_id)";
            
            Database::execute($sql, $reservationData);
            $reservationId = Database::lastInsertId();
            
            // Associate client with reservation
            $this->associateClientWithReservation($reservationId, $clientId, 'titular');
            
            // Create payment record
            $paymentData = [
                'reserva_id' => $reservationId,
                'monto' => $bookingData['total_price'],
                'metodo_pago' => 'web',
                'estado' => 'pendiente',
                'fecha_pago' => null
            ];
            
            $this->createPaymentRecord($paymentData);
            
            Database::commit();
            
            AppLogger::business('Web booking created', [
                'reservation_id' => $reservationId,
                'client_id' => $clientId,
                'room_id' => $bookingData['room_id'],
                'total_price' => $bookingData['total_price']
            ]);
            
            return [
                'reservation_id' => $reservationId,
                'client_id' => $clientId,
                'status' => 'pending_payment'
            ];
            
        } catch (Exception $e) {
            Database::rollBack();
            AppLogger::error('Error creating web booking', [
                'booking_data' => $bookingData,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Get room details for web display
     */
    public function getRoomDetails(int $roomId): array {
        $sql = "SELECT h.*, 
                       (SELECT AVG(rating) FROM habitacion_reviews hr WHERE hr.habitacion_id = h.id AND hr.deleted_at IS NULL) as rating,
                       (SELECT COUNT(*) FROM habitacion_reviews hr WHERE hr.habitacion_id = h.id AND hr.deleted_at IS NULL) as review_count,
                       GROUP_CONCAT(DISTINCT a.nombre) as amenities
                FROM habitaciones h
                LEFT JOIN habitacion_amenities ha ON h.id = ha.habitacion_id
                LEFT JOIN amenities a ON ha.amenity_id = a.id
                WHERE h.id = :room_id AND h.deleted_at IS NULL
                GROUP BY h.id";
        
        $room = Database::fetch($sql, [':room_id' => $roomId]);
        
        if ($room) {
            // Get room photos
            $photosSql = "SELECT * FROM habitacion_fotos WHERE habitacion_id = :room_id AND deleted_at IS NULL ORDER BY orden";
            $room['photos'] = Database::fetchAll($photosSql, [':room_id' => $roomId]);
            
            // Get room reviews
            $reviewsSql = "SELECT cr.*, c.nombre, c.apellido 
                          FROM habitacion_reviews cr
                          LEFT JOIN clientes c ON cr.cliente_id = c.id
                          WHERE cr.habitacion_id = :room_id AND cr.deleted_at IS NULL
                          ORDER BY cr.fecha_review DESC
                          LIMIT 10";
            $room['reviews'] = Database::fetchAll($reviewsSql, [':room_id' => $roomId]);
        }
        
        return $room;
    }
    
    /**
     * Process web payment
     */
    public function processWebPayment(int $reservationId, array $paymentData): array {
        Database::beginTransaction();
        
        try {
            // Get reservation
            $reservation = $this->getReservationById($reservationId);
            if (!$reservation) {
                throw new Exception('Reservation not found');
            }
            
            // Process payment (integrate with payment gateway)
            $paymentResult = $this->processPaymentGateway($paymentData, $reservation['precio_total']);
            
            if ($paymentResult['success']) {
                // Update payment record
                $sql = "UPDATE pagos 
                        SET estado = 'pagado', fecha_pago = NOW(), transaction_id = :transaction_id, metodo_pago = :metodo_pago
                        WHERE reserva_id = :reserva_id AND estado = 'pendiente'";
                
                Database::execute($sql, [
                    ':transaction_id' => $paymentResult['transaction_id'],
                    ':metodo_pago' => $paymentData['payment_method'],
                    ':reserva_id' => $reservationId
                ]);
                
                // Update reservation status
                $sql = "UPDATE reservas SET estado = 'confirmada' WHERE id = :id";
                Database::execute($sql, [':id' => $reservationId]);
                
                // Update room status
                $sql = "UPDATE habitaciones SET estado = 'reservada' WHERE id = :id";
                Database::execute($sql, [':id' => $reservation['habitacion_id']]);
                
                Database::commit();
                
                // Send confirmation email
                $this->sendBookingConfirmation($reservationId);
                
                AppLogger::business('Web payment processed successfully', [
                    'reservation_id' => $reservationId,
                    'transaction_id' => $paymentResult['transaction_id'],
                    'amount' => $reservation['precio_total']
                ]);
                
                return [
                    'success' => true,
                    'reservation_id' => $reservationId,
                    'transaction_id' => $paymentResult['transaction_id']
                ];
            } else {
                Database::rollBack();
                throw new Exception('Payment failed: ' . $paymentResult['message']);
            }
            
        } catch (Exception $e) {
            Database::rollBack();
            AppLogger::error('Error processing web payment', [
                'reservation_id' => $reservationId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
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
     * Calculate room price for date range
     */
    private function calculateRoomPrice(int $roomId, string $checkin, string $checkout): float {
        $sql = "SELECT precio_base FROM habitaciones WHERE id = :id AND deleted_at IS NULL";
        $room = Database::fetch($sql, [':id' => $roomId]);
        
        if (!$room) {
            return 0;
        }
        
        $basePrice = $room['precio_base'];
        $nights = $this->calculateNights($checkin, $checkout);
        
        // Check for dynamic pricing
        $dynamicPriceSql = "SELECT precio FROM tarifas 
                           WHERE tipo_habitacion_id = (SELECT tipo FROM habitaciones WHERE id = :id)
                           AND fecha_inicio <= :checkin AND fecha_fin >= :checkout
                           AND deleted_at IS NULL
                           ORDER BY fecha_inicio DESC LIMIT 1";
        
        $dynamicPrice = Database::fetch($dynamicPriceSql, [
            ':id' => $roomId,
            ':checkin' => $checkin,
            ':checkout' => $checkout
        ]);
        
        $pricePerNight = $dynamicPrice ? $dynamicPrice['precio'] : $basePrice;
        
        return $pricePerNight * $nights;
    }
    
    /**
     * Create or find client
     */
    private function createClient(array $clientData): int {
        // Check if client already exists
        $sql = "SELECT id FROM clientes WHERE email = :email AND deleted_at IS NULL";
        $existing = Database::fetch($sql, [':email' => $clientData['email']]);
        
        if ($existing) {
            return $existing['id'];
        }
        
        // Create new client
        $sql = "INSERT INTO clientes (nombre, apellido, email, telefono, documento, tipo_documento, hotel_id) 
                VALUES (:nombre, :apellido, :email, :telefono, :documento, :tipo_documento, :hotel_id)";
        
        Database::execute($sql, [
            ':nombre' => $clientData['nombre'],
            ':apellido' => $clientData['apellido'],
            ':email' => $clientData['email'],
            ':telefono' => $clientData['telefono'] ?? null,
            ':documento' => $clientData['documento'] ?? null,
            ':tipo_documento' => $clientData['tipo_documento'] ?? 'CC',
            ':hotel_id' => 1
        ]);
        
        return Database::lastInsertId();
    }
    
    /**
     * Associate client with reservation
     */
    private function associateClientWithReservation(int $reservationId, int $clientId, string $rol): void {
        $sql = "INSERT INTO reserva_clientes (reserva_id, cliente_id, rol) VALUES (:reserva_id, :cliente_id, :rol)";
        Database::execute($sql, [
            ':reserva_id' => $reservationId,
            ':cliente_id' => $clientId,
            ':rol' => $rol
        ]);
    }
    
    /**
     * Create payment record
     */
    private function createPaymentRecord(array $paymentData): void {
        $sql = "INSERT INTO pagos (reserva_id, monto, metodo_pago, estado, fecha_pago) 
                VALUES (:reserva_id, :monto, :metodo_pago, :estado, :fecha_pago)";
        
        Database::execute($sql, $paymentData);
    }
    
    /**
     * Get reservation by ID
     */
    private function getReservationById(int $reservationId): ?array {
        $sql = "SELECT * FROM reservas WHERE id = :id AND deleted_at IS NULL";
        return Database::fetch($sql, [':id' => $reservationId]);
    }
    
    /**
     * Process payment gateway (mock implementation)
     */
    private function processPaymentGateway(array $paymentData, float $amount): array {
        // This would integrate with actual payment gateway (Stripe, PayPal, etc.)
        // For now, return mock success
        return [
            'success' => true,
            'transaction_id' => 'txn_' . uniqid(),
            'message' => 'Payment processed successfully'
        ];
    }
    
    /**
     * Send booking confirmation email
     */
    private function sendBookingConfirmation(int $reservationId): void {
        // This would integrate with email service
        AppLogger::business('Booking confirmation email sent', [
            'reservation_id' => $reservationId
        ]);
    }
}
