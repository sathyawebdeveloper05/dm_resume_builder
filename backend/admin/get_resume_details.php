<?php
session_start();
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Resume ID is required']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$id = (int)$_GET['id'];

try {
    // Get resume details
    $query = "SELECT r.*, u.email, u.full_name, u.phone 
              FROM resumes r 
              JOIN users u ON r.user_id = u.id 
              WHERE r.id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $resume = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resume) {
        echo json_encode(['success' => false, 'message' => 'Resume not found']);
        exit;
    }
    
    // Prepare user data
    $userData = [
        'full_name' => $resume['full_name'],
        'email' => $resume['email'],
        'phone' => $resume['phone']
    ];
    
    // Remove user columns from resume data
    unset($resume['full_name'], $resume['email'], $resume['phone']);
    
    echo json_encode([
        'success' => true,
        'resume' => $resume,
        'user' => $userData
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
exit;