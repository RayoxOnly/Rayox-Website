<?php
session_start();

// --- Pengecekan Login DAN Role Admin ---
if (!isset($_SESSION['user']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login?error=Akses ditolak. Hanya admin.');
    exit();
}

// --- Koneksi Database ---
// (Ganti dengan kredensial database Anda)
$conn = new mysqli("localhost", "admin", "admin123", "login_app");
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// --- Ambil Data Changelogs ---
$sql = "SELECT id, version, description, update_date FROM changelogs ORDER BY update_date DESC, id DESC";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin | Kelola Changelogs</title>
<link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
<link rel="manifest" href="/assets/site.webmanifest">
<style>
body { font-family: sans-serif; padding: 20px; background-color: #f4f4f4; }
.container { max-width: 900px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
h1 { color: #333; border-bottom: 2px solid #4f46e5; padding-bottom: 10px; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 1px solid #ddd; padding: 10px; text-align: left; vertical-align: top; }
th { background-color: #e9e7fd; color: #4f46e5; }
tr:nth-child(even) { background-color: #f9f9f9; }
.actions a { margin-right: 8px; text-decoration: none; padding: 3px 7px; border-radius: 3px; }
.actions .edit { background-color: #ffc107; color: #333; }
.actions .delete { background-color: #dc3545; color: white; }
.add-button {
    display: inline-block;
    margin-top: 15px;
    margin-bottom: 15px;
    padding: 10px 15px;
    background-color: #28a745;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}
.add-button:hover { background-color: #218838; }
.nav-links { margin-bottom: 20px; }
.nav-links a { margin-right: 15px; text-decoration: none; color: #4f46e5; }
.nav-links a:hover { text-decoration: underline; }
.message { padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; }
.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.description-cell { max-width: 400px; word-wrap: break-word; } /* Batasi lebar deskripsi */
</style>
</head>
<body>
<div class="container">
<h1>Kelola Changelogs</h1>

<div class="nav-links">
<p>Admin: <?php echo htmlspecialchars($_SESSION['user']); ?></p>
<a href="/admin">Kembali ke Dashboard Admin</a>
<a href="/logout.php">Logout</a>
</div>

<?php if (isset($_GET['status'])): ?>
<div class="message success"><?= htmlspecialchars($_GET['status']) ?></div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
<div class="message error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<a href="manage_changelog.php?action=add" class="add-button">Tambah Changelog Baru</a>

<table>
<thead>
<tr>
<th>Versi</th>
<th>Tanggal Update</th>
<th>Deskripsi</th>
<th>Aksi</th>
</tr>
</thead>
<tbody>
<?php if ($result && $result->num_rows > 0): ?>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($row['version']) ?></td>
<td><?= date("d M Y", strtotime($row['update_date'])) // Format tanggal ?></td>
<td class="description-cell"><?= nl2br(htmlspecialchars($row['description'])) ?></td>
<td class="actions">
<a href="manage_changelog.php?action=edit&id=<?= $row['id'] ?>" class="edit">Edit</a>
<a href="process_changelog.php?action=delete&id=<?= $row['id'] ?>" class="delete" onclick="return confirm('Yakin ingin menghapus changelog ini?')">Hapus</a>
</td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr>
<td colspan="4" style="text-align:center;">Belum ada data changelogs.</td>
</tr>
<?php endif; ?>
</tbody>
</table>
</div>
<?php
$conn->close();
?>
</body>
</html>
