<?php
// config/MessageHandler.php

class MessageHandler {
    
    /**
     * Establecer un mensaje en la sesión
     */
    public static function setMessage($type, $message) {
        $_SESSION['messages'][] = [
            'type' => $type,
            'message' => $message,
            'timestamp' => time()
        ];
    }
    
    /**
     * Obtener todos los mensajes y limpiarlos
     */
    public static function getMessages() {
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);
        return $messages;
    }
    
    /**
     * Verificar si hay mensajes
     */
    public static function hasMessages() {
        return !empty($_SESSION['messages']);
    }
    
    /**
     * Establecer mensaje de éxito
     */
    public static function success($message) {
        self::setMessage('success', $message);
    }
    
    /**
     * Establecer mensaje de error
     */
    public static function error($message) {
        self::setMessage('error', $message);
    }
    
    /**
     * Establecer mensaje de advertencia
     */
    public static function warning($message) {
        self::setMessage('warning', $message);
    }
    
    /**
     * Establecer mensaje de información
     */
    public static function info($message) {
        self::setMessage('info', $message);
    }
    
    /**
     * Procesar mensajes de URL (para redirecciones)
     */
    public static function processUrlMessages() {
        $messageTypes = ['success', 'error', 'warning', 'info'];
        
        foreach ($messageTypes as $type) {
            if (isset($_GET[$type])) {
                $message = $_GET[$type];
                self::setMessage($type, urldecode($message));
            }
        }
        
        // Procesar mensajes específicos del sistema
        if (isset($_GET['message'])) {
            $message = $_GET['message'];
            switch ($message) {
                case 'session_expired':
                    self::warning('Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
                    break;
                case 'login_required':
                    self::warning('Debes iniciar sesión para acceder a esta página.');
                    break;
                case 'admin_required':
                    self::error('No tienes permisos para acceder a esta página.');
                    break;
                case 'logout_success':
                    self::success('Has cerrado sesión correctamente.');
                    break;
            }
        }
    }
    
    /**
     * Renderizar mensajes en HTML
     */
    public static function renderMessages() {
        $messages = self::getMessages();
        $html = '';
        
        foreach ($messages as $msg) {
            $type = $msg['type'];
            $message = htmlspecialchars($msg['message']);
            
            $icon = 'info-circle';
            if ($type === 'success') $icon = 'check-circle';
            elseif ($type === 'error') $icon = 'exclamation-circle';
            elseif ($type === 'warning') $icon = 'exclamation-triangle';
            
            $html .= <<<HTML
            <div class="alert alert-{$type} alert-dismissible fade show" role="alert">
                <i class="fas fa-{$icon}"></i>
                {$message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            HTML;
        }
        
        return $html;
    }
}
?> 