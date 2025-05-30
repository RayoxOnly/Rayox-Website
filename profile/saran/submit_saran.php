<?php
// File: profile/saran/submit_saran.php (Modifikasi)
session_start();

// 1. Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: /login?error=Harus login untuk submit saran');
    exit();
}

// 2. Cek apakah request method adalah POST dan ada data suggestion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['suggestion'])) {
    $suggestion_text = trim($_POST['suggestion']);
    $user_id = $_SESSION['user_id'];

    // 3. Validasi dasar
    if (empty($suggestion_text)) {
        header('Location: /profile/saran?error=Saran tidak boleh kosong'); // <<--- UBAH INI
        exit();
    }

    // 4. Koneksi ke database
    $conn = new mysqli("localhost", "admin", "admin123", "login_app");
    if ($conn->connect_error) {
        header('Location: /profile/saran?error=Gagal terhubung ke sistem data'); // <<--- UBAH INI
        exit();
    }

    // 5. Siapkan dan jalankan query INSERT
    $stmt = $conn->prepare("INSERT INTO suggestions (user_id, suggestion_text) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $suggestion_text);

    if ($stmt->execute()) {
        header('Location: /profile/saran?status=Saran berhasil dikirim! Terima kasih.'); // <<--- UBAH INI
    } else {
        header('Location: /profile/saran?error=Terjadi kesalahan saat menyimpan saran.'); // <<--- UBAH INI
    }

    $stmt->close();
    $conn->close();
    exit();

} else {
    header('Location: /profile/saran'); // <<--- UBAH INI
    exit();
}
?>
