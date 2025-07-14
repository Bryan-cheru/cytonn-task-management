<?php
// Production Admin Setup for Render.com
// This script sets up the database schema and creates an admin user
// Access this via: https://your-app-url.onrender.com/setup-production-admin.php

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Setup - Cytonn Task Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
            padding: 2rem 0; 
        }
        .setup-container { 
            background: white; 
            border-radius: 20px; 
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25); 
            padding: 3rem; 
            max-width: 800px; 
            margin: 0 auto; 
        }
        .log-output {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
            max-height: 400px;
            overflow-y: auto;
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #17a2b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="setup-container">
            <div class="text-center mb-4">
                <h1><i class="fas fa-database"></i> Production Setup</h1>
                <p class="text-muted">Cytonn Task Management System</p>
            </div>

            <div class="log-output mb-4">
<?php
try {
    echo "<span class='info'>🚀 Starting Production Setup...</span>\n\n";
    
    // Check environment
    $database_url = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? getenv('DATABASE_URL') ?? null;
    
    if (!$database_url) {
        echo "<span class='error'>❌ DATABASE_URL not found. Are we in production?</span>\n";
        echo "<span class='info'>Available ENV vars: " . implode(', ', array_keys($_ENV)) . "</span>\n";
        exit;
    }
    
    echo "<span class='success'>✅ DATABASE_URL found</span>\n";
    
    // Include database config
    require_once __DIR__ . '/config/database-render.php';
    
    $db = new Database();
    echo "<span class='success'>✅ Database connection established</span>\n";
    
    // Check if we're using PostgreSQL
    if (!$db->isPostgreSQL()) {
        echo "<span class='error'>❌ Expected PostgreSQL, but got different database type</span>\n";
        exit;
    }
    
    echo "<span class='success'>✅ PostgreSQL database confirmed</span>\n";
    
    // Create tables if they don't exist
    echo "\n<span class='info'>📋 Setting up database schema...</span>\n";
    
    // Users table
    $sql_users = "
    CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('admin', 'user')),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $db->query($sql_users);
    echo "<span class='success'>✅ Users table created/verified</span>\n";
    
    // Tasks table
    $sql_tasks = "
    CREATE TABLE IF NOT EXISTS tasks (
        id SERIAL PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        description TEXT,
        assigned_to INTEGER REFERENCES users(id) ON DELETE SET NULL,
        created_by INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'in_progress', 'completed')),
        deadline DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $db->query($sql_tasks);
    echo "<span class='success'>✅ Tasks table created/verified</span>\n";
    
    // Check if admin user exists
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $stmt->execute();
    $adminCount = $stmt->fetch()['count'];
    
    if ($adminCount == 0) {
        echo "\n<span class='info'>👤 Creating admin user...</span>\n";
        
        // Create admin user
        $adminName = "System Administrator";
        $adminEmail = "admin@cytonn.com";
        $adminPassword = "CytonnAdmin2025!"; // Strong default password
        $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
        $result = $stmt->execute([$adminName, $adminEmail, $hashedPassword]);
        
        if ($result) {
            echo "<span class='success'>✅ Admin user created successfully!</span>\n";
            echo "<span class='info'>📧 Email: {$adminEmail}</span>\n";
            echo "<span class='info'>🔑 Password: {$adminPassword}</span>\n";
            echo "<span class='info'>🔗 Login URL: " . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://{$_SERVER['HTTP_HOST']}/index.php</span>\n";
        } else {
            echo "<span class='error'>❌ Failed to create admin user</span>\n";
        }
    } else {
        echo "\n<span class='info'>👤 Admin user already exists ({$adminCount} admin(s) found)</span>\n";
        
        // Show existing admins
        $stmt = $db->prepare("SELECT name, email FROM users WHERE role = 'admin'");
        $stmt->execute();
        $admins = $stmt->fetchAll();
        
        foreach ($admins as $admin) {
            echo "<span class='info'>   - {$admin['name']} ({$admin['email']})</span>\n";
        }
    }
    
    // Show all users
    echo "\n<span class='info'>📊 Database Summary:</span>\n";
    
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $userCount = $stmt->fetch()['count'];
    
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM tasks");
    $stmt->execute();
    $taskCount = $stmt->fetch()['count'];
    
    echo "<span class='info'>   Users: {$userCount}</span>\n";
    echo "<span class='info'>   Tasks: {$taskCount}</span>\n";
    
    echo "\n<span class='success'>🎉 Production setup completed successfully!</span>\n";
    echo "<span class='info'>You can now access your application.</span>\n";
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Error: " . $e->getMessage() . "</span>\n";
    echo "<span class='error'>Stack trace: " . $e->getTraceAsString() . "</span>\n";
}
?>
            </div>
            
            <div class="text-center">
                <a href="index.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-sign-in-alt"></i> Go to Login
                </a>
                <a href="dashboard.php" class="btn btn-outline-primary btn-lg ms-2">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </div>
            
            <div class="alert alert-warning mt-4">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Security Note:</strong> Delete this file after setup is complete for security reasons.
            </div>
        </div>
    </div>
</body>
</html>
