<?php
// ARCHIVO MODIFICADO - DISEÑO UNIFORME - <?php echo date('Y-m-d H:i:s'); ?>
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/i18n.php';
require_once __DIR__ . '/../../models/Vehicle.php';

$pageTitle = I18n::t('vehicles.details');
$activePage = 'vehicles';

$vehicleModel = new Vehicle();
$vehicle = $vehicleModel->getById($_GET['id']);

if (!$vehicle) {
    header('Location: ' . APP_URL . '/vehicles');
    exit;
}

require_once __DIR__ . '/../layouts/header.php';
?>

<main class="container py-5">
    <div class="row">
        <div class="col-md-6">
            <img src="<?php echo htmlspecialchars($vehicle['image']); ?>" 
                 class="img-fluid rounded" 
                 alt="<?php echo htmlspecialchars($vehicle['name']); ?>">
        </div>
        <div class="col-md-6">
            <h3><?php echo htmlspecialchars($vehicle['name']); ?></h3>
            <p class="lead"><?php echo htmlspecialchars($vehicle['description']); ?></p>
            
            <div class="details-list">
                <p><strong><?php echo I18n::t('vehicles.year'); ?>:</strong> <?php echo htmlspecialchars($vehicle['year']); ?></p>
                <p><strong><?php echo I18n::t('vehicles.category'); ?>:</strong> <?php echo htmlspecialchars($vehicle['category']); ?></p>
                <p><strong><?php echo I18n::t('vehicles.location'); ?>:</strong> <?php echo htmlspecialchars($vehicle['location_name']); ?></p>
                <p><strong><?php echo I18n::t('vehicles.daily_rate'); ?>:</strong> €<?php echo number_format($vehicle['daily_rate'], 2); ?></p>
            </div>
            
            <?php if (!empty($vehicle['features']) && $vehicle['features'] !== '[]'): ?>
            <div class="mb-3">
                <strong><?php echo I18n::t('vehicles.features'); ?>:</strong>
                <ul class="list-unstyled mt-2">
                    <?php foreach (json_decode($vehicle['features']) as $feature): ?>
                        <li><i class="fas fa-check text-success me-2"></i><?php echo htmlspecialchars($feature); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="mt-4">
                <a href="<?php echo APP_URL; ?>/vehicles" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left me-2"></i><?php echo I18n::t('common.back'); ?>
                </a>
                <button class="btn btn-primary" onclick="showReservationForm('<?php echo $vehicle['id']; ?>', 'vehicle', <?php echo htmlspecialchars(json_encode($vehicle)); ?>)">
                    <i class="fas fa-calendar-plus me-2"></i>Reservar Ahora
                </button>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>