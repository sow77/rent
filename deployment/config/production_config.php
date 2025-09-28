<?php
// Configuración para 000webhost (Producción)
// Reemplaza estos valores con los datos de tu cuenta de 000webhost

// Configuración de la base de datos de 000webhost
define('DB_HOST', 'localhost'); // 000webhost usa localhost
define('DB_NAME', 'id_tu_database_name'); // Reemplaza con tu nombre de BD de 000webhost
define('DB_USER', 'id_tu_username'); // Reemplaza con tu usuario de 000webhost
define('DB_PASS', 'tu_password'); // Reemplaza con tu contraseña de 000webhost

// Configuración de la aplicación para producción
define('APP_URL', 'https://tu-dominio.000webhostapp.com'); // Reemplaza con tu dominio de 000webhost
define('APP_NAME', 'Dev Rent');
define('APP_DESCRIPTION', 'Alquiler de vehículos, barcos y transfers');

// Configuración de idiomas
define('DEFAULT_LANG', 'es');
define('AVAILABLE_LANGUAGES', ['es', 'en', 'fr', 'de']);

// Configuración de sesión
session_start();

// Cargar traducciones
require_once __DIR__ . '/i18n.php';

// Cargar clases necesarias
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/MessageHandler.php';
require_once __DIR__ . '/../models/Vehicle.php';
require_once __DIR__ . '/../models/Boat.php';
require_once __DIR__ . '/../models/Transfer.php';
require_once __DIR__ . '/../models/Location.php';

// Inicializar internacionalización
I18n::init();

// Procesar mensajes de URL solo si NO es una API call
if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api') === false) {
    MessageHandler::processUrlMessages();
}

// Funciones de autenticación (mantener compatibilidad)
/**
 * Comprueba si el usuario está autenticado
 * @return bool
 */
function isLoggedIn() {
    return Auth::isAuthenticated();
}

/**
 * Comprueba si el usuario tiene rol administrador
 * @return bool
 */
function isAdmin() {
    return Auth::isAdmin();
}
?>
