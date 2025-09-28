// Rutas de autenticación
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@register');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');

// Rutas API de autenticación
$router->post('/auth/login', 'AuthController@loginApi');
$router->post('/auth/register', 'AuthController@registerApi');
$router->post('/auth/logout', 'AuthController@logoutApi');
$router->post('/auth/renew-session', 'AuthController@renewSession');

// Rutas de recuperación de contraseña
$router->post('/auth/forgot-password', 'AuthController@forgotPassword');
$router->post('/auth/reset-password', 'AuthController@resetPassword');
$router->post('/auth/validate-reset-token', 'AuthController@validateResetToken');
$router->get('/auth/reset-password', 'AuthController@showResetPasswordPage');

// Rutas de verificación
$router->get('/verify-email', 'VerificationController@verifyEmail');
$router->get('/verify-phone', 'VerificationController@showVerifyPhonePage');
$router->get('/verify-email-required', 'AuthController@showVerifyEmailRequired');
$router->post('/auth/send-phone-otp', 'VerificationController@sendPhoneOTP');
$router->post('/auth/verify-phone-otp', 'VerificationController@verifyPhoneOTP');
$router->post('/auth/resend-verification', 'AuthController@resendVerificationEmail');

// Rutas protegidas (requieren autenticación)
$router->group(['middleware' => 'auth'], function($router) {
    // Rutas del panel de administración
    $router->get('/admin', 'AdminController@index');
    $router->get('/admin/users', 'AdminController@users');
    
    // Rutas de vehículos
    $router->get('/admin/vehicles', 'AdminController@vehicles');
    $router->get('/admin/vehicles/list', 'AdminController@getVehicles');
    $router->get('/admin/vehicles/{id}', 'AdminController@getVehicle');
    $router->post('/admin/vehicles/save', 'AdminController@saveVehicle');
    $router->delete('/admin/vehicles/delete/{id}', 'AdminController@deleteVehicle');
    
    // Rutas de barcos
    $router->get('/admin/boats', 'AdminController@boats');
    $router->get('/admin/boats/list', 'AdminController@getBoats');
    $router->get('/admin/boats/{id}', 'AdminController@getBoat');
    $router->post('/admin/boats/save', 'AdminController@saveBoat');
    $router->delete('/admin/boats/delete/{id}', 'AdminController@deleteBoat');
    
    // Rutas de traslados
    $router->get('/admin/transfers', 'AdminController@transfers');
    $router->get('/admin/transfers/list', 'AdminController@getTransfers');
    $router->get('/admin/transfers/{id}', 'AdminController@getTransfer');
    $router->post('/admin/transfers/save', 'AdminController@saveTransfer');
    $router->delete('/admin/transfers/delete/{id}', 'AdminController@deleteTransfer');
    
    // Rutas de reservas
    $router->get('/admin/reservations', 'AdminController@reservations');
    $router->get('/admin/reservations/list', 'AdminController@getReservations');
    $router->get('/admin/reservations/{id}', 'AdminController@getReservation');
    $router->delete('/admin/reservations/{id}', 'AdminController@deleteReservation');
    
    // Configuración
    $router->get('/admin/settings', 'AdminController@settings');

    // Rutas API para usuarios
    $router->get('/admin/users/api', 'AdminController@getUsersApi');
    $router->get('/admin/users/api/{id}', 'AdminController@getUserApi');
    $router->post('/admin/users/api', 'AdminController@createUserApi');
    $router->put('/admin/users/api/{id}', 'AdminController@updateUserApi');
    $router->delete('/admin/users/api/{id}', 'AdminController@deleteUserApi');
}); 