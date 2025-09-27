<?php
// views/auth/verify-phone.php
$pageTitle = 'Verificar Teléfono';
require_once __DIR__ . '/../layouts/header.php';

$userId = $_GET['user_id'] ?? '';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-phone text-primary" style="font-size: 3rem;"></i>
                        <h2 class="mt-3 mb-2">Verificar Teléfono</h2>
                        <p class="text-muted">Confirma tu número de teléfono</p>
                    </div>

                    <div id="phoneForm">
                        <form id="phoneVerificationForm">
                            <input type="hidden" id="userId" value="<?php echo htmlspecialchars($userId); ?>">
                            
                            <div class="mb-3">
                                <label for="phoneNumber" class="form-label">
                                    <i class="fas fa-phone"></i> Número de Teléfono
                                </label>
                                <input type="tel" class="form-control" id="phoneNumber" name="phone" required 
                                       placeholder="+34 123 456 789" pattern="^(\+?[1-9]\d{1,14})$">
                                <div class="invalid-feedback">Por favor ingrese un número de teléfono válido.</div>
                            </div>
                            
                            <div class="d-grid mb-3">
                                <button type="button" id="sendOTPBtn" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Enviar Código
                                </button>
                            </div>
                        </form>
                    </div>

                    <div id="otpForm" class="d-none">
                        <form id="otpVerificationForm">
                            <input type="hidden" id="otpUserId" value="<?php echo htmlspecialchars($userId); ?>">
                            <input type="hidden" id="otpPhoneNumber">
                            
                            <div class="mb-3">
                                <label for="otpCode" class="form-label">
                                    <i class="fas fa-key"></i> Código de Verificación
                                </label>
                                <input type="text" class="form-control text-center" id="otpCode" name="otp" required 
                                       placeholder="123456" maxlength="6" pattern="[0-9]{6}">
                                <div class="invalid-feedback">Por favor ingrese el código de 6 dígitos.</div>
                                <div class="form-text">
                                    Ingresa el código de 6 dígitos que enviamos a tu teléfono
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" id="verifyOTPBtn" class="btn btn-success">
                                    <i class="fas fa-check"></i> Verificar Código
                                </button>
                                <button type="button" id="resendOTPBtn" class="btn btn-outline-primary">
                                    <i class="fas fa-redo"></i> Reenviar Código
                                </button>
                            </div>
                        </form>
                    </div>

                    <div id="successMessage" class="d-none">
                        <div class="text-center">
                            <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                            <h4 class="text-success">¡Teléfono Verificado!</h4>
                            <p class="text-muted">Tu número de teléfono ha sido verificado correctamente.</p>
                            <p class="text-muted">¡Tu cuenta está completamente activa!</p>
                            <a href="<?php echo APP_URL; ?>/login" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                            </a>
                        </div>
                    </div>

                    <div id="errorMessage" class="d-none">
                        <div class="text-center">
                            <i class="fas fa-exclamation-triangle text-danger mb-3" style="font-size: 3rem;"></i>
                            <h4 class="text-danger">Error de Verificación</h4>
                            <p class="text-muted" id="errorText">Error al verificar el teléfono.</p>
                            <button type="button" id="retryBtn" class="btn btn-primary">
                                <i class="fas fa-redo"></i> Intentar Nuevamente
                            </button>
                        </div>
                    </div>

                    <div class="alert alert-danger d-none" id="formError"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userId = document.getElementById('userId').value;
    
    if (!userId) {
        showError('ID de usuario no válido');
        return;
    }
    
    // Configurar formulario de teléfono
    const phoneForm = document.getElementById('phoneVerificationForm');
    const otpForm = document.getElementById('otpVerificationForm');
    const sendOTPBtn = document.getElementById('sendOTPBtn');
    const verifyOTPBtn = document.getElementById('verifyOTPBtn');
    const resendOTPBtn = document.getElementById('resendOTPBtn');
    const retryBtn = document.getElementById('retryBtn');
    
    // Enviar código OTP
    sendOTPBtn.addEventListener('click', async function() {
        const phone = document.getElementById('phoneNumber').value.trim();
        
        if (!phone) {
            showFormError('Por favor ingrese su número de teléfono');
            return;
        }
        
        if (!/^\+[1-9]\d{8,14}$/.test(phone)) {
            showFormError('Formato de teléfono inválido. Use formato internacional: +34 123 456 789');
            return;
        }
        
        try {
            sendOTPBtn.disabled = true;
            sendOTPBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            
            const response = await fetch(`${APP_URL}/auth/send-phone-otp`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    phone: phone,
                    user_id: userId
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Mostrar formulario de OTP
                document.getElementById('phoneForm').classList.add('d-none');
                document.getElementById('otpForm').classList.remove('d-none');
                document.getElementById('otpPhoneNumber').value = phone;
                
                // Iniciar countdown para reenvío
                startResendCountdown();
            } else {
                showFormError(data.message || 'Error al enviar el código');
            }
        } catch (error) {
            console.error('Error sending OTP:', error);
            showFormError('Error de conexión. Por favor, inténtalo de nuevo.');
        } finally {
            sendOTPBtn.disabled = false;
            sendOTPBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Código';
        }
    });
    
    // Verificar código OTP
    otpForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const phone = document.getElementById('otpPhoneNumber').value;
        const otp = document.getElementById('otpCode').value.trim();
        
        if (!otp || otp.length !== 6) {
            showFormError('Por favor ingrese el código de 6 dígitos');
            return;
        }
        
        try {
            verifyOTPBtn.disabled = true;
            verifyOTPBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
            
            const response = await fetch(`${APP_URL}/auth/verify-phone-otp`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    phone: phone,
                    otp: otp,
                    user_id: userId
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showSuccess();
            } else {
                showFormError(data.message || 'Código inválido');
            }
        } catch (error) {
            console.error('Error verifying OTP:', error);
            showFormError('Error de conexión. Por favor, inténtalo de nuevo.');
        } finally {
            verifyOTPBtn.disabled = false;
            verifyOTPBtn.innerHTML = '<i class="fas fa-check"></i> Verificar Código';
        }
    });
    
    // Reenviar código OTP
    resendOTPBtn.addEventListener('click', async function() {
        const phone = document.getElementById('otpPhoneNumber').value;
        
        try {
            resendOTPBtn.disabled = true;
            resendOTPBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Reenviando...';
            
            const response = await fetch(`${APP_URL}/auth/send-phone-otp`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    phone: phone,
                    user_id: userId
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showFormError('Código reenviado correctamente', 'success');
                startResendCountdown();
            } else {
                showFormError(data.message || 'Error al reenviar el código');
            }
        } catch (error) {
            console.error('Error resending OTP:', error);
            showFormError('Error de conexión. Por favor, inténtalo de nuevo.');
        } finally {
            resendOTPBtn.disabled = false;
            resendOTPBtn.innerHTML = '<i class="fas fa-redo"></i> Reenviar Código';
        }
    });
    
    // Reintentar
    retryBtn.addEventListener('click', function() {
        document.getElementById('errorMessage').classList.add('d-none');
        document.getElementById('phoneForm').classList.remove('d-none');
        document.getElementById('otpForm').classList.add('d-none');
        document.getElementById('phoneNumber').value = '';
        document.getElementById('otpCode').value = '';
    });
    
    function showSuccess() {
        document.getElementById('phoneForm').classList.add('d-none');
        document.getElementById('otpForm').classList.add('d-none');
        document.getElementById('successMessage').classList.remove('d-none');
    }
    
    function showError(message) {
        document.getElementById('phoneForm').classList.add('d-none');
        document.getElementById('otpForm').classList.add('d-none');
        document.getElementById('errorMessage').classList.remove('d-none');
        document.getElementById('errorText').textContent = message;
    }
    
    function showFormError(message, type = 'danger') {
        const errorDiv = document.getElementById('formError');
        errorDiv.textContent = message;
        errorDiv.className = `alert alert-${type}`;
        errorDiv.classList.remove('d-none');
        
        setTimeout(() => {
            errorDiv.classList.add('d-none');
        }, 5000);
    }
    
    function startResendCountdown() {
        let countdown = 60;
        resendOTPBtn.disabled = true;
        
        const interval = setInterval(() => {
            resendOTPBtn.innerHTML = `<i class="fas fa-redo"></i> Reenviar (${countdown}s)`;
            countdown--;
            
            if (countdown < 0) {
                clearInterval(interval);
                resendOTPBtn.disabled = false;
                resendOTPBtn.innerHTML = '<i class="fas fa-redo"></i> Reenviar Código';
            }
        }, 1000);
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
