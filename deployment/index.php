<?php
// index.php - Punto de entrada principal de la aplicación
// Cargar configuraciones y helpers
require_once 'config/config.php';
require_once 'config/Database.php';
require_once 'config/SessionConfig.php';
require_once 'config/i18n.php';
require_once 'config/Auth.php';
require_once 'config/MessageHandler.php';
require_once 'utils/Utils.php';

// Inicializar configuración de sesiones segura
SessionConfig::init();

// Cargar modelos
require_once 'models/User.php';
require_once 'models/Vehicle.php';
require_once 'models/Location.php';
require_once 'models/Reservation.php';
require_once 'models/Boat.php';
require_once 'models/Transfer.php';

// Cargar controladores
require_once 'controllers/AuthController.php';
require_once 'controllers/VehicleController.php';
require_once 'controllers/LocationController.php';
require_once 'controllers/ReservationController.php';
require_once 'controllers/BoatController.php';
require_once 'controllers/TransferController.php';
require_once 'controllers/UserController.php';
require_once 'controllers/AdminController.php';

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Router - Obtener la ruta solicitada
$request = $_SERVER['REQUEST_URI'];
$basePath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
$path = str_replace($basePath, '', $request);

// DEBUG: Ver qué ruta se está procesando
error_log("DEBUG: Procesando ruta: " . $path);

