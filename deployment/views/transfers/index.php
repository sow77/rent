<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/i18n.php';
require_once __DIR__ . '/../../models/Transfer.php';

$pageTitle = I18n::t('transfers.title');
$activePage = 'transfers';

$transferModel = new Transfer();
$transfers = $transferModel->getAll();

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid px-4">
    <div class="container">
    <h1 class="text-center mb-5"><?php echo I18n::t('transfers.title'); ?></h1>

        <!-- Search Section -->
        <div class="row justify-content-center">
        <div class="col-md-8">
                <div class="input-group mb-4">
                    <input type="text" id="transferSearch" class="form-control" 
                           placeholder="<?php echo I18n::t('transfers.search_placeholder'); ?>">
                    <button class="btn btn-primary" type="button" id="transferSearchBtn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                            </div>
                            </div>

        <!-- Category Filters -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-8">
                <div id="transferCategoryGroup" class="btn-group d-flex flex-wrap justify-content-center" role="group">
                    <button type="button" class="btn btn-outline-primary category-filter active mb-2" data-category="all">
                        <?php echo I18n::t('transfers.categories.all'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Transfers Container -->
        <div id="transfersContainer">
            <?php
            // Agrupar transfers de 3 en 3
            $chunks = array_chunk($transfers, 3);
            ?>
            <?php foreach ($chunks as $chunk): ?>
                <?php $len = count($chunk); ?>
            <div class="row g-4 <?php echo $len < 3 ? 'row-few ' . ($len === 1 ? 'row-one' : 'row-two') : ''; ?>">
                    <?php
                    // Selección de clases de columnas según elementos en esta fila
                    if ($len === 1) {
                        $colClass = 'col-12';
                    } elseif ($len === 2) {
                        $colClass = 'col-12 col-md-6';
                    } else {
                        $colClass = 'col-12 col-md-6 col-lg-4';
                    }
                    ?>

                    <?php foreach ($chunk as $transfer): ?>
                        <div class="<?= $colClass ?>">
                            <div class="card h-100 shadow-sm">
                                <?php
                                // Obtener múltiples imágenes desde el objeto
                                $images = [];
                                if (!empty($transfer->images)) {
                                    $decoded = json_decode($transfer->images, true);
                                    if (is_array($decoded) && !empty($decoded)) {
                                        $images = $decoded;
                                    }
                                }
                                if (empty($images)) {
                                    $images = [$transfer->image ?? ''];
                                }
                                
                                $altText = htmlspecialchars($transfer->name);
                                ?>

                                <?php if ($len === 1): ?>
                                    <!-- Caso 1 elemento en la fila - carrusel panorámico -->
                                    <?php if (count($images) > 1): ?>
                                        <div id="carousel-one-tra-<?php echo $transfer->id; ?>" class="publication-carousel carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
                                            <div class="carousel-inner">
                                                <?php foreach ($images as $idx => $imgUrl): ?>
                                                    <div class="carousel-item <?php echo $idx === 0 ? 'active' : ''; ?>">
                                                        <img src="<?php echo htmlspecialchars($imgUrl); ?>" 
                                                             alt="<?php echo $altText; ?>" 
                                                             loading="lazy">
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <button class="carousel-control-prev" type="button" data-bs-target="#carousel-one-tra-<?php echo $transfer->id; ?>" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Previous</span>
                                            </button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#carousel-one-tra-<?php echo $transfer->id; ?>" data-bs-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Next</span>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <!-- Imagen única panorámica -->
                                        <div class="ratio ratio-4x3">
                                            <img src="<?php echo htmlspecialchars($transfer->image); ?>" 
                                                 alt="<?php echo $altText; ?>" 
                                                 class="card-img-top"
                                                 loading="lazy"
                                                 style="object-fit: cover; width:100%; height:100%;">
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <!-- Caso 2 o 3 elementos - carrusel estándar -->
                                    <div class="publication-carousel" 
                                         data-images="<?php echo htmlspecialchars(json_encode($images)); ?>"
                                         data-alt="<?php echo $altText; ?>">
                                        <div class="carousel-inner">
                                            <div class="carousel-item active">
                                                <img src="<?php echo htmlspecialchars($transfer->image); ?>" 
                                                     alt="<?php echo $altText; ?>" 
                                                     loading="lazy">
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($transfer->name); ?></h5>
                            <p class="card-text flex-grow-1"><?php echo htmlspecialchars($transfer->description); ?></p>
                            <div class="mt-auto">
                                <p class="card-text mb-3">
                                <strong><?php echo I18n::t('transfers.daily_rate'); ?>:</strong> 
                                    <span class="text-primary fw-bold">€<?php echo number_format($transfer->price, 2); ?></span>
                            </p>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary view-details" 
                                    data-type="transfers" 
                                            data-id="<?php echo $transfer->id; ?>">
                                        <i class="fas fa-eye me-1"></i><?php echo I18n::t('transfers.view_details'); ?>
                                    </button>
                                    <button class="btn btn-success" 
                                            onclick="showReservationForm('<?php echo $transfer->id; ?>', 'transfer', <?php echo htmlspecialchars(json_encode($transfer)); ?>)">
                                        <i class="fas fa-calendar-plus me-1"></i>Reservar
                            </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Incluir el archivo JavaScript -->
<script src="<?php echo APP_URL; ?>/public/js/transfers.js"></script>

<!-- Scripts para el sistema de reservas -->

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>