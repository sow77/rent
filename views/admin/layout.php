<?php
// views/admin/layout.php - Layout específico para el panel de administración
require_once __DIR__ . '/../../config/config.php';

// Solo incluir I18n si no está ya incluido
if (!class_exists('I18n')) {
    require_once __DIR__ . '/../../utils/I18n.php';
}

// Solo incluir Auth si no está ya incluido
if (!class_exists('Auth')) {
    require_once __DIR__ . '/../../utils/Auth.php';
}

// Obtener información del usuario actual (sin verificar autenticación aquí)
$currentUser = null;
if (class_exists('Auth') && Auth::isAuthenticated()) {
    $currentUser = Auth::getCurrentUser();
}

$currentLang = I18n::getCurrentLang();
$baseUrl = APP_URL;

// Determinar la página activa
// Si no está definida en la vista, intentar obtenerla de la URL
if (!isset($currentPage)) {
    $currentPage = 'dashboard';
    $uri = $_SERVER['REQUEST_URI'];
    if (strpos($uri, '/admin/users') !== false) {
        $currentPage = 'users';
    } elseif (strpos($uri, '/admin/vehicles') !== false) {
        $currentPage = 'vehicles';
    } elseif (strpos($uri, '/admin/boats') !== false) {
        $currentPage = 'boats';
    } elseif (strpos($uri, '/admin/transfers') !== false) {
        $currentPage = 'transfers';
    } elseif (strpos($uri, '/admin/reservations') !== false) {
        $currentPage = 'reservations';
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Panel de Administración'; ?> - <?php echo APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Flag Icons -->
    <link href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@6.6.6/css/flag-icons.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #1a237e;
            --secondary-color: #283593;
            --accent-color: #3498db;
            --light-bg: #f8f9fa;
            --dark-text: #2c3e50;
            --light-text: #ffffff;
            --border-color: #e9ecef;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: var(--dark-text);
            background-color: var(--light-bg);
            min-height: 100vh;
        }

        /* Header */
        .admin-header {
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
            color: white;
            box-shadow: var(--shadow);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            height: 70px;
        }

        .admin-header .navbar {
            height: 100%;
            padding: 0 1rem;
        }

        .admin-header .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.5rem;
            text-decoration: none;
        }

        .admin-header .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .admin-header .nav-link:hover {
            color: white !important;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 6px;
        }

        /* Dropdown menus */
        .dropdown-menu {
            border: none;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-radius: 12px;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-top: 0.5rem;
        }

        .dropdown-item {
            padding: 0.75rem 1rem;
            border-radius: 6px;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .dropdown-item:hover {
            background-color: rgba(26, 35, 126, 0.1);
            transform: translateX(3px);
        }

        /* Main Layout */
        .admin-container {
            display: flex;
            min-height: 100vh;
            padding-top: 90px;
        }

        /* Sidebar */
        .admin-sidebar {
            width: 280px;
            background: white;
            box-shadow: var(--shadow);
            position: fixed;
            left: 0;
            top: 90px;
            height: calc(100vh - 90px);
            overflow-y: auto;
            z-index: 999;
        }

        .admin-sidebar .sidebar-header {
            background: #343a40;
            color: white;
            padding: 1.5rem;
            font-weight: 600;
            font-size: 1.1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .admin-sidebar .sidebar-menu {
            padding: 1rem 0;
        }

        .admin-sidebar .sidebar-item {
            display: block;
            padding: 0.875rem 1.5rem;
            color: #495057;
            text-decoration: none;
            border-bottom: 1px solid var(--border-color);
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .admin-sidebar .sidebar-item:last-child {
            border-bottom: none;
        }

        .admin-sidebar .sidebar-item:hover {
            background: #f8f9fa;
            color: var(--primary-color);
            text-decoration: none;
        }

        .admin-sidebar .sidebar-item.active {
            background: var(--primary-color);
            color: white;
            border-left: 4px solid #fff;
        }

        .admin-sidebar .sidebar-item i {
            width: 20px;
            margin-right: 0.75rem;
        }

        /* Main Content */
        .admin-main {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            background: var(--light-bg);
            min-height: calc(100vh - 70px);
        }

        /* Content Cards */
        .content-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: none;
            margin-bottom: 1.5rem;
        }

        .content-card .card-header {
            background: white;
            color: var(--dark-text);
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            padding: 1.25rem 1.5rem;
            border-radius: 12px 12px 0 0;
        }

        .content-card .card-body {
            padding: 1.5rem;
        }

        /* Tables */
        .table {
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 0;
        }

        .table thead {
            background: #f8f9fa;
            color: var(--dark-text);
        }

        .table thead th {
            border: none;
            font-weight: 600;
            padding: 1rem;
            border-bottom: 2px solid var(--border-color);
            color: var(--dark-text);
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }

        /* Buttons */
        .btn {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border: none;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            color: white;
        }

        /* Badges */
        .badge {
            font-size: 0.75rem;
            padding: 0.5em 0.75em;
            border-radius: 6px;
            font-weight: 500;
        }

        /* Forms */
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            padding: 0.75rem 1rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(26, 35, 126, 0.25);
        }

        /* Modals */
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background: #f8f9fa;
            color: var(--dark-text);
            border-radius: 12px 12px 0 0;
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark-text);
            margin: 0;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .admin-sidebar.show {
                transform: translateX(0);
            }

            .admin-main {
                margin-left: 0;
                padding: 1rem;
            }

            .admin-header .navbar-toggler {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .admin-main {
                padding: 1rem 0.5rem;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .table-responsive {
                border-radius: 8px;
                overflow: hidden;
            }

            .content-card .card-body {
                padding: 1rem;
            }
        }

        /* Utilities */
        .text-muted {
            color: #6c757d !important;
        }

        .img-thumbnail {
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        /* Loading states */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.3s ease;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <a class="navbar-brand" href="<?php echo $baseUrl; ?>">
                    <i class="fas fa-car me-2"></i><?php echo APP_NAME; ?>
                </a>
                
                <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $baseUrl; ?>">
                                <i class="fas fa-home me-1"></i>Inicio
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $baseUrl; ?>/vehicles">
                                <i class="fas fa-car me-1"></i>Vehículos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $baseUrl; ?>/boats">
                                <i class="fas fa-ship me-1"></i>Barcos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $baseUrl; ?>/transfers">
                                <i class="fas fa-shuttle-van me-1"></i>Traslados
                            </a>
                        </li>
                    </ul>
                    
                    <ul class="navbar-nav align-items-center">
                        <!-- Language Dropdown -->
                        <li class="nav-item dropdown me-3">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fi fi-<?php echo $currentLang === 'en' ? 'gb' : $currentLang; ?>" style="font-size: 1.2em;"></i>
                                <span class="d-none d-sm-inline"><?php echo I18n::t('languages.' . $currentLang); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item d-flex align-items-center gap-2<?php echo $currentLang === 'es' ? ' active fw-bold' : ''; ?>" href="?lang=es">
                                    <i class="fi fi-es"></i><span>Español</span>
                                </a></li>
                                <li><a class="dropdown-item d-flex align-items-center gap-2<?php echo $currentLang === 'en' ? ' active fw-bold' : ''; ?>" href="?lang=en">
                                    <i class="fi fi-gb"></i><span>English</span>
                                </a></li>
                            </ul>
                        </li>
                        
                        <!-- User Dropdown -->
                        <?php if ($currentUser): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle fa-lg me-2"></i>
                                <span class="d-none d-sm-inline"><?php echo htmlspecialchars($currentUser['name']); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>/user/dashboard">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>/user/profile">
                                    <i class="fas fa-user-cog me-2"></i>Perfil
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>/logout">
                                    <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                </a></li>
                            </ul>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Container -->
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <i class="fas fa-cogs me-2"></i>Panel de Administración
            </div>
            <nav class="sidebar-menu">
                <a href="<?php echo $baseUrl; ?>/admin" class="sidebar-item <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>Panel de Control
                </a>
                <a href="<?php echo $baseUrl; ?>/admin/users" class="sidebar-item <?php echo $currentPage === 'users' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>Usuarios
                </a>
                <a href="<?php echo $baseUrl; ?>/admin/vehicles" class="sidebar-item <?php echo $currentPage === 'vehicles' ? 'active' : ''; ?>">
                    <i class="fas fa-car"></i>Vehículos
                </a>
                <a href="<?php echo $baseUrl; ?>/admin/boats" class="sidebar-item <?php echo $currentPage === 'boats' ? 'active' : ''; ?>">
                    <i class="fas fa-ship"></i>Barcos
                </a>
                <a href="<?php echo $baseUrl; ?>/admin/transfers" class="sidebar-item <?php echo $currentPage === 'transfers' ? 'active' : ''; ?>">
                    <i class="fas fa-shuttle-van"></i>Traslados
                </a>
                <a href="<?php echo $baseUrl; ?>/admin/reservations" class="sidebar-item <?php echo $currentPage === 'reservations' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check"></i>Reservas
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main fade-in">
            <?php echo $content ?? ''; ?>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle sidebar on mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.querySelector('.navbar-toggler');
            const sidebar = document.querySelector('.admin-sidebar');
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
            }
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 991.98) {
                    if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                        sidebar.classList.remove('show');
                    }
                }
            });
        });
    </script>
    
    <?php echo $scripts ?? ''; ?>
</body>
</html> 