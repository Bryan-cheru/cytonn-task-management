<?php
echo "<h1>Directory Debug</h1>";
echo "<h2>Current Directory: " . __DIR__ . "</h2>";
echo "<h3>Files in public directory:</h3>";
$files = scandir(__DIR__ . '/public');
foreach($files as $file) {
    if ($file != '.' && $file != '..') {
        echo "- <a href='public/$file'>$file</a><br>";
    }
}
echo "<h3>Direct Links:</h3>";
echo "- <a href='public/index.php'>Login Page (index.php)</a><br>";
echo "- <a href='public/test.php'>Test Page (test.php)</a><br>";
echo "- <a href='public/dashboard.php'>Dashboard (dashboard.php)</a><br>";
?>
