<?php
/**
 * Servicio de Email Empresarial Robusto
 * Sistema escalable con múltiples proveedores y fallbacks
 */

// Cargar autoloader
require_once __DIR__ . '/autoloader.php';

class EnterpriseEmailService {
    
    private static $providers = [];
    private static $currentProvider = null;
    private static $retryAttempts = 3;
    private static $retryDelay = 2; // segundos
    
    /**
     * Inicializar proveedores SMTP
     */
    public static function initializeProviders() {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            // Obtener todos los proveedores configurados
            $stmt = $db->prepare("
                SELECT config_key, config_value 
                FROM system_config 
                WHERE config_key LIKE 'smtp_provider_%' 
                ORDER BY config_key
            ");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Procesar proveedores
            $providers = [];
            foreach ($results as $key => $value) {
                if (strpos($key, 'smtp_provider_') === 0) {
                    $providerData = json_decode($value, true);
                    if ($providerData && isset($providerData['name'])) {
                        $providers[$providerData['name']] = $providerData;
                    }
                }
            }
            
            self::$providers = $providers;
            
            // Seleccionar proveedor principal
            $primaryProvider = self::getConfigValue('email_primary_provider', 'gmail');
            self::$currentProvider = $primaryProvider;
            
            return true;
        } catch (Exception $e) {
            error_log("Error initializing email providers: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar email con fallback automático
     */
    public static function sendEmail($to, $subject, $message, $isHTML = true, $priority = 'normal') {
        // Inicializar proveedores si no están cargados
        if (empty(self::$providers)) {
            self::initializeProviders();
        }
        
        // Validar email
        if (!self::validateEmail($to)) {
            self::logEmailAttempt($to, $subject, 'invalid_email', 'Email inválido');
            return false;
        }
        
        // Intentar con proveedor principal
        $attempts = 0;
        $lastError = '';
        
        while ($attempts < self::$retryAttempts) {
            $attempts++;
            
            // Seleccionar proveedor
            $provider = self::selectProvider($priority);
            
            if (!$provider) {
                self::logEmailAttempt($to, $subject, 'no_provider', 'No hay proveedores disponibles');
                return false;
            }
            
            // Intentar envío
            $result = self::sendViaProvider($provider, $to, $subject, $message, $isHTML);
            
            if ($result['success']) {
                self::logEmailAttempt($to, $subject, 'success', "Enviado via {$provider['name']}");
                return true;
            }
            
            $lastError = $result['error'];
            self::logEmailAttempt($to, $subject, 'failed', "Error via {$provider['name']}: {$lastError}");
            
            // Marcar proveedor como fallido temporalmente
            self::markProviderFailed($provider['name']);
            
            // Esperar antes del siguiente intento
            if ($attempts < self::$retryAttempts) {
                sleep(self::$retryDelay);
            }
        }
        
        // Todos los intentos fallaron
        self::logEmailAttempt($to, $subject, 'all_failed', "Todos los proveedores fallaron. Último error: {$lastError}");
        return false;
    }
    
    /**
     * Enviar email de verificación empresarial
     */
    public static function sendVerificationEmail($email, $token, $userName, $companyName = null) {
        $companyName = $companyName ?: APP_NAME;
        $verificationUrl = APP_URL . "/auth/verify-email?token=" . $token;
        
        $subject = "Verificación de Email - {$companyName}";
        
        $message = self::getVerificationEmailTemplate($userName, $verificationUrl, $companyName);
        
        return self::sendEmail($email, $subject, $message, true, 'high');
    }
    
    /**
     * Enviar email de recuperación de contraseña empresarial
     */
    public static function sendPasswordResetEmail($email, $token, $userName, $companyName = null) {
        $companyName = $companyName ?: APP_NAME;
        $resetUrl = APP_URL . "/auth/reset-password?token=" . $token;
        
        $subject = "Recuperación de Contraseña - {$companyName}";
        
        $message = self::getPasswordResetEmailTemplate($userName, $resetUrl, $companyName);
        
        return self::sendEmail($email, $subject, $message, true, 'high');
    }
    
    /**
     * Enviar email de notificación de seguridad
     */
    public static function sendSecurityNotification($email, $userName, $event, $details = []) {
        $subject = "Notificación de Seguridad - " . APP_NAME;
        $message = self::getSecurityNotificationTemplate($userName, $event, $details);
        
        return self::sendEmail($email, $subject, $message, true, 'high');
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
     * Enviar email via proveedor específico
     */
    private static function sendViaProvider($provider, $to, $subject, $message, $isHTML) {
        try {
            // Configurar headers
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: ' . ($isHTML ? 'text/html' : 'text/plain') . '; charset=UTF-8',
                'From: ' . $provider['from_name'] . ' <' . $provider['from_email'] . '>',
                'Reply-To: ' . $provider['reply_to'],
                'X-Mailer: PHP/' . phpversion(),
                'X-Priority: 3',
                'X-MSMail-Priority: Normal'
            ];
            
            // Agregar headers de seguridad
            $headers[] = 'X-Content-Type-Options: nosniff';
            $headers[] = 'X-Frame-Options: DENY';
            $headers[] = 'X-XSS-Protection: 1; mode=block';
            
            // Usar PHPMailer si está disponible
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                return self::sendViaPHPMailer($provider, $to, $subject, $message, $isHTML);
            } else {
                // Fallback a mail() nativo
                return self::sendViaNativeMail($to, $subject, $message, $headers);
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Enviar via PHPMailer (más robusto)
     */
    private static function sendViaPHPMailer($provider, $to, $subject, $message, $isHTML) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Configuración SMTP
            $mail->isSMTP();
            $mail->Host = $provider['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $provider['username'];
            $mail->Password = $provider['password'];
            $mail->SMTPSecure = $provider['encryption'];
            $mail->Port = $provider['port'];
            $mail->CharSet = 'UTF-8';
            
            // Configuración del email
            $mail->setFrom($provider['from_email'], $provider['from_name']);
            $mail->addAddress($to);
            $mail->isHTML($isHTML);
            $mail->Subject = $subject;
            $mail->Body = $message;
            
            // Configuración de seguridad
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'allow_self_signed' => false,
                ]
            ];
            
            $result = $mail->send();
            
            return ['success' => $result, 'error' => $result ? '' : $mail->ErrorInfo];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Enviar via mail() nativo
     */
    private static function sendViaNativeMail($to, $subject, $message, $headers) {
        $result = mail($to, $subject, $message, implode("\r\n", $headers));
        return ['success' => $result, 'error' => $result ? '' : 'Error en mail() nativo'];
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
     * Validar email
     */
    private static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
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
     * Log de intentos de email
     */
    private static function logEmailAttempt($to, $subject, $status, $message) {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                INSERT INTO email_logs (to_email, subject, status, message, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$to, $subject, $status, $message]);
        } catch (Exception $e) {
            error_log("Error logging email attempt: " . $e->getMessage());
        }
    }
    
    /**
     * Template de email de verificación
     */
    private static function getVerificationEmailTemplate($userName, $verificationUrl, $companyName) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Verificación de Email</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='margin: 0; font-size: 28px;'>Verificación de Email</h1>
                <p style='margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>{$companyName}</p>
            </div>
            
            <div style='background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px;'>
                <h2 style='color: #2c3e50; margin-top: 0;'>¡Hola {$userName}!</h2>
                
                <p>Gracias por registrarte en <strong>{$companyName}</strong>. Para completar tu registro y activar tu cuenta, necesitas verificar tu dirección de email.</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$verificationUrl}' 
                       style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                              color: white; 
                              padding: 15px 30px; 
                              text-decoration: none; 
                              border-radius: 25px; 
                              display: inline-block; 
                              font-weight: bold; 
                              font-size: 16px;'>
                        Verificar Email
                    </a>
                </div>
                
                <p style='color: #666; font-size: 14px;'>
                    Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
                    <a href='{$verificationUrl}' style='color: #667eea; word-break: break-all;'>{$verificationUrl}</a>
                </p>
                
                <div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #2196f3;'>
                    <p style='margin: 0; color: #1976d2; font-size: 14px;'>
                        <strong>⚠️ Importante:</strong> Este enlace expirará en 24 horas por motivos de seguridad.
                    </p>
                </div>
                
                <p style='color: #666; font-size: 14px; margin-bottom: 0;'>
                    Si no creaste esta cuenta, puedes ignorar este email de forma segura.
                </p>
            </div>
            
            <div style='text-align: center; margin-top: 20px; color: #999; font-size: 12px;'>
                <p>© " . date('Y') . " {$companyName}. Todos los derechos reservados.</p>
                <p>Este es un email automático, por favor no respondas a este mensaje.</p>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Template de email de recuperación de contraseña
     */
    private static function getPasswordResetEmailTemplate($userName, $resetUrl, $companyName) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Recuperación de Contraseña</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='margin: 0; font-size: 28px;'>Recuperación de Contraseña</h1>
                <p style='margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>{$companyName}</p>
            </div>
            
            <div style='background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px;'>
                <h2 style='color: #2c3e50; margin-top: 0;'>¡Hola {$userName}!</h2>
                
                <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en <strong>{$companyName}</strong>.</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$resetUrl}' 
                       style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); 
                              color: white; 
                              padding: 15px 30px; 
                              text-decoration: none; 
                              border-radius: 25px; 
                              display: inline-block; 
                              font-weight: bold; 
                              font-size: 16px;'>
                        Restablecer Contraseña
                    </a>
                </div>
                
