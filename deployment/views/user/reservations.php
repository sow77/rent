<?php
// views/user/reservations.php
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-calendar-check me-2"></i>Mis Reservas</h2>
                <a href="<?php echo APP_URL; ?>/" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nueva Reserva
                </a>
            </div>
        </div>
    </div>

    <?php if (empty($reservations)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted">No tienes reservas aún</h4>
                        <p class="text-muted mb-4">Explora nuestros servicios y haz tu primera reserva</p>
                        <a href="<?php echo APP_URL; ?>/" class="btn btn-primary btn-lg">
                            <i class="fas fa-search me-2"></i>Explorar Servicios
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($reservations as $reservation): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <?php if ($reservation['entity_image']): ?>
                            <img src="<?php echo APP_URL . '/uploads/' . $reservation['entity_image']; ?>" 
                                 class="card-img-top" style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title"><?php echo htmlspecialchars($reservation['entity_name']); ?></h5>
                                <?php
                                $statusClass = '';
                                $statusIcon = '';
                                switch($reservation['status']) {
                                    case 'confirmada':
                                        $statusClass = 'success';
                                        $statusIcon = 'check-circle';
                                        break;
                                    case 'pendiente':
                                        $statusClass = 'warning';
                                        $statusIcon = 'clock';
                                        break;
                                    case 'cancelada':
                                        $statusClass = 'danger';
                                        $statusIcon = 'times-circle';
                                        break;
                                }
                                ?>
                                <span class="badge bg-<?php echo $statusClass; ?>">
                                    <i class="fas fa-<?php echo $statusIcon; ?> me-1"></i>
                                    <?php echo ucfirst($reservation['status']); ?>
                                </span>
                            </div>
                            
                            <p class="text-muted mb-2">
                                <i class="fas fa-tag me-1"></i>
                                <?php echo ucfirst($reservation['entity_type']); ?>
                            </p>
                            
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    <strong>Inicio:</strong> <?php echo date('d/m/Y', strtotime($reservation['pickup_date'])); ?>
                                </small>
                            </div>
                            
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-calendar-check me-1"></i>
                                    <strong>Fin:</strong> <?php echo date('d/m/Y', strtotime($reservation['return_date'])); ?>
                                </small>
                            </div>
                            
                            <?php if ($reservation['phone']): ?>
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-phone me-1"></i>
                                        <strong>Teléfono:</strong> <?php echo htmlspecialchars($reservation['phone']); ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <h6 class="text-primary mb-0">
                                    <i class="fas fa-euro-sign me-1"></i>
                                    €<?php echo number_format($reservation['total_cost'], 2); ?>
                                </h6>
                            </div>
                            
                            <?php if ($reservation['notes']): ?>
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-sticky-note me-1"></i>
                                        <strong>Notas:</strong><br>
                                        <?php echo htmlspecialchars($reservation['notes']); ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Creada: <?php echo date('d/m/Y H:i', strtotime($reservation['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-transparent">
                            <div class="btn-group w-100" role="group">
                                <button class="btn btn-outline-primary btn-sm" 
                                        onclick="viewReservation('<?php echo $reservation['id']; ?>')">
                                    <i class="fas fa-eye me-1"></i>Ver
                                </button>
                                <?php if ($reservation['status'] === 'pendiente'): ?>
                                    <button class="btn btn-outline-danger btn-sm" 
                                            onclick="cancelReservation('<?php echo $reservation['id']; ?>')">
                                        <i class="fas fa-times me-1"></i>Cancelar
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function viewReservation(reservationId) {
    // Implementar vista de detalles de reserva
    alert('Ver detalles de reserva: ' + reservationId);
}

function cancelReservation(reservationId) {
    if (confirm('¿Estás seguro de que quieres cancelar esta reserva?')) {
        // Implementar cancelación de reserva
        alert('Cancelar reserva: ' + reservationId);
    }
}
</script>
