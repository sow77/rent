<?php
/**
 * Servicio de SMS con Twilio
 */

class SMSService {
    
    /**
     * Enviar SMS usando Twilio
     */
    public static function sendSMS($phone, $message) {
        try {
            $config = self::getTwilioConfig();
            
            if (!$config) {
                error_log("Twilio configuration not found");
                return false;
            }
            
            $url = "https://api.twilio.com/2010-04-01/Accounts/" . $config['account_sid'] . "/Messages.json";
            
            $data = [
                'From' => $config['phone_number'],
                'To' => $phone,
                'Body' => $message
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $config['account_sid'] . ':' . $config['auth_token']);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 201) {
                error_log("SMS sent successfully to {$phone}");
                return true;
            } else {
                error_log("Twilio SMS error (HTTP {$httpCode}): " . $response);
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Error sending SMS: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar código OTP por SMS
     */
    public static function sendOTP($phone, $otp, $userName = null) {
        $message = "Tu código de verificación para " . APP_NAME . " es: " . $otp . ". Válido por 5 minutos.";
        
        if ($userName) {
            $message = "Hola " . $userName . ", " . $message;
        }
        
        return self::sendSMS($phone, $message);
    }
    
    /**
     * Obtener configuración de Twilio
     */
    private static function getTwilioConfig() {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                SELECT config_key, config_value 
                FROM system_config 
                WHERE config_key IN ('twilio_account_sid', 'twilio_auth_token', 'twilio_phone_number')
                ORDER BY config_key
            ");
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            if (count($results) === 3 && 
                !empty($results['twilio_account_sid']) && 
                !empty($results['twilio_auth_token']) && 
                !empty($results['twilio_phone_number'])) {
                
                return [
                    'account_sid' => $results['twilio_account_sid'],
                    'auth_token' => $results['twilio_auth_token'],
                    'phone_number' => $results['twilio_phone_number']
                ];
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error getting Twilio config: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Configurar Twilio
     */
    public static function configureTwilio($accountSid, $authToken, $phoneNumber) {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $configs = [
                'twilio_account_sid' => $accountSid,
                'twilio_auth_token' => $authToken,
                'twilio_phone_number' => $phoneNumber
            ];
            
            foreach ($configs as $key => $value) {
                $stmt = $db->prepare("
                    INSERT INTO system_config (config_key, config_value, created_at, updated_at) 
                    VALUES (?, ?, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE config_value = VALUES(config_value), updated_at = NOW()
                ");
                $stmt->execute([$key, $value]);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error configuring Twilio: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar configuración de Twilio
     */
    public static function testConfiguration() {
        try {
            $config = self::getTwilioConfig();
            
            if (!$config) {
                return [
                    'success' => false,
                    'message' => 'Configuración de Twilio no encontrada'
                ];
            }
            
            // Hacer una petición de prueba a la API de Twilio
            $url = "https://api.twilio.com/2010-04-01/Accounts/" . $config['account_sid'] . ".json";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $config['account_sid'] . ':' . $config['auth_token']);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                return [
                    'success' => true,
                    'message' => 'Configuración de Twilio válida'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Credenciales de Twilio inválidas (HTTP ' . $httpCode . ')'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error probando configuración: ' . $e->getMessage()
            ];
        }
    }
}
?>
