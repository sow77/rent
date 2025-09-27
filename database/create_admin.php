<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';

try {
    $user = new User();
    
    // Verificar si ya existe un admin
    $adminExists = $user->emailExists('admin@devrent.com');
    
    if (!$adminExists) {
        // Crear usuario administrador
        $result = $user->register(
            'Administrador',
            'admin@devrent.com',
            'admin123' // Cambiar esto en producción
        );
        
        if ($result) {
            // Actualizar el rol a admin
            $db = Database::getInstance();
            $stmt = $db->prepare("UPDATE users SET role = 'admin' WHERE email = ?");
            $stmt->execute(['admin@devrent.com']);
            
            echo "Usuario administrador creado exitosamente.\n";
            echo "Email: admin@devrent.com\n";
            echo "Contraseña: admin123\n";
        } else {
            echo "Error al crear el usuario administrador.\n";
        }
    } else {
        echo "El usuario administrador ya existe.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 