<div class="vehicle-details">
    <div class="row">
        <div class="col-md-6">
            <img src="<?php echo htmlspecialchars($vehicleData['image']); ?>" 
                 class="img-fluid rounded" 
                 alt="<?php echo htmlspecialchars($vehicleData['brand'] . ' ' . $vehicleData['model']); ?>">
        </div>
        <div class="col-md-6">
            <h3><?php echo htmlspecialchars($vehicleData['brand'] . ' ' . $vehicleData['model']); ?></h3>
            <p class="lead"><?php echo htmlspecialchars($vehicleData['description']); ?></p>
            
            <div class="details-list">
                <p><strong><?php echo I18n::t('vehicles.category'); ?>:</strong> 
                   <?php echo I18n::t('vehicles.categories.' . $vehicleData['category']); ?></p>
                <p><strong><?php echo I18n::t('vehicles.daily_rate'); ?>:</strong> 
                   €<?php echo number_format($vehicleData['daily_rate'], 2); ?></p>
                <p><strong><?php echo I18n::t('vehicles.year'); ?>:</strong> 
                   <?php echo $vehicleData['year']; ?></p>
                <p><strong><?php echo I18n::t('vehicles.location'); ?>:</strong> 
                   <?php echo $vehicleData['location_name'] ?: I18n::t('common.not_specified'); ?></p>
            </div>

            <div class="mt-4">
                <a href="<?php echo APP_URL; ?>/vehicles/show/<?php echo $vehicleData['id']; ?>" 
                   class="btn btn-primary">
                    <?php echo I18n::t('vehicles.reserve_now'); ?>
                </a>
            </div>
        </div>
    </div>
</div> 