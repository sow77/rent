    <!-- Featured Boats -->
    <section class="featured-section">
        <div class="container">
            <h2 class="section-title">Barcos Destacados</h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 justify-content-center">
                <?php foreach ($featuredBoats as $boat): ?>
                <div class="col">
                    <div class="card h-100">
                        <img src="<?php echo htmlspecialchars($boat['image']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($boat['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($boat['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($boat['description'], 0, 100)) . '...'; ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="price">€<?php echo number_format($boat['daily_rate'], 2); ?>/día</span>
                                <button class="btn btn-primary view-details" 
                                        data-type="boats" 
                                        data-id="<?php echo $boat['id']; ?>">
                                    Ver Detalles
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section> 