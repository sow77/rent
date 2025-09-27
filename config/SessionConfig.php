<?php
/**
 * Configuración segura de sesiones
 */

class SessionConfig {
    
    public static function init() {
        // Solo configurar si la sesión no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            // Configuración de cookies de sesión más segura
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.use_strict_mode', 1);
            ini_set('session.use_only_cookies', 1);
            
            // Configuración de tiempo de vida de sesión
            ini_set('session.gc_maxlifetime', 1800); // 30 minutos
            ini_set('session.cookie_lifetime', 0); // Sesión de navegador (se cierra al cerrar navegador)
            
            // Configuración de limpieza de sesiones
            ini_set('session.gc_probability', 1);
            ini_set('session.gc_divisor', 100);
            
            // Iniciar sesión
            session_start();
        }
        
        // Solo verificar expiración si hay una sesión autenticada
        if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
            // Verificar si la sesión ha expirado
            self::checkSessionExpiry();
            
            // Regenerar ID de sesión periódicamente para mayor seguridad
            self::regenerateSessionId();
        }
    }
    
    /**
     * Verificar si la sesión ha expirado
     */
    private static function checkSessionExpiry() {
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity'] > 1800)) { // 30 minutos
            // Sesión expirada
            self::destroySession();
            return false;
        }
        
        // Actualizar tiempo de última actividad
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    /**
     * Regenerar ID de sesión cada 15 minutos para mayor seguridad
     */
    private static function regenerateSessionId() {
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 900) { // 15 minutos
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
    
    /**
     * Destruir sesión completamente
     */
    public static function destroySession() {
        // Limpiar todas las variables de sesión
        $_SESSION = array();
        
        // Destruir la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destruir la sesión
        session_destroy();
    }
    
    /**
     * Verificar si la sesión es válida
     */
    public static function isValidSession() {
        return isset($_SESSION['authenticated']) && 
               $_SESSION['authenticated'] === true &&
               isset($_SESSION['last_activity']) &&
               (time() - $_SESSION['last_activity'] <= 1800);
    }
    
    /**
     * Renovar sesión (actualizar tiempo de actividad)
     */
    public static function renewSession() {
        if (self::isValidSession()) {
            $_SESSION['last_activity'] = time();
            return true;
        }
        return false;
    }
}
