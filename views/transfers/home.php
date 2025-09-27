    <!-- Featured Transfers -->
    <section class="featured-section">
        <div class="container">
            <h2 class="section-title">Transferencias Destacadas</h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 justify-content-center">
                <?php foreach ($featuredTransfers as $transfer): ?>
                <div class="col">
                    <div class="card h-100">
                        <img src="<?php echo htmlspecialchars($transfer['image']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($transfer['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($transfer['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($transfer['description'], 0, 100)) . '...'; ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="price">€<?php echo number_format($transfer['price'], 2); ?></span>
                                <button class="btn btn-primary view-details" 
                                        data-type="transfers" 
                                        data-id="<?php echo $transfer['id']; ?>">
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