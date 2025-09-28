<?php
/**
 * Servicio de email con SMTP real
 */

class EmailService {
    
    /**
     * Enviar email usando SMTP
     */
    public static function sendEmail($to, $subject, $message, $isHTML = true) {
        try {
            // Obtener configuración SMTP
            $smtpConfig = self::getSMTPConfig();
            
            if (!$smtpConfig) {
                error_log("SMTP configuration not found");
                return false;
            }
            
            // Configurar headers
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: ' . ($isHTML ? 'text/html' : 'text/plain') . '; charset=UTF-8',
                'From: ' . $smtpConfig['from_name'] . ' <' . $smtpConfig['from_email'] . '>',
                'Reply-To: ' . $smtpConfig['reply_to'],
                'X-Mailer: PHP/' . phpversion()
            ];
            
            // Si es SMTP, usar PHPMailer si está disponible; si no, fallback a mail()
            // Forzar SMTP si hay credenciales configuradas
            if ($smtpConfig['use_smtp'] || (!empty($smtpConfig['username']) && !empty($smtpConfig['password']))) {
                return self::sendViaSMTP($to, $subject, $message, $headers, $smtpConfig);
            }
            // Fallback a mail() nativo
            return mail($to, $subject, $message, implode("\r\n", $headers));
            
        } catch (Exception $e) {
            error_log("Error sending email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar email de verificación
     */
    public static function sendVerificationEmail($email, $token, $userName) {
        $verificationUrl = APP_URL . "/verify-email?token=" . $token;
        
        $subject = "Verifica tu cuenta - " . APP_NAME;
        
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Verifica tu cuenta</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>¡Bienvenido a " . APP_NAME . "!</h1>
                </div>
                <div class='content'>
                    <h2>Hola " . htmlspecialchars($userName) . ",</h2>
                    <p>Gracias por registrarte en " . APP_NAME . ". Para activar tu cuenta y comenzar a usar nuestros servicios, necesitas verificar tu dirección de email.</p>
                    
                    <p>Haz clic en el siguiente botón para verificar tu cuenta:</p>
                    
                    <div style='text-align: center;'>
                        <a href='" . $verificationUrl . "' class='button'>Verificar mi cuenta</a>
                    </div>
                    
                    <p>O copia y pega esta URL en tu navegador:</p>
                    <p style='word-break: break-all; background: #e9ecef; padding: 10px; border-radius: 5px;'>" . $verificationUrl . "</p>
                    
                    <div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <strong>⚠️ Importante:</strong> Este enlace expira en 1 hora por seguridad. Si no verificas tu cuenta en ese tiempo, deberás registrarte nuevamente.
                    </div>
                    
                    <p>Si no creaste esta cuenta, puedes ignorar este email de forma segura.</p>
                </div>
                <div class='footer'>
                    <p>Este email fue enviado automáticamente, por favor no respondas a este mensaje.</p>
                    <p>&copy; " . date('Y') . " " . APP_NAME . ". Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::sendEmail($email, $subject, $message, true);
    }
    
    /**
     * Enviar email de recuperación de contraseña
     */
    public static function sendPasswordResetEmail($email, $token, $userName) {
        $resetUrl = APP_URL . "/auth/reset-password?token=" . $token;
        
        $subject = "Restablecer contraseña - " . APP_NAME;
        
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Restablecer contraseña</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #dc3545; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Restablecer contraseña</h1>
                </div>
                <div class='content'>
                    <h2>Hola " . htmlspecialchars($userName) . ",</h2>
                    <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en " . APP_NAME . ".</p>
                    
                    <p>Haz clic en el siguiente botón para crear una nueva contraseña:</p>
                    
                    <div style='text-align: center;'>
                        <a href='" . $resetUrl . "' class='button'>Restablecer contraseña</a>
                    </div>
                    
                    <p>O copia y pega esta URL en tu navegador:</p>
                    <p style='word-break: break-all; background: #e9ecef; padding: 10px; border-radius: 5px;'>" . $resetUrl . "</p>
                    
                    <div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <strong>⚠️ Importante:</strong> Este enlace expira en 24 horas por seguridad. Si no lo usas en ese tiempo, deberás solicitar un nuevo enlace.
                    </div>
                    
                    <p>Si no solicitaste restablecer tu contraseña, puedes ignorar este email de forma segura.</p>
                </div>
                <div class='footer'>
                    <p>Este email fue enviado automáticamente, por favor no respondas a este mensaje.</p>
                    <p>&copy; " . date('Y') . " " . APP_NAME . ". Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::sendEmail($email, $subject, $message, true);
    }
    
    /**
     * Enviar email via SMTP
     */
    private static function sendViaSMTP($to, $subject, $message, $headers, $smtpConfig) {
        try {
            // Usar PHPMailer si está disponible, sino usar mail() nativo
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                return self::sendViaPHPMailer($to, $subject, $message, $smtpConfig);
            } else {
                // Fallback a mail() nativo
                return mail($to, $subject, $message, implode("\r\n", $headers));
            }
        } catch (Exception $e) {
            error_log("SMTP error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar usando PHPMailer
     */
    private static function sendViaPHPMailer($to, $subject, $message, $smtpConfig) {
        // Implementación con PHPMailer
        try {
            require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
            require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
            require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';

            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $smtpConfig['host'] ?? 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $smtpConfig['username'];
            $mail->Password = $smtpConfig['password'];
            $enc = strtolower($smtpConfig['encryption'] ?? 'tls');
            $mail->SMTPSecure = $enc === 'ssl' ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int)($smtpConfig['port'] ?? ($enc === 'ssl' ? 465 : 587));

            $mail->CharSet = 'UTF-8';
            // Preferir el usuario SMTP como remitente real para evitar rechazos
            $fromEmail = !empty($smtpConfig['from_email']) ? $smtpConfig['from_email'] : $smtpConfig['username'];
            $fromName = $smtpConfig['from_name'] ?? (defined('APP_NAME') ? APP_NAME : 'App');
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);
            // Validar Reply-To; si es inválido, usar el mismo remitente
            $replyTo = $smtpConfig['reply_to'] ?? '';
            if (!empty($replyTo) && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                $mail->addReplyTo($replyTo);
            } else {
                $mail->addReplyTo($fromEmail);
            }
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;

            return $mail->send();
        } catch (Exception $e) {
            error_log('PHPMailer send error: ' . $e->getMessage());
            // Fallback a mail()
            return mail($to, $subject, $message, implode("\r\n", [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: ' . $smtpConfig['from_name'] . ' <' . $smtpConfig['from_email'] . '>',
                'Reply-To: ' . $smtpConfig['reply_to']
            ]));
        }
    }
    
    /**
     * Obtener configuración SMTP
     */
    public static function getSMTPConfig() {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                SELECT config_key, config_value 
                FROM system_config 
                WHERE config_key IN ('smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_encryption', 'email_from_name', 'email_from_email', 'email_reply_to', 'use_smtp')
                ORDER BY config_key
            ");
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            if (count($results) >= 6) {
                return [
                    'host' => $results['smtp_host'] ?? 'localhost',
                    'port' => $results['smtp_port'] ?? 587,
                    'username' => $results['smtp_username'] ?? '',
                    'password' => $results['smtp_password'] ?? '',
                    'encryption' => $results['smtp_encryption'] ?? 'tls',
                    'from_name' => $results['email_from_name'] ?? APP_NAME,
                    'from_email' => $results['email_from_email'] ?? 'noreply@' . parse_url(APP_URL, PHP_URL_HOST),
                    'reply_to' => $results['email_reply_to'] ?? 'noreply@' . parse_url(APP_URL, PHP_URL_HOST),
                    'use_smtp' => ($results['use_smtp'] ?? '0') === '1'
                ];
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error getting SMTP config: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Configurar SMTP
     */
    public static function configureSMTP($host, $port, $username, $password, $encryption = 'tls', $fromName = null, $fromEmail = null, $replyTo = null) {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $configs = [
                'smtp_host' => $host,
                'smtp_port' => $port,
                'smtp_username' => $username,
                'smtp_password' => $password,
                'smtp_encryption' => $encryption,
                'email_from_name' => $fromName ?? APP_NAME,
                'email_from_email' => $fromEmail ?? 'noreply@' . parse_url(APP_URL, PHP_URL_HOST),
                'email_reply_to' => $replyTo ?? 'noreply@' . parse_url(APP_URL, PHP_URL_HOST),
                'use_smtp' => '1'
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
            error_log("Error configuring SMTP: " . $e->getMessage());
            return false;
        }
    }
}
?>
