<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Transfer.php';

class TransferController {
    private $transferModel;

    public function __construct() {
        $this->transferModel = new Transfer();
    }

    // Mostrar todos los transfers
    public function index() {
        $transfers = $this->transferModel->getAll();
        
        // Convertir objetos a arrays para la vista
        $transfers = array_map(function($transfer) {
            $features = $transfer->getFeatures();
            return [
                'id' => $transfer->getId(),
                'name' => $transfer->getName(),
                'description' => $transfer->getDescription(),
                'price' => $transfer->getPrice(),
                'image' => $transfer->getImage(),
                'images' => $transfer->getImages(),
                'duration' => $transfer->getDuration(),
                'features' => is_string($features) ? json_decode($features, true) : $features
            ];
        }, $transfers);
        
        require_once __DIR__ . '/../views/transfers/index.php';
    }

    // Mostrar un transfer específico
    public function show($id) {
        $transfer = $this->transferModel->getById($id);
        
        if (!$transfer) {
            header('Location: ' . APP_URL . '/transfers');
            exit;
        }
        
        require_once __DIR__ . '/../views/transfers/show.php';
    }

    // Obtener transfers destacados
    public function getFeatured($limit = 3) {
        return $this->transferModel->getFeatured($limit);
    }

    public function search() {
        try {
            $term = $_GET['term'] ?? '';
            $category = $_GET['category'] ?? 'all';
            
            error_log("Buscando traslados - Término: $term, Categoría: $category");
            
            if ($category !== 'all') {
                $transfers = $this->transferModel->getByCategory($category);
            } else {
                $transfers = $this->transferModel->search($term);
            }
            
            // Convertir objetos a arrays para la respuesta JSON
            $transfers = array_map(function($transfer) {
                $features = $transfer->getFeatures();
                return [
                    'id' => $transfer->getId(),
                    'name' => $transfer->getName(),
                    'type' => $transfer->getType(),
                    'description' => $transfer->getDescription(),
                    'price' => $transfer->getPrice(),
                    'image' => $transfer->getImage(),
                    'duration' => $transfer->getDuration(),
                    'capacity' => $transfer->getCapacity(),
                    'features' => array_column($features, 'name')
                ];
            }, $transfers);

            // Filtrar por disponibilidad si viene una fecha
            $date = $_GET['date'] ?? '';
            if (!empty($date)) {
                $pickup = $date;
                $return = date('Y-m-d', strtotime($date . ' +1 day'));
                $transfers = array_values(array_filter($transfers, function($t) use ($pickup, $return) {
                    return Reservation::checkAvailability('transfer', $t['id'], $pickup, $return);
                }));
            }
            
            error_log("Traslados encontrados: " . count($transfers));
            
            header('Content-Type: application/json');
            echo json_encode(['data' => $transfers]);
        } catch (Exception $e) {
            error_log("Error en TransferController::search: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Error al buscar los traslados: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    public function category() {
        try {
            $category = $_REQUEST['category'] ?? 'all';
            error_log("Controlador - Buscando traslados por categoría: " . $category);
            
            $transfers = $this->transferModel->getByCategory($category);
            
            // Convertir objetos a arrays para la respuesta JSON
            $transfers = array_map(function($transfer) {
                $features = $transfer->getFeatures();
                return [
                    'id' => $transfer->getId(),
                    'name' => $transfer->getName(),
                    'type' => $transfer->getType(),
                    'description' => $transfer->getDescription(),
                    'price' => $transfer->getPrice(),
                    'image' => $transfer->getImage(),
                    'duration' => $transfer->getDuration(),
                    'capacity' => $transfer->getCapacity(),
                    'features' => array_column($features, 'name')
                ];
            }, $transfers);
            
            error_log("Controlador - Traslados encontrados: " . count($transfers));
            if (count($transfers) > 0) {
                error_log("Controlador - Primer traslado: " . print_r($transfers[0], true));
            }
            
            header('Content-Type: application/json');
            echo json_encode(['data' => $transfers]);
        } catch (Exception $e) {
            error_log("Error en TransferController::category: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener los traslados: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    public function details($id) {
        try {
            $transfer = $this->transferModel->getById($id);
            
            if (!$transfer) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'Traslado no encontrado'
                ]);
                exit;
            }

            $features = $transfer->getFeatures();
            $data = [
                'id' => $transfer->getId(),
                'name' => $transfer->getName(),
                'type' => $transfer->getType(),
                'price' => $transfer->getPrice(),
                'image' => $transfer->getImage(),
                'images' => $transfer->getImages(),
                'description' => $transfer->getDescription(),
                'capacity' => $transfer->getCapacity(),
                'duration' => $transfer->getDuration(),
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
                'error' => 'Error al obtener los detalles del traslado: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    public function home() {
        $transferModel = new Transfer();
        $featuredTransfers = $transferModel->getFeatured();
        
        // Convertir objetos a arrays para la vista
        $featuredTransfers = array_map(function($transfer) {
            $features = $transfer->getFeatures();
            return [
                'id' => $transfer->getId(),
                'name' => $transfer->getName(),
                'description' => $transfer->getDescription(),
                'price' => $transfer->getPrice(),
                'image' => $transfer->getImage(),
                'duration' => $transfer->getDuration(),
                'features' => array_column($features, 'name')
            ];
        }, $featuredTransfers);
        
        require_once 'views/transfers/home.php';
    }
}
?>