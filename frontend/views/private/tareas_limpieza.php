<?php
require_once 'config/database.php';

// Verificar sesión de usuario
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Solo personal de limpieza puede acceder
if ($_SESSION['usuario']['rol'] !== 'limpieza') {
    header('Location: index.php');
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Obtener habitaciones que necesitan limpieza
    $stmt = $db->query("SELECT h.id, h.numero, h.tipo, h.piso, h.estado, 
                                r.fecha_salida, r.fecha_entrada as proxima_entrada,
                                (SELECT COUNT(*) FROM reservas WHERE habitacion_id = h.id AND estado = 'confirmada' AND deleted_at IS NULL) as reservas_activas
                         FROM habitaciones h 
                         LEFT JOIN reservas r ON h.id = r.habitacion_id AND r.estado = 'confirmada' 
                         WHERE (h.estado = 'limpieza' OR h.estado = 'ocupada') 
                         AND h.deleted_at IS NULL 
                         ORDER BY h.piso, h.numero");
    $habitaciones = $stmt ? $stmt->fetchAll() : [];

} catch (Exception $e) {
    $error = "Error cargando datos: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tareas de Limpieza - Hotel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .room-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .status-limpieza { background: #fff3cd; color: #856404; }
        .status-ocupada { background: #f8d7da; color: #721c24; }
        .status-disponible { background: #d4edda; color: #155724; }
        .priority-urgent {
            border-left: 4px solid #dc3545;
        }
        .priority-high {
            border-left: 4px solid #ffc107;
        }
        .priority-normal {
            border-left: 4px solid #28a745;
        }
        .task-checklist {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
        }
        .checklist-item {
            margin: 8px 0;
            cursor: pointer;
        }
        .checklist-item:hover {
            background: #e9ecef;
            border-radius: 5px;
            padding: 5px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div id="notification-container"></div>
        
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-broom me-2"></i>Tareas de Limpieza</h1>
                    <p class="text-muted mb-0">Gestiona el estado de limpieza de las habitaciones</p>
                </div>
                <div>
                    <span class="badge bg-success fs-6">
                        <i class="fas fa-user me-1"></i>
                        <?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?>
                    </span>
                </div>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Estadísticas Rápidas -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-broom fa-2x text-warning mb-2"></i>
                        <h4><?php echo count(array_filter($habitaciones, fn($h) => $h['estado'] == 'limpieza')); ?></h4>
                        <p class="text-muted mb-0">Por Limpiar</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-bed fa-2x text-danger mb-2"></i>
                        <h4><?php echo count(array_filter($habitaciones, fn($h) => $h['estado'] == 'ocupada')); ?></h4>
                        <p class="text-muted mb-0">Ocupadas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-clock fa-2x text-info mb-2"></i>
                        <h4><?php echo count(array_filter($habitaciones, fn($h) => $h['proxima_entrada'] && strtotime($h['proxima_entrada']) < strtotime('+6 hours'))); ?></h4>
                        <p class="text-muted mb-0">Próximas Entradas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <h4><?php echo count(array_filter($habitaciones, fn($h) => $h['estado'] == 'disponible')); ?></h4>
                        <p class="text-muted mb-0">Disponibles</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Habitaciones -->
        <div class="row g-4">
            <?php if (empty($habitaciones)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-2"></i>
                        No hay habitaciones asignadas para limpieza en este momento.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($habitaciones as $habitacion): ?>
                    <?php
                    // Determinar prioridad
                    $priority = 'normal';
                    if ($habitacion['proxima_entrada'] && strtotime($habitacion['proxima_entrada']) < strtotime('+2 hours')) {
                        $priority = 'urgent';
                    } elseif ($habitacion['proxima_entrada'] && strtotime($habitacion['proxima_entrada']) < strtotime('+6 hours')) {
                        $priority = 'high';
                    }
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card room-card h-100 priority-<?php echo $priority; ?>">
                            <div class="card-header bg-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-door-open me-2"></i>
                                        Habitación <?php echo htmlspecialchars($habitacion['numero']); ?>
                                    </h5>
                                    <span class="badge status-<?php echo $habitacion['estado']; ?>">
                                        <?php echo ucfirst($habitacion['estado']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-layer-group me-1"></i>
                                        Piso <?php echo $habitacion['piso']; ?> • 
                                        <i class="fas fa-bed me-1"></i>
                                        <?php echo ucfirst($habitacion['tipo']); ?>
                                    </small>
                                </div>

                                <?php if ($habitacion['proxima_entrada']): ?>
                                    <div class="alert alert-info py-2 mb-3">
                                        <small>
                                            <i class="fas fa-clock me-1"></i>
                                            Próxima entrada: <?php echo date('d/m H:i', strtotime($habitacion['proxima_entrada'])); ?>
                                        </small>
                                    </div>
                                <?php endif; ?>

                                <?php if ($habitacion['estado'] == 'limpieza'): ?>
                                    <div class="task-checklist">
                                        <h6 class="mb-3">
                                            <i class="fas fa-tasks me-2"></i>
                                            Checklist de Limpieza
                                        </h6>
                                        <div class="checklist-item" onclick="toggleTask(this)">
                                            <i class="far fa-square me-2"></i>
                                            Cambiar sábanas y toallas
                                        </div>
                                        <div class="checklist-item" onclick="toggleTask(this)">
                                            <i class="far fa-square me-2"></i>
                                            Limpiar baño completo
                                        </div>
                                        <div class="checklist-item" onclick="toggleTask(this)">
                                            <i class="far fa-square me-2"></i>
                                            Aspirar y trapear piso
                                        </div>
                                        <div class="checklist-item" onclick="toggleTask(this)">
                                            <i class="far fa-square me-2"></i>
                                            Limpiar superficies y muebles
                                        </div>
                                        <div class="checklist-item" onclick="toggleTask(this)">
                                            <i class="far fa-square me-2"></i>
                                            Reponer amenidades
                                        </div>
                                        <div class="checklist-item" onclick="toggleTask(this)">
                                            <i class="far fa-square me-2"></i>
                                            Sacar basura
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="mt-3">
                                    <?php if ($habitacion['estado'] == 'limpieza'): ?>
                                        <button class="btn btn-success w-100" onclick="marcarLimpia(<?php echo $habitacion['id']; ?>)">
                                            <i class="fas fa-check me-2"></i>
                                            Marcar como Disponible
                                        </button>
                                    <?php elseif ($habitacion['estado'] == 'ocupada' && $habitacion['fecha_salida']): ?>
                                        <button class="btn btn-warning w-100" onclick="marcarParaLimpieza(<?php echo $habitacion['id']; ?>)">
                                            <i class="fas fa-broom me-2"></i>
                                            Iniciar Limpieza
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary w-100" disabled>
                                            <i class="fas fa-info-circle me-2"></i>
                                            <?php echo $habitacion['estado'] == 'ocupada' ? 'Ocupada' : 'Disponible'; ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function toggleTask(element) {
            const icon = element.querySelector('i');
            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                element.style.textDecoration = 'line-through';
                element.style.opacity = '0.6';
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                element.style.textDecoration = 'none';
                element.style.opacity = '1';
            }
        }

        function marcarLimpia(habitacionId) {
            if (!confirm('¿Estás seguro de marcar esta habitación como disponible?')) {
                return;
            }

            $.ajax({
                url: 'api/endpoints/habitaciones.php',
                type: 'PUT',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({ 
                    id: habitacionId, 
                    estado: 'disponible' 
                }),
                success: function(response) {
                    showNotification('Habitación marcada como disponible', 'success');
                    setTimeout(() => location.reload(), 1500);
                },
                error: function(xhr) {
                    let msg = 'Error al actualizar habitación';
                    try {
                        const json = JSON.parse(xhr.responseText);
                        msg = json.message || json.error || xhr.responseText;
                    } catch (e) {
                        msg = xhr.responseText || msg;
                    }
                    showNotification(msg, 'error');
                }
            });
        }

        function marcarParaLimpieza(habitacionId) {
            if (!confirm('¿Estás seguro de iniciar la limpieza de esta habitación?')) {
                return;
            }

            $.ajax({
                url: 'api/endpoints/habitaciones.php',
                type: 'PUT',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({ 
                    id: habitacionId, 
                    estado: 'limpieza' 
                }),
                success: function(response) {
                    showNotification('Habitación marcada para limpieza', 'success');
                    setTimeout(() => location.reload(), 1500);
                },
                error: function(xhr) {
                    let msg = 'Error al actualizar habitación';
                    try {
                        const json = JSON.parse(xhr.responseText);
                        msg = json.message || json.error || xhr.responseText;
                    } catch (e) {
                        msg = xhr.responseText || msg;
                    }
                    showNotification(msg, 'error');
                }
            });
        }

        function showNotification(message, type) {
            const notification = $(`
                <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
            $('body').append(notification);
            
            setTimeout(() => {
                notification.alert('close');
            }, 5000);
        }
    </script>
</body>
</html>
