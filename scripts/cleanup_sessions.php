<?php
/**
 * Script de limpieza de sesiones expiradas
 * Este script debe ejecutarse periódicamente (cron job) para limpiar sesiones expiradas
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/SessionConfig.php';

class SessionCleanup {
    
    public static function cleanup() {
        try {
            // Obtener conexión a la base de datos
            $db = Database::getInstance()->getConnection();
            
            // Limpiar sesiones expiradas de la tabla de sesiones de PHP
            $sessionPath = session_save_path();
            if (empty($sessionPath)) {
                $sessionPath = sys_get_temp_dir();
            }
            
            $sessionFiles = glob($sessionPath . '/sess_*');
            $cleanedCount = 0;
            
            foreach ($sessionFiles as $sessionFile) {
                if (filemtime($sessionFile) < (time() - 1800)) { // 30 minutos
                    unlink($sessionFile);
                    $cleanedCount++;
                }
            }
            
            // Limpiar datos de sesión de la base de datos si los hay
            // (esto es opcional, dependiendo de si usas almacenamiento de sesiones en BD)
            
            echo "Limpieza completada: $cleanedCount sesiones expiradas eliminadas\n";
            return true;
            
        } catch (Exception $e) {
            echo "Error en limpieza de sesiones: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Ejecutar limpieza si se llama directamente
if (php_sapi_name() === 'cli') {
    SessionCleanup::cleanup();
}
