<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require __DIR__ . '/../../config/'database.php';

// Verificar sesión de usuario
// // session_start(); // Ya iniciada en router; // Ya iniciada en router
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Verificar rol permitido para backups
$allowed_roles = ['admin', 'Contador', 'Auxiliar Contable'];
if (!in_array($_SESSION['usuario']['rol'], $allowed_roles)) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

header("Content-Type: application/json; charset=UTF-8");;

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Crear tabla de backups si no existe
    $db->exec("CREATE TABLE IF NOT EXISTS backups (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        filepath VARCHAR(500) NOT NULL,
        size BIGINT NOT NULL,
        status ENUM('pending', 'completed', 'failed', 'restoring') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        completed_at TIMESTAMP NULL,
        created_by VARCHAR(100),
        notes TEXT
    )");
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'list':
            listBackups($db);
            break;
            
        case 'stats':
            getStats($db);
            break;
            
        case 'create':
            createBackup($db);
            break;
            
        case 'download':
            downloadBackup($db);
            break;
            
        case 'restore':
            restoreBackup($db);
            break;
            
        case 'delete':
            deleteBackup($db);
            break;
            
        default:
            echo json_encode(['error' => 'Acción no válida']);
            http_response_code(400);
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
    http_response_code(500);
}

function listBackups($db) {
    try {
        $stmt = $db->query("SELECT * FROM backups ORDER BY created_at DESC");
        $backups = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $backups[] = [
                'id' => $row['id'],
                'filename' => $row['filename'],
                'size' => formatBytes($row['size']),
                'status' => $row['status'],
                'status_text' => getStatusText($row['status']),
                'created_at' => date('d/m/Y H:i', strtotime($row['created_at'])),
                'created_by' => $row['created_by']
            ];
        }
        
        echo json_encode(['backups' => $backups]);
        
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
        http_response_code(500);
    }
}

