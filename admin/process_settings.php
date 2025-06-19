<?php
session_start();
if (!isset($_SESSION['user']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login?error=Akses ditolak. Hanya admin.');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: settings.php?error=Metode tidak valid.');
    exit();
}
$conn = new mysqli("localhost", "admin", "admin123", "login_app");
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
$site_name = trim($_POST['site_name'] ?? '');
$maintenance = ($_POST['maintenance'] ?? '0') === '1' ? '1' : '0';
if ($site_name === '') {
    header('Location: settings.php?error=Nama situs wajib diisi.');
    exit();
}
foreach ([['site_name', $site_name], ['maintenance_mode', $maintenance]] as $setting) {
    list($key, $value) = $setting;
    $stmt = $conn->prepare("INSERT INTO settings (key_name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)");
    $stmt->bind_param("ss", $key, $value);
    $stmt->execute();
    $stmt->close();
}
$conn->close();
header('Location: settings.php?status=Pengaturan berhasil disimpan.');
exit(); 