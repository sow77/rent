<?php
// views/user/dashboard.php
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-tachometer-alt me-2"></i>Mi Dashboard</h2>
                <div class="text-muted">
                    <i class="fas fa-user me-1"></i>Bienvenido, <?php echo htmlspecialchars($user['name']); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo count($reservations); ?></h4>
                            <p class="mb-0">Total Reservas</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo count(array_filter($reservations, function($r) { return $r['status'] === 'confirmada'; })); ?></h4>
                            <p class="mb-0">Confirmadas</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo count(array_filter($reservations, function($r) { return $r['status'] === 'pendiente'; })); ?></h4>
                            <p class="mb-0">Pendientes</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>€<?php echo number_format(array_sum(array_column($reservations, 'total_cost')), 2); ?></h4>
                            <p class="mb-0">Total Gastado</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-euro-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones rápidas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Acciones Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="<?php echo APP_URL; ?>/vehicles" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-car me-2"></i>Ver Vehículos
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="<?php echo APP_URL; ?>/boats" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-ship me-2"></i>Ver Barcos
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="<?php echo APP_URL; ?>/transfers" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-bus me-2"></i>Ver Traslados
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reservas recientes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Reservas Recientes</h5>
                    <a href="<?php echo APP_URL; ?>/user/reservations" class="btn btn-sm btn-outline-primary">
                        Ver Todas
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($reservations)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No tienes reservas aún</h5>
                            <p class="text-muted">Explora nuestros servicios y haz tu primera reserva</p>
                            <a href="<?php echo APP_URL; ?>/" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Explorar Servicios
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Servicio</th>
                                        <th>Fechas</th>
                                        <th>Estado</th>
                                        <th>Total</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($reservations, 0, 5) as $reservation): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($reservation['entity_image']): ?>
                                                        <img src="<?php echo APP_URL . '/uploads/' . $reservation['entity_image']; ?>" 
                                                             class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($reservation['entity_name']); ?></strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?php echo ucfirst($reservation['entity_type']); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>Inicio:</strong> <?php echo date('d/m/Y', strtotime($reservation['pickup_date'])); ?>
                                                    <br>
                                                    <strong>Fin:</strong> <?php echo date('d/m/Y', strtotime($reservation['return_date'])); ?>
                                                </div>
                                            </td>
                                            <td>
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
                                            </td>
                                            <td>
                                                <strong>€<?php echo number_format($reservation['total_cost'], 2); ?></strong>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="viewReservation('<?php echo $reservation['id']; ?>')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewReservation(reservationId) {
    // Implementar vista de detalles de reserva
    alert('Ver detalles de reserva: ' + reservationId);
}
</script>
