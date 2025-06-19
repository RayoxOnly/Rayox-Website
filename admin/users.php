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
$search = trim($_GET['search'] ?? '');
$where = '';
if ($search !== '') {
    $search_sql = $conn->real_escape_string($search);
    $where = "WHERE username LIKE '%$search_sql%' OR email LIKE '%$search_sql%'";
}
$sql = "SELECT id, username, email, role, banned FROM users $where ORDER BY id DESC LIMIT 100";
$result = $conn->query($sql);
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin | Manajemen User</title>
<link rel="stylesheet" href="/assets/css/main.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="admin-container">
    <div class="page-header no-border">
        <h1 class="gradient-text"><i class="fas fa-users-cog"></i> Manajemen User</h1>
    </div>
    <div class="nav-links">
        <a href="/admin" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
        <a href="/logout.php" class="btn btn-sm btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    <form method="get" class="form-inline" style="margin-bottom:1rem;">
        <input type="text" name="search" placeholder="Cari username/email..." value="<?= htmlspecialchars($search) ?>" class="form-control">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Cari</button>
    </form>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['role']) ?></td>
                        <td><?= $row['banned'] ? '<span class="badge badge-danger">Banned</span>' : '<span class="badge badge-success">Active</span>' ?></td>
                        <td>
                            <a href="process_user.php?action=edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                            <?php if ($row['banned']): ?>
                                <a href="process_user.php?action=unban&id=<?= $row['id'] ?>" class="btn btn-sm btn-success"><i class="fas fa-unlock"></i> Unban</a>
                            <?php else: ?>
                                <a href="process_user.php?action=ban&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger"><i class="fas fa-ban"></i> Ban</a>
                            <?php endif; ?>
                            <a href="process_user.php?action=resetpw&id=<?= $row['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-key"></i> Reset PW</a>
                            <a href="process_user.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus user ini?')"><i class="fas fa-trash"></i> Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center;">Tidak ada user ditemukan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $conn->close(); ?>
</body>
</html> 