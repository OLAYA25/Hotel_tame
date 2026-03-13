<?php

namespace App\Services;

use Database\Database;
use App\Services\AppLogger;
use Exception;

class CRMService {
    
    /**
     * Get guest profile with preferences and history
     */
    public function getGuestProfile(int $clientId): array {
        // Get basic client info
        $sql = "SELECT c.*, cl.nivel, cl.puntos_acumulados, cl.total_estanias, cl.total_noches, cl.total_gastado, cl.ultima_estancia
                FROM clientes c
                LEFT JOIN clientes_loyalty cl ON c.id = cl.cliente_id
                WHERE c.id = :client_id AND c.deleted_at IS NULL";
        
        $client = Database::fetch($sql, [':client_id' => $clientId]);
        
        if (!$client) {
            return null;
        }
        
        // Get preferences
        $preferencesSql = "SELECT * FROM clientes_preferencias WHERE cliente_id = :client_id AND deleted_at IS NULL";
        $client['preferences'] = Database::fetch($preferencesSql, [':client_id' => $clientId]);
        
        // Get reservation history
        $historySql = "SELECT r.*, h.numero as habitacion_numero, h.tipo as habitacion_tipo
                      FROM reservas r
                      LEFT JOIN habitaciones h ON r.habitacion_id = h.id
                      WHERE r.id IN (SELECT reserva_id FROM reserva_clientes WHERE cliente_id = :client_id)
                      AND r.deleted_at IS NULL
                      ORDER BY r.fecha_entrada DESC
                      LIMIT 10";
        
        $client['reservation_history'] = Database::fetchAll($historySql, [':client_id' => $clientId]);
        
        // Get reviews
        $reviewsSql = "SELECT hr.*, h.numero as habitacion_numero
                      FROM habitacion_reviews hr
                      LEFT JOIN habitaciones h ON hr.habitacion_id = h.id
                      WHERE hr.cliente_id = :client_id AND hr.deleted_at IS NULL
                      ORDER BY hr.fecha_review DESC
                      LIMIT 5";
        
        $client['reviews'] = Database::fetchAll($reviewsSql, [':client_id' => $clientId]);
        
        // Get communication history
        $commSql = "SELECT * FROM clientes_comunicaciones 
                   WHERE cliente_id = :client_id AND deleted_at IS NULL
                   ORDER BY fecha_comunicacion DESC
                   LIMIT 10";
        
        $client['communications'] = Database::fetchAll($commSql, [':client_id' => $clientId]);
        
        // Get special requests
        $requestsSql = "SELECT * FROM clientes_solicitudes_especiales 
                        WHERE cliente_id = :client_id AND deleted_at IS NULL
                        ORDER BY created_at DESC
                        LIMIT 10";
        
        $client['special_requests'] = Database::fetchAll($requestsSql, [':client_id' => $clientId]);
        
        return $client;
    }
    
    /**
     * Update guest preferences
     */
    public function updateGuestPreferences(int $clientId, array $preferences): bool {
        $sql = "UPDATE clientes_preferencias 
                SET tipo_habitacion_preferida = :tipo_habitacion_preferida,
                    piso_preferido = :piso_preferido,
                    cama_preferida = :cama_preferida,
                    vista_preferida = :vista_preferida,
                    fumar = :fumar,
                    mascotas = :mascotas,
                    alergias = :alergias,
                    necesidades_especiales = :necesidades_especiales,
                    preferencias_comida = :preferencias_comida,
                    preferencias_temperatura = :preferencias_temperatura,
                    ruido_preferido = :ruido_preferido,
                    frecuencia_visita = :frecuencia_visita,
                    motivo_viaje = :motivo_viaje,
                    observaciones = :observaciones,
                    updated_at = NOW()
                WHERE cliente_id = :client_id";
        
        $params = array_merge($preferences, [':client_id' => $clientId]);
        
        $result = Database::execute($sql, $params);
        
        AppLogger::business('Guest preferences updated', [
            'client_id' => $clientId,
            'preferences' => $preferences
        ]);
        
        return $result;
    }
    
