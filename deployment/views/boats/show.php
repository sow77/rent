<?php
// ARCHIVO MODIFICADO - DISEÑO UNIFORME - <?php echo date('Y-m-d H:i:s'); ?>
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/i18n.php';
require_once __DIR__ . '/../models/Boat.php';

$boatModel = new Boat();
$boat = $boatModel->getById($_GET['id']);

if (!$boat) {
    header('Location: ' . APP_URL . '/boats');
    exit;
}

$pageTitle = I18n::t('boats.details');
$activePage = 'boats';

require_once __DIR__ . '/../layouts/header.php';
?>

<main class="container py-5">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <img src="<?php echo htmlspecialchars($boat['image']); ?>" 
                     class="card-img-top" 
                     alt="<?php echo htmlspecialchars($boat['name']); ?>">
            </div>
        </div>
        
        <div class="col-md-6">
            <h1><?php echo htmlspecialchars($boat['name']); ?></h1>
            <p class="lead"><?php echo htmlspecialchars($boat['description']); ?></p>
            
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?php echo I18n::t('boats.daily_rate'); ?></h5>
                    <h2 class="text-primary">€<?php echo number_format($boat['daily_rate'], 2); ?></h2>
                </div>
            </div>
            
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?php echo I18n::t('boats.details'); ?></h5>
                    <p><strong><?php echo I18n::t('boats.type'); ?>:</strong> <?php echo htmlspecialchars($boat['type']); ?></p>
                    <p><strong><?php echo I18n::t('boats.capacity'); ?>:</strong> <?php echo htmlspecialchars($boat['capacity']); ?> <?php echo I18n::t('boats.passengers'); ?></p>
                    <p><strong><?php echo I18n::t('boats.location'); ?>:</strong> <?php echo htmlspecialchars($boat['location_name']); ?></p>
                </div>
            </div>
            
            <?php if (!empty($boat['features']) && $boat['features'] !== '[]'): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?php echo I18n::t('boats.features'); ?></h5>
                    <ul class="list-unstyled">
                        <?php foreach (json_decode($boat['features']) as $feature): ?>
                            <li><i class="fas fa-check text-success me-2"></i><?php echo htmlspecialchars($feature); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <div class="mt-4">
                <a href="<?php echo APP_URL; ?>/boats" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left me-2"></i><?php echo I18n::t('common.back'); ?>
                </a>
                <button class="btn btn-primary" onclick="showReservationForm('<?php echo $boat['id']; ?>', 'boat', <?php echo htmlspecialchars(json_encode($boat)); ?>)">
                    <i class="fas fa-calendar-plus me-2"></i>Reservar Ahora
                </button>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 