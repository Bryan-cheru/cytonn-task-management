<?php
// Database initialization script
// Run this to ensure all tables are created properly

header('Content-Type: text/plain');

echo "=== DATABASE INITIALIZATION ===\n\n";

try {
    // Include database config
    if (isset($_ENV['DATABASE_URL']) || getenv('DATABASE_URL') || isset($_SERVER['DATABASE_URL'])) {
        require_once __DIR__ . '/config/database-docker.php';
    } else {
        require_once __DIR__ . '/config/database.php';
    }
    
    $db = new Database();
    echo "✅ Database connection established\n\n";
    
    // Check if tables exist
    if ($db->isPostgreSQL()) {
        echo "Using PostgreSQL database\n";
        $tableQuery = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name";
    } else {
        echo "Using MySQL database\n";
        $tableQuery = "SHOW TABLES";
    }
    
    $stmt = $db->prepare($tableQuery);
    $stmt->execute();
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Existing tables: " . (empty($tables) ? "None" : implode(', ', $tables)) . "\n\n";
    
    if (empty($tables) || !in_array('users', $tables)) {
        echo "Creating database tables...\n";
        
        if ($db->isPostgreSQL()) {
            // PostgreSQL table creation
            $db->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id SERIAL PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    role VARCHAR(10) DEFAULT 'user' CHECK (role IN ('admin', 'user')),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            $db->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS tasks (
                    id SERIAL PRIMARY KEY,
                    title VARCHAR(200) NOT NULL,
                    description TEXT,
                    assigned_to INTEGER REFERENCES users(id) ON DELETE SET NULL,
                    created_by INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                    status VARCHAR(15) DEFAULT 'pending' CHECK (status IN ('pending', 'in_progress', 'completed')),
                    priority VARCHAR(10) DEFAULT 'medium' CHECK (priority IN ('low', 'medium', 'high')),
                    due_date DATE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            echo "✅ PostgreSQL tables created\n";
        } else {
            // MySQL table creation
            $db->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    role ENUM('admin', 'user') DEFAULT 'user',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
            ");
            
            $db->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS tasks (
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
                    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
            ");
            
            echo "✅ MySQL tables created\n";
        }
    } else {
        echo "✅ All required tables already exist\n";
    }
    
    // Check user count
    $stmt = $db->prepare("SELECT COUNT(*) FROM users");
    $stmt->execute();
    $userCount = $stmt->fetchColumn();
    
    echo "\nCurrent users in database: $userCount\n";
    
    if ($userCount == 0) {
        echo "\n🔧 No users found. You need to create the first admin user.\n";
        echo "Visit: /setup-admin.php to create the super admin\n";
    } else {
        // Show existing users
        $stmt = $db->prepare("SELECT id, name, email, role FROM users ORDER BY id");
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        echo "\nExisting users:\n";
        foreach ($users as $user) {
            echo "- ID {$user['id']}: {$user['name']} ({$user['email']}) - {$user['role']}\n";
        }
    }
    
    echo "\n✅ Database initialization complete!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
