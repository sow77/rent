<?php
/**
 * Servicio de Seguridad Empresarial
 * Sistema robusto de seguridad para aplicaciones financieras
 */

class EnterpriseSecurityService {
    
    private static $encryptionKey = null;
    private static $rateLimitCache = [];
    private static $fraudDetectionRules = [];
    
    /**
     * Inicializar servicio de seguridad
     */
    public static function initialize() {
        try {
            // Cargar clave de encriptación
            self::loadEncryptionKey();
            
            // Cargar reglas de detección de fraude
            self::loadFraudDetectionRules();
            
            return true;
        } catch (Exception $e) {
            error_log("Error initializing security service: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crear token de verificación
     */
    public static function createVerificationToken($userId, $email) {
        try {
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 86400); // 24 horas
            
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                INSERT INTO verification_tokens (user_id, token, type, expires_at, created_at) 
                VALUES (?, ?, 'email', ?, NOW())
            ");
            $stmt->execute([$userId, $token, $expiresAt]);
            
            return $token;
        } catch (Exception $e) {
            error_log("Error creating verification token: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar token de verificación
     */
    public static function verifyToken($token, $type = 'email') {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                SELECT user_id, expires_at 
                FROM verification_tokens 
                WHERE token = ? AND type = ? AND used_at IS NULL
            ");
            $stmt->execute([$token, $type]);
            $result = $stmt->fetch();
            
            if (!$result) {
                return ['success' => false, 'message' => 'Token inválido'];
            }
            
            if (strtotime($result['expires_at']) < time()) {
                return ['success' => false, 'message' => 'Token expirado'];
            }
            
            // Marcar token como usado
            $stmt = $db->prepare("
                UPDATE verification_tokens 
                SET used_at = NOW() 
                WHERE token = ?
            ");
            $stmt->execute([$token]);
            
            return ['success' => true, 'user_id' => $result['user_id']];
            
        } catch (Exception $e) {
            error_log("Error verifying token: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno'];
        }
    }
    
    /**
     * Obtener instancia (para compatibilidad)
     */
    public static function getInstance() {
        return new self();
    }
    
    /**
     * Crear token de reset de contraseña
     */
    public static function createPasswordResetToken($userId, $email) {
        try {
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hora
            
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                INSERT INTO verification_tokens (user_id, token, type, expires_at, created_at) 
                VALUES (?, ?, 'password_reset', ?, NOW())
            ");
            $stmt->execute([$userId, $token, $expiresAt]);
            
            return $token;
        } catch (Exception $e) {
            error_log("Error creating password reset token: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar token de reset de contraseña
     */
    public static function validateResetToken($token) {
        return self::verifyToken($token, 'password_reset');
    }
    
    /**
     * Resetear contraseña
     */
    public static function resetPassword($token, $newPassword) {
        try {
            $tokenResult = self::validateResetToken($token);
            
            if (!$tokenResult['success']) {
                return $tokenResult;
            }
            
            $userId = $tokenResult['user_id'];
            $hashedPassword = self::hashPassword($newPassword);
            
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                UPDATE users 
                SET password = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$hashedPassword, $userId]);
            
            return ['success' => true, 'message' => 'Contraseña actualizada correctamente'];
            
        } catch (Exception $e) {
            error_log("Error resetting password: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno'];
        }
    }
    
    /**
     * Hash de contraseña con Argon2ID
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,       // 4 iteraciones
            'threads' => 3          // 3 hilos
        ]);
    }
    
    /**
     * Verificar contraseña
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Generar token seguro
     */
    public static function generateSecureToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Encriptar datos sensibles
     */
    public static function encrypt($data) {
        try {
            $key = self::getEncryptionKey();
            $iv = random_bytes(16);
            $encrypted = openssl_encrypt($data, 'AES-256-GCM', $key, 0, $iv, $tag);
            return base64_encode($iv . $tag . $encrypted);
        } catch (Exception $e) {
            error_log("Error encrypting data: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Desencriptar datos sensibles
     */
    public static function decrypt($encryptedData) {
        try {
            $data = base64_decode($encryptedData);
            $iv = substr($data, 0, 16);
            $tag = substr($data, 16, 16);
            $encrypted = substr($data, 32);
            $key = self::getEncryptionKey();
            return openssl_decrypt($encrypted, 'AES-256-GCM', $key, 0, $iv, $tag);
        } catch (Exception $e) {
            error_log("Error decrypting data: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener clave de encriptación
     */
    private static function getEncryptionKey() {
        if (self::$encryptionKey === null) {
            self::loadEncryptionKey();
        }
        return self::$encryptionKey;
    }
    
    /**
     * Cargar clave de encriptación
     */
    private static function loadEncryptionKey() {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("SELECT config_value FROM system_config WHERE config_key = 'encryption_key'");
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result) {
                self::$encryptionKey = base64_decode($result['config_value']);
            } else {
                // Generar nueva clave
                $key = random_bytes(32);
                $stmt = $db->prepare("INSERT INTO system_config (config_key, config_value, config_type, is_encrypted) VALUES ('encryption_key', ?, 'string', 1)");
                $stmt->execute([base64_encode($key)]);
                self::$encryptionKey = $key;
            }
        } catch (Exception $e) {
            error_log("Error loading encryption key: " . $e->getMessage());
            self::$encryptionKey = random_bytes(32);
        }
    }
    
    /**
     * Cargar reglas de detección de fraude
     */
    private static function loadFraudDetectionRules() {
        self::$fraudDetectionRules = [
            'max_login_attempts' => 5,
            'max_password_reset_attempts' => 3,
            'suspicious_ip_threshold' => 10,
            'unusual_location_threshold' => 0.8
        ];
    }
    
    /**
     * Detectar fraude
     */
    public static function detectFraud($userId, $ipAddress, $action) {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            // Verificar intentos de login
            if ($action === 'login') {
                $stmt = $db->prepare("
                    SELECT COUNT(*) as attempts 
                    FROM security_logs 
                    WHERE user_id = ? AND event_type = 'failed_login' 
                    AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                ");
                $stmt->execute([$userId]);
                $result = $stmt->fetch();
                
                if ($result['attempts'] >= self::$fraudDetectionRules['max_login_attempts']) {
                    return ['fraud_detected' => true, 'reason' => 'Exceso de intentos de login'];
                }
            }
            
            // Verificar IP sospechosa
            $stmt = $db->prepare("
                SELECT COUNT(DISTINCT user_id) as unique_users 
                FROM security_logs 
                WHERE ip_address = ? 
                AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");
            $stmt->execute([$ipAddress]);
            $result = $stmt->fetch();
            
            if ($result['unique_users'] >= self::$fraudDetectionRules['suspicious_ip_threshold']) {
                return ['fraud_detected' => true, 'reason' => 'IP sospechosa'];
            }
            
            return ['fraud_detected' => false];
            
        } catch (Exception $e) {
            error_log("Error detecting fraud: " . $e->getMessage());
            return ['fraud_detected' => false];
        }
    }
    
    /**
     * Aplicar rate limiting
     */
    public static function applyRateLimiting($identifier, $action, $limit = 10, $window = 3600) {
        $key = $identifier . ':' . $action;
        $now = time();
        
        if (!isset(self::$rateLimitCache[$key])) {
            self::$rateLimitCache[$key] = [];
        }
        
        // Limpiar entradas antiguas
        self::$rateLimitCache[$key] = array_filter(
            self::$rateLimitCache[$key],
            function($timestamp) use ($now, $window) {
                return ($now - $timestamp) < $window;
            }
        );
        
        // Verificar límite
        if (count(self::$rateLimitCache[$key]) >= $limit) {
            return false;
        }
        
        // Agregar nueva entrada
        self::$rateLimitCache[$key][] = $now;
        return true;
    }
    
    /**
     * Verificar rate limiting
     */
    public static function checkRateLimit($identifier, $action, $limit = 10, $window = 3600) {
        return self::applyRateLimiting($identifier, $action, $limit, $window);
    }
    
    /**
     * Log de evento de seguridad
     */
    public static function logSecurityEvent($eventType, $userId = null, $ipAddress = null, $data = []) {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                INSERT INTO security_logs (event_type, user_id, ip_address, data, severity, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $severity = self::getEventSeverity($eventType);
            $stmt->execute([
                $eventType,
                $userId,
                $ipAddress,
                json_encode($data),
                $severity
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log("Error logging security event: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener severidad del evento
     */
    private static function getEventSeverity($eventType) {
        $severityMap = [
            'login_success' => 'low',
            'login_failed' => 'medium',
            'password_reset' => 'medium',
            'suspicious_activity' => 'high',
            'fraud_detected' => 'critical'
        ];
        
        return $severityMap[$eventType] ?? 'medium';
    }
    
    /**
     * Log de auditoría
     */
    public static function logAuditTrail($userId, $action, $resourceType = null, $resourceId = null, $oldValues = null, $newValues = null) {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                INSERT INTO audit_logs (user_id, action, resource_type, resource_id, old_values, new_values, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $userId,
                $action,
                $resourceType,
                $resourceId,
                $oldValues ? json_encode($oldValues) : null,
                $newValues ? json_encode($newValues) : null,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log("Error logging audit trail: " . $e->getMessage());
            return false;
        }
    }
}
?>
