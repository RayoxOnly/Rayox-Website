<?php
// File: admin/saran/index.php (Modifikasi)
session_start();

// --- Pengecekan Login DAN Role Admin ---
// 1. Cek apakah user login
// 2. Cek apakah role user adalah 'admin'
if (!isset($_SESSION['user']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Jika tidak login atau bukan admin, redirect
    // Beri pesan berbeda tergantung user sudah login atau belum
    if (isset($_SESSION['user'])) {
        // Sudah login tapi bukan admin
        header('Location: /dashboard?error=Anda tidak memiliki hak akses admin');
    } else {
        // Belum login sama sekali
        header('Location: /login?error=Anda harus login sebagai admin');
    }
    exit(); // Hentikan eksekusi
}

// --- Koneksi Database ---
$conn = new mysqli("localhost", "admin", "admin123", "login_app"); // Ganti kredensial
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error); // Boleh tampilkan error di halaman admin
}

// --- Ambil Data Saran ---
// Query untuk mengambil saran dan nama user yang mengirim (JOIN)
$sql = "SELECT s.id, u.username, s.suggestion_text, s.submitted_at
FROM suggestions s
JOIN users u ON s.user_id = u.id
ORDER BY s.submitted_at DESC"; // Urutkan dari terbaru
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Daftar Saran</title>
<style>
body { font-family: sans-serif; padding: 20px; background-color: #f4f4f4; }
h1 { color: #333; border-bottom: 2px solid #4f46e5; padding-bottom: 10px; }
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background-color: #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
th, td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: left;
    vertical-align: top; /* Ratakan teks ke atas */
}
th {
    background-color: #e9e7fd; /* Warna header tabel */
    color: #4f46e5;
    font-weight: bold;
}
tr:nth-child(even) { background-color: #f9f9f9; } /* Warna baris genap */
.no-suggestions { text-align: center; color: #777; padding: 20px; }
.nav-links { margin-bottom: 20px; }
.nav-links a { margin-right: 15px; text-decoration: none; color: #4f46e5; }
.nav-links a:hover { text-decoration: underline; }
</style>
</head>
<body>

<h1>Admin Page - Daftar Saran Masuk</h1>

<div class="nav-links">
<p>Selamat datang, <?php echo htmlspecialchars($_SESSION['user']); ?>!</p>
<a href="/admin">Kembali ke Dashboard Admin</a>
<a href="/logout.php">Logout</a>
</div>

<table>
<thead>
<tr>
<th>ID Saran</th>
<th>Username Pengirim</th>
<th>Isi Saran</th>
<th>Waktu Submit</th>
</tr>
</thead>
<tbody>
<?php if ($result && $result->num_rows > 0): ?>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?= $row['id'] ?></td>
<td><?= htmlspecialchars($row['username']) ?></td>
<td><?= nl2br(htmlspecialchars($row['suggestion_text'])) // nl2br untuk menampilkan baris baru ?></td>
<td><?= $row['submitted_at'] ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr>
<td colspan="4" class="no-suggestions">Belum ada saran yang masuk.</td>
</tr>
<?php endif; ?>
</tbody>
</table>

<?php
// Tutup koneksi database
$conn->close();
?>

</body>
</html>
