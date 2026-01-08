<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Resume ID required']);
    exit;
}

$resume_id = (int)$_GET['id'];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get resume data
    $query = "SELECT r.*, u.email, u.full_name, u.phone 
              FROM resumes r 
              JOIN users u ON r.user_id = u.id 
              WHERE r.id = :id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $resume_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Resume not found']);
        exit;
    }
    
    $resume = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Decode JSON fields
    $resume['personal_info'] = json_decode($resume['personal_info'], true);
    $resume['education'] = json_decode($resume['education'], true);
    $resume['experience'] = json_decode($resume['experience'], true);
    $resume['projects'] = json_decode($resume['projects'], true);
    $resume['skills'] = json_decode($resume['skills'], true);
    $resume['certifications'] = json_decode($resume['certifications'], true);
    
    // Remove sensitive data
    unset($resume['user_id']);
    
    echo json_encode([
        'success' => true,
        'resume' => $resume
    ]);
    
} catch(PDOException $e) {
    error_log("Get resume error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>