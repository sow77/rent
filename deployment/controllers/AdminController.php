<?php
// controllers/AdminController.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/MessageHandler.php';

class AdminController {
    
    public function __construct() {
        // Solo verificar autenticación para métodos que no son API
        $this->checkAuthForNonApiMethods();
    }
    
    private function checkAuthForNonApiMethods() {
        // Obtener la ruta actual
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        
        // Si es una API call, no verificar autenticación
        if (strpos($requestUri, '/api') !== false) {
            return;
        }
        
        // Verificar autenticación y rol de administrador solo para métodos no-API
        if (!Auth::isAuthenticated()) {
            if (!headers_sent()) {
            header('Location: ' . APP_URL . '?message=login_required');
            }
            exit;
        }
        
        if (!Auth::isAdmin()) {
            if (!headers_sent()) {
            header('Location: ' . APP_URL . '?message=admin_required');
            }
            exit;
        }
    }
    
    /**
     * Panel principal de administración
     */
    public function index() {
        $user = Auth::getCurrentUser();
        $pageTitle = 'Panel de Control';
        $currentPage = 'dashboard';
        
        // Obtener estadísticas generales
        $stats = $this->getAdminStats();
        
        // Incluir la vista del dashboard (que ya incluye header y footer)
        include 'views/admin/dashboard.php';
    }
    
    /**
     * Gestión de usuarios
     */
    public function users() {
        $pageTitle = 'Gestión de Usuarios';
        $currentPage = 'users';
        $users = $this->getAllUsers();
        
        // Incluir la vista de usuarios (que incluirá header y footer)
        include 'views/admin/users.php';
    }
    
    /**
     * Gestión de vehículos
     */
    public function vehicles() {
        $pageTitle = 'Gestión de Vehículos';
        $currentPage = 'vehicles';
        $vehicles = $this->getAllVehicles();
        
        // Incluir la vista de vehículos (que incluirá header y footer)
        include 'views/admin/vehicles.php';
    }
    
    /**
     * Gestión de barcos
     */
    public function boats() {
        $pageTitle = 'Gestión de Barcos';
        $currentPage = 'boats';
        $boats = $this->getAllBoats();
        
        // Incluir la vista de barcos (que incluirá header y footer)
        include 'views/admin/boats.php';
    }
    
    /**
     * Gestión de traslados
     */
    public function transfers() {
        $pageTitle = 'Gestión de Traslados';
        $currentPage = 'transfers';
        $transfers = $this->getAllTransfers();
        
        // Incluir la vista de traslados (que incluirá header y footer)
        include 'views/admin/transfers.php';
    }
    
    /**
     * Gestión de reservas
     */
    public function reservations() {
        $pageTitle = 'Gestión de Reservas';
        $currentPage = 'reservations';
        $reservations = $this->getAllReservations();
        
        // Incluir la vista de reservas (que incluirá header y footer)
        include 'views/admin/reservations.php';
    }
    
