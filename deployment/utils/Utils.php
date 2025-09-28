<?php
// helpers/Utils.php

class Utils {
    // Formatear precio en euros
    public static function formatPrice($price) {
        return number_format($price, 2, ',', '.') . ' €';
    }
    
    // Formatear fecha
    public static function formatDate($date) {
        $timestamp = strtotime($date);
        return date('d/m/Y', $timestamp);
    }
    
    // Calcular duración en días entre dos fechas
    public static function dateDiff($start, $end) {
        $start_date = new DateTime($start);
        $end_date = new DateTime($end);
        $interval = $start_date->diff($end_date);
        return $interval->days;
    }
    
    // Generar mensajes de alerta
    public static function showAlert($type, $message) {
        $alertClass = '';
        
        switch ($type) {
            case 'success':
                $alertClass = 'alert-success';
                break;
            case 'error':
                $alertClass = 'alert-danger';
                break;
            case 'warning':
                $alertClass = 'alert-warning';
                break;
            case 'info':
                $alertClass = 'alert-info';
                break;
            default:
                $alertClass = 'alert-info';
        }
        
        return '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">
                    ' . $message . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
    }
    
    // Redirigir con mensaje
    public static function redirect($url, $message = '', $type = 'info') {
        if (!empty($message)) {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            $_SESSION['alert'] = [
                'message' => $message,
                'type' => $type
            ];
        }
        
        header('Location: ' . $url);
        exit;
    }
    
    // Obtener mensaje de alerta si existe y eliminarlo de la sesión
    public static function getAlert() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['alert'])) {
            $alert = self::showAlert($_SESSION['alert']['type'], $_SESSION['alert']['message']);
            unset($_SESSION['alert']);
            return $alert;
        }
        
        return '';
    }
}
