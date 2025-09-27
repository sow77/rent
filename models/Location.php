<?php
// models/Location.php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/User.php';

class Location {
    private $id;
    private $name;
    private $address;
    private $latitude;
    private $longitude;
    
    public function __construct($id) {
        $this->id = $id;
        $data = self::getById($id);
        if ($data) {
            $this->name = $data['name'];
            $this->address = $data['address'];
            $this->latitude = $data['latitude'];
            $this->longitude = $data['longitude'];
        }
    }
    
    public function getName() {
        return $this->name;
    }
    
    // Obtener todas las ubicaciones
    public static function getAll() {
        $sql = "SELECT * FROM locations ORDER BY name ASC";
        return executeQuery($sql);
    }
    
    // Obtener una ubicación por su ID
    public static function getById($id) {
        $sql = "SELECT * FROM locations WHERE id = ?";
        $locations = executeQuery($sql, [$id]);
        
        return !empty($locations) ? $locations[0] : null;
    }
    
    // Crear una nueva ubicación
    public static function create($data) {
        if (!User::isAdmin()) {
            return ['success' => false, 'message' => 'No tienes permisos para esta acción'];
        }
        
        $sql = "INSERT INTO locations (id, name, address, latitude, longitude) 
                VALUES (UUID(), ?, ?, ?, ?)";
        
        $result = executeQuery($sql, [
            $data['name'],
            $data['address'],
            $data['latitude'],
            $data['longitude']
        ]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Ubicación creada correctamente'];
        } else {
            return ['success' => false, 'message' => 'Error al crear la ubicación'];
        }
    }
    
    // Actualizar una ubicación existente
    public static function update($id, $data) {
        if (!User::isAdmin()) {
            return ['success' => false, 'message' => 'No tienes permisos para esta acción'];
        }
        
        $sql = "UPDATE locations SET 
                name = ?, 
                address = ?, 
                latitude = ?, 
                longitude = ? 
                WHERE id = ?";
        
        $result = executeQuery($sql, [
            $data['name'],
            $data['address'],
            $data['latitude'],
            $data['longitude'],
            $id
        ]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Ubicación actualizada correctamente'];
        } else {
            return ['success' => false, 'message' => 'Error al actualizar la ubicación'];
        }
    }
    
    // Eliminar una ubicación
    public static function delete($id) {
        if (!User::isAdmin()) {
            return ['success' => false, 'message' => 'No tienes permisos para esta acción'];
        }
        
        // Comprobar si hay vehículos asociados a esta ubicación
        $sql = "SELECT COUNT(*) as count FROM vehicles WHERE location_id = ?";
        $result = executeQuery($sql, [$id]);
        
        if ($result[0]['count'] > 0) {
            return ['success' => false, 'message' => 'No se puede eliminar la ubicación porque hay vehículos asociados'];
        }
        
        $sql = "DELETE FROM locations WHERE id = ?";
        $result = executeQuery($sql, [$id]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Ubicación eliminada correctamente'];
        } else {
            return ['success' => false, 'message' => 'Error al eliminar la ubicación'];
        }
    }
}
