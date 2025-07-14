<?php
// Include the unified database configuration that handles all environments
require_once __DIR__ . '/../../config/database-unified.php';

class User {
    private $db;
    private $table = 'users';

    public function __construct() {
        $this->db = new Database();
    }

    // Create a new user
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (name, email, password, role) VALUES (?, ?, ?, ?)";
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        return $this->db->execute($sql, [
            $data['name'],
            $data['email'],
            $hashedPassword,
            $data['role'] ?? 'user'
        ]);
    }

    // Get all users
    public function getAll() {
        $sql = "SELECT id, name, email, role, created_at FROM {$this->table} ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }

    // Get user by ID
    public function getById($id) {
        $sql = "SELECT id, name, email, role, created_at FROM {$this->table} WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    // Get user by email
    public function getByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        return $this->db->fetch($sql, [$email]);
    }

    // Update user
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET name = ?, email = ?, role = ? WHERE id = ?";
        return $this->db->execute($sql, [
            $data['name'],
            $data['email'],
            $data['role'],
            $id
        ]);
    }

    // Update user password
    public function updatePassword($id, $password) {
        $sql = "UPDATE {$this->table} SET password = ? WHERE id = ?";
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        return $this->db->execute($sql, [$hashedPassword, $id]);
    }

    // Delete user
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    // Verify user credentials
    public function verifyCredentials($email, $password) {
        $user = $this->getByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']); // Remove password from returned data
            return $user;
        }
        return false;
    }

    // Get users for dropdown (excluding admins)
    public function getUsersForAssignment() {
        $sql = "SELECT id, name FROM {$this->table} WHERE role = 'user' ORDER BY name";
        return $this->db->fetchAll($sql);
    }
}
?>
