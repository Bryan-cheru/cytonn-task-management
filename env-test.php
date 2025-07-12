<?php
// Simple environment variable test for Render.com
header('Content-Type: text/plain');

echo "=== ENVIRONMENT VARIABLE DETECTION TEST ===\n\n";

echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n\n";

echo "=== DATABASE_URL Detection ===\n";
echo "\$_ENV['DATABASE_URL']: " . (isset($_ENV['DATABASE_URL']) ? "Found (length: " . strlen($_ENV['DATABASE_URL']) . ")" : "Not found") . "\n";
echo "getenv('DATABASE_URL'): " . (getenv('DATABASE_URL') ? "Found (length: " . strlen(getenv('DATABASE_URL')) . ")" : "Not found") . "\n";
echo "\$_SERVER['DATABASE_URL']: " . (isset($_SERVER['DATABASE_URL']) ? "Found (length: " . strlen($_SERVER['DATABASE_URL']) . ")" : "Not found") . "\n\n";

echo "=== ALL ENVIRONMENT VARIABLES ===\n";
$env_vars = [];
foreach ($_ENV as $key => $value) {
    if (strpos($key, 'DATABASE') !== false || strpos($key, 'EMAIL') !== false) {
        $env_vars[$key] = strlen($value) . " chars";
    }
}

foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'DATABASE') !== false || strpos($key, 'EMAIL') !== false) {
        $env_vars[$key] = strlen($value) . " chars";
    }
}

if (empty($env_vars)) {
    echo "No DATABASE or EMAIL related environment variables found.\n";
} else {
    foreach ($env_vars as $key => $info) {
        echo "$key: $info\n";
    }
}

echo "\n=== PHP EXTENSIONS ===\n";
echo "PDO: " . (extension_loaded('pdo') ? 'Loaded' : 'NOT LOADED') . "\n";
echo "PDO PostgreSQL: " . (extension_loaded('pdo_pgsql') ? 'Loaded' : 'NOT LOADED') . "\n";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? 'Loaded' : 'NOT LOADED') . "\n";

echo "\n=== INCLUDE PATH TEST ===\n";
echo "Current working directory: " . getcwd() . "\n";
echo "Config directory exists: " . (is_dir(__DIR__ . '/config') ? 'Yes' : 'No') . "\n";
echo "database-docker.php exists: " . (file_exists(__DIR__ . '/config/database-docker.php') ? 'Yes' : 'No') . "\n";
echo "database.php exists: " . (file_exists(__DIR__ . '/config/database.php') ? 'Yes' : 'No') . "\n";
?>
