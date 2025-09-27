<?php
// Autoloader personalizado para el sistema empresarial
spl_autoload_register(function ($class) {
    $directories = [
        __DIR__ . "/../config/",
        __DIR__ . "/../controllers/",
        __DIR__ . "/../models/",
        __DIR__ . "/../vendor/PHPMailer/src/",
        __DIR__ . "/../vendor/security/defuse/src/"
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . str_replace("\\", "/", $class) . ".php";
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
?>