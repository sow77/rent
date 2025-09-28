<?php
// views/layouts/header.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/i18n.php';

// Asegurarnos de que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// La verificación de sesión se maneja ahora en el middleware correspondiente
// No verificamos sesión aquí para mantener las páginas públicas accesibles

// Asegurar que I18n esté inicializado
I18n::init();
    $currentLang = I18n::getCurrentLang();
    $baseUrl = APP_URL;
?>
<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Flag Icons -->
    <link href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@6.6.6/css/flag-icons.min.css" rel="stylesheet">
    
    <!-- Estilos personalizados para navbar -->
    <style>
        .navbar .dropdown-menu {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem;
        }
        
        .navbar .dropdown-item {
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
        }
        
        .navbar .dropdown-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }
        
        .navbar .dropdown-item.active {
            background-color: #0d6efd;
            color: white;
        }
        
        .navbar .dropdown-divider {
            margin: 0.5rem 0;
        }
        
        .navbar-scrolled {
            background-color: rgba(13, 110, 253, 0.95) !important;
            backdrop-filter: blur(10px);
        }
        
        .navbar-notification {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1050;
            padding: 0.75rem 0;
            animation: slideDown 0.3s ease;
        }
        
        .navbar-notification-info {
            background-color: #0dcaf0;
            color: white;
        }
        
        .navbar-notification-success {
            background-color: #198754;
            color: white;
        }
        
        .navbar-notification-warning {
            background-color: #ffc107;
            color: black;
        }
        
        .navbar-notification-error {
            background-color: #dc3545;
            color: white;
        }
        
        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        /* Estilos para mejorar el layout */
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        main {
            flex: 1;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            margin: 20px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        footer {
            margin-top: auto;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            border-top: 3px solid #3498db;
        }
        
        .container {
            max-width: 1200px;
        }
        
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            border-bottom: none;
            border-radius: 12px 12px 0 0 !important;
            padding: 1.25rem;
        }
        
        .card-header h5 {
            margin: 0;
            font-weight: 600;
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.4);
        }
        
        .btn-outline-primary {
            border: 2px solid #3498db;
            color: #3498db;
        }
        
        .btn-outline-primary:hover {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border-color: #3498db;
            transform: translateY(-1px);
        }
        
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .navbar {
            background: linear-gradient(135deg, #1a252f 0%, #2c3e50 100%) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            transform: translateY(-1px);
        }
        
        h1 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        
        .badge {
            border-radius: 6px;
            font-weight: 500;
        }
        
        .text-muted {
            color: #6c757d !important;
        }
        
        /* Animaciones */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .card {
            animation: fadeInUp 0.6s ease;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            main {
                margin: 10px;
                border-radius: 10px;
            }
            
            .card-header {
                padding: 1rem;
            }
        }
    </style>
    <!-- Custom CSS -->
    <link href="<?php echo $baseUrl; ?>/public/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="<?php echo $baseUrl; ?>/public/css/modals.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="<?php echo $baseUrl; ?>/public/css/carousel.css?v=<?php echo time(); ?>" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    
    <!-- Global JavaScript Variables -->
    <script>
        const APP_URL = '<?php echo APP_URL; ?>';
        // Desactivar logs en producción por defecto. Actívalo temporalmente con window.DEBUG=true en consola.
        window.DEBUG = false;
        (function(){
            const silent = ['log','debug','info'];
            if (!window.DEBUG) {
                silent.forEach(fn => { try { console[fn] = function(){}; } catch(e){} });
            }
        })();
        <?php if (Auth::isAuthenticated()): ?>
        const currentUserId = '<?php echo Auth::getCurrentUserId(); ?>';
        const currentUserName = '<?php echo htmlspecialchars(Auth::getCurrentUser()['name']); ?>';
        const currentUserEmail = '<?php echo htmlspecialchars(Auth::getCurrentUser()['email']); ?>';
        const currentUserRole = '<?php echo Auth::getCurrentUser()['role']; ?>';
        <?php endif; ?>
    </script>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo $baseUrl; ?>/public/js/publication-carousel.js"></script>
    <script src="<?php echo $baseUrl; ?>/public/js/details.js"></script>
    <script src="<?php echo $baseUrl; ?>/public/js/quick-auth-update.js"></script>
    <script src="<?php echo $baseUrl; ?>/public/js/auth.js"></script>
    <script src="<?php echo $baseUrl; ?>/public/js/user.js"></script>
    
    <!-- Reservation System -->
    <script src="<?php echo $baseUrl; ?>/public/js/reservation-form.js"></script>
    <script src="<?php echo $baseUrl; ?>/public/js/reservation-details.js"></script>
    
    <!-- User Reservation Details (solo en páginas de usuario) -->
    <?php if (strpos($_SERVER['REQUEST_URI'], '/user/') !== false): ?>
    <script src="<?php echo $baseUrl; ?>/public/js/user-reservation-details.js"></script>
    <?php endif; ?>
    
    <!-- Session Management -->
    <script src="<?php echo $baseUrl; ?>/public/js/session-manager.js"></script>
    <script src="<?php echo $baseUrl; ?>/public/js/navbar.js"></script>
    
    <!-- Verificación de estilos -->
    <script>
        window.addEventListener('load', function() {
            const styles = document.styleSheets;
            let modalsCssLoaded = false;
            for (let i = 0; i < styles.length; i++) {
                try {
                    if (styles[i].href && styles[i].href.includes('modals.css')) {
                        modalsCssLoaded = true;
                        break;
                    }
                } catch (e) {
                    console.error('Error al verificar estilos:', e);
                }
            }
            if (!modalsCssLoaded) {
                console.error('El archivo modals.css no se ha cargado correctamente');
            }
        });
    </script>
    <script>
        // Abrir modal de login si open=login en la URL (por ejemplo tras reset)
        document.addEventListener('DOMContentLoaded', function() {
            try {
                const params = new URLSearchParams(window.location.search);
                if (params.get('open') === 'login') {
                    const loginModalEl = document.getElementById('loginModal');
                    if (loginModalEl && typeof bootstrap !== 'undefined') {
                        const modal = new bootstrap.Modal(loginModalEl);
                        modal.show();
                    }
                }
            } catch (e) {
                console.error('Error abriendo modal de login:', e);
            }
        });
    </script>
</head>
<body>
<?php
    // Procesar mensajes de URL
    MessageHandler::processUrlMessages();
    ?>
    
    <?php require_once __DIR__ . '/navbar.php'; ?>
    
    <!-- Sistema de mensajes -->
    <div class="container mt-3">
        <?php echo MessageHandler::renderMessages(); ?>
    </div>
    
    <?php require_once __DIR__ . '/modals.php'; ?>

<main>