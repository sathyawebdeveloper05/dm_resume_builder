<?php
session_start();
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if ID is provided
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Resume ID is required']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$id = (int)$_POST['id'];

try {
    // First, verify resume exists
    $checkQuery = "SELECT id FROM resumes WHERE id = :id";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Resume not found']);
        exit;
    }
    
    // Delete the resume
    $deleteQuery = "DELETE FROM resumes WHERE id = :id";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $deleteResult = $deleteStmt->execute();
    
    if ($deleteResult) {
        echo json_encode(['success' => true, 'message' => 'Resume deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete resume']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
exit;