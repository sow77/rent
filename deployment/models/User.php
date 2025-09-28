<?php

class User {
    private $db;
    private $table = 'users';
    private $id;
    private $email;
    private $password;
    private $name;
    private $role;
    private $created_at;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getEmail() { return $this->email; }
    public function getName() { return $this->name; }
    public function getRole() { return $this->role; }
    public function getCreatedAt() { return $this->created_at; }
    
    public function getByEmail($email) {
        $query = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        $query = "SELECT * FROM {$this->table} ORDER BY name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count() {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    public function create($data) {
        $query = "INSERT INTO {$this->table} (name, email, password, phone, role) VALUES (:name, :email, :password, :phone, :role)";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', $data['password']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':role', $data['role']);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }
    
    public function update($id, $data) {
        $query = "UPDATE {$this->table} SET name = :name, email = :email, role = :role";
        $params = [':id' => $id, ':name' => $data['name'], ':email' => $data['email'], ':role' => $data['role']];
        
        if (!empty($data['password'])) {
            $query .= ", password = :password";
            $params[':password'] = $data['password'];
        }
        
        $query .= " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }
    
    public function isAdmin() {
        return $this->role === 'admin';
    }

    public function emailExists($email) {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function save() {
        if ($this->id) {
            // Update
            $sql = "UPDATE users SET email = ?, name = ?, role = ? WHERE id = ?";
            return $this->db->query($sql, [$this->email, $this->name, $this->role, $this->id]);
        } else {
            // Insert
            $sql = "INSERT INTO users (id, email, name, role, password) VALUES (UUID(), ?, ?, ?, ?)";
            $hashedPassword = password_hash('default_password', PASSWORD_DEFAULT); // Debe ser reemplazado por un valor real
            return $this->db->query($sql, [$this->email, $this->name, $this->role, $hashedPassword]);
        }
    }

    public function register($name, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$name, $email, $hashedPassword]);
    }

    public static function authenticate($email, $password) {
        $db = Database::getInstance();
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $userObj = new self();
            $userObj->id = $user['id'];
            $userObj->name = $user['name'];
            $userObj->email = $user['email'];
            $userObj->role = $user['role'];
            return $userObj;
        }

        return null;
    }
}
