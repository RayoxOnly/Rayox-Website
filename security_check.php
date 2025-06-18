<?php
/**
 * Security Checker for Rayox Website
 * This script helps identify potential security issues
 * Run this script to check your website's security status
 */

require_once 'config.php';

echo "<h1>Rayox Website Security Check</h1>\n";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .pass{color:green;} .fail{color:red;} .warning{color:orange;}</style>\n";

$issues = [];
$warnings = [];
$passes = [];

// Check 1: PHP Version
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    $passes[] = "PHP Version: " . PHP_VERSION . " ✓";
} else {
    $issues[] = "PHP Version: " . PHP_VERSION . " (Upgrade to 7.4+ recommended)";
}

// Check 2: HTTPS
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $passes[] = "HTTPS is enabled ✓";
} else {
    $warnings[] = "HTTPS is not enabled (recommended for production)";
}

// Check 3: Database Connection
$conn = getDBConnection();
if ($conn) {
    $passes[] = "Database connection successful ✓";
    $conn->close();
} else {
    $issues[] = "Database connection failed";
}

// Check 4: Required PHP Extensions
$required_extensions = ['mysqli', 'session', 'json'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        $passes[] = "PHP Extension '$ext' is loaded ✓";
    } else {
        $issues[] = "PHP Extension '$ext' is missing";
    }
}

// Check 5: File Permissions
$sensitive_files = ['config.php', '.htaccess', 'Dump20250503.sql'];
foreach ($sensitive_files as $file) {
    if (file_exists($file)) {
        $perms = fileperms($file);
        if (($perms & 0x0177) === 0) {
            $passes[] = "File '$file' has secure permissions ✓";
        } else {
            $warnings[] = "File '$file' may have insecure permissions";
        }
    }
}

// Check 6: Session Security
if (ini_get('session.cookie_httponly')) {
    $passes[] = "Session cookies are HttpOnly ✓";
} else {
    $warnings[] = "Session cookies should be HttpOnly";
}

if (ini_get('session.cookie_secure') || !isset($_SERVER['HTTPS'])) {
    $passes[] = "Session security settings are appropriate ✓";
} else {
    $warnings[] = "Session cookies should be secure in HTTPS";
}

// Check 7: Error Reporting
if (!ini_get('display_errors')) {
    $passes[] = "Error display is disabled ✓";
} else {
    $warnings[] = "Error display should be disabled in production";
}

// Check 8: Directory Listing
$test_file = 'test_directory_listing_' . time() . '.txt';
file_put_contents($test_file, 'test');
$headers = get_headers('http://' . $_SERVER['HTTP_HOST'] . '/' . $test_file);
unlink($test_file);

if (empty($headers) || strpos($headers[0], '403') !== false) {
    $passes[] = "Directory listing appears to be disabled ✓";
} else {
    $warnings[] = "Directory listing may be enabled";
}

// Display Results
echo "<h2>Security Status</h2>\n";

if (!empty($passes)) {
    echo "<h3 class='pass'>✓ Passed Checks (" . count($passes) . ")</h3>\n";
    echo "<ul>\n";
    foreach ($passes as $pass) {
        echo "<li class='pass'>$pass</li>\n";
    }
    echo "</ul>\n";
}

if (!empty($warnings)) {
    echo "<h3 class='warning'>⚠ Warnings (" . count($warnings) . ")</h3>\n";
    echo "<ul>\n";
    foreach ($warnings as $warning) {
        echo "<li class='warning'>$warning</li>\n";
    }
    echo "</ul>\n";
}

if (!empty($issues)) {
    echo "<h3 class='fail'>✗ Issues (" . count($issues) . ")</h3>\n";
    echo "<ul>\n";
    foreach ($issues as $issue) {
        echo "<li class='fail'>$issue</li>\n";
    }
    echo "</ul>\n";
}

// Security Recommendations
echo "<h2>Security Recommendations</h2>\n";
echo "<ul>\n";
echo "<li>Use HTTPS in production</li>\n";
echo "<li>Keep PHP and all dependencies updated</li>\n";
echo "<li>Regularly backup your database</li>\n";
echo "<li>Monitor access logs for suspicious activity</li>\n";
echo "<li>Consider implementing rate limiting</li>\n";
echo "<li>Use strong, unique passwords for database access</li>\n";
echo "<li>Regularly review and update security configurations</li>\n";
echo "</ul>\n";

// Database Security Check
echo "<h2>Database Security Check</h2>\n";
$conn = getDBConnection();
if ($conn) {
    // Check for SQL injection vulnerabilities in suggestions table
    $result = $conn->query("SELECT COUNT(*) as count FROM suggestions WHERE suggestion_text LIKE '%<script>%' OR suggestion_text LIKE '%UNION%' OR suggestion_text LIKE '%DROP%'");
    if ($result) {
        $row = $result->fetch_assoc();
        if ($row['count'] > 0) {
            echo "<p class='fail'>⚠ Found " . $row['count'] . " potentially malicious entries in suggestions table</p>\n";
        } else {
            echo "<p class='pass'>✓ No obvious malicious entries found in suggestions table</p>\n";
        }
    }
    
    // Check user table for weak passwords (this is just a demonstration)
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE username LIKE '%script%' OR username LIKE '%UNION%'");
    if ($result) {
        $row = $result->fetch_assoc();
        if ($row['count'] > 0) {
            echo "<p class='fail'>⚠ Found " . $row['count'] . " suspicious usernames</p>\n";
        } else {
            echo "<p class='pass'>✓ No suspicious usernames found</p>\n";
        }
    }
    
    $conn->close();
}

echo "<p><strong>Note:</strong> This is a basic security check. For comprehensive security assessment, consider using professional security tools and services.</p>\n";
?> 