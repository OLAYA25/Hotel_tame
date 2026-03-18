<?php
// Configuración para subida de archivos de habitaciones optimizado
class RoomFileUpload {
    private $uploadDir;
    private $maxFileSize = 5242880; // 5MB
    private $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $maxWidth = 1600; // Máximo 1600px de ancho para habitaciones
    private $webpQuality = 80; // Calidad WebP
    private $jpegQuality = 85; // Calidad JPEG para fallback
    
    public function __construct($uploadDir) {
        $this->uploadDir = $uploadDir;
        // Crear directorio si no existe
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    public function uploadFile($file, $prefix = '') {
        try {
            error_log("=== INICIO UPLOAD HABITACIÓN ===");
            error_log("File data: " . print_r($file, true));
            
            // Validar archivo
            if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                error_log("ERROR: Archivo no válido o no subido");
                return ['success' => false, 'message' => 'Archivo no válido'];
            }
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                error_log("ERROR: Upload error code: " . $file['error']);
                return ['success' => false, 'message' => 'Error al subir archivo'];
            }
            
            if ($file['size'] > $this->maxFileSize) {
                error_log("ERROR: Archivo demasiado grande: " . $file['size'] . " > " . $this->maxFileSize);
                return ['success' => false, 'message' => 'Archivo demasiado grande'];
            }
            
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $this->allowedTypes)) {
                error_log("ERROR: Tipo no permitido: " . $extension);
                return ['success' => false, 'message' => 'Tipo de archivo no permitido'];
            }
            
            error_log("Validación exitosa, procesando imagen...");
            
            // Generar nombre único SIEMPRE como WebP
            $fileName = $prefix . '_' . time() . '_' . mt_rand(1000, 9999) . '.webp';
            $filePath = $this->uploadDir . '/' . $fileName;
            
            error_log("Nombre generado: " . $fileName);
            error_log("Ruta destino: " . $filePath);
            
            // Procesar y optimizar imagen
            $result = $this->processAndOptimizeImage($file['tmp_name'], $filePath, $extension);
            
            error_log("Resultado procesamiento: " . print_r($result, true));
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'filename' => $result['filename'],
                    'filePath' => $result['path'] ?? '',
                    'url' => basename($this->uploadDir) . '/' . $result['filename'],
                    'originalExtension' => $extension,
                    'convertedToWebP' => ($result['format'] ?? 'jpg') === 'webp',
                    'optimized' => true,
                    'fileSize' => $result['final_size'] ?? 0,
                    'compressionRatio' => $result['compression_ratio'] ?? 0,
                    'format' => $result['format'] ?? 'jpg',
                    'final_dimensions' => $result['final_dimensions'] ?? ['width' => 0, 'height' => 0]
                ];
            } else {
                error_log("ERROR en procesamiento: " . ($result['message'] ?? 'Unknown error'));
                return ['success' => false, 'message' => $result['message']];
            }
        } catch (Exception $e) {
            error_log('Error en uploadFile: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al procesar la imagen'];
        }
    }
    
    private function processAndOptimizeImage($sourcePath, $targetPath, $originalExtension) {
        try {
            // Verificar si hay soporte para procesamiento de imágenes
            if (!function_exists('imagecreatetruecolor')) {
                error_log("GD no disponible, copiando archivo original");
                // Sin GD, simplemente copiar el archivo
                $finalFileName = basename($targetPath);
                $finalPath = $this->uploadDir . '/' . $finalFileName;
                
                // Cambiar extensión a jpg si es webp y no hay soporte
                if ($originalExtension === 'webp') {
                    $finalFileName = preg_replace('/\.webp$/', '.jpg', $finalFileName);
                    $finalPath = $this->uploadDir . '/' . $finalFileName;
                }
                
                if (copy($sourcePath, $finalPath)) {
                    $fileSize = filesize($finalPath);
                    return [
                        'success' => true,
                        'filename' => $finalFileName,
                        'path' => $finalPath,
                        'format' => $originalExtension === 'webp' ? 'jpg' : $originalExtension,
                        'final_size' => $fileSize,
                        'compression_ratio' => 0,
                        'final_dimensions' => ['width' => 0, 'height' => 0],
                        'message' => 'Archivo copiado sin procesamiento (GD no disponible)'
                    ];
                } else {
                    return ['success' => false, 'message' => 'No se pudo copiar el archivo'];
                }
            }
            
            // Obtener dimensiones originales
            $imageInfo = getimagesize($sourcePath);
            if (!$imageInfo) {
                return ['success' => false, 'message' => 'No se pudo obtener información de la imagen'];
            }
            
            $originalWidth = $imageInfo[0];
            $originalHeight = $imageInfo[1];
            
            // Calcular nuevas dimensiones (mantener proporción, máximo 1600px de ancho)
            if ($originalWidth > $this->maxWidth) {
                $ratio = $this->maxWidth / $originalWidth;
                $newWidth = $this->maxWidth;
                $newHeight = (int)($originalHeight * $ratio);
            } else {
                $newWidth = $originalWidth;
                $newHeight = $originalHeight;
            }
            
            // Crear imagen desde archivo según tipo
            switch ($originalExtension) {
                case 'jpg':
                case 'jpeg':
                    $source = imagecreatefromjpeg($sourcePath);
                    break;
                case 'png':
                    $source = imagecreatefrompng($sourcePath);
                    break;
                case 'gif':
                    $source = imagecreatefromgif($sourcePath);
                    break;
                case 'webp':
                    if (function_exists('imagecreatefromwebp')) {
                        $source = imagecreatefromwebp($sourcePath);
                    } else {
                        error_log("WebP no soportado, copiando archivo sin procesar");
                        // Sin soporte WebP, copiar archivo original con extensión cambiada
                        $finalFileName = preg_replace('/\.webp$/', '.jpg', basename($targetPath));
                        $finalPath = $this->uploadDir . '/' . $finalFileName;
                        
                        if (copy($sourcePath, $finalPath)) {
                            $fileSize = filesize($finalPath);
                            return [
                                'success' => true,
                                'filename' => $finalFileName,
                                'path' => $finalPath,
                                'format' => 'jpg',
                                'final_size' => $fileSize,
                                'compression_ratio' => 0,
                                'final_dimensions' => ['width' => 0, 'height' => 0],
                                'message' => 'Imagen WebP copiada como JPG (sin procesamiento)'
                            ];
                        } else {
                            return ['success' => false, 'message' => 'No se pudo copiar el archivo WebP'];
                        }
                    }
                    break;
                default:
                    return ['success' => false, 'message' => 'Formato no soportado'];
            }
            
            if (!$source) {
                return ['success' => false, 'message' => 'No se pudo cargar la imagen'];
            }
            
            // Crear imagen optimizada con dimensiones nuevas
            $optimized = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preservar transparencia para PNG
            if ($originalExtension === 'png') {
                imagealphablending($optimized, false);
                imagesavealpha($optimized, true);
            }
            
            // Redimensionar imagen
            imagecopyresampled($optimized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
            
            // Verificar soporte WebP y guardar imagen optimizada
            $result = false;
            $targetExtension = 'webp';
            
            if (function_exists('imagewebp')) {
                // Guardar como WebP
                $result = imagewebp($optimized, $targetPath, $this->webpQuality);
                error_log("Imagen guardada como WebP: " . ($result ? "EXITOSO" : "FALLÓ"));
            } else {
                // Fallback: guardar como JPEG
                $targetExtension = 'jpg';
                $targetPath = preg_replace('/\.[^.]+$/', '.jpg', $targetPath);
                $result = imagejpeg($optimized, $targetPath, $this->jpegQuality);
                error_log("WebP no disponible, guardando como JPEG: " . ($result ? "EXITOSO" : "FALLÓ"));
            }
            
            if ($result) {
                // Obtener tamaño final
                $finalSize = filesize($targetPath);
                
                // Log de optimización
                $compressionRatio = round((1 - $finalSize / filesize($sourcePath)) * 100, 2);
                error_log("IMAGEN HABITACIÓN OPTIMIZADA - Original: " . filesize($sourcePath) . " bytes, Final: $finalSize bytes, Compresión: $compressionRatio%");
                error_log("DIMENSIONES - Original: {$originalWidth}x{$originalHeight}, Final: {$newWidth}x{$newHeight}");
                error_log("FORMATO FINAL: $targetExtension");
                
                // Limpiar memoria
                imagedestroy($source);
                imagedestroy($optimized);
                
                return [
                    'success' => true,
                    'filename' => basename($targetPath),
                    'path' => $targetPath,
                    'original_size' => filesize($sourcePath),
                    'final_size' => $finalSize,
                    'compression_ratio' => $compressionRatio,
                    'original_dimensions' => ['width' => $originalWidth, 'height' => $originalHeight],
                    'final_dimensions' => ['width' => $newWidth, 'height' => $newHeight],
                    'format' => $targetExtension
                ];
            } else {
                error_log("ERROR: No se pudo guardar la imagen optimizada");
                imagedestroy($source);
                imagedestroy($optimized);
                return ['success' => false, 'message' => 'No se pudo guardar la imagen optimizada'];
            }
        } catch (Exception $e) {
            error_log('Error en processAndOptimizeImage: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al procesar la imagen'];
        }
    }
    
    /**
     * Eliminar imagen anterior al actualizar
     */
    public function deletePreviousImage($imageUrl) {
        if (!empty($imageUrl)) {
            $filePath = '../../' . $imageUrl;
            if (file_exists($filePath)) {
                unlink($filePath);
                error_log("Imagen anterior eliminada: " . $filePath);
            }
        }
    }
}
?>
