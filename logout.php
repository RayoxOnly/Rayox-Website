<?php
require_once 'config.php';

// Set security headers
setSecurityHeaders();

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log logout activity if user was logged in
if (isset($_SESSION['user_id'])) {
    logActivity($_SESSION['user_id'], 'logout', 'User logged out');
}

// Clear all session variables
$_SESSION = array();

// Delete session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
              $params["path"], $params["domain"],
              $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page with success message
header("Location: /login?status=You have been successfully logged out.");
exit();
?>
