<?php
// Create Specific Admin User
// This script creates an admin user with your specified credentials

// Database connection
try {
    if (isset($_ENV['DATABASE_URL']) || getenv('DATABASE_URL') || isset($_SERVER['DATABASE_URL'])) {
        require_once __DIR__ . '/config/database-docker.php';
    } else {
        require_once __DIR__ . '/config/database.php';
    }
    
    $db = new Database();
    echo "✅ Database connected successfully\n";
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Admin credentials
$name = "Brian Cheruiyot";
$email = "briancheruiyot501@gmail.com";
$password = "@Bryan2213";

echo "Creating admin user...\n";
echo "Name: $name\n";
echo "Email: $email\n";

try {
    // Check if user already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch();
    
    if ($existingUser) {
        echo "⚠️  User already exists with this email. Updating password...\n";
        
        // Update existing user to admin with new password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password = ?, role = 'admin', name = ? WHERE email = ?");
        $result = $stmt->execute([$hashedPassword, $name, $email]);
        
        if ($result) {
            echo "✅ User updated successfully!\n";
        } else {
            echo "❌ Failed to update user\n";
        }
    } else {
        // Create new admin user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, 'admin', NOW())");
        $result = $stmt->execute([$name, $email, $hashedPassword]);
        
        if ($result) {
            echo "✅ Admin user created successfully!\n";
        } else {
            echo "❌ Failed to create admin user\n";
        }
    }
    
    // Verify the user was created/updated
    $stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "\n📋 User Details:\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Name: " . $user['name'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Role: " . $user['role'] . "\n";
        
        echo "\n🎉 You can now login with:\n";
        echo "Email: briancheruiyot501@gmail.com\n";
        echo "Password: @Bryan2213\n";
        echo "\nLogin URL: https://cytonn-task-management.onrender.com/\n";
    } else {
        echo "❌ Failed to verify user creation\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
