<?php
// models/Reservation.php

class Reservation {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todas las reservas (admin) o solo las del usuario actual
     */
    public static function getAll($userId = null) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT r.*, 
                       u.name as user_name, u.email as user_email,
                       l1.name as pickup_location_name,
                       l2.name as return_location_name,
                       CASE 
                           WHEN r.entity_type = 'vehicle' THEN CONCAT(v.brand, ' ', v.model)
                           WHEN r.entity_type = 'boat' THEN b.name
                           WHEN r.entity_type = 'transfer' THEN t.name
                       END as entity_name,
                       CASE 
                           WHEN r.entity_type = 'vehicle' THEN v.image
                           WHEN r.entity_type = 'boat' THEN b.image
                           WHEN r.entity_type = 'transfer' THEN t.image
                       END as entity_image
                FROM reservations r 
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN locations l1 ON r.pickup_location_id = l1.id
                LEFT JOIN locations l2 ON r.return_location_id = l2.id
                LEFT JOIN vehicles v ON r.entity_type = 'vehicle' AND r.entity_id = v.id
                LEFT JOIN boats b ON r.entity_type = 'boat' AND r.entity_id = b.id
                LEFT JOIN transfers t ON r.entity_type = 'transfer' AND r.entity_id = t.id";
        
        $params = [];
        
        // Si se especifica un usuario, filtrar por ese usuario
        if ($userId) {
            $sql .= " WHERE r.user_id = ?";
            $params[] = $userId;
        }
        
        $sql .= " ORDER BY r.created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener una reserva por su ID
     */
    public static function getById($id, $userId = null) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT r.*, 
                       u.name as user_name, u.email as user_email,
                       l1.name as pickup_location_name,
                       l2.name as return_location_name,
                       CASE 
                           WHEN r.entity_type = 'vehicle' THEN CONCAT(v.brand, ' ', v.model)
                           WHEN r.entity_type = 'boat' THEN b.name
                           WHEN r.entity_type = 'transfer' THEN t.name
                       END as entity_name,
                       CASE 
                           WHEN r.entity_type = 'vehicle' THEN v.image
                           WHEN r.entity_type = 'boat' THEN b.image
                           WHEN r.entity_type = 'transfer' THEN t.image
                       END as entity_image
                FROM reservations r 
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN locations l1 ON r.pickup_location_id = l1.id
                LEFT JOIN locations l2 ON r.return_location_id = l2.id
                LEFT JOIN vehicles v ON r.entity_type = 'vehicle' AND r.entity_id = v.id
                LEFT JOIN boats b ON r.entity_type = 'boat' AND r.entity_id = b.id
                LEFT JOIN transfers t ON r.entity_type = 'transfer' AND r.entity_id = t.id
                WHERE r.id = ?";
        
        $params = [$id];
        
