<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Boat.php';

class BoatController {
    private $boatModel;

    public function __construct() {
        $this->boatModel = new Boat();
    }

    // Mostrar todos los barcos
    public function index() {
        $boats = $this->boatModel->getAll();
        
        // Convertir objetos a arrays para la vista
        $boats = array_map(function($boat) {
            $features = $boat->getFeatures();
            return [
                'id' => $boat->getId(),
                'name' => $boat->getName(),
                'description' => $boat->getDescription(),
                'daily_rate' => $boat->getDailyRate(),
                'image' => $boat->getImage(),
                'capacity' => $boat->getCapacity(),
                'type' => $boat->getType(),
                'features' => is_string($features) ? json_decode($features, true) : $features
            ];
        }, $boats);
        
        require_once __DIR__ . '/../views/boats/index.php';
    }

    // Mostrar un barco específico
    public function show($id) {
        $boat = $this->boatModel->getById($id);
        
        if (!$boat) {
            header('Location: ' . APP_URL . '/boats');
            exit;
        }
        
        require_once __DIR__ . '/../views/boats/show.php';
    }

    // Obtener barcos destacados
    public function getFeatured($limit = 3) {
        return $this->boatModel->getFeatured($limit);
    }

    public function search() {
        $term = $_REQUEST['term'] ?? '';
        $category = $_REQUEST['category'] ?? 'all';
        
        error_log("Búsqueda de barcos - Categoría original: " . $category);
        
        // Mapeo de categorías en inglés a español
        $categoryMap = [
            'luxury' => 'yate',
            'sport' => 'deportivo',
            'family' => 'familiar'
        ];
        
        // Convertir la categoría si existe en el mapeo
        $category = $categoryMap[$category] ?? $category;
        error_log("Búsqueda de barcos - Categoría mapeada: " . $category);
        
        if ($category !== 'all') {
            $boats = $this->boatModel->getByCategory($category);
        } else {
            $boats = $this->boatModel->search($term);
        }
        
        error_log("Barcos encontrados: " . count($boats));
        
        // Convertir objetos a arrays para la respuesta JSON
        $boats = array_map(function($boat) {
            return [
                'id' => $boat->getId(),
                'name' => $boat->getName(),
                'type' => $boat->getType(),
                'description' => $boat->getDescription(),
                'daily_rate' => $boat->getDailyRate(),
                'image' => $boat->getImage(),
                'capacity' => $boat->getCapacity(),
                'category' => $boat->getType()
            ];
        }, $boats);
        
        // Filtrar por disponibilidad si viene una fecha
        $date = $_GET['date'] ?? '';
        if (!empty($date)) {
            $pickup = $date;
            $return = date('Y-m-d', strtotime($date . ' +1 day'));
            $boats = array_values(array_filter($boats, function($b) use ($pickup, $return) {
                return Reservation::checkAvailability('boat', $b['id'], $pickup, $return);
            }));
        }

        header('Content-Type: application/json');
        echo json_encode(['data' => $boats]);
        exit;
    }
    
    public function category() {
        $category = $_REQUEST['category'] ?? 'all';
        
        // Mapeo de categorías en inglés a español
        $categoryMap = [
            'luxury' => 'lujo',
            'sport' => 'deportivo',
            'family' => 'familiar'
        ];
        
        // Convertir la categoría si existe en el mapeo
        $category = $categoryMap[$category] ?? $category;
        
        $boats = $this->boatModel->getByCategory($category);
        
        // Convertir objetos a arrays para la respuesta JSON
        $boats = array_map(function($boat) {
            return [
                'id' => $boat->getId(),
                'name' => $boat->getName(),
                'type' => $boat->getType(),
                'description' => $boat->getDescription(),
                'daily_rate' => $boat->getDailyRate(),
                'image' => $boat->getImage(),
                'capacity' => $boat->getCapacity(),
                'category' => $boat->getType()
            ];
        }, $boats);
        
        header('Content-Type: application/json');
        echo json_encode(['data' => $boats]);
        exit;
    }
    
    public function details($id) {
        try {
            $boat = $this->boatModel->getById($id);
            
            if (!$boat) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'Barco no encontrado'
                ]);
                exit;
            }

            $features = $boat->getFeatures();
            $data = [
                'id' => $boat->getId(),
                'name' => $boat->getName(),
                'type' => $boat->getType(),
                'daily_rate' => $boat->getDailyRate(),
                'image' => $boat->getImage(),
                'images' => $boat->getImages(),
                'description' => $boat->getDescription(),
                'capacity' => $boat->getCapacity(),
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
                'error' => 'Error al obtener los detalles del barco: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    public function home() {
        $boatModel = new Boat();
        $featuredBoats = $boatModel->getFeatured();
        
        // Convertir objetos a arrays para la vista
        $featuredBoats = array_map(function($boat) {
            $features = $boat->getFeatures();
            return [
                'id' => $boat->getId(),
                'name' => $boat->getName(),
                'description' => $boat->getDescription(),
                'daily_rate' => $boat->getDailyRate(),
                'image' => $boat->getImage(),
                'capacity' => $boat->getCapacity(),
                'type' => $boat->getType(),
                'features' => array_column($features, 'name')
            ];
        }, $featuredBoats);
        
        require_once 'views/boats/home.php';
    }
}
?>