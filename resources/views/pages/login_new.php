<?php
/**
 * Vista de Login mejorada con seguridad
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../app/Helpers/SecurityHelper.php';

// Si ya está logueado, redirigir al dashboard
if (SecurityHelper::validateSession()) {
    header('Location: /Hotel_tame/dashboard');
    exit;
}

$error = '';
$success = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $email = SecurityHelper::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validar datos
        if (empty($email) || empty($password)) {
            throw new Exception("Todos los campos son requeridos");
        }
        
        if (!SecurityHelper::validateEmail($email)) {
            throw new Exception("Email inválido");
        }
        
        // Verificar si cuenta está bloqueada
        if (SecurityHelper::isAccountLocked($email)) {
            throw new Exception("Cuenta temporalmente bloqueada. Intenta más tarde.");
        }
        
        // Conectar a la base de datos
        require_once __DIR__ . '/../../config/Database.php';
        require_once __DIR__ . '/../../app/Models/User.php';
        
        $userModel = new User();
        $user = $userModel->authenticate($email, $password);
        
        if ($user) {
            // Regenerar sesión
            SecurityHelper::regenerateSession();
            
            // Establecer sesión
            $_SESSION['usuario'] = [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'apellido' => $user['apellido'],
                'email' => $user['email'],
                'rol' => $user['rol'],
                'telefono' => $user['telefono']
            ];
            
            $_SESSION['last_activity'] = time();
            
            // Redirigir al dashboard
            header('Location: /Hotel_tame/dashboard');
            exit;
            
        } else {
            // Registrar intento fallido
            SecurityHelper::logFailedAttempt($email);
            throw new Exception("Credenciales incorrectas");
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Generar token CSRF
$csrfToken = SecurityHelper::generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= Config::APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .logo-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px 0 0 15px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="login-card overflow-hidden">
                    <div class="row g-0">
                        <div class="col-md-6 logo-section">
                            <div class="text-center p-4">
                                <i class="fas fa-hotel fa-4x mb-3"></i>
                                <h2 class="fw-bold"><?= Config::APP_NAME ?></h2>
                                <p class="mb-0">Sistema de Gestión Hotelera</p>
                                <small>Professional Management System</small>
                            </div>
                        </div>
                        <div class="col-md-6 p-4">
                            <div class="card-body">
                                <h3 class="card-title text-center mb-4">Iniciar Sesión</h3>
                                
                                <?php if ($error): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <?= $error ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($success): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <?= $success ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST" action="">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">
                                            <i class="fas fa-envelope me-2"></i>Email
                                        </label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label">
                                            <i class="fas fa-lock me-2"></i>Contraseña
                                        </label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password" name="password" required>
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="remember">
                                        <label class="form-check-label" for="remember">
                                            Recordarme
                                        </label>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                                        </button>
                                    </div>
                                </form>
                                
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        ¿Olvidaste tu contraseña? 
                                        <a href="#" class="text-primary">Recuperar</a>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-white">
                        © <?= date('Y') ?> <?= Config::APP_NAME ?> v<?= Config::APP_VERSION ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Auto-hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
