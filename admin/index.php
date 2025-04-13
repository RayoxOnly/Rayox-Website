<?php
// File: admin/index.php (Modifikasi)
session_start();

// --- Pengecekan Login DAN Role Admin ---
if (!isset($_SESSION['user']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    if (isset($_SESSION['user'])) {
        header('Location: /dashboard?error=Anda tidak memiliki hak akses admin');
    } else {
        header('Location: /login?error=Anda harus login sebagai admin');
    }
    exit();
}
?>
<!DOCTYPE html> <html>
<head>
<title>Admin Dashboard</title>
<style> /* Tambahkan sedikit style */
body { font-family: sans-serif; padding: 20px; }
h1 { color: #333; }
a { color: #4f46e5; text-decoration: none; }
a:hover { text-decoration: underline; }
ul { list-style: none; padding: 0; }
li { margin-bottom: 10px; }
</style>
</head>
<body>
<h1>Admin Dashboard</h1>
<p>Selamat datang, <?php echo htmlspecialchars($_SESSION['user']); ?>!</p>
<p>Area ini khusus untuk administrasi website.</p>

<h2>Menu Admin:</h2>
<ul>
<li><a href="/admin/saran/">Lihat Saran Pengguna</a></li>
</ul>

<p><a href="/logout.php">Logout</a></p>
</body>
</html>
