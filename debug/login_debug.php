<?php
require_once 'config/database.php';
session_start();

// Procesar login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    echo "<!DOCTYPE html><html><head><title>Debug Login</title></head><body>";
    echo "<h1>🔍 DEBUG LOGIN</h1>";
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        echo "<p>✅ Conexión BD exitosa</p>";
        
        $stmt = $db->prepare("SELECT id, nombre, apellido, email, password, rol, telefono FROM usuarios WHERE email = :email AND activo = 1 AND deleted_at IS NULL");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $usuario = $stmt->fetch();
        echo "<p>🔍 Usuario encontrado: " . ($usuario ? 'Sí' : 'No') . "</p>";
        
        if ($usuario && password_verify($password, $usuario['password'])) {
            echo "<p>✅ Contraseña correcta</p>";
            
            $_SESSION['usuario'] = [
                'id' => $usuario['id'],
                'nombre' => $usuario['nombre'],
                'apellido' => $usuario['apellido'],
                'email' => $usuario['email'],
                'rol' => $usuario['rol'],
                'telefono' => $usuario['telefono']
            ];
            
            echo "<p>✅ Sesión creada</p>";
            echo "<p>👤 Usuario: " . $_SESSION['usuario']['nombre'] . " " . $_SESSION['usuario']['apellido'] . "</p>";
            echo "<p>🔑 Rol: " . $_SESSION['usuario']['rol'] . "</p>";
            
            // Redirigir según el rol
            echo "<p>🔄 Redirigiendo a index.php...</p>";
            header('Location: index.php');
            exit;
            
        } else {
            echo "<p>❌ Contraseña incorrecta o usuario no encontrado</p>";
            echo "<p><a href='login.php'>Volver al login</a></p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    }
    
    echo "</body></html>";
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            margin: 20px;
        }
        .login-image {
            background: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3') center/cover;
            min-height: 500px;
            position: relative;
        }
        .login-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
        }
        .login-form {
            padding: 60px 40px;
        }
        .form-control {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 12px 20px;
            font-size: 16px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .hotel-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .hotel-logo i {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 10px;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="row g-0">
            <div class="col-md-6">
                <div class="login-image"></div>
            </div>
            <div class="col-md-6">
                <div class="login-form">
                    <div class="hotel-logo">
                        <i class="fas fa-hotel"></i>
                        <h2>Hotel Management</h2>
                        <p class="text-muted">Sistema de Gestión Hotelera</p>
                    </div>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="login_debug.php">
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2"></i>Correo Electrónico
                            </label>
                            <input type="email" class="form-control" id="email" name="email" required 
                                   placeholder="correo@ejemplo.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Contraseña
                            </label>
                            <input type="password" class="form-control" id="password" name="password" required 
                                   placeholder="••••••••">
                        </div>
                        
                        <button type="submit" class="btn btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión (Debug)
                        </button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i>
                            Acceso seguro al sistema
                        </small>
                    </div>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            © 2024 Hotel Management System
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
