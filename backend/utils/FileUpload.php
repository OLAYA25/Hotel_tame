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
            // Generar thumbnails
            $thumbnailPath = $this->generateThumbnail($filePath, $extension);
            
            return [
                'success' => true,
                'fileName' => $fileName,
                'filePath' => $filePath,
                'thumbnailPath' => $thumbnailPath,
                'url' => basename($this->uploadDir) . '/' . $fileName
            ];
        } else {
            return ['success' => false, 'message' => 'Error al mover el archivo'];
        }
    }
    
    private function generateThumbnail($sourcePath, $extension) {
        $thumbnailPath = str_replace('.' . $extension, '_thumb.jpg', $sourcePath);
        
        try {
            // Crear thumbnail con GD
            if (in_array($extension, ['jpg', 'jpeg'])) {
                $source = imagecreatefromjpeg($sourcePath);
            } elseif ($extension === 'png') {
                $source = imagecreatefrompng($sourcePath);
            } elseif ($extension === 'gif') {
                $source = imagecreatefromgif($sourcePath);
            } elseif ($extension === 'webp') {
                $source = imagecreatefromwebp($sourcePath);
            } else {
                // Para formatos no soportados, no crear thumbnail
                $source = null;
            }
            
            if ($source) {
                $width = imagesx($source);
                $height = imagesy($source);
                $thumbWidth = 300;
                $thumbHeight = 200;
                
                $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
                
                // Redimensionar
                imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
                
                // Guardar thumbnail
                imagejpeg($thumbnail, $thumbnailPath, 85);
                
                // Liberar memoria
                imagedestroy($source);
                imagedestroy($thumbnail);
                
                return $thumbnailPath;
            }
        } catch (Exception $e) {
            error_log('Error generating thumbnail: ' . $e->getMessage());
            return null;
        }
    }
}
?>
