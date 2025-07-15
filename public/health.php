<?php
// Simple health check endpoint for Railway
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Check if basic PHP is working
    $status = [
        'status' => 'healthy',
        'timestamp' => date('Y-m-d H:i:s'),
        'php_version' => phpversion(),
        'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown'
    ];
    
    // Try to check database connection
    try {
        require_once __DIR__ . '/config/database-unified.php';
        $status['database'] = 'connected';
    } catch (Exception $e) {
        $status['database'] = 'error: ' . $e->getMessage();
    }
    
    http_response_code(200);
    echo json_encode($status, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}
?>
