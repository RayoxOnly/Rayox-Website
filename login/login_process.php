<?php
// File: login/login_process.php (Modifikasi)
require_once '../config.php';

// Set security headers
setSecurityHeaders();

// Start secure session
if (!secureSession()) {
    header("Location: index.php?error=Session expired. Please login again.");
    exit();
}

// Validate CSRF token
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        header("Location: index.php?error=Invalid request. Please try again.");
        exit();
    }
    
    // Sanitize inputs
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        header("Location: index.php?error=Username and password are required");
        exit();
    }
    
    // Get database connection
    $conn = getDBConnection();
    if (!$conn) {
        header("Location: index.php?error=System temporarily unavailable. Please try again later.");
        exit();
    }
    
    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    if (!$stmt) {
        $conn->close();
        header("Location: index.php?error=System error. Please try again later.");
        exit();
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Store user information in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();
            
            // Log successful login
            logActivity($user['id'], 'login', 'Successful login');
            
            // Regenerate session ID after successful login
            session_regenerate_id(true);
            
            $stmt->close();
            $conn->close();
            
            header("Location: /profile");
            exit();
        } else {
            // Log failed login attempt
            logActivity(0, 'login_failed', "Failed login attempt for username: $username");
            
            $stmt->close();
            $conn->close();
            
            header("Location: index.php?error=Invalid username or password");
            exit();
        }
    } else {
        // Log failed login attempt
        logActivity(0, 'login_failed', "Failed login attempt for username: $username");
        
        $stmt->close();
        $conn->close();
        
        header("Location: index.php?error=Invalid username or password");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