// Manejar rutas específicas
switch (true) {
    // ========================================
    // RUTAS DE ADMIN - DEBEN IR PRIMERO
    // ========================================
    
    // Admin panel principal
    case $path === '/admin':
        require_once __DIR__ . '/middleware/AuthMiddleware.php';
        $adminMiddleware = new AdminMiddleware();
        $adminMiddleware->handle();
        $controller = new AdminController();
        $controller->index();
        break;
        
    // Admin Vehicles
    case $path === '/admin/vehicles':
        require_once __DIR__ . '/middleware/AuthMiddleware.php';
        $adminMiddleware = new AdminMiddleware();
        $adminMiddleware->handle();
        $controller = new AdminController();
        $controller->vehicles();
        break;
        
    // Admin Boats
    case $path === '/admin/boats':
        require_once __DIR__ . '/middleware/AuthMiddleware.php';
        $adminMiddleware = new AdminMiddleware();
        $adminMiddleware->handle();
        $controller = new AdminController();
        $controller->boats();
        break;
        
    // Admin Transfers
    case $path === '/admin/transfers':
        require_once __DIR__ . '/middleware/AuthMiddleware.php';
        $adminMiddleware = new AdminMiddleware();
        $adminMiddleware->handle();
        $controller = new AdminController();
        $controller->transfers();
        break;
        
    // Admin Reservations
    case $path === '/admin/reservations':
        require_once __DIR__ . '/middleware/AuthMiddleware.php';
        $adminMiddleware = new AdminMiddleware();
        $adminMiddleware->handle();
        $controller = new AdminController();
        $controller->reservations();
        break;
        
    // Admin Users
    case $path === '/admin/users':
        require_once __DIR__ . '/middleware/AuthMiddleware.php';
        $adminMiddleware = new AdminMiddleware();
        $adminMiddleware->handle();
        $controller = new AdminController();
        $controller->users();
        break;
        
    // Admin Users API
    case preg_match('/^\/admin\/users\/api$/', $path):
        $controller = new AdminController();
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'GET':
                $controller->getUsersApi();
                break;
            case 'POST':
                $controller->createUserApi();
                break;
        }
        break;
        
    case preg_match('/^\/admin\/users\/api\/([^\/]+)$/', $path, $matches):
        $controller = new AdminController();
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'GET':
                $controller->getUserApi($matches[1]);
                break;
            case 'PUT':
                $controller->updateUserApi($matches[1]);
                break;
            case 'DELETE':
                $controller->deleteUserApi($matches[1]);
                break;
        }
        break;
        
    // Admin Vehicles API
    case preg_match('/^\/admin\/vehicles\/api$/', $path):
        $controller = new AdminController();
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'GET':
                $controller->getVehiclesApi();
                break;
            case 'POST':
                $controller->createVehicleApi();
                break;
        }
        break;
        
    case preg_match('/^\/admin\/vehicles\/api\/([^\/]+)$/', $path, $matches):
        $controller = new AdminController();
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'GET':
                $controller->getVehicleApi($matches[1]);
                break;
            case 'PUT':
                $controller->updateVehicleApi($matches[1]);
                break;
            case 'DELETE':
                $controller->deleteVehicleApi($matches[1]);
                break;
        }
        break;
        
    // Admin Boats API
    case preg_match('/^\/admin\/boats\/api$/', $path):
        $controller = new AdminController();
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'GET':
                $controller->getBoatsApi();
                break;
            case 'POST':
                $controller->createBoatApi();
                break;
        }
        break;
        
    case preg_match('/^\/admin\/boats\/api\/([^\/]+)$/', $path, $matches):
        $controller = new AdminController();
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'GET':
                $controller->getBoatApi($matches[1]);
                break;
            case 'PUT':
                $controller->updateBoatApi($matches[1]);
                break;
            case 'DELETE':
                $controller->deleteBoatApi($matches[1]);
                break;
        }
        break;
        
    // Admin Transfers API
    case preg_match('/^\/admin\/transfers\/api$/', $path):
        $controller = new AdminController();
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'GET':
                $controller->getTransfersApi();
                break;
            case 'POST':
                $controller->createTransferApi();
                break;
        }
        break;
        
    case preg_match('/^\/admin\/transfers\/api\/([^\/]+)$/', $path, $matches):
        $controller = new AdminController();
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'GET':
                $controller->getTransferApi($matches[1]);
                break;
            case 'PUT':
                $controller->updateTransferApi($matches[1]);
                break;
            case 'DELETE':
                $controller->deleteTransferApi($matches[1]);
                break;
        }
        break;
        
    // Admin Reservations API
    case preg_match('/^\/admin\/reservations\/api$/', $path):
        $adminController = new AdminController();
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $adminController->getReservationsApi();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adminController->createReservationApi();
        }
        break;
        
    case preg_match('/^\/admin\/reservations\/api\/([^\/]+)$/', $path, $matches):
        $adminController = new AdminController();
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'GET':
                $adminController->getReservationApi($matches[1]);
                break;
            case 'PUT':
                $adminController->updateReservationApi($matches[1]);
                break;
            case 'DELETE':
                $adminController->deleteReservationApi($matches[1]);
                break;
        }
        break;
        
    case preg_match('/^\/admin\/reservations\/api\/availability(\?.*)?$/', $path):
        $adminController = new AdminController();
        $adminController->checkAvailabilityApi();
        break;
        
    case preg_match('/^\/admin\/reservations\/api\/stats(\?.*)?$/', $path):
        $adminController = new AdminController();
        $adminController->getReservationStatsApi();
        break;

    // Página: verificación de email requerida
    case $path === '/verify-email-required':
        require_once __DIR__ . '/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->showVerifyEmailRequired();
        break;

    // Admin Locations API
    case preg_match('/^\/admin\/locations\/api(\?.*)?$/', $path):
        $adminController = new AdminController();
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $adminController->getLocationsApi();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adminController->createLocationApi();
        }
        break;
        
    // Admin Features API
    case preg_match('/^\/admin\/features\/api(\?.*)?$/', $path):
        error_log("DEBUG: Ruta features API detectada: " . $path);
        $adminController = new AdminController();
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $adminController->getFeaturesApi();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adminController->createFeatureApi();
        }
        break;

    case preg_match('/^\/admin\/features\/api\/([^\/]+)$/', $path, $matches):
        $adminController = new AdminController();
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'PUT':
                $adminController->updateFeatureApi($matches[1]);
                break;
            case 'DELETE':
                $adminController->deleteFeatureApi($matches[1]);
                break;
        }
        break;
        
    case preg_match('/^\/admin\/types\/api(\?.*)?$/', $path):
        error_log("DEBUG: Ruta types API detectada: " . $path);
        $adminController = new AdminController();
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $adminController->getTypesApi();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adminController->createTypeApi();
        }
        break;

    case preg_match('/^\/admin\/types\/api\/([^\/]+)$/', $path, $matches):
        $adminController = new AdminController();
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'PUT':
                $adminController->updateTypeApi($matches[1]);
                break;
            case 'DELETE':
                $adminController->deleteTypeApi($matches[1]);
                break;
        }
        break;

    // Admin Service Categories API
    case preg_match('/^\/admin\/service-categories\/api(\?.*)?$/', $path):
        $adminController = new AdminController();
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $adminController->getServiceCategoriesApi();
        }
        break;
    
    // ========================================
    // RUTAS DE USUARIO AUTENTICADO
    // ========================================
    
    // Dashboard del usuario
    case $path === '/user/dashboard':
        require_once __DIR__ . '/controllers/UserController.php';
        $controller = new UserController();
        $controller->dashboard();
        break;

    // Actualizar perfil de usuario (API)
    case $path === '/user/update-profile':
        require_once __DIR__ . '/controllers/UserController.php';
        $controller = new UserController();
        $controller->updateProfile();
        break;
        
    // Mis reservas
    case $path === '/user/reservations':
        require_once __DIR__ . '/controllers/UserController.php';
        $controller = new UserController();
        $controller->reservations();
        break;
        
    // API de reservas del usuario
    case preg_match('/^\/user\/reservations\/api\/([^\/]+)$/', $path, $matches):
        require_once __DIR__ . '/controllers/UserController.php';
        $controller = new UserController();
        $controller->getReservationApi($matches[1]);
        break;
        
    case preg_match('/^\/user\/reservations\/api\/([^\/]+)\/cancel$/', $path, $matches):
        require_once __DIR__ . '/controllers/UserController.php';
        $controller = new UserController();
        $controller->cancelReservationApi($matches[1]);
        break;
    
    // ========================================
    // RUTAS PÚBLICAS
    // ========================================
    
    case preg_match('/^\/(\?.*)?$/', $path):
        $controller = new VehicleController();
        $controller->home();
        break;
        
    case $path === '/vehicles':
        $controller = new VehicleController();
        $controller->index();
        break;
        
    case preg_match('/^\/vehicles\/details\/([^\/]+)$/', $path, $matches):
        $controller = new VehicleController();
        $controller->details($matches[1]);
        break;
        
    case preg_match('/^\/vehicles\/search(\?.*)?$/', $path):
        $controller = new VehicleController();
        $controller->search();
        break;
        
    case preg_match('/^\/vehicles\/details\?id=([^&]+)$/', $path, $matches):
        $controller = new VehicleController();
        $controller->details($matches[1]);
        break;
        
    case $path === '/boats':
        $controller = new BoatController();
        $controller->index();
        break;
        
    case preg_match('/^\/boats\/search(\?.*)?$/', $path):
        $controller = new BoatController();
        $controller->search();
        break;
        
    case $path === '/boats/category':
        $controller = new BoatController();
        $controller->category();
        break;
        
    case preg_match('/^\/boats\/details\?id=([^&]+)$/', $path, $matches):
        $controller = new BoatController();
        $controller->details($matches[1]);
        break;
        
    case $path === '/transfers':
        $controller = new TransferController();
        $controller->index();
        break;
        
    case preg_match('/^\/transfers\/search(\?.*)?$/', $path):
        $controller = new TransferController();
        $controller->search();
        break;
        
    case preg_match('/^\/transfers\/details\?id=([^&]+)$/', $path, $matches):
        $controller = new TransferController();
        $controller->details($matches[1]);
        break;
        
    // Rutas de autenticación - Solo APIs, no páginas
    case $path === '/auth/current-user':
        $controller = new AuthController();
        $controller->getCurrentUser();
        break;
        
    case $path === '/auth/check-user-exists':
        $controller = new AuthController();
        $controller->checkUserExists();
        break;
        
    case $path === '/auth/renew-session':
        $controller = new AuthController();
        $controller->renewSession();
        break;
        
    case $path === '/auth/login':
        $controller = new AuthController();
        $controller->login();
        break;
        
    case $path === '/auth/register':
        $controller = new AuthController();
        $controller->register();
        break;
        
    case $path === '/auth/logout':
        $controller = new AuthController();
        $controller->logout();
        break;
        
    // Redirigir rutas obsoletas a la página principal
    case $path === '/login':
    case preg_match('/^\/login(\?.*)?$/', $path):
        // Redirigir a la página principal donde están los modales
        header('Location: ' . APP_URL);
        exit();
        break;
        
    case $path === '/register':
    case preg_match('/^\/register(\?.*)?$/', $path):
        // Redirigir a la página principal donde están los modales
        header('Location: ' . APP_URL);
        exit();
        break;
        
    case $path === '/auth':
        // Redirigir a la página principal donde están los modales
        header('Location: ' . APP_URL);
        exit();
        break;
        
    case $path === '/logout':
        $controller = new AuthController();
        $controller->logout();
        break;
    
    // Recuperación de contraseña
    case $path === '/auth/forgot-password':
        require_once __DIR__ . '/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->forgotPassword();
        break;

    case $path === '/auth/validate-reset-token':
        require_once __DIR__ . '/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->validateResetToken();
        break;

    case (preg_match('/^\/auth\/reset-password(\?.*)?$/', $path) && $_SERVER['REQUEST_METHOD'] === 'GET'):
        require_once __DIR__ . '/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->showResetPasswordPage();
        break;

    case $path === '/auth/reset-password' && $_SERVER['REQUEST_METHOD'] === 'POST':
        require_once __DIR__ . '/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->resetPassword();
        break;
        
    // Verificación de email
    case preg_match('/^\/verify-email(\?.*)?$/', $path):
        require_once __DIR__ . '/controllers/VerificationController.php';
        $controller = new VerificationController();
        $controller->verifyEmail();
        break;

    // Reenviar email de verificación
    case $path === '/auth/resend-verification':
        require_once __DIR__ . '/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->resendVerificationEmail();
        break;

    // Reenviar email de verificación (público, sin sesión)
    case $path === '/auth/resend-verification-public':
        require_once __DIR__ . '/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->resendVerificationEmailPublic();
        break;
        
    // Verificación de teléfono - DESHABILITADA TEMPORALMENTE
    // TODO: Habilitar cuando se configure Twilio
    // case preg_match('/^\/verify-phone(\?.*)?$/', $path):
    //     require_once __DIR__ . '/controllers/VerificationController.php';
    //     $controller = new VerificationController();
    //     $controller->showVerifyPhonePage();
    //     break;
        
    // Verificación de teléfono - Enviar OTP - DESHABILITADA TEMPORALMENTE
    // TODO: Habilitar cuando se configure Twilio
    // case $path === '/verify-phone/send-otp':
    //     require_once __DIR__ . '/controllers/VerificationController.php';
    //     $controller = new VerificationController();
    //     $controller->sendPhoneOTP();
    //     break;
        
    // Verificación de teléfono - Verificar OTP - DESHABILITADA TEMPORALMENTE
    // TODO: Habilitar cuando se configure Twilio
    // case $path === '/verify-phone/verify-otp':
    //     require_once __DIR__ . '/controllers/VerificationController.php';
    //     $controller = new VerificationController();
    //     $controller->verifyPhoneOTP();
    //     break;
        
    // Rutas específicas del usuario
    case $path === '/user/dashboard':
        $controller = new UserController();
        $controller->dashboard();
        break;
        
    case $path === '/user/profile':
        $controller = new UserController();
        $controller->profile();
        break;
        
    case $path === '/user/reservations':
        $controller = new UserController();
        $controller->reservations();
        break;
        
    case $path === '/admin':
        $controller = new AdminController();
        $controller->index();
        break;
        
    default:
        // Para rutas más complejas usamos el sistema de enrutamiento basado en segmentos
