<?php
/**
 * Servicio de SMS Empresarial Robusto
 * Sistema escalable con múltiples proveedores y fallbacks
 */

class EnterpriseSMSService {
    
    private static $providers = [];
    private static $currentProvider = null;
    private static $retryAttempts = 3;
    private static $retryDelay = 2; // segundos
    private static $rateLimitWindow = 3600; // 1 hora
    private static $maxMessagesPerHour = 100;
    
    /**
     * Inicializar proveedores SMS
     */
    public static function initializeProviders() {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            // Obtener todos los proveedores configurados
            $stmt = $db->prepare("
                SELECT config_key, config_value 
                FROM system_config 
                WHERE config_key LIKE 'sms_provider_%' 
                ORDER BY config_key
            ");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Procesar proveedores
            $providers = [];
            foreach ($results as $key => $value) {
                if (strpos($key, 'sms_provider_') === 0) {
                    $providerData = json_decode($value, true);
                    if ($providerData && isset($providerData['name'])) {
                        $providers[$providerData['name']] = $providerData;
                    }
                }
            }
            
            self::$providers = $providers;
            
            // Seleccionar proveedor principal
            $primaryProvider = self::getConfigValue('sms_primary_provider', 'twilio');
            self::$currentProvider = $primaryProvider;
            
            return true;
        } catch (Exception $e) {
            error_log("Error initializing SMS providers: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar SMS con fallback automático
     */
    public static function sendSMS($phone, $message, $priority = 'normal') {
        // Inicializar proveedores si no están cargados
        if (empty(self::$providers)) {
            self::initializeProviders();
        }
        
        // Validar teléfono
        if (!self::validatePhone($phone)) {
            self::logSMSAttempt($phone, $message, 'invalid_phone', 'Teléfono inválido');
            return false;
        }
        
        // Verificar rate limiting
        if (!self::checkRateLimit($phone)) {
            self::logSMSAttempt($phone, $message, 'rate_limited', 'Rate limit excedido');
            return false;
        }
        
        // Normalizar número de teléfono
        $normalizedPhone = self::normalizePhoneNumber($phone);
        
        // Intentar con proveedor principal
        $attempts = 0;
        $lastError = '';
        
        while ($attempts < self::$retryAttempts) {
            $attempts++;
            
            // Seleccionar proveedor
            $provider = self::selectProvider($priority);
            
            if (!$provider) {
                self::logSMSAttempt($normalizedPhone, $message, 'no_provider', 'No hay proveedores disponibles');
                return false;
            }
            
            // Intentar envío
            $result = self::sendViaProvider($provider, $normalizedPhone, $message);
            
            if ($result['success']) {
                self::logSMSAttempt($normalizedPhone, $message, 'success', "Enviado via {$provider['name']}");
                self::updateRateLimit($normalizedPhone);
                return true;
            }
            
            $lastError = $result['error'];
            self::logSMSAttempt($normalizedPhone, $message, 'failed', "Error via {$provider['name']}: {$lastError}");
            
            // Marcar proveedor como fallido temporalmente
            self::markProviderFailed($provider['name']);
            
            // Esperar antes del siguiente intento
            if ($attempts < self::$retryAttempts) {
                sleep(self::$retryDelay);
            }
        }
        
        // Todos los intentos fallaron
        self::logSMSAttempt($normalizedPhone, $message, 'all_failed', "Todos los proveedores fallaron. Último error: {$lastError}");
        return false;
    }
    
    /**
     * Enviar código OTP empresarial
     */
    public static function sendOTP($phone, $otp, $userName = null, $companyName = null) {
        $companyName = $companyName ?: APP_NAME;
        $userName = $userName ? "Hola {$userName}, " : "";
        
        $message = "{$userName}tu código de verificación para {$companyName} es: {$otp}. Válido por 5 minutos. No compartas este código.";
        
        return self::sendSMS($phone, $message, 'high');
    }
    
    /**
     * Enviar notificación de seguridad por SMS
     */
    public static function sendSecurityNotification($phone, $userName, $event, $details = []) {
        $eventMessages = [
            'login_attempt' => 'Se detectó un intento de inicio de sesión en tu cuenta',
            'password_change' => 'Se cambió la contraseña de tu cuenta',
            'email_change' => 'Se cambió el email de tu cuenta',
            'phone_change' => 'Se cambió el número de teléfono de tu cuenta',
            'suspicious_activity' => 'Se detectó actividad sospechosa en tu cuenta'
        ];
        
        $eventMessage = $eventMessages[$event] ?? 'Se detectó una actividad en tu cuenta';
        $message = "Hola {$userName}, {$eventMessage}. Si no fuiste tú, contacta soporte inmediatamente.";
        
        return self::sendSMS($phone, $message, 'high');
    }
    
    /**
     * Seleccionar proveedor basado en prioridad y estado
     */
    private static function selectProvider($priority = 'normal') {
        // Filtrar proveedores disponibles
        $availableProviders = array_filter(self::$providers, function($provider) {
            return !isset($provider['failed_until']) || $provider['failed_until'] < time();
        });
        
        if (empty($availableProviders)) {
            return null;
        }
        
        // Ordenar por prioridad y confiabilidad
        uasort($availableProviders, function($a, $b) use ($priority) {
            $aScore = self::calculateProviderScore($a, $priority);
            $bScore = self::calculateProviderScore($b, $priority);
            return $bScore - $aScore;
        });
        
        return reset($availableProviders);
    }
    
    /**
     * Calcular score del proveedor
     */
    private static function calculateProviderScore($provider, $priority) {
        $score = 0;
        
        // Score base
        $score += $provider['reliability'] ?? 50;
        
        // Bonus por prioridad
        if ($priority === 'high' && ($provider['priority'] ?? 'normal') === 'high') {
            $score += 20;
        }
        
        // Penalización por fallos recientes
        if (isset($provider['failure_count'])) {
            $score -= $provider['failure_count'] * 5;
        }
        
        return $score;
    }
    
    /**
     * Enviar SMS via proveedor específico
     */
    private static function sendViaProvider($provider, $phone, $message) {
        try {
            switch ($provider['type']) {
                case 'twilio':
                    return self::sendViaTwilio($provider, $phone, $message);
                case 'nexmo':
                    return self::sendViaNexmo($provider, $phone, $message);
                case 'aws_sns':
                    return self::sendViaAWSSNS($provider, $phone, $message);
                default:
                    return ['success' => false, 'error' => 'Tipo de proveedor no soportado'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Enviar via Twilio
     */
    private static function sendViaTwilio($provider, $phone, $message) {
        try {
            $url = "https://api.twilio.com/2010-04-01/Accounts/{$provider['account_sid']}/Messages.json";
            
            $data = [
                'From' => $provider['phone_number'],
                'To' => $phone,
                'Body' => $message
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $provider['account_sid'] . ':' . $provider['auth_token']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                return ['success' => false, 'error' => "cURL error: {$error}"];
            }
            
            if ($httpCode >= 200 && $httpCode < 300) {
                $responseData = json_decode($response, true);
                if (isset($responseData['sid'])) {
                    return ['success' => true, 'error' => '', 'message_id' => $responseData['sid']];
                }
            }
            
            $errorMessage = "HTTP {$httpCode}";
            if ($response) {
                $responseData = json_decode($response, true);
                if (isset($responseData['message'])) {
                    $errorMessage .= ": " . $responseData['message'];
                }
            }
            
            return ['success' => false, 'error' => $errorMessage];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Enviar via Nexmo (Vonage)
     */
    private static function sendViaNexmo($provider, $phone, $message) {
        try {
            $url = "https://rest.nexmo.com/sms/json";
            
            $data = [
                'api_key' => $provider['api_key'],
                'api_secret' => $provider['api_secret'],
                'from' => $provider['from_name'],
                'to' => $phone,
                'text' => $message
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                return ['success' => false, 'error' => "cURL error: {$error}"];
            }
            
            $responseData = json_decode($response, true);
            
            if (isset($responseData['messages']) && count($responseData['messages']) > 0) {
                $messageData = $responseData['messages'][0];
                if ($messageData['status'] == '0') {
                    return ['success' => true, 'error' => '', 'message_id' => $messageData['message-id']];
                } else {
                    return ['success' => false, 'error' => $messageData['error-text']];
                }
            }
            
            return ['success' => false, 'error' => 'Respuesta inválida del proveedor'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Enviar via AWS SNS
     */
    private static function sendViaAWSSNS($provider, $phone, $message) {
        try {
            // Implementar AWS SNS aquí
            // Por ahora, retornar error para indicar que no está implementado
            return ['success' => false, 'error' => 'AWS SNS no implementado aún'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Marcar proveedor como fallido
     */
    private static function markProviderFailed($providerName) {
        if (isset(self::$providers[$providerName])) {
            self::$providers[$providerName]['failed_until'] = time() + 300; // 5 minutos
            self::$providers[$providerName]['failure_count'] = (self::$providers[$providerName]['failure_count'] ?? 0) + 1;
        }
    }
    
    /**
     * Validar teléfono
     */
    private static function validatePhone($phone) {
        // Remover caracteres no numéricos excepto +
        $cleaned = preg_replace('/[^\d+]/', '', $phone);
        
        // Verificar que tenga al menos 10 dígitos
        $digits = preg_replace('/[^\d]/', '', $cleaned);
        return strlen($digits) >= 10;
    }
    
    /**
     * Normalizar número de teléfono
     */
    private static function normalizePhoneNumber($phone) {
        // Remover caracteres no numéricos excepto +
        $cleaned = preg_replace('/[^\d+]/', '', $phone);
        
        // Si no empieza con +, agregar código de país por defecto
        if (!str_starts_with($cleaned, '+')) {
            $cleaned = '+1' . $cleaned; // Código de país por defecto
        }
        
        return $cleaned;
    }
    
    /**
     * Verificar rate limiting
     */
    private static function checkRateLimit($phone) {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                SELECT COUNT(*) as count 
                FROM sms_logs 
                WHERE to_phone = ? 
                AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
                AND status = 'success'
            ");
            $stmt->execute([$phone, self::$rateLimitWindow]);
            $result = $stmt->fetch();
            
            return $result['count'] < self::$maxMessagesPerHour;
            
        } catch (Exception $e) {
            error_log("Error checking rate limit: " . $e->getMessage());
            return true; // Permitir en caso de error
        }
    }
    
    /**
     * Actualizar rate limiting
     */
    private static function updateRateLimit($phone) {
        // El rate limiting se actualiza automáticamente con el log
        return true;
    }
    
    /**
     * Obtener valor de configuración
     */
    private static function getConfigValue($key, $default = null) {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("SELECT config_value FROM system_config WHERE config_key = ?");
            $stmt->execute([$key]);
            $result = $stmt->fetch();
            
            return $result ? $result['config_value'] : $default;
        } catch (Exception $e) {
            return $default;
        }
    }
    
    /**
     * Log de intentos de SMS
     */
    private static function logSMSAttempt($phone, $message, $status, $errorMessage) {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                INSERT INTO sms_logs (to_phone, message, status, error_message, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$phone, $message, $status, $errorMessage]);
        } catch (Exception $e) {
            error_log("Error logging SMS attempt: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener estadísticas de SMS
     */
    public static function getSMSStats($hours = 24) {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                SELECT 
                    status,
                    COUNT(*) as count,
                    DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour
                FROM sms_logs 
                WHERE created_at > DATE_SUB(NOW(), INTERVAL ? HOUR)
                GROUP BY status, hour
                ORDER BY hour DESC
            ");
            $stmt->execute([$hours]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error getting SMS stats: " . $e->getMessage());
            return [];
        }
    }
}
?>
