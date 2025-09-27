<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

class Transfer {
    private $db;
    public $id;
    public $name;
    public $type;
    public $capacity;
    public $price;
    public $image;
    public $images;
    public $description;
    public $duration;
    public $available;
    public $location_id;
    public $location_name;
    public $created_at;
    public $updated_at;
    
    public function __construct($data = []) {
        $this->db = Database::getInstance();
        
        if (!empty($data)) {
        $this->id = $data['id'] ?? null;
            $this->name = $data['name'] ?? null;
            $this->type = $data['type'] ?? null;
            $this->capacity = $data['capacity'] ?? null;
            $this->price = $data['price'] ?? null;
            $this->image = $data['image'] ?? null;
            $this->images = $data['images'] ?? null;
            $this->description = $data['description'] ?? null;
            $this->duration = $data['duration'] ?? null;
        $this->available = $data['available'] ?? true;
        $this->location_id = $data['location_id'] ?? null;
            $this->location_name = $data['location_name'] ?? null;
            $this->created_at = $data['created_at'] ?? null;
            $this->updated_at = $data['updated_at'] ?? null;
        }
    }
    
    public function getById($id) {
        $sql = "SELECT t.*, 
                COALESCE(tr.name, t.name) as name,
                COALESCE(tr.description, t.description) as description,
                l.name as location_name
                FROM transfers t
                LEFT JOIN translations tr ON tr.entity_type = 'transfer' 
                    AND tr.entity_id = t.id 
                    AND tr.language = ?
                LEFT JOIN locations l ON t.location_id = l.id
                WHERE t.id = ? AND t.available = 1";
        
        $result = $this->db->query($sql, [I18n::getCurrentLang(), $id]);
        if ($result) {
            $transferData = $result[0];
            $transferData['location_name'] = $transferData['location_name'] ?? null;
            return new Transfer($transferData);
        }
        return null;
    }

    public function getAll() {
        $sql = "SELECT t.*, 
                COALESCE(tr.name, t.name) as name,
                COALESCE(tr.description, t.description) as description
                FROM transfers t
                LEFT JOIN translations tr ON tr.entity_type = 'transfer' 
                    AND tr.entity_id = t.id 
                    AND tr.language = ?
                WHERE t.available = 1";
        
        $result = $this->db->query($sql, [I18n::getCurrentLang()]);
        $transfers = [];
        foreach ($result as $transferData) {
            $transfers[] = new Transfer($transferData);
        }
        return $transfers;
    }
    
    public function getFeatured($limit = 3) {
        $sql = "SELECT t.*, 
                COALESCE(tr.name, t.name) as name,
                COALESCE(tr.description, t.description) as description
                FROM transfers t
                LEFT JOIN translations tr ON tr.entity_type = 'transfer' 
                    AND tr.entity_id = t.id 
                    AND tr.language = ?
                WHERE t.available = 1
                ORDER BY RAND()
                LIMIT ?";
        
        $result = $this->db->query($sql, [I18n::getCurrentLang(), $limit]);
        $transfers = [];
        foreach ($result as $transferData) {
            $transfers[] = new Transfer($transferData);
        }
        return $transfers;
    }

    public function search($term) {
        $sql = "SELECT t.*, 
                COALESCE(tr.name, t.name) as name,
                COALESCE(tr.description, t.description) as description
                FROM transfers t
                LEFT JOIN translations tr ON tr.entity_type = 'transfer' 
                    AND tr.entity_id = t.id 
                    AND tr.language = ?
                WHERE t.available = 1
                AND (t.name LIKE ? OR t.description LIKE ? OR t.type LIKE ?)";
        
        $searchTerm = "%{$term}%";
        $result = $this->db->query($sql, [I18n::getCurrentLang(), $searchTerm, $searchTerm, $searchTerm]);
        $transfers = [];
        foreach ($result as $transferData) {
            $transfers[] = new Transfer($transferData);
        }
        return $transfers;
    }

    public function getByCategory($category) {
        try {
            if ($category === 'all') {
                return $this->getAll();
            }

            // Buscar directamente por el tipo (ya no necesitamos mapeo)
            $sql = "SELECT t.*, 
                    COALESCE(tr.name, t.name) as name,
                    COALESCE(tr.description, t.description) as description
                    FROM transfers t
                    LEFT JOIN translations tr ON tr.entity_type = 'transfer' 
                        AND tr.entity_id = t.id 
                        AND tr.language = ?
                    WHERE t.available = 1 AND LOWER(t.type) = LOWER(?)";
            
            $params = [I18n::getCurrentLang(), $category];
            
            $result = $this->db->query($sql, $params);
            $transfers = [];
            foreach ($result as $transferData) {
                $transfers[] = new Transfer($transferData);
            }
            
            return $transfers;
        } catch (PDOException $e) {
            error_log("Error en Transfer::getByCategory: " . $e->getMessage());
            return [];
        }
    }