$segments = explode('/', trim($path, '/'));
$controller_name = !empty($segments[0]) ? $segments[0] : 'home';
$action = !empty($segments[1]) ? $segments[1] : 'index';
$param = !empty($segments[2]) ? $segments[2] : null;

// Definir mapeo de rutas a controladores
$routes = [
    'home' => 'VehicleController',
    'vehicles' => 'VehicleController',
    'boats' => 'BoatController',
    'transfers' => 'TransferController',
    'locations' => 'LocationController',
    'reservations' => 'ReservationController',
    'login' => 'AuthController',
    'register' => 'AuthController',
    'logout' => 'AuthController',
            'user' => 'UserController',
    'profile' => 'UserController',
    'admin' => 'AdminController'
];

// Mapeo de acciones especiales
$action_mappings = [
    'login' => 'login',
    'register' => 'register',
    'logout' => 'logout'
];

        if (isset($routes[$controller_name])) {
            $controller_class = $routes[$controller_name];
            
            // Verificar si hay un mapeo especial para la acción
            $action = isset($action_mappings[$controller_name]) ? $action_mappings[$controller_name] : $action;
            
            // Crear instancia del controlador
            if (class_exists($controller_class)) {
                $controller = new $controller_class();
                
                // Manejar rutas de detalles
                if ($action === 'show' && $param) {
                    if (method_exists($controller, 'show')) {
                        $controller->show($param);
                        return;
                    }
                }
                
                // Verificar si el método existe
                if (method_exists($controller, $action)) {
                    // Ejecutar el método con el parámetro si existe
                    if ($param) {
                        $controller->$action($param);
                    } else {
                        $controller->$action();
                    }
                } else {
                    // Método no encontrado
                    http_response_code(404);
                    require __DIR__ . '/views/404.php';
                }
            } else {
                // Controlador no encontrado
                http_response_code(404);
                require __DIR__ . '/views/404.php';
            }
        } else {
            // Ruta no encontrada
            http_response_code(404);
            require __DIR__ . '/views/404.php';
        }
        break;
}

