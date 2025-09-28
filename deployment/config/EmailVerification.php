<?php
/**
 * Sistema de verificación de email con tokens
 */

class EmailVerification {
    
    /**
     * Crear token de verificación para email
     */
    public static function createVerificationToken($userId, $email) {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            // Generar token único
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hora
            
            // Guardar token en la base de datos
            $stmt = $db->prepare("
                UPDATE users 
                SET verification_token = ?, 
                    verification_token_expires_at = ?,
                    account_status = 'pending_verification'
                WHERE id = ? AND email = ?
            ");
            
            $result = $stmt->execute([$token, $expiresAt, $userId, $email]);
            
            if ($result) {
                return $token;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error creating verification token: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar email de verificación
     */
    public static function sendVerificationEmail($email, $token, $userName) {
        try {
            // Cargar PHPMailer
            require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
            require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
            require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
            
            // Obtener configuración SMTP
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT config_value FROM system_config WHERE config_key = 'smtp_username'");
            $stmt->execute();
            $smtpUser = $stmt->fetch()['config_value'];
            
            $stmt = $db->prepare("SELECT config_value FROM system_config WHERE config_key = 'smtp_password'");
            $stmt->execute();
            $smtpPass = $stmt->fetch()['config_value'];
            
            // Crear instancia de PHPMailer
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Configurar SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            // Configurar email de verificación
            $mail->setFrom($smtpUser, 'Dev Rent - Sistema de Verificación');
            $mail->addAddress($email, $userName);
            
            $mail->isHTML(true);
            $mail->Subject = 'Verificación de Email - Dev Rent';
            
            // Normalizar email (trim espacios y puntos accidentales)
            $normalizedEmail = trim($email);
            $normalizedEmail = preg_replace('/\s+/', '', $normalizedEmail);
            // Evitar casos como "nombre.@dominio.com"
            $normalizedEmail = preg_replace('/\.@/', '@', $normalizedEmail);

            $userId = self::getUserIdByEmail($normalizedEmail);
            $verificationUrl = APP_URL . "/verify-email?token=$token" . ($userId ? "&user_id=$userId" : "");
            
            $mail->Body = "
            <html>
            <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center;'>
                    <h1 style='margin: 0; font-size: 24px;'>🔐 Verificación de Email</h1>
                    <p style='margin: 10px 0 0 0; opacity: 0.9;'>Dev Rent - Sistema Empresarial</p>
                </div>
                
                <div style='background: white; padding: 30px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 10px 10px;'>
                    <h2 style='color: #2c3e50; margin-top: 0;'>¡Hola $userName!</h2>
                    
                    <p style='color: #555; line-height: 1.6; font-size: 16px;'>
                        Gracias por registrarte en <strong>Dev Rent</strong>. Para completar tu registro y activar tu cuenta, 
                        necesitas verificar tu dirección de email.
                    </p>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$verificationUrl' 
                           style='background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); 
                                  color: white; 
                                  padding: 15px 30px; 
                                  text-decoration: none; 
                                  border-radius: 8px; 
                                  font-weight: bold; 
                                  font-size: 16px;
                                  display: inline-block;
                                  box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);'>
                            ✅ Verificar Mi Email
                        </a>
                    </div>
                    
                    <p style='color: #666; font-size: 14px; margin-top: 30px;'>
                        Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
                        <a href='$verificationUrl' style='color: #3498db; word-break: break-all;'>$verificationUrl</a>
                    </p>
                    
                    <div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 20px; border-left: 4px solid #3498db;'>
                        <p style='margin: 0; color: #555; font-size: 14px;'>
                            <strong>⚠️ Importante:</strong> Este enlace expirará en 1 hora por seguridad. 
                            Si no verificas tu email en este tiempo, deberás solicitar un nuevo enlace.
                        </p>
                    </div>
                </div>
                
                <div style='text-align: center; margin-top: 20px; color: #999; font-size: 12px;'>
                    <p>Este email fue enviado automáticamente por el sistema Dev Rent.</p>
                    <p>Si no te registraste en nuestro servicio, puedes ignorar este mensaje.</p>
                </div>
            </body>
            </html>
            ";
            
            // Enviar email
            $mail->send();
            
            // Log de verificación (incluir user_id y token para trazabilidad)
            self::logVerification('email', $email, 'sent', [
                'user_id' => $userId,
                'user_name' => $userName,
                'token' => $token
            ]);
            return true;
            
        } catch (Exception $e) {
            error_log("Error sending verification email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar token de email
     */
    public static function verifyToken($token) {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            // Buscar usuario con token válido (incluyendo ya verificados)
            $stmt = $db->prepare("
                SELECT id, email, name, verification_token_expires_at, account_status
                FROM users 
                WHERE verification_token = ? 
                AND verification_token_expires_at > NOW()
            ");
            $stmt->execute([$token]);
            $user = $stmt->fetch();

            // Fallback: si no está en users, intentar resolverlo desde logs recientes (última hora)
            if (!$user) {
                $logStmt = $db->prepare("
                    SELECT details
                    FROM verification_logs
                    WHERE type = 'email' AND status = 'sent'
                    AND created_at > (NOW() - INTERVAL 1 HOUR)
                    ORDER BY created_at DESC
                    LIMIT 50
                ");
                $logStmt->execute();
                while ($row = $logStmt->fetch()) {
                    $details = json_decode($row['details'] ?? '{}', true);
                    if (isset($details['token']) && hash_equals($token, (string)$details['token'])) {
                        // Cargar el usuario por user_id del log
                        if (!empty($details['user_id'])) {
                            $uStmt = $db->prepare("SELECT id, email, name, account_status FROM users WHERE id = ?");
                            $uStmt->execute([$details['user_id']]);
                            $candidate = $uStmt->fetch();
                            if ($candidate) {
                                $user = $candidate;
                                break;
                            }
                        }
                    }
                }
            }
            
            if ($user) {
                // Refuerzo: exigir que exista un log de envío asociado antes de activar
                try {
                    $logStmt = $db->prepare("
                        SELECT details
                        FROM verification_logs
                        WHERE user_id = ? AND type = 'email' AND status = 'sent'
                        ORDER BY created_at DESC
                        LIMIT 5
                    ");
                    $logStmt->execute([$user['id']]);
                    $hasMatchingSent = false;
                    while ($row = $logStmt->fetch()) {
                        $details = json_decode($row['details'] ?? '{}', true);
                        if (isset($details['token']) && hash_equals($token, (string)$details['token'])) {
                            $hasMatchingSent = true;
                            break;
                        }
                    }
                    if (!$hasMatchingSent) {
                        return [
                            'success' => false,
                            'message' => 'No se encontró un envío válido asociado a este token'
                        ];
                    }
                } catch (Exception $inner) {
                    // Si falla el chequeo de logs por alguna razón, no activar por seguridad
                    return [
                        'success' => false,
                        'message' => 'No se pudo validar el origen del token'
                    ];
                }
                // Verificar si ya está verificado
                if ($user['account_status'] === 'active') {
                    return [
                        'success' => true,
                        'user' => $user,
                        'message' => 'Email ya verificado anteriormente'
                    ];
                }
                
                // Activar cuenta
                $updateStmt = $db->prepare("
                    UPDATE users 
                    SET email_verified_at = NOW(),
                        verification_token = NULL,
                        verification_token_expires_at = NULL,
                        account_status = 'active',
                        active = 1
                    WHERE id = ?
                ");
                
                $updateStmt->execute([$user['id']]);
                
                self::logVerification('email', $user['email'], 'verified', ['user_id' => $user['id']]);
                
                return [
                    'success' => true,
                    'user' => $user
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Token inválido o expirado'
            ];
        } catch (Exception $e) {
            error_log("Error verifying token: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        }
    }
    
    /**
     * Obtener ID de usuario por email
     */
    private static function getUserIdByEmail($email) {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            return $user ? $user['id'] : null;
        } catch (Exception $e) {
            error_log("Error getting user ID by email: " . $e->getMessage());
            return null;
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
     * Limpiar tokens expirados
     */
    public static function cleanExpiredTokens() {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                UPDATE users 
                SET verification_token = NULL,
                    verification_token_expires_at = NULL
                WHERE verification_token_expires_at < NOW()
                AND account_status = 'pending_email'
            ");
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error cleaning expired tokens: " . $e->getMessage());
            return false;
        }
    }
}