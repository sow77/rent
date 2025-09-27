<?php
// views/auth/register.php
$pageTitle = I18n::t('auth.registerTitle');
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-user-plus"></i> <?php echo I18n::t('auth.registerTitle'); ?>
                    </h4>
                </div>
                <div class="card-body">
                    <form id="registerForm" class="needs-validation" novalidate>
                        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect'] ?? ''); ?>">
                        <div class="mb-3">
                            <label for="registerName" class="form-label">
                                <i class="fas fa-user"></i> <?php echo I18n::t('auth.name'); ?>
                            </label>
                            <input type="text" class="form-control" id="registerName" name="name" required>
                            <div class="invalid-feedback"><?php echo I18n::t('auth.nameRequired'); ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="registerEmail" class="form-label">
                                <i class="fas fa-envelope"></i> <?php echo I18n::t('auth.email'); ?>
                            </label>
                            <input type="email" class="form-control" id="registerEmail" name="email" required>
                            <div class="invalid-feedback"><?php echo I18n::t('auth.emailRequired'); ?></div>
                            <div class="form-text">Solo emails auténticos, no se permiten emails temporales</div>
                        </div>
                        <div class="mb-3">
                            <label for="registerPhone" class="form-label">
                                <i class="fas fa-phone"></i> Número de Teléfono
                            </label>
                            <input type="tel" class="form-control" id="registerPhone" name="phone" required 
                                   placeholder="+34 123 456 789" pattern="^(\+?[1-9]\d{1,14})$">
                            <div class="invalid-feedback">Número de teléfono válido requerido</div>
                            <div class="form-text">Formato internacional: +34 123 456 789</div>
                        </div>
                        <div class="mb-3">
                            <label for="registerPassword" class="form-label">
                                <i class="fas fa-lock"></i> <?php echo I18n::t('auth.password'); ?>
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="registerPassword" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleRegisterPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback"><?php echo I18n::t('auth.passwordRequired'); ?></div>
                            <div class="form-text">
                                <small>La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas, números y caracteres especiales</small>
                            </div>
                            <div class="password-strength mt-2">
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar" id="passwordStrength" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small id="passwordStrengthText" class="text-muted">Introduce una contraseña</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="registerPasswordConfirm" class="form-label">
                                <i class="fas fa-lock"></i> <?php echo I18n::t('auth.confirmPassword'); ?>
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="registerPasswordConfirm" name="password_confirm" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleRegisterPasswordConfirm">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback"><?php echo I18n::t('auth.passwordsMustMatch'); ?></div>
                        </div>
                        <div class="alert alert-danger d-none" id="registerError"></div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-user-plus"></i> <?php echo I18n::t('auth.register'); ?>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <p class="mb-0"><?php echo I18n::t('auth.haveAccount'); ?> 
                        <a href="<?php echo APP_URL; ?>/auth/login"><?php echo I18n::t('auth.loginHere'); ?></a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    const togglePassword = document.getElementById('toggleRegisterPassword');
    const togglePasswordConfirm = document.getElementById('toggleRegisterPasswordConfirm');
    const passwordInput = document.getElementById('registerPassword');
    const passwordConfirmInput = document.getElementById('registerPasswordConfirm');
    const phoneInput = document.getElementById('registerPhone');
    const errorDiv = document.getElementById('registerError');
    const passwordStrength = document.getElementById('passwordStrength');
    const passwordStrengthText = document.getElementById('passwordStrengthText');

    // Toggle password visibility
    function setupPasswordToggle(input, toggle) {
        toggle.addEventListener('click', function() {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }

    setupPasswordToggle(passwordInput, togglePassword);
    setupPasswordToggle(passwordConfirmInput, togglePasswordConfirm);

    // Validación de contraseña en tiempo real
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const strength = calculatePasswordStrength(password);
        
        passwordStrength.style.width = strength.score + '%';
        passwordStrength.className = 'progress-bar ' + strength.class;
        passwordStrengthText.textContent = strength.text;
        passwordStrengthText.className = 'text-' + strength.textClass;
    });

    // Validación de teléfono en tiempo real
    phoneInput.addEventListener('input', function() {
        let value = this.value.replace(/[^0-9+]/g, '');
        if (value && !value.startsWith('+')) {
            value = '+' + value;
        }
        this.value = value;
    });

    // Función para calcular la fortaleza de la contraseña
    function calculatePasswordStrength(password) {
        let score = 0;
        let feedback = [];

        if (password.length >= 8) score += 20;
        else feedback.push('Al menos 8 caracteres');

        if (/[a-z]/.test(password)) score += 20;
        else feedback.push('Letras minúsculas');

        if (/[A-Z]/.test(password)) score += 20;
        else feedback.push('Letras mayúsculas');

        if (/[0-9]/.test(password)) score += 20;
        else feedback.push('Números');

        if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`]/.test(password)) score += 20;
        else feedback.push('Caracteres especiales');

        if (password.length > 12) score += 10;
        if (password.length > 16) score += 10;

        let className = 'bg-danger';
        let textClass = 'danger';
        let text = 'Muy débil';

        if (score >= 80) {
            className = 'bg-success';
            textClass = 'success';
            text = 'Muy fuerte';
        } else if (score >= 60) {
            className = 'bg-warning';
            textClass = 'warning';
            text = 'Fuerte';
        } else if (score >= 40) {
            className = 'bg-info';
            textClass = 'info';
            text = 'Media';
        } else if (score >= 20) {
            className = 'bg-warning';
            textClass = 'warning';
            text = 'Débil';
        }

        if (feedback.length > 0) {
            text += ' - Falta: ' + feedback.join(', ');
        }

        return { score, class: className, text, textClass };
    }

    // Validación del formulario
    function validateForm() {
        const name = document.getElementById('registerName').value.trim();
        const email = document.getElementById('registerEmail').value.trim();
        const phone = phoneInput.value.trim();
        const password = passwordInput.value;
        const passwordConfirm = passwordConfirmInput.value;

        const errors = [];

        // Validar nombre
        if (name.length < 2) {
            errors.push('El nombre debe tener al menos 2 caracteres');
        }

        // Validar email
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errors.push('El formato del email no es válido');
        }

        // Validar teléfono
        if (!/^(\+?[1-9]\d{1,14})$/.test(phone)) {
            errors.push('El formato del número de teléfono no es válido');
        }

        // Validar contraseña
        if (password.length < 8) {
            errors.push('La contraseña debe tener al menos 8 caracteres');
        }

        if (!/[a-z]/.test(password)) {
            errors.push('La contraseña debe contener al menos una letra minúscula');
        }

        if (!/[A-Z]/.test(password)) {
            errors.push('La contraseña debe contener al menos una letra mayúscula');
        }

        if (!/[0-9]/.test(password)) {
            errors.push('La contraseña debe contener al menos un número');
        }

        if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`]/.test(password)) {
            errors.push('La contraseña debe contener al menos un carácter especial');
        }

        if (password !== passwordConfirm) {
            errors.push('Las contraseñas no coinciden');
        }

        return errors;
    }

    // Handle form submission
    registerForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Limpiar errores anteriores
        errorDiv.classList.add('d-none');
        errorDiv.innerHTML = '';

        // Validar formulario
        const errors = validateForm();
        if (errors.length > 0) {
            errorDiv.innerHTML = '<ul class="mb-0"><li>' + errors.join('</li><li>') + '</li></ul>';
            errorDiv.classList.remove('d-none');
            return;
        }

        const formData = new FormData(this);
        
        try {
            const response = await fetch(`${APP_URL}/auth/register`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                window.location.href = data.data.redirect || `${APP_URL}/auth/login?registered=1`;
            } else {
                if (data.data && Array.isArray(data.data)) {
                    errorDiv.innerHTML = '<ul class="mb-0"><li>' + data.data.join('</li><li>') + '</li></ul>';
                } else {
                    errorDiv.textContent = data.message;
                }
                errorDiv.classList.remove('d-none');
            }
        } catch (error) {
            errorDiv.textContent = 'Error de conexión. Por favor, inténtalo de nuevo.';
            errorDiv.classList.remove('d-none');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 