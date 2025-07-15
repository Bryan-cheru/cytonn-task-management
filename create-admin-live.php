<?php
// Simple Admin Creator for Live Website
// Visit: https://cytonn-task-management.onrender.com/create-admin-live.php
// Use this to create admin accounts on your live website

header('Content-Type: text/html; charset=UTF-8');

$message = '';
$admin_created = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_once __DIR__ . '/config/database-unified.php';
        require_once __DIR__ . '/app/Models/User.php';
        
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($name) || empty($email) || empty($password)) {
            $message = "All fields are required.";
        } elseif ($password !== $confirm_password) {
            $message = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $message = "Password must be at least 6 characters.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email format.";
        } else {
            $userModel = new User();
            
            // Check if user already exists
            $existingUser = $userModel->getByEmail($email);
            if ($existingUser) {
                $message = "User with this email already exists.";
            } else {
                // Create admin user
                $result = $userModel->create([
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'role' => 'admin'
                ]);
                
                if ($result) {
                    $admin_created = true;
                    $message = "Admin user created successfully! You can now login.";
                } else {
                    $message = "Failed to create admin user. Please try again.";
                }
            }
        }
        
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin User - Cytonn Task Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
            padding: 2rem 0; 
        }
        .admin-container { 
            background: white; 
            border-radius: 20px; 
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25); 
            padding: 3rem; 
            max-width: 500px; 
            margin: 0 auto; 
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-admin {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            transition: transform 0.2s;
        }
        .btn-admin:hover {
            transform: translateY(-2px);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="admin-container">
            <div class="text-center mb-4">
                <h1><i class="fas fa-user-shield text-primary"></i></h1>
                <h2>Create Admin User</h2>
                <p class="text-muted">Create an administrator account for the task management system</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $admin_created ? 'success' : 'danger'; ?> mb-4">
                    <i class="fas fa-<?php echo $admin_created ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (!$admin_created): ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <i class="fas fa-user"></i> Full Name
                        </label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                               placeholder="Enter your full name" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i> Email Address
                        </label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                               placeholder="Enter your email address" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Enter password (min 6 characters)" required>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-lock"></i> Confirm Password
                        </label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               placeholder="Confirm your password" required>
                    </div>

                    <button type="submit" class="btn btn-admin btn-lg w-100 mb-3">
                        <i class="fas fa-user-plus"></i> Create Admin Account
                    </button>
                </form>
            <?php else: ?>
                <div class="text-center">
                    <a href="index.php" class="btn btn-success btn-lg">
                        <i class="fas fa-sign-in-alt"></i> Go to Login Page
                    </a>
                </div>
            <?php endif; ?>

            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="fas fa-shield-alt"></i> This will create an administrator account with full system access
                </small>
            </div>

            <div class="alert alert-warning mt-4">
                <small>
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Security Note:</strong> Delete this file after creating your admin account for security reasons.
                </small>
            </div>
        </div>
    </div>
</body>
</html>
