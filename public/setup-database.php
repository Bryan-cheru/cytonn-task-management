<?php
// Robust database initialization for Render deployment
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Setup - Cytonn Task Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; max-width: 800px; margin: 0 auto; }
        .success { color: #059669; background: #ecfdf5; padding: 15px; border-radius: 6px; margin: 10px 0; }
        .error { color: #dc2626; background: #fef2f2; padding: 15px; border-radius: 6px; margin: 10px 0; }
        .info { color: #2563eb; background: #eff6ff; padding: 15px; border-radius: 6px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 6px; overflow-x: auto; }
        .btn { background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>
<div class="container">
    <h1>🚀 Cytonn Task Management - Database Setup</h1>
    
    <?php
    try {
        echo '<div class="info">🔄 Starting database initialization...</div>';
        
        // Check if database config exists
        $config_path = __DIR__ . '/../config/database-unified.php';
        if (!file_exists($config_path)) {
            throw new Exception("Database configuration file not found at: " . $config_path);
        }
        
        echo '<div class="success">✅ Database configuration found</div>';
        
        // Include database configuration
        require_once $config_path;
        
        // Create database connection
        $db = new Database();
        $pdo = $db->getConnection();
        
        echo '<div class="success">✅ Database connection successful</div>';
        
        // Check if we have DATABASE_URL (Render environment)
        $database_url = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? getenv('DATABASE_URL') ?? null;
        if ($database_url) {
            echo '<div class="info">🌐 Detected Render PostgreSQL environment</div>';
            $schema_file = __DIR__ . '/../database/postgres-schema.sql';
        } else {
            echo '<div class="info">🏠 Detected local MySQL environment</div>';
            $schema_file = __DIR__ . '/../database/cytonn_task_management.sql';
        }
        
        // Check if schema file exists
        if (!file_exists($schema_file)) {
            throw new Exception("Schema file not found: " . $schema_file);
        }
        
        echo '<div class="success">✅ Schema file found: ' . basename($schema_file) . '</div>';
        
        // Read schema file
        $schema = file_get_contents($schema_file);
        if (!$schema) {
            throw new Exception("Could not read schema file");
        }
        
        echo '<div class="success">✅ Schema file loaded (' . strlen($schema) . ' bytes)</div>';
        
        // Execute schema
        echo '<div class="info">🔄 Creating database tables...</div>';
        
        // For PostgreSQL, execute each statement separately
        if ($database_url) {
            $statements = explode(';', $schema);
            $executed = 0;
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    try {
                        $pdo->exec($statement);
                        $executed++;
                    } catch (Exception $e) {
                        // Ignore "already exists" errors
                        if (strpos($e->getMessage(), 'already exists') === false && 
                            strpos($e->getMessage(), 'duplicate key') === false) {
                            echo '<div class="error">⚠️ Statement warning: ' . $e->getMessage() . '</div>';
                        }
                    }
                }
            }
            echo '<div class="success">✅ Executed ' . $executed . ' SQL statements</div>';
        } else {
            // For MySQL, execute as one block
            $pdo->exec($schema);
            echo '<div class="success">✅ MySQL schema executed successfully</div>';
        }
        
        // Verify tables were created
        if ($database_url) {
            $tables = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'")->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        }
        
        echo '<div class="success">✅ Tables created: ' . implode(', ', $tables) . '</div>';
        
        // Check if admin user exists
        $adminCheck = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
        
        if ($adminCheck > 0) {
            echo '<div class="success">✅ Admin user already exists (' . $adminCheck . ' admin(s) found)</div>';
        } else {
            echo '<div class="info">🔄 Creating admin user...</div>';
            
            // Create admin user
            $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute(['System Administrator', 'admin@cytonn.com', $hashedPassword, 'admin']);
            
            echo '<div class="success">✅ Admin user created successfully</div>';
        }
        
        // Final verification
        $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $taskCount = $pdo->query("SELECT COUNT(*) FROM tasks")->fetchColumn();
        
        echo '<div class="success">
            ✅ Database initialization complete!<br>
            📊 Users: ' . $userCount . '<br>
            📋 Tasks: ' . $taskCount . '
        </div>';
        
        echo '<div class="info">
            <h3>🎉 Ready to Login!</h3>
            <p><strong>Email:</strong> admin@cytonn.com</p>
            <p><strong>Password:</strong> admin123</p>
            <p><a href="/" class="btn">Go to Login Page</a></p>
        </div>';
        
    } catch (Exception $e) {
        echo '<div class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        echo '<div class="error">
            <h3>Troubleshooting:</h3>
            <ol>
                <li>Check that DATABASE_URL environment variable is set correctly</li>
                <li>Ensure PostgreSQL database is running and accessible</li>
                <li>Verify database credentials and permissions</li>
                <li>Try the emergency admin creator: <a href="/create-emergency-admin.php">Create Emergency Admin</a></li>
            </ol>
        </div>';
        
        echo '<h3>Debug Information:</h3>';
        echo '<pre>';
        echo 'DATABASE_URL exists: ' . (getenv('DATABASE_URL') ? 'Yes' : 'No') . "\n";
        echo 'Config file: ' . ($config_path ?? 'Not set') . "\n";
        echo 'Schema file: ' . ($schema_file ?? 'Not set') . "\n";
        echo 'PHP Version: ' . phpversion() . "\n";
        echo 'Available PDO drivers: ' . implode(', ', PDO::getAvailableDrivers()) . "\n";
        echo '</pre>';
    }
    ?>
    
    <div class="info">
        <h3>🔧 Other Useful Tools:</h3>
        <p><a href="/health-debug.php" class="btn">Health Check</a></p>
        <p><a href="/create-emergency-admin.php" class="btn">Emergency Admin Creator</a></p>
    </div>
</div>
</body>
</html>
