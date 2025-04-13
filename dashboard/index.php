<?php
// File: dashboard/index.php (Modifikasi)
session_start(); // Mulai session

// --- Pengecekan Login ---
if (!isset($_SESSION['user'])) {
    // Jika session 'user' tidak ada, artinya belum login
    header('Location: /login?error=Anda harus login untuk mengakses halaman ini'); // Redirect ke halaman login
    exit(); // Hentikan eksekusi script
}

// Ambil username dari session untuk ditampilkan
$username = htmlspecialchars($_SESSION['user']); // Gunakan htmlspecialchars untuk keamanan
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | Rayox</title> <style>
/* ... (style kamu sebelumnya) ... */
body {
    background-color: #f3f4f6;
    font-family: Arial, sans-serif;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-height: 100vh; /* Gunakan min-height */
    color: #4b5563;
    padding: 20px; /* Tambahkan padding */
    box-sizing: border-box;
}
.welcome-message {
    font-size: 1.5rem; /* Ukuran font untuk sambutan */
    margin-bottom: 1rem;
    color: #333;
}
.content-box {
    background: #fff; /* Latar belakang putih untuk konten */
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    text-align: center; /* Pusatkan teks di dalam box */
    max-width: 600px; /* Batasi lebar box */
    width: 100%; /* Agar responsif */
}
.content-box h1 { /* Styling untuk H1 di dalam box */
    font-size: 1.8rem;
    color: #4f46e5;
    margin-bottom: 1rem;
}
.content-box p {
    margin-bottom: 1.5rem;
    line-height: 1.6;
}
.content-box a {
    color: #4f46e5;
    text-decoration: none;
    font-weight: bold;
}
.content-box a:hover {
    text-decoration: underline;
}
.logout-link { /* Styling untuk link logout */
    margin-top: 1.5rem;
}
</style>
</head>
<body>
<div class="content-box">
<div class="welcome-message">Halo, <?php echo $username; ?>!</div>

<h1>Selamat Datang di Rayox.site</h1>
<p>Website ini masih dalam pengembangan.</p>
<p>Mau ngasih saran? berikan saran <a href="/dashboard/saran">disini</a>.</p>

<div class="logout-link">
<a href="/logout.php">Logout</a>
</div>
</div>
</body>
</html>
