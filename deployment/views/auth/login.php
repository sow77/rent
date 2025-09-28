<?php
// views/auth/login.php
$pageTitle = I18n::t('auth.loginTitle');
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-sign-in-alt"></i> <?php echo I18n::t('auth.loginTitle'); ?>
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Mensajes del sistema -->
                    <?php if (isset($_GET['message'])): ?>
                        <?php if ($_GET['message'] === 'session_expired'): ?>
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Sesión expirada:</strong> Tu sesión ha expirado por inactividad. Por favor, inicia sesión nuevamente.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php elseif ($_GET['message'] === 'login_required'): ?>
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Acceso requerido:</strong> Debes iniciar sesión para acceder a esta página.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php elseif ($_GET['message'] === 'admin_required'): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-shield-alt me-2"></i>
                                <strong>Acceso denegado:</strong> Se requieren permisos de administrador para acceder a esta página.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <form id="loginForm" class="needs-validation" novalidate>
                        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect'] ?? ''); ?>">
                        <div class="mb-3">
                            <label for="loginEmail" class="form-label">
                                <i class="fas fa-envelope"></i> <?php echo I18n::t('auth.email'); ?>
                            </label>
                            <input type="email" class="form-control" id="loginEmail" name="email" required>
                            <div class="invalid-feedback"><?php echo I18n::t('auth.emailRequired'); ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">
                                <i class="fas fa-lock"></i> <?php echo I18n::t('auth.password'); ?>
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="loginPassword" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleLoginPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback"><?php echo I18n::t('auth.passwordRequired'); ?></div>
                        </div>
                        <div class="alert alert-danger d-none" id="loginError"></div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> <?php echo I18n::t('auth.login'); ?>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <p class="mb-0"><?php echo I18n::t('auth.noAccount'); ?> 
                        <a href="<?php echo APP_URL; ?>/auth/register"><?php echo I18n::t('auth.registerHere'); ?></a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const togglePassword = document.getElementById('toggleLoginPassword');
    const passwordInput = document.getElementById('loginPassword');
    const errorDiv = document.getElementById('loginError');

    // Toggle password visibility
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    // Handle form submission
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!this.checkValidity()) {
            e.stopPropagation();
            this.classList.add('was-validated');
            return;
        }

        const formData = new FormData(this);
        
        try {
            const response = await fetch(`${APP_URL}/auth/login`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                window.location.href = data.data.redirect || APP_URL;
            } else {
                errorDiv.textContent = data.message;
                errorDiv.classList.remove('d-none');
            }
        } catch (error) {
            errorDiv.textContent = '<?php echo I18n::t('auth.requestError'); ?>';
            errorDiv.classList.remove('d-none');
        }
    });
});

// Limpiar el parámetro de mensaje de la URL después de mostrar el mensaje
if (window.location.search.includes('message=')) {
    // Limpiar la URL después de 5 segundos
    setTimeout(() => {
        const url = new URL(window.location);
        url.searchParams.delete('message');
        window.history.replaceState({}, document.title, url.pathname);
    }, 5000);
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 