<?php
// Koneksi ke database
$conn = new mysqli("localhost", "admin", "admin123", "login_app"); // Ganti sesuai user/password MySQL kamu

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (empty($username) || empty($password)) {
        header("Location: register.php?error=Username dan password wajib diisi");
        exit();
    }

    // Cek apakah username sudah digunakan
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        header("Location: register.php?error=Username sudah digunakan");
        exit();
    }

    // Hash password dan simpan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashed_password);


    if ($stmt->execute()) {
        // --- PERUBAHAN DI SINI ---
        header("Location: index.php"); // Arahkan ke halaman login setelah registrasi berhasil
        exit();
        // --- AKHIR PERUBAHAN ---
    } else {
        header("Location: register.php?error=Terjadi kesalahan saat menyimpan data");
        exit();
    }
}
?>
