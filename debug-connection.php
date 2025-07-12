<?php
// Debug script to test database connection on Render.com
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Debug</h1>";

// Check environment variables
echo "<h2>Environment Variables:</h2>";
echo "DATABASE_URL exists: " . (getenv('DATABASE_URL') ? 'YES' : 'NO') . "<br>";
if (getenv('DATABASE_URL')) {
    $url = parse_url(getenv('DATABASE_URL'));
    echo "Host: " . ($url['host'] ?? 'Not set') . "<br>";
    echo "Port: " . ($url['port'] ?? 'Not set') . "<br>";
    echo "Database: " . (isset($url['path']) ? ltrim($url['path'], '/') : 'Not set') . "<br>";
    echo "User: " . ($url['user'] ?? 'Not set') . "<br>";
    echo "Password: " . (isset($url['pass']) ? '***HIDDEN***' : 'Not set') . "<br>";
}

echo "<h2>PHP Extensions:</h2>";
echo "PDO: " . (extension_loaded('pdo') ? 'Loaded' : 'NOT LOADED') . "<br>";
echo "PDO PostgreSQL: " . (extension_loaded('pdo_pgsql') ? 'Loaded' : 'NOT LOADED') . "<br>";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? 'Loaded' : 'NOT LOADED') . "<br>";

echo "<h2>Direct Connection Test:</h2>";
try {
    if (getenv('DATABASE_URL')) {
        $url = parse_url(getenv('DATABASE_URL'));
        $dsn = "pgsql:host={$url['host']};port=" . ($url['port'] ?? 5432) . ";dbname=" . ltrim($url['path'], '/');
        $pdo = new PDO($dsn, $url['user'], $url['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        echo "✅ Direct PostgreSQL connection successful!<br>";
        
        // Test a simple query
        $stmt = $pdo->query("SELECT version()");
        $version = $stmt->fetchColumn();
        echo "PostgreSQL Version: " . $version . "<br>";
        
    } else {
        echo "❌ No DATABASE_URL found, would use local MySQL<br>";
    }
} catch (Exception $e) {
    echo "❌ Direct connection failed: " . $e->getMessage() . "<br>";
}

echo "<h2>Database Class Test:</h2>";
try {
    // Include the correct database configuration
    if (getenv('DATABASE_URL')) {
        require_once __DIR__ . '/config/database-docker.php';
    } else {
        require_once __DIR__ . '/config/database.php';
    }
    
    $db = new Database();
    echo "✅ Database class instantiated successfully!<br>";
    
    // Test the connection
    $conn = $db->getConnection();
    if ($conn) {
        echo "✅ Database connection obtained successfully!<br>";
    } else {
        echo "❌ Database connection is null<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Database class test failed: " . $e->getMessage() . "<br>";
}

echo "<h2>Model Test:</h2>";
try {
    require_once __DIR__ . '/app/Models/User.php';
    $userModel = new User();
    echo "✅ User model instantiated successfully!<br>";
    
    // Try to get all users
    $users = $userModel->getAll();
    echo "✅ User query executed successfully! Found " . count($users) . " users.<br>";
    
} catch (Exception $e) {
    echo "❌ Model test failed: " . $e->getMessage() . "<br>";
}
?>