function getStats($db) {
    try {
        // Total de backups
        $stmt = $db->query("SELECT COUNT(*) as total FROM backups");
        $total = $stmt->fetch()['total'];
        
        // Último backup
        $stmt = $db->query("SELECT created_at FROM backups ORDER BY created_at DESC LIMIT 1");
        $lastBackup = $stmt->fetch();
        $lastBackupText = $lastBackup ? date('d/m H:i', strtotime($lastBackup['created_at'])) : '-';
        
        // Espacio total usado
        $stmt = $db->query("SELECT SUM(size) as total_size FROM backups");
        $totalSize = $stmt->fetch()['total_size'] ?? 0;
        $totalSizeText = formatBytes($totalSize);
        
        // Próximo backup (simulado - en producción sería de la configuración)
        $nextBackup = date('d/m H:i', strtotime('+1 day'));
        
        echo json_encode([
            'total' => $total,
            'last_backup' => $lastBackupText,
            'total_size' => $totalSizeText,
            'next_backup' => $nextBackup
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
        http_response_code(500);
    }
}

function createBackup($db) {
    try {
        $backupDir = __DIR__ . '/../../backups/';
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $backupDir . $filename;
        
        // Crear backup de la base de datos
        $database = new Database();
        $result = $database->backup($filepath);
        
        if ($result) {
            $fileSize = filesize($filepath);
            
            // Registrar en la base de datos
            $stmt = $db->prepare("INSERT INTO backups (filename, filepath, size, status, created_by) 
                                VALUES (:filename, :filepath, :size, 'completed', :created_by)");
            $stmt->bindParam(':filename', $filename);
            $stmt->bindParam(':filepath', $filepath);
            $stmt->bindParam(':size', $fileSize);
            $stmt->bindParam(':created_by', $_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellido']);
            $stmt->execute();
            
            echo json_encode([
                'success' => true,
                'message' => 'Backup creado correctamente',
                'filename' => $filename,
                'size' => formatBytes($fileSize)
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al crear el backup'
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
        http_response_code(500);
    }
}

function downloadBackup($db) {
    try {
        $backupId = $_GET['id'] ?? '';
        
        if (empty($backupId)) {
            echo json_encode(['error' => 'ID de backup no proporcionado']);
            http_response_code(400);
            return;
        }
        
        $stmt = $db->prepare("SELECT * FROM backups WHERE id = :id");
        $stmt->bindParam(':id', $backupId);
        $stmt->execute();
        
        $backup = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$backup) {
            echo json_encode(['error' => 'Backup no encontrado']);
            http_response_code(404);
            return;
        }
        
        if (!file_exists($backup['filepath'])) {
            echo json_encode(['error' => 'Archivo de backup no encontrado']);
            http_response_code(404);
            return;
        }
        
        // Enviar archivo para descarga
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $backup['filename'] . '"');
        header('Content-Length: ' . filesize($backup['filepath']));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        readfile($backup['filepath']);
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
        http_response_code(500);
    }
}

function restoreBackup($db) {
    try {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        $backupId = $data['backup_id'] ?? '';
        
        if (empty($backupId)) {
            echo json_encode(['error' => 'ID de backup no proporcionado']);
            http_response_code(400);
            return;
        }
        
        $stmt = $db->prepare("SELECT * FROM backups WHERE id = :id");
        $stmt->bindParam(':id', $backupId);
        $stmt->execute();
        
        $backup = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$backup) {
            echo json_encode(['error' => 'Backup no encontrado']);
            http_response_code(404);
            return;
        }
        
        if (!file_exists($backup['filepath'])) {
            echo json_encode(['error' => 'Archivo de backup no encontrado']);
            http_response_code(404);
            return;
        }
        
        // Actualizar estado a restaurando
        $stmt = $db->prepare("UPDATE backups SET status = 'restoring' WHERE id = :id");
        $stmt->bindParam(':id', $backupId);
        $stmt->execute();
        
        // Restaurar backup
        $database = new Database();
        $result = $database->restore($backup['filepath']);
        
        if ($result) {
            // Actualizar estado a completado
            $stmt = $db->prepare("UPDATE backups SET status = 'completed', completed_at = NOW() WHERE id = :id");
            $stmt->bindParam(':id', $backupId);
            $stmt->execute();
            
            echo json_encode([
                'success' => true,
                'message' => 'Backup restaurado correctamente'
            ]);
        } else {
            // Actualizar estado a fallido
            $stmt = $db->prepare("UPDATE backups SET status = 'failed' WHERE id = :id");
            $stmt->bindParam(':id', $backupId);
            $stmt->execute();
            
            echo json_encode([
                'success' => false,
                'message' => 'Error al restaurar el backup'
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
        http_response_code(500);
    }
}

function deleteBackup($db) {
    try {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        $backupId = $data['backup_id'] ?? '';
        
        if (empty($backupId)) {
            echo json_encode(['error' => 'ID de backup no proporcionado']);
            http_response_code(400);
            return;
        }
        
        $stmt = $db->prepare("SELECT * FROM backups WHERE id = :id");
        $stmt->bindParam(':id', $backupId);
        $stmt->execute();
        
        $backup = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$backup) {
            echo json_encode(['error' => 'Backup no encontrado']);
            http_response_code(404);
            return;
        }
        
        // Eliminar archivo físico
        if (file_exists($backup['filepath'])) {
            unlink($backup['filepath']);
        }
        
        // Eliminar registro de la base de datos
        $stmt = $db->prepare("DELETE FROM backups WHERE id = :id");
        $stmt->bindParam(':id', $backupId);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Backup eliminado correctamente'
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
        http_response_code(500);
    }
}

// Funciones auxiliares
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

function getStatusText($status) {
    $statusTexts = [
        'pending' => 'Pendiente',
        'completed' => 'Completado',
        'failed' => 'Fallido',
        'restoring' => 'Restaurando'
    ];
    
    return $statusTexts[$status] ?? $status;
}
?>
