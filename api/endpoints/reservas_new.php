<?php
/**
 * API Endpoint mejorado para reservas
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-CSRF-Token");

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../app/Models/Reservation.php';
require_once __DIR__ . '/../../app/Models/Room.php';
require_once __DIR__ . '/../../app/Models/Client.php';
require_once __DIR__ . '/../../app/Helpers/SecurityHelper.php';

// Iniciar sesión para validación
session_start();

// Validar sesión para operaciones que no sean login
if (!SecurityHelper::validateSession()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$database = Database::getInstance();
$reservation = new Reservation();
$room = new Room();
$client = new Client();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'resumen_financiero') {
                $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
                $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
                
                $stats = $reservation->getStatistics('month');
                
                echo json_encode([
                    'ingresos' => $stats['ingresos_totales'] ?? 0,
                    'reservas' => $stats['total_reservas'] ?? 0,
                    'ocupacion' => 75, // Placeholder - calcular ocupación real
                    'clientes' => $client->count()
                ]);
            }
            elseif ($action === 'balance_comprobacion') {
                $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
                $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
                
                // Balance de comprobación simplificado
                echo json_encode([
                    'debe' => 100000,
                    'haber' => 100000,
                    'saldo' => 0
                ]);
            }
            else {
                // Obtener todas las reservas
                $reservas = $reservation->getWithDetails();
                echo json_encode($reservas);
            }
            break;
            
        case 'POST':
            if ($action === 'create') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                // Validar token CSRF
                if (!isset($data['csrf_token']) || !SecurityHelper::verifyCSRFToken($data['csrf_token'])) {
                    throw new Exception("Token CSRF inválido");
                }
                
                // Validar datos requeridos
                if (!isset($data['habitacion_id']) || !isset($data['fecha_entrada']) || !isset($data['fecha_salida'])) {
                    throw new Exception("Faltan datos requeridos");
                }
                
                // Verificar disponibilidad
                if (!$reservation->checkAvailability($data['habitacion_id'], $data['fecha_entrada'], $data['fecha_salida'])) {
                    throw new Exception("La habitación no está disponible en las fechas seleccionadas");
                }
                
                // Crear reserva
                $reservaId = $reservation->createWithValidation($data, $data['clientes'] ?? []);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Reserva creada exitosamente',
                    'reserva_id' => $reservaId
                ]);
            }
            break;
            
        case 'PUT':
            if ($action === 'update') {
                $data = json_decode(file_get_contents('php://input'), true);
                $id = $_GET['id'] ?? null;
                
                if (!$id) {
                    throw new Exception("ID de reserva requerido");
                }
                
                $reservation->update($id, $data);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Reserva actualizada exitosamente'
                ]);
            }
            break;
            
        case 'DELETE':
            if ($action === 'delete') {
                $id = $_GET['id'] ?? null;
                
                if (!$id) {
                    throw new Exception("ID de reserva requerido");
                }
                
                $reservation->cancel($id);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Reserva cancelada exitosamente'
                ]);
            }
            break;
            
        default:
            throw new Exception("Método no permitido");
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
