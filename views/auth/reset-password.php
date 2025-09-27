<?php
// views/auth/reset-password.php
$pageTitle = 'Restablecer Contraseña';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-lock text-primary" style="font-size: 3rem;"></i>
                        <h2 class="mt-3 mb-2">Restablecer Contraseña</h2>
                        <p class="text-muted">Ingresa tu nueva contraseña</p>
                    </div>

                    <div id="resetFormContainer">
                        <form id="resetPasswordPageForm" class="needs-validation" novalidate>
                            <input type="hidden" id="resetTokenPage" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
                            
                            <div class="mb-3">
                                <label for="newPasswordPage" class="form-label">
                                    <i class="fas fa-lock"></i> Nueva Contraseña
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="newPasswordPage" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleNewPasswordPage">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Por favor ingrese una contraseña válida.</div>
                                <div class="form-text">
                                    <small>La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas, números y caracteres especiales</small>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="confirmNewPasswordPage" class="form-label">
                                    <i class="fas fa-lock"></i> Confirmar Nueva Contraseña
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirmNewPasswordPage" name="password_confirm" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirmNewPasswordPage">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Las contraseñas deben coincidir.</div>
                            </div>
                            
                            <div class="alert alert-danger d-none" id="resetPasswordPageError"></div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Cambiar Contraseña
                                </button>
                            </div>
                        </form>
                    </div>

                    <div id="successMessage" class="d-none">
                        <div class="text-center">
                            <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                            <h4 class="text-success">¡Contraseña Actualizada!</h4>
                            <p class="text-muted">Tu contraseña ha sido restablecida exitosamente.</p>
                            <a href="<?php echo APP_URL; ?>/login" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                            </a>
                        </div>
                    </div>

                    <div id="errorMessage" class="d-none">
                        <div class="text-center">
                            <i class="fas fa-exclamation-triangle text-danger mb-3" style="font-size: 3rem;"></i>
                            <h4 class="text-danger">Token Inválido</h4>
                            <p class="text-muted">El enlace de recuperación no es válido o ha expirado.</p>
                            <a href="<?php echo APP_URL; ?>" class="btn btn-primary">
                                <i class="fas fa-home"></i> Volver al Inicio
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
    const token = document.getElementById('resetTokenPage').value;
    
    // Validar token al cargar la página
    if (token) {
        validateToken();
    } else {
        showError();
    }
    
    // Configurar toggles de contraseña (defensivo si no existe la función global)
    if (typeof setupPasswordToggle === 'function') {
        setupPasswordToggle('newPasswordPage', 'toggleNewPasswordPage');
        setupPasswordToggle('confirmNewPasswordPage', 'toggleConfirmNewPasswordPage');
    } else {
        const a = document.getElementById('toggleNewPasswordPage');
        const b = document.getElementById('toggleConfirmNewPasswordPage');
        if (a) a.addEventListener('click', () => {
            const input = document.getElementById('newPasswordPage');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            a.querySelector('i').classList.toggle('fa-eye');
            a.querySelector('i').classList.toggle('fa-eye-slash');
        });
        if (b) b.addEventListener('click', () => {
            const input = document.getElementById('confirmNewPasswordPage');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            b.querySelector('i').classList.toggle('fa-eye');
            b.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }
    
    // Manejar formulario
    const form = document.getElementById('resetPasswordPageForm');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const password = document.getElementById('newPasswordPage').value;
            const passwordConfirm = document.getElementById('confirmNewPasswordPage').value;
            const errorDiv = document.getElementById('resetPasswordPageError');
            
            if (password !== passwordConfirm) {
                if (errorDiv) {
                    errorDiv.textContent = 'Las contraseñas no coinciden';
                    errorDiv.classList.remove('d-none');
                }
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('token', token);
                formData.append('password', password);
                formData.append('password_confirm', passwordConfirm);
                
                const response = await fetch(`${APP_URL}/auth/reset-password`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showSuccess();
                } else {
                    if (errorDiv) {
                        errorDiv.textContent = data.message;
                        errorDiv.classList.remove('d-none');
                    }
                }
            } catch (error) {
                console.error('Error en reset de contraseña:', error);
                if (errorDiv) {
                    errorDiv.textContent = 'Error al procesar la solicitud. Por favor, inténtalo de nuevo.';
                    errorDiv.classList.remove('d-none');
                }
            }
        });
    }
    
    async function validateToken() {
        try {
            const response = await fetch(`${APP_URL}/auth/validate-reset-token`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ token: token })
            });
            
            const data = await response.json();
            
            if (!data.success) {
                showError();
            }
        } catch (error) {
            console.error('Error validando token:', error);
            showError();
        }
    }
    
    function showSuccess() {
        // Redirigir al home y abrir modal de login
        window.location.href = `${APP_URL}/?open=login&reset=1`;
    }
    
    function showError() {
        document.getElementById('resetFormContainer').classList.add('d-none');
        document.getElementById('errorMessage').classList.remove('d-none');
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
