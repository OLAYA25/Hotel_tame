<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../backend/config/database.php';

// Iniciar sesión para verificar acceso
session_start();

// Verificar si el usuario está autenticado (excepto para GET que puede ser público)
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && !isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado - Debe iniciar sesión'
    ]);
    exit;
}

// Verificar método de la solicitud
$method = $_SERVER['REQUEST_METHOD'];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception('No se pudo establecer conexión a la base de datos');
    }
    
    switch ($method) {
        case 'GET':
            // Obtener todas las categorías
            $stmt = $db->query("SELECT * FROM categorias WHERE activo = TRUE ORDER BY nombre");
            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $categorias
            ]);
            break;
            
        case 'POST':
            // Crear nueva categoría
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            error_log("Datos recibidos: " . $input);
            
            if (!$data || !isset($data['nombre'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'El nombre de la categoría es obligatorio'
                ]);
                break;
            }
            
            // Verificar si ya existe
            $stmt = $db->prepare("SELECT id FROM categorias WHERE nombre = ? AND activo = TRUE");
            $stmt->execute([$data['nombre']]);
            
            if ($stmt->rowCount() > 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Ya existe una categoría con ese nombre'
                ]);
                break;
            }
            
            // Insertar nueva categoría
            $stmt = $db->prepare("INSERT INTO categorias (nombre, icono, color) VALUES (?, ?, ?)");
            $result = $stmt->execute([
                $data['nombre'],
                $data['icono'] ?? 'fas fa-box',
                $data['color'] ?? '#007bff'
            ]);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Categoría creada exitosamente'
                ]);
            } else {
                throw new Exception('Error al insertar categoría');
            }
            break;
            
        case 'PUT':
            // Actualizar categoría (para futuras implementaciones)
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id']) || !isset($data['nombre'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID y nombre son obligatorios'
                ]);
                break;
            }
            
            $stmt = $db->prepare("UPDATE categorias SET nombre = ?, icono = ?, color = ? WHERE id = ?");
            $stmt->execute([
                $data['nombre'],
                $data['icono'] ?? 'fas fa-box',
                $data['color'] ?? '#007bff',
                $data['id']
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Categoría actualizada exitosamente'
            ]);
            break;
            
        case 'DELETE':
            // Eliminar categoría (desactivar)
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID es obligatorio'
                ]);
                break;
            }
            
            // Verificar si hay productos usando esta categoría
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM productos WHERE categoria = (SELECT nombre FROM categorias WHERE id = ?) AND activo = TRUE");
            $stmt->execute([$data['id']]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'No se puede eliminar la categoría porque hay productos asociados'
                ]);
                break;
            }
            
            // Desactivar categoría (no eliminar físicamente)
            $stmt = $db->prepare("UPDATE categorias SET activo = FALSE WHERE id = ?");
            $stmt->execute([$data['id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Categoría eliminada exitosamente'
            ]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Método no permitido'
            ]);
            break;
    }
    
} catch (Exception $e) {
    error_log("Error en categorías API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}
?>
