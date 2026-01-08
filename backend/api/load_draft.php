<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// For now, just return empty response
echo json_encode([
    'success' => false,
    'message' => 'No drafts available'
]);
exit;