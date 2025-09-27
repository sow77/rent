<?php
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <h1 class="display-1">404</h1>
            <h2 class="mb-4">Página no encontrada</h2>
            <p class="lead mb-4">Lo sentimos, la página que estás buscando no existe o ha sido movida.</p>
            <a href="<?php echo APP_URL; ?>" class="btn btn-primary">Volver al inicio</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 