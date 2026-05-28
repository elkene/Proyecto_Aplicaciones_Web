<?php
header('Content-Type: application/json');

// Test 1: PHP is working
echo json_encode([
    'test' => 'PHP is working',
    'php_version' => phpversion(),
    'timestamp' => date('Y-m-d H:i:s')
]);
?>
