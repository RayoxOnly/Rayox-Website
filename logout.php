<?php
// File: logout.php (File Baru)
session_start(); // Akses session yang sedang aktif

// 1. Hapus semua variabel session
$_SESSION = array();

// 2. Jika menggunakan cookie session (opsional tapi bagus), hapus cookie-nya
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
              $params["path"], $params["domain"],
              $params["secure"], $params["httponly"]
    );
}

// 3. Hancurkan session
session_destroy();

// 4. Redirect ke halaman login dengan pesan
header("Location: /login?status=Anda telah berhasil logout.");
exit();
?>
