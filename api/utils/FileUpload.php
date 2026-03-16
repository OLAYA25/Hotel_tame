<?php
// Configuración para subida de archivos optimizado
class FileUpload {
    private $uploadDir;
    private $maxFileSize = 5242880; // 5MB
    private $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $maxWidth = 1200; // Máximo 1200px de ancho
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
            error_log("=== INICIO UPLOAD ===");
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
            // Crear imagen desde el formato original
            $source = null;
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
                        error_log("WebP reading not supported, trying fallback");
                        // Fallback: intentar con imagecreatefromstring
                        $webpData = file_get_contents($sourcePath);
                        if ($webpData !== false) {
                            $source = imagecreatefromstring($webpData);
                        } else {
                            return ['success' => false, 'message' => 'No se puede leer el archivo WebP'];
                        }
                    }
                    break;
                default:
                    return ['success' => false, 'message' => 'Formato no soportado'];
            }
            
            if (!$source) {
                return ['success' => false, 'message' => 'No se pudo cargar la imagen'];
            }
            
            // Obtener dimensiones originales
            $originalWidth = imagesx($source);
            $originalHeight = imagesy($source);
            
            // Calcular nuevas dimensiones (mantener proporción, máximo 1200px de ancho)
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
            
            if ($originalWidth > $this->maxWidth) {
                $ratio = $this->maxWidth / $originalWidth;
                $newWidth = $this->maxWidth;
                $newHeight = round($originalHeight * $ratio);
            }
            
            // Crear lienzo para la imagen optimizada
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
                error_log("IMAGEN OPTIMIZADA - Original: " . filesize($sourcePath) . " bytes, Final: $finalSize bytes, Compresión: $compressionRatio%");
                error_log("DIMENSIONES - Original: $originalWidth" . "x$originalHeight, Final: $newWidth" . "x$newHeight");
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
     * Generar thumbnail (versión compatible para fallback)
     */
    public function generateThumbnail($sourcePath, $extension) {
        $thumbnailPath = str_replace('.webp', '_thumb.jpg', $sourcePath);
        
        try {
            if (!function_exists('imagecreatetruecolor') || !function_exists('imagecopyresampled')) {
                return null;
            }
            
            // Crear thumbnail desde WebP
            if (function_exists('imagecreatefromwebp')) {
                $source = imagecreatefromwebp($sourcePath);
            } else {
                error_log('imagecreatefromwebp not available, skipping thumbnail for WebP');
                return null;
            }
            
            if (!$source) {
                return null;
            }
            
            $width = imagesx($source);
            $height = imagesy($source);
            $thumbWidth = 300;
            $thumbHeight = 200;
            
            $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
            imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
            
            // Guardar thumbnail como JPG para máxima compatibilidad
            if (function_exists('imagejpeg')) {
                imagejpeg($thumbnail, $thumbnailPath, 85);
            } else {
                imagedestroy($source);
                imagedestroy($thumbnail);
                return null;
            }
            
            imagedestroy($source);
            imagedestroy($thumbnail);
            
            return $thumbnailPath;
        } catch (Exception $e) {
            error_log('Error generating thumbnail: ' . $e->getMessage());
            return null;
        }
    }
}
?>
