// File: admin/index.php (Modifikasi)
// ... (kode session check yang sudah ada) ...
?>
<!DOCTYPE html> <html>
<head>
<title>Admin | Dashboard</title>
<link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
<link rel="manifest" href="/assets/site.webmanifest">
<style> /* Tambahkan sedikit style */
body { font-family: sans-serif; padding: 20px; }
h1 { color: #333; }
a { color: #4f46e5; text-decoration: none; }
a:hover { text-decoration: underline; }
ul { list-style: none; padding: 0; }
li { margin-bottom: 10px; }
.container { max-width: 800px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);}
</style>
</head>
<body>
<div class="container">
<h1>Admin Dashboard</h1>
<p>Selamat datang, <?php echo htmlspecialchars($_SESSION['user']); ?>!</p>
<p>Area ini khusus untuk administrasi website.</p>

<h2>Menu Admin:</h2>
<ul>
<li><a href="/admin/saran/">Lihat Saran Pengguna</a></li>
<li><a href="/admin/changelogs/">Kelola Changelogs</a></li> {/* <-- TAMBAHKAN INI */}
</ul>

<p><a href="/logout.php">Logout</a></p>
</div>
</body>
</html>
