<?php
// Test file to verify setup
echo "<h1>Cytonn Task Management System - Setup Test</h1>";

// Test PHP
echo "<h2>✅ PHP Status</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br><br>";

// Test Database Connection
echo "<h2>🔍 Database Connection Test</h2>";
try {
    require_once '../config/database.php';
    $db = new Database();
    echo "✅ Database connection successful!<br>";
    
    // Test if tables exist
    $result = $db->fetchAll("SHOW TABLES");
    echo "📊 Tables found: " . count($result) . "<br>";
    foreach ($result as $table) {
        echo "- " . array_values($table)[0] . "<br>";
    }
    
    // Test user count
    $userCount = $db->fetch("SELECT COUNT(*) as count FROM users");
    echo "<br>👥 Users in database: " . $userCount['count'] . "<br>";
    
    // Test task count
    $taskCount = $db->fetch("SELECT COUNT(*) as count FROM tasks");
    echo "📝 Tasks in database: " . $taskCount['count'] . "<br>";
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

echo "<br><h2>🚀 Next Steps</h2>";
echo "1. Go to <a href='index.php'>Login Page</a><br>";
echo "2. Use admin@cytonn.com / password to login as admin<br>";
echo "3. Or use john@example.com / password to login as user<br>";
echo "4. Test all features!<br>";

echo "<br><h2>📱 Application URLs</h2>";
echo "• <a href='index.php'>Login Page</a><br>";
echo "• <a href='dashboard.php'>Dashboard</a> (requires login)<br>";
echo "• <a href='manage-users.php'>Manage Users</a> (admin only)<br>";
echo "• <a href='manage-tasks.php'>Manage Tasks</a> (admin only)<br>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
h1 { color: #007bff; }
h2 { color: #333; margin-top: 30px; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
