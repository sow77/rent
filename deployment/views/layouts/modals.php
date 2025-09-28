<?php
// views/layouts/modals.php
?>
<!-- Modal de Login -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="loginModalLabel">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="loginForm" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="loginEmail" class="form-label">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input type="email" class="form-control" id="loginEmail" name="email" required>
                        <div class="invalid-feedback">Por favor ingrese un email válido.</div>
                    </div>
                    <div class="mb-3">
                        <label for="loginPassword" class="form-label">
                            <i class="fas fa-lock"></i> Contraseña
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="loginPassword" name="password" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleLoginPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Por favor ingrese su contraseña.</div>
                    </div>
                    <div class="alert alert-danger d-none" id="loginError"></div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                        </button>
                    </div>
                    <div class="text-center mt-3">
                        <a href="#" id="forgotPasswordLink" class="text-decoration-none">
                            <i class="fas fa-key me-1"></i>¿Olvidaste tu contraseña?
                        </a>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-center">
                <p class="mb-0">¿No tienes una cuenta? 
                    <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">
                        Regístrate aquí
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Registro -->
<div class="modal fade" id="registerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus"></i> Registrarse
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="registerForm" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="registerName" class="form-label">
                            <i class="fas fa-user"></i> Nombre
                        </label>
                        <input type="text" class="form-control" id="registerName" name="name" required>
                        <div class="invalid-feedback">Por favor ingrese su nombre.</div>
                        <div id="registerNameError" class="text-danger small d-none"></div>
                    </div>
                    <div class="mb-3">
                        <label for="registerEmail" class="form-label">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input type="email" class="form-control" id="registerEmail" name="email" required>
                        <div class="invalid-feedback">Por favor ingrese un email válido.</div>
                        <div id="registerEmailError" class="text-danger small d-none"></div>
                        <div class="form-text">Solo emails auténticos, no se permiten emails temporales</div>
                    </div>
                    <div class="mb-3">
                        <label for="registerPhone" class="form-label">
                            <i class="fas fa-phone"></i> Número de Teléfono
                        </label>
                        <input type="tel" class="form-control" id="registerPhone" name="phone" required 
                               placeholder="+34 123 456 789" pattern="^(\+?[1-9]\d{1,14})$">
                        <div class="invalid-feedback">Número de teléfono válido requerido</div>
                        <div id="registerPhoneError" class="text-danger small d-none"></div>
                        <div class="form-text">Formato internacional: +34 123 456 789</div>
                    </div>
                    <div class="mb-3">
                        <label for="registerPassword" class="form-label">
                            <i class="fas fa-lock"></i> Contraseña
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="registerPassword" name="password" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleRegisterPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Por favor ingrese una contraseña.</div>
                        <div id="registerPasswordError" class="text-danger small d-none"></div>
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
                            <i class="fas fa-lock"></i> Confirmar Contraseña
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="registerPasswordConfirm" name="password_confirm" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleRegisterPasswordConfirm">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Las contraseñas deben coincidir.</div>
                        <div id="registerPasswordConfirmError" class="text-danger small d-none"></div>
                    </div>
                    <div class="mb-3">
                        <div class="g-recaptcha" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>
                        <div id="recaptchaError" class="text-danger small d-none"></div>
                    </div>
                    <div class="alert alert-danger d-none" id="registerError"></div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-user-plus"></i> Registrarse
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-center">
                <p class="mb-0">¿Ya tienes una cuenta? 
                    <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">
                        Inicia sesión aquí
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Recuperación de Contraseña -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-key"></i> Recuperar Contraseña
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="forgotPasswordStep1">
                    <p class="text-muted mb-4">
                        Ingresa tu email y te enviaremos un enlace para restablecer tu contraseña.
                    </p>
                    <form id="forgotPasswordForm" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="forgotEmail" class="form-label">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <input type="email" class="form-control" id="forgotEmail" name="email" required>
                            <div class="invalid-feedback">Por favor ingrese un email válido.</div>
                        </div>
                        <div class="alert alert-danger d-none" id="forgotPasswordError"></div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-paper-plane"></i> Enviar Enlace de Recuperación
                            </button>
                        </div>
                    </form>
                </div>
                <div id="forgotPasswordStep2" class="d-none">
                    <div class="text-center">
                        <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                        <h5 class="text-success">¡Email Enviado!</h5>
                        <p class="text-muted">
                            Hemos enviado un enlace de recuperación a tu email. 
                            Revisa tu bandeja de entrada y sigue las instrucciones.
                        </p>
                        <p class="text-muted small">
                            Si no recibes el email en unos minutos, revisa tu carpeta de spam.
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <p class="mb-0">¿Recordaste tu contraseña? 
                    <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">
                        Inicia sesión aquí
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Reset de Contraseña -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-lock"></i> Nueva Contraseña
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="resetPasswordForm" class="needs-validation" novalidate>
                    <input type="hidden" id="resetToken" name="token">
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">
                            <i class="fas fa-lock"></i> Nueva Contraseña
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="newPassword" name="password" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Por favor ingrese una contraseña válida.</div>
                        <div class="form-text">
                            <small>La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas, números y caracteres especiales</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="confirmNewPassword" class="form-label">
                            <i class="fas fa-lock"></i> Confirmar Nueva Contraseña
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirmNewPassword" name="password_confirm" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmNewPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Las contraseñas deben coincidir.</div>
                    </div>
                    <div class="alert alert-danger d-none" id="resetPasswordError"></div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-save"></i> Cambiar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Vehicle Details Modal -->
<div class="modal fade" id="vehiclesDetailsModal" tabindex="-1" aria-labelledby="vehiclesDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo I18n::t('vehicles.details.title'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- El contenido se cargará dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="boatsDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo I18n::t('boats.details.title'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- El contenido se cargará dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="transfersDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo I18n::t('transfers.details.title'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- El contenido se cargará dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Error</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- El mensaje de error se mostrará aquí -->
            </div>
        </div>
    </div>
</div> 