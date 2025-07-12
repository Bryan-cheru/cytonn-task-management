<?php
// Debug login and check database users
header('Content-Type: text/plain');

echo "=== LOGIN DEBUG TEST ===\n\n";

try {
    // Include database config
    if (isset($_ENV['DATABASE_URL']) || getenv('DATABASE_URL') || isset($_SERVER['DATABASE_URL'])) {
        require_once __DIR__ . '/config/database-docker.php';
    } else {
        require_once __DIR__ . '/config/database.php';
    }
    
    $db = new Database();
    echo "✅ Database connection established\n\n";
    
    // Check what users exist
    $stmt = $db->prepare("SELECT id, name, email, role, created_at FROM users ORDER BY id");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    echo "=== USERS IN DATABASE ===\n";
    if (empty($users)) {
        echo "❌ No users found in database!\n";
        
        // Create admin user if none exists
        echo "Creating admin user...\n";
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute(['System Administrator', 'admin@cytonn.com', $hashedPassword, 'admin']);
        
        if ($result) {
            echo "✅ Admin user created successfully\n";
        } else {
            echo "❌ Failed to create admin user\n";
        }
    } else {
        foreach ($users as $user) {
            echo "ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}, Role: {$user['role']}\n";
        }
    }
    
    echo "\n=== LOGIN TEST ===\n";
    
    // Test login with admin credentials
    $testEmail = 'admin@cytonn.com';
    $testPassword = 'admin123';
    
    echo "Testing login with: $testEmail / $testPassword\n";
    
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$testEmail]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ User found in database\n";
        echo "User ID: {$user['id']}\n";
        echo "User Name: {$user['name']}\n";
        echo "User Role: {$user['role']}\n";
        echo "Password Hash: " . substr($user['password'], 0, 30) . "...\n";
        
        // Test password verification
        if (password_verify($testPassword, $user['password'])) {
            echo "✅ Password verification SUCCESSFUL\n";
        } else {
            echo "❌ Password verification FAILED\n";
            
            // Try to update password with correct hash
            echo "Updating password with new hash...\n";
            $newHash = password_hash($testPassword, PASSWORD_DEFAULT);
            $updateStmt = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
            $updateResult = $updateStmt->execute([$newHash, $testEmail]);
            
            if ($updateResult) {
                echo "✅ Password updated successfully\n";
                echo "Try logging in again with: $testEmail / $testPassword\n";
            } else {
                echo "❌ Failed to update password\n";
            }
        }
    } else {
        echo "❌ User not found in database\n";
        
        // Create the user
        echo "Creating admin user...\n";
        $hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute(['System Administrator', $testEmail, $hashedPassword, 'admin']);
        
        if ($result) {
            echo "✅ Admin user created successfully\n";
            echo "You can now login with: $testEmail / $testPassword\n";
        } else {
            echo "❌ Failed to create admin user\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
