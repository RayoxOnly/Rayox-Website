<?php
session_start();

// --- Pengecekan Login ---
if (!isset($_SESSION['user'])) {
    header('Location: /login?error=Anda harus login untuk mengakses halaman ini');
    exit();
}

$username = htmlspecialchars($_SESSION['user']);

// --- Koneksi Database untuk Ambil Changelogs ---
// (Ganti dengan kredensial database Anda)
$conn = new mysqli("localhost", "admin", "admin123", "login_app");
if ($conn->connect_error) {
    // Di halaman user, sebaiknya tidak menampilkan error detail
    // Mungkin log error atau tampilkan pesan generik jika perlu
    $changelogs = []; // Set array kosong agar halaman tetap tampil
} else {
    // Ambil data changelogs, misal 10 terbaru
    $sql = "SELECT version, description, update_date
    FROM changelogs
    ORDER BY update_date DESC, id DESC
    LIMIT 10"; // Batasi jumlah yang ditampilkan awal
    $result = $conn->query($sql);
    $changelogs = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $changelogs[] = $row;
        }
    }
    $conn->close();
}

?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
<link rel="manifest" href="/assets/site.webmanifest">
<title>Dashboard | Rayox</title>
<style>
body {
    background-color: #f3f4f6;
    font-family: Arial, sans-serif;
    color: #4b5563;
    margin: 0; /* Hapus margin default */
    padding: 0; /* Hapus padding default */
    display: flex; /* Gunakan Flexbox untuk layout */
    min-height: 100vh;
}
/* Sidebar untuk Changelogs */
#changelog-panel {
width: 280px; /* Lebar sidebar */
background-color: #fff;
padding: 20px;
box-shadow: 2px 0 5px rgba(0,0,0,0.1);
height: 100vh; /* Tinggi penuh viewport */
overflow-y: auto; /* Aktifkan scroll jika konten lebih panjang */
position: sticky; /* Membuat sidebar tetap saat scroll */
top: 0; /* Menempel di bagian atas */
box-sizing: border-box;
}
#changelog-panel h2 {
color: #4f46e5;
border-bottom: 1px solid #eee;
padding-bottom: 10px;
margin-top: 0;
font-size: 1.2rem;
}
.changelog-item {
    margin-bottom: 18px;
    padding-bottom: 15px;
    border-bottom: 1px dashed #eee;
}
.changelog-item ul.changelog-list {
    font-size: 0.85rem;    /* Sesuaikan ukuran font jika perlu */
    line-height: 1.5;      /* Atur jarak antar baris */
    color: #555;
    margin: 0;             /* Hapus margin default <ul> */
    padding-left: 20px;    /* Beri sedikit indentasi untuk bullet point */
    list-style-type: disc; /* Pastikan menggunakan bullet point (disc) */
    white-space: normal; /* Gantikan pre-wrap agar wrap normal */
    word-wrap: break-word;
}
.changelog-item ul.changelog-list li {
    margin-bottom: 4px; /* Beri sedikit jarak antar item list, sesuaikan nilainya */
}
.changelog-item ul.changelog-list li:last-child {
    margin-bottom: 0; /* Hapus margin bawah untuk item terakhir */
}
.changelog-item:last-child {
    border-bottom: none; /* Hapus border untuk item terakhir */
    margin-bottom: 0;
    padding-bottom: 0;
}
.changelog-item h4 {
    font-size: 0.95rem;
    margin: 0 0 5px 0;
    color: #333;
}
.changelog-item .date {
    font-size: 0.8rem;
    color: #888;
    margin-bottom: 8px;
    display: block; /* Agar span bisa diberi margin-bottom */
}
.changelog-item p {
    font-size: 0.85rem;
    line-height: 1.5;
    color: #555;
    margin: 0;
    white-space: pre-wrap; /* Agar line break dari textarea tampil */
    word-wrap: break-word; /* Agar teks panjang bisa wrap */
}
.no-changelog {
    font-size: 0.9rem;
    color: #888;
    text-align: center;
    margin-top: 20px;
}

/* Konten Utama */
.main-content {
    flex-grow: 1; /* Ambil sisa ruang */
    padding: 30px;
    display: flex;
    flex-direction: column; /* Susun konten secara vertikal */
    justify-content: center; /* Pusatkan secara vertikal */
    align-items: center; /* Pusatkan secara horizontal */
    box-sizing: border-box;
}
.content-box {
    background: #fff;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    text-align: center;
    max-width: 600px;
    width: 90%; /* Buat sedikit responsif */
}
.welcome-message {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: #333;
}
.content-box h1 {
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
.logout-link {
    margin-top: 1.5rem;
}

/* Responsiveness (Optional Basic Example) */
@media (max-width: 768px) {
    body {
        flex-direction: column; /* Stack sidebar dan konten */
    }
    #changelog-panel {
    width: 100%; /* Lebar penuh di mobile */
    height: 300px; /* Tinggi terbatas, bisa scroll */
    position: static; /* Hapus sticky */
    margin-bottom: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .main-content {
        padding: 20px; /* Kurangi padding */
    }
}

</style>
</head>
<body>
<aside id="changelog-panel">
<h2>Changelogs</h2>
<?php if (!empty($changelogs)): ?>
<?php foreach ($changelogs as $log): ?>
<div class="changelog-item">
<h4><?= htmlspecialchars($log['version']) ?></h4>
<span class="date"><?= date("d F Y", strtotime($log['update_date'])) ?></span>
<?php
// 1. Ambil deskripsi mentah dan amankan
$raw_description = htmlspecialchars($log['description']);
// 2. Pisahkan deskripsi menjadi baris-baris
$lines = explode("\n", $raw_description);
// 3. Mulai list HTML
echo '<ul class="changelog-list">';
// 4. Loop melalui setiap baris
foreach ($lines as $line) {
    // 5. Hapus spasi ekstra di awal/akhir baris
    $trimmed_line = trim($line);
    // 6. Hanya proses baris yang tidak kosong
    if (!empty($trimmed_line)) {
        // 7. Cek apakah baris dimulai dengan "-", hapus jika iya
        if (strpos($trimmed_line, '-') === 0) {
            // Hapus tanda '-' dan spasi setelahnya (jika ada)
            $content = ltrim(substr($trimmed_line, 1));
        } else {
            $content = $trimmed_line; // Gunakan baris asli jika tidak ada '-'
        }
        // 8. Tampilkan sebagai list item
        echo '<li>' . $content . '</li>';
    }
}
// 9. Tutup list HTML
echo '</ul>';
?>
</div>
<?php endforeach; ?>
<?php else: ?>
<p class="no-changelog">Belum ada riwayat pembaruan.</p>
<?php endif; ?>
</aside>

<main class="main-content">
<div class="content-box">
<div class="welcome-message">Halo, <?php echo $username; ?>!</div>

<h1>Selamat Datang di Rayox.site</h1>
<p>Website sudah masuk fase pembangunan.</p>
<p>Lihat riwayat Changelogs di panel sebelah kiri!</p>
<p>Mau ngasih saran? berikan saran <a href="/dashboard/saran">disini</a>.</p>

<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
<p>Anda login sebagai admin. <a href="/admin">Masuk ke Panel Admin</a>.</p>
<?php endif; ?>

<div class="logout-link">
<a href="/logout.php">Logout</a>
</div>
</div>
</main>

</body>
</html>
