-- Cytonn Task Management System Database Schema
-- Generated for Software Engineering Internship Challenge
-- Database: MySQL 5.7+ / MariaDB
-- Date: July 12, 2025

-- Create database
CREATE DATABASE IF NOT EXISTS task_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE task_management;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tasks table
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    assigned_to INT,
    created_by INT NOT NULL,
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_created_by (created_by),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Sample data (optional - remove for production)
-- Admin user (password: 'admin123' - change in production)
INSERT INTO users (name, email, password, role) VALUES 
('System Administrator', 'admin@cytonn.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Sample regular user (password: 'user123' - change in production)
INSERT INTO users (name, email, password, role) VALUES 
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Sample task
INSERT INTO tasks (title, description, assigned_to, created_by, status, priority, due_date) VALUES 
('Welcome Task', 'This is a sample task to demonstrate the system functionality', 2, 1, 'pending', 'medium', DATE_ADD(CURDATE(), INTERVAL 7 DAY));

-- Create views for common queries
CREATE VIEW task_overview AS
SELECT 
    t.id,
    t.title,
    t.description,
    t.status,
    t.priority,
    t.due_date,
    t.created_at,
    u_assigned.name AS assigned_to_name,
    u_assigned.email AS assigned_to_email,
    u_creator.name AS created_by_name,
    CASE 
        WHEN t.due_date < CURDATE() AND t.status != 'completed' THEN 'overdue'
        WHEN t.due_date = CURDATE() AND t.status != 'completed' THEN 'due_today'
        ELSE 'normal'
    END AS urgency_status
FROM tasks t
LEFT JOIN users u_assigned ON t.assigned_to = u_assigned.id
LEFT JOIN users u_creator ON t.created_by = u_creator.id;

-- Create view for user statistics
CREATE VIEW user_stats AS
SELECT 
    u.id,
    u.name,
    u.email,
    u.role,
    COUNT(t.id) AS total_tasks,
    COUNT(CASE WHEN t.status = 'completed' THEN 1 END) AS completed_tasks,
    COUNT(CASE WHEN t.status = 'pending' THEN 1 END) AS pending_tasks,
    COUNT(CASE WHEN t.status = 'in_progress' THEN 1 END) AS in_progress_tasks,
    COUNT(CASE WHEN t.due_date < CURDATE() AND t.status != 'completed' THEN 1 END) AS overdue_tasks
FROM users u
LEFT JOIN tasks t ON u.id = t.assigned_to
WHERE u.role = 'user'
GROUP BY u.id, u.name, u.email, u.role;

-- Stored procedure for task assignment with email logging
DELIMITER $$
CREATE PROCEDURE AssignTask(
    IN task_id INT,
    IN user_id INT,
    IN assigned_by INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    UPDATE tasks 
    SET assigned_to = user_id, 
        updated_at = CURRENT_TIMESTAMP 
    WHERE id = task_id;
    
    -- Log assignment (application will handle email notification)
    INSERT INTO task_assignments_log (task_id, assigned_to, assigned_by, assigned_at) 
    VALUES (task_id, user_id, assigned_by, CURRENT_TIMESTAMP)
    ON DUPLICATE KEY UPDATE 
        assigned_to = user_id,
        assigned_by = assigned_by,
        assigned_at = CURRENT_TIMESTAMP;
    
    COMMIT;
END$$
DELIMITER ;

-- Optional: Task assignment log table
CREATE TABLE task_assignments_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    assigned_to INT NOT NULL,
    assigned_by INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (task_id, assigned_to)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Triggers for automatic timestamp updates
DELIMITER $$
CREATE TRIGGER update_task_timestamp 
    BEFORE UPDATE ON tasks 
    FOR EACH ROW 
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END$$

CREATE TRIGGER update_user_timestamp 
    BEFORE UPDATE ON users 
    FOR EACH ROW 
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END$$
DELIMITER ;

-- Performance indexes
CREATE INDEX idx_tasks_status_date ON tasks(status, due_date);
CREATE INDEX idx_tasks_assigned_status ON tasks(assigned_to, status);
CREATE INDEX idx_users_role_email ON users(role, email);

-- Grant permissions (adjust as needed for your environment)
-- GRANT SELECT, INSERT, UPDATE, DELETE ON task_management.* TO 'app_user'@'localhost';
-- FLUSH PRIVILEGES;

-- Database configuration notes:
-- 1. Change default passwords before production deployment
-- 2. Remove sample data for production use
-- 3. Configure proper user permissions
-- 4. Enable binary logging for replication if needed
-- 5. Set up regular backups

-- End of schema