    /**
     * Obtener estadísticas para el admin
     */
    public function getAdminStats() {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Total usuarios
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM users");
            $stmt->execute();
            $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Total vehículos
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM vehicles");
            $stmt->execute();
            $totalVehicles = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Total reservas
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM reservations");
            $stmt->execute();
            $totalReservations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Ingresos totales
            $stmt = $db->prepare("SELECT SUM(total_cost) as total FROM reservations WHERE status != 'cancelada'");
            $stmt->execute();
            $totalIncome = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            return [
                'total_users' => $totalUsers,
                'total_vehicles' => $totalVehicles,
                'total_reservations' => $totalReservations,
                'total_income' => $totalIncome
            ];
        } catch (Exception $e) {
            return [
                'total_users' => 0,
                'total_vehicles' => 0,
                'total_reservations' => 0,
                'total_income' => 0
            ];
        }
    }
    
    /**
     * Obtener todos los usuarios
     */
    public function getAllUsers() {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM users ORDER BY created_at DESC");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Asegurar que todos los usuarios tengan el campo active
            foreach ($users as &$user) {
                if (!isset($user['active'])) {
                    $user['active'] = 1; // Por defecto activo
                }
                if (!isset($user['phone'])) {
                    $user['phone'] = null;
                }
                if (!isset($user['avatar'])) {
                    $user['avatar'] = null;
                }
                if (!isset($user['address'])) {
                    $user['address'] = null;
                }
            }
            
            return $users;
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtener todos los vehículos
     */
    public function getAllVehicles() {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM vehicles ORDER BY created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtener todos los barcos
     */
    public function getAllBoats() {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM boats ORDER BY created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtener todos los traslados
     */
    public function getAllTransfers() {
        try {
            $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT * FROM transfers ORDER BY created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getAllTransfers: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener todas las reservas
     */
    public function getAllReservations() {
        try {
            return Reservation::getAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // ========================================
    // MÉTODOS API PARA USUARIOS
    // ========================================

    /**
     * API: Obtener todos los usuarios
     */
    public function getUsersApi() {
        header('Content-Type: application/json');
        try {
            $q = $_GET['q'] ?? $_GET['search'] ?? '';
            $role = $_GET['role'] ?? '';
            $active = isset($_GET['active']) && $_GET['active'] !== '' ? $_GET['active'] : '';

            $db = Database::getInstance()->getConnection();
            $sql = "SELECT * FROM users WHERE 1=1";
            $params = [];

            if ($q !== '') {
                $like = "%{$q}%";
                $sql .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ? OR id LIKE ?)";
                array_push($params, $like, $like, $like, $like);
            }

            if ($role !== '') {
                $sql .= " AND role = ?";
                $params[] = $role;
            }

            if ($active !== '') {
                $sql .= " AND active = ?";
                $params[] = (int)$active;
            }

            $sql .= " ORDER BY created_at DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Normalizar campos esperados
            foreach ($users as &$user) {
                if (!isset($user['active'])) { $user['active'] = 1; }
                if (!isset($user['phone'])) { $user['phone'] = null; }
                if (!isset($user['avatar'])) { $user['avatar'] = null; }
                if (!isset($user['address'])) { $user['address'] = null; }
            }

            echo json_encode(['success' => true, 'data' => $users]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error del servidor']);
        }
    }

    /**
     * API: Obtener usuario por ID
     */
    public function getUserApi($id) {
        header('Content-Type: application/json');
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                echo json_encode(['success' => true, 'data' => $user]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error del servidor']);
        }
    }

    /**
     * API: Crear usuario
     */
    public function createUserApi() {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Log de entrada
            error_log("CREATE USER API - Input recibido: " . json_encode($input));
            
            // Validar datos requeridos
            if (empty($input['name']) || empty($input['email']) || empty($input['password'])) {
                error_log("CREATE USER API - Datos requeridos faltantes");
                echo json_encode(['success' => false, 'message' => 'Datos requeridos faltantes']);
                return;
            }

            $db = Database::getInstance()->getConnection();
            error_log("CREATE USER API - Conexión DB exitosa");
            
            // Verificar si el email ya existe
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$input['email']]);
            if ($stmt->fetchColumn() > 0) {
                error_log("CREATE USER API - Email ya existe: " . $input['email']);
                echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
                return;
            }

            // Asignar avatar por defecto si no se proporciona uno
            if (empty($input['avatar'])) {
                $input['avatar'] = 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face'; // Avatar por defecto
                error_log("CREATE USER API - Avatar por defecto asignado: " . $input['avatar']);
            }
            
            // Crear usuario: por seguridad, active=0 y pendiente de verificación
            $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (id, name, email, password, role, phone, address, avatar, active, account_status) VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, 0, 'pending_verification')";
            error_log("CREATE USER API - SQL: " . $sql);
            
            $stmt = $db->prepare($sql);
            $params = [
                $input['name'],
                $input['email'],
                $hashedPassword,
                $input['role'] ?? 'user',
                $input['phone'] ?? null,
                $input['address'] ?? null,
                $input['avatar'] ?? null,
                $input['active'] ?? 1
            ];
            
            error_log("CREATE USER API - Parámetros: " . json_encode($params));
            
            $result = $stmt->execute($params);
            error_log("CREATE USER API - Execute result: " . ($result ? 'true' : 'false'));
            
            if (!$result) {
                error_log("CREATE USER API - Error en execute: " . json_encode($stmt->errorInfo()));
            }

            // Obtener el ID del usuario creado
            $userId = $db->lastInsertId();
            error_log("CREATE USER API - Usuario creado con ID: " . $userId);

            echo json_encode(['success' => true, 'message' => 'Usuario creado exitosamente', 'user_id' => $userId]);
            error_log("CREATE USER API - Usuario creado exitosamente");
        } catch (Exception $e) {
            error_log("CREATE USER API - Exception: " . $e->getMessage());
            error_log("CREATE USER API - Trace: " . $e->getTraceAsString());
            echo json_encode(['success' => false, 'message' => 'Error al crear usuario: ' . $e->getMessage()]);
        }
    }

    /**
     * API: Actualizar usuario
     */
    public function updateUserApi($id) {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['name']) || empty($input['email'])) {
                echo json_encode(['success' => false, 'message' => 'Datos requeridos faltantes']);
                return;
            }

            $db = Database::getInstance()->getConnection();
            
            // Verificar si el email ya existe en otro usuario
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$input['email'], $id]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
                return;
            }

            // Construir query de actualización
            $updateFields = ['name = ?', 'email = ?', 'role = ?', 'phone = ?', 'address = ?', 'avatar = ?', 'active = ?'];
            $params = [
                $input['name'],
                $input['email'],
                $input['role'] ?? 'user',
                $input['phone'] ?? null,
                $input['address'] ?? null,
                $input['avatar'] ?? null,
                $input['active'] ?? 1,
                $id
            ];

            // Si se proporciona nueva contraseña, actualizarla
            if (!empty($input['password'])) {
                $updateFields[] = 'password = ?';
                $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
                array_splice($params, -1, 0, [$hashedPassword]);
            }

            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            echo json_encode(['success' => true, 'message' => 'Usuario actualizado exitosamente']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar usuario']);
        }
    }

    /**
     * API: Eliminar usuario
     */
    public function deleteUserApi($id) {
        header('Content-Type: application/json');
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Usuario eliminado exitosamente']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar usuario']);
        }
    }

    // ========================================
    // MÉTODOS API PARA VEHÍCULOS
    // ========================================

    /**
     * API: Obtener todos los vehículos
     */
    public function getVehiclesApi() {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $this->getAllVehicles()]);
    }

    /**
     * API: Obtener vehículo por ID
     */
    public function getVehicleApi($id) {
        header('Content-Type: application/json');
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM vehicles WHERE id = ?");
            $stmt->execute([$id]);
            $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($vehicle) {
                echo json_encode(['success' => true, 'data' => $vehicle]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Vehículo no encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error del servidor']);
        }
    }

    /**
     * API: Crear vehículo
     */
    public function createVehicleApi() {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Log detallado para debugging
            error_log("=== CREAR VEHÍCULO ===");
            error_log("Input recibido: " . print_r($input, true));
            
            if (empty($input['brand']) || empty($input['model']) || empty($input['category'])) {
                error_log("Campos requeridos faltantes");
                echo json_encode(['success' => false, 'message' => 'Datos requeridos faltantes']);
                return;
            }

            $db = Database::getInstance()->getConnection();
            
            // Verificar si location_id está vacío y asignar uno por defecto
            if (empty($input['location_id'])) {
                // Obtener el primer location_id disponible
                $stmt = $db->prepare("SELECT id FROM locations LIMIT 1");
                $stmt->execute();
                $location = $stmt->fetch(PDO::FETCH_ASSOC);
                $input['location_id'] = $location ? $location['id'] : null;
                error_log("Location ID asignado: " . $input['location_id']);
            }
            
            // Procesar imágenes
            $images = [];
            if (!empty($input['images'])) {
                if (is_string($input['images'])) {
                    $images = json_decode($input['images'], true) ?: [];
                } elseif (is_array($input['images'])) {
                    $images = $input['images'];
                }
            }
            
            // Si no hay imágenes, usar imagen por defecto
            if (empty($images)) {
                $images = ['https://images.unsplash.com/photo-1555215695-3004980ad54e?w=800'];
                error_log("Imagen por defecto asignada");
            }
            
            // Usar la primera imagen como imagen principal para compatibilidad
            $input['image'] = $images[0];
            $input['images'] = json_encode($images);
            
            $stmt = $db->prepare("
                INSERT INTO vehicles (id, brand, model, year, category, daily_rate, capacity, image, images, description, available, location_id) 
                VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $params = [
                $input['brand'],
                $input['model'],
                $input['year'] ?? date('Y'),
                $input['category'],
                $input['daily_rate'] ?? 0,
                $input['capacity'] ?? 5,
                $input['image'] ?? null,
                $input['images'] ?? null,
                $input['description'] ?? null,
                $input['available'] ?? 1,
                $input['location_id']
            ];
            
            error_log("Parámetros para INSERT: " . print_r($params, true));
            
            $result = $stmt->execute($params);
            error_log("Resultado de execute: " . ($result ? 'true' : 'false'));
            
            if ($result) {
                $vehicleId = $db->lastInsertId();
                error_log("Vehículo creado con ID: " . $vehicleId);
                
            // Manejar características si se proporcionan (mapear nombres -> IDs)
            if (!empty($input['features'])) {
                $features = is_string($input['features']) ? json_decode($input['features'], true) : $input['features'];
                if (is_array($features) && !empty($features)) {
                    $featureIds = [];
                    $mapStmt = $db->prepare("SELECT id FROM features WHERE name = ? AND category = 'vehicle'");
                    foreach ($features as $featureName) {
                        if (!is_string($featureName) || $featureName === '') { continue; }
                        $mapStmt->execute([$featureName]);
                        $feature = $mapStmt->fetch(PDO::FETCH_ASSOC);
                        if ($feature && isset($feature['id'])) {
                            $featureIds[] = $feature['id'];
                        }
                    }
                    if (!empty($featureIds)) {
                        $this->saveVehicleFeatures($db, $vehicleId, $featureIds);
                    }
                }
            }
                
                echo json_encode(['success' => true, 'message' => 'Vehículo creado exitosamente', 'id' => $vehicleId]);
            } else {
                error_log("Error en execute: " . print_r($stmt->errorInfo(), true));
                echo json_encode(['success' => false, 'message' => 'Error en la base de datos']);
            }
            
        } catch (Exception $e) {
            error_log("Excepción en createVehicleApi: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            echo json_encode(['success' => false, 'message' => 'Error al crear vehículo: ' . $e->getMessage()]);
        }
    }

    /**
     * Guardar características de vehículo en entity_features
     */
    private function saveVehicleFeatures($db, $vehicleId, $features) {
        try {
            // Eliminar características existentes
            $stmt = $db->prepare("DELETE FROM entity_features WHERE entity_type = 'vehicle' AND entity_id = ?");
            $stmt->execute([$vehicleId]);
            
            // Insertar nuevas características
            if (!empty($features)) {
                $stmt = $db->prepare("
                    INSERT INTO entity_features (id, entity_type, entity_id, feature_id) 
                    VALUES (UUID(), 'vehicle', ?, ?)
                ");
                
                foreach ($features as $featureId) {
                    $stmt->execute([$vehicleId, $featureId]);
                }
            }
        } catch (Exception $e) {
            error_log("Error al guardar características del vehículo: " . $e->getMessage());
        }
    }

    /**
     * API: Actualizar vehículo
     */
    public function updateVehicleApi($id) {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['brand']) || empty($input['model']) || empty($input['category'])) {
                echo json_encode(['success' => false, 'message' => 'Datos requeridos faltantes']);
                return;
            }

            $db = Database::getInstance()->getConnection();
            
            // Procesar imágenes
            $images = [];
            if (!empty($input['images'])) {
                if (is_string($input['images'])) {
                    $images = json_decode($input['images'], true) ?: [];
                } elseif (is_array($input['images'])) {
                    $images = $input['images'];
                }
            }
            
            // Si no hay imágenes, usar imagen por defecto
            if (empty($images)) {
                $images = ['https://images.unsplash.com/photo-1555215695-3004980ad54e?w=800'];
            }
            
            // Usar la primera imagen como imagen principal para compatibilidad
            $input['image'] = $images[0];
            $input['images'] = json_encode($images);
            
            $stmt = $db->prepare("
                UPDATE vehicles SET 
                brand = ?, model = ?, year = ?, category = ?, daily_rate = ?, 
                image = ?, images = ?, description = ?, available = ?, location_id = ?, capacity = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $input['brand'],
                $input['model'],
                $input['year'] ?? date('Y'),
                $input['category'],
                $input['daily_rate'] ?? 0,
                $input['image'],
                $input['images'],
                $input['description'] ?? null,
                $input['available'] ?? 1,
                $input['location_id'] ?? null,
                $input['capacity'] ?? 5,
                $id
            ]);

            // Actualizar características en entity_features
            if (isset($input['features'])) {
                try {
                    // Eliminar características existentes
                    $stmt = $db->prepare("DELETE FROM entity_features WHERE entity_type = 'vehicle' AND entity_id = ?");
                    $stmt->execute([$id]);
                    
                    // Insertar nuevas características
                    if (!empty($input['features'])) {
                        $features = json_decode($input['features'], true);
                        if (is_array($features)) {
                            $stmt = $db->prepare("INSERT INTO entity_features (entity_type, entity_id, feature_id) VALUES (?, ?, ?)");
                            foreach ($features as $featureName) {
                                // Obtener feature_id por nombre
                                $featureStmt = $db->prepare("SELECT id FROM features WHERE name = ? AND category = 'vehicle'");
                                $featureStmt->execute([$featureName]);
                                $feature = $featureStmt->fetch(PDO::FETCH_ASSOC);
                                if ($feature) {
                                    $stmt->execute(['vehicle', $id, $feature['id']]);
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log("Error actualizando características: " . $e->getMessage());
                }
            }

            echo json_encode(['success' => true, 'message' => 'Vehículo actualizado exitosamente']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar vehículo']);
        }
    }

    /**
     * API: Eliminar vehículo
     */
    public function deleteVehicleApi($id) {
        header('Content-Type: application/json');
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM vehicles WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Vehículo eliminado exitosamente']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar vehículo']);
        }
    }

    // ========================================
    // MÉTODOS API PARA BARCOS
    // ========================================

    /**
     * API: Obtener todos los barcos
     */
    public function getBoatsApi() {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $this->getAllBoats()]);
    }

    /**
     * API: Obtener barco por ID
     */
    public function getBoatApi($id) {
        header('Content-Type: application/json');
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM boats WHERE id = ?");
            $stmt->execute([$id]);
            $boat = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($boat) {
                echo json_encode(['success' => true, 'data' => $boat]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Barco no encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error del servidor']);
        }
    }

    /**
     * API: Crear barco
     */
    public function createBoatApi() {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Log de entrada
            error_log("CREATE BOAT API - Input recibido: " . json_encode($input));
            
            if (empty($input['name']) || empty($input['type'])) {
                error_log("CREATE BOAT API - Datos requeridos faltantes");
                echo json_encode(['success' => false, 'message' => 'Datos requeridos faltantes']);
                return;
            }

            $db = Database::getInstance()->getConnection();
            error_log("CREATE BOAT API - Conexión DB exitosa");
            
            // Procesar imágenes
            $images = [];
            if (!empty($input['images'])) {
                if (is_string($input['images'])) {
                    $images = json_decode($input['images'], true) ?: [];
                } elseif (is_array($input['images'])) {
                    $images = $input['images'];
                }
            }
            
            // Si no hay imágenes, usar imagen por defecto
            if (empty($images)) {
                $images = ['https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800'];
                error_log("CREATE BOAT API - Imagen por defecto asignada");
            }
            
            // Usar la primera imagen como imagen principal para compatibilidad
            $input['image'] = $images[0];
            $input['images'] = json_encode($images);
            
            $stmt = $db->prepare("
                INSERT INTO boats (id, name, type, capacity, daily_rate, image, images, description, available, location_id) 
                VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $params = [
                $input['name'],
                $input['type'],
                $input['capacity'] ?? 1,
                $input['daily_rate'] ?? 0,
                $input['image'] ?? null,
                $input['images'] ?? null,
                $input['description'] ?? null,
                $input['available'] ?? 1,
                $input['location_id'] ?? null
            ];
            
            error_log("CREATE BOAT API - Parámetros: " . json_encode($params));
            
            $result = $stmt->execute($params);
            error_log("CREATE BOAT API - Execute result: " . ($result ? 'true' : 'false'));
            
            if (!$result) {
                error_log("CREATE BOAT API - Error en execute: " . json_encode($stmt->errorInfo()));
            }

            // Obtener el ID del barco creado
            $boatId = $db->lastInsertId();
            error_log("CREATE BOAT API - Barco creado con ID: " . $boatId);

            // Manejar características si se proporcionan
            if (isset($input['features']) && !empty($input['features'])) {
                try {
                    $features = json_decode($input['features'], true);
                    if (is_array($features)) {
                        $stmt = $db->prepare("INSERT INTO entity_features (entity_type, entity_id, feature_id) VALUES (?, ?, ?)");
                        foreach ($features as $featureName) {
                            // Obtener feature_id por nombre
                            $featureStmt = $db->prepare("SELECT id FROM features WHERE name = ? AND category = 'boat'");
                            $featureStmt->execute([$featureName]);
                            $feature = $featureStmt->fetch(PDO::FETCH_ASSOC);
                            if ($feature) {
                                $stmt->execute(['boat', $boatId, $feature['id']]);
                            }
                        }
                        error_log("CREATE BOAT API - Características asignadas: " . json_encode($features));
                    }
                } catch (Exception $e) {
                    error_log("CREATE BOAT API - Error asignando características: " . $e->getMessage());
                }
            }

            echo json_encode(['success' => true, 'message' => 'Barco creado exitosamente', 'boat_id' => $boatId]);
            error_log("CREATE BOAT API - Barco creado exitosamente");
        } catch (Exception $e) {
            error_log("CREATE BOAT API - Exception: " . $e->getMessage());
            error_log("CREATE BOAT API - Trace: " . $e->getTraceAsString());
            echo json_encode(['success' => false, 'message' => 'Error al crear barco: ' . $e->getMessage()]);
        }
    }

    /**
     * API: Actualizar barco
     */
    public function updateBoatApi($id) {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['name']) || empty($input['type'])) {
                echo json_encode(['success' => false, 'message' => 'Datos requeridos faltantes']);
                return;
            }

            $db = Database::getInstance()->getConnection();
            
            // Procesar imágenes
            $images = [];
            if (!empty($input['images'])) {
                if (is_string($input['images'])) {
                    $images = json_decode($input['images'], true) ?: [];
                } elseif (is_array($input['images'])) {
                    $images = $input['images'];
                }
            }
            
            // Si no hay imágenes, usar imagen por defecto
            if (empty($images)) {
                $images = ['https://images.unsplash.com/photo-1555215695-3004980ad54e?w=800'];
            }
            
            // Usar la primera imagen como imagen principal para compatibilidad
            $input['image'] = $images[0];
            $input['images'] = json_encode($images);
            
            $stmt = $db->prepare("
                UPDATE boats SET 
                name = ?, type = ?, capacity = ?, daily_rate = ?, 
                image = ?, images = ?, description = ?, available = ?, location_id = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $input['name'],
                $input['type'],
                $input['capacity'] ?? 1,
                $input['daily_rate'] ?? 0,
                $input['image'],
                $input['images'],
                $input['description'] ?? null,
                $input['available'] ?? 1,
                $input['location_id'] ?? null,
                $id
            ]);

            // Actualizar características en entity_features
            if (isset($input['features'])) {
                try {
                    // Eliminar características existentes
                    $stmt = $db->prepare("DELETE FROM entity_features WHERE entity_type = 'boat' AND entity_id = ?");
                    $stmt->execute([$id]);
                    
                    // Insertar nuevas características
                    if (!empty($input['features'])) {
                        $features = json_decode($input['features'], true);
                        if (is_array($features)) {
                            $stmt = $db->prepare("INSERT INTO entity_features (entity_type, entity_id, feature_id) VALUES (?, ?, ?)");
                            foreach ($features as $featureName) {
                                // Obtener feature_id por nombre
                                $featureStmt = $db->prepare("SELECT id FROM features WHERE name = ? AND category = 'boat'");
                                $featureStmt->execute([$featureName]);
                                $feature = $featureStmt->fetch(PDO::FETCH_ASSOC);
                                if ($feature) {
                                    $stmt->execute(['boat', $id, $feature['id']]);
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log("Error actualizando características de barco: " . $e->getMessage());
                }
            }

            echo json_encode(['success' => true, 'message' => 'Barco actualizado exitosamente']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar barco: ' . $e->getMessage()]);
        }
    }

    /**
     * API: Eliminar barco
     */
    public function deleteBoatApi($id) {
        header('Content-Type: application/json');
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM boats WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Barco eliminado exitosamente']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar barco']);
        }
    }

    // ========================================
    // MÉTODOS API PARA TRASLADOS
    // ========================================

    /**
     * API: Obtener todos los traslados
     */
    public function getTransfersApi() {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $this->getAllTransfers()]);
    }

    /**
     * API: Obtener traslado por ID
     */
    public function getTransferApi($id) {
        header('Content-Type: application/json');
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM transfers WHERE id = ?");
            $stmt->execute([$id]);
            $transfer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($transfer) {
                // Obtener características
                $stmt = $db->prepare("
                    SELECT f.name FROM features f 
                    INNER JOIN entity_features ef ON f.id = ef.feature_id 
                    WHERE ef.entity_type = 'transfer' AND ef.entity_id = ?
                ");
                $stmt->execute([$id]);
                $features = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                $transfer['features'] = $features;
                
                echo json_encode(['success' => true, 'data' => $transfer]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Traslado no encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error del servidor']);
        }
    }

    /**
     * API: Crear traslado
     */
    public function createTransferApi() {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['name']) || empty($input['type'])) {
                echo json_encode(['success' => false, 'message' => 'Datos requeridos faltantes']);
                return;
            }

            $db = Database::getInstance()->getConnection();
            
            // Procesar imágenes
            $images = [];
            if (!empty($input['images'])) {
                if (is_string($input['images'])) {
                    $images = json_decode($input['images'], true) ?: [];
                } elseif (is_array($input['images'])) {
                    $images = $input['images'];
                }
            }
            
            // Si no hay imágenes, usar imagen por defecto
            if (empty($images)) {
                $images = ['https://images.unsplash.com/photo-1570125909232-eb263c188f7e?w=800'];
            }
            
            // Usar la primera imagen como imagen principal para compatibilidad
            $input['image'] = $images[0];
            $input['images'] = json_encode($images);
            
            $stmt = $db->prepare("
                INSERT INTO transfers (name, type, capacity, price, image, images, description, available, location_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $input['name'],
                $input['type'],
                $input['capacity'] ?? 1,
                $input['price'] ?? 0,
                $input['image'] ?? null,
                $input['images'] ?? null,
                $input['description'] ?? null,
                $input['available'] ?? 1,
                $input['location_id'] ?? null
            ]);
            
            // Obtener el ID del transfer creado
            $transferId = $db->lastInsertId();
            
            // Manejar características si se proporcionan
            if (isset($input['features']) && !empty($input['features'])) {
                try {
                    $features = json_decode($input['features'], true);
                    if (is_array($features)) {
                        $stmt = $db->prepare("INSERT INTO entity_features (entity_type, entity_id, feature_id) VALUES (?, ?, ?)");
                        foreach ($features as $featureName) {
                            // Obtener feature_id por nombre
                            $featureStmt = $db->prepare("SELECT id FROM features WHERE name = ? AND category = 'transfer'");
                            $featureStmt->execute([$featureName]);
                            $feature = $featureStmt->fetch(PDO::FETCH_ASSOC);
                            if ($feature) {
                                $stmt->execute(['transfer', $transferId, $feature['id']]);
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log("Error asignando características: " . $e->getMessage());
                }
            }

            echo json_encode(['success' => true, 'message' => 'Traslado creado exitosamente']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al crear traslado']);
        }
    }

    /**
     * API: Actualizar traslado
     */
    public function updateTransferApi($id) {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['name']) || empty($input['type'])) {
                echo json_encode(['success' => false, 'message' => 'Datos requeridos faltantes']);
                return;
            }

            $db = Database::getInstance()->getConnection();
            
            // Procesar imágenes
            $images = [];
            if (!empty($input['images'])) {
                if (is_string($input['images'])) {
                    $images = json_decode($input['images'], true) ?: [];
                } elseif (is_array($input['images'])) {
                    $images = $input['images'];
                }
            }
            
            // Si no hay imágenes, usar imagen por defecto
            if (empty($images)) {
                $images = ['https://images.unsplash.com/photo-1555215695-3004980ad54e?w=800'];
            }
            
            // Usar la primera imagen como imagen principal para compatibilidad
            $input['image'] = $images[0];
            $input['images'] = json_encode($images);
            
            $stmt = $db->prepare("
                UPDATE transfers SET 
                name = ?, type = ?, capacity = ?, price = ?, image = ?, images = ?, description = ?, available = ?, location_id = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $input['name'],
                $input['type'],
                $input['capacity'] ?? 1,
                $input['price'] ?? 0,
                $input['image'],
                $input['images'],
                $input['description'] ?? null,
                $input['available'] ?? 1,
                $input['location_id'] ?? null,
                $id
            ]);
            
            // Actualizar características
            if (isset($input['features'])) {
                try {
                    // Eliminar características existentes
                    $stmt = $db->prepare("DELETE FROM entity_features WHERE entity_type = 'transfer' AND entity_id = ?");
                    $stmt->execute([$id]);
                    
                    // Agregar nuevas características
                    $features = json_decode($input['features'], true);
                    if (is_array($features) && !empty($features)) {
                        $stmt = $db->prepare("INSERT INTO entity_features (entity_type, entity_id, feature_id) VALUES (?, ?, ?)");
                        foreach ($features as $featureName) {
                            // Obtener feature_id por nombre
                            $featureStmt = $db->prepare("SELECT id FROM features WHERE name = ? AND category = 'transfer'");
                            $featureStmt->execute([$featureName]);
                            $feature = $featureStmt->fetch(PDO::FETCH_ASSOC);
                            if ($feature) {
                                $stmt->execute(['transfer', $id, $feature['id']]);
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log("Error actualizando características: " . $e->getMessage());
                }
            }

            echo json_encode(['success' => true, 'message' => 'Traslado actualizado exitosamente']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar traslado']);
        }
    }

    /**
     * API: Eliminar traslado
     */
    public function deleteTransferApi($id) {
        header('Content-Type: application/json');
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM transfers WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Traslado eliminado exitosamente']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar traslado']);
        }
    }

    // ========================================
    // APIs para Reservas
    // ========================================

    /**
     * API: Obtener todas las reservas
     */
    public function getReservationsApi() {
        header('Content-Type: application/json');
        try {
            $q = $_GET['q'] ?? '';
            $type = $_GET['type'] ?? '';
            $status = $_GET['status'] ?? '';

            $reservations = Reservation::getAll();

            // Filtrado en memoria (suficiente para admin); si es grande, mover a SQL
            $filtered = array_values(array_filter($reservations, function($r) use ($q, $type, $status) {
                $ok = true;
                if ($q !== '') {
                    $haystack = strtolower(($r['user_name'] ?? '') . ' ' . ($r['user_email'] ?? '') . ' ' . ($r['entity_name'] ?? '') . ' ' . ($r['id'] ?? ''));
                    $ok = $ok && (strpos($haystack, strtolower($q)) !== false);
                }
                if ($type !== '') {
                    $ok = $ok && (isset($r['entity_type']) && strtolower($r['entity_type']) === strtolower($type));
                }
                if ($status !== '') {
                    $ok = $ok && (isset($r['status']) && strtolower($r['status']) === strtolower($status));
                }
                return $ok;
            }));

            echo json_encode(['success' => true, 'data' => $filtered]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al obtener reservas: ' . $e->getMessage()]);
        }
    }

    /**
     * API: Obtener reserva específica
     */
    public function getReservationApi($id) {
        header('Content-Type: application/json');
        try {
            $reservation = Reservation::getById($id);
            if ($reservation) {
                echo json_encode(['success' => true, 'data' => $reservation]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Reserva no encontrada']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al obtener reserva: ' . $e->getMessage()]);
        }
    }
    
    /**
     * API: Crear nueva reserva
     */
    public function createReservationApi() {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['user_id']) || empty($input['entity_type']) || empty($input['entity_id'])) {
                echo json_encode(['success' => false, 'message' => 'Datos requeridos faltantes']);
                return;
            }
            
            $reservationId = Reservation::create($input);
            echo json_encode(['success' => true, 'message' => 'Reserva creada exitosamente', 'id' => $reservationId]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al crear reserva: ' . $e->getMessage()]);
        }
    }
    
    /**
     * API: Actualizar reserva
     */
    public function updateReservationApi($id) {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $success = Reservation::update($id, $input);
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Reserva actualizada exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo actualizar la reserva']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar reserva: ' . $e->getMessage()]);
        }
    }
    
    /**
     * API: Eliminar reserva
     */
    public function deleteReservationApi($id) {
        header('Content-Type: application/json');
        try {
            $success = Reservation::delete($id);
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Reserva eliminada exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo eliminar la reserva']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar reserva: ' . $e->getMessage()]);
        }
    }
    
    /**
     * API: Verificar disponibilidad
     */
    public function checkAvailabilityApi() {
        header('Content-Type: application/json');
        try {
            $entityType = $_GET['entity_type'] ?? '';
            $entityId = $_GET['entity_id'] ?? '';
            $pickupDate = $_GET['pickup_date'] ?? '';
            $returnDate = $_GET['return_date'] ?? '';
            $excludeId = $_GET['exclude_id'] ?? null;
            
            if (empty($entityType) || empty($entityId) || empty($pickupDate) || empty($returnDate)) {
                echo json_encode(['success' => false, 'message' => 'Parámetros requeridos faltantes']);
                return;
            }
            
            $available = Reservation::checkAvailability($entityType, $entityId, $pickupDate, $returnDate, $excludeId);
            echo json_encode(['success' => true, 'available' => $available]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al verificar disponibilidad: ' . $e->getMessage()]);
        }
    }
    
    /**
     * API: Obtener estadísticas de reservas
     */
    public function getReservationStatsApi() {
        header('Content-Type: application/json');
        try {
            $stats = Reservation::getStats();
            echo json_encode(['success' => true, 'data' => $stats]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al obtener estadísticas: ' . $e->getMessage()]);
        }
    }

    // ========================================
    // MÉTODOS API PARA UBICACIONES
    // ========================================

    /**
     * API: Obtener todas las ubicaciones
     */
    public function getLocationsApi() {
        header('Content-Type: application/json');
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM locations ORDER BY name");
            $stmt->execute();
            $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $locations]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al obtener ubicaciones']);
        }
    }

    /**
     * API: Crear nueva ubicación
     */
    public function createLocationApi() {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['name']) || empty($input['address'])) {
                echo json_encode(['success' => false, 'message' => 'Nombre y dirección son requeridos']);
                return;
            }

            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO locations (id, name, address, latitude, longitude) 
                VALUES (UUID(), ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $input['name'],
                $input['address'],
                $input['latitude'] ?? null,
                $input['longitude'] ?? null
            ]);

            $locationId = $db->lastInsertId();
            echo json_encode(['success' => true, 'message' => 'Ubicación creada exitosamente', 'id' => $locationId]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al crear ubicación: ' . $e->getMessage()]);
        }
    }
    
    // ========================================
    // MÉTODOS API PARA TIPOS
    // ========================================
    
    /**
     * API: Obtener tipos por categoría
     */
    public function getTypesApi() {
        header('Content-Type: application/json');
        try {
            $db = Database::getInstance()->getConnection();
            $category = $_GET['category'] ?? 'vehicle';
            
            $stmt = $db->prepare("SELECT * FROM types WHERE category = ? AND active = 1 ORDER BY name");
            $stmt->execute([$category]);
            $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $types]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al obtener tipos']);
        }
    }
    
    /**
     * API: Crear nuevo tipo
     */
    public function createTypeApi() {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['name']) || empty($input['category'])) {
                echo json_encode(['success' => false, 'message' => 'Nombre y categoría son requeridos']);
                return;
            }

            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO types (id, name, category) 
                VALUES (UUID(), ?, ?)
            ");
            
            $stmt->execute([
                $input['name'],
                $input['category']
            ]);

            $typeId = $db->lastInsertId();
            echo json_encode(['success' => true, 'message' => 'Tipo creado exitosamente', 'id' => $typeId]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al crear tipo: ' . $e->getMessage()]);
        }
    }

    /**
     * API: Actualizar tipo
     */
    public function updateTypeApi($id) {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) { echo json_encode(['success' => false, 'message' => 'Datos vacíos']); return; }
            $db = Database::getInstance()->getConnection();
            $fields = [];
            $params = [];
            if (isset($input['name']) && $input['name'] !== '') { $fields[] = 'name = ?'; $params[] = $input['name']; }
            if (isset($input['active'])) { $fields[] = 'active = ?'; $params[] = (int)$input['active']; }
            if (empty($fields)) { echo json_encode(['success' => false, 'message' => 'Nada para actualizar']); return; }
            $params[] = $id;
            $sql = 'UPDATE types SET ' . implode(', ', $fields) . ' WHERE id = ?';
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true, 'message' => 'Tipo actualizado']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar tipo']);
        }
    }

    /**
     * API: Eliminar tipo
     */
    public function deleteTypeApi($id) {
        header('Content-Type: application/json');
        try {
            $db = Database::getInstance()->getConnection();
            // Obtener nombre y categoría del tipo
            $get = $db->prepare('SELECT name, category FROM types WHERE id = ?');
            $get->execute([$id]);
            $type = $get->fetch(PDO::FETCH_ASSOC);
            if (!$type) { echo json_encode(['success' => false, 'message' => 'Tipo no encontrado']); return; }
            $name = $type['name'];
            $category = $type['category'];
            // Verificar uso según categoría
            if ($category === 'vehicle') {
                $check = $db->prepare('SELECT COUNT(*) FROM vehicles WHERE category = ?');
                $check->execute([$name]);
            } elseif ($category === 'boat') {
                $check = $db->prepare('SELECT COUNT(*) FROM boats WHERE type = ?');
                $check->execute([$name]);
            } elseif ($category === 'transfer') {
                $check = $db->prepare('SELECT COUNT(*) FROM transfers WHERE type = ?');
                $check->execute([$name]);
            } else {
                $check = null;
            }
            if ($check) {
                $inUse = (int)$check->fetchColumn();
                if ($inUse > 0) {
                    echo json_encode(['success' => false, 'message' => 'No se puede eliminar: el tipo está en uso']);
                    return;
                }
            }
            $stmt = $db->prepare('DELETE FROM types WHERE id = ?');
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Tipo eliminado']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar tipo']);
        }
    }

    // ========================================
    // MÉTODOS API PARA CARACTERÍSTICAS
    // ========================================
    
    /**
     * API: Obtener todas las características
     */
    public function getFeaturesApi() {
        header('Content-Type: application/json');
        try {
            $db = Database::getInstance()->getConnection();
            $category = $_GET['category'] ?? 'vehicle';
            
            $stmt = $db->prepare("SELECT * FROM features WHERE category = ? AND active = 1 ORDER BY name");
            $stmt->execute([$category]);
            $features = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $features]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al obtener características']);
        }
    }
    
    /**
     * API: Crear nueva característica
     */
    public function createFeatureApi() {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['name']) || empty($input['category'])) {
                echo json_encode(['success' => false, 'message' => 'Nombre y categoría son requeridos']);
                return;
            }

            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO features (id, name, category, icon) 
                VALUES (UUID(), ?, ?, ?)
            ");
            
            $stmt->execute([
                $input['name'],
                $input['category'],
                $input['icon'] ?? 'fas fa-check'
            ]);

            $featureId = $db->lastInsertId();
            echo json_encode(['success' => true, 'message' => 'Característica creada exitosamente', 'id' => $featureId]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al crear característica: ' . $e->getMessage()]);
        }
    }

    /**
     * API: Actualizar característica
     */
    public function updateFeatureApi($id) {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) { echo json_encode(['success' => false, 'message' => 'Datos vacíos']); return; }
            $db = Database::getInstance()->getConnection();
            $fields = [];
            $params = [];
            if (isset($input['name']) && $input['name'] !== '') { $fields[] = 'name = ?'; $params[] = $input['name']; }
            if (isset($input['icon']) && $input['icon'] !== '') { $fields[] = 'icon = ?'; $params[] = $input['icon']; }
            if (isset($input['active'])) { $fields[] = 'active = ?'; $params[] = (int)$input['active']; }
            if (empty($fields)) { echo json_encode(['success' => false, 'message' => 'Nada para actualizar']); return; }
            $params[] = $id;
            $sql = 'UPDATE features SET ' . implode(', ', $fields) . ' WHERE id = ?';
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true, 'message' => 'Característica actualizada']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar característica']);
        }
    }

    /**
     * API: Eliminar característica
     */
    public function deleteFeatureApi($id) {
        header('Content-Type: application/json');
        try {
            $db = Database::getInstance()->getConnection();
            // Verificar uso en entity_features
            $check = $db->prepare('SELECT COUNT(*) FROM entity_features WHERE feature_id = ?');
            $check->execute([$id]);
            $inUse = (int)$check->fetchColumn();
            if ($inUse > 0) {
                echo json_encode(['success' => false, 'message' => 'No se puede eliminar: la característica está en uso']);
                return;
            }
            $stmt = $db->prepare('DELETE FROM features WHERE id = ?');
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Característica eliminada']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar característica']);
        }
    }

    /**
     * API: Obtener categorías de servicio configurables (vehicle/boat/transfer/general)
     */
    public function getServiceCategoriesApi() {
        header('Content-Type: application/json');
        try {
            $allowed = ['vehicle','boat','transfer','general'];
            $labels = [
                'vehicle' => 'Vehículo',
                'boat'    => 'Barco',
                'transfer'=> 'Traslado',
                'general' => 'General'
            ];

            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT `value` FROM system_config WHERE `key` = 'service_categories' LIMIT 1");
            $categories = [];
            if ($stmt->execute()) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row && !empty($row['value'])) {
                    $cfg = json_decode($row['value'], true);
                    if (is_array($cfg)) {
                        foreach ($cfg as $item) {
                            $key = strtolower(trim((string)$item));
                            if (in_array($key, $allowed, true)) {
                                $categories[] = $key;
                            }
                        }
                    }
                }
            }
            if (empty($categories)) {
                $categories = $allowed;
            }
            $data = array_map(function($k) use ($labels) {
                return [ 'key' => $k, 'label' => $labels[$k] ?? ucfirst($k) ];
            }, $categories);
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al obtener categorías']);
        }
    }

    /**
     * Llamar al endpoint de limpieza de imágenes
     */
    private function callCleanupImages($entityType, $images) {
        try {
            $data = [
                'entity_type' => $entityType,
                'images' => $images
            ];
            
            $options = [
                'http' => [
                    'header' => "Content-type: application/json\r\n",
                    'method' => 'POST',
                    'content' => json_encode($data)
                ]
            ];
            
            $context = stream_context_create($options);
            $result = file_get_contents(APP_URL . '/api/cleanup-images.php', false, $context);
            
            if ($result !== false) {
                $response = json_decode($result, true);
                if ($response && $response['success']) {
                    error_log("Imágenes eliminadas: {$response['deleted_count']}");
                }
            }
        } catch (Exception $e) {
            error_log("Error llamando cleanup-images: " . $e->getMessage());
        }
    }

    /**
     * Eliminar imágenes de vehículo del servidor
     */
    private function deleteVehicleImages($vehicle) {
        try {
            $images = [];
            
            // Agregar imagen principal si existe y no es URL externa
            if (!empty($vehicle['image']) && !filter_var($vehicle['image'], FILTER_VALIDATE_URL)) {
                $images[] = $vehicle['image'];
            }
            
            // Agregar imágenes múltiples si existen
            if (!empty($vehicle['images'])) {
                $multipleImages = json_decode($vehicle['images'], true);
                if (is_array($multipleImages)) {
                    $images = array_merge($images, $multipleImages);
                }
            }
            
            // Llamar al endpoint de limpieza si hay imágenes
            if (!empty($images)) {
                $this->callCleanupImages('vehicle', $images);
            }
        } catch (Exception $e) {
            error_log("Error al eliminar imágenes del vehículo: " . $e->getMessage());
        }
    }

    /**
     * Eliminar imágenes de barco del servidor
     */
    private function deleteBoatImages($boat) {
        try {
            $images = [];
            
            // Agregar imagen principal si existe y no es URL externa
            if (!empty($boat['image']) && !filter_var($boat['image'], FILTER_VALIDATE_URL)) {
                $images[] = $boat['image'];
            }
            
            // Agregar imágenes múltiples si existen
            if (!empty($boat['images'])) {
                $multipleImages = json_decode($boat['images'], true);
                if (is_array($multipleImages)) {
                    $images = array_merge($images, $multipleImages);
                }
            }
            
            // Llamar al endpoint de limpieza si hay imágenes
            if (!empty($images)) {
                $this->callCleanupImages('boat', $images);
            }
        } catch (Exception $e) {
            error_log("Error al eliminar imágenes del barco: " . $e->getMessage());
        }
    }

    /**
     * Eliminar imágenes de transfer del servidor
     */
    private function deleteTransferImages($transfer) {
        try {
            $images = [];
            
            // Agregar imagen principal si existe y no es URL externa
            if (!empty($transfer['image']) && !filter_var($transfer['image'], FILTER_VALIDATE_URL)) {
                $images[] = $transfer['image'];
            }
            
            // Agregar imágenes múltiples si existen
            if (!empty($transfer['images'])) {
                $multipleImages = json_decode($transfer['images'], true);
                if (is_array($multipleImages)) {
                    $images = array_merge($images, $multipleImages);
                }
            }
            
            // Llamar al endpoint de limpieza si hay imágenes
            if (!empty($images)) {
                $this->callCleanupImages('transfer', $images);
            }
        } catch (Exception $e) {
            error_log("Error al eliminar imágenes del transfer: " . $e->getMessage());
        }
    }
}
?> 