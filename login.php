<?php
session_start();

// Koneksi ke database
$conn = new mysqli("localhost", "admin", "admin123", "login_app"); // Ganti sesuai user/password MySQL kamu

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Cek apakah user ada
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            header("Location: index.php?error=Password salah");
            exit();
        }
    } else {
        header("Location: index.php?error=Username tidak ditemukan");
        exit();
    }
}
?>
