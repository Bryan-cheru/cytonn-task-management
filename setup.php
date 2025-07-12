<?php
// Redirect to production setup
header('Location: setup-production.php');
exit();
?>

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Setup - Cytonn Task Management System</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>
    <style>
        body { background-color: #f8f9fa; }
        .setup-container { max-width: 800px; margin: 2rem auto; }
        .log-output { background: #1e293b; color: #e2e8f0; padding: 1rem; border-radius: 0.5rem; font-family: monospace; max-height: 400px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class='setup-container'>
        <div class='card'>
            <div class='card-header bg-primary text-white'>
                <h3 class='mb-0'><i class='fas fa-database me-2'></i>Database Setup</h3>
            </div>
            <div class='card-body'>
                <div class='log-output' id='setupLog'>";

function logMessage($message, $type = 'info') {
    $icon = $type === 'success' ? 'check-circle' : ($type === 'error' ? 'exclamation-triangle' : 'info-circle');
    $color = $type === 'success' ? '#10b981' : ($type === 'error' ? '#ef4444' : '#3b82f6');
    echo "<div style='color: $color; margin-bottom: 0.5rem;'><i class='fas fa-$icon'></i> $message</div>";
    flush();
}

try {
    logMessage("Starting database setup...");
    
    $db = new Database();
    $connection = $db->getConnection();
    
    logMessage("Database connection established successfully", 'success');
    
    // Create users table
    logMessage("Creating users table...");
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $connection->exec($sql);
    logMessage("Users table created successfully", 'success');
    
    // Create tasks table
    logMessage("Creating tasks table...");
    $sql = "CREATE TABLE IF NOT EXISTS tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        assigned_to INT NOT NULL,
        created_by INT NOT NULL,
        deadline DATE NOT NULL,
        status ENUM('Pending', 'In Progress', 'Completed') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
    )";
    $connection->exec($sql);
    logMessage("Tasks table created successfully", 'success');
    
    // Insert default admin user
    logMessage("Creating default admin user...");
    $adminPassword = password_hash('password', PASSWORD_DEFAULT);
    $sql = "INSERT IGNORE INTO users (name, email, password, role) VALUES 
            ('Administrator', 'admin@cytonn.com', ?, 'admin')";
    $stmt = $connection->prepare($sql);
    $stmt->execute([$adminPassword]);
    logMessage("Default admin user created: admin@cytonn.com / password", 'success');
    
    // Insert sample users
    logMessage("Creating sample users...");
    $userPassword = password_hash('password', PASSWORD_DEFAULT);
    $sql = "INSERT IGNORE INTO users (name, email, password, role) VALUES 
            ('John Doe', 'john@example.com', ?, 'user'),
            ('Jane Smith', 'jane@example.com', ?, 'user'),
            ('Alice Johnson', 'alice@example.com', ?, 'user'),
            ('Bob Wilson', 'bob@example.com', ?, 'user')";
    $stmt = $connection->prepare($sql);
    $stmt->execute([$userPassword, $userPassword, $userPassword, $userPassword]);
    logMessage("Sample users created successfully", 'success');
    
    // Insert sample tasks
    logMessage("Creating sample tasks...");
    
    // Get user IDs
    $adminId = $connection->query("SELECT id FROM users WHERE email = 'admin@cytonn.com'")->fetchColumn();
    $johnId = $connection->query("SELECT id FROM users WHERE email = 'john@example.com'")->fetchColumn();
    $janeId = $connection->query("SELECT id FROM users WHERE email = 'jane@example.com'")->fetchColumn();
    $aliceId = $connection->query("SELECT id FROM users WHERE email = 'alice@example.com'")->fetchColumn();
    
    if ($adminId && $johnId && $janeId && $aliceId) {
        $sampleTasks = [
            [
                'title' => 'Design User Interface Mockups',
                'description' => 'Create wireframes and high-fidelity mockups for the new dashboard interface. Include responsive design considerations.',
                'assigned_to' => $johnId,
                'created_by' => $adminId,
                'deadline' => date('Y-m-d', strtotime('+7 days')),
                'status' => 'In Progress'
            ],
            [
                'title' => 'Database Performance Optimization',
                'description' => 'Analyze and optimize database queries for better performance. Focus on slow-running queries and indexing strategies.',
                'assigned_to' => $janeId,
                'created_by' => $adminId,
                'deadline' => date('Y-m-d', strtotime('+14 days')),
                'status' => 'Pending'
            ],
            [
                'title' => 'User Authentication Testing',
                'description' => 'Comprehensive testing of user authentication system including edge cases and security vulnerabilities.',
                'assigned_to' => $aliceId,
                'created_by' => $adminId,
                'deadline' => date('Y-m-d', strtotime('+5 days')),
                'status' => 'Completed'
            ],
            [
                'title' => 'API Documentation Update',
                'description' => 'Update API documentation with latest endpoints and examples. Include authentication requirements and response formats.',
                'assigned_to' => $johnId,
                'created_by' => $adminId,
                'deadline' => date('Y-m-d', strtotime('+10 days')),
                'status' => 'Pending'
            ],
            [
                'title' => 'Mobile App Bug Fixes',
                'description' => 'Fix reported bugs in the mobile application including login issues and data synchronization problems.',
                'assigned_to' => $janeId,
                'created_by' => $adminId,
                'deadline' => date('Y-m-d', strtotime('-2 days')), // Overdue task
                'status' => 'In Progress'
            ],
            [
                'title' => 'Code Review and Refactoring',
                'description' => 'Review existing codebase and refactor legacy components to improve maintainability and performance.',
                'assigned_to' => $aliceId,
                'created_by' => $adminId,
                'deadline' => date('Y-m-d', strtotime('+21 days')),
                'status' => 'Pending'
            ]
        ];
        
        $sql = "INSERT IGNORE INTO tasks (title, description, assigned_to, created_by, deadline, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($sql);
        
        foreach ($sampleTasks as $task) {
            $stmt->execute([
                $task['title'],
                $task['description'],
                $task['assigned_to'],
                $task['created_by'],
                $task['deadline'],
                $task['status']
            ]);
        }
        
        logMessage("Sample tasks created successfully", 'success');
    }
    
    // Get final statistics
    $userCount = $connection->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $taskCount = $connection->query("SELECT COUNT(*) FROM tasks")->fetchColumn();
    
    logMessage("Database setup completed successfully!", 'success');
    logMessage("Total users: $userCount", 'info');
    logMessage("Total tasks: $taskCount", 'info');
    
    echo "</div>
                <div class='mt-4'>
                    <h5>Setup Summary:</h5>
                    <ul class='list-group'>
                        <li class='list-group-item d-flex justify-content-between align-items-center'>
                            Users Created
                            <span class='badge bg-primary rounded-pill'>$userCount</span>
                        </li>
                        <li class='list-group-item d-flex justify-content-between align-items-center'>
                            Tasks Created
                            <span class='badge bg-primary rounded-pill'>$taskCount</span>
                        </li>
                    </ul>
                    
                    <div class='mt-4'>
                        <h6>Login Credentials:</h6>
                        <div class='row'>
                            <div class='col-md-6'>
                                <div class='card bg-light'>
                                    <div class='card-body'>
                                        <h6 class='card-title text-danger'>Administrator</h6>
                                        <p class='card-text mb-1'><strong>Email:</strong> admin@cytonn.com</p>
                                        <p class='card-text'><strong>Password:</strong> password</p>
                                    </div>
                                </div>
                            </div>
                            <div class='col-md-6'>
                                <div class='card bg-light'>
                                    <div class='card-body'>
                                        <h6 class='card-title text-primary'>Sample Users</h6>
                                        <p class='card-text mb-1'><strong>Email:</strong> john@example.com</p>
                                        <p class='card-text mb-1'><strong>Email:</strong> jane@example.com</p>
                                        <p class='card-text'><strong>Password:</strong> password (for all)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class='mt-4 text-center'>
                        <a href='index.php' class='btn btn-primary btn-lg'>
                            <i class='fas fa-sign-in-alt me-2'></i>Go to Application
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>";
    
} catch (Exception $e) {
    logMessage("Error: " . $e->getMessage(), 'error');
    echo "</div>
                <div class='alert alert-danger mt-4'>
                    <h5>Setup Failed!</h5>
                    <p>Please check your database configuration in <code>config/database.php</code> and ensure MySQL is running.</p>
                    <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>";
}
?>
