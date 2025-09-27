<?php
/**
 * Validación usando servicios externos
 */

class ExternalValidation {
    
    /**
     * Validar email usando Kickbox
     */
    public static function validateEmailWithKickbox($email) {
        try {
            $apiKey = self::getConfig('kickbox_api_key');
            
            if (!$apiKey || empty($apiKey)) {
                return ['valid' => true, 'reason' => 'no_api_key'];
            }
            
            $url = "https://api.kickbox.com/v2/verify?email=" . urlencode($email) . "&apikey=" . $apiKey;
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $data = json_decode($response, true);
                
                if ($data && isset($data['result'])) {
                    $result = $data['result'];
                    
                    // Aceptar solo emails válidos y entregables
                    if ($result === 'deliverable') {
                        return ['valid' => true, 'reason' => 'deliverable'];
                    } elseif ($result === 'undeliverable') {
                        return ['valid' => false, 'reason' => 'undeliverable'];
                    } elseif ($result === 'risky') {
                        return ['valid' => false, 'reason' => 'risky'];
                    } elseif ($result === 'unknown') {
                        return ['valid' => true, 'reason' => 'unknown'];
                    }
                }
            }
            
            return ['valid' => true, 'reason' => 'api_error'];
        } catch (Exception $e) {
            error_log("Kickbox validation error: " . $e->getMessage());
            return ['valid' => true, 'reason' => 'exception'];
        }
    }
    
    /**
     * Validar teléfono usando Twilio Lookup
     */
    public static function validatePhoneWithTwilio($phone) {
        try {
            $config = self::getTwilioConfig();
            
            if (!$config || empty($config['account_sid']) || empty($config['auth_token'])) {
                return ['valid' => true, 'reason' => 'no_config'];
            }
            
            $url = "https://lookups.twilio.com/v1/PhoneNumbers/" . urlencode($phone);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_USERPWD, $config['account_sid'] . ':' . $config['auth_token']);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $data = json_decode($response, true);
                
                if ($data && isset($data['phone_number'])) {
                    return ['valid' => true, 'reason' => 'valid', 'formatted' => $data['phone_number']];
                }
            } elseif ($httpCode === 404) {
                return ['valid' => false, 'reason' => 'not_found'];
            }
            
            return ['valid' => true, 'reason' => 'api_error'];
        } catch (Exception $e) {
            error_log("Twilio validation error: " . $e->getMessage());
            return ['valid' => true, 'reason' => 'exception'];
        }
    }
    
    /**
     * Verificar reCAPTCHA
     */
    public static function verifyRecaptcha($recaptchaResponse, $ipAddress) {
        try {
            $secretKey = self::getConfig('recaptcha_secret_key');
            
            if (!$secretKey || empty($secretKey)) {
                return ['valid' => true, 'reason' => 'no_secret_key'];
            }
            
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = [
                'secret' => $secretKey,
                'response' => $recaptchaResponse,
                'remoteip' => $ipAddress
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $data = json_decode($response, true);
                
                if ($data && isset($data['success'])) {
                    if ($data['success']) {
                        $score = $data['score'] ?? 0.5;
                        $minScore = 0.5; // Puntuación mínima aceptable
                        
                        if ($score >= $minScore) {
                            return ['valid' => true, 'score' => $score];
                        } else {
                            return ['valid' => false, 'reason' => 'low_score', 'score' => $score];
                        }
                    } else {
                        return ['valid' => false, 'reason' => 'failed', 'errors' => $data['error-codes'] ?? []];
                    }
                }
            }
            
            return ['valid' => true, 'reason' => 'api_error'];
        } catch (Exception $e) {
            error_log("reCAPTCHA verification error: " . $e->getMessage());
            return ['valid' => true, 'reason' => 'exception'];
        }
    }
    
    /**
     * Obtener configuración de Twilio
     */
    private static function getTwilioConfig() {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                SELECT config_value 
                FROM system_config 
                WHERE config_key IN ('twilio_account_sid', 'twilio_auth_token')
                ORDER BY config_key
            ");
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($results) === 2) {
                return [
                    'account_sid' => $results[0],
                    'auth_token' => $results[1]
                ];
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error getting Twilio config: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener configuración del sistema
     */
    private static function getConfig($key) {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                SELECT config_value 
                FROM system_config 
                WHERE config_key = ?
            ");
            
            $stmt->execute([$key]);
            $result = $stmt->fetch();
            
            return $result ? $result['config_value'] : false;
        } catch (Exception $e) {
            error_log("Error getting config: " . $e->getMessage());
            return false;
        }
    }
}
