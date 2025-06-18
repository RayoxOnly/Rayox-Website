<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'admin');
define('DB_PASS', 'admin123');
define('DB_NAME', 'login_app');

// Security Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8);

// Application Configuration
define('SITE_NAME', 'Rayox.site');
define('SITE_URL', 'http://localhost'); // Change this in production
define('ADMIN_EMAIL', 'admin@rayox.site');

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security Headers
function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' cdnjs.cloudflare.com; style-src \'self\' \'unsafe-inline\' cdnjs.cloudflare.com; img-src \'self\' data:; font-src \'self\' cdnjs.cloudflare.com;');
}

// Database Connection Function
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        return false;
    }
}

// CSRF Protection
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function validateCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

// Input Sanitization
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Password Validation
function validatePassword($password) {
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        return false;
    }
    // Add more password strength requirements if needed
    return true;
}

// Session Security
function secureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Regenerate session ID periodically
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    // Set session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        return false;
    }
    $_SESSION['last_activity'] = time();
    
    return true;
}

// Logging Function
function logActivity($user_id, $action, $details = '') {
    $conn = getDBConnection();
    if ($conn) {
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $stmt->bind_param("isss", $user_id, $action, $details, $ip);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
}
?> 