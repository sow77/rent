<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/i18n.php';
require_once __DIR__ . '/../../models/Boat.php';

$pageTitle = I18n::t('boats.title');
$activePage = 'boats';

$boatModel = new Boat();
$boats = $boatModel->getAll();

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid px-4">
    <div class="container">
        <h1 class="text-center mb-5"><?php echo I18n::t('boats.title'); ?></h1>

        <!-- Search Section -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="input-group mb-4">
                    <input type="text" id="boatSearch" class="form-control" 
                           placeholder="<?php echo I18n::t('boats.search_placeholder'); ?>">
                    <button class="btn btn-primary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Category Filters -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-8">
                <div id="boatCategoryGroup" class="btn-group d-flex flex-wrap justify-content-center" role="group">
                    <button type="button" class="btn btn-outline-primary category-filter active mb-2" data-category="all">
                        <?php echo I18n::t('boats.categories.all'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Boats Container -->
        <div id="boatsContainer">
            <?php include '_list.php'; ?>
        </div>
    </div>
</div>

<!-- Boat Details Modal -->
<div class="modal fade" id="boatsDetailsModal" tabindex="-1" aria-labelledby="boatsDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="boatsDetailsModalLabel"><?php echo I18n::t('boats.details.title'); ?></h5>
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

<!-- Incluir el archivo JavaScript -->
<script src="<?php echo APP_URL; ?>/public/js/boats.js"></script>

<!-- Scripts para el sistema de reservas -->

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>