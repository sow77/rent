<?php
/**
 * Controlador para verificación de email y teléfono
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/EmailVerification.php';
require_once __DIR__ . '/../config/PhoneVerification.php';
require_once __DIR__ . '/../config/EnterpriseEmailService.php';
require_once __DIR__ . '/../config/EnterpriseSMSService.php';
require_once __DIR__ . '/../config/EnterpriseSecurityService.php';

class VerificationController {
    
    /**
     * Verificar email con token
     */
    public function verifyEmail() {
        try {
            $token = $_GET['token'] ?? '';
            
            if (empty($token)) {
                if ($this->isAjaxRequest()) {
                    $this->sendJsonResponse(['success' => false, 'message' => 'Token de verificación no válido']);
                } else {
                    $this->showError('Token de verificación no válido');
                }
                return;
            }
            
            $result = EmailVerification::verifyToken($token);
            
            if ($result['success']) {
                if ($this->isAjaxRequest()) {
                    $this->sendJsonResponse(['success' => true, 'user_id' => $result['user']['id']]);
                } else {
                    // Mostrar página de éxito y enlace a login (teléfono deshabilitado temporalmente)
                    $this->showSuccess('Tu email ha sido verificado correctamente. Ya puedes iniciar sesión.');
                    return;
                }
            } else {
                if ($this->isAjaxRequest()) {
                    $this->sendJsonResponse(['success' => false, 'message' => $result['message']]);
                } else {
                    $this->showError($result['message']);
                }
            }
        } catch (Exception $e) {
            error_log("Error verifying email: " . $e->getMessage());
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(['success' => false, 'message' => 'Error interno del servidor']);
            } else {
                $this->showError('Error interno del servidor');
            }
        }
    }
    
    public function showVerifyPhonePage() {
        $pageTitle = 'Verificar Teléfono';
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/auth/verify-phone.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
    
    /**
     * Enviar código OTP para verificación de teléfono
     */
    public function sendPhoneOTP() {
        try {
            header('Content-Type: application/json');
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->jsonResponse(false, 'Método no permitido');
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $phone = $input['phone'] ?? '';
            $userId = $input['user_id'] ?? '';
            
            if (empty($phone) || empty($userId)) {
                $this->jsonResponse(false, 'Teléfono y ID de usuario requeridos');
                return;
            }
            
            $result = EnterpriseSMSService::sendOTP($phone, $userId);
            
            if ($result) {
                $this->jsonResponse(true, 'Código OTP enviado correctamente');
            } else {
                $this->jsonResponse(false, 'Error al enviar el código OTP');
            }
        } catch (Exception $e) {
            error_log("Error sending OTP: " . $e->getMessage());
            $this->jsonResponse(false, 'Error interno del servidor');
        }
    }
    
    /**
     * Verificar código OTP
     */
    public function verifyPhoneOTP() {
        try {
            header('Content-Type: application/json');
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->jsonResponse(false, 'Método no permitido');
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $phone = $input['phone'] ?? '';
            $otp = $input['otp'] ?? '';
            $userId = $input['user_id'] ?? '';
            
            if (empty($phone) || empty($otp) || empty($userId)) {
                $this->jsonResponse(false, 'Todos los campos son requeridos');
                return;
            }
            
            $result = EnterpriseSMSService::verifyOTP($phone, $otp, $userId);
            
            if ($result['success']) {
                $this->jsonResponse(true, $result['message']);
            } else {
                $this->jsonResponse(false, $result['message']);
            }
        } catch (Exception $e) {
            error_log("Error verifying OTP: " . $e->getMessage());
            $this->jsonResponse(false, 'Error interno del servidor');
        }
    }
    
    /**
     * Mostrar página de éxito
     */
    private function showSuccess($message) {
        $pageTitle = 'Verificación exitosa';
        require_once __DIR__ . '/../views/layouts/header.php';
        echo '<div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="alert alert-success text-center">
                        <h4>¡Verificación exitosa!</h4>
                        <p>' . htmlspecialchars($message) . '</p>
                        <a href="' . APP_URL . '/login" class="btn btn-primary">Iniciar Sesión</a>
                    </div>
                </div>
            </div>
        </div>';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
    
    /**
     * Mostrar página de error
     */
    private function showError($message) {
        $pageTitle = 'Error de verificación';
        require_once __DIR__ . '/../views/layouts/header.php';
        echo '<div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="alert alert-danger text-center">
                        <h4>Error de verificación</h4>
                        <p>' . htmlspecialchars($message) . '</p>
                        <a href="' . APP_URL . '/register" class="btn btn-primary">Registrarse</a>
                    </div>
                </div>
            </div>
        </div>';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
    
    /**
     * Verificar si es una petición AJAX
     */
    private function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    /**
     * Enviar respuesta JSON
     */
    private function sendJsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Respuesta JSON
     */
    private function jsonResponse($success, $message, $data = []) {
        $response = [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
