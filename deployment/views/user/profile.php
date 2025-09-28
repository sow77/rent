<?php
// views/user/profile.php
// No incluir header aquí, ya se incluye en el controlador
?>

<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="fas fa-user-edit"></i> Mi Perfil
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-edit"></i> Editar Información Personal
                    </h5>
                </div>
                <div class="card-body">
                    <form id="profileForm" action="<?php echo APP_URL; ?>/user/update-profile" method="POST" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user"></i> Nombre Completo
                                </label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                <div class="invalid-feedback">
                                    Por favor ingresa tu nombre completo.
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i> Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                <div class="invalid-feedback">
                                    Por favor ingresa un email válido.
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        
                        <h6 class="mb-3">
                            <i class="fas fa-lock"></i> Cambiar Contraseña (opcional)
                        </h6>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="current_password" class="form-label">Contraseña Actual</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                                <div class="form-text">Solo si quieres cambiar tu contraseña</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_password" class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" 
                                       minlength="6">
                                <div class="form-text">Mínimo 6 caracteres</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                <div class="invalid-feedback">
                                    Las contraseñas no coinciden.
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?php echo APP_URL; ?>/user/dashboard" class="btn btn-secondary me-md-2">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Información y enlaces rápidos -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Información Actual
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Nombre:</strong><br>
                            <span class="text-muted small"><?php echo htmlspecialchars($user['name']); ?></span>
                        </div>
                        <div class="col-6">
                            <strong>Email:</strong><br>
                            <span class="text-muted small"><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Rol:</strong><br>
                            <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </div>
                        <div class="col-6">
                            <strong>Miembro desde:</strong><br>
                            <span class="text-muted small"><?php echo date('d/m/Y', strtotime($user['created_at'] ?? 'now')); ?></span>
                        </div>
                    </div>
                    
                    <hr class="my-3">
                    
                    <h6 class="mb-2">
                        <i class="fas fa-link"></i> Enlaces Rápidos
                    </h6>
                    <div class="d-grid gap-1">
                        <a href="<?php echo APP_URL; ?>/user/dashboard" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="<?php echo APP_URL; ?>/user/reservations" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-list"></i> Mis Reservas
                        </a>
                        <a href="<?php echo APP_URL; ?>/vehicles" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-car"></i> Alquilar Vehículo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación de contraseñas
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (newPassword && confirmPassword && newPassword !== confirmPassword) {
        this.setCustomValidity('Las contraseñas no coinciden');
    } else {
        this.setCustomValidity('');
    }
});

document.getElementById('new_password').addEventListener('input', function() {
    const confirmPassword = document.getElementById('confirm_password');
    if (confirmPassword.value) {
        confirmPassword.dispatchEvent(new Event('input'));
    }
});
</script>

 