                <p style='color: #666; font-size: 14px;'>
                    Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
                    <a href='{$resetUrl}' style='color: #ff6b6b; word-break: break-all;'>{$resetUrl}</a>
                </p>
                
                <div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;'>
                    <p style='margin: 0; color: #856404; font-size: 14px;'>
                        <strong>🔒 Seguridad:</strong> Este enlace expirará en 1 hora por motivos de seguridad.
                    </p>
                </div>
                
                <div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #dc3545;'>
                    <p style='margin: 0; color: #721c24; font-size: 14px;'>
                        <strong>⚠️ Importante:</strong> Si no solicitaste este cambio, ignora este email y considera cambiar tu contraseña.
                    </p>
                </div>
                
                <p style='color: #666; font-size: 14px; margin-bottom: 0;'>
                    Por motivos de seguridad, este enlace solo puede usarse una vez.
                </p>
            </div>
            
            <div style='text-align: center; margin-top: 20px; color: #999; font-size: 12px;'>
                <p>© " . date('Y') . " {$companyName}. Todos los derechos reservados.</p>
                <p>Este es un email automático, por favor no respondas a este mensaje.</p>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Template de notificación de seguridad
     */
    private static function getSecurityNotificationTemplate($userName, $event, $details) {
        $eventMessages = [
            'login_attempt' => 'Se detectó un intento de inicio de sesión',
            'password_change' => 'Se cambió la contraseña de tu cuenta',
            'email_change' => 'Se cambió el email de tu cuenta',
            'phone_change' => 'Se cambió el número de teléfono de tu cuenta',
            'suspicious_activity' => 'Se detectó actividad sospechosa en tu cuenta'
        ];
        
        $eventMessage = $eventMessages[$event] ?? 'Se detectó una actividad en tu cuenta';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Notificación de Seguridad</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='margin: 0; font-size: 28px;'>🔒 Notificación de Seguridad</h1>
                <p style='margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>" . APP_NAME . "</p>
            </div>
            
            <div style='background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px;'>
                <h2 style='color: #2c3e50; margin-top: 0;'>¡Hola {$userName}!</h2>
                
                <p>{$eventMessage}.</p>
                
                <div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;'>
                    <p style='margin: 0; color: #856404; font-size: 14px;'>
                        <strong>📅 Fecha y hora:</strong> " . date('d/m/Y H:i:s') . "
                    </p>
                </div>
                
                <p style='color: #666; font-size: 14px; margin-bottom: 0;'>
                    Si no reconoces esta actividad, contacta inmediatamente con nuestro equipo de soporte.
                </p>
            </div>
        </body>
        </html>
        ";
    }
}
?>
