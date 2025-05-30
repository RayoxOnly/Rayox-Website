<?php
// File: register/register_process.php (Modifikasi)

// Koneksi ke database
$conn = new mysqli("localhost", "admin", "admin123", "login_app");

if ($conn->connect_error) {
    header("Location: index.php?error=Koneksi database gagal");
    exit();
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (empty(trim($username)) || empty($password)) {
        header("Location: index.php?error=Username dan Password tidak boleh kosong");
        exit();
    }

    // Cek apakah username sudah digunakan
    $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        header("Location: index.php?error=Username sudah digunakan");
        $stmt_check->close();
        $conn->close();
        exit();
    }
    $stmt_check->close();

    // Hash password dan simpan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $default_role = 'user';
    // --- UBAH NILAI DEFAULT DI SINI ---
    $default_money = 50000; // <-- Ubah dari 1000 menjadi 50000

    // Modifikasi prepare statement untuk menyertakan role dan money
    $stmt = $conn->prepare("INSERT INTO users (username, password, role, money) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $username, $hashed_password, $default_role, $default_money);

    if ($stmt->execute()) {
        header("Location: /login?success=Registrasi berhasil, silahkan login.");
        exit();
    } else {
        header("Location: index.php?error=Terjadi kesalahan saat menyimpan data: " . $stmt->error);
        exit();
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: index.php");
    exit();
}
?>
