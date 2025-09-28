<?php
// Configuración para 000webhost (Producción)
// IMPORTANTE: Reemplaza estos valores con los datos de tu cuenta de 000webhost

// Configuración de la base de datos de 000webhost
define("DB_HOST", "localhost");
define("DB_NAME", "id_TU_DATABASE_NAME"); // Reemplaza con tu nombre de BD
define("DB_USER", "id_TU_USERNAME"); // Reemplaza con tu usuario
define("DB_PASS", "TU_PASSWORD"); // Reemplaza con tu contraseña

// Configuración de la aplicación para producción
define("APP_URL", "https://TU-DOMINIO.000webhostapp.com"); // Reemplaza con tu dominio
define("APP_NAME", "Dev Rent");
define("APP_DESCRIPTION", "Alquiler de vehículos, barcos y transfers");

// Configuración de idiomas
define("DEFAULT_LANG", "es");
define("AVAILABLE_LANGUAGES", ["es", "en", "fr", "de"]);

// Configuración de sesión
session_start();

// Cargar traducciones
require_once __DIR__ . "/i18n.php";

// Cargar clases necesarias
require_once __DIR__ . "/database.php";
require_once __DIR__ . "/Auth.php";
require_once __DIR__ . "/MessageHandler.php";
require_once __DIR__ . "/../models/Vehicle.php";
require_once __DIR__ . "/../models/Boat.php";
require_once __DIR__ . "/../models/Transfer.php";
require_once __DIR__ . "/../models/Location.php";

// Inicializar internacionalización
I18n::init();

// Procesar mensajes de URL solo si NO es una API call
if (strpos($_SERVER["REQUEST_URI"] ?? "", "/api") === false) {
    MessageHandler::processUrlMessages();
}

// Funciones de autenticación (mantener compatibilidad)
function isLoggedIn() {
    return Auth::isAuthenticated();
}

function isAdmin() {
    return Auth::isAdmin();
}
?>