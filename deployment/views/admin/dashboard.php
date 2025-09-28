<?php
// views/admin/dashboard.php - Vista del panel de control con layout unificado
if (!isset($stats)) {
    $stats = [
        'total_users' => 0,
        'total_vehicles' => 0,
        'total_reservations' => 0,
        'total_income' => 0
    ];
}

$pageTitle = 'Panel de Control';
$currentPage = 'dashboard';

// Capturar el contenido en un buffer
ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Panel de Control</h1>
    <div class="text-muted">
        <i class="fas fa-user me-1"></i>Bienvenido, <?php echo htmlspecialchars($user['name'] ?? 'Administrador'); ?>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="content-card">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="fas fa-users fa-2x text-primary"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold"><?php echo number_format($stats['total_users']); ?></h3>
                        <p class="text-muted mb-0">Usuarios</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="content-card">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="fas fa-car fa-2x text-success"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold"><?php echo number_format($stats['total_vehicles']); ?></h3>
                        <p class="text-muted mb-0">Vehículos</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="content-card">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="bg-info bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="fas fa-calendar-check fa-2x text-info"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold"><?php echo number_format($stats['total_reservations']); ?></h3>
                        <p class="text-muted mb-0">Reservas</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="content-card">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="fas fa-euro-sign fa-2x text-warning"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold">€<?php echo number_format($stats['total_income'], 2); ?></h3>
                        <p class="text-muted mb-0">Ingresos</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions and System Info -->
<div class="row g-4">
    <!-- Quick Actions -->
    <div class="col-lg-6">
        <div class="content-card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Acciones Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-3">
                    <a href="<?php echo APP_URL; ?>/admin/users" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-users me-2"></i>Gestionar Usuarios
                    </a>
                    <a href="<?php echo APP_URL; ?>/admin/vehicles" class="btn btn-outline-success btn-lg">
                        <i class="fas fa-car me-2"></i>Gestionar Vehículos
                    </a>
                    <a href="<?php echo APP_URL; ?>/admin/boats" class="btn btn-outline-info btn-lg">
                        <i class="fas fa-ship me-2"></i>Gestionar Barcos
                    </a>
                    <a href="<?php echo APP_URL; ?>/admin/transfers" class="btn btn-outline-warning btn-lg">
                        <i class="fas fa-shuttle-van me-2"></i>Gestionar Traslados
                    </a>
                    <a href="<?php echo APP_URL; ?>/admin/reservations" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-calendar-check me-2"></i>Ver Reservas
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- System Information -->
    <div class="col-lg-6">
        <div class="content-card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del Sistema</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-server text-primary me-3"></i>
                            <div>
                                <strong>Servidor:</strong><br>
                                <small class="text-muted"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Apache/2.4.58 (Win64) OpenSSL/3.1.3'; ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-code text-success me-3"></i>
                            <div>
                                <strong>PHP:</strong><br>
                                <small class="text-muted"><?php echo PHP_VERSION; ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-database text-info me-3"></i>
                            <div>
                                <strong>Base de Datos:</strong><br>
                                <small class="text-muted">MySQL</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clock text-warning me-3"></i>
                            <div>
                                <strong>Última Actualización:</strong><br>
                                <small class="text-muted"><?php echo date('d/m/Y H:i:s'); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="content-card mt-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Actividad Reciente</h5>
    </div>
    <div class="card-body">
        <div class="text-center text-muted py-4">
            <i class="fas fa-chart-line fa-3x mb-3"></i><br>
            <h5>No hay actividad reciente</h5>
            <p class="text-muted">Las actividades del sistema aparecerán aquí</p>
        </div>
    </div>
</div>

<?php
// Obtener el contenido del buffer
$content = ob_get_clean();

// Incluir el layout de admin unificado
include 'views/admin/layout.php';
?> 