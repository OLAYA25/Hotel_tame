<?php
require_once 'config/database.php';
session_start();

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
                    header('Location: index.php');
                    break;
                case 'gerente':
                    header('Location: index.php');
                    break;
                case 'recepcionista':
                    header('Location: index.php');
                    break;
                case 'limpieza':
                    header('Location: tareas_limpieza.php');
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
    header('Location: login.php');
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
    <link rel="stylesheet" href="assets/css/style.css">
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
            padding: 60px 40px;
            flex: 1;
            min-width: 300px;
        }
        .role-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            margin: 5px;
        }
        .role-admin { background: rgba(220, 53, 69, 0.2); color: #dc3545; border: 1px solid #dc3545; }
        .role-gerente { background: rgba(255, 193, 7, 0.2); color: #ffc107; border: 1px solid #ffc107; }
        .role-recepcionista { background: rgba(23, 162, 184, 0.2); color: #17a2b4; border: 1px solid #17a2b4; }
        .role-limpieza { background: rgba(40, 167, 69, 0.2); color: #28a745; border: 1px solid #28a745; }
        
        @media (max-width: 768px) {
            .login-info {
                padding: 40px 30px;
            }
            .login-form {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Información del Sistema -->
            <div class="login-info">
                <div class="text-center mb-4">
                    <i class="fas fa-hotel fa-3x mb-3"></i>
                    <h2 class="fw-bold">Hotel Management</h2>
                    <p class="mb-4">Sistema de Gestión Hotelera</p>
                </div>
                
                <h4 class="mb-3">Accesos por Rol</h4>
                
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <span class="role-badge role-admin">Admin</span>
                        <div class="ms-3">
                            <strong>Administrador</strong>
                            <p class="small mb-0">Control total del sistema</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <span class="role-badge role-gerente">Gerente</span>
                        <div class="ms-3">
                            <strong>Gerente</strong>
                            <p class="small mb-0">Gestión y reportes</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <span class="role-badge role-recepcionista">Recepcionista</span>
                        <div class="ms-3">
                            <strong>Recepcionista</strong>
                            <p class="small mb-0">Check-in/out y reservas</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <span class="role-badge role-limpieza">Limpieza</span>
                        <div class="ms-3">
                            <strong>Personal de Limpieza</strong>
                            <p class="small mb-0">Gestión de habitaciones</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-auto">
                    <hr class="border-white border-opacity-25">
                    <p class="small mb-2">
                        <i class="fas fa-envelope me-2"></i>
                        soporte@hotelmanagement.com
                    </p>
                    <p class="small mb-0">
                        <i class="fas fa-phone me-2"></i>
                        +1 234 567 890
                    </p>
                </div>
            </div>
            
            <!-- Formulario de Login -->
            <div class="login-form">
                <div class="text-center mb-4">
                    <h3 class="fw-bold">Iniciar Sesión</h3>
                    <p class="text-muted">Ingresa tus credenciales para acceder</p>
                </div>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" class="form-control" name="email" required 
                                   placeholder="tu@email.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control" name="password" required 
                                   placeholder="Ingresa tu contraseña" id="password">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember">
                            <label class="form-check-label" for="remember">
                                Recordar sesión
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Iniciar Sesión
                    </button>
                    
                    <div class="text-center">
                        <a href="#" class="text-muted text-decoration-none">
                            <i class="fas fa-question-circle me-1"></i>
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                </form>
                
                <!-- Cuentas de Demostración -->
                <div class="mt-4 p-3 bg-light rounded">
                    <h6 class="fw-bold mb-3">Cuentas de Demostración:</h6>
                    <div class="small">
                        <div class="mb-2">
                            <strong>Admin:</strong> admin@hotel.com / password
                        </div>
                        <div class="mb-2">
                            <strong>Gerente:</strong> gerente@hotel.com / password
                        </div>
                        <div>
                            <strong>Recepcionista:</strong> recepcion@hotel.com / password
                        </div>
                        <div>
                            <strong>Limpieza:</strong> limpieza@hotel.com / password
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="home.php" class="btn btn-outline-primary">
                        <i class="fas fa-home me-2"></i>
                        Volver al Inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Auto-focus email field
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('input[name="email"]').focus();
        });
    </script>
</body>
</html>
