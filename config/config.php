<?php
// Configuración de la base de datos (permite variables de entorno)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'carrent_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Configuración de la aplicación
define('APP_URL', getenv('APP_URL') ?: 'http://localhost/dev-rent');
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