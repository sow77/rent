<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/FileUpload.php';

header('Content-Type: application/json');

try {
    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }
    
    // Verificar que se haya subido un archivo
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No se recibió ningún archivo válido');
    }
    
    // Obtener el tipo de entidad (vehicle, boat, transfer, avatar)
    $type = $_POST['type'] ?? 'general';
    
    // Crear subdirectorio según el tipo
    $subdirectory = '';
    switch ($type) {
        case 'vehicle':
            $subdirectory = 'vehicles';
            break;
        case 'boat':
            $subdirectory = 'boats';
            break;
        case 'transfer':
            $subdirectory = 'transfers';
            break;
        case 'avatar':
            $subdirectory = 'avatars';
            break;
        default:
            $subdirectory = 'general';
    }
    
    // Inicializar FileUpload
    $fileUpload = new FileUpload();
    
    // Subir imagen
    $result = $fileUpload->uploadImage($_FILES['image'], $subdirectory);
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'url' => $result['url'],
            'filename' => $result['filename'],
            'filepath' => $result['filepath']
        ]);
    } else {
        throw new Exception($result['message']);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
