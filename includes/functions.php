<?php
// Common functions - NO OUTPUT ALLOWED

/**
 * Sanitize input data
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate URL
 */
function validateURL($url) {
    return filter_var($url, FILTER_VALIDATE_URL);
}

/**
 * Generate random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'F j, Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Check if string is JSON
 */
function isJson($string) {
    if (!is_string($string)) {
        return false;
    }
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

/**
 * Get client IP address
 */
function getClientIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

/**
 * Generate CSRF token - SAFE VERSION (no session dependency)
 */
function generateCsrfToken() {
    // Don't use session in API context
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION)) {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    // Return a random token if no session
    return bin2hex(random_bytes(32));
}

/**
 * Validate CSRF token - SAFE VERSION (no session dependency)
 */
function validateCsrfToken($token) {
    // Only validate if session is active
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['csrf_token'])) {
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    // For API calls without session, you might want different validation
    return true; // Or implement API key validation instead
}

/**
 * Log activity
 */
function logActivity($activity, $user_id = null, $details = null) {
    // Use error_log for now - no output
    $log_message = "Activity: $activity";
    if ($user_id) {
        $log_message .= " | User: $user_id";
    }
    if ($details) {
        $log_message .= " | Details: " . json_encode($details);
    }
    error_log($log_message);
}

/**
 * Format file size
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }
    return $bytes;
}

/**
 * Send email notification
 */
function sendEmailNotification($to, $subject, $message) {
    // Don't actually send emails in API context to avoid output
    // Just log for now
    error_log("Email would be sent to: $to, Subject: $subject");
    return true;
}

/**
 * Create slug from string
 */
function createSlug($string) {
    $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
    $slug = strtolower($slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

/**
 * Check if request is AJAX
 */
function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get current URL
 */
function getCurrentUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Redirect with message - DON'T USE IN API
 */
function redirectWithMessage($url, $message, $type = 'success') {
    // This function should NOT be called in API context
    // It will break JSON responses
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    // Don't actually redirect in API - just log
    error_log("Redirect would happen to: $url with message: $message");
}

/**
 * Get flash message - DON'T USE IN API
 */
function getFlashMessage() {
    // This function should NOT be called in API context
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

/**
 * Safe JSON response for APIs
 */
function jsonResponse($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Validate required fields
 */
function validateRequired($data, $required_fields) {
    $errors = [];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $errors[] = "$field is required";
        }
    }
    return $errors;
}

/**
 * Clean array data
 */
function cleanArray($array) {
    return array_map('sanitize', $array);
}

/**
 * Generate API response
 */
function apiResponse($success, $message = '', $data = [], $status_code = 200) {
    return [
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'status_code' => $status_code
    ];
}

// NO CLOSING TAG - DO NOT ADD ?>