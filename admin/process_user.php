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
$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? intval($_GET['id']) : null;
if ($action === 'ban' && $id) {
    $stmt = $conn->prepare("UPDATE users SET banned = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: users.php?status=User berhasil diban.');
    exit();
} elseif ($action === 'unban' && $id) {
    $stmt = $conn->prepare("UPDATE users SET banned = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: users.php?status=User berhasil di-unban.');
    exit();
} elseif ($action === 'delete' && $id) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: users.php?status=User berhasil dihapus.');
    exit();
} elseif ($action === 'resetpw' && $id) {
    $newpw = bin2hex(random_bytes(4));
    $hash = password_hash($newpw, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hash, $id);
    $stmt->execute();
    $stmt->close();
    header('Location: users.php?status=Password baru user: ' . urlencode($newpw));
    exit();
} elseif ($action === 'edit' && $id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? 'user');
    if ($username === '' || $email === '' || !in_array($role, ['user','admin'])) {
        header('Location: users.php?error=Data tidak valid.');
        exit();
    }
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
    $stmt->bind_param("sssi", $username, $email, $role, $id);
    $stmt->execute();
    $stmt->close();
    header('Location: users.php?status=User berhasil diupdate.');
    exit();
} elseif ($action === 'edit' && $id && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare("SELECT username, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
        <meta charset="UTF-8">
        <title>Edit User</title>
        <link rel="stylesheet" href="/assets/css/main.css">
        </head>
        <body>
        <div class="page-container" style="max-width:400px;">
        <h2>Edit User</h2>
        <form method="post" action="process_user.php?action=edit&id=<?= $id ?>">
            <label>Username: <input type="text" name="username" value="<?= htmlspecialchars($row['username']) ?>" required></label><br>
            <label>Email: <input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" required></label><br>
            <label>Role: <select name="role"><option value="user"<?= $row['role']==='user'?' selected':'' ?>>User</option><option value="admin"<?= $row['role']==='admin'?' selected':'' ?>>Admin</option></select></label><br>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="users.php" class="btn btn-secondary">Batal</a>
        </form>
        </div>
        </body>
        </html>
        <?php
        exit();
    } else {
        header('Location: users.php?error=User tidak ditemukan.');
        exit();
    }
}
header('Location: users.php?error=Aksi tidak valid.');
exit(); 