<?php
// Database connection test and setup script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Cytonn Task Management System - Setup Test</h1>";

// Test 1: Check PHP version
echo "<h3>1. PHP Version Check</h3>";
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo "✅ PHP version: " . PHP_VERSION . " (OK)<br>";
} else {
    echo "❌ PHP version: " . PHP_VERSION . " (Requires 7.4+)<br>";
}

// Test 2: Check required extensions
echo "<h3>2. Required Extensions</h3>";
$extensions = ['pdo', 'pdo_mysql', 'json', 'session'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext extension loaded<br>";
    } else {
        echo "❌ $ext extension NOT loaded<br>";
    }
}

// Test 3: Database connection
echo "<h3>3. Database Connection Test</h3>";
try {
    require_once __DIR__ . '/config/database.php';
    $db = new Database();
    echo "✅ Database connection successful<br>";
    
    // Test if tables exist
    $tables = ['users', 'tasks'];
    foreach ($tables as $table) {
        $result = $db->fetch("SHOW TABLES LIKE '$table'");
        if ($result) {
            echo "✅ Table '$table' exists<br>";
        } else {
            echo "❌ Table '$table' does NOT exist<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    echo "<p><strong>Make sure to:</strong></p>";
    echo "<ul>";
    echo "<li>Create database 'task_management'</li>";
    echo "<li>Import schema.sql</li>";
    echo "<li>Update database credentials in config/database.php</li>";
    echo "</ul>";
}

// Test 4: File permissions
echo "<h3>4. File Permissions</h3>";
$writableDirs = ['logs'];
foreach ($writableDirs as $dir) {
    if (is_writable(__DIR__ . '/' . $dir)) {
        echo "✅ Directory '$dir' is writable<br>";
    } else {
        echo "❌ Directory '$dir' is NOT writable<br>";
    }
}

// Test 5: Model instantiation
echo "<h3>5. Model Classes Test</h3>";
try {
    require_once __DIR__ . '/app/Models/User.php';
    require_once __DIR__ . '/app/Models/Task.php';
    
    $userModel = new User();
    $taskModel = new Task();
    echo "✅ User and Task models loaded successfully<br>";
    
    // Test user count
    $users = $userModel->getAll();
    echo "✅ Found " . count($users) . " users in database<br>";
    
    // Test task count
    $tasks = $taskModel->getAll();
    echo "✅ Found " . count($tasks) . " tasks in database<br>";
    
} catch (Exception $e) {
    echo "❌ Model test failed: " . $e->getMessage() . "<br>";
}

// Test 6: Email service
echo "<h3>6. Email Service Test</h3>";
try {
    require_once __DIR__ . '/app/Services/EmailService.php';
    $emailService = new EmailService();
    echo "✅ Email service loaded successfully<br>";
    echo "📧 Email logging will be saved to: " . __DIR__ . "/logs/email.log<br>";
} catch (Exception $e) {
    echo "❌ Email service test failed: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>Setup Complete!</h3>";
echo "<p>If all tests passed, you can access the application:</p>";
echo "<ul>";
echo "<li><a href='public/index.php'>Login Page</a></li>";
echo "<li><strong>Admin:</strong> admin@cytonn.com / password</li>";
echo "<li><strong>User:</strong> john@example.com / password</li>";
echo "</ul>";

echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Configure email settings in app/Services/EmailService.php</li>";
echo "<li>Set up your web server to point to the 'public' directory</li>";
echo "<li>Test the application functionality</li>";
echo "<li>Deploy to your hosting environment</li>";
echo "</ol>";
?>
