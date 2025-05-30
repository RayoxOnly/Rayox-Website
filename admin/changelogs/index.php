<?php
// File: admin/changelogs/index.php (Redesigned)
session_start();

// --- Pengecekan Login DAN Role Admin ---
if (!isset($_SESSION['user']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login?error=Akses ditolak. Hanya admin.');
    exit();
}

// --- Koneksi Database ---
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
<title>Admin | Kelola Changelogs | Rayox</title>
<link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
<link rel="manifest" href="/assets/site.webmanifest">
<link rel="stylesheet" href="/assets/css/main.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    /* Tambahkan style spesifik jika perlu, misal max-width kolom */
    .description-cell { max-width: 450px; word-wrap: break-word; }
</style>
</head>
<body>

<div class="admin-container">
    <div class="page-header no-border">
         <h1 class="gradient-text"><i class="fas fa-history"></i> Kelola Changelogs</h1>
    </div>

     <div class="nav-links">
         <p>Admin: <strong><?php echo htmlspecialchars($_SESSION['user']); ?></strong></p>
         <a href="/admin" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
         <a href="/logout.php" class="btn btn-sm btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <?php if (isset($_GET['status'])): ?>
    <div class="message success fade-out"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['status']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
    <div class="message error fade-out"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <div style="margin-bottom: 1.5rem;">
        <a href="manage_changelog.php?action=add" class="btn btn-success"><i class="fas fa-plus"></i> Tambah Changelog Baru</a>
    </div>


    <div class="table-container">
        <table class="table">
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
                        <td><?= date("d M Y", strtotime($row['update_date'])) ?></td>
                        <td class="description-cell"><?= nl2br(htmlspecialchars($row['description'])) ?></td>
                        <td class="actions">
                            <a href="manage_changelog.php?action=edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                            <a href="process_changelog.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus changelog versi <?= htmlspecialchars($row['version']) ?> ini?')"><i class="fas fa-trash"></i> Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding: 20px; color: #6b7280;">
                             <i class="fas fa-info-circle"></i> Belum ada data changelogs.
                         </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $conn->close(); ?>
 <script>
     // Script fade out message
    const messages = document.querySelectorAll('.message.fade-out');
    messages.forEach(msg => {
        // setTimeout(() => msg.remove(), 5000);
    });
 </script>
</body>
</html>