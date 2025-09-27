<?php
// controllers/VehicleController.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Vehicle.php';
require_once __DIR__ . '/../models/Boat.php';
require_once __DIR__ . '/../models/Transfer.php';

class VehicleController {
    private $vehicleModel;
    private $boatModel;
    private $transferModel;

    public function __construct() {
        $this->vehicleModel = new Vehicle();
        $this->boatModel = new Boat();
        $this->transferModel = new Transfer();
    }

    // Mostrar todos los vehículos
    public function index() {
        $vehicles = $this->vehicleModel->getAll();
        require_once __DIR__ . '/../views/vehicles/index.php';
    }
    
    public function home() {
        $featuredVehicles = array_merge(
            $this->vehicleModel->getFeaturedByCategory('lujo', 3) ?? [],
            $this->vehicleModel->getFeaturedByCategory('deportivo', 3) ?? [],
            $this->vehicleModel->getFeaturedByCategory('familiar', 3) ?? []
        );

        // Ya no necesitamos convertir los datos porque getFeaturedByCategory devuelve un array
        $data = [
            'featuredVehicles' => $featuredVehicles,
            'featuredBoats' => $this->boatModel->getFeatured(3) ?? [],
            'featuredTransfers' => $this->transferModel->getFeatured(3) ?? [],
            'baseUrl' => APP_URL
        ];
        
        require_once 'views/home.php';
    }
    // Mostrar un vehículo específico
    public function show($id) {
        $vehicle = $this->vehicleModel->getById($id);
        
        if (!$vehicle) {
            header('Location: ' . APP_URL . '/vehicles');
            exit;
        }
        
        require_once __DIR__ . '/../views/vehicles/show.php';
    }
    
    // Mostrar formulario para crear un vehículo
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $vehicle = new Vehicle($_POST);
            if ($vehicle->save()) {
                header('Location: ' . APP_URL . '/vehicles');
                exit;
            }
        }
        require_once 'views/vehicles/create.php';
    }
    
    // Mostrar formulario para editar un vehículo
    public function edit($id) {
        $vehicle = $this->vehicleModel->getById($id);
        
        if (!$vehicle) {
            header('Location: ' . APP_URL . '/vehicles');
            exit;
        }
        
        require_once 'views/vehicles/edit.php';
    }
    
    // Procesar el formulario para actualizar un vehículo
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $data['id'] = $id;
            $vehicle = new Vehicle($data);
            if ($vehicle->save()) {
                header('Location: ' . APP_URL . '/vehicles');
                exit;
            }
        }
        header('Location: ' . APP_URL . '/vehicles/edit/' . $id);
        exit;
    }
    
    // Eliminar un vehículo
    public function delete($id) {
        $vehicle = new Vehicle(['id' => $id]);
        if ($vehicle->delete()) {
            header('Location: ' . APP_URL . '/vehicles');
            exit;
        }
        header('Location: ' . APP_URL . '/vehicles');
        exit;
    }
    
    // Buscar vehículos disponibles
    public function search() {
        try {
            $term = $_REQUEST['term'] ?? '';
            $category = $_REQUEST['category'] ?? 'all';
            
            // Las categorías ya están en español, no necesitamos mapeo
            
            if ($category !== 'all') {
                $vehicles = $this->vehicleModel->getByCategory($category);
            } else {
                $vehicles = $this->vehicleModel->search($term);
            }
            
            // Convertir objetos a arrays para la respuesta JSON
            $vehicles = array_map(function($vehicle) {
                return [
                    'id' => $vehicle->getId(),
                    'brand' => $vehicle->getBrand(),
                    'model' => $vehicle->getModel(),
                    'year' => $vehicle->getYear(),
                    'description' => $vehicle->getDescription(),
                    'daily_rate' => $vehicle->getDailyRate(),
                    'image' => $vehicle->getImage(),
                    'images' => $vehicle->getImages(),
                    'category' => $vehicle->getCategory(),
                    'capacity' => $vehicle->getCapacity(),
                    'available' => $vehicle->isAvailable(),
                    'location_id' => $vehicle->getLocationId()
                ];
            }, $vehicles);

            // Filtrar por disponibilidad si viene una fecha
            $date = $_GET['date'] ?? '';
            if (!empty($date)) {
                $pickup = $date;
                // Asumir reserva de 1 día para la portada
                $return = date('Y-m-d', strtotime($date . ' +1 day'));
                $vehicles = array_values(array_filter($vehicles, function($v) use ($pickup, $return) {
                    return Reservation::checkAvailability('vehicle', $v['id'], $pickup, $return);
                }));
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $vehicles]);
            exit;
        } catch (Exception $e) {
            error_log("Error en VehicleController::search: " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
            exit;
        }
    }

    public function list() {
        $vehicles = $this->vehicleModel->getAll();
        require_once 'views/vehicles/_list.php';
        }
        
    public function details($id) {
        try {
            $vehicle = $this->vehicleModel->getById($id);
            
            if (!$vehicle) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'Vehículo no encontrado'
                ]);
                exit;
            }

            // Crear instancia del modelo para obtener características
            $vehicleModel = new Vehicle($vehicle);
            $features = $vehicleModel->getFeatures();
            
            $data = [
                'id' => $vehicle['id'],
                'brand' => $vehicle['brand'],
                'model' => $vehicle['model'],
                'year' => $vehicle['year'],
                'category' => $vehicle['category'],
                'daily_rate' => $vehicle['daily_rate'],
                'capacity' => $vehicle['capacity'] ?? 5,
                'image' => $vehicle['image'],
                'images' => $vehicle['images'] ?? '',
                'description' => $vehicle['description'],
                'features' => array_column($features, 'name')
            ];

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
            
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener los detalles del vehículo: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    public function category() {
        $category = $_REQUEST['category'] ?? 'all';
        $vehicles = $this->vehicleModel->getByCategory($category);
        
        // Convertir objetos a arrays para la respuesta JSON
        $vehicles = array_map(function($vehicle) {
            return [
                'id' => $vehicle->getId(),
                'brand' => $vehicle->getBrand(),
                'model' => $vehicle->getModel(),
                'year' => $vehicle->getYear(),
                'description' => $vehicle->getDescription(),
                'daily_rate' => $vehicle->getDailyRate(),
                'image' => $vehicle->getImage(),
                'category' => $vehicle->getCategory()
            ];
        }, $vehicles);
        
        header('Content-Type: application/json');
        echo json_encode(['data' => $vehicles]);
        exit;
    }
}
