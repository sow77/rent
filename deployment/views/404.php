<?php
// views/404.php
// No incluir header aquí, ya se incluye en el controlador
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="card">
                <div class="card-body py-5">
                    <div class="mb-4">
                        <i class="fas fa-exclamation-triangle fa-5x text-warning"></i>
                    </div>
                    <h1 class="display-4 fw-bold text-danger mb-3">404</h1>
                    <h2 class="h3 mb-4">Página no encontrada</h2>
                    <p class="lead text-muted mb-5">
                        Lo sentimos, la página que buscas no existe o ha sido movida.
                    </p>
                    <div class="d-grid gap-3 d-md-flex justify-content-md-center">
                        <a href="<?php echo APP_URL; ?>" class="btn btn-primary btn-lg">
                            <i class="fas fa-home me-2"></i>Volver al Inicio
                        </a>
                        <a href="<?php echo APP_URL; ?>/vehicles" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-car me-2"></i>Ver Vehículos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 