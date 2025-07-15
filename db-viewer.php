<?php
// Simple Database Viewer for Live Website
// Visit: https://cytonn-task-management.onrender.com/db-viewer.php
// View your database tables and data

header('Content-Type: text/html; charset=UTF-8');

try {
    require_once __DIR__ . '/config/database-unified.php';
    $db = new Database();
    $connected = true;
} catch (Exception $e) {
    $connected = false;
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Viewer - Cytonn Task Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 2rem 0; }
        .viewer-container { max-width: 1200px; margin: 0 auto; }
        .table-container { background: white; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .code-block { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 1rem; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container viewer-container">
        <h1 class="text-center mb-4">🗄️ Database Viewer</h1>
        
        <?php if (!$connected): ?>
            <div class="alert alert-danger">
                <h5>❌ Database Connection Failed</h5>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <h5>✅ Database Connected Successfully</h5>
                <p>Connected to: <?php echo $db->isPostgreSQL() ? 'PostgreSQL' : 'MySQL'; ?></p>
            </div>

            <!-- Users Table -->
            <div class="table-container">
                <h3><i class="fas fa-users"></i> Users Table</h3>
                <?php
                try {
                    $stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC");
                    $users = $stmt->fetchAll();
                    
                    if (empty($users)) {
                        echo '<div class="alert alert-warning">No users found in database.</div>';
                    } else {
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-striped">';
                        echo '<thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Created At</th></tr></thead>';
                        echo '<tbody>';
                        foreach ($users as $user) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($user['id']) . '</td>';
                            echo '<td>' . htmlspecialchars($user['name']) . '</td>';
                            echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                            echo '<td><span class="badge bg-' . ($user['role'] === 'admin' ? 'danger' : 'primary') . '">' . htmlspecialchars($user['role']) . '</span></td>';
                            echo '<td>' . htmlspecialchars($user['created_at']) . '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table>';
                        echo '</div>';
                        echo '<p class="text-muted">Total users: ' . count($users) . '</p>';
                    }
                } catch (Exception $e) {
                    echo '<div class="alert alert-danger">Error fetching users: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                ?>
            </div>

            <!-- Tasks Table -->
            <div class="table-container">
                <h3><i class="fas fa-tasks"></i> Tasks Table</h3>
                <?php
                try {
                    $stmt = $db->query("SELECT t.*, u1.name as assigned_user, u2.name as created_by_user 
                                       FROM tasks t 
                                       LEFT JOIN users u1 ON t.assigned_to = u1.id 
                                       LEFT JOIN users u2 ON t.created_by = u2.id 
                                       ORDER BY t.created_at DESC");
                    $tasks = $stmt->fetchAll();
                    
                    if (empty($tasks)) {
                        echo '<div class="alert alert-warning">No tasks found in database.</div>';
                    } else {
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-striped">';
                        echo '<thead><tr><th>ID</th><th>Title</th><th>Status</th><th>Assigned To</th><th>Created By</th><th>Deadline</th><th>Created At</th></tr></thead>';
                        echo '<tbody>';
                        foreach ($tasks as $task) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($task['id']) . '</td>';
                            echo '<td>' . htmlspecialchars($task['title']) . '</td>';
                            echo '<td><span class="badge bg-' . getStatusColor($task['status']) . '">' . htmlspecialchars($task['status']) . '</span></td>';
                            echo '<td>' . htmlspecialchars($task['assigned_user'] ?? 'Unassigned') . '</td>';
                            echo '<td>' . htmlspecialchars($task['created_by_user'] ?? 'Unknown') . '</td>';
                            echo '<td>' . htmlspecialchars($task['deadline'] ?? 'No deadline') . '</td>';
                            echo '<td>' . htmlspecialchars($task['created_at']) . '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table>';
                        echo '</div>';
                        echo '<p class="text-muted">Total tasks: ' . count($tasks) . '</p>';
                    }
                } catch (Exception $e) {
                    echo '<div class="alert alert-danger">Error fetching tasks: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                
                function getStatusColor($status) {
                    switch ($status) {
                        case 'completed': return 'success';
                        case 'in_progress': return 'warning';
                        case 'pending': return 'secondary';
                        default: return 'light';
                    }
                }
                ?>
            </div>

            <!-- Database Info -->
            <div class="table-container">
                <h3><i class="fas fa-info-circle"></i> Database Information</h3>
                <div class="code-block">
<?php
try {
    echo "Database Type: " . ($db->isPostgreSQL() ? "PostgreSQL" : "MySQL") . "\n";
    
    if ($db->isPostgreSQL()) {
        $stmt = $db->query("SELECT version()");
        $version = $stmt->fetchColumn();
        echo "Version: " . $version . "\n";
        
        $stmt = $db->query("SELECT current_database()");
        $dbname = $stmt->fetchColumn();
        echo "Database: " . $dbname . "\n";
        
        $stmt = $db->query("SELECT current_user");
        $user = $stmt->fetchColumn();
        echo "User: " . $user . "\n";
    }
    
    // Table counts
    echo "\nTable Statistics:\n";
    $stmt = $db->query("SELECT COUNT(*) FROM users");
    echo "Users: " . $stmt->fetchColumn() . "\n";
    
    $stmt = $db->query("SELECT COUNT(*) FROM tasks");
    echo "Tasks: " . $stmt->fetchColumn() . "\n";
    
    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    echo "Admin users: " . $stmt->fetchColumn() . "\n";
    
} catch (Exception $e) {
    echo "Error getting database info: " . $e->getMessage();
}
?>
                </div>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-primary me-2">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
            <a href="create-admin-live.php" class="btn btn-success">
                <i class="fas fa-user-plus"></i> Create Admin User
            </a>
        </div>

        <div class="alert alert-warning mt-4">
            <small>
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Security Note:</strong> This page shows sensitive database information. Delete it after use.
            </small>
        </div>
    </div>
</body>
</html>
