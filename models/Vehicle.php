<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/Location.php';

class Vehicle {
    private $db;
    private $table = 'vehicles';
    private $id;
    private $brand;
    private $model;
    private $year;
    private $category;
    private $daily_rate;
    private $image;
    private $images;
    private $description;
    private $capacity;
    private $available;
    private $location_id;
    private $location;
    
    public function __construct($vehicleData = null) {
        $this->db = Database::getInstance();
        
        if ($vehicleData) {
            $this->id = $vehicleData['id'] ?? null;
            $this->brand = $vehicleData['brand'] ?? null;
            $this->model = $vehicleData['model'] ?? null;
            $this->year = $vehicleData['year'] ?? null;
            $this->category = $vehicleData['category'] ?? null;
            $this->daily_rate = $vehicleData['daily_rate'] ?? null;
            $this->image = $vehicleData['image'] ?? null;
            $this->images = $vehicleData['images'] ?? null;
            $this->description = $vehicleData['description'] ?? null;
            $this->capacity = $vehicleData['capacity'] ?? 5;
            $this->available = $vehicleData['available'] ?? true;
            $this->location_id = $vehicleData['location_id'] ?? null;
            
            if ($this->location_id) {
                $this->location = new Location($this->location_id);
            }
        }
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getBrand() { return $this->brand; }
    public function getModel() { return $this->model; }
    public function getYear() { return $this->year; }
    public function getCategory() { return $this->category; }
    public function getDailyRate() { return $this->daily_rate; }
    public function getImage() { 
        if (empty($this->image)) {
            return APP_URL . '/public/images/no-image.png';
        }
        return $this->image;
    }
    
    public function getImages() {
        // Priorizar campo 'images' si existe y tiene datos
        if (!empty($this->images)) {
            $decoded = json_decode($this->images, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }
        
        // Fallback a imagen única
        return [$this->getImage()];
    }
    public function getDescription() { return $this->description; }
    public function getCapacity() { return $this->capacity; }
    
    public function getFeatures() {
        if (!$this->id) return [];
        
        $sql = "SELECT f.name, f.icon FROM features f 
                INNER JOIN entity_features ef ON f.id = ef.feature_id 
                WHERE ef.entity_type = 'vehicle' AND ef.entity_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }
    
    public function isAvailable() { return $this->available; }
    public function getLocationId() { return $this->location_id; }
    public function getLocation() { return $this->location; }

    public function getAll($search = '', $category = '') {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (brand LIKE ? OR model LIKE ? OR description LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if (!empty($category)) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (brand, model, year, category, daily_rate, image, description, features, available, location_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['brand'],
            $data['model'],
            $data['year'],
            $data['category'],
            $data['daily_rate'],
            $data['image'],
            $data['description'],
            $data['features'] ?? '[]',
            $data['available'] ?? 1,
            $data['location_id']
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE {$this->table} 
                SET brand = ?, model = ?, year = ?, category = ?, daily_rate = ?, description = ?, features = ?, available = ?, location_id = ?";
        $params = [
            $data['brand'],
            $data['model'],
            $data['year'],
            $data['category'],
            $data['daily_rate'],
            $data['description'],
            $data['features'] ?? '[]',
            $data['available'] ?? 1,
            $data['location_id']
        ];

        if (isset($data['image'])) {
            $sql .= ", image = ?";
            $params[] = $data['image'];
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function count() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getFeaturedByCategory($category = null, $limit = 4) {
        $sql = "SELECT * FROM {$this->table} WHERE available = 1";
        $params = [];

        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        $sql .= " ORDER BY RAND() LIMIT ?";
        $params[] = $limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCategory($category) {
        $sql = "SELECT * FROM {$this->table} WHERE category = ? AND available = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$category]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convertir arrays a objetos Vehicle
        $vehicles = [];
        foreach ($results as $data) {
            $vehicles[] = new Vehicle($data);
        }
        return $vehicles;
    }

    public function search($term) {
        $sql = "SELECT * FROM {$this->table} WHERE (brand LIKE ? OR model LIKE ? OR description LIKE ?) AND available = 1";
        $term = "%{$term}%";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$term, $term, $term]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convertir arrays a objetos Vehicle
        $vehicles = [];
        foreach ($results as $data) {
            $vehicles[] = new Vehicle($data);
        }
        return $vehicles;
    }

    public function save() {
        if ($this->id) {
            return $this->update($this->id, [
                'brand' => $this->brand,
                'model' => $this->model,
                'year' => $this->year,
                'category' => $this->category,
                'daily_rate' => $this->daily_rate,
                'image' => $this->image,
                'description' => $this->description,
                'features' => $this->features,
                'available' => $this->available,
                'location_id' => $this->location_id
            ]);
        } else {
            return $this->create([
                'brand' => $this->brand,
                'model' => $this->model,
                'year' => $this->year,
                'category' => $this->category,
                'daily_rate' => $this->daily_rate,
                'image' => $this->image,
                'description' => $this->description,
                'features' => $this->features,
                'available' => $this->available,
                'location_id' => $this->location_id
            ]);
        }
    }
}
