<?php
// File: register/register_process.php (Tidak perlu diubah jika hanya ingin default 'user')

// Koneksi ke database
$conn = new mysqli("localhost", "admin", "admin123", "login_app"); // Ganti sesuai user/password MySQL kamu

// ... (kode koneksi error handling) ...

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // ... (kode validasi input) ...

    // Cek apakah username sudah digunakan
    // ... (kode cek username) ...

    // Hash password dan simpan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $default_role = 'user'; // Secara eksplisit mendefinisikan role default

    // Modifikasi prepare statement untuk menyertakan role
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    // Jika hanya mengandalkan default SQL, cukup:
    // $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");

    // Jika menyertakan role secara eksplisit:
    $stmt->bind_param("sss", $username, $hashed_password, $default_role);
    // Jika hanya mengandalkan default SQL:
    // $stmt->bind_param("ss", $username, $hashed_password);


    if ($stmt->execute()) {
        header("Location: /login?success=Registrasi berhasil, silahkan login."); // Redirect ke login dengan pesan sukses
        exit();
    } else {
        header("Location: index.php?error=Terjadi kesalahan saat menyimpan data"); // Redirect kembali ke register
        exit();
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: index.php"); // Redirect jika bukan POST request
    exit();
}
?>
