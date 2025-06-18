<?php
// File: register/register_process.php (Modifikasi)

require_once '../config.php';

// Set security headers
setSecurityHeaders();

// Start secure session
if (!secureSession()) {
    header("Location: index.php?error=Session expired. Please try again.");
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
    
    if (empty(trim($username)) || empty($password)) {
        header("Location: index.php?error=Username and password are required");
        exit();
    }
    
    // Validate username (alphanumeric and underscore only, 3-20 characters)
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        header("Location: index.php?error=Username must be 3-20 characters long and contain only letters, numbers, and underscores");
        exit();
    }
    
    // Validate password strength
    if (!validatePassword($password)) {
        header("Location: index.php?error=Password must be at least " . PASSWORD_MIN_LENGTH . " characters long");
        exit();
    }
    
    // Get database connection
    $conn = getDBConnection();
    if (!$conn) {
        header("Location: index.php?error=System temporarily unavailable. Please try again later.");
        exit();
    }
    
    // Check if username already exists
    $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    if (!$stmt_check) {
        $conn->close();
        header("Location: index.php?error=System error. Please try again later.");
        exit();
    }
    
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $stmt_check->store_result();
    
    if ($stmt_check->num_rows > 0) {
        $stmt_check->close();
        $conn->close();
        header("Location: index.php?error=Username already exists");
        exit();
    }
    $stmt_check->close();
    
    // Hash password and create user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $default_role = 'user';
    $default_money = 50000;
    
    $stmt = $conn->prepare("INSERT INTO users (username, password, role, money) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        $conn->close();
        header("Location: index.php?error=System error. Please try again later.");
        exit();
    }
    
    $stmt->bind_param("sssi", $username, $hashed_password, $default_role, $default_money);
    
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        
        // Log successful registration
        logActivity($user_id, 'registration', 'New user registered');
        
        $stmt->close();
        $conn->close();
        
        header("Location: /login?success=Registration successful! Please login.");
        exit();
    } else {
        $stmt->close();
        $conn->close();
        header("Location: index.php?error=Registration failed. Please try again.");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
