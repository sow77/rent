<?php
// views/admin/index.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../utils/I18n.php';

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ' . APP_URL . '/login');
    exit;
}

$currentLang = I18n::getCurrentLang();
$baseUrl = APP_URL;
$activePage = 'admin';
?>

<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - <?php echo APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Flag Icons -->
    <link href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@6.6.6/css/flag-icons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo APP_URL; ?>/public/css/style.css" rel="stylesheet">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="fas fa-cogs"></i> Panel de Administración
                </h1>
            </div>
        </div>

        <div class="row">
            <!-- Gestión de Usuarios -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-users"></i> Gestión de Usuarios
                        </h5>
                        <p class="card-text">Administra usuarios, roles y permisos.</p>
                        <a href="<?php echo $baseUrl; ?>/admin/users" class="btn btn-primary">
                            <i class="fas fa-users-cog"></i> Gestionar Usuarios
                        </a>
                    </div>
                </div>
            </div>

            <!-- Gestión de Vehículos -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-car"></i> Gestión de Vehículos
                        </h5>
                        <p class="card-text">Administra la flota de vehículos.</p>
                        <a href="<?php echo $baseUrl; ?>/admin/vehicles" class="btn btn-primary">
                            <i class="fas fa-car-side"></i> Gestionar Vehículos
                        </a>
                    </div>
                </div>
            </div>

            <!-- Gestión de Barcos -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-ship"></i> Gestión de Barcos
                        </h5>
                        <p class="card-text">Administra la flota de barcos.</p>
                        <a href="<?php echo $baseUrl; ?>/admin/boats" class="btn btn-primary">
                            <i class="fas fa-ship"></i> Gestionar Barcos
                        </a>
                    </div>
                </div>
            </div>

            <!-- Gestión de Transferencias -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-exchange-alt"></i> Gestión de Transferencias
                        </h5>
                        <p class="card-text">Administra los servicios de transferencia.</p>
                        <a href="<?php echo $baseUrl; ?>/admin/transfers" class="btn btn-primary">
                            <i class="fas fa-exchange-alt"></i> Gestionar Transferencias
                        </a>
                    </div>
                </div>
            </div>

            <!-- Gestión de Reservas -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-calendar-alt"></i> Gestión de Reservas
                        </h5>
                        <p class="card-text">Administra las reservas y calendario.</p>
                        <a href="<?php echo $baseUrl; ?>/admin/bookings" class="btn btn-primary">
                            <i class="fas fa-calendar-check"></i> Gestionar Reservas
                        </a>
                    </div>
                </div>
            </div>

            <!-- Configuración -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-cog"></i> Configuración
                        </h5>
                        <p class="card-text">Configura los ajustes del sistema.</p>
                        <a href="<?php echo $baseUrl; ?>/admin/settings" class="btn btn-primary">
                            <i class="fas fa-sliders-h"></i> Configuración
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html> 