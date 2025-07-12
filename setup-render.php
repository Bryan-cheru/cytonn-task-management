<?php
// Database setup for Docker deployment on Render.com
// This script automatically detects the environment and sets up the database

// Include the correct database configuration
if (isset($_ENV['DATABASE_URL']) || getenv('DATABASE_URL') || isset($_SERVER['DATABASE_URL'])) {
    // Production environment (Render.com with Docker)
    require_once __DIR__ . '/config/database-docker.php';
} else {
    // Local development
    require_once __DIR__ . '/config/database.php';
}

function setupDatabase($db) {
    try {
        return $db->setupDatabase();
    } catch (Exception $e) {
        error_log("Database setup error: " . $e->getMessage());
        return false;
    }
}

// Auto-setup for Render.com deployment
if ((isset($_ENV['DATABASE_URL']) || getenv('DATABASE_URL') || isset($_SERVER['DATABASE_URL'])) && !isset($_GET['manual'])) {
    $db = new Database();
    if (setupDatabase($db)) {
        echo "Database setup completed successfully for Render.com deployment.";
    } else {
        echo "Database setup failed. Check logs for details.";
    }
    exit;
}

// Manual setup interface for local development
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Cytonn Task Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 2rem 0; }
        .setup-container { background: white; border-radius: 20px; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25); padding: 3rem; max-width: 600px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="setup-container">
            <div class="text-center mb-4">
                <h1 class="text-primary">Database Setup</h1>
                <p class="text-muted">Initialize your Cytonn Task Management System</p>
            </div>

            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <?php
                try {
                    $db = new Database();
                    if (setupDatabase($db)) {
                        echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Database setup completed successfully!</div>';
                        echo '<div class="text-center"><a href="public/" class="btn btn-primary">Access Application</a></div>';
                    } else {
                        echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Database setup failed!</div>';
                    }
                } catch (Exception $e) {
                    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                ?>
            <?php else: ?>
                <form method="POST">
                    <div class="alert alert-info">
                        <strong>Ready to setup the database?</strong><br>
                        This will create the necessary tables and insert default data.
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Setup Database</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
