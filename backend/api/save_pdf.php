<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['resume_id']) || empty($_POST['resume_id'])) {
    echo json_encode(['success' => false, 'message' => 'Resume ID required']);
    exit;
}

$resume_id = (int)$_POST['resume_id'];

// Check if file was uploaded
if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No PDF file uploaded']);
    exit;
}

$pdf_file = $_FILES['pdf_file'];

// Validate file type
$allowed_types = ['application/pdf'];
$file_type = mime_content_type($pdf_file['tmp_name']);
if (!in_array($file_type, $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only PDF files are allowed.']);
    exit;
}

// Validate file size (5MB max)
$max_size = 5 * 1024 * 1024;
if ($pdf_file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File size too large. Maximum size is 5MB.']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if resume exists
    $query = "SELECT id FROM resumes WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $resume_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Resume not found']);
        exit;
    }
    
    // Create uploads directory if it doesn't exist
    $upload_dir = __DIR__ . '/../../uploads/resumes/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $file_extension = pathinfo($pdf_file['name'], PATHINFO_EXTENSION);
    $unique_filename = 'resume_' . $resume_id . '_' . time() . '.' . $file_extension;
    $file_path = $upload_dir . $unique_filename;
    
    // Move uploaded file
    if (move_uploaded_file($pdf_file['tmp_name'], $file_path)) {
        // Update resume record with PDF info
        $query = "UPDATE resumes SET 
                  pdf_file_path = :file_path,
                  pdf_file_size = :file_size,
                  updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':file_path', $unique_filename);
        $stmt->bindParam(':file_size', $pdf_file['size']);
        $stmt->bindParam(':id', $resume_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Also save to pdf_files table
        $query = "INSERT INTO pdf_files (resume_id, file_name, file_path, file_size) 
                  VALUES (:resume_id, :file_name, :file_path, :file_size)
                  ON DUPLICATE KEY UPDATE 
                  file_name = :file_name,
                  file_path = :file_path,
                  file_size = :file_size,
                  download_count = download_count + 1";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':resume_id', $resume_id, PDO::PARAM_INT);
        $stmt->bindParam(':file_name', $pdf_file['name']);
        $stmt->bindParam(':file_path', $unique_filename);
        $stmt->bindParam(':file_size', $pdf_file['size']);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'PDF saved successfully',
            'file_path' => $unique_filename,
            'file_size' => formatFileSize($pdf_file['size'])
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save PDF file']);
    }
    
} catch(PDOException $e) {
    error_log("PDF save error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch(Exception $e) {
    error_log("PDF save error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>