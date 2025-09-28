<?php
// views/layouts/footer.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/i18n.php';

// Asegurarnos de que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentLang = I18n::getCurrentLang();
$baseUrl = APP_URL;
?>

<footer class="mt-5 py-4"> 
        <div class="container">
            <div class="row">
            <div class="col-md-4">
                    <h5 class="text-white fw-bold"><?php echo APP_NAME; ?></h5>
                <p class="text-light"><?php echo I18n::t('footer.description'); ?></p>
                </div>
            <div class="col-md-4">
                <h5 class="text-white fw-bold"><?php echo I18n::t('footer.quick_links'); ?></h5>
                    <ul class="list-unstyled">
                    <li><a href="<?php echo $baseUrl; ?>/vehicles" class="text-light text-decoration-none"><?php echo I18n::t('nav.vehicles'); ?></a></li>
                    <li><a href="<?php echo $baseUrl; ?>/boats" class="text-light text-decoration-none"><?php echo I18n::t('nav.boats'); ?></a></li>
                    <li><a href="<?php echo $baseUrl; ?>/transfers" class="text-light text-decoration-none"><?php echo I18n::t('nav.transfers'); ?></a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                <h5 class="text-white fw-bold"><?php echo I18n::t('footer.contact'); ?></h5>
                <ul class="list-unstyled">
                    <li class="text-light"><i class="fas fa-phone me-2"></i> <?php echo I18n::t('footer.phone'); ?></li>
                    <li class="text-light"><i class="fas fa-envelope me-2"></i> <?php echo I18n::t('footer.email'); ?></li>
                    <li class="text-light"><i class="fas fa-map-marker-alt me-2"></i> <?php echo I18n::t('footer.address'); ?></li>
                    </ul>
            </div>
        </div>
        <hr class="border-light">
        <div class="text-center">
            <p class="text-light mb-0">&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. <?php echo I18n::t('footer.rights'); ?></p>
        </div>
    </div>
</footer>

</main>

<!-- Modal dinámico para el sistema -->
<div class="modal fade" id="dynamicModal" tabindex="-1" aria-labelledby="dynamicModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dynamicModalLabel">Cargando...</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Cargando contenido...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
    
</body>
</html>