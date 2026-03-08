<?php
require_once __DIR__ . '/../../../backend/config/database.php';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
                
        $stmt = $db->prepare("SELECT id, nombre, apellido, email, password, rol, telefono FROM usuarios WHERE email = :email AND activo = 1 AND deleted_at IS NULL");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['usuario'] = [
                'id' => $usuario['id'],
                'nombre' => $usuario['nombre'],
                'apellido' => $usuario['apellido'],
                'email' => $usuario['email'],
                'rol' => $usuario['rol'],
                'telefono' => $usuario['telefono']
            ];
            
            // Redirigir según el rol
            switch ($usuario['rol']) {
                case 'admin':
                    header('Location: /Hotel_tame/dashboard');
                    break;
                case 'gerente':
                    header('Location: /Hotel_tame/dashboard');
                    break;
                case 'recepcionista':
                    header('Location: /Hotel_tame/dashboard');
                    break;
                case 'limpieza':
                    header('Location: /Hotel_tame/dashboard');
                    break;
                default:
                    $error = "Rol no reconocido";
            }
            exit;
        } else {
            $error = "Email o contraseña incorrectos";
        }
    } catch (Exception $e) {
        $error = "Error en el sistema: " . $e->getMessage();
    }
}

// Procesar logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header('Location: /Hotel_tame/login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hotel Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/Hotel_tame/assets/css/style.css">
    <style>
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            display: flex;
            flex-wrap: wrap;
        }
        .login-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
            flex: 1;
            min-width: 300px;
        }
        .login-form {
            flex: 1;
            min-width: 300px;
            padding: 60px 40px;
        }
        .login-form h2 {
            color: #333;
            margin-bottom: 30px;
            font-weight: 600;
        }
        .form-floating {
            margin-bottom: 20px;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 50px;
            padding: 15px 40px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        .hotel-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        .hotel-name {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .hotel-description {
            font-size: 1.1rem;
            line-height: 1.6;
            opacity: 0.9;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        @media (max-width: 768px) {
            .login-card {
                flex-direction: column;
            }
            .login-info, .login-form {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-info">
                <div class="text-center">
                    <i class="fas fa-hotel hotel-icon"></i>
                    <h1 class="hotel-name">Hotel Tame</h1>
                    <p class="hotel-description">
                        Bienvenido al sistema de gestión hotelera más completo. 
                        Administra tus reservas, habitaciones y clientes con la mejor tecnología.
                    </p>
                </div>
            </div>
            <div class="login-form">
                <h2>Iniciar Sesión</h2>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-floating">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                        <label for="email"><i class="fas fa-envelope me-2"></i>Email</label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
                        <label for="password"><i class="fas fa-lock me-2"></i>Contraseña</label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-4">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i>
                        Acceso seguro y encriptado
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
