<?php
require_once __DIR__ . '/../config/Auth.php';

// Función base_url si no está definida
if (!function_exists('base_url')) {
    function base_url($path = '') {
        $base_url = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
        $base_url .= $_SERVER['HTTP_HOST'];
        $base_url .= str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
        
        return $base_url . '/' . ltrim($path, '/');
    }
}

class AuthMiddleware {
    public function handle() {
        // Verificar si la sesión ha expirado
        if (Auth::isSessionExpired()) {
            Auth::getInstance()->logout();
            header('Location: ' . APP_URL);
            exit;
        }
        
        // Verificar si el usuario está autenticado
        if (!Auth::isAuthenticated()) {
            header('Location: ' . APP_URL);
            exit;
        }
        
        // Verificar si el email está verificado
        if (!Auth::isEmailVerified()) {
            header('Location: ' . APP_URL . '/verify-email-required');
            exit;
        }
        
        // Renovar la sesión si es válida
        Auth::renewSession();
    }
}

class AdminMiddleware {
    public function handle() {
        // Primero verificar autenticación
        $authMiddleware = new AuthMiddleware();
        $authMiddleware->handle();
        
        // Luego verificar si es administrador
        if (!Auth::isAdmin()) {
            header('Location: ' . APP_URL);
            exit;
        }
    }
} 