        // Si se especifica un usuario, añadir restricción
        if ($userId) {
            $sql .= " AND r.user_id = ?";
            $params[] = $userId;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear una nueva reserva
     */
    public static function create($data) {
        $db = Database::getInstance()->getConnection();
        
        try {
            // Validar datos requeridos
            $required = ['user_id', 'phone', 'entity_type', 'entity_id', 'pickup_date', 'return_date'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Campo requerido faltante: $field");
                }
            }
            
            // Validar fechas
            if (strtotime($data['return_date']) <= strtotime($data['pickup_date'])) {
                throw new Exception("La fecha de devolución debe ser posterior a la fecha de recogida");
            }
            
            // Calcular costo total
            $totalCost = self::calculateTotalCost($data['entity_type'], $data['entity_id'], $data['pickup_date'], $data['return_date']);
            
            // Verificar disponibilidad
            if (!self::checkAvailability($data['entity_type'], $data['entity_id'], $data['pickup_date'], $data['return_date'])) {
                throw new Exception("El elemento no está disponible en las fechas seleccionadas");
            }
            
            $sql = "INSERT INTO reservations (
                        user_id, email, phone, entity_type, entity_id, pickup_location_id, return_location_id,
                        pickup_date, return_date, total_cost, status, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $data['user_id'],
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $data['entity_type'],
                $data['entity_id'],
                $data['pickup_location_id'] ?? null,
                $data['return_location_id'] ?? null,
            $data['pickup_date'],
                $data['return_date'],
                $totalCost,
                $data['status'] ?? 'pendiente',
                $data['notes'] ?? null
            ];
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            return $db->lastInsertId();
            
        } catch (Exception $e) {
            error_log("Error al crear reserva: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Actualizar una reserva
     */
    public static function update($id, $data, $userId = null) {
        $db = Database::getInstance()->getConnection();
        
        try {
            // Verificar que la reserva existe y el usuario tiene permisos
            $existing = self::getById($id, $userId);
            if (!$existing) {
                throw new Exception("Reserva no encontrada");
            }
            
            // Validar fechas si se proporcionan
            if (isset($data['pickup_date']) && isset($data['return_date'])) {
                if (strtotime($data['return_date']) <= strtotime($data['pickup_date'])) {
                    throw new Exception("La fecha de devolución debe ser posterior a la fecha de recogida");
                }
            }
            
            // Recalcular costo si cambian las fechas o el elemento
            if (isset($data['entity_type']) || isset($data['entity_id']) || 
                isset($data['pickup_date']) || isset($data['return_date'])) {
                
                $entityType = $data['entity_type'] ?? $existing['entity_type'];
                $entityId = $data['entity_id'] ?? $existing['entity_id'];
                $pickupDate = $data['pickup_date'] ?? $existing['pickup_date'];
                $returnDate = $data['return_date'] ?? $existing['return_date'];
                
                $data['total_cost'] = self::calculateTotalCost($entityType, $entityId, $pickupDate, $returnDate);
            }
            
            $fields = [];
            $params = [];
            
            $allowedFields = ['entity_type', 'entity_id', 'pickup_location_id', 'return_location_id',
                             'pickup_date', 'return_date', 'total_cost', 'status', 'notes'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
            
            if (empty($fields)) {
                throw new Exception("No hay campos para actualizar");
            }
            
            $params[] = $id;
            
            $sql = "UPDATE reservations SET " . implode(', ', $fields) . " WHERE id = ?";
            
            // Si se especifica un usuario, añadir restricción
            if ($userId) {
                $sql .= " AND user_id = ?";
                $params[] = $userId;
            }
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->rowCount() > 0;
            
        } catch (Exception $e) {
            error_log("Error al actualizar reserva: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Eliminar una reserva
     */
    public static function delete($id, $userId = null) {
        $db = Database::getInstance()->getConnection();
        
        try {
            $sql = "DELETE FROM reservations WHERE id = ?";
            $params = [$id];
            
            // Si se especifica un usuario, añadir restricción
            if ($userId) {
                $sql .= " AND user_id = ?";
                $params[] = $userId;
            }
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->rowCount() > 0;
            
        } catch (Exception $e) {
            error_log("Error al eliminar reserva: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Calcular el costo total de una reserva
     */
    private static function calculateTotalCost($entityType, $entityId, $pickupDate, $returnDate) {
        $db = Database::getInstance()->getConnection();
        
        $days = (strtotime($returnDate) - strtotime($pickupDate)) / (60 * 60 * 24);
        
        switch ($entityType) {
            case 'vehicle':
                $stmt = $db->prepare("SELECT daily_rate FROM vehicles WHERE id = ?");
                break;
            case 'boat':
                $stmt = $db->prepare("SELECT daily_rate FROM boats WHERE id = ?");
                break;
            case 'transfer':
                $stmt = $db->prepare("SELECT price FROM transfers WHERE id = ?");
                break;
            default:
                throw new Exception("Tipo de entidad no válido");
        }
        
        $stmt->execute([$entityId]);
        $entity = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$entity) {
            throw new Exception("Entidad no encontrada");
        }
        
        $rate = $entityType === 'transfer' ? $entity['price'] : $entity['daily_rate'];
        return $days * $rate;
    }
    
    /**
     * Verificar disponibilidad de una entidad en un rango de fechas
     */
    public static function checkAvailability($entityType, $entityId, $pickupDate, $returnDate, $excludeReservationId = null) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT COUNT(*) as count FROM reservations 
                WHERE entity_type = ? AND entity_id = ? 
                AND status != 'cancelada'
                AND (
                    (pickup_date BETWEEN ? AND ?) OR
                    (return_date BETWEEN ? AND ?) OR
                    (pickup_date <= ? AND return_date >= ?)
                )";
        
        $params = [$entityType, $entityId, $pickupDate, $returnDate, $pickupDate, $returnDate, $pickupDate, $returnDate];
        
        // Excluir una reserva específica (para actualizaciones)
        if ($excludeReservationId) {
            $sql .= " AND id != ?";
            $params[] = $excludeReservationId;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] == 0;
    }
    
    /**
     * Obtener reservas por usuario
     */
    public static function getByUser($userId) {
        return self::getAll($userId);
    }
    
    /**
     * Obtener reservas por entidad
     */
    public static function getByEntity($entityType, $entityId) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT r.*, u.name as user_name, u.email as user_email
                FROM reservations r 
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.entity_type = ? AND r.entity_id = ?
                ORDER BY r.created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$entityType, $entityId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener estadísticas de reservas
     */
    public static function getStats() {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT 
                    COUNT(*) as total_reservations,
                    SUM(CASE WHEN status = 'confirmada' THEN 1 ELSE 0 END) as confirmed,
                    SUM(CASE WHEN status = 'pendiente' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'cancelada' THEN 1 ELSE 0 END) as cancelled,
                    SUM(total_cost) as total_revenue
                FROM reservations";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener reservas por ID de usuario
     */
    public static function getByUserId($userId) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT r.*, 
                       CASE 
                           WHEN r.entity_type = 'vehicle' THEN CONCAT(v.brand, ' ', v.model)
                           WHEN r.entity_type = 'boat' THEN b.name
                           WHEN r.entity_type = 'transfer' THEN t.name
                       END as entity_name,
                       CASE 
                           WHEN r.entity_type = 'vehicle' THEN v.image
                           WHEN r.entity_type = 'boat' THEN b.image
                           WHEN r.entity_type = 'transfer' THEN t.image
                       END as entity_image
                FROM reservations r
                LEFT JOIN vehicles v ON r.entity_type = 'vehicle' AND r.entity_id = v.id
                LEFT JOIN boats b ON r.entity_type = 'boat' AND r.entity_id = b.id
                LEFT JOIN transfers t ON r.entity_type = 'transfer' AND r.entity_id = t.id
                WHERE r.user_id = ?
                ORDER BY r.created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>