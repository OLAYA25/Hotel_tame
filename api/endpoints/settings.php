<?php
require_once '../../config/database.php';

// Verificar sesión de usuario
session_start();
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Solo admin puede acceder a configuración
if ($_SESSION['usuario']['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Crear tabla de configuración si no existe
    $db->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'export':
            exportSettings($db);
            break;
            
        case 'import':
            importSettings($db);
            break;
            
        case 'reset':
            resetSettings($db);
            break;
            
        case 'clear_cache':
            clearCache();
            break;
            
        case 'test':
            testSettings($db);
            break;
            
        default:
            echo json_encode(['error' => 'Acción no válida']);
            http_response_code(400);
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
    http_response_code(500);
}

function exportSettings($db) {
    try {
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        // Convertir valores booleanos
        foreach ($settings as $key => $value) {
            if ($value === 'true' || $value === 'false') {
                $settings[$key] = $value === 'true';
            }
        }
        
        $exportData = [
            'settings' => $settings,
            'exported_at' => date('Y-m-d H:i:s'),
            'exported_by' => $_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellido'],
            'version' => '1.0'
        ];
        
        echo json_encode($exportData);
        
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
        http_response_code(500);
    }
}

function importSettings($db) {
    try {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data || !isset($data['settings'])) {
            echo json_encode(['error' => 'Formato de configuración inválido']);
            http_response_code(400);
            return;
        }
        
        $db->beginTransaction();
        
        foreach ($data['settings'] as $key => $value) {
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) 
                                VALUES (:key, :value) 
                                ON DUPLICATE KEY UPDATE setting_value = :value");
            $stmt->bindParam(':key', $key);
            $stmt->bindParam(':value', $value);
            $stmt->execute();
        }
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Configuración importada correctamente'
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['error' => $e->getMessage()]);
        http_response_code(500);
    }
}

function resetSettings($db) {
    try {
        $defaultSettings = [
            'hotel_name' => 'Hotel Tame',
            'hotel_address' => 'Calle Principal #123',
            'hotel_phone' => '+57 1 234 5678',
            'hotel_email' => 'info@hoteltame.com',
            'currency' => 'COP',
            'timezone' => 'America/Bogota',
            'language' => 'es',
            'checkin_time' => '15:00',
            'checkout_time' => '12:00',
            'max_guests_per_room' => '4',
            'auto_backup' => 'true',
            'backup_frequency' => 'daily',
            'notification_email' => 'true',
            'maintenance_mode' => 'false'
        ];
        
        $db->beginTransaction();
        
        foreach ($defaultSettings as $key => $value) {
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) 
                                VALUES (:key, :value) 
                                ON DUPLICATE KEY UPDATE setting_value = :value");
            $stmt->bindParam(':key', $key);
            $stmt->bindParam(':value', $value);
            $stmt->execute();
        }
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Configuración restaurada a valores por defecto'
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['error' => $e->getMessage()]);
        http_response_code(500);
    }
}

function clearCache() {
    try {
        // Limpiar caché de sesión
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        
        // Limpiar caché de archivos temporales
        $cacheDir = '../../cache/';
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        
        // Limpiar caché de templates si existe
        $templateCacheDir = '../../templates/cache/';
        if (is_dir($templateCacheDir)) {
            $files = glob($templateCacheDir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Caché del sistema limpiada correctamente'
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
        http_response_code(500);
    }
}

function testSettings($db) {
    try {
        $json = file_get_contents('php://input');
        $settings = json_decode($json, true);
        
        if (!$settings) {
            echo json_encode(['error' => 'No se recibieron datos de configuración']);
            http_response_code(400);
            return;
        }
        
        $errors = [];
        
        // Validar configuración requerida
        if (empty($settings['hotel_name'])) {
            $errors[] = 'El nombre del hotel es obligatorio';
        }
        
        // Validar email si está presente
        if (!empty($settings['hotel_email'])) {
            if (!filter_var($settings['hotel_email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'El email del hotel no es válido';
            }
        }
        
        // Validar formato de hora
        if (!empty($settings['checkin_time'])) {
            if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $settings['checkin_time'])) {
                $errors[] = 'El formato de hora de check-in no es válido (HH:MM)';
            }
        }
        
        if (!empty($settings['checkout_time'])) {
            if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $settings['checkout_time'])) {
                $errors[] = 'El formato de hora de check-out no es válido (HH:MM)';
            }
        }
        
        // Validar número máximo de huéspedes
        if (!empty($settings['max_guests_per_room'])) {
            $maxGuests = (int)$settings['max_guests_per_room'];
            if ($maxGuests < 1 || $maxGuests > 10) {
                $errors[] = 'El máximo de huéspedes por habitación debe estar entre 1 y 10';
            }
        }
        
        // Validar moneda
        $validCurrencies = ['COP', 'USD', 'EUR'];
        if (!empty($settings['currency']) && !in_array($settings['currency'], $validCurrencies)) {
            $errors[] = 'La moneda seleccionada no es válida';
        }
        
        // Validar zona horaria
        $validTimezones = ['America/Bogota', 'America/Mexico_City', 'America/Argentina/Buenos_Aires', 'Europe/Madrid'];
        if (!empty($settings['timezone']) && !in_array($settings['timezone'], $validTimezones)) {
            $errors[] = 'La zona horaria seleccionada no es válida';
        }
        
        // Validar idioma
        $validLanguages = ['es', 'en', 'pt'];
        if (!empty($settings['language']) && !in_array($settings['language'], $validLanguages)) {
            $errors[] = 'El idioma seleccionado no es válido';
        }
        
        // Validar frecuencia de backup
        $validFrequencies = ['daily', 'weekly', 'monthly'];
        if (!empty($settings['backup_frequency']) && !in_array($settings['backup_frequency'], $validFrequencies)) {
            $errors[] = 'La frecuencia de backup no es válida';
        }
        
        if (empty($errors)) {
            echo json_encode([
                'success' => true,
                'message' => 'Configuración validada correctamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'errors' => $errors
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
        http_response_code(500);
    }
}
?>
