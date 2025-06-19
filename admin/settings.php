<?php
session_start();
if (!isset($_SESSION['user']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login?error=Akses ditolak. Hanya admin.');
    exit();
}
$conn = new mysqli("localhost", "admin", "admin123", "login_app");
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
// Cek apakah tabel settings ada
$table_exists = $conn->query("SHOW TABLES LIKE 'settings'")->num_rows > 0;
$site_name = '';
$maintenance = '0';
if ($table_exists) {
    $res = $conn->query("SELECT key_name, value FROM settings WHERE key_name IN ('site_name','maintenance_mode')");
    while ($row = $res->fetch_assoc()) {
        if ($row['key_name'] === 'site_name') $site_name = $row['value'];
        if ($row['key_name'] === 'maintenance_mode') $maintenance = $row['value'];
    }
}
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Admin | Pengaturan Umum</title>
<link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
<div class="admin-container">
    <div class="page-header no-border">
        <h1 class="gradient-text"><i class="fas fa-cogs"></i> Pengaturan Umum</h1>
    </div>
    <div class="nav-links">
        <a href="/admin" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
        <a href="/logout.php" class="btn btn-sm btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    <?php if (!$table_exists): ?>
        <div class="message error">Tabel <b>settings</b> tidak ditemukan di database. Silakan buat tabel <code>settings</code> dengan kolom <code>key_name</code> (varchar) dan <code>value</code> (text/varchar).</div>
    <?php else: ?>
    <form method="post" action="process_settings.php">
        <div class="form-group">
            <label for="site_name">Nama Situs:</label>
            <input type="text" id="site_name" name="site_name" value="<?= htmlspecialchars($site_name) ?>" required>
        </div>
        <div class="form-group">
            <label for="maintenance">Maintenance Mode:</label>
            <select id="maintenance" name="maintenance">
                <option value="0"<?= $maintenance==='0'?' selected':'' ?>>Nonaktif</option>
                <option value="1"<?= $maintenance==='1'?' selected':'' ?>>Aktif</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
    </form>
    <?php endif; ?>
</div>
<?php $conn->close(); ?>
</body>
</html> 