<?php
// File: dashboard/submit_saran.php (File Baru)
session_start();

// 1. Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) { // Gunakan user_id yang disimpan saat login
    header('Location: /login?error=Harus login untuk submit saran');
    exit();
}

// 2. Cek apakah request method adalah POST dan ada data suggestion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['suggestion'])) {
    $suggestion_text = trim($_POST['suggestion']); // Ambil teks saran dan hapus spasi ekstra
    $user_id = $_SESSION['user_id']; // Ambil user_id dari session

    // 3. Validasi dasar: pastikan saran tidak kosong
    if (empty($suggestion_text)) {
        header('Location: /dashboard/saran?error=Saran tidak boleh kosong');
        exit();
    }

    // 4. Koneksi ke database
    $conn = new mysqli("localhost", "admin", "admin123", "login_app"); // Ganti kredensial
    if ($conn->connect_error) {
        // Jangan tampilkan error detail ke user biasa
        header('Location: /dashboard/saran?error=Gagal terhubung ke sistem data');
        // Untuk debugging, kamu bisa pakai: die("Koneksi gagal: " . $conn->connect_error);
        exit();
    }

    // 5. Siapkan dan jalankan query INSERT menggunakan prepared statement
    $stmt = $conn->prepare("INSERT INTO suggestions (user_id, suggestion_text) VALUES (?, ?)");
    // 'i' untuk integer (user_id), 's' untuk string (suggestion_text)
    $stmt->bind_param("is", $user_id, $suggestion_text);

    if ($stmt->execute()) {
        // Jika berhasil disimpan
        header('Location: /dashboard/saran?status=Saran berhasil dikirim! Terima kasih atas masukannya.');
    } else {
        // Jika gagal disimpan
        header('Location: /dashboard/saran?error=Terjadi kesalahan saat menyimpan saran.');
        // Untuk debugging: header('Location: saran.php?error=Error: ' . $stmt->error);
    }

    // 6. Tutup statement dan koneksi
    $stmt->close();
    $conn->close();
    exit();

} else {
    // Jika bukan POST request atau data 'suggestion' tidak ada, redirect kembali
    header('Location: /dashboard/saran');
    exit();
}
?>
