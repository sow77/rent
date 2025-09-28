<?php
// views/auth/verify-email.php
$pageTitle = 'Verificar Email';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-envelope text-primary" style="font-size: 3rem;"></i>
                        <h2 class="mt-3 mb-2">Verificar Email</h2>
                        <p class="text-muted">Confirma tu dirección de email</p>
                    </div>

                    <div id="verificationContainer">
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Verificando...</span>
                            </div>
                            <p>Verificando tu email...</p>
                        </div>
                    </div>

                    <div id="successMessage" class="d-none">
                        <div class="text-center">
                            <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                            <h4 class="text-success">¡Email Verificado!</h4>
                            <p class="text-muted">Tu email ha sido verificado correctamente.</p>
                            <p class="text-muted">Tu cuenta está ahora activa y puedes acceder a todas las funcionalidades.</p>
                            <a href="<?php echo APP_URL; ?>/login" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                            </a>
                        </div>
                    </div>

                    <div id="errorMessage" class="d-none">
                        <div class="text-center">
                            <i class="fas fa-exclamation-triangle text-danger mb-3" style="font-size: 3rem;"></i>
                            <h4 class="text-danger">Error de Verificación</h4>
                            <p class="text-muted" id="errorText">El enlace de verificación no es válido o ha expirado.</p>
                            <a href="<?php echo APP_URL; ?>/register" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> Registrarse Nuevamente
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const token = new URLSearchParams(window.location.search).get('token');
    const userId = new URLSearchParams(window.location.search).get('user_id');
    
    if (token) {
        verifyEmail(token);
    } else {
        showError('Token de verificación no válido');
    }
    
    async function verifyEmail(token) {
        try {
            const response = await fetch(`${APP_URL}/verify-email?token=${encodeURIComponent(token)}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.ok) {
                showSuccess(userId);
            } else {
                const data = await response.json();
                showError(data.message || 'Error al verificar el email');
            }
        } catch (error) {
            console.error('Error verifying email:', error);
            showError('Error de conexión. Por favor, inténtalo de nuevo.');
        }
    }
    
    function showSuccess(userId) {
        document.getElementById('verificationContainer').classList.add('d-none');
        document.getElementById('successMessage').classList.remove('d-none');
        
        // Verificación de teléfono DESHABILITADA temporalmente
        // TODO: Habilitar cuando se configure Twilio
        // if (userId) {
        //     const phoneLink = document.querySelector('#successMessage a');
        //     phoneLink.href = `${APP_URL}/verify-phone?user_id=${userId}`;
        // }
    }
    
    function showError(message) {
        document.getElementById('verificationContainer').classList.add('d-none');
        document.getElementById('errorMessage').classList.remove('d-none');
        document.getElementById('errorText').textContent = message;
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
