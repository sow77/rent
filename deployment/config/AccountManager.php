<?php
/**
 * Gestor de cuentas con deshabilitación automática
 * Deshabilita cuentas que no verifiquen su email en 24 horas
 */

class AccountManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Crear cuenta con estado pendiente de verificación
     */
    public function createPendingAccount($userId, $email) {
        try {
            $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            $query = "UPDATE users SET 
                account_status = 'pending_verification', 
                verification_expires_at = :expires_at 
                WHERE id = :user_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':expires_at', $expiresAt);
            $stmt->bindParam(':user_id', $userId);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error creating pending account: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Activar cuenta después de verificar email
     */
    public function activateAccount($userId) {
        try {
            $query = "UPDATE users SET 
                account_status = 'active', 
                email_verified = 1, 
                verification_expires_at = NULL 
                WHERE id = :user_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error activating account: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Deshabilitar cuentas expiradas
     */
    public function disableExpiredAccounts() {
        try {
            $query = "UPDATE users SET 
                account_status = 'disabled', 
                disabled_at = NOW() 
                WHERE account_status = 'pending_verification' 
                AND verification_expires_at < NOW()";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Error disabling expired accounts: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Verificar si una cuenta está activa
     */
    public function isAccountActive($userId) {
        try {
            $query = "SELECT account_status, verification_expires_at FROM users WHERE id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return false;
            }
            
            // Si está pendiente de verificación, verificar si no ha expirado
            if ($user['account_status'] === 'pending_verification') {
                if ($user['verification_expires_at'] && strtotime($user['verification_expires_at']) < time()) {
                    // Deshabilitar cuenta expirada
                    $this->disableExpiredAccounts();
                    return false;
                }
            }
            
            return $user['account_status'] === 'active';
        } catch (Exception $e) {
            error_log("Error checking account status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener estado de la cuenta
     */
    public function getAccountStatus($userId) {
        try {
            $query = "SELECT account_status, verification_expires_at, disabled_at FROM users WHERE id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting account status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Limpiar cuentas deshabilitadas antiguas (más de 7 días)
     */
    public function cleanDisabledAccounts() {
        try {
            $query = "DELETE FROM users WHERE 
                account_status = 'disabled' 
                AND disabled_at < DATE_SUB(NOW(), INTERVAL 7 DAY)";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Error cleaning disabled accounts: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtener estadísticas de cuentas
     */
    public function getAccountStats() {
        try {
            $query = "SELECT 
                account_status, 
                COUNT(*) as count 
                FROM users 
                GROUP BY account_status";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting account stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Reenviar email de verificación (si la cuenta aún no ha expirado)
     */
    public function resendVerificationEmail($userId) {
        try {
            $query = "SELECT * FROM users WHERE id = :user_id AND account_status = 'pending_verification'";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return false;
            }
            
            // Verificar si no ha expirado
            if ($user['verification_expires_at'] && strtotime($user['verification_expires_at']) < time()) {
                return false;
            }
            
            // Crear nuevo token de verificación
            require_once 'config/EmailVerification.php';
            $emailVerification = new EmailVerification();
            $token = $emailVerification->createVerificationToken($userId, $user['email']);
            
            if ($token) {
                return $emailVerification->sendVerificationEmail($user['email'], $token, $user['name']);
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error resending verification email: " . $e->getMessage());
            return false;
        }
    }
}
?>
