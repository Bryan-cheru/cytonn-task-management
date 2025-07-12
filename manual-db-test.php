<?php
// Simple test to check if we can manually set DATABASE_URL for testing
header('Content-Type: text/plain');

echo "=== MANUAL DATABASE_URL TEST ===\n\n";

// Try to manually set DATABASE_URL for testing
// This is just for debugging - in production, Render should set this
$test_db_url = getenv('RENDER_DATABASE_URL') ?: getenv('DATABASE_URL');

if ($test_db_url) {
    echo "Found DATABASE_URL: " . substr($test_db_url, 0, 30) . "...\n";
    
    try {
        $url = parse_url($test_db_url);
        $dsn = "pgsql:host={$url['host']};port=" . ($url['port'] ?? 5432) . ";dbname=" . ltrim($url['path'], '/');
        
        echo "Parsed URL:\n";
        echo "  Host: " . $url['host'] . "\n";
        echo "  Port: " . ($url['port'] ?? 5432) . "\n";
        echo "  Database: " . ltrim($url['path'], '/') . "\n";
        echo "  User: " . $url['user'] . "\n";
        
        $pdo = new PDO($dsn, $url['user'], $url['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        echo "\n✅ Connection successful!\n";
        
        // Try to create tables if they don't exist
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(10) DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Insert test admin user
        $pdo->exec("INSERT INTO users (name, email, password, role) 
                   VALUES ('Admin', 'admin@cytonn.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
                   ON CONFLICT (email) DO NOTHING");
        
        echo "✅ Database setup completed!\n";
        
    } catch (Exception $e) {
        echo "❌ Connection failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ No DATABASE_URL found\n";
    echo "Available environment variables:\n";
    foreach ($_ENV as $key => $value) {
        if (strpos(strtoupper($key), 'DATA') !== false || strpos(strtoupper($key), 'RENDER') !== false) {
            echo "  $key: " . substr($value, 0, 30) . "...\n";
        }
    }
}
?>
