<?php
// Direct connection test with the exact DATABASE_URL
header('Content-Type: text/plain');

echo "=== DIRECT CONNECTION TEST ===\n\n";

$database_url = "postgresql://admin:j8OrQvF3ooNARtREWc99YfQV9RbvTDFh@dpg-d1pc146r433s73d84fc0-a/cytonn_task_management";

echo "Testing connection with URL: " . substr($database_url, 0, 40) . "...\n\n";

try {
    $url = parse_url($database_url);
    
    echo "Parsed components:\n";
    echo "- Scheme: " . $url['scheme'] . "\n";
    echo "- Host: " . $url['host'] . "\n";
    echo "- Port: " . ($url['port'] ?? 5432) . "\n";
    echo "- Database: " . ltrim($url['path'], '/') . "\n";
    echo "- User: " . $url['user'] . "\n";
    echo "- Password: ***\n\n";
    
    $dsn = "pgsql:host={$url['host']};port=" . ($url['port'] ?? 5432) . ";dbname=" . ltrim($url['path'], '/');
    echo "DSN: $dsn\n\n";
    
    $pdo = new PDO($dsn, $url['user'], $url['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 30
    ]);
    
    echo "✅ Connection successful!\n\n";
    
    // Test query
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "PostgreSQL version: $version\n\n";
    
    // Check if tables exist
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "No tables found. Setting up database...\n";
        
        // Create users table
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(10) DEFAULT 'user' CHECK (role IN ('admin', 'user')),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Create tasks table
        $pdo->exec("CREATE TABLE IF NOT EXISTS tasks (
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
        )");
        
        // Insert admin user
        $pdo->exec("INSERT INTO users (name, email, password, role) 
                   VALUES ('System Administrator', 'admin@cytonn.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
                   ON CONFLICT (email) DO NOTHING");
        
        // Insert test user
        $pdo->exec("INSERT INTO users (name, email, password, role) 
                   VALUES ('John Doe', 'john@example.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user')
                   ON CONFLICT (email) DO NOTHING");
        
        echo "✅ Database setup completed!\n";
    } else {
        echo "✅ Found tables: " . implode(', ', $tables) . "\n";
    }
    
    // Test user count
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    echo "✅ Found $userCount users in database\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n";
}
?>
