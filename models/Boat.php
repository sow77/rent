<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

class Boat {
    private $db;
    public $id;
    public $name;
    public $type;
    public $capacity;
    public $daily_rate;
    public $image;
    public $images;
    public $description;
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
            $this->daily_rate = $data['daily_rate'] ?? null;
            $this->image = $data['image'] ?? null;
            $this->images = $data['images'] ?? null;
            $this->description = $data['description'] ?? null;
            $this->available = $data['available'] ?? true;
            $this->location_id = $data['location_id'] ?? null;
            $this->location_name = $data['location_name'] ?? null;
            $this->created_at = $data['created_at'] ?? null;
            $this->updated_at = $data['updated_at'] ?? null;
        }
    }
    
    public function getById($id) {
        $sql = "SELECT b.*, 
                COALESCE(t.name, b.name) as name,
                COALESCE(t.description, b.description) as description,
                l.name as location_name
                FROM boats b
                LEFT JOIN translations t ON t.entity_type = 'boat' 
                    AND t.entity_id = b.id 
                    AND t.language = ?
                LEFT JOIN locations l ON b.location_id = l.id
                WHERE b.id = ? AND b.available = 1";
        
        $result = $this->db->query($sql, [I18n::getCurrentLang(), $id]);
        if ($result) {
            $boatData = $result[0];
            $boatData['location_name'] = $boatData['location_name'] ?? null;
            return new Boat($boatData);
        }
        return null;
    }

    public function getAll() {
        $sql = "SELECT b.*, 
                COALESCE(t.name, b.name) as name,
                COALESCE(t.description, b.description) as description
                FROM boats b
                LEFT JOIN translations t ON t.entity_type = 'boat' 
                    AND t.entity_id = b.id 
                    AND t.language = ?
                WHERE b.available = 1";
        
        $result = $this->db->query($sql, [I18n::getCurrentLang()]);
        $boats = [];
        foreach ($result as $boatData) {
            $boats[] = new Boat($boatData);
        }
        return $boats;
    }
    
    public function getFeatured($limit = 3) {
        $sql = "SELECT b.*, 
                COALESCE(t.name, b.name) as name,
                COALESCE(t.description, b.description) as description
                FROM boats b
                LEFT JOIN translations t ON t.entity_type = 'boat' 
                    AND t.entity_id = b.id 
                    AND t.language = ?
                WHERE b.available = 1
                ORDER BY RAND()
                LIMIT ?";
        
        $result = $this->db->query($sql, [I18n::getCurrentLang(), $limit]);
        $boats = [];
        foreach ($result as $boatData) {
            $boats[] = new Boat($boatData);
        }
        return $boats;
    }

    public function save() {
        if ($this->id) {
            // Update
            $sql = "UPDATE boats SET 
                    name = ?, 
                    type = ?, 
                    capacity = ?, 
                    daily_rate = ?, 
                    image = ?, 
                    description = ?, 
                     
                    available = ?, 
                    location_id = ? 
                    WHERE id = ?";
            
            return $this->db->execute($sql, [
                $this->name,
                $this->type,
                $this->capacity,
                $this->daily_rate,
                $this->image,
                $this->description,
                $this->available ? 1 : 0,
                $this->location_id,
                $this->id
            ]);
        } else {
            // Insert
            $sql = "INSERT INTO boats (name, type, capacity, daily_rate, image, description, available, location_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            return $this->db->execute($sql, [
                $this->name,
                $this->type,
                $this->capacity,
                $this->daily_rate,
                $this->image,
                $this->description,
                $this->available ? 1 : 0,
                $this->location_id
            ]);
    }
    }

    // public function delete() {
    //     if (!$this->id) {
    //         return false;
    //     }
        
    //     $sql = "UPDATE boats SET available = 0 WHERE id = ?";
    //     return $this->db->execute($sql, [$this->id]);
    // }

    public function getId() {
        return $this->id;
    }

    public function getName() {
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

    public function getDailyRate() {
        return $this->daily_rate;
    }

    public function getImage() {
        if (empty($this->image)) {
            return APP_URL . '/public/img/boat-placeholder.jpg';
        }
        return strpos($this->image, 'http') === 0 ? $this->image : APP_URL . '/public/img/boats/' . $this->image;
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
        if (is_array($this->description)) {
            return $this->description[0] ?? 'Sin descripción';
        }
        return $this->description ?? 'Sin descripción';
    }

    public function getFeatures() {
        if (!$this->id) return [];
        
        $sql = "SELECT f.name, f.icon FROM features f 
                INNER JOIN entity_features ef ON f.id = ef.feature_id 
                WHERE ef.entity_type = 'boat' AND ef.entity_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    public function getAvailable() {
        return $this->available;
    }

    public function getLocationId() {
        return $this->location_id;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    public function getUpdatedAt() {
        return $this->updated_at;
    }

    public function search($term = '') {
        try {
            error_log("Buscando barcos con término: " . $term);
            
            $sql = "SELECT b.*, 
                    COALESCE(t.name, b.name) as name,
                    COALESCE(t.description, b.description) as description,
                    
                    l.name as location_name
                    FROM boats b
                    LEFT JOIN translations t ON t.entity_type = 'boat' 
                        AND t.entity_id = b.id 
                        AND t.language = ?
                    LEFT JOIN locations l ON b.location_id = l.id
                    WHERE b.available = 1";
            $params = [I18n::getCurrentLang()];

            if (!empty($term)) {
                $sql .= " AND (b.name LIKE ? OR b.description LIKE ? OR b.type LIKE ?)";
                $searchTerm = "%{$term}%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
            }

            $sql .= " ORDER BY b.name ASC";
            
            error_log("SQL Query: " . $sql);
            error_log("Params: " . print_r($params, true));

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Resultados encontrados: " . count($results));

            return array_map(function($row) {
                return new Boat($row);
            }, $results);
        } catch (PDOException $e) {
            error_log("Error en Boat::search: " . $e->getMessage());
            return [];
        }
    }

    public function getByCategory($category) {
        try {
            error_log("Buscando barcos por categoría: " . $category);
            
            $sql = "SELECT b.*, 
                    COALESCE(t.name, b.name) as name,
                    COALESCE(t.description, b.description) as description,
                    
                    l.name as location_name
                    FROM boats b
                    LEFT JOIN translations t ON t.entity_type = 'boat' 
                        AND t.entity_id = b.id 
                        AND t.language = ?
                    LEFT JOIN locations l ON b.location_id = l.id
                    WHERE b.available = 1";
            $params = [I18n::getCurrentLang()];

            if ($category !== 'all') {
                $sql .= " AND LOWER(b.type) = LOWER(?)";
                $params[] = $category;
            }

            $sql .= " ORDER BY b.name ASC";
            
            error_log("SQL Query: " . $sql);
            error_log("Params: " . print_r($params, true));

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Resultados encontrados: " . count($results));
            if (count($results) > 0) {
                error_log("Primer resultado: " . print_r($results[0], true));
            }

            return array_map(function($row) {
                return new Boat($row);
            }, $results);
        } catch (PDOException $e) {
            error_log("Error en Boat::getByCategory: " . $e->getMessage());
            return [];
        }
    }

    public function count() {
        $sql = "SELECT COUNT(*) as total FROM boats WHERE available = 1";
        $result = $this->db->query($sql);
        return $result[0]['total'];
    }

    public function create($data) {
        $sql = "INSERT INTO boats (name, type, capacity, daily_rate, image, description,  available, location_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->execute($sql, [
            $data['name'],
            $data['type'],
            $data['capacity'] ?? null,
            $data['price'],
            $data['image'] ?? null,
            $data['description'],
            json_encode($data['features'] ?? []),
            $data['status'] === 'available' ? 1 : 0,
            $data['location_id'] ?? null
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE boats SET 
                name = ?, 
                type = ?, 
                capacity = ?, 
                daily_rate = ?, 
                image = ?, 
                description = ?, 
                 
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
            $data['status'] === 'available' ? 1 : 0,
            $data['location_id'] ?? null,
            $id
        ]);
    }

    public function delete($id) {
        $sql = "UPDATE boats SET available = 0 WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
}
?>