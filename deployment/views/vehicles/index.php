<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/i18n.php';
require_once __DIR__ . '/../../models/Vehicle.php';

$pageTitle = I18n::t('vehicles.title');
$activePage = 'vehicles';

$vehicleModel = new Vehicle();
$vehicles = $vehicleModel->getAll();

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid px-4">
    <div class="container">
        <h1 class="text-center mb-5"><?php echo I18n::t('vehicles.title'); ?></h1>

        <!-- Search Section -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="input-group mb-4">
                    <input type="text" id="vehicleSearch" class="form-control" 
                           placeholder="<?php echo I18n::t('vehicles.search_placeholder'); ?>">
                    <button class="btn btn-primary" type="button" id="vehicleSearchBtn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Category Filters -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-8">
                <div id="vehicleCategoryGroup" class="btn-group d-flex flex-wrap justify-content-center" role="group">
                    <button type="button" class="btn btn-outline-primary category-filter active mb-2" data-category="all">
                        <?php echo I18n::t('vehicles.categories.all'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Vehicles Container -->
        <div id="vehiclesContainer">
            <?php include '_list.php'; ?>
        </div>
    </div>
</div>

<!-- Vehicle Details Modal -->
<div class="modal fade" id="vehiclesDetailsModalPage" tabindex="-1" aria-labelledby="vehiclesDetailsModalPageLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vehiclesDetailsModalPageLabel"><?php echo I18n::t('vehicles.details.title'); ?></h5>
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

<!-- Estilos para el modal de detalles -->
<style>
.features-grid .badge {
    font-size: 0.85rem;
    padding: 0.5rem 0.75rem;
    border-radius: 20px;
    margin: 0.25rem;
}

.modal-body img {
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.modal-body .badge {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
}

.modal-body strong {
    color: #495057;
}

.modal-body .text-success {
    color: #28a745 !important;
}
</style>

<!-- Incluir el archivo JavaScript -->
<script src="<?php echo APP_URL; ?>/public/js/vehicles.js"></script>

<!-- Scripts para el sistema de reservas ya incluidos en header.php -->

<script>
// Asegurar que el grid responsivo se configure correctamente
document.addEventListener('DOMContentLoaded', function() {
    if (window.setupResponsiveGrid) {
        setupResponsiveGrid();
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>