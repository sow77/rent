<?php
// config/Auth.php
require_once __DIR__ . '/SessionConfig.php';

class Auth {
    private static $instance = null;
    private $db;
    
    private function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Iniciar sesión de usuario
     */
    public function login($email, $password) {
        try {
            $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Los administradores siempre pueden acceder sin verificación
                if ($user['role'] === 'admin') {
                    // Crear sesión de administrador sin verificación
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['authenticated'] = true;
                    $_SESSION['login_time'] = time();
                    $_SESSION['last_activity'] = time();
                    
                    return [
                        'success' => true,
                        'user' => $user,
                        'message' => 'Inicio de sesión exitoso'
                    ];
                }
                
                // Para usuarios normales, verificar estado de la cuenta
                if ($user['account_status'] === 'active') {
                    // Usuario activo, permitir login
                } elseif ($user['account_status'] === 'pending_verification') {
                    // Usuario pendiente de verificación
                    require_once 'config/AccountManager.php';
                    $accountManager = new AccountManager();
                    
                    // Deshabilitar cuentas expiradas
                    $accountManager->disableExpiredAccounts();
                    
                    // Verificar si la cuenta sigue pendiente
                    $accountStatus = $accountManager->getAccountStatus($user['id']);
                    
                    if ($accountStatus['account_status'] === 'disabled') {
                        return [
                            'success' => false,
                            'message' => '❌ Tu cuenta ha sido DESHABILITADA por no verificar tu email en 24 horas. No puedes hacer reservas ni usar los servicios. Debes registrarte nuevamente con un email válido.',
                            'account_disabled' => true,
                            'warning' => 'Para evitar esto en el futuro, asegúrate de verificar tu email inmediatamente después del registro.'
                        ];
                    } elseif ($accountStatus['account_status'] === 'pending_verification') {
                        $expiresAt = $accountStatus['verification_expires_at'];
                        $timeLeft = strtotime($expiresAt) - time();
                        $hoursLeft = max(0, floor($timeLeft / 3600));
                        $minutesLeft = max(0, floor(($timeLeft % 3600) / 60));
                        
                        if ($hoursLeft > 0) {
                            $timeMessage = "Te quedan {$hoursLeft} horas y {$minutesLeft} minutos";
                        } else {
                            $timeMessage = "Te quedan {$minutesLeft} minutos";
                        }
                        
                        return [
                            'success' => false,
                            'message' => "⚠️ Debes verificar tu email antes de poder iniciar sesión. {$timeMessage} para verificar tu cuenta o será deshabilitada automáticamente.",
                            'requires_verification' => true,
                            'expires_in' => $expiresAt,
                            'warning' => 'Si no verificas tu email a tiempo, tu cuenta será deshabilitada y no podrás hacer reservas.'
                        ];
                    }
                } elseif ($user['account_status'] === 'disabled') {
                    return [
                        'success' => false,
                        'message' => '❌ Tu cuenta ha sido DESHABILITADA. No puedes hacer reservas ni usar los servicios.',
                        'account_disabled' => true
                    ];
                }
                
                // Crear sesión de usuario
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['authenticated'] = true;
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();
                
                return [
                    'success' => true,
                    'user' => $user,
                    'message' => 'Inicio de sesión exitoso'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Credenciales inválidas'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error en el servidor: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Registrar nuevo usuario
     */
    public function register($name, $email, $password, $phone) {
        try {
            // Verificar si el email ya existe
            $query = "SELECT COUNT(*) as count FROM users WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result['count'] > 0) {
                return [
                    'success' => false,
                    'message' => 'El email ya está registrado'
                ];
            }
            
            // Verificar si el teléfono ya existe
            $query = "SELECT COUNT(*) as count FROM users WHERE phone = :phone";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result['count'] > 0) {
                return [
                    'success' => false,
                    'message' => 'El número de teléfono ya está registrado'
                ];
            }
            
        // Crear nuevo usuario (inicialmente no verificado)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $userId = uniqid('user_', true); // Generar ID único
        $query = "INSERT INTO users (id, name, email, password, phone, role, email_verified, account_status, active) VALUES (:id, :name, :email, :password, :phone, 'user', 0, 'pending_verification', 0)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':phone', $phone);
        
        if ($stmt->execute()) {
            
            // Configurar cuenta como pendiente de verificación (sin enviar email aquí)
            require_once 'config/AccountManager.php';
            $accountManager = new AccountManager();
            $accountManager->createPendingAccount($userId, $email);
            
            // Devolver user_id para que el controlador gestione envío de email
            return [
                'success' => true,
                'message' => 'Usuario registrado. Verificación requerida.',
                'requires_verification' => true,
                'user_id' => $userId
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al registrar el usuario'
            ];
        }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error en el servidor: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        SessionConfig::destroySession();
        
        return [
            'success' => true,
            'message' => 'Sesión cerrada exitosamente'
        ];
    }
    
    /**
     * Verificar si el usuario está autenticado
     */
    public static function isAuthenticated() {
        return SessionConfig::isValidSession();
    }
    
    /**
     * Verificar si el usuario es administrador
     */
    public static function isAdmin() {
        return self::isAuthenticated() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    
    /**
     * Verificar si el email del usuario está verificado
     */
    public static function isEmailVerified() {
        if (!self::isAuthenticated()) {
            return false;
        }
        
        try {
            require_once __DIR__ . '/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                SELECT email_verified_at, account_status 
                FROM users 
                WHERE id = ? AND email = ?
            ");
            $stmt->execute([$_SESSION['user_id'], $_SESSION['user_email']]);
            $user = $stmt->fetch();
            
            return $user && $user['email_verified_at'] !== null && $user['account_status'] === 'active';
        } catch (Exception $e) {
            error_log("Error checking email verification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener datos del usuario actual
     */
    public static function getCurrentUser() {
        if (!self::isAuthenticated()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'name' => $_SESSION['user_name'],
            'role' => $_SESSION['user_role']
        ];
    }
    
    /**
     * Obtener ID del usuario actual
     */
    public static function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Verificar si la sesión ha expirado (30 minutos)
     */
    public static function isSessionExpired() {
        if (!isset($_SESSION['last_activity'])) {
            return true;
        }
        
        $sessionTimeout = 30 * 60; // 30 minutos
        return (time() - $_SESSION['last_activity']) > $sessionTimeout;
    }
    
    /**
     * Renovar la sesión
     */
    public static function renewSession() {
        return SessionConfig::renewSession();
    }
} 