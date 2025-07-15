<?php
// Simple Railway health check - no database dependencies
header('Content-Type: text/plain');
header('Access-Control-Allow-Origin: *');

// Just respond with OK - no database checks
http_response_code(200);
echo "OK";
exit;
?>
