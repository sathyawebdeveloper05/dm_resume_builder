<?php
// ENABLE ERROR REPORTING (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start secure session
session_start();

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

require_once '../config/database.php';

// Simple rate limiting (remove if not needed)
$ip = $_SERVER['REMOTE_ADDR'];
$max_attempts = 5;
$lockout_time = 900; // 15 minutes

// Simple lockout check (you can remove this for now if it causes issues)
$lockout_key = 'lockout_' . md5($ip);
if (isset($_SESSION[$lockout_key])) {
    if (time() - $_SESSION[$lockout_key]['time'] < $lockout_time && 
        $_SESSION[$lockout_key]['attempts'] >= $max_attempts) {
        $remaining = $lockout_time - (time() - $_SESSION[$lockout_key]['time']);
        die("Too many login attempts. Please try again in " . ceil($remaining / 60) . " minutes.");
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Input validation
    if (empty($username) || empty($password)) {
        $error = "Please enter username and password";
    } elseif (strlen($username) > 50 || strlen($password) > 100) {
        $error = "Invalid input length";
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Use prepared statement to prevent SQL injection
            $query = "SELECT id, username, password FROM admin WHERE username = :username";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password (plain text for now)
                if ($password === $admin['password']) {
                    // Reset login attempts on success
                    if (isset($_SESSION[$lockout_key])) {
                        unset($_SESSION[$lockout_key]);
                    }
                    
                    // Regenerate session ID to prevent fixation
                    session_regenerate_id(true);
                    
                    // Set session variables
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = htmlspecialchars($admin['username']);
                    $_SESSION['login_time'] = time();
                    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
                    $_SESSION['ip_address'] = $ip;
                    
                    // Redirect to dashboard
                    header('Location: dashboard.php');
                    exit;
                } else {
                    // Increment failed attempts
                    $this->logFailedAttempt($ip);
                    $error = "Invalid username or password";
                }
            } else {
                // Increment failed attempts
                $this->logFailedAttempt($ip);
                $error = "Invalid username or password"; // Generic message for security
            }
        } catch (PDOException $e) {
            // Log error but show generic message
            error_log("Login error: " . $e->getMessage());
            $error = "System error. Please try again later.";
        }
    }
}

/**
 * Simple function to track failed attempts (in session)
 */
function logFailedAttempt($ip) {
    $lockout_key = 'lockout_' . md5($ip);
    $max_attempts = 5;
    
    if (!isset($_SESSION[$lockout_key])) {
        $_SESSION[$lockout_key] = [
            'attempts' => 1,
            'time' => time(),
            'ip' => $ip
        ];
    } else {
        $_SESSION[$lockout_key]['attempts']++;
        $_SESSION[$lockout_key]['time'] = time();
    }
    
    // Optional: You can add email alert here if needed
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Dragon Media Resume Builder</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            padding: 40px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #666;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 600;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
        }
        
        .error-message {
            background: #fee;
            color: #c33;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
            font-size: 14px;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
        }
        
        .security-note {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Admin Login</h1>
            <p>Dragon Media Resume Builder</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" autocomplete="off" id="loginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required 
                       placeholder="Enter admin username" autocomplete="username">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Enter admin password" autocomplete="current-password">
            </div>
            
            <button type="submit" class="btn-login" id="loginBtn">Login</button>
        </form>
        
        <div class="security-note">
            ⚡ Secure Admin Access
        </div>
        
        <div class="back-link">
            <a href="../../frontend/index.html">← Back to Resume Builder</a>
        </div>
    </div>
    
    <script>
    // Prevent form resubmission on page refresh
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    
    // Simple client-side validation
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        const loginBtn = document.getElementById('loginBtn');
        
        if (!username || !password) {
            e.preventDefault();
            alert('Please fill in both fields');
            return false;
        }
        
        // Disable button to prevent double submission
        loginBtn.disabled = true;
        loginBtn.innerHTML = 'Logging in...';
        return true;
    });
    </script>
</body>
</html>