// Funciones de ayuda para la navegación y vistas
function base_url($path = '') {
    $base_url = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
    $base_url .= $_SERVER['HTTP_HOST'];
    $base_url .= str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
    
    return $base_url . '/' . ltrim($path, '/');
}

function redirect($path) {
    header('Location: ' . base_url($path));
    exit();
}

function view($template, $data = []) {
    // Extraer variables para que estén disponibles en la vista
    extract($data);
    
    // Si la vista existe, incluirla
    $view_path = 'views/' . $template . '.php';
    if (file_exists($view_path)) {
        // Incluir el header
        require_once 'views/layouts/header.php';
        
        // Incluir la vista
        require_once($view_path);
        
        // Incluir el footer
        require_once 'views/layouts/footer.php';
    } else {
        // Si no existe la vista, mostrar error 404
        http_response_code(404);
        require_once(__DIR__ . '/views/404.php');
    }
}

// Mover estas funciones a helpers.php o utils/User.php si no están ya definidas
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
}

// Para proteger rutas de administrador
function require_admin() {
    if (!isLoggedIn() || !isAdmin()) {
        redirect('login');
        exit();
    }
}

// Para proteger rutas de usuario autenticado
function require_login() {
    if (!isLoggedIn()) {
        redirect('login');
        exit();
    }
}
