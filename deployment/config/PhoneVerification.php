<?php
/**
 * Sistema de verificación de teléfono con OTP
 */

class PhoneVerification {
    
    /**
     * Generar código OTP
     */
    public static function generateOTP() {
        return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Enviar código OTP por SMS
     */
    public static function sendOTP($phone, $userId) {
        try {
            $otp = self::generateOTP();
            $expiresAt = date('Y-m-d H:i:s', time() + 300); // 5 minutos
            
            // Guardar código en la base de datos
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                UPDATE users 
                SET phone_verification_code = ?,
                    phone_verification_expires_at = ?
                WHERE id = ? AND phone = ?
            ");
            
            $result = $stmt->execute([$otp, $expiresAt, $userId, $phone]);
            
            if (!$result) {
                return false;
            }
            
            // Enviar SMS usando servicio empresarial
            require_once 'config/EnterpriseSMSService.php';
            $smsService = new EnterpriseSMSService();
            $smsResult = $smsService->sendOTP($phone, $otp);
            $smsSent = $smsResult['success'] ?? false;
            
            if ($smsSent) {
                self::logVerification('phone', $phone, 'sent', ['user_id' => $userId]);
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error sending OTP: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar SMS usando Twilio
     */
    private static function sendSMS($phone, $otp) {
        try {
            require_once 'config/SMSService.php';
            
            $result = SMSService::sendOTP($phone, $otp);
            
            if ($result) {
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error sending SMS: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar código OTP
     */
    public static function verifyOTP($phone, $otp, $userId) {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            // Buscar usuario con código válido
            $stmt = $db->prepare("
                SELECT id, phone, phone_verification_expires_at 
                FROM users 
                WHERE phone = ? 
                AND phone_verification_code = ?
                AND phone_verification_expires_at > NOW()
                AND id = ?
                AND account_status = 'pending_phone'
            ");
            
            $stmt->execute([$phone, $otp, $userId]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Activar cuenta completamente
                $updateStmt = $db->prepare("
                    UPDATE users 
                    SET phone_verified_at = NOW(),
                        phone_verification_code = NULL,
                        phone_verification_expires_at = NULL,
                        account_status = 'active'
                    WHERE id = ?
                ");
                
                $updateStmt->execute([$user['id']]);
                
                self::logVerification('phone', $phone, 'verified', ['user_id' => $user['id']]);
                
                return [
                    'success' => true,
                    'message' => 'Teléfono verificado correctamente'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Código inválido o expirado'
            ];
        } catch (Exception $e) {
            error_log("Error verifying OTP: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
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
                WHERE config_key IN ('twilio_account_sid', 'twilio_auth_token', 'twilio_phone_number')
                ORDER BY config_key
            ");
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($results) === 3) {
                return [
                    'account_sid' => $results[0],
                    'auth_token' => $results[1],
                    'phone_number' => $results[2]
                ];
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error getting Twilio config: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Registrar log de verificación
     */
    private static function logVerification($type, $identifier, $status, $details = []) {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $logId = uniqid();
            $stmt = $db->prepare("
                INSERT INTO verification_logs (id, user_id, type, status, details, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $logId,
                $details['user_id'] ?? null,
                $type,
                $status,
                json_encode($details),
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (Exception $e) {
            error_log("Error logging verification: " . $e->getMessage());
        }
    }
    
    /**
     * Limpiar códigos OTP expirados
     */
    public static function cleanExpiredCodes() {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                UPDATE users 
                SET phone_verification_code = NULL,
                    phone_verification_expires_at = NULL
                WHERE phone_verification_expires_at < NOW()
                AND account_status = 'pending_phone'
            ");
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error cleaning expired codes: " . $e->getMessage());
            return false;
        }
    }
}
