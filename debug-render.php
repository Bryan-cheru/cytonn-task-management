<?php
// Diagnostic script for Render.com deployment
// Visit: https://your-app-url.onrender.com/debug-render.php

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Render.com Debug - Cytonn Task Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 2rem 0; }
        .debug-container { max-width: 900px; margin: 0 auto; }
        .debug-section { background: white; border-radius: 8px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .code-block { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 1rem; font-family: monospace; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
    </style>
</head>
<body>
    <div class="container debug-container">
        <h1 class="text-center mb-4">🔍 Render.com Deployment Debug</h1>
        
        <div class="debug-section">
            <h3>1. Environment Variables</h3>
            <div class="code-block">
<?php
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Current Time: " . date('Y-m-d H:i:s T') . "\n\n";

echo "Environment Variable Detection:\n";
$database_url_env = $_ENV['DATABASE_URL'] ?? null;
$database_url_server = $_SERVER['DATABASE_URL'] ?? null;
$database_url_getenv = getenv('DATABASE_URL');

echo "- \$_ENV['DATABASE_URL']: " . ($database_url_env ? "✅ Found (" . strlen($database_url_env) . " chars)" : "❌ Not found") . "\n";
echo "- \$_SERVER['DATABASE_URL']: " . ($database_url_server ? "✅ Found (" . strlen($database_url_server) . " chars)" : "❌ Not found") . "\n";
echo "- getenv('DATABASE_URL'): " . ($database_url_getenv ? "✅ Found (" . strlen($database_url_getenv) . " chars)" : "❌ Not found") . "\n";

$final_url = $database_url_env ?? $database_url_server ?? $database_url_getenv ?? null;
echo "\nFinal DATABASE_URL: " . ($final_url ? "✅ Available" : "❌ Not available") . "\n";

if ($final_url) {
    $parsed = parse_url($final_url);
    echo "\nParsed URL Components:\n";
    echo "- Scheme: " . ($parsed['scheme'] ?? 'N/A') . "\n";
    echo "- Host: " . ($parsed['host'] ?? 'N/A') . "\n";
    echo "- Port: " . ($parsed['port'] ?? 'default') . "\n";
    echo "- Database: " . (isset($parsed['path']) ? ltrim($parsed['path'], '/') : 'N/A') . "\n";
    echo "- User: " . ($parsed['user'] ?? 'N/A') . "\n";
    echo "- Password: " . (isset($parsed['pass']) ? "[HIDDEN]" : 'N/A') . "\n";
}
?>
            </div>
        </div>

        <div class="debug-section">
            <h3>2. Database Connection Test</h3>
            <div class="code-block">
<?php
try {
    require_once __DIR__ . '/config/database-render.php';
    echo "✅ Database config loaded successfully\n";
    
    $db = new Database();
    echo "✅ Database object created\n";
    
    $connection = $db->getConnection();
    echo "✅ Database connection established\n";
    
    $driver = $connection->getAttribute(PDO::ATTR_DRIVER_NAME);
    echo "✅ Database driver: " . $driver . "\n";
    
    if ($db->isPostgreSQL()) {
        echo "✅ Using PostgreSQL database\n";
        
        // Test query
        $stmt = $connection->query("SELECT version()");
        $version = $stmt->fetchColumn();
        echo "✅ PostgreSQL version: " . $version . "\n";
    } elseif ($db->isMySQL()) {
        echo "✅ Using MySQL database\n";
        
        // Test query
        $stmt = $connection->query("SELECT VERSION()");
        $version = $stmt->fetchColumn();
        echo "✅ MySQL version: " . $version . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "❌ Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
            </div>
        </div>

        <div class="debug-section">
            <h3>3. Table Structure Check</h3>
            <div class="code-block">
<?php
try {
    if (isset($db) && $connection) {
        // Check if tables exist
        if ($db->isPostgreSQL()) {
            $stmt = $connection->query("
                SELECT table_name 
                FROM information_schema.tables 
                WHERE table_schema = 'public' 
                AND table_type = 'BASE TABLE'
                ORDER BY table_name
            ");
        } else {
            $stmt = $connection->query("SHOW TABLES");
        }
        
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            echo "⚠️  No tables found in database\n";
            echo "   Run setup-production-admin.php to create tables\n";
        } else {
            echo "✅ Found tables: " . implode(', ', $tables) . "\n";
            
            // Check users table
            if (in_array('users', $tables)) {
                $stmt = $connection->query("SELECT COUNT(*) FROM users");
                $userCount = $stmt->fetchColumn();
                echo "✅ Users table: {$userCount} records\n";
                
                $stmt = $connection->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
                $adminCount = $stmt->fetchColumn();
                echo "✅ Admin users: {$adminCount}\n";
                
                if ($adminCount > 0) {
                    $stmt = $connection->query("SELECT name, email FROM users WHERE role = 'admin' LIMIT 3");
                    $admins = $stmt->fetchAll();
                    echo "   Admin accounts:\n";
                    foreach ($admins as $admin) {
                        echo "   - {$admin['name']} ({$admin['email']})\n";
                    }
                }
            }
            
            // Check tasks table
            if (in_array('tasks', $tables)) {
                $stmt = $connection->query("SELECT COUNT(*) FROM tasks");
                $taskCount = $stmt->fetchColumn();
                echo "✅ Tasks table: {$taskCount} records\n";
            }
        }
    }
} catch (Exception $e) {
    echo "❌ Table check error: " . $e->getMessage() . "\n";
}
?>
            </div>
        </div>

        <div class="debug-section">
            <h3>4. File System Check</h3>
            <div class="code-block">
<?php
$files_to_check = [
    'config/database-render.php',
    'config/database.php',
    'app/Models/User.php',
    'app/Models/Task.php',
    'app/Auth.php',
    'public/index.php',
    'setup-production-admin.php'
];

foreach ($files_to_check as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✅ {$file} exists\n";
    } else {
        echo "❌ {$file} missing\n";
    }
}
?>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="setup-production-admin.php" class="btn btn-primary btn-lg">
                🛠️ Run Production Setup
            </a>
            <a href="index.php" class="btn btn-outline-primary btn-lg ms-2">
                🔐 Try Login
            </a>
        </div>
    </div>
</body>
</html>
