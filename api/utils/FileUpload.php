<?php
// Configuración para subida de archivos
class FileUpload {
    private $uploadDir;
    private $maxFileSize = 5242880; // 5MB
    private $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    public function __construct($uploadDir) {
        $this->uploadDir = $uploadDir;
        // Crear directorio si no existe
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    public function uploadFile($file, $prefix = '') {
        // Validar archivo
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Error al subir archivo'];
        }
        
        // Validar tamaño
        if ($file['size'] > $this->maxFileSize) {
            return ['success' => false, 'message' => 'El archivo es demasiado grande (máximo 5MB)'];
        }
        
        // Validar tipo
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);
        
        if (!in_array($extension, $this->allowedTypes)) {
            return ['success' => false, 'message' => 'Tipo de archivo no permitido (solo JPG, PNG, GIF, WEBP)'];
        }
        
        // Generar nombre único
        $fileName = $prefix . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;
        $filePath = $this->uploadDir . '/' . $fileName;
        
        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Intentar convertir a WebP si está disponible
            $webpPath = $this->convertToWebP($filePath);
            
            if ($webpPath && $webpPath !== $filePath) {
                // Usar la versión WebP
                $finalPath = $webpPath;
                $finalFileName = basename($webpPath);
                
                // Generar thumbnails de la versión WebP
                $thumbnailPath = $this->generateThumbnail($finalPath, 'webp');
                $convertedToWebP = true;
            } else {
                // Mantener original si no se puede convertir o WebP no está disponible
                $finalPath = $filePath;
                $finalFileName = $fileName;
                
                // Generar thumbnails del original
                $thumbnailPath = $this->generateThumbnail($finalPath, $extension);
                $convertedToWebP = false;
            }
            
            return [
                'success' => true,
                'fileName' => $finalFileName,
                'filePath' => $finalPath,
                'thumbnailPath' => $thumbnailPath,
                'url' => basename($this->uploadDir) . '/' . $finalFileName,
                'originalExtension' => $extension,
                'convertedToWebP' => $convertedToWebP
            ];
        } else {
            return ['success' => false, 'message' => 'Error al mover el archivo'];
        }
    }
    
    private function generateThumbnail($sourcePath, $extension) {
        $thumbnailPath = str_replace('.' . $extension, '_thumb.jpg', $sourcePath);
        
        try {
            // Verificar soporte para funciones de imagen
            if (!function_exists('imagecreatetruecolor') || !function_exists('imagecopyresampled')) {
                error_log('GD functions not available for thumbnail generation');
                return null;
            }
            
            // Crear thumbnail con GD
            if (in_array($extension, ['jpg', 'jpeg'])) {
                if (function_exists('imagecreatefromjpeg')) {
                    $source = imagecreatefromjpeg($sourcePath);
                } else {
                    error_log('imagecreatefromjpeg not available');
                    return null;
                }
            } elseif ($extension === 'png') {
                if (function_exists('imagecreatefrompng')) {
                    $source = imagecreatefrompng($sourcePath);
                } else {
                    error_log('imagecreatefrompng not available');
                    return null;
                }
            } elseif ($extension === 'gif') {
                if (function_exists('imagecreatefromgif')) {
                    $source = imagecreatefromgif($sourcePath);
                } else {
                    error_log('imagecreatefromgif not available');
                    return null;
                }
            } elseif ($extension === 'webp') {
                if (function_exists('imagecreatefromwebp')) {
                    $source = imagecreatefromwebp($sourcePath);
                } else {
                    error_log('imagecreatefromwebp not available, skipping thumbnail for WebP');
                    return null;
                }
            } else {
                // Para formatos no soportados, no crear thumbnail
                error_log("Unsupported format for thumbnail: $extension");
                return null;
            }
            
            if (!$source) {
                error_log('Failed to create image source for thumbnail');
                return null;
            }
            
            $width = imagesx($source);
            $height = imagesy($source);
            $thumbWidth = 300;
            $thumbHeight = 200;
            
            $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
            
            // Redimensionar
            imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
            
            // Guardar thumbnail como JPG para máxima compatibilidad
            if (function_exists('imagejpeg')) {
                imagejpeg($thumbnail, $thumbnailPath, 85);
            } else {
                error_log('imagejpeg not available for thumbnail');
                imagedestroy($source);
                imagedestroy($thumbnail);
                return null;
            }
            
            // Liberar memoria
            imagedestroy($source);
            imagedestroy($thumbnail);
            
            return $thumbnailPath;
        } catch (Exception $e) {
            error_log('Error generating thumbnail: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Convertir imagen a WebP para mayor eficiencia
     */
    public function convertToWebP($sourcePath, $quality = 85) {
        try {
            // Verificación completa de soporte WebP
            $webpSupported = function_exists('imagewebp') && 
                           function_exists('imagecreatefromjpeg') && 
                           function_exists('imagecreatefrompng') && 
                           function_exists('imagecreatefromgif');
            
            if (!$webpSupported) {
                error_log('WebP no está soportado en esta instalación de PHP (Apache context)');
                return $sourcePath; // Retornar ruta original
            }
            
            $fileInfo = pathinfo($sourcePath);
            $extension = strtolower($fileInfo['extension']);
            $webpPath = str_replace('.' . $extension, '.webp', $sourcePath);
            
            // Si ya es WebP, retornar la misma ruta
            if ($extension === 'webp') {
                return $webpPath;
            }
            
            // Crear imagen desde el formato original
            if (in_array($extension, ['jpg', 'jpeg'])) {
                $source = imagecreatefromjpeg($sourcePath);
            } elseif ($extension === 'png') {
                $source = imagecreatefrompng($sourcePath);
            } elseif ($extension === 'gif') {
                $source = imagecreatefromgif($sourcePath);
            } else {
                return $sourcePath; // Retornar original si no es soportado
            }
            
            if (!$source) {
                return $sourcePath;
            }
            
            // Convertir a WebP
            $width = imagesx($source);
            $height = imagesy($source);
            
            $webp = imagecreatetruecolor($width, $height);
            
            // Preservar transparencia para PNG
            if ($extension === 'png') {
                imagealphablending($webp, false);
                imagesavealpha($webp, true);
                imagecopyresampled($webp, $source, 0, 0, 0, 0, $width, $height, $width, $height);
            } else {
                imagecopyresampled($webp, $source, 0, 0, 0, 0, $width, $height, $width, $height);
            }
            
            // Guardar como WebP
            $result = imagewebp($webp, $webpPath, $quality);
            
            // Liberar memoria
            imagedestroy($source);
            imagedestroy($webp);
            
            if ($result) {
                // Eliminar imagen original para ahorrar espacio
                if (file_exists($sourcePath)) {
                    unlink($sourcePath);
                }
                error_log("Successfully converted to WebP: " . basename($webpPath));
                return $webpPath;
            }
            
            return $sourcePath;
        } catch (Exception $e) {
            error_log('Error converting to WebP: ' . $e->getMessage());
            return $sourcePath; // Retornar original si falla
        }
    }
}
?>
