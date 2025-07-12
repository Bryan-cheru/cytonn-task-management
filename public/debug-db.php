<?php
// Debug script for database connection testing
echo "<h1>Database Connection Debug</h1>";

// Check environment
echo "<h2>Environment Variables</h2>";
echo "DATABASE_URL: " . (getenv('DATABASE_URL') ? "Found" : "Not found") . "<br>";
if (getenv('DATABASE_URL')) {
    $url = parse_url(getenv('DATABASE_URL'));
    echo "Host: " . ($url['host'] ?? 'not set') . "<br>";
    echo "Port: " . ($url['port'] ?? 'not set') . "<br>";
    echo "Database: " . (isset($url['path']) ? ltrim($url['path'], '/') : 'not set') . "<br>";
    echo "User: " . ($url['user'] ?? 'not set') . "<br>";
    echo "Password: " . ($url['pass'] ? "***set***" : 'not set') . "<br>";
}

// Check PHP extensions
echo "<h2>PHP Extensions</h2>";
echo "PDO: " . (extension_loaded('pdo') ? "✅ Loaded" : "❌ Not loaded") . "<br>";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? "✅ Loaded" : "❌ Not loaded") . "<br>";
echo "PDO PostgreSQL: " . (extension_loaded('pdo_pgsql') ? "✅ Loaded" : "❌ Not loaded") . "<br>";

// Test database connection
echo "<h2>Database Connection Test</h2>";

try {
    if (getenv('DATABASE_URL')) {
        // Production environment (Render.com with PostgreSQL)
        require_once __DIR__ . '/../config/database-docker.php';
        echo "Using database-docker.php configuration<br>";
    } else {
        // Local development environment
        require_once __DIR__ . '/../config/database.php';
        echo "Using database.php configuration<br>";
    }
    
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "✅ Database connection successful!<br>";
        
        // Test a simple query
        if ($db->isPostgreSQL()) {
            $stmt = $conn->query("SELECT version() as version");
            $result = $stmt->fetch();
            echo "PostgreSQL Version: " . $result['version'] . "<br>";
        } else {
            $stmt = $conn->query("SELECT VERSION() as version");
            $result = $stmt->fetch();
            echo "MySQL Version: " . $result['version'] . "<br>";
        }
    } else {
        echo "❌ Failed to get database connection<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2 { color: #333; }
</style>
