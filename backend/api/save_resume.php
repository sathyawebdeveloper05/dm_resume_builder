<?php
require_once '../config/database.php';
require_once '../config/config.php';
// save_resume.php - DEBUGGED VERSION

// Enable error reporting FIRST
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set JSON headers
header('Content-Type: application/json; charset=utf-8');

// Simple database connection with better error handling
function connectDB() {
    $host = 'localhost';
    $dbname = 'dm_resume_builder';
    $username = 'root';
    $password = '';
    
    // First, try to connect to MySQL server (without database)
    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Check if database exists
        $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
        $dbExists = $stmt->fetch();
        
        if (!$dbExists) {
            // Create database
            $pdo->exec("CREATE DATABASE $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
        
        // Now connect to the specific database
        $pdo->exec("USE $dbname");
        
        // Create tables if they don't exist
        createTables($pdo);
        
        return $pdo;
        
    } catch(PDOException $e) {
        // Return detailed error for debugging
        error_log("Database Error: " . $e->getMessage());
        return null;
    }
}

function createTables($pdo) {
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        email VARCHAR(255) UNIQUE NOT NULL,
        full_name VARCHAR(255),
        phone VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Create resumes table
    $pdo->exec("CREATE TABLE IF NOT EXISTS resumes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        resume_name VARCHAR(255) NOT NULL,
        personal_info TEXT,
        education TEXT,
        experience TEXT,
        projects TEXT,
        skills TEXT,
        certifications TEXT,
        template_used VARCHAR(50) DEFAULT 'template-1',
        font_family VARCHAR(100),
        primary_color VARCHAR(20),
        text_align VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    error_log("Tables created/verified successfully");
}

// Main processing
try {
    // Log the request for debugging
    error_log("Received request: " . $_SERVER['REQUEST_METHOD']);
    
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method is allowed. Received: ' . $_SERVER['REQUEST_METHOD']);
    }
    
    // Get POST data
    $input = file_get_contents('php://input');
    if (empty($input)) {
        throw new Exception('No data received in request body');
    }
    
    // Log raw input for debugging
    error_log("Raw input (first 500 chars): " . substr($input, 0, 500));
    
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg() . ' - Input: ' . substr($input, 0, 200));
    }
    
    // Log decoded data
    error_log("Decoded data keys: " . implode(', ', array_keys($data)));
    
    // Basic validation
    if (empty($data['resume_name'])) {
        throw new Exception('Resume name is required');
    }
    
    if (empty($data['personal_info']['email'])) {
        throw new Exception('Email is required in personal_info');
    }
    
    // Connect to database
    error_log("Attempting database connection...");
    $pdo = connectDB();
    
    if (!$pdo) {
        // More detailed error
        throw new Exception('Database connection failed. Check if MySQL is running and credentials are correct.');
    }
    
    error_log("Database connected successfully");
    
    // Start transaction
    $pdo->beginTransaction();
    
    // 1. Handle user
    $email = trim($data['personal_info']['email']);
    $full_name = trim($data['personal_info']['full_name'] ?? '');
    $phone = trim($data['personal_info']['phone'] ?? '');
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        $user_id = $user['id'];
        // Update user
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$full_name, $phone, $user_id]);
        error_log("Updated existing user ID: $user_id");
    } else {
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (email, full_name, phone) VALUES (?, ?, ?)");
        $stmt->execute([$email, $full_name, $phone]);
        $user_id = $pdo->lastInsertId();
        error_log("Created new user ID: $user_id");
    }
    
    // 2. Save resume
    $stmt = $pdo->prepare("
        INSERT INTO resumes (
            user_id, resume_name, personal_info, education, experience,
            projects, skills, certifications, template_used,
            font_family, primary_color, text_align
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            personal_info = VALUES(personal_info),
            education = VALUES(education),
            experience = VALUES(experience),
            projects = VALUES(projects),
            skills = VALUES(skills),
            certifications = VALUES(certifications),
            template_used = VALUES(template_used),
            font_family = VALUES(font_family),
            primary_color = VALUES(primary_color),
            text_align = VALUES(text_align),
            updated_at = NOW()
    ");
    
    $params = [
        $user_id,
        $data['resume_name'],
        json_encode($data['personal_info'] ?? [], JSON_UNESCAPED_UNICODE),
        json_encode($data['education'] ?? [], JSON_UNESCAPED_UNICODE),
        json_encode($data['experience'] ?? [], JSON_UNESCAPED_UNICODE),
        json_encode($data['projects'] ?? [], JSON_UNESCAPED_UNICODE),
        json_encode($data['skills'] ?? [], JSON_UNESCAPED_UNICODE),
        json_encode($data['certifications'] ?? [], JSON_UNESCAPED_UNICODE),
        $data['template'] ?? 'template-1',
        $data['font_family'] ?? 'Arial, sans-serif',
        $data['primary_color'] ?? '#007bff',
        $data['text_align'] ?? 'left'
    ];
    
    $stmt->execute($params);
    $resume_id = $pdo->lastInsertId();
    
    // Commit transaction
    $pdo->commit();
    
    error_log("Resume saved successfully. Resume ID: $resume_id, User ID: $user_id");
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Resume saved successfully!',
        'resume_id' => $resume_id,
        'user_id' => $user_id,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Rollback if in transaction
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    // Log the error
    error_log("Error in save_resume.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'error' => 'SERVER_ERROR',
        'debug_info' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
        ]
    ], JSON_UNESCAPED_UNICODE);
}

// Flush output
ob_flush();
flush();
?>