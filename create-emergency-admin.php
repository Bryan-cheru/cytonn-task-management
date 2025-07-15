<?php
// Emergency admin user creation for production
// Use this if you need to create an admin user manually

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Emergency Admin Creator - Cytonn</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; }
        .success { color: #059669; background: #ecfdf5; padding: 15px; border-radius: 6px; margin: 10px 0; }
        .error { color: #dc2626; background: #fef2f2; padding: 15px; border-radius: 6px; margin: 10px 0; }
        .btn { background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>
<div class="container">
    <h1>🚨 Emergency Admin Creator</h1>
    
    <?php
    try {
        require_once __DIR__ . '/config/database-unified.php';
        
        $db = new Database();
        $pdo = $db->getConnection();
        
        // Create admin user
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?) ON CONFLICT (email) DO UPDATE SET password = EXCLUDED.password, role = EXCLUDED.role";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'Emergency Admin',
            'admin@cytonn.com',
            $hashedPassword,
            'admin'
        ]);
        
        echo '<div class="success">
            ✅ Emergency admin user created/updated successfully!<br><br>
            <strong>Email:</strong> admin@cytonn.com<br>
            <strong>Password:</strong> admin123<br><br>
            ⚠️ Please change this password after logging in!
        </div>';
        
        echo '<p><a href="/" class="btn">Go to Login Page</a></p>';
        
    } catch (Exception $e) {
        echo '<div class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        echo '<p>Try the full database setup instead: <a href="/setup-database.php" class="btn">Setup Database</a></p>';
    }
    ?>
</div>
</body>
</html>