    /**
     * Create guest preferences
     */
    public function createGuestPreferences(int $clientId, array $preferences): int {
        $sql = "INSERT INTO clientes_preferencias 
                (cliente_id, tipo_habitacion_preferida, piso_preferido, cama_preferida, vista_preferida, 
                 fumar, mascotas, alergias, necesidades_especiales, preferencias_comida, 
                 preferencias_temperatura, ruido_preferido, frecuencia_visita, motivo_viaje, observaciones, hotel_id) 
                VALUES (:cliente_id, :tipo_habitacion_preferida, :piso_preferido, :cama_preferida, :vista_preferida,
                        :fumar, :mascotas, :alergias, :necesidades_especiales, :preferencias_comida,
                        :preferencias_temperatura, :ruido_preferido, :frecuencia_visita, :motivo_viaje, :observaciones, :hotel_id)";
        
        $params = array_merge([':cliente_id' => $clientId, ':hotel_id' => 1], $preferences);
        
        Database::execute($sql, $params);
        $preferenceId = Database::lastInsertId();
        
        AppLogger::business('Guest preferences created', [
            'preference_id' => $preferenceId,
            'client_id' => $clientId
        ]);
        
        return $preferenceId;
    }
    
    /**
     * Add communication record
     */
    public function addCommunication(int $clientId, array $communicationData): int {
        $sql = "INSERT INTO clientes_comunicaciones 
                (cliente_id, tipo_comunicacion, asunto, mensaje, enviado_por, proximo_seguimiento, categoria, hotel_id) 
                VALUES (:cliente_id, :tipo_comunicacion, :asunto, :mensaje, :enviado_por, :proximo_seguimiento, :categoria, :hotel_id)";
        
        $params = array_merge([
            ':cliente_id' => $clientId,
            ':hotel_id' => 1
        ], $communicationData);
        
        Database::execute($sql, $params);
        $commId = Database::lastInsertId();
        
        AppLogger::business('Guest communication added', [
            'communication_id' => $commId,
            'client_id' => $clientId,
            'type' => $communicationData['tipo_comunicacion']
        ]);
        
        return $commId;
    }
    
    /**
     * Add guest review
     */
    public function addGuestReview(array $reviewData): int {
        $sql = "INSERT INTO habitacion_reviews 
                (reserva_id, cliente_id, habitacion_id, rating, titulo, comentario, 
                 limpieza_rating, servicio_rating, comodidad_rating, ubicacion_rating, precio_rating, hotel_id) 
                VALUES (:reserva_id, :cliente_id, :habitacion_id, :rating, :titulo, :comentario,
                        :limpieza_rating, :servicio_rating, :comodidad_rating, :ubicacion_rating, :precio_rating, :hotel_id)";
        
        Database::execute($sql, $reviewData);
        $reviewId = Database::lastInsertId();
        
        AppLogger::business('Guest review added', [
            'review_id' => $reviewId,
            'reservation_id' => $reviewData['reserva_id'],
            'rating' => $reviewData['rating']
        ]);
        
        return $reviewId;
    }
    
    /**
     * Respond to guest review
     */
    public function respondToReview(int $reviewId, string $response, int $respondedPor): bool {
        $sql = "UPDATE habitacion_reviews 
                SET respuesta_hotel = :respuesta_hotel, 
                    fecha_respuesta = NOW(), 
                    respondido_por = :respondido_por,
                    updated_at = NOW()
                WHERE id = :review_id";
        
        $params = [
            ':respuesta_hotel' => $response,
            ':respondido_por' => $respondedPor,
            ':review_id' => $reviewId
        ];
        
        $result = Database::execute($sql, $params);
        
        AppLogger::business('Review response added', [
            'review_id' => $reviewId,
            'responded_by' => $respondedPor
        ]);
        
        return $result;
    }
    
