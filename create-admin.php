<?php
// Quick admin creation script
// This bypasses the UI and directly creates an admin user

header('Content-Type: text/plain');

echo "=== QUICK ADMIN CREATION ===\n\n";

try {
    // Include database config
    if (isset($_ENV['DATABASE_URL']) || getenv('DATABASE_URL') || isset($_SERVER['DATABASE_URL'])) {
        require_once __DIR__ . '/config/database-docker.php';
    } else {
        require_once __DIR__ . '/config/database.php';
    }
    
    $db = new Database();
    echo "✅ Database connected\n";
    
    // Check current users
    $stmt = $db->prepare("SELECT id, name, email, role FROM users ORDER BY id");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    echo "Current users: " . count($users) . "\n";
    
    if (empty($users)) {
        echo "\nCreating default admin user...\n";
        
        // Create admin user
        $name = "System Administrator";
        $email = "admin@cytonn.com";
        $password = "admin123";
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
        $result = $stmt->execute([$name, $email, $hashedPassword]);
        
        if ($result) {
            echo "✅ Admin user created successfully!\n";
            echo "Email: $email\n";
            echo "Password: $password\n";
            echo "\nYou can now login at: /index.php\n";
        } else {
            echo "❌ Failed to create admin user\n";
        }
    } else {
        echo "\nExisting users:\n";
        foreach ($users as $user) {
            echo "- {$user['name']} ({$user['email']}) - {$user['role']}\n";
        }
        
        // Test password for admin user
        $adminUser = null;
        foreach ($users as $user) {
            if ($user['role'] === 'admin') {
                $adminUser = $user;
                break;
            }
        }
        
        if ($adminUser) {
            echo "\nTesting admin login...\n";
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$adminUser['email']]);
            $fullUser = $stmt->fetch();
            
            if (password_verify('admin123', $fullUser['password'])) {
                echo "✅ Password 'admin123' works for {$adminUser['email']}\n";
            } else {
                echo "❌ Password 'admin123' does NOT work for {$adminUser['email']}\n";
                echo "Updating password...\n";
                
                $newHash = password_hash('admin123', PASSWORD_DEFAULT);
                $updateStmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updateResult = $updateStmt->execute([$newHash, $adminUser['id']]);
                
                if ($updateResult) {
                    echo "✅ Password updated! Use: {$adminUser['email']} / admin123\n";
                } else {
                    echo "❌ Failed to update password\n";
                }
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
