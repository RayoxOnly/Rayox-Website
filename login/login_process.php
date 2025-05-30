<?php
// File: login/login_process.php (Modifikasi)
session_start();

// Koneksi ke database
$conn = new mysqli("localhost", "admin", "admin123", "login_app"); // Ganti kredensial

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (empty($username) || empty($password)) {
        header("Location: index.php?error=Username dan Password wajib diisi");
        exit();
    }

    // Ambil id, username, password, dan role dari database
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Simpan informasi user ke session
            $_SESSION['user_id'] = $user['id']; // Simpan ID user
            $_SESSION['user'] = $user['username']; // Simpan username
            $_SESSION['role'] = $user['role']; // Simpan role user

            header("Location: /profile"); // Redirect ke dashboard
            exit();
        } else {
            header("Location: index.php?error=Password salah");
            exit();
        }
    } else {
        header("Location: index.php?error=Username tidak ditemukan");
        exit();
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: index.php"); // Redirect jika bukan POST
    exit();
}
?>
