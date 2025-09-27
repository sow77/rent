<?php
// config/FileUpload.php

class FileUpload {
    
    private $uploadDir;
    private $allowedTypes;
    private $maxSize;
    
    public function __construct($uploadDir = null) {
        if ($uploadDir === null) {
            // Determinar la ruta correcta basada en la ubicación del script
            $scriptDir = dirname(__FILE__);
            $projectRoot = dirname($scriptDir);
            $this->uploadDir = $projectRoot . '/public/images/';
        } else {
            $this->uploadDir = $uploadDir;
        }
        $this->allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $this->maxSize = 5 * 1024 * 1024; // 5MB
    }
    
    /**
     * Subir archivo de imagen
     */
    public function uploadImage($file, $subdirectory = '') {
        try {
            // Validar archivo
            if (!$this->validateFile($file)) {
                return ['success' => false, 'message' => 'Archivo no válido'];
            }
            
            // Crear directorio si no existe
            $uploadPath = $this->uploadDir . $subdirectory;
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            // Generar nombre único
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $filepath = $uploadPath . '/' . $filename;
            
            // Mover archivo
            if (is_uploaded_file($file['tmp_name'])) {
                $moved = move_uploaded_file($file['tmp_name'], $filepath);
            } else {
                // Para archivos de prueba, usar copy
                $moved = copy($file['tmp_name'], $filepath);
            }
            
            if ($moved) {
                return [
                    'success' => true,
                    'filename' => $filename,
                    'filepath' => $filepath,
                    'url' => APP_URL . '/public/images/' . $subdirectory . '/' . $filename
                ];
            } else {
                return ['success' => false, 'message' => 'Error al mover el archivo'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Validar archivo
     */
    private function validateFile($file) {
        // Verificar si se subió un archivo
        if (!isset($file['tmp_name']) || (!is_uploaded_file($file['tmp_name']) && !file_exists($file['tmp_name']))) {
            return false;
        }
        
        // Verificar tamaño
        if ($file['size'] > $this->maxSize) {
            return false;
        }
        
        // Verificar tipo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedTypes)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Eliminar archivo
     */
    public function deleteFile($filename, $subdirectory = '') {
        $filepath = $this->uploadDir . $subdirectory . '/' . $filename;
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }
    
    /**
     * Obtener URL completa del archivo
     */
    public function getFileUrl($filename, $subdirectory = '') {
        return APP_URL . '/' . $this->uploadDir . $subdirectory . '/' . $filename;
    }
    
    /**
     * Obtener directorio de subida
     */
    public function getUploadDir() {
        return $this->uploadDir;
    }
    
    /**
     * Obtener tipos permitidos
     */
    public function getAllowedTypes() {
        return $this->allowedTypes;
    }
    
    /**
     * Obtener tamaño máximo
     */
    public function getMaxSize() {
        return $this->maxSize;
    }
}
?>
