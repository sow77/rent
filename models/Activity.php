<?php
require_once __DIR__ . '/../config/Database.php';

class Activity {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function log($userId, $action, $entityType, $entityId, $details = null) {
        $sql = "INSERT INTO activities (user_id, action, entity_type, entity_id, details) 
                VALUES (:user_id, :action, :entity_type, :entity_id, :details)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':action' => $action,
            ':entity_type' => $entityType,
            ':entity_id' => $entityId,
            ':details' => $details
        ]);
    }

    public function getRecent($limit = 10) {
        $sql = "SELECT a.*, u.name as user_name 
                FROM activities a 
                LEFT JOIN users u ON a.user_id = u.id 
                ORDER BY a.created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByEntity($entityType, $entityId) {
        $sql = "SELECT a.*, u.name as user_name 
                FROM activities a 
                LEFT JOIN users u ON a.user_id = u.id 
                WHERE a.entity_type = :entity_type 
                AND a.entity_id = :entity_id 
                ORDER BY a.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':entity_type' => $entityType,
            ':entity_id' => $entityId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 