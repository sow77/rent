<?php
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../models/Reservation.php';

class UserController {
    
    public function __construct() {
        // Verificar autenticación
        if (!Auth::isAuthenticated()) {
            header('Location: ' . APP_URL . '/login?message=login_required');
            exit;
        }
    }
    
    public function dashboard() {
        $user = Auth::getCurrentUser();
        $reservations = Reservation::getByUserId($user['id']);
        
        $pageTitle = 'Mi Dashboard';
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/user/dashboard.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
    
    public function reservations() {
        $user = Auth::getCurrentUser();
        $reservations = Reservation::getByUserId($user['id']);
        
        $pageTitle = 'Mis Reservas';
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/user/reservations.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
    
    public function profile() {
        $user = Auth::getCurrentUser();
        
        $pageTitle = 'Mi Perfil';
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/user/profile.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }

    public function updateProfile() {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                return;
            }
            header('Location: ' . APP_URL . '/user/profile');
            return;
        }
        try {
            $current = Auth::getCurrentUser();
            $db = Database::getInstance()->getConnection();
            $userModel = new User($db);

            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if ($name === '' || $email === '') {
                if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Nombre y email son obligatorios']); } else { MessageHandler::error('Nombre y email son obligatorios'); header('Location: ' . APP_URL . '/user/profile'); }
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Email no válido']); } else { MessageHandler::error('Email no válido'); header('Location: ' . APP_URL . '/user/profile'); }
                return;
            }

            // Si cambia el email, verificar que no exista
            if (strtolower($email) !== strtolower($current['email'])) {
                $stmt = $db->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
                $stmt->execute([$email, $current['id']]);
                if ($stmt->fetch()) {
                    if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'El email ya está en uso']); } else { MessageHandler::error('El email ya está en uso'); header('Location: ' . APP_URL . '/user/profile'); }
                    return;
                }
            }

            $fields = [ 'name' => $name, 'email' => $email ];

            // Si quiere cambiar contraseña
            if ($newPassword !== '' || $confirmPassword !== '') {
                if ($newPassword !== $confirmPassword) {
                    if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']); } else { MessageHandler::error('Las contraseñas no coinciden'); header('Location: ' . APP_URL . '/user/profile'); }
                    return;
                }
                // Verificar contraseña actual
                $stmt = $db->prepare('SELECT password FROM users WHERE id = ?');
                $stmt->execute([$current['id']]);
                $row = $stmt->fetch();
                if (!$row || !password_verify($currentPassword, $row['password'])) {
                    if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'La contraseña actual no es correcta']); } else { MessageHandler::error('La contraseña actual no es correcta'); header('Location: ' . APP_URL . '/user/profile'); }
                    return;
                }
                // Validar fortaleza
                $val = Validation::validatePassword($newPassword);
                if (!$val['valid']) {
                    if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => implode('\n', $val['errors'])]); } else { MessageHandler::error(implode('\n', $val['errors'])); header('Location: ' . APP_URL . '/user/profile'); }
                    return;
                }
                $fields['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            // Construir UPDATE dinámico
            $setParts = [];
            $params = [];
            foreach ($fields as $k => $v) {
                $setParts[] = "$k = ?";
                $params[] = $v;
            }
            $params[] = $current['id'];
            $sql = 'UPDATE users SET ' . implode(', ', $setParts) . ', updated_at = NOW() WHERE id = ?';
            $stmt = $db->prepare($sql);
            $ok = $stmt->execute($params);

            if ($ok) {
                // Sincronizar sesión si cambió nombre/email
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success' => true, 'message' => 'Perfil actualizado correctamente']); }
                else { MessageHandler::success('Perfil actualizado correctamente'); header('Location: ' . APP_URL . '/user/profile'); }
            } else {
                if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el perfil']); }
                else { MessageHandler::error('No se pudo actualizar el perfil'); header('Location: ' . APP_URL . '/user/profile'); }
            }
        } catch (Exception $e) {
            if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]); }
            else { MessageHandler::error('Error: ' . $e->getMessage()); header('Location: ' . APP_URL . '/user/profile'); }
        }
    }
    
    /**
     * API: Obtener detalles de una reserva específica del usuario
     */
    public function getReservationApi($reservationId) {
        header('Content-Type: application/json');
        
        try {
            $user = Auth::getCurrentUser();
            $reservation = Reservation::getById($reservationId, $user['id']);
            
            if (!$reservation) {
                echo json_encode(['success' => false, 'message' => 'Reserva no encontrada']);
                return;
            }
            
            // Verificar que la reserva pertenece al usuario
            if ($reservation['user_id'] !== $user['id']) {
                echo json_encode(['success' => false, 'message' => 'No tienes permisos para ver esta reserva']);
                return;
            }
            
            echo json_encode(['success' => true, 'reservation' => $reservation]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al obtener la reserva: ' . $e->getMessage()]);
        }
    }
    
    /**
     * API: Cancelar una reserva del usuario
     */
    public function cancelReservationApi($reservationId) {
        header('Content-Type: application/json');
        
        try {
            $user = Auth::getCurrentUser();
            $reservation = Reservation::getById($reservationId, $user['id']);
            
            if (!$reservation) {
                echo json_encode(['success' => false, 'message' => 'Reserva no encontrada']);
                return;
            }
            
            // Verificar que la reserva pertenece al usuario
            if ($reservation['user_id'] !== $user['id']) {
                echo json_encode(['success' => false, 'message' => 'No tienes permisos para cancelar esta reserva']);
                return;
            }
            
            // Verificar que la reserva se puede cancelar
            if ($reservation['status'] !== 'pendiente') {
                echo json_encode(['success' => false, 'message' => 'Solo se pueden cancelar reservas pendientes']);
                return;
            }
            
            // Cancelar la reserva
            $result = Reservation::update($reservationId, ['status' => 'cancelada'], $user['id']);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Reserva cancelada exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al cancelar la reserva']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al cancelar la reserva: ' . $e->getMessage()]);
        }
    }
}
?>
