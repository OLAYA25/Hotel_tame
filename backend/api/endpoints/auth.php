<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Incluir archivos necesarios
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../includes/simple_permissions.php';

// session_start(); // Ya iniciada en router

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Verificar estado de autenticación
        if (isset($_GET['check'])) {
            $isAuthenticated = isset($_SESSION['usuario']);
            $user = null;
            
            if ($isAuthenticated) {
                $user = [
                    'id' => $_SESSION['usuario']['id'],
                    'nombre' => $_SESSION['usuario']['nombre'],
                    'apellido' => $_SESSION['usuario']['apellido'],
                    'rol' => $_SESSION['usuario']['rol'],
                    'email' => $_SESSION['usuario']['email']
                ];
            }
            
            echo json_encode([
                'authenticated' => $isAuthenticated,
                'user' => $user
            ]);
        } else {
            // Si no hay parámetro check, devolver estado general
            echo json_encode([
                'message' => 'API Auth funcionando con BD',
                'method' => 'GET',
                'session_active' => isset($_SESSION['usuario'])
            ]);
        }
        break;
        
    case 'POST':
        // Login con base de datos real
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email y contraseña son requeridos']);
            exit;
        }
        
        $email = $data['email'];
        $password = $data['password'];
        
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Buscar usuario en la base de datos
            $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email AND activo = 1");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Crear sesión
                $_SESSION['usuario'] = [
                    'id' => $user['id'],
                    'nombre' => $user['nombre'],
                    'apellido' => $user['apellido'],
                    'email' => $user['email'],
                    'rol' => $user['rol']
                ];
                
                // Actualizar último login (si la columna existe)
                try {
                    $updateStmt = $db->prepare("UPDATE usuarios SET updated_at = NOW() WHERE id = :id");
                    $updateStmt->bindParam(':id', $user['id']);
                    $updateStmt->execute();
                } catch (Exception $e) {
                    // Ignorar error si la columna no existe
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Login exitoso',
                    'user' => [
                        'id' => $user['id'],
                        'nombre' => $user['nombre'],
                        'apellido' => $user['apellido'],
                        'rol' => $user['rol'],
                        'email' => $user['email']
                    ]
                ]);
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Credenciales incorrectas']);
            }
        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error en el servidor: ' . $e->getMessage()]);
        }
        break;
        
    case 'DELETE':
        // Logout
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Sesión cerrada']);
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>
