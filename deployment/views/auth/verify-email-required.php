<?php
// views/auth/verify-email-required.php
$pageTitle = 'Verificación de Email Requerida';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-envelope-open-text text-warning" style="font-size: 4rem;"></i>
                        <h2 class="mt-3 mb-2">Verificación de Email Requerida</h2>
                        <p class="text-muted">Debes verificar tu email para continuar</p>
                    </div>

                    <div class="alert alert-warning" role="alert">
                        <h5 class="alert-heading">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Acceso Restringido
                        </h5>
                        <p class="mb-0">
                            Para acceder a todas las funcionalidades de la plataforma, 
                            necesitas verificar tu dirección de email.
                        </p>
                    </div>

                    <div class="text-center">
                        <h5>¿Qué necesitas hacer?</h5>
                        <ol class="text-start">
                            <li class="mb-2">
                                <strong>Revisa tu bandeja de entrada</strong><br>
                                <small class="text-muted">Busca un email de "Dev Rent - Sistema de Verificación"</small>
                            </li>
                            <li class="mb-2">
                                <strong>Haz clic en el enlace de verificación</strong><br>
                                <small class="text-muted">El enlace te llevará a una página de confirmación</small>
                            </li>
                            <li class="mb-3">
                                <strong>¡Listo! Tu cuenta estará activa</strong><br>
                                <small class="text-muted">Podrás acceder a todas las funcionalidades</small>
                            </li>
                        </ol>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" onclick="resendVerificationEmail()">
                            <i class="fas fa-paper-plane me-2"></i>
                            Reenviar Email de Verificación
                        </button>
                        <div class="input-group mt-2">
                            <input type="email" id="publicEmail" class="form-control" placeholder="Tu email (si no estás logueado)">
                            <button class="btn btn-outline-primary" onclick="resendVerificationEmailPublic()">Enviar sin iniciar sesión</button>
                        </div>
                        <a href="<?php echo APP_URL; ?>/logout" class="btn btn-outline-secondary">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            Cerrar Sesión
                        </a>
                    </div>

                    <div class="mt-4 text-center">
                        <small class="text-muted">
                            ¿No recibiste el email? Revisa tu carpeta de spam o 
                            <a href="#" onclick="resendVerificationEmail()">haz clic aquí para reenviarlo</a>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function resendVerificationEmail() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    // Mostrar estado de carga
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enviando...';
    button.disabled = true;
    
    fetch('<?php echo APP_URL; ?>/auth/resend-verification', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar mensaje de éxito
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show mt-3';
            alert.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            button.parentNode.appendChild(alert);
        } else {
            // Mostrar mensaje de error
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show mt-3';
            alert.innerHTML = `
                <i class="fas fa-exclamation-circle me-2"></i>
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            button.parentNode.appendChild(alert);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show mt-3';
        alert.innerHTML = `
            <i class="fas fa-exclamation-circle me-2"></i>
            Error al enviar el email. Por favor, inténtalo de nuevo.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        button.parentNode.appendChild(alert);
    })
    .finally(() => {
        // Restaurar botón
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function resendVerificationEmailPublic() {
    const email = document.getElementById('publicEmail').value.trim();
    if (!email) {
        alert('Introduce tu email');
        return;
    }
    fetch('<?php echo APP_URL; ?>/auth/resend-verification-public', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ email })
    })
    .then(r => r.json())
    .then(data => {
        const alert = document.createElement('div');
        alert.className = `alert ${data.success ? 'alert-success' : 'alert-danger'} alert-dismissible fade show mt-3`;
        alert.innerHTML = `
            <i class="fas ${data.success ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>
            ${data.message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('.d-grid.gap-2').appendChild(alert);
    })
    .catch(() => {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show mt-3';
        alert.innerHTML = `
            <i class="fas fa-exclamation-circle me-2"></i>
            Error al enviar el email. Por favor, inténtalo de nuevo.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('.d-grid.gap-2').appendChild(alert);
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
