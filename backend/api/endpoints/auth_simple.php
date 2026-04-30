<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// session_start(); // Ya iniciada en router

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Verificar estado de autenticación
        if (isset($_GET['check'])) {
            $isAuthenticated = isset($_SESSION['usuario']);
            $user = null;
            
            if ($isAuthenticated) {
                $user = $_SESSION['usuario'];
            }
            
            echo json_encode([
                'authenticated' => $isAuthenticated,
                'user' => $user
            ]);
        } else {
            // Si no hay parámetro check, devolver estado general
            echo json_encode([
                'message' => 'API Auth funcionando (modo simple)',
                'method' => 'GET',
                'session_active' => isset($_SESSION['usuario'])
            ]);
        }
        break;
        
    case 'POST':
        // Login simplificado para pruebas
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email y contraseña son requeridos']);
            exit;
        }
        
        $email = $data['email'];
        $password = $data['password'];
        
        // Login de prueba sin base de datos
        if ($email === 'admin@hotel.com' && $password === 'admin123') {
            $_SESSION['usuario'] = [
                'id' => 1,
                'nombre' => 'Administrador',
                'apellido' => 'Sistema',
                'email' => 'admin@hotel.com',
                'rol' => 'admin'
            ];
            
            echo json_encode([
                'success' => true,
                'message' => 'Login exitoso (modo prueba)',
                'user' => [
                    'id' => 1,
                    'nombre' => 'Administrador',
                    'apellido' => 'Sistema',
                    'rol' => 'admin',
                    'email' => 'admin@hotel.com'
                ]
            ]);
        } else if ($email === 'user@hotel.com' && $password === 'user123') {
            $_SESSION['usuario'] = [
                'id' => 2,
                'nombre' => 'Usuario',
                'apellido' => 'Prueba',
                'email' => 'user@hotel.com',
                'rol' => 'user'
            ];
            
            echo json_encode([
                'success' => true,
                'message' => 'Login exitoso (modo prueba)',
                'user' => [
                    'id' => 2,
                    'nombre' => 'Usuario',
                    'apellido' => 'Prueba',
                    'rol' => 'user',
                    'email' => 'user@hotel.com'
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Credenciales incorrectas. Use admin@hotel.com/admin123 o user@hotel.com/user123']);
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
