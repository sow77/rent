<?php
// views/auth/modals.php
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
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="registerModalLabel">
                    <i class="fas fa-user-plus"></i> Registrarse
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="registerForm" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="registerName" class="form-label">
                            <i class="fas fa-user"></i> Nombre
                        </label>
                        <input type="text" class="form-control" id="registerName" name="name" required>
                        <div class="invalid-feedback">Por favor ingrese su nombre.</div>
                    </div>
                    <div class="mb-3">
                        <label for="registerEmail" class="form-label">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input type="email" class="form-control" id="registerEmail" name="email" required>
                        <div class="invalid-feedback">Por favor ingrese un email válido.</div>
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