    public function save() {
        if ($this->id) {
            // Update
            $sql = "UPDATE transfers SET 
                    name = ?, 
                    type = ?, 
                    capacity = ?, 
                    price = ?, 
                    image = ?, 
                    description = ?, 
                    duration = ?,
                    available = ?, 
                    location_id = ? 
                    WHERE id = ?";
            
            return $this->db->execute($sql, [
                $this->name,
                $this->type,
                $this->capacity,
                $this->price,
                $this->image,
                $this->description,
                $this->duration,
                $this->available ? 1 : 0,
                $this->location_id,
                $this->id
            ]);
        } else {
            // Insert
            $sql = "INSERT INTO transfers (name, type, capacity, price, image, description, duration, available, location_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            return $this->db->execute($sql, [
                $this->name,
                $this->type,
                $this->capacity,
                $this->price,
                $this->image,
                $this->description,
                $this->duration,
                $this->available ? 1 : 0,
                $this->location_id
            ]);
        }
    }

    public function delete() {
        if (!$this->id) {
            return false;
        }
        
        $sql = "UPDATE transfers SET available = 0 WHERE id = ?";
        return $this->db->execute($sql, [$this->id]);
    }
    
    // Getters
    public function getId() {
        return $this->id;
    }

    public function getName() {
        // Asegurar que siempre devuelva un string
        if (is_array($this->name)) {
            return $this->name[0] ?? 'Sin nombre';
        }
        return $this->name ?? 'Sin nombre';
    }

    public function getType() {
        return $this->type;
    }

    public function getCapacity() {
        return $this->capacity;
    }

    public function getPrice() {
        return $this->price;
    }

    public function getImage() {
        if (empty($this->image)) {
            return APP_URL . '/public/img/transfer-placeholder.jpg';
        }
        return strpos($this->image, 'http') === 0 ? $this->image : APP_URL . '/public/img/transfers/' . $this->image;
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

    public function getDescription() {
        // Asegurar que siempre devuelva un string
        if (is_array($this->description)) {
            return $this->description[0] ?? 'Sin descripción';
        }
        return $this->description ?? 'Sin descripción';
    }

    public function getFeatures() {
        if (!$this->id) return [];
        
        $sql = "SELECT f.name, f.icon FROM features f 
                INNER JOIN entity_features ef ON f.id = ef.feature_id 
                WHERE ef.entity_type = 'transfer' AND ef.entity_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    public function getDuration() {
        return $this->duration;
    }

    public function getAvailable() {
        return $this->available;
    }

    public function getLocationId() {
        return $this->location_id;
    }

    public function getLocationName() {
        return $this->location_name;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    public function getUpdatedAt() {
        return $this->updated_at;
    }

    public function count() {
        $sql = "SELECT COUNT(*) as total FROM transfers WHERE available = 1";
        $result = $this->db->query($sql);
        return $result[0]['total'];
    }

    public function create($data) {
        $sql = "INSERT INTO transfers (name, type, capacity, price, image, description,  duration, available, location_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->execute($sql, [
            $data['name'],
            $data['type'],
            $data['capacity'] ?? null,
            $data['price'],
            $data['image'] ?? null,
            $data['description'],
            json_encode($data['features'] ?? []),
            $data['duration'] ?? null,
            $data['status'] === 'available' ? 1 : 0,
            $data['location_id'] ?? null
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE transfers SET 
                name = ?, 
                type = ?, 
                capacity = ?, 
                price = ?, 
                image = ?, 
                description = ?, 
                 
                duration = ?,
                available = ?, 
                location_id = ? 
                WHERE id = ?";
        
        return $this->db->execute($sql, [
            $data['name'],
            $data['type'],
            $data['capacity'] ?? null,
            $data['price'],
            $data['image'] ?? null,
            $data['description'],
            json_encode($data['features'] ?? []),
            $data['duration'] ?? null,
            $data['status'] === 'available' ? 1 : 0,
            $data['location_id'] ?? null,
            $id
        ]);
    }
}
?>