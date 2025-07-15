<?php
// Database diagnostic and emergency setup tool
header('Content-Type: text/html; charset=utf-8');
echo "<h1>🔧 Cytonn Database Diagnostic</h1>";

try {
    // Test database connection
    echo "<h2>1. Testing Database Connection...</h2>";
    require_once __DIR__ . '/../config/database-unified.php';
    
    $db = new Database();
    $pdo = $db->getConnection();
    echo "✅ Database connection successful!<br>";
    
    // Check if tables exist
    echo "<h2>2. Checking Tables...</h2>";
    $tables = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'")->fetchAll();
    
    if (empty($tables)) {
        echo "❌ No tables found. Creating schema...<br>";
        
        // Create tables
        $schema = file_get_contents(__DIR__ . '/../database/postgres-schema.sql');
        if ($schema) {
            $pdo->exec($schema);
            echo "✅ Database schema created successfully!<br>";
        } else {
            echo "❌ Could not read schema file<br>";
        }
    } else {
        echo "✅ Tables exist: " . implode(', ', array_column($tables, 'table_name')) . "<br>";
    }
    
    // Check for admin user
    echo "<h2>3. Checking Admin User...</h2>";
    $admin = $pdo->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1")->fetch();
    
    if (!$admin) {
        echo "❌ No admin user found. Creating emergency admin...<br>";
        
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?) ON CONFLICT (email) DO UPDATE SET password = EXCLUDED.password");
        $stmt->execute(['Emergency Admin', 'admin@cytonn.com', $hashedPassword, 'admin']);
        
        echo "✅ Emergency admin created!<br>";
    } else {
        echo "✅ Admin user exists: " . $admin['email'] . "<br>";
    }
    
    echo "<h2>🎉 Setup Complete!</h2>";
    echo "<p><strong>Login credentials:</strong></p>";
    echo "<ul>";
    echo "<li>Email: admin@cytonn.com</li>";
    echo "<li>Password: admin123</li>";
    echo "</ul>";
    echo "<p><a href='/'>🚀 Go to Login Page</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Check your DATABASE_URL environment variable.</p>";
}
?>
