<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/config.php';

class PasswordReset {
    private $db;
    private const TOKEN_LENGTH = 64;
    private const TOKEN_EXPIRY_HOURS = 24; // 24 horas

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crear un token de recuperación de contraseña
     */
    public function createResetToken($email) {
        try {
            // Verificar que el usuario existe
            $user = $this->getUserByEmail($email);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'No existe una cuenta con ese email'
                ];
            }

            // Invalidar tokens anteriores del usuario
            $this->invalidateUserTokens($user['id']);

            // Generar nuevo token
            $token = $this->generateSecureToken();
            $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::TOKEN_EXPIRY_HOURS . ' hours'));

            // Guardar token en la base de datos
            $sql = "INSERT INTO password_reset_tokens (user_id, token, email, expires_at) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$user['id'], $token, $email, $expiresAt]);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Token creado exitosamente',
                    'data' => [
                        'token' => $token,
                        'expires_at' => $expiresAt,
                        'user' => $user
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear el token'
                ];
            }

        } catch (Exception $e) {
            error_log("Error en createResetToken: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        }
    }

    /**
     * Validar un token de recuperación
     */
    public function validateToken($token) {
        try {
            $sql = "SELECT * FROM password_reset_tokens 
                    WHERE token = ? AND expires_at > NOW() AND used_at IS NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$token]);
            $tokenData = $stmt->fetch();

            if (!$tokenData) {
                return [
                    'success' => false,
                    'message' => 'Token inválido o expirado'
                ];
            }

            return [
                'success' => true,
                'message' => 'Token válido',
                'data' => $tokenData
            ];

        } catch (Exception $e) {
            error_log("Error en validateToken: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        }
    }

    /**
     * Resetear la contraseña usando un token
     */
    public function resetPassword($token, $newPassword) {
        try {
            // Validar token
            $tokenValidation = $this->validateToken($token);
            if (!$tokenValidation['success']) {
                return $tokenValidation;
            }

            $tokenData = $tokenValidation['data'];

            // Hash de la nueva contraseña
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Actualizar contraseña del usuario
            $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$hashedPassword, $tokenData['user_id']]);

            if ($result) {
                // Marcar token como usado
                $this->markTokenAsUsed($token);
                
                return [
                    'success' => true,
                    'message' => 'Contraseña actualizada exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar la contraseña'
                ];
            }

        } catch (Exception $e) {
            error_log("Error en resetPassword: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        }
    }

    /**
     * Obtener usuario por email
     */
    private function getUserByEmail($email) {
        $sql = "SELECT id, email, name FROM users WHERE email = ? AND active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Generar token seguro
     */
    private function generateSecureToken() {
        return bin2hex(random_bytes(self::TOKEN_LENGTH / 2));
    }

    /**
     * Invalidar tokens anteriores del usuario
     */
    private function invalidateUserTokens($userId) {
        $sql = "UPDATE password_reset_tokens SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
    }

    /**
     * Marcar token como usado
     */
    private function markTokenAsUsed($token) {
        $sql = "UPDATE password_reset_tokens SET used_at = NOW() WHERE token = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);
    }

    /**
     * Limpiar tokens expirados (para mantenimiento)
     */
    public function cleanExpiredTokens() {
        try {
            $sql = "DELETE FROM password_reset_tokens WHERE expires_at < NOW()";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute();
            
            return [
                'success' => true,
                'message' => 'Tokens expirados limpiados',
                'deleted_count' => $stmt->rowCount()
            ];
        } catch (Exception $e) {
            error_log("Error en cleanExpiredTokens: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al limpiar tokens expirados'
            ];
        }
    }
}
?>
