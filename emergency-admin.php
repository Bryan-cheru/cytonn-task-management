<?php
// Emergency Admin Creation and Login Debug
// Visit: https://your-app-url.onrender.com/emergency-admin.php
// This script will create an admin user AND show login debug info

header('Content-Type: text/html; charset=UTF-8');

// Handle admin creation
$action = $_GET['action'] ?? '';
$admin_created = false;
$error_message = '';

if ($action === 'create_admin') {
    try {
        // Include the correct database configuration
        $database_url = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? getenv('DATABASE_URL') ?? null;
        
        if ($database_url) {
            require_once __DIR__ . '/config/database-render.php';
        } else {
            require_once __DIR__ . '/config/database.php';
        }
        
        $db = new Database();
        
        // Create tables first (in case they don't exist)
        if ($db->isPostgreSQL()) {
            // PostgreSQL schema
            $db->query("
                CREATE TABLE IF NOT EXISTS users (
                    id SERIAL PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('admin', 'user')),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
        } else {
            // MySQL schema
            $db->query("
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    role ENUM('admin', 'user') DEFAULT 'user',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
            ");
        }
        
        // Check if admin already exists
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute(['admin@cytonn.com']);
        $exists = $stmt->fetchColumn();
        
        if ($exists == 0) {
            // Create admin user
            $name = "System Administrator";
            $email = "admin@cytonn.com";
            $password = "admin123"; // Simple password for testing
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
            $result = $stmt->execute([$name, $email, $hashedPassword]);
            
            if ($result) {
                $admin_created = true;
            } else {
                $error_message = "Failed to create admin user";
            }
        } else {
            $error_message = "Admin user already exists";
        }
        
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Handle login test
$login_test_result = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_login'])) {
    try {
        require_once __DIR__ . '/app/Models/User.php';
        
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $userModel = new User();
        $user = $userModel->verifyCredentials($email, $password);
        
        if ($user) {
            $login_test_result = "✅ LOGIN SUCCESS! User: {$user['name']} ({$user['role']})";
        } else {
            // Debug the failed login
            $userFromDB = $userModel->getByEmail($email);
            if ($userFromDB) {
                $login_test_result = "❌ Password incorrect for user: {$userFromDB['name']}";
            } else {
                $login_test_result = "❌ User with email '{$email}' not found in database";
            }
        }
    } catch (Exception $e) {
        $login_test_result = "❌ Error during login test: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚨 Emergency Admin Setup - Cytonn Task Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%); min-height: 100vh; padding: 2rem 0; }
        .emergency-container { background: white; border-radius: 15px; box-shadow: 0 20px 40px rgba(0,0,0,0.3); padding: 3rem; max-width: 800px; margin: 0 auto; }
        .code-block { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 1rem; font-family: monospace; white-space: pre-wrap; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="emergency-container">
            <div class="text-center mb-4">
                <h1><i class="fas fa-exclamation-triangle text-danger"></i> Emergency Admin Setup</h1>
                <p class="text-muted">Fix login issues and create admin user</p>
            </div>

            <!-- Database Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-database"></i> Database Status</h5>
                </div>
                <div class="card-body">
                    <div class="code-block">
<?php
try {
    // Include the correct database configuration
    $database_url = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? getenv('DATABASE_URL') ?? null;
    
    if ($database_url) {
        require_once __DIR__ . '/config/database-render.php';
        echo "✅ Using Render.com PostgreSQL database\n";
    } else {
        require_once __DIR__ . '/config/database.php';
        echo "✅ Using local MySQL database\n";
    }
    
    $db = new Database();
    echo "✅ Database connection established\n";
    
    // Check users table
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM users");
        $userCount = $stmt->fetchColumn();
        echo "✅ Users table exists with {$userCount} users\n";
        
        $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $adminCount = $stmt->fetchColumn();
        echo "✅ Admin users: {$adminCount}\n";
        
        if ($adminCount > 0) {
            $stmt = $db->query("SELECT name, email FROM users WHERE role = 'admin'");
            $admins = $stmt->fetchAll();
            echo "   Available admin accounts:\n";
            foreach ($admins as $admin) {
                echo "   - {$admin['name']} ({$admin['email']})\n";
            }
        } else {
            echo "⚠️  No admin users found! Click 'Create Admin User' below.\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Users table doesn't exist or error: " . $e->getMessage() . "\n";
        echo "   Click 'Create Admin User' to create the table and admin user.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}
?>
                    </div>
                </div>
            </div>

            <!-- Admin Creation -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-user-shield"></i> Admin User Creation</h5>
                </div>
                <div class="card-body">
                    <?php if ($admin_created): ?>
                        <div class="alert alert-success">
                            <h6 class="success">✅ Admin User Created Successfully!</h6>
                            <p><strong>Email:</strong> admin@cytonn.com<br>
                            <strong>Password:</strong> admin123</p>
                            <p>You can now login using these credentials.</p>
                        </div>
                    <?php elseif ($error_message): ?>
                        <div class="alert alert-warning">
                            <p class="warning"><?php echo htmlspecialchars($error_message); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <a href="?action=create_admin" class="btn btn-danger btn-lg">
                        <i class="fas fa-user-plus"></i> Create Admin User
                    </a>
                    <small class="text-muted d-block mt-2">
                        This will create an admin user with email: admin@cytonn.com and password: admin123
                    </small>
                </div>
            </div>

            <!-- Login Test -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-sign-in-alt"></i> Test Login</h5>
                </div>
                <div class="card-body">
                    <?php if ($login_test_result): ?>
                        <div class="alert alert-<?php echo strpos($login_test_result, '✅') !== false ? 'success' : 'danger'; ?>">
                            <?php echo htmlspecialchars($login_test_result); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="email" name="email" class="form-control" placeholder="Email" value="admin@cytonn.com" required>
                            </div>
                            <div class="col-md-4">
                                <input type="password" name="password" class="form-control" placeholder="Password" value="admin123" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" name="test_login" class="btn btn-primary w-100">
                                    <i class="fas fa-vial"></i> Test Login
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="text-center">
                <a href="index.php" class="btn btn-success btn-lg me-2">
                    <i class="fas fa-sign-in-alt"></i> Go to Login Page
                </a>
                <a href="debug-render.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-bug"></i> Full Debug Info
                </a>
            </div>

            <div class="alert alert-info mt-4">
                <h6><i class="fas fa-info-circle"></i> Quick Fix Steps:</h6>
                <ol>
                    <li>Click "Create Admin User" above</li>
                    <li>Use the "Test Login" form to verify it works</li>
                    <li>Go to the login page and use: <code>admin@cytonn.com</code> / <code>admin123</code></li>
                    <li>Delete this file after successful login for security</li>
                </ol>
            </div>
        </div>
    </div>
</body>
</html>
