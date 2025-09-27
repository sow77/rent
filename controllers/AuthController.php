<?php
// Configurar manejo de errores para API
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Función para manejar errores fatales
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error interno del servidor',
            'data' => []
        ]);
        exit;
    }
});

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Validation.php';
require_once __DIR__ . '/../config/EnterpriseEmailService.php';
require_once __DIR__ . '/../config/EnterpriseSMSService.php';
require_once __DIR__ . '/../config/EnterpriseSecurityService.php';

class AuthController {
    private $auth;
    private $passwordReset;

    public function __construct() {
        $this->auth = Auth::getInstance();
        require_once __DIR__ . '/../config/PasswordReset.php';
        $this->passwordReset = new PasswordReset();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Aceptar JSON y form-data
            $rawInput = file_get_contents('php://input');
            $parsedJson = json_decode($rawInput, true);
            $email = isset($parsedJson['email']) ? $parsedJson['email'] : ($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $redirect = $_POST['redirect'] ?? '';
        
            if (empty($email) || empty($password)) {
                $this->jsonResponse(false, 'Por favor complete todos los campos');
                return;
            }
            
            $result = $this->auth->login($email, $password);
            
            if ($result['success']) {
                // Obtener datos del usuario para actualización rápida
                $user = $this->auth->getCurrentUser();
                $responseData = [
                    'redirect' => !empty($redirect) ? $redirect : APP_URL,
                    'user' => [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ]
                ];
                $this->jsonResponse(true, $result['message'], $responseData);
            } else {
                $this->jsonResponse(false, $result['message']);
            }
        } else {
            // Mostrar la vista de login para peticiones GET
            $this->showLoginView();
        }
    }
    
