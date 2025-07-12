<?php
require_once 'config/database.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Database Reset - Cytonn Task Management</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
        <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>
        <style>
            body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 2rem 0; }
            .reset-container { background: white; border-radius: 20px; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25); padding: 3rem; max-width: 600px; margin: 0 auto; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='reset-container'>
                <div class='text-center mb-4'>
                    <i class='fas fa-trash-alt fa-3x text-danger mb-3'></i>
                    <h1 class='text-danger'>Database Reset</h1>
                    <p class='text-muted'>Clearing all demo data...</p>
                </div>";
    
    // Drop existing tables
    $pdo->exec("DROP TABLE IF EXISTS tasks");
    echo "<div class='alert alert-warning'><i class='fas fa-check'></i> Tasks table dropped</div>";
    
    $pdo->exec("DROP TABLE IF EXISTS users");
    echo "<div class='alert alert-warning'><i class='fas fa-check'></i> Users table dropped</div>";
    
    echo "<div class='alert alert-success'><i class='fas fa-check-circle'></i> Database reset complete!</div>";
    echo "<div class='text-center'><a href='setup-production.php' class='btn btn-primary'><i class='fas fa-rocket'></i> Setup Production System</a></div>";
    echo "</div></div></body></html>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}
?>
