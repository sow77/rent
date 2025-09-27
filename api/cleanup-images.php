<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/FileUpload.php';

header('Content-Type: application/json');

try {
    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['entity_type']) || empty($input['images'])) {
        throw new Exception('Datos requeridos faltantes');
    }
    
    $entityType = $input['entity_type']; // vehicle, boat, transfer
    $images = $input['images']; // Array de URLs de imágenes
    
    if (!is_array($images)) {
        $images = json_decode($images, true) ?: [];
    }
    
    $fileUpload = new FileUpload();
    $deletedCount = 0;
    $errors = [];
    
    foreach ($images as $imageUrl) {
        try {
            // Solo eliminar archivos locales, no URLs externas
            if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                $filename = basename($imageUrl);
                $subdirectory = $entityType . 's'; // vehicles, boats, transfers
                
                if ($fileUpload->deleteFile($filename, $subdirectory)) {
                    $deletedCount++;
                }
            }
        } catch (Exception $e) {
            $errors[] = "Error eliminando {$imageUrl}: " . $e->getMessage();
        }
    }
    
    echo json_encode([
        'success' => true,
        'deleted_count' => $deletedCount,
        'errors' => $errors
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
