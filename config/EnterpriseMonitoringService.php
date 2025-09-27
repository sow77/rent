<?php
/**
 * Servicio de Monitoreo Empresarial
 * Sistema de alertas y monitoreo en tiempo real
 */

class EnterpriseMonitoringService {
    
    private static $alertThresholds = [
        'email_failure_rate' => 0.1, // 10% de fallos
        'sms_failure_rate' => 0.15,  // 15% de fallos
        'response_time' => 5,         // 5 segundos
        'error_rate' => 0.05,         // 5% de errores
        'concurrent_users' => 1000    // 1000 usuarios concurrentes
    ];
    
    /**
     * Verificar salud del sistema
     */
    public static function checkSystemHealth() {
        $health = [
            'overall' => 'healthy',
            'services' => [],
            'alerts' => [],
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Verificar servicios de email
        $emailHealth = self::checkEmailServiceHealth();
        $health['services']['email'] = $emailHealth;
        
        // Verificar servicios de SMS
        $smsHealth = self::checkSMSServiceHealth();
        $health['services']['sms'] = $smsHealth;
        
        // Verificar base de datos
        $dbHealth = self::checkDatabaseHealth();
        $health['services']['database'] = $dbHealth;
        
        // Verificar sistema de archivos
        $fsHealth = self::checkFileSystemHealth();
        $health['services']['filesystem'] = $fsHealth;
        
        // Determinar salud general
        $unhealthyServices = array_filter($health['services'], function($service) {
            return $service['status'] !== 'healthy';
        });
        
        if (count($unhealthyServices) > 0) {
            $health['overall'] = 'unhealthy';
            $health['alerts'] = self::generateAlerts($unhealthyServices);
        }
        
        // Guardar estado de salud
        self::saveHealthStatus($health);
        
        return $health;
    }
    
    /**
     * Verificar salud del servicio de email
     */
    private static function checkEmailServiceHealth() {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            // Obtener estadísticas de las últimas 24 horas
            $stmt = $db->prepare("
                SELECT 
                    status,
                    COUNT(*) as count
                FROM email_logs 
                WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY status
            ");
            $stmt->execute();
            $stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            $total = array_sum($stats);
            $successful = $stats['success'] ?? 0;
            $failed = $total - $successful;
            
            $failureRate = $total > 0 ? $failed / $total : 0;
            
            $status = 'healthy';
            $message = "Email service operating normally";
            
            if ($failureRate > self::$alertThresholds['email_failure_rate']) {
                $status = 'unhealthy';
                $message = "High email failure rate: " . round($failureRate * 100, 2) . "%";
            }
            
            return [
                'status' => $status,
                'message' => $message,
                'metrics' => [
                    'total_emails' => $total,
                    'successful' => $successful,
                    'failed' => $failed,
                    'failure_rate' => $failureRate
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error checking email service: ' . $e->getMessage(),
                'metrics' => []
            ];
        }
    }
    
    /**
     * Verificar salud del servicio de SMS
     */
    private static function checkSMSServiceHealth() {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            // Obtener estadísticas de las últimas 24 horas
            $stmt = $db->prepare("
                SELECT 
                    status,
                    COUNT(*) as count
                FROM sms_logs 
                WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY status
            ");
            $stmt->execute();
            $stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            $total = array_sum($stats);
            $successful = $stats['success'] ?? 0;
            $failed = $total - $successful;
            
            $failureRate = $total > 0 ? $failed / $total : 0;
            
            $status = 'healthy';
            $message = "SMS service operating normally";
            
            if ($failureRate > self::$alertThresholds['sms_failure_rate']) {
                $status = 'unhealthy';
                $message = "High SMS failure rate: " . round($failureRate * 100, 2) . "%";
            }
            
            return [
                'status' => $status,
                'message' => $message,
                'metrics' => [
                    'total_sms' => $total,
                    'successful' => $successful,
                    'failed' => $failed,
                    'failure_rate' => $failureRate
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error checking SMS service: ' . $e->getMessage(),
                'metrics' => []
            ];
        }
    }
    
    /**
     * Verificar salud de la base de datos
     */
    private static function checkDatabaseHealth() {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            // Verificar conexión
            $startTime = microtime(true);
            $stmt = $db->query("SELECT 1");
            $endTime = microtime(true);
            
            $responseTime = ($endTime - $startTime) * 1000; // en milisegundos
            
            $status = 'healthy';
            $message = "Database responding normally";
            
            if ($responseTime > self::$alertThresholds['response_time'] * 1000) {
                $status = 'warning';
                $message = "Database response time high: " . round($responseTime, 2) . "ms";
            }
            
            // Verificar espacio en disco (si es posible)
            $diskSpace = self::checkDiskSpace();
            
            return [
                'status' => $status,
                'message' => $message,
                'metrics' => [
                    'response_time_ms' => $responseTime,
                    'disk_space_percent' => $diskSpace
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage(),
                'metrics' => []
            ];
        }
    }
    
    /**
     * Verificar salud del sistema de archivos
     */
    private static function checkFileSystemHealth() {
        try {
            $uploadDir = 'public/uploads';
            $logDir = 'logs';
            
            $status = 'healthy';
            $message = "File system operating normally";
            $issues = [];
            
            // Verificar directorio de uploads
            if (!is_dir($uploadDir)) {
                $issues[] = "Upload directory missing";
            } elseif (!is_writable($uploadDir)) {
                $issues[] = "Upload directory not writable";
            }
            
            // Verificar directorio de logs
            if (!is_dir($logDir)) {
                $issues[] = "Log directory missing";
            } elseif (!is_writable($logDir)) {
                $issues[] = "Log directory not writable";
            }
            
            // Verificar espacio en disco
            $diskSpace = self::checkDiskSpace();
            if ($diskSpace > 90) {
                $issues[] = "Disk space critically low: " . $diskSpace . "%";
            } elseif ($diskSpace > 80) {
                $issues[] = "Disk space low: " . $diskSpace . "%";
            }
            
            if (count($issues) > 0) {
                $status = count($issues) > 2 ? 'unhealthy' : 'warning';
                $message = implode(', ', $issues);
            }
            
            return [
                'status' => $status,
                'message' => $message,
                'metrics' => [
                    'disk_space_percent' => $diskSpace,
                    'issues_count' => count($issues)
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error checking file system: ' . $e->getMessage(),
                'metrics' => []
            ];
        }
    }
    
    /**
     * Verificar espacio en disco
     */
    private static function checkDiskSpace() {
        try {
            $bytes = disk_free_space('.');
            $total = disk_total_space('.');
            
            if ($bytes === false || $total === false) {
                return 0;
            }
            
            $used = $total - $bytes;
            return round(($used / $total) * 100, 2);
            
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Generar alertas basadas en servicios no saludables
     */
    private static function generateAlerts($unhealthyServices) {
        $alerts = [];
        
        foreach ($unhealthyServices as $serviceName => $service) {
            $alert = [
                'service' => $serviceName,
                'level' => $service['status'] === 'error' ? 'critical' : 'warning',
                'message' => $service['message'],
                'timestamp' => date('Y-m-d H:i:s'),
                'metrics' => $service['metrics']
            ];
            
            $alerts[] = $alert;
            
            // Enviar notificación si es crítica
            if ($alert['level'] === 'critical') {
                self::sendCriticalAlert($alert);
            }
        }
        
        return $alerts;
    }
    
    /**
     * Enviar alerta crítica
     */
    private static function sendCriticalAlert($alert) {
        try {
            // Obtener administradores
            $admins = self::getAdministrators();
            
            foreach ($admins as $admin) {
                // Enviar email de alerta
                if (!empty($admin['email'])) {
                    self::sendAlertEmail($admin['email'], $alert);
                }
                
                // Enviar SMS de alerta si está disponible
                if (!empty($admin['phone'])) {
                    self::sendAlertSMS($admin['phone'], $alert);
                }
            }
            
        } catch (Exception $e) {
            error_log("Error sending critical alert: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener administradores del sistema
     */
    private static function getAdministrators() {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                SELECT id, email, phone, first_name, last_name 
                FROM users 
                WHERE role = 'admin' AND status = 'active'
            ");
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error getting administrators: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Enviar email de alerta
     */
    private static function sendAlertEmail($email, $alert) {
        try {
            require_once 'config/EnterpriseEmailService.php';
            
            $subject = "🚨 ALERTA CRÍTICA - " . APP_NAME;
            $message = self::getAlertEmailTemplate($alert);
            
            EnterpriseEmailService::sendEmail($email, $subject, $message, true, 'high');
            
        } catch (Exception $e) {
            error_log("Error sending alert email: " . $e->getMessage());
        }
    }
    
    /**
     * Enviar SMS de alerta
     */
    private static function sendAlertSMS($phone, $alert) {
        try {
            require_once 'config/EnterpriseSMSService.php';
            
            $message = "🚨 ALERTA CRÍTICA: {$alert['service']} - {$alert['message']}";
            
            EnterpriseSMSService::sendSMS($phone, $message, 'high');
            
        } catch (Exception $e) {
            error_log("Error sending alert SMS: " . $e->getMessage());
        }
    }
    
    /**
     * Template de email de alerta
     */
    private static function getAlertEmailTemplate($alert) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Alerta Crítica del Sistema</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='margin: 0; font-size: 28px;'>🚨 ALERTA CRÍTICA</h1>
                <p style='margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>" . APP_NAME . "</p>
            </div>
            
            <div style='background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px;'>
                <h2 style='color: #2c3e50; margin-top: 0;'>Servicio Afectado: {$alert['service']}</h2>
                
                <div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;'>
                    <p style='margin: 0; color: #856404; font-size: 14px;'>
                        <strong>Mensaje:</strong> {$alert['message']}
                    </p>
                </div>
                
                <div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #dc3545;'>
                    <p style='margin: 0; color: #721c24; font-size: 14px;'>
                        <strong>Timestamp:</strong> {$alert['timestamp']}
                    </p>
                </div>
                
                <p style='color: #666; font-size: 14px; margin-bottom: 0;'>
                    Por favor, revisa el sistema inmediatamente y toma las acciones necesarias.
                </p>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Guardar estado de salud
     */
    private static function saveHealthStatus($health) {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                INSERT INTO system_health_logs (overall_status, services_data, alerts_data, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([
                $health['overall'],
                json_encode($health['services']),
                json_encode($health['alerts'])
            ]);
            
        } catch (Exception $e) {
            error_log("Error saving health status: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener historial de salud del sistema
     */
    public static function getHealthHistory($hours = 24) {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                SELECT overall_status, services_data, alerts_data, created_at
                FROM system_health_logs 
                WHERE created_at > DATE_SUB(NOW(), INTERVAL ? HOUR)
                ORDER BY created_at DESC
                LIMIT 100
            ");
            $stmt->execute([$hours]);
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decodificar JSON
            foreach ($results as &$result) {
                $result['services_data'] = json_decode($result['services_data'], true);
                $result['alerts_data'] = json_decode($result['alerts_data'], true);
            }
            
            return $results;
            
        } catch (Exception $e) {
            error_log("Error getting health history: " . $e->getMessage());
            return [];
        }
    }
}
?>
