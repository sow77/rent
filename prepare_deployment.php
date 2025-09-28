<?php
/**
 * Script para preparar el proyecto para despliegue en 000webhost
 * Este script crea una copia limpia del proyecto sin archivos de desarrollo
 */

echo "🚀 Preparando proyecto para despliegue en 000webhost...\n\n";

// Directorio de destino
$deployDir = 'deployment/';

// Crear directorio de despliegue si no existe
if (!is_dir($deployDir)) {
    mkdir($deployDir, 0755, true);
    echo "✅ Directorio de despliegue creado: $deployDir\n";
}

// Archivos y carpetas a incluir
$includeFiles = [
    'api/',
    'config/',
    'controllers/',
    'middleware/',
    'models/',
    'public/',
    'utils/',
    'vendor/',
    'views/',
    'index.php',
    '.htaccess',
    'robots.txt',
    'sitemap.xml'
];

// Archivos y carpetas a excluir
$excludeFiles = [
    'database/',
    'maintenance/',
    'scripts/',
    'deployment/',
    'config/production_config.php', // No incluir el archivo de ejemplo
    'prepare_deployment.php',
    'DEPLOY_INSTRUCTIONS.md',
    '.git/',
    '.gitignore',
    'README.md',
    'docker-compose.yml'
];

// Función para copiar archivos recursivamente
function copyRecursive($src, $dst, $excludeFiles = []) {
    $dir = opendir($src);
    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
    }
    
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            $srcFile = $src . '/' . $file;
            $dstFile = $dst . '/' . $file;
            
            // Verificar si el archivo debe ser excluido
            $shouldExclude = false;
            foreach ($excludeFiles as $exclude) {
                if (strpos($srcFile, $exclude) !== false) {
                    $shouldExclude = true;
                    break;
                }
            }
            
            if (!$shouldExclude) {
                if (is_dir($srcFile)) {
                    copyRecursive($srcFile, $dstFile, $excludeFiles);
                } else {
                    copy($srcFile, $dstFile);
                    echo "📁 Copiado: $srcFile -> $dstFile\n";
                }
            } else {
                echo "❌ Excluido: $srcFile\n";
            }
        }
    }
    closedir($dir);
}

// Copiar archivos incluidos
foreach ($includeFiles as $item) {
    if (file_exists($item)) {
        if (is_dir($item)) {
            copyRecursive($item, $deployDir . $item, $excludeFiles);
        } else {
            copy($item, $deployDir . $item);
            echo "📄 Copiado: $item\n";
        }
    } else {
        echo "⚠️  No encontrado: $item\n";
    }
}

// Crear archivo de configuración de producción
$productionConfig = '<?php
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
?>';

file_put_contents($deployDir . 'config/config.php', $productionConfig);
echo "📄 Creado archivo de configuración de producción\n";

// Crear archivo README para el despliegue
$readmeContent = "# Dev Rent - Archivos de Despliegue

## Instrucciones de instalación:

1. Sube todos estos archivos a la carpeta `public_html` de tu cuenta de 000webhost
2. Importa el archivo `database/carrent_db.sql` en tu base de datos MySQL
3. Edita `config/config.php` con los datos de tu base de datos de 000webhost
4. Visita tu sitio web

## Datos que necesitas cambiar en config/config.php:
- DB_NAME: Nombre de tu base de datos en 000webhost
- DB_USER: Usuario de tu base de datos en 000webhost  
- DB_PASS: Contraseña de tu base de datos en 000webhost
- APP_URL: URL de tu sitio en 000webhost

¡Listo para desplegar! 🚀
";

file_put_contents($deployDir . 'README_DEPLOY.txt', $readmeContent);
echo "📄 Creado archivo README para despliegue\n";

echo "\n✅ ¡Preparación completada!\n";
echo "📁 Archivos listos en: $deployDir\n";
echo "📋 Sigue las instrucciones en DEPLOY_INSTRUCTIONS.md\n";
echo "🚀 ¡Tu proyecto está listo para desplegar en 000webhost!\n";
?>
