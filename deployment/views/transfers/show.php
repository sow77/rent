<?php
// ARCHIVO MODIFICADO - DISEÑO UNIFORME - <?php echo date('Y-m-d H:i:s'); ?>
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/i18n.php';
require_once __DIR__ . '/../models/Transfer.php';

$transferModel = new Transfer();
$transfer = $transferModel->getById($_GET['id']);

if (!$transfer) {
    header('Location: ' . APP_URL . '/transfers');
    exit;
}

$pageTitle = I18n::t('transfers.details');
$activePage = 'transfers';

require_once __DIR__ . '/../layouts/header.php';
?>

<main class="container py-5">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <img src="<?php echo htmlspecialchars($transfer['image']); ?>" 
                     class="card-img-top" 
                     alt="<?php echo htmlspecialchars($transfer['name']); ?>">
            </div>
        </div>
        
        <div class="col-md-6">
            <h1><?php echo htmlspecialchars($transfer['name']); ?></h1>
            <p class="lead"><?php echo htmlspecialchars($transfer['description']); ?></p>
            
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?php echo I18n::t('transfers.price'); ?></h5>
                    <h2 class="text-primary">€<?php echo number_format($transfer['price'], 2); ?></h2>
                </div>
            </div>
            
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?php echo I18n::t('transfers.details'); ?></h5>
                    <p><strong><?php echo I18n::t('transfers.type'); ?>:</strong> <?php echo htmlspecialchars($transfer['type']); ?></p>
                    <p><strong><?php echo I18n::t('transfers.capacity'); ?>:</strong> <?php echo htmlspecialchars($transfer['capacity']); ?> <?php echo I18n::t('transfers.passengers'); ?></p>
                    <p><strong><?php echo I18n::t('transfers.location'); ?>:</strong> <?php echo htmlspecialchars($transfer['location_name']); ?></p>
                </div>
            </div>
            
            <?php if (!empty($transfer['features']) && $transfer['features'] !== '[]'): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?php echo I18n::t('transfers.features'); ?></h5>
                    <ul class="list-unstyled">
                        <?php foreach (json_decode($transfer['features']) as $feature): ?>
                            <li><i class="fas fa-check text-success me-2"></i><?php echo htmlspecialchars($feature); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <div class="mt-4">
                <a href="<?php echo APP_URL; ?>/transfers" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left me-2"></i><?php echo I18n::t('common.back'); ?>
                </a>
                <button class="btn btn-primary" onclick="showReservationForm('<?php echo $transfer['id']; ?>', 'transfer', <?php echo htmlspecialchars(json_encode($transfer)); ?>)">
                    <i class="fas fa-calendar-plus me-2"></i>Reservar Ahora
                </button>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 