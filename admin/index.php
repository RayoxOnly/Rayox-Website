<?php
// File: admin/index.php (Redesigned)
session_start();

// --- Pengecekan Login DAN Role Admin ---
if (!isset($_SESSION['user']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    if (isset($_SESSION['user'])) {
        header('Location: /profile?error=Anda tidak memiliki hak akses admin');
    } else {
        header('Location: /login?error=Anda harus login sebagai admin');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin | Dashboard | Rayox</title>
<link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
<link rel="manifest" href="/assets/site.webmanifest">
<link rel="stylesheet" href="/assets/css/main.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
 <div class="admin-container"> <div class="page-header">
        <h1 class="gradient-text"><i class="fas fa-shield-alt"></i> Admin Dashboard</h1>
        <p class="subtitle">Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['user']); ?>!</strong> Area administrasi website.</p>
    </div>

    <div class="admin-menu">
        <h2><i class="fas fa-bars"></i> Menu Admin</h2>
        <ul>
            <li><a href="/admin/saran/"><i class="fas fa-envelope-open-text"></i> Lihat Saran Pengguna</a></li>
            <li><a href="/admin/changelogs/"><i class="fas fa-history"></i> Kelola Changelogs</a></li>
            <li><a href="/admin/users.php"><i class="fas fa-users-cog"></i> Manajemen User</a></li>
            <li><a href="/admin/settings.php"><i class="fas fa-cogs"></i> Pengaturan Umum</a></li>
        </ul>
    </div>

    <div class="logout-link-container">
         <a href="/profile" class="btn btn-secondary btn-sm"><i class="fas fa-user"></i> Kembali ke Profile</a>
         <a href="/logout.php" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>
</body>
</html>