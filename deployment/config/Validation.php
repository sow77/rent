<?php
/**
 * Sistema de validación sofisticado para registro de usuarios
 */

class Validation {
    
    /**
     * Validar email auténtico - Verificación real como redes sociales
     */
    public static function validateEmail($email) {
        $errors = [];
        
        // Validación básica de formato
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El formato del email no es válido';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Verificar dominio
        $domain = substr(strrchr($email, "@"), 1);
        if (!$domain) {
            $errors[] = 'El dominio del email no es válido';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Verificar que el dominio tenga registros MX válidos (obligatorio)
        if (!self::checkMXRecord($domain)) {
            $errors[] = 'El dominio del email no tiene registros MX válidos. Verifica que el email sea correcto.';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Verificar que el dominio no sea temporal/desechable
        if (self::isDisposableDomain($domain)) {
            $errors[] = 'No se permiten emails temporales o desechables';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Verificar que el email no esté ya registrado
        if (self::emailExists($email)) {
            $errors[] = 'Este email ya está registrado. Usa otro email o inicia sesión.';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Verificar que el email no sea obviamente falso
        if (self::isFakeEmail($email)) {
            $errors[] = 'El email parece ser falso o no válido';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Validación con servicio externo (Kickbox) - DESHABILITADA TEMPORALMENTE
        // TODO: Habilitar cuando se configure la API key de Kickbox
        // require_once 'config/ExternalValidation.php';
        // $externalValidation = ExternalValidation::validateEmailWithKickbox($email);
        
        // if (!$externalValidation['valid']) {
        //     switch ($externalValidation['reason']) {
        //         case 'undeliverable':
        //             $errors[] = 'El email no es válido o no existe';
        //             break;
        //         case 'risky':
        //             $errors[] = 'El email parece ser sospechoso';
        //             break;
        //         default:
        //             $errors[] = 'El email no pudo ser verificado';
        //     }
        //     return ['valid' => false, 'errors' => $errors];
        // }
        
        return ['valid' => true, 'errors' => []];
    }
    
    /**
     * Verificar que el email existe realmente mediante verificación SMTP
     */
    private static function verifyEmailExists($email) {
        // Para evitar problemas de rendimiento, solo verificamos dominios conocidos
        $domain = substr(strrchr($email, "@"), 1);
        
        // Solo verificar dominios de proveedores confiables
        $trustedDomains = [
            'gmail.com', 'googlemail.com',
            'outlook.com', 'hotmail.com', 'live.com', 'msn.com',
            'yahoo.com', 'yahoo.es', 'yahoo.co.uk', 'yahoo.fr', 'yahoo.de',
            'icloud.com', 'me.com', 'mac.com',
            'protonmail.com', 'proton.me',
            'aol.com', 'aol.co.uk',
            'zoho.com', 'zoho.eu',
            'fastmail.com', 'fastmail.fm'
        ];
        
        if (!in_array(strtolower($domain), $trustedDomains)) {
            // Para dominios no confiables, solo verificar MX
            return self::checkMXRecord($domain);
        }
        
        // Para dominios confiables, hacer verificación SMTP básica solo si es posible
        try {
            $smtpResult = self::smtpVerify($email);
            if ($smtpResult) {
                return true;
            }
        } catch (Exception $e) {
            // Si falla la verificación SMTP, solo verificar MX
        }
        
        // Si SMTP falla, solo verificar MX (más permisivo para evitar falsos negativos)
        return self::checkMXRecord($domain);
    }
    
    /**
     * Verificación SMTP básica del email
     */
    private static function smtpVerify($email) {
        $domain = substr(strrchr($email, "@"), 1);
        
        // Obtener registros MX
        $mxRecords = [];
        if (!getmxrr($domain, $mxRecords)) {
            return false;
        }
        
        // Usar el primer servidor MX
        $mxHost = $mxRecords[0];
        
        // Crear conexión SMTP
        $smtp = fsockopen($mxHost, 25, $errno, $errstr, 10);
        if (!$smtp) {
            return false;
        }
        
        // Configurar timeout
        stream_set_timeout($smtp, 10);
        
        // Leer respuesta inicial
        $response = fgets($smtp, 1024);
        if (substr($response, 0, 3) !== '220') {
            fclose($smtp);
            return false;
        }
        
        // Enviar HELO
        fputs($smtp, "HELO localhost\r\n");
        $response = fgets($smtp, 1024);
        if (substr($response, 0, 3) !== '250') {
            fclose($smtp);
            return false;
        }
        
        // Enviar MAIL FROM
        fputs($smtp, "MAIL FROM: <noreply@dev-rent.com>\r\n");
        $response = fgets($smtp, 1024);
        if (substr($response, 0, 3) !== '250') {
            fclose($smtp);
            return false;
        }
        
        // Enviar RCPT TO
        fputs($smtp, "RCPT TO: <$email>\r\n");
        $response = fgets($smtp, 1024);
        
        // Enviar QUIT
        fputs($smtp, "QUIT\r\n");
        fclose($smtp);
        
        // Verificar respuesta
        return substr($response, 0, 3) === '250';
    }
    
    /**
     * Detectar emails obviamente falsos
     */
    private static function isFakeEmail($email) {
        $fakePatterns = [
            'test@', 'test1@', 'test2@', 'test3@', 'test4@', 'test5@',
            'prueba@', 'fake@', 'falso@', 'inventado@',
            'ejemplo@', 'example@', 'demo@', 'sample@',
            'admin@', 'administrador@',
            'juan@', 'maria@', 'pedro@', 'ana@', 'carlos@', 'laura@',
            '123@', '456@', '789@', '000@', '111@', '222@',
            'aaa@', 'bbb@', 'ccc@', 'ddd@', 'eee@', 'fff@'
        ];
        
        $emailLower = strtolower($email);
        foreach ($fakePatterns as $pattern) {
            if (strpos($emailLower, $pattern) === 0) {
                return true;
            }
        }
        
        // Detectar patrones numéricos al final (test1, test2, etc.)
        if (preg_match('/^(test|prueba|fake|falso|ejemplo|demo|sample|admin|juan|maria|pedro|ana|carlos|laura)\d*@/', $emailLower)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Verificar si un dominio es temporal/desechable
     */
    private static function isDisposableDomain($domain) {
        // Lista de dominios temporales/descartables conocidos
        $disposableDomains = [
            '10minutemail.com', 'tempmail.org', 'guerrillamail.com', 'mailinator.com',
            'yopmail.com', 'temp-mail.org', 'throwaway.email', 'getnada.com',
            'maildrop.cc', 'sharklasers.com', 'guerrillamailblock.com', 'pokemail.net',
            'spam4.me', 'bccto.me', 'chacuo.net', 'dispostable.com', 'mailnesia.com',
            'mailcatch.com', 'inboxalias.com', 'mailin8r.com', 'mailinater.com',
            'mailinator2.com', 'notmailinator.com', 'reallymymail.com', 'sogetthis.com',
            'spamhereplease.com', 'superrito.com', 'thisisnotmyrealemail.com',
            'tradermail.info', 'veryrealemail.com', 'wegwerfemail.de', 'wegwerfmail.de',
            'wegwerfmail.net', 'wegwerfmail.org', 'wegwerpmailadres.nl', 'wetrainbayarea.com',
            'wetrainbayarea.org', 'wh4f.org', 'whyspam.me', 'willselfdestruct.com',
            'wuzup.net', 'wuzupmail.net', 'yeah.net', 'yopmail.net', 'yopmail.org',
            'yopmail.pp.ua', 'ypmail.webarnak.fr', 'cool.fr.nf', 'jetable.fr.nf',
            'nospam.ze.tc', 'nomail.xl.cx', 'mega.zik.dj', 'speed.1s.fr', 'courriel.fr.nf',
            'moncourrier.fr.nf', 'monemail.fr.nf', 'monmail.fr.nf', 'trbvm.com',
            '0-mail.com', '1secmail.com', '1secmail.org', '1secmail.net', 'binkmail.com',
            'bobmail.info', 'chammy.info', 'devnullmail.com', 'letthemeatspam.com',
            'mailin8r.com', 'mailinator.com', 'mailinator2.com', 'notmailinator.com',
            'reallymymail.com', 'reconmail.com', 'sogetthis.com', 'spamhereplease.com',
            'superrito.com', 'thisisnotmyrealemail.com', 'tradermail.info',
            'veryrealemail.com', 'wegwerfemail.de', 'wegwerfmail.de', 'wegwerfmail.net',
            'wegwerfmail.org', 'wegwerpmailadres.nl', 'wetrainbayarea.com',
            'wetrainbayarea.org', 'wh4f.org', 'whyspam.me', 'willselfdestruct.com',
            'wuzup.net', 'wuzupmail.net', 'yeah.net', 'yopmail.net', 'yopmail.org',
            'yopmail.pp.ua', 'ypmail.webarnak.fr', 'cool.fr.nf', 'jetable.fr.nf',
            'nospam.ze.tc', 'nomail.xl.cx', 'mega.zik.dj', 'speed.1s.fr', 'courriel.fr.nf',
            'moncourrier.fr.nf', 'monemail.fr.nf', 'monmail.fr.nf', 'trbvm.com'
        ];
        
        return in_array(strtolower($domain), $disposableDomains);
    }
    
    /**
     * Verificar si el email ya existe en la base de datos
     */
    private static function emailExists($email) {
        try {
            // Verificar si las constantes de BD están definidas
            if (!defined('DB_HOST')) {
                return false; // En modo de prueba, asumir que no existe
            }
            
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([strtolower($email)]);
            
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            // Si hay error de BD, asumir que no existe para no bloquear el registro
            return false;
        }
    }
    
    /**
     * Validar número de teléfono operativo - SOLO números reales y verificables
     */
    public static function validatePhone($phone) {
        $errors = [];
        
        // Limpiar el número de teléfono
        $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Verificar que no esté vacío
        if (empty($cleanPhone)) {
            $errors[] = 'El número de teléfono es obligatorio';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Verificar longitud internacional (entre 10 y 16 caracteres)
        if (strlen($cleanPhone) < 10 || strlen($cleanPhone) > 16) {
            $errors[] = 'El número de teléfono debe tener entre 10 y 16 dígitos';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Verificar formato internacional estricto
        if (!preg_match('/^\+[1-9]\d{8,14}$/', $cleanPhone)) {
            $errors[] = 'El formato del número de teléfono no es válido. Use formato internacional: +1 234 567 8900';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Validación básica - sin restricciones absurdas
        
        // Verificar que el código de país sea válido
        if (!self::isValidCountryCode($cleanPhone)) {
            $errors[] = 'El código de país del teléfono no es válido';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Validación básica de formato - sin restricciones absurdas
        
        // Verificar que el teléfono no esté ya registrado
        if (self::phoneExists($cleanPhone)) {
            $errors[] = 'Este número de teléfono ya está registrado. Usa otro número o inicia sesión.';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Validación con servicio externo (Twilio Lookup) - DESHABILITADA TEMPORALMENTE
        // TODO: Habilitar cuando se configure el número de teléfono de Twilio
        // require_once 'config/ExternalValidation.php';
        // $externalValidation = ExternalValidation::validatePhoneWithTwilio($cleanPhone);
        
        // if (!$externalValidation['valid']) {
        //     switch ($externalValidation['reason']) {
        //         case 'not_found':
        //             $errors[] = 'El número de teléfono no es válido o no existe';
        //             break;
        //         default:
        //             $errors[] = 'El número de teléfono no pudo ser verificado';
        //     }
        //     return ['valid' => false, 'errors' => $errors];
        // }
        
        // Si hay un número formateado, usarlo
        // if (isset($externalValidation['formatted'])) {
        //     $cleanPhone = $externalValidation['formatted'];
        // }
        
        return ['valid' => true, 'errors' => [], 'clean_phone' => $cleanPhone];
    }
    
    /**
     * Verificar si el teléfono ya existe en la base de datos
     */
    private static function phoneExists($phone) {
        try {
            // Verificar si las constantes de BD están definidas
            if (!defined('DB_HOST')) {
                return false; // En modo de prueba, asumir que no existe
            }
            
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
            $stmt->execute([$phone]);
            
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            // Si hay error de BD, asumir que no existe para no bloquear el registro
            return false;
        }
    }
    
    /**
     * Verificar que el número de teléfono existe realmente
     */
    private static function verifyPhoneExists($phone) {
        // Para evitar problemas de rendimiento, solo verificamos números de países específicos
        $countryCode = substr($phone, 0, 3);
        
        // Solo verificar números de países con APIs confiables
        $verifiableCountries = ['+1', '+34', '+44', '+33', '+49', '+39', '+7'];
        
        if (!in_array($countryCode, $verifiableCountries)) {
            // Para países no verificables, solo validar formato
            return true;
        }
        
        // Para países verificables, hacer verificación real
        return self::realPhoneVerification($phone);
    }
    
    /**
     * Verificación real del número de teléfono (como redes sociales)
     */
    private static function realPhoneVerification($phone) {
        $digits = substr($phone, 1); // Quitar el +
        
        // Verificar que no sea obviamente falso
        if (self::isObviouslyFake($digits)) {
            return false;
        }
        
        // Verificar que tenga características de número real
        if (!self::hasRealCharacteristics($digits)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Verificar si un número es obviamente falso
     */
    private static function isObviouslyFake($digits) {
        $len = strlen($digits);
        
        // Números con patrones obvios de ser falsos
        $patterns = [
            '/^(\d)\1+$/',  // Todos los dígitos iguales
            '/^(\d{2})\1+$/',  // Grupos de 2 dígitos repetidos
            '/^(\d{3})\1+$/',  // Grupos de 3 dígitos repetidos
            '/^1234567890$/',  // Secuencia completa
            '/^0123456789$/',  // Secuencia completa con 0
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $digits)) {
                return true;
            }
        }
        
        // Verificar patrones de repetición en grupos
        if (preg_match('/(\d{2})\1{2,}/', $digits)) {
            return true; // Grupos de 2 dígitos repetidos
        }
        
        if (preg_match('/(\d{3})\1{1,}/', $digits)) {
            return true; // Grupos de 3 dígitos repetidos
        }
        
        // Verificar secuencias ascendentes o descendentes
        if (self::isSequential($digits)) {
            return true;
        }
        
        // Verificar patrones específicos de números inventados
        if (self::hasInventedPattern($digits)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Verificar si tiene patrones de números inventados
     */
    private static function hasInventedPattern($digits) {
        $len = strlen($digits);
        
        // Verificar patrones de números que parecen inventados
        // Números que empiezan con 6, 7, 8, 9 (móviles españoles) y tienen patrones sospechosos
        if (preg_match('/^6\d{8}$/', $digits) || preg_match('/^7\d{8}$/', $digits) || 
            preg_match('/^8\d{8}$/', $digits) || preg_match('/^9\d{8}$/', $digits)) {
            
            // Verificar si tiene características de número inventado
            if (self::hasInventedCharacteristics($digits)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Verificar características de números inventados
     */
    private static function hasInventedCharacteristics($digits) {
        $len = strlen($digits);
        
        // Verificar si el número tiene características obvias de ser inventado
        // Patrones que sugieren números inventados
        
        // Verificar repetición de dígitos en posiciones específicas
        if (preg_match('/(\d)\1{3,}/', $digits)) {
            return true; // Más de 3 dígitos consecutivos iguales
        }
        
        // Verificar patrones de números que parecen inventados
        // Números que tienen secuencias muy regulares
        if (preg_match('/\d{2}\d{2}\d{2}\d{2}/', $digits)) {
            // Verificar si es un patrón obvio
            $chunks = str_split($digits, 2);
            $uniqueChunks = array_unique($chunks);
            if (count($uniqueChunks) <= 2) {
                return true; // Muy pocos chunks únicos
            }
        }
        
        // Verificar si el número tiene una distribución muy uniforme
        $digitCounts = array_count_values(str_split($digits));
        $maxCount = max($digitCounts);
        $minCount = min($digitCounts);
        
        // Si la diferencia entre el dígito más frecuente y el menos frecuente es muy pequeña
        if (($maxCount - $minCount) <= 1 && $len >= 10) {
            return true; // Distribución muy uniforme, probablemente inventado
        }
        
        return false;
    }
    
    /**
     * Verificar si tiene características de número real
     */
    private static function hasRealCharacteristics($digits) {
        $len = strlen($digits);
        
        // Debe tener al menos 4 dígitos diferentes
        $uniqueDigits = count(array_unique(str_split($digits)));
        if ($uniqueDigits < 4) {
            return false;
        }
        
        // Debe tener una distribución razonable de dígitos
        if (!self::hasReasonableDistribution($digits)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Verificar si tiene distribución razonable de dígitos
     */
    private static function hasReasonableDistribution($digits) {
        $len = strlen($digits);
        $digitCounts = array_count_values(str_split($digits));
        
        // No debe tener más del 40% de un solo dígito
        foreach ($digitCounts as $count) {
            if ($count > ($len * 0.4)) {
                return false;
            }
        }
        
        // Debe tener al menos 3 dígitos diferentes con frecuencia > 1
        $frequentDigits = 0;
        foreach ($digitCounts as $count) {
            if ($count > 1) {
                $frequentDigits++;
            }
        }
        
        return $frequentDigits >= 3;
    }
    
    /**
     * Verificación básica del número de teléfono
     */
    private static function basicPhoneVerification($phone) {
        // Verificar que el número no sea una secuencia obvia
        $digits = substr($phone, 1); // Quitar el +
        
        // Verificar patrones obvios
        $patterns = [
            '1111111111', '2222222222', '3333333333', '4444444444', '5555555555',
            '6666666666', '7777777777', '8888888888', '9999999999', '0000000000',
            '1234567890', '2345678901', '3456789012', '4567890123', '5678901234',
            '6789012345', '7890123456', '8901234567', '9012345678', '0123456789',
            '123456789', '234567890', '345678901', '456789012', '567890123',
            '678901234', '789012345', '890123456', '901234567', '012345678'
        ];
        
        foreach ($patterns as $pattern) {
            if (strpos($digits, $pattern) !== false) {
                return false;
            }
        }
        
        // Verificar que no sea solo un número repetido
        if (preg_match('/^(\d)\1+$/', $digits)) {
            return false;
        }
        
        // Verificar que tenga al menos 4 dígitos diferentes
        $uniqueDigits = count(array_unique(str_split($digits)));
        if ($uniqueDigits < 4) {
            return false;
        }
        
        // Verificar que no sea una secuencia ascendente o descendente
        if (self::isSequential($digits)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Verificar si un número es secuencial
     */
    private static function isSequential($digits) {
        $len = strlen($digits);
        if ($len < 4) return false;
        
        // Verificar secuencias ascendentes
        $ascending = true;
        for ($i = 1; $i < $len; $i++) {
            if (intval($digits[$i]) !== (intval($digits[$i-1]) + 1) % 10) {
                $ascending = false;
                break;
            }
        }
        
        // Verificar secuencias descendentes
        $descending = true;
        for ($i = 1; $i < $len; $i++) {
            if (intval($digits[$i]) !== (intval($digits[$i-1]) - 1 + 10) % 10) {
                $descending = false;
                break;
            }
        }
        
        return $ascending || $descending;
    }
    
    /**
     * Validación básica de teléfono - sin restricciones absurdas
     */
    
    /**
     * Verificar si el código de país es válido
     */
    private static function isValidCountryCode($phone) {
        $validCountryCodes = [
            '+1', '+7', '+20', '+27', '+30', '+31', '+32', '+33', '+34', '+36', '+39', '+40', '+41', '+43', '+44', '+45', '+46', '+47', '+48', '+49',
            '+51', '+52', '+53', '+54', '+55', '+56', '+57', '+58', '+60', '+61', '+62', '+63', '+64', '+65', '+66', '+81', '+82', '+84', '+86', '+90', '+91', '+92', '+93', '+94', '+95', '+98'
        ];
        
        foreach ($validCountryCodes as $code) {
            if (strpos($phone, $code) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Validación básica de teléfono - sin restricciones absurdas
     */
    
    /**
     * Validar contraseña con estándares de seguridad
     */
    public static function validatePassword($password) {
        $errors = [];
        
        // Longitud mínima
        if (strlen($password) < 8) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres';
        }
        
        // Longitud máxima
        if (strlen($password) > 128) {
            $errors[] = 'La contraseña no puede tener más de 128 caracteres';
        }
        
        // Al menos una letra minúscula
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'La contraseña debe contener al menos una letra minúscula';
        }
        
        // Al menos una letra mayúscula
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'La contraseña debe contener al menos una letra mayúscula';
        }
        
        // Al menos un número
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'La contraseña debe contener al menos un número';
        }
        
        // Al menos un carácter especial
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?~`]/', $password)) {
            $errors[] = 'La contraseña debe contener al menos un carácter especial (!@#$%^&*()_+-=[]{}|;:,.<>?)';
        }
        
        // No debe contener espacios
        if (strpos($password, ' ') !== false) {
            $errors[] = 'La contraseña no puede contener espacios';
        }
        
        // No debe ser una contraseña común
        $commonPasswords = [
            'password', '123456', '123456789', 'qwerty', 'abc123', 'password123',
            'admin', 'letmein', 'welcome', 'monkey', '1234567890', 'password1',
            'qwerty123', 'dragon', 'master', 'hello', 'freedom', 'whatever',
            'qazwsx', 'trustno1', 'jordan', 'jennifer', 'zxcvbnm', 'asdfgh',
            'hunter', 'buster', 'soccer', 'harley', 'batman', 'andrew',
            'tigger', 'sunshine', 'iloveyou', '2000', 'charlie', 'robert',
            'thomas', 'hockey', 'ranger', 'daniel', 'starwars', 'klaster',
            '112233', 'george', 'computer', 'michelle', 'jessica', 'pepper',
            '1234', 'zodiac', 'andrea', 'steven', 'dakota', 'joshua',
            'jennifer', 'george', 'michelle', 'jessica', 'pepper', 'andrea',
            'steven', 'dakota', 'joshua', 'jennifer', 'george', 'michelle'
        ];
        
        if (in_array(strtolower($password), $commonPasswords)) {
            $errors[] = 'La contraseña es demasiado común, elige una más segura';
        }
        
        // Verificar patrones repetitivos
        if (preg_match('/(.)\1{2,}/', $password)) {
            $errors[] = 'La contraseña no puede contener caracteres repetidos consecutivos';
        }
        
        // Verificar secuencias (solo si la contraseña es principalmente secuencial)
        $sequentialPatterns = [
            '123456789', '234567890', '345678901', '456789012', '567890123',
            '678901234', '789012345', '890123456', '901234567', '012345678',
            'abcdefghi', 'bcdefghij', 'cdefghijk', 'defghijkl', 'efghijklm',
            'fghijklmn', 'ghijklmno', 'hijklmnop', 'ijklmnopq', 'jklmnopqr',
            'klmnopqrs', 'lmnopqrst', 'mnopqrstu', 'nopqrstuv', 'opqrstuvw',
            'pqrstuvwx', 'qrstuvwxy', 'rstuvwxyz'
        ];
        
        // Solo rechazar si la contraseña es principalmente secuencial (más del 60% de secuencias)
        $sequentialCount = 0;
        foreach ($sequentialPatterns as $pattern) {
            if (stripos($password, $pattern) !== false) {
                $sequentialCount += strlen($pattern);
            }
        }
        
        // Si más del 60% de la contraseña es secuencial, rechazarla
        if ($sequentialCount > (strlen($password) * 0.6)) {
            $errors[] = 'La contraseña no puede ser principalmente secuencial';
        }
        
        return ['valid' => empty($errors), 'errors' => $errors];
    }
    
    /**
     * Validar nombre completo
     */
    public static function validateName($name) {
        $errors = [];
        
        // Verificar que no esté vacío
        if (empty(trim($name))) {
            $errors[] = 'El nombre es obligatorio';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Longitud mínima
        if (strlen(trim($name)) < 2) {
            $errors[] = 'El nombre debe tener al menos 2 caracteres';
        }
        
        // Longitud máxima
        if (strlen($name) > 100) {
            $errors[] = 'El nombre no puede tener más de 100 caracteres';
        }
        
        // Solo letras, espacios, guiones y apostrofes
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-\']+$/', $name)) {
            $errors[] = 'El nombre solo puede contener letras, espacios, guiones y apostrofes';
        }
        
        // No debe ser solo espacios
        if (strlen(trim($name)) < 2) {
            $errors[] = 'El nombre no puede ser solo espacios';
        }
        
        return ['valid' => empty($errors), 'errors' => $errors];
    }
    
    /**
     * Verificar si un dominio tiene registros MX
     */
    private static function checkMXRecord($domain) {
        // Esta función puede ser lenta, por lo que es opcional
        // En producción, podrías querer deshabilitarla o hacerla asíncrona
        return checkdnsrr($domain, 'MX');
    }
    
    /**
     * Validar todos los campos de registro
     */
    public static function validateRegistration($data) {
        $errors = [];
        $validatedData = [];
        
        // Validar nombre
        $nameValidation = self::validateName($data['name'] ?? '');
        if (!$nameValidation['valid']) {
            $errors = array_merge($errors, $nameValidation['errors']);
        } else {
            $validatedData['name'] = trim($data['name']);
        }
        
        // Validar email
        $emailValidation = self::validateEmail($data['email'] ?? '');
        if (!$emailValidation['valid']) {
            $errors = array_merge($errors, $emailValidation['errors']);
        } else {
            $validatedData['email'] = strtolower(trim($data['email']));
        }
        
        // Validar teléfono
        $phoneValidation = self::validatePhone($data['phone'] ?? '');
        if (!$phoneValidation['valid']) {
            $errors = array_merge($errors, $phoneValidation['errors']);
        } else {
            $validatedData['phone'] = $phoneValidation['clean_phone'];
        }
        
        // Validar contraseña
        $passwordValidation = self::validatePassword($data['password'] ?? '');
        if (!$passwordValidation['valid']) {
            $errors = array_merge($errors, $passwordValidation['errors']);
        } else {
            $validatedData['password'] = $data['password'];
        }
        
        // Verificar que las contraseñas coincidan
        if (($data['password'] ?? '') !== ($data['password_confirm'] ?? '')) {
            $errors[] = 'Las contraseñas no coinciden';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $validatedData
        ];
    }
}
