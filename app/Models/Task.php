<?php
// Include the correct database configuration for the environment
if (isset($_ENV['DATABASE_URL']) || getenv('DATABASE_URL') || isset($_SERVER['DATABASE_URL'])) {
    // Production environment (Render.com with Docker)
    require_once __DIR__ . '/../../config/database-docker.php';
} else {
    // Local development environment
    require_once __DIR__ . '/../../config/database.php';
}

class Task {
    private $db;
    private $table = 'tasks';

    public function __construct() {
        $this->db = new Database();
    }

    // Create a new task
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (title, description, assigned_to, created_by, deadline, status) VALUES (?, ?, ?, ?, ?, ?)";
        
        return $this->db->execute($sql, [
            $data['title'],
            $data['description'],
            $data['assigned_to'],
            $data['created_by'],
            $data['deadline'],
            $data['status'] ?? 'Pending'
        ]);
    }

    // Get all tasks with user names
    public function getAll() {
        $sql = "SELECT t.*, 
                       u1.name as assigned_user_name,
                       u1.email as assigned_user_email,
                       u2.name as created_by_name
                FROM {$this->table} t
                LEFT JOIN users u1 ON t.assigned_to = u1.id
                LEFT JOIN users u2 ON t.created_by = u2.id
                ORDER BY t.created_at DESC";
        return $this->db->fetchAll($sql);
    }

    // Get task by ID
    public function getById($id) {
        $sql = "SELECT t.*, 
                       u1.name as assigned_user_name,
                       u1.email as assigned_user_email,
                       u2.name as created_by_name
                FROM {$this->table} t
                LEFT JOIN users u1 ON t.assigned_to = u1.id
                LEFT JOIN users u2 ON t.created_by = u2.id
                WHERE t.id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    // Get tasks assigned to a specific user
    public function getTasksByUser($userId) {
        $sql = "SELECT t.*, 
                       u2.name as created_by_name
                FROM {$this->table} t
                LEFT JOIN users u2 ON t.created_by = u2.id
                WHERE t.assigned_to = ?
                ORDER BY t.deadline ASC";
        return $this->db->fetchAll($sql, [$userId]);
    }

    // Update task
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET title = ?, description = ?, assigned_to = ?, deadline = ?, status = ? WHERE id = ?";
        return $this->db->execute($sql, [
            $data['title'],
            $data['description'],
            $data['assigned_to'],
            $data['deadline'],
            $data['status'],
            $id
        ]);
    }

    // Update task status only
    public function updateStatus($id, $status) {
        $sql = "UPDATE {$this->table} SET status = ? WHERE id = ?";
        return $this->db->execute($sql, [$status, $id]);
    }

    // Delete task
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    // Get task statistics
    public function getStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total_tasks,
                    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_tasks,
                    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_tasks,
                    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_tasks,
                    SUM(CASE WHEN deadline < CURDATE() AND status != 'Completed' THEN 1 ELSE 0 END) as overdue_tasks
                FROM {$this->table}";
        return $this->db->fetch($sql);
    }

    // Get tasks by status
    public function getTasksByStatus($status) {
        $sql = "SELECT t.*, 
                       u1.name as assigned_user_name,
                       u2.name as created_by_name
                FROM {$this->table} t
                LEFT JOIN users u1 ON t.assigned_to = u1.id
                LEFT JOIN users u2 ON t.created_by = u2.id
                WHERE t.status = ?
                ORDER BY t.deadline ASC";
        return $this->db->fetchAll($sql, [$status]);
    }
}
?>
