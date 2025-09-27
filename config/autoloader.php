<?php
/**
 * Autoloader personalizado para Dev Rent
 */

// PHPMailer
if (file_exists(__DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
}

// Defuse Encryption
if (file_exists(__DIR__ . '/../vendor/defuse/php-encryption/src/Defuse/Crypto/Crypto.php')) {
    require_once __DIR__ . '/../vendor/defuse/php-encryption/src/Defuse/Crypto/Crypto.php';
    require_once __DIR__ . '/../vendor/defuse/php-encryption/src/Defuse/Crypto/Key.php';
    require_once __DIR__ . '/../vendor/defuse/php-encryption/src/Defuse/Crypto/KeyOrPassword.php';
    require_once __DIR__ . '/../vendor/defuse/php-encryption/src/Defuse/Crypto/KeyProtectedByPassword.php';
    require_once __DIR__ . '/../vendor/defuse/php-encryption/src/Defuse/Crypto/RuntimeTests.php';
    require_once __DIR__ . '/../vendor/defuse/php-encryption/src/Defuse/Crypto/Encoding.php';
    require_once __DIR__ . '/../vendor/defuse/php-encryption/src/Defuse/Crypto/File.php';
    require_once __DIR__ . '/../vendor/defuse/php-encryption/src/Defuse/Crypto/Hash.php';
    require_once __DIR__ . '/../vendor/defuse/php-encryption/src/Defuse/Crypto/Key.php';
    require_once __DIR__ . '/../vendor/defuse/php-encryption/src/Defuse/Crypto/KeyOrPassword.php';
    require_once __DIR__ . '/../vendor/defuse/php-encryption/src/Defuse/Crypto/KeyProtectedByPassword.php';
    require_once __DIR__ . '/../vendor/defuse/php-encryption/src/Defuse/Crypto/RuntimeTests.php';
    require_once __DIR__ . '/../vendor/defuse/php-encryption/src/Defuse/Crypto/Encoding.php';
    require_once __DIR__ . '/../vendor/defuse/php-encryption/src/Defuse/Crypto/File.php';
    require_once __DIR__ . '/../vendor/defuse/php-encryption/src/Defuse/Crypto/Hash.php';
}

// End of autoloader
?>
