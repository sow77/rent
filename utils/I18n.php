<?php
class I18n {
    private static $translations = [];
    private static $currentLang = 'es';

    public static function init() {
        self::$currentLang = $_SESSION['lang'] ?? 'es';
        self::loadTranslations();
    }

    public static function loadTranslations() {
        // Cargar traducciones principales
        $mainFile = __DIR__ . '/../config/translations/' . self::$currentLang . '.php';
        if (file_exists($mainFile)) {
            self::$translations = require $mainFile;
        }

        // Cargar traducciones del panel de administración
        $adminFile = __DIR__ . '/../config/translations/admin_' . self::$currentLang . '.php';
        if (file_exists($adminFile)) {
            $adminTranslations = require $adminFile;
            self::$translations['admin'] = $adminTranslations;
        }
    }

    public static function t($key) {
        $keys = explode('.', $key);
        $value = self::$translations;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $key;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public static function getCurrentLang() {
        return self::$currentLang;
    }

    public static function setLang($lang) {
        self::$currentLang = $lang;
        $_SESSION['lang'] = $lang;
        self::loadTranslations();
    }
} 