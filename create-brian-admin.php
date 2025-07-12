<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Brian's Admin Account - Cytonn Task Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }
        .setup-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            padding: 3rem;
            max-width: 600px;
            margin: 2rem auto;
        }
        .logo {
            color: #667eea;
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 2rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
        }
        .alert {
            border-radius: 10px;
        }
        .code-block {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="setup-container">
            <div class="logo">
                <i class="fas fa-user-shield"></i>
                <h2 class="mt-2">Create Brian's Admin Account</h2>
                <p class="text-muted">Setting up your administrator access</p>
            </div>

<?php
// Create Specific Admin User through Web Interface
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    try {
        // Database connection
        if (isset($_ENV['DATABASE_URL']) || getenv('DATABASE_URL') || isset($_SERVER['DATABASE_URL'])) {
            require_once __DIR__ . '/config/database-docker.php';
        } else {
            require_once __DIR__ . '/config/database.php';
        }
        
        $db = new Database();
        
        // Admin credentials
        $name = "Brian Cheruiyot";
        $email = "briancheruiyot501@gmail.com";
        $password = "@Bryan2213";
        
        // Check if user already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $existingUser = $stmt->fetch();
        
        if ($existingUser) {
            // Update existing user to admin with new password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ?, role = 'admin', name = ? WHERE email = ?");
            $result = $stmt->execute([$hashedPassword, $name, $email]);
            
            if ($result) {
                $success = "User updated successfully! Password has been reset.";
            } else {
                $error = "Failed to update user";
            }
        } else {
            // Create new admin user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, 'admin', NOW())");
            $result = $stmt->execute([$name, $email, $hashedPassword]);
            
            if ($result) {
                $success = "Admin user created successfully!";
            } else {
                $error = "Failed to create admin user";
            }
        }
        
        // Verify the user was created/updated
        if (isset($success)) {
            $stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
    } catch (Exception $e) {
        $error = "Database error: " . $e->getMessage();
    }
} else {
    // Check database connection and current state
    try {
        if (isset($_ENV['DATABASE_URL']) || getenv('DATABASE_URL') || isset($_SERVER['DATABASE_URL'])) {
            require_once __DIR__ . '/config/database-docker.php';
        } else {
            require_once __DIR__ . '/config/database.php';
        }
        
        $db = new Database();
        $dbConnected = true;
        
        // Check if user already exists
        $stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE email = ?");
        $stmt->execute(["briancheruiyot501@gmail.com"]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        $dbConnected = false;
        $dbError = $e->getMessage();
    }
}
?>

            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>Success!</strong>
                    <p class="mb-0 mt-2"><?= htmlspecialchars($success) ?></p>
                    
                    <?php if (isset($user)): ?>
                        <div class="mt-3">
                            <h6>User Details:</h6>
                            <div class="code-block">
                                ID: <?= $user['id'] ?><br>
                                Name: <?= htmlspecialchars($user['name']) ?><br>
                                Email: <?= htmlspecialchars($user['email']) ?><br>
                                Role: <?= $user['role'] ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-3">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-key"></i> Login Credentials:</h6>
                            <div class="code-block">
                                <strong>Email:</strong> briancheruiyot501@gmail.com<br>
                                <strong>Password:</strong> @Bryan2213
                            </div>
                        </div>
                        <a href="/index.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login Now
                        </a>
                    </div>
                </div>
            <?php elseif (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <strong>Error</strong>
                    <p class="mb-0 mt-2"><?= htmlspecialchars($error) ?></p>
                </div>
            <?php else: ?>
                <?php if (!$dbConnected): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <strong>Database Connection Failed</strong>
                        <p class="mb-0 mt-2"><?= htmlspecialchars($dbError) ?></p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-database"></i>
                        <strong>Database Connected</strong>
                        <p class="mb-0 mt-2">Ready to create your admin account.</p>
                    </div>
                    
                    <?php if ($existingUser): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-user"></i>
                            <strong>User Already Exists</strong>
                            <p class="mb-0 mt-2">A user with email "briancheruiyot501@gmail.com" already exists. Clicking "Create Admin" will update their password and role.</p>
                            <div class="mt-2">
                                <small>Current Role: <?= htmlspecialchars($existingUser['role']) ?></small>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-primary">
                        <h6><i class="fas fa-user-plus"></i> Admin Account Details:</h6>
                        <div class="code-block">
                            <strong>Name:</strong> Brian Cheruiyot<br>
                            <strong>Email:</strong> briancheruiyot501@gmail.com<br>
                            <strong>Password:</strong> @Bryan2213<br>
                            <strong>Role:</strong> admin
                        </div>
                    </div>
                    
                    <form method="POST" action="">
                        <button type="submit" name="create_admin" class="btn btn-primary w-100">
                            <i class="fas fa-user-shield"></i> 
                            <?= $existingUser ? 'Update User to Admin' : 'Create Admin Account' ?>
                        </button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
