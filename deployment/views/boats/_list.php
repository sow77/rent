<?php if (empty($boats)): ?>
    <div class="alert alert-info">
        <?php echo I18n::t('boats.no_results'); ?>
    </div>
<?php else: ?>
    <?php
    // Agrupar barcos de 3 en 3
    $chunks = array_chunk($boats, 3);
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

            <?php foreach ($chunk as $boat): ?>
                <div class="<?= $colClass ?>">
                    <div class="card h-100 shadow-sm">
                        <?php
                        // Los barcos vienen como objetos
                        // Procesar imágenes desde el objeto
                        $boatImages = [];
                        if (!empty($boat->images)) {
                            $decoded = json_decode($boat->images, true);
                            if (is_array($decoded) && !empty($decoded)) {
                                $boatImages = $decoded;
                            }
                        }
                        if (empty($boatImages)) {
                            $boatImages = [$boat->image ?? ''];
                        }
                        
                        $images = $boatImages;
                        $altText = htmlspecialchars($boat->name);
                        ?>

                        <?php if ($len === 1): ?>
                            <!-- Caso 1 elemento en la fila - carrusel panorámico -->
                            <?php if (count($images) > 1): ?>
                                <div id="carousel-one-boat-<?php echo $boat->id; ?>" class="publication-carousel carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
                                    <div class="carousel-inner">
                                        <?php foreach ($images as $idx => $imgUrl): ?>
                                            <div class="carousel-item <?php echo $idx === 0 ? 'active' : ''; ?>">
                                                <img src="<?php echo htmlspecialchars($imgUrl); ?>" 
                                                     alt="<?php echo $altText; ?>" 
                                                     loading="lazy">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel-one-boat-<?php echo $boat->id; ?>" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carousel-one-boat-<?php echo $boat->id; ?>" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                </div>
                            <?php else: ?>
                                <!-- Imagen única panorámica -->
                                <div class="ratio ratio-4x3">
                                    <img src="<?php echo htmlspecialchars($boat->image); ?>" 
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
                                        <img src="<?php echo htmlspecialchars($boat->image); ?>" 
                                             alt="<?php echo $altText; ?>" 
                                             loading="lazy">
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($boat->name); ?></h5>
                        <p class="card-text flex-grow-1"><?php echo htmlspecialchars($boat->description); ?></p>
                        <div class="mt-auto">
                            <p class="card-text mb-3">
                                <strong><?php echo I18n::t('boats.daily_rate'); ?>:</strong> 
                                <span class="text-primary fw-bold">€<?php echo number_format($boat->daily_rate, 2); ?></span>
                            </p>
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary view-details" 
                                        data-type="boats" 
                                        data-id="<?php echo $boat->id; ?>">
                                    <i class="fas fa-eye me-1"></i><?php echo I18n::t('boats.view_details'); ?>
                                </button>
                                <button class="btn btn-success" 
                                        onclick="showReservationForm('<?php echo $boat->id; ?>', 'boat', <?php echo htmlspecialchars(json_encode($boat)); ?>)">
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
<?php endif; ?>