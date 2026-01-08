<?php
// Application configuration

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'dm_resume_builder');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('APP_NAME', 'Dragon Media Resume Builder');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/dm-resume-builder');
define('APP_TIMEZONE', 'UTC');

// Security settings
define('SESSION_NAME', 'DM_RESUME_SESSION');
define('SESSION_LIFETIME', 86400); // 24 hours
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// File upload settings
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', ['pdf', 'jpg', 'jpeg', 'png']);
define('UPLOAD_DIR', __DIR__ . '/../../uploads/');

// PDF generation settings
define('PDF_QUALITY', 0.98);
define('PDF_SCALE', 2);
define('PDF_MARGIN', 10);

// Email settings
define('EMAIL_FROM', 'noreply@dragonmedia.com');
define('EMAIL_FROM_NAME', 'Dragon Media Resume Builder');
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_SECURE', 'tls');

// Debug mode - SET TO FALSE FOR PRODUCTION
define('DEBUG_MODE', false);

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Error reporting - IMPORTANT: Never display errors in config file
error_reporting(0);
ini_set('display_errors', 0);

// Auto-load classes
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../classes/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
// NO CLOSING TAG - DO NOT ADD ?>