    /**
     * Get guest statistics
     */
    public function getGuestStatistics(): array {
        $sql = "SELECT 
                    COUNT(*) as total_guests,
                    COUNT(CASE WHEN cl.nivel = 'platinum' THEN 1 END) as platinum_guests,
                    COUNT(CASE WHEN cl.nivel = 'gold' THEN 1 END) as gold_guests,
                    COUNT(CASE WHEN cl.nivel = 'silver' THEN 1 END) as silver_guests,
                    COUNT(CASE WHEN cl.nivel = 'bronze' THEN 1 END) as bronze_guests,
                    AVG(cl.total_estanias) as avg_stays,
                    AVG(cl.total_noches) as avg_nights,
                    AVG(cl.total_gastado) as avg_spent,
                    SUM(cl.total_gastado) as total_revenue
                FROM clientes c
                LEFT JOIN clientes_loyalty cl ON c.id = cl.cliente_id
                WHERE c.deleted_at IS NULL";
        
        $stats = Database::fetch($sql);
        
        // Get top spending guests
        $topGuestsSql = "SELECT c.id, c.nombre, c.apellido, c.email, cl.total_gastado, cl.total_estanias, cl.nivel
                         FROM clientes c
                         LEFT JOIN clientes_loyalty cl ON c.id = cl.cliente_id
                         WHERE c.deleted_at IS NULL AND cl.total_gastado > 0
                         ORDER BY cl.total_gastado DESC
                         LIMIT 10";
        
        $stats['top_guests'] = Database::fetchAll($topGuestsSql);
        
        // Get recent reviews
        $recentReviewsSql = "SELECT hr.*, c.nombre, c.apellido, h.numero as habitacion_numero
                            FROM habitacion_reviews hr
                            LEFT JOIN clientes c ON hr.cliente_id = c.id
                            LEFT JOIN habitaciones h ON hr.habitacion_id = h.id
                            WHERE hr.deleted_at IS NULL
                            ORDER BY hr.fecha_review DESC
                            LIMIT 5";
        
        $stats['recent_reviews'] = Database::fetchAll($recentReviewsSql);
        
        return $stats;
    }
    
    /**
     * Get guests with follow-up reminders
     */
    public function getGuestsWithFollowUp(): array {
        $sql = "SELECT DISTINCT c.id, c.nombre, c.apellido, c.email, cc.proximo_seguimiento, cc.tipo_comunicacion
                FROM clientes c
                INNER JOIN clientes_comunicaciones cc ON c.id = cc.cliente_id
                WHERE c.deleted_at IS NULL 
                AND cc.deleted_at IS NULL
                AND cc.proximo_seguimiento IS NOT NULL
                AND cc.proximo_seguimiento <= CURDATE()
                ORDER BY cc.proximo_seguimiento ASC";
        
        return Database::fetchAll($sql);
    }
    
    /**
     * Update guest loyalty points
     */
    public function updateLoyaltyPoints(int $clientId, int $points, string $action = 'add'): bool {
        $operation = $action === 'add' ? '+' : '-';
        
        $sql = "UPDATE clientes_loyalty 
                SET puntos_acumulados = puntos_acumulados {$operation} :points,
                    updated_at = NOW()
                WHERE cliente_id = :client_id";
        
        $params = [
            ':points' => $points,
            ':client_id' => $clientId
        ];
        
        $result = Database::execute($sql, $params);
        
        AppLogger::business('Guest loyalty points updated', [
            'client_id' => $clientId,
            'points' => $points,
            'action' => $action
        ]);
        
        return $result;
    }
    
    /**
     * Get guest room recommendations based on preferences
     */
    public function getGuestRoomRecommendations(int $clientId, string $checkin, string $checkout): array {
        $preferencesSql = "SELECT * FROM clientes_preferencias WHERE cliente_id = :client_id AND deleted_at IS NULL";
        $preferences = Database::fetch($preferencesSql, [':client_id' => $clientId]);
        
        if (!$preferences) {
            return [];
        }
        
        $sql = "SELECT h.*, 
                       (SELECT AVG(rating) FROM habitacion_reviews hr WHERE hr.habitacion_id = h.id AND hr.deleted_at IS NULL) as rating
                FROM habitaciones h
                WHERE h.estado = 'disponible'
                AND h.deleted_at IS NULL";
        
        $params = [];
        
        if ($preferences['tipo_habitacion_preferida']) {
            $sql .= " AND h.tipo = :tipo_habitacion";
            $params[':tipo_habitacion'] = $preferences['tipo_habitacion_preferida'];
        }
        
        if ($preferences['piso_preferido']) {
            $sql .= " AND h.piso = :piso_preferido";
            $params[':piso_preferido'] = $preferences['piso_preferido'];
        }
        
        // Check availability
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
        
        $sql .= " ORDER BY h.precio_base ASC LIMIT 5";
        
        return Database::fetchAll($sql, $params);
    }
}
