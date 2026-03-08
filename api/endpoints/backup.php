<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../backend/config/database.php';
include_once '../../lib/BackupSystem.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['accion'])) {
            switch($_GET['accion']) {
                case 'listar':
                    $backup_system = new BackupSystem($database);
                    $backups = $backup_system->getBackupList();
                    
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'backups' => $backups,
                        'total' => count($backups)
                    ]);
                    break;
                    
                case 'crear':
                    $tipo = $_GET['tipo'] ?? 'full'; // full, database, files
                    
                    $backup_system = new BackupSystem($database);
                    $result = $backup_system->createBackup($tipo);
                    
                    if ($result['success']) {
                        http_response_code(200);
                        echo json_encode([
                            'success' => true,
                            'message' => 'Backup creado exitosamente',
                            'backup' => $result
                        ]);
                    } else {
                        http_response_code(500);
                        echo json_encode([
                            'success' => false,
                            'message' => 'Error al crear backup',
                            'error' => $result['error'] ?? 'Error desconocido'
                        ]);
                    }
                    break;
                    
                case 'descargar':
                    $backup_name = $_GET['backup'] ?? '';
                    
                    if (empty($backup_name)) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Nombre de backup no especificado']);
                        break;
                    }
                    
                    $backup_system = new BackupSystem($database);
                    $backups = $backup_system->getBackupList();
                    
                    $backup_encontrado = null;
                    foreach ($backups as $backup) {
                        if ($backup['name'] === $backup_name) {
                            $backup_encontrado = $backup;
                            break;
                        }
                    }
                    
                    if (!$backup_encontrado) {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'message' => 'Backup no encontrado']);
                        break;
                    }
                    
                    $backup_file = $backup_encontrado['path'];
                    
                    if (!file_exists($backup_file)) {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'message' => 'Archivo de backup no encontrado']);
                        break;
                    }
                    
                    // Determinar tipo de contenido
                    $file_info = pathinfo($backup_file);
                    $content_type = 'application/octet-stream';
                    
                    switch (strtolower($file_info['extension'])) {
                        case 'sql':
                            $content_type = 'text/sql';
                            break;
                        case 'zip':
                            $content_type = 'application/zip';
                            break;
                        case 'gz':
                            $content_type = 'application/gzip';
                            break;
                    }
                    
                    header('Content-Type: ' . $content_type);
                    header('Content-Disposition: attachment; filename="' . $backup_encontrado['name'] . '"');
                    header('Content-Length: ' . filesize($backup_file));
                    header('Cache-Control: max-age=0');
                    
                    readfile($backup_file);
                    exit;
                    break;
                    
                case 'restaurar':
                    $backup_name = $_GET['backup'] ?? '';
                    
                    if (empty($backup_name)) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Nombre de backup no especificado']);
                        break;
                    }
                    
                    $backup_system = new BackupSystem($database);
                    $backups = $backup_system->getBackupList();
                    
                    $backup_encontrado = null;
                    foreach ($backups as $backup) {
                        if ($backup['name'] === $backup_name) {
                            $backup_encontrado = $backup;
                            break;
                        }
                    }
                    
                    if (!$backup_encontrado) {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'message' => 'Backup no encontrado']);
                        break;
                    }
                    
                    $backup_file = $backup_encontrado['path'];
                    
                    if (!file_exists($backup_file)) {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'message' => 'Archivo de backup no encontrado']);
                        break;
                    }
                    
                    $result = $backup_system->restoreBackup($backup_file);
                    
                    if ($result['success']) {
                        http_response_code(200);
                        echo json_encode([
                            'success' => true,
                            'message' => 'Backup restaurado exitosamente',
                            'restored' => $backup_encontrado
                        ]);
                    } else {
                        http_response_code(500);
                        echo json_encode([
                            'success' => false,
                            'message' => 'Error al restaurar backup',
                            'error' => $result['error'] ?? 'Error desconocido'
                        ]);
                    }
                    break;
                    
                case 'eliminar':
                    $backup_name = $_GET['backup'] ?? '';
                    
                    if (empty($backup_name)) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Nombre de backup no especificado']);
                        break;
                    }
                    
                    $backup_system = new BackupSystem($database);
                    $result = $backup_system->deleteBackup($backup_name);
                    
                    if ($result) {
                        http_response_code(200);
                        echo json_encode([
                            'success' => true,
                            'message' => 'Backup eliminado exitosamente',
                            'deleted' => $backup_name
                        ]);
                    } else {
                        http_response_code(500);
                        echo json_encode([
                            'success' => false,
                            'message' => 'Error al eliminar backup',
                            'backup' => $backup_name
                        ]);
                    }
                    break;
                    
                case 'configuracion':
                    // Obtener configuración del sistema de backup
                    $backup_system = new BackupSystem($database);
                    
                    // Simular obtención de configuración (en implementación real sería desde BD)
                    $config = [
                        'auto_backup' => true,
                        'backup_interval' => 'daily',
                        'max_backups' => 30,
                        'compress_backups' => true,
                        'notification_email' => 'admin@hotel.com',
                        'next_backup' => date('Y-m-d H:i:s', time() + 24*60*60), // mañana
                        'last_backup' => date('Y-m-d H:i:s', time() - 2*60*60) // hace 2 horas
                    ];
                    
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'config' => $config
                    ]);
                    break;
                    
                case 'estadisticas':
                    $backup_system = new BackupSystem($database);
                    $backups = $backup_system->getBackupList();
                    
                    // Calcular estadísticas
                    $stats = [
                        'total_backups' => count($backups),
                        'total_size' => array_sum(array_column($backups, 'size')),
                        'tipos' => array_count_values(array_column($backups, 'type')),
                        'ultimo_backup' => !empty($backups) ? $backups[0]['created_at'] : null,
                        'backup_mas_grande' => !empty($backups) ? max(array_column($backups, 'size')) : 0,
                        'backup_mas_pequeno' => !empty($backups) ? min(array_column($backups, 'size')) : 0,
                        'promedio_size' => !empty($backups) ? round(array_sum(array_column($backups, 'size')) / count($backups)) : 0
                    ];
                    
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'estadisticas' => $stats
                    ]);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(array("message" => "Acción no válida"));
                    break;
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Acción no especificada"));
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if(isset($data['accion'])) {
            switch($data['accion']) {
                case 'programar':
                    $backup_system = new BackupSystem($database);
                    $result = $backup_system->scheduleAutoBackup();
                    
                    if ($result) {
                        http_response_code(200);
                        echo json_encode([
                            'success' => true,
                            'message' => 'Backup programado y ejecutado exitosamente'
                        ]);
                    } else {
                        http_response_code(200);
                        echo json_encode([
                            'success' => true,
                            'message' => 'No es necesario ejecutar backup en este momento'
                        ]);
                    }
                    break;
                    
                case 'actualizar_config':
                    // Actualizar configuración (simulado)
                    $config = $data['config'] ?? [];
                    
                    // Validar configuración
                    $required_fields = ['auto_backup', 'backup_interval', 'max_backups'];
                    foreach ($required_fields as $field) {
                        if (!isset($config[$field])) {
                            http_response_code(400);
                            echo json_encode([
                                'success' => false,
                                'message' => "Campo requerido: {$field}"
                            ]);
                            break;
                        }
                    }
                    
                    // En implementación real, guardar en BD
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Configuración actualizada exitosamente',
                        'config' => $config
                    ]);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(array("message" => "Acción no válida"));
                    break;
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Acción no especificada"));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Método no permitido"));
        break;
}
?>
