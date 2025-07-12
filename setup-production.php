<?php
require_once 'config/database.php';

// Get database connection using the Database class
$db = new Database();
$pdo = $db->getConnection();

function createTables($pdo) {
    // Users table
    $userTable = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    // Tasks table
    $taskTable = "
    CREATE TABLE IF NOT EXISTS tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        description TEXT,
        assigned_to INT,
        created_by INT NOT NULL,
        status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
        priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
        due_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($userTable);
    $pdo->exec($taskTable);
    
    return true;
}

function createAdminUser($pdo, $name, $email, $password) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
    return $stmt->execute([$name, $email, $hashedPassword]);
}

// HTML Interface
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
            max-width: 600px;
            margin: 0 auto;
        }
        
        .setup-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .setup-header h1 {
            color: #2563eb;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            padding: 1rem;
        }
        
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
        }
        
        .success-message {
            background: #dcfce7;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
        }
        
        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="setup-container">
            <div class="setup-header">
                <i class="fas fa-cogs fa-3x text-primary mb-3"></i>
                <h1>Production Setup</h1>
                <p class="text-muted">Initialize your Cytonn Task Management System</p>
            </div>

            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    // Use the existing PDO connection
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    // Create tables
                    if (createTables($pdo)) {
                        echo '<div class="success-message"><i class="fas fa-check-circle"></i> Database tables created successfully!</div>';
                        
                        // Create admin user
                        if (createAdminUser($pdo, $_POST['admin_name'], $_POST['admin_email'], $_POST['admin_password'])) {
                            echo '<div class="success-message"><i class="fas fa-user-shield"></i> Admin user created successfully!</div>';
                            echo '<div class="text-center mt-4">';
                            echo '<a href="public/" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Go to Login</a>';
                            echo '</div>';
                        } else {
                            echo '<div class="error-message"><i class="fas fa-exclamation-triangle"></i> Failed to create admin user. Email might already exist.</div>';
                        }
                    }
                    
                } catch (PDOException $e) {
                    echo '<div class="error-message"><i class="fas fa-exclamation-triangle"></i> Database Error: ' . $e->getMessage() . '</div>';
                }
            } else {
            ?>

            <form method="POST">
                <div class="mb-4">
                    <h5><i class="fas fa-user-shield"></i> Create Administrator Account</h5>
                    <p class="text-muted small">This will be your primary admin account to manage the system.</p>
                </div>

                <div class="mb-3">
                    <label for="admin_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="admin_name" name="admin_name" required>
                </div>

                <div class="mb-3">
                    <label for="admin_email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="admin_email" name="admin_email" required>
                </div>

                <div class="mb-3">
                    <label for="admin_password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="admin_password" name="admin_password" required minlength="6">
                    <div class="form-text">Minimum 6 characters</div>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-rocket"></i> Initialize System
                    </button>
                </div>
            </form>

            <script>
                document.querySelector('form').addEventListener('submit', function(e) {
                    const password = document.getElementById('admin_password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;
                    
                    if (password !== confirmPassword) {
                        e.preventDefault();
                        alert('Passwords do not match!');
                    }
                });
            </script>

            <?php } ?>
        </div>
    </div>
</body>
</html>