    private function showLoginView() {
        $pageTitle = 'Iniciar Sesión';
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/auth/login.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $redirect = $_POST['redirect'] ?? '';
                $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
                
                // Verificar reCAPTCHA
                require_once __DIR__ . '/../config/ExternalValidation.php';
                $recaptchaResult = ExternalValidation::verifyRecaptcha($recaptcha_response, $_SERVER['REMOTE_ADDR'] ?? '');
                if (!$recaptchaResult['valid']) {
                    $this->jsonResponse(false, 'reCAPTCHA no válido. Por favor, verifica que no eres un robot.', []);
                    return;
                }
                
                // Validar todos los campos con el sistema sofisticado
                $validation = Validation::validateRegistration($_POST);
                
                if (!$validation['valid']) {
                    $this->jsonResponse(false, 'Errores de validación:', $validation['errors']);
                    return;
                }
                
                // Usar los datos validados
                $validatedData = $validation['data'];
                
                $result = $this->auth->register(
                    $validatedData['name'], 
                    $validatedData['email'], 
                    $validatedData['password'],
                    $validatedData['phone']
                );
                
                if ($result['success']) {
                    // Enviar email de verificación
                    require_once __DIR__ . '/../config/EmailVerification.php';
                    $token = EmailVerification::createVerificationToken($result['user_id'], $validatedData['email']);
                    
                    if ($token) {
                        $emailSent = EmailVerification::sendVerificationEmail($validatedData['email'], $token, $validatedData['name']);
                        
                        if ($emailSent) {
                            // Seguridad: NO exponer el token en el front. Redirigir a página informativa.
                            $this->jsonResponse(true, 'Usuario registrado correctamente. Revisa tu email para activar la cuenta.', ['redirect' => APP_URL . '/verify-email-required']);
                        } else {
                            $this->jsonResponse(false, 'Usuario registrado pero error al enviar email de verificación. Contacta al administrador.');
                        }
                    } else {
                        $this->jsonResponse(false, 'Error al generar token de verificación. Contacta al administrador.');
                    }
                } else {
                    $this->jsonResponse(false, $result['message']);
                }
            } catch (Exception $e) {
                error_log("Error en registro: " . $e->getMessage());
                $this->jsonResponse(false, 'Error interno del servidor', []);
            }
        }
    }

    public function logout() {
        $result = $this->auth->logout();
        
        // Si es una petición AJAX o sendBeacon, devolver JSON
        if ((!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
            ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout']))) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Sesión cerrada exitosamente',
                'redirect' => APP_URL
            ]);
            exit;
        }
        
        // Si no es AJAX, redirigir normalmente
        header('Location: ' . APP_URL);
        exit;
    }

    public function getCurrentUser() {
        header('Content-Type: application/json');
        
        if (!Auth::isAuthenticated()) {
            $this->jsonResponse(false, 'Usuario no autenticado');
            return;
        }
        
        $user = Auth::getCurrentUser();
        $this->jsonResponse(true, 'Usuario obtenido exitosamente', $user);
    }

    public function renewSession() {
        header('Content-Type: application/json');
        
        if (!Auth::isAuthenticated()) {
            $this->jsonResponse(false, 'Usuario no autenticado');
            return;
        }
        
        // Renovar la sesión
        $renewed = Auth::renewSession();
        
        if ($renewed) {
            $this->jsonResponse(true, 'Sesión renovada exitosamente');
        } else {
            $this->jsonResponse(false, 'No se pudo renovar la sesión');
        }
    }

    public function checkUserExists() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido');
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? '';
        
        if (empty($email)) {
            $this->jsonResponse(false, 'Email requerido');
            return;
        }
        
        // Verificar si el usuario existe en la BD
        $db = Database::getInstance()->getConnection();
        $userModel = new User($db);
        $user = $userModel->getByEmail($email);
        
        if ($user) {
            $this->jsonResponse(true, 'Usuario encontrado', ['exists' => true, 'user_id' => $user['id']]);
        } else {
            $this->jsonResponse(true, 'Usuario no encontrado', ['exists' => false]);
        }
    }

    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            
            if (empty($email)) {
                $this->jsonResponse(false, 'Por favor ingrese su email');
                return;
            }
            
            // Validar formato de email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->jsonResponse(false, 'Por favor ingrese un email válido');
                return;
            }
            
            // Crear token de recuperación
            $result = $this->passwordReset->createResetToken($email);
            
            if ($result['success']) {
                // Enviar email de recuperación (no bloquear respuesta si falla el envío)
                $this->sendPasswordResetEmail($result['data']['user'], $result['data']['token']);
                $this->jsonResponse(true, 'Si el email existe y está activo, se ha enviado un enlace de recuperación');
            } else {
                $this->jsonResponse(false, $result['message']);
            }
        } else {
            $this->jsonResponse(false, 'Método no permitido');
        }
    }

    public function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['token'] ?? '';
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';

            if (empty($token) || empty($password) || empty($password_confirm)) {
                $this->jsonResponse(false, 'Por favor complete todos los campos');
                return;
            }
            
            if ($password !== $password_confirm) {
                $this->jsonResponse(false, 'Las contraseñas no coinciden');
                return;
            }

            // Validar fortaleza de la contraseña
            $validation = Validation::validatePassword($password);
            if (!$validation['valid']) {
                $this->jsonResponse(false, 'Contraseña no válida', $validation['errors']);
                return;
            }
            
            // Resetear contraseña
            $result = $this->passwordReset->resetPassword($token, $password);
            $this->jsonResponse($result['success'], $result['message']);
            } else {
            $this->jsonResponse(false, 'Método no permitido');
        }
    }

    public function validateResetToken() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $token = $input['token'] ?? '';
            
            if (empty($token)) {
                $this->jsonResponse(false, 'Token requerido');
                return;
            }
            
            $result = $this->passwordReset->validateToken($token);
            $this->jsonResponse($result['success'], $result['message'], $result['data'] ?? []);
        } else {
            $this->jsonResponse(false, 'Método no permitido');
        }
    }

    public function showResetPasswordPage() {
        $pageTitle = 'Restablecer Contraseña';
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/auth/reset-password.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }

    private function sendPasswordResetEmail($user, $token) {
        try {
            require_once __DIR__ . '/../config/EmailService.php';
            
            $result = EmailService::sendPasswordResetEmail($user['email'], $token, $user['name']);
            
            if ($result) {
                error_log("Password reset email sent successfully to {$user['email']}");
                return true;
            } else {
                error_log("Failed to send password reset email to {$user['email']}");
                return false;
            }
        } catch (Exception $e) {
            error_log("Error sending password reset email: " . $e->getMessage());
            return false;
        }
    }

    private function jsonResponse($success, $message, $data = []) {
        // Limpiar cualquier salida previa
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        
        $response = [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Mostrar página de verificación de email requerida
     */
    public function showVerifyEmailRequired() {
        $pageTitle = 'Verificación de Email Requerida';
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/auth/verify-email-required.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
    
    /**
     * Reenviar email de verificación
     */
    public function resendVerificationEmail() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido');
            return;
        }
        
        try {
            if (!Auth::isAuthenticated()) {
                $this->jsonResponse(false, 'Usuario no autenticado');
                return;
            }
            
            $user = Auth::getCurrentUser();
            if (!$user) {
                $this->jsonResponse(false, 'No se pudo obtener información del usuario');
                return;
            }
            
            // Verificar si ya está verificado
            if (Auth::isEmailVerified()) {
                $this->jsonResponse(false, 'Tu email ya está verificado');
                return;
            }
            
            // Crear nuevo token de verificación
            require_once __DIR__ . '/../config/EmailVerification.php';
            $token = EmailVerification::createVerificationToken($user['id'], $user['email']);
            
            if ($token) {
                // Enviar email de verificación
                $emailSent = EmailVerification::sendVerificationEmail($user['email'], $token, $user['name']);
                
                if ($emailSent) {
                    $this->jsonResponse(true, 'Email de verificación reenviado correctamente. Revisa tu bandeja de entrada.');
                } else {
                    $this->jsonResponse(false, 'Error al enviar el email. Por favor, inténtalo de nuevo.');
                }
            } else {
                $this->jsonResponse(false, 'Error al generar el token de verificación');
            }
            
        } catch (Exception $e) {
            error_log("Error resending verification email: " . $e->getMessage());
            $this->jsonResponse(false, 'Error interno del servidor');
        }
    }

    /**
     * Reenviar email de verificación (público por email, sin sesión)
     */
    public function resendVerificationEmailPublic() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido');
            return;
        }
        try {
            // Leer JSON { email }
            $input = json_decode(file_get_contents('php://input'), true);
            $email = trim($input['email'] ?? '');
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->jsonResponse(false, 'Email no válido');
                return;
            }

            // Buscar usuario por email
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id, name, account_status, email_verified_at FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                // Respuesta genérica para no filtrar existencia
                $this->jsonResponse(true, 'Si el email existe, se reenviará el enlace de verificación.');
                return;
            }

            // Si ya está verificado, responder sin enviar
            if ($user['email_verified_at'] !== null || $user['account_status'] === 'active') {
                $this->jsonResponse(false, 'Tu email ya está verificado');
                return;
            }

            // Crear nuevo token y enviar
            require_once __DIR__ . '/../config/EmailVerification.php';
            $token = EmailVerification::createVerificationToken($user['id'], $email);
            if (!$token) {
                $this->jsonResponse(false, 'Error al generar el token de verificación');
                return;
            }
            $emailSent = EmailVerification::sendVerificationEmail($email, $token, $user['name'] ?? '');
            if ($emailSent) {
                $this->jsonResponse(true, 'Hemos enviado un nuevo enlace de verificación a tu email.');
            } else {
                $this->jsonResponse(false, 'Error al enviar el email. Por favor, inténtalo de nuevo.');
            }
        } catch (Exception $e) {
            error_log('Error resendVerificationEmailPublic: ' . $e->getMessage());
            $this->jsonResponse(false, 'Error interno del servidor');
        }
    }
}
?>
