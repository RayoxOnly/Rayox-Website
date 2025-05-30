<?php
// File: admin/saran/index.php (Redesigned)
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

// --- Ambil Data Saran ---
$sql = "SELECT s.id, u.username, s.suggestion_text, s.submitted_at
        FROM suggestions s
        JOIN users u ON s.user_id = u.id
        ORDER BY s.submitted_at DESC";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin | Daftar Saran | Rayox</title>
<link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
<link rel="manifest" href="/assets/site.webmanifest">
<link rel="stylesheet" href="/assets/css/main.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="admin-container">
    <div class="page-header no-border"> <h1 class="gradient-text"><i class="fas fa-envelope-open-text"></i> Daftar Saran Pengguna</h1>
    </div>

    <div class="nav-links">
         <p>Admin: <strong><?php echo htmlspecialchars($_SESSION['user']); ?></strong></p>
         <a href="/admin" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
         <a href="/logout.php" class="btn btn-sm btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pengirim</th>
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
                        <td><?= nl2br(htmlspecialchars($row['suggestion_text'])) ?></td>
                        <td><?= date("d M Y, H:i", strtotime($row['submitted_at'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding: 20px; color: #6b7280;">
                            <i class="fas fa-info-circle"></i> Belum ada saran yang masuk.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $conn->close(); ?>
</body>
</html>