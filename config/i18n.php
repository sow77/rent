<?php
// Sistema unificado de internacionalización
// Eliminamos las funciones globales conflictivas y usamos solo la clase I18n

class I18n {
    private static $translations = [];
    private static $currentLang = 'es';
    private static $cache = [];
    private static $initialized = false;
    
    public static function init() {
        if (self::$initialized) {
            return;
        }

        // Iniciar sesión si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Manejar cambio de idioma desde URL
        if (isset($_GET['lang'])) {
            self::setLang($_GET['lang']);
            // Redirigir sin el parámetro lang para evitar problemas
            $redirectUrl = strtok($_SERVER['REQUEST_URI'], '?');
            header("Location: $redirectUrl");
            exit();
        }

        // Establecer idioma actual
        if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], ['es', 'en', 'fr', 'de'])) {
            self::$currentLang = $_SESSION['lang'];
        } elseif (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], ['es', 'en', 'fr', 'de'])) {
            self::$currentLang = $_COOKIE['lang'];
            $_SESSION['lang'] = $_COOKIE['lang'];
        } else {
            self::$currentLang = 'es'; // Default
            $_SESSION['lang'] = 'es';
        }

        // Cargar todas las traducciones al inicio
        $langs = ['es', 'en', 'fr', 'de'];
        foreach ($langs as $lang) {
            // Cargar traducciones principales
            $file = __DIR__ . "/translations/{$lang}.php";
            if (file_exists($file)) {
                self::$translations[$lang] = require $file;
            }

            // Cargar traducciones del panel de administración
            $adminFile = __DIR__ . "/translations/admin_{$lang}.php";
            if (file_exists($adminFile)) {
                $adminTranslations = require $adminFile;
                self::$translations[$lang]['admin'] = $adminTranslations;
            }
        }

        self::$initialized = true;
    }

    public static function setLang($lang) {
        if (in_array($lang, ['es', 'en', 'fr', 'de'])) {
            self::$currentLang = $lang;
            $_SESSION['lang'] = $lang;
            setcookie('lang', $lang, time() + (86400 * 30), "/");
            // Limpiar caché al cambiar de idioma
            self::$cache = [];
        }
    }

    public static function getCurrentLang() {
        if (!self::$initialized) {
            self::init();
        }
        return self::$currentLang;
    }

    public static function isInitialized() {
        return self::$initialized;
    }

    public static function t($key) {
        if (!self::$initialized) {
            self::init();
        }

        // Usar caché para mejorar rendimiento
        if (isset(self::$cache[self::$currentLang][$key])) {
            return self::$cache[self::$currentLang][$key];
        }

        $keys = explode('.', $key);
        $value = self::$translations[self::$currentLang] ?? self::$translations['es'];
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                // Si no se encuentra la traducción, intentar con el idioma por defecto
                if (self::$currentLang !== 'es') {
                    $defaultValue = self::$translations['es'];
                    foreach ($keys as $defaultKey) {
                        if (!isset($defaultValue[$defaultKey])) {
                            // Log del error para debugging
                            error_log("Traducción no encontrada: $key en idioma " . self::$currentLang);
                            return $key;
                        }
                        $defaultValue = $defaultValue[$defaultKey];
                    }
                    return $defaultValue;
                }
                // Log del error para debugging
                error_log("Traducción no encontrada: $key en idioma " . self::$currentLang);
                return $key;
            }
            $value = $value[$k];
        }
        
        // Guardar en caché
        self::$cache[self::$currentLang][$key] = $value;
        return $value;
    }

    // Método para precargar todas las traducciones
    public static function preloadTranslations() {
        if (!self::$initialized) {
            self::init();
        }
        
        // Precargar traducciones comunes
        $commonKeys = [
            'nav.home', 'nav.vehicles', 'nav.boats', 'nav.transfers',
            'auth.login', 'auth.logout', 'auth.profile',
            'vehicles.title', 'boats.title', 'transfers.title',
            'languages.es', 'languages.en', 'languages.fr', 'languages.de',
            // Traducciones del panel de administración
            'admin.dashboard', 'admin.users', 'admin.vehicles', 'admin.boats',
            'admin.transfers', 'admin.settings', 'admin.actions',
            'admin.add', 'admin.edit', 'admin.delete', 'admin.save',
            'admin.cancel', 'admin.error', 'admin.success',
            'admin.confirm_delete', 'admin.no_records', 'admin.search'
        ];
        
        foreach ($commonKeys as $key) {
            self::t($key);
        }
    }
}

// Inicializar y precargar traducciones
I18n::init();
I18n::preloadTranslations();
?>