<?php
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

// --- Inisialisasi Variabel ---
$page_title = "Tambah Changelog Baru";
$form_action = "process_changelog.php?action=add";
$changelog_id = null;
$version = '';
$description = '';
$update_date = date('Y-m-d'); // Default ke hari ini

// --- Cek Aksi (Edit atau Add) ---
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $changelog_id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT version, description, update_date FROM changelogs WHERE id = ?");
    $stmt->bind_param("i", $changelog_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $page_title = "Edit Changelog (ID: " . $changelog_id . ")";
        $form_action = "process_changelog.php?action=edit&id=" . $changelog_id;
        $version = $row['version'];
        $description = $row['description'];
        $update_date = $row['update_date'];
    } else {
        // ID tidak ditemukan, redirect kembali dengan error
        header('Location: index.php?error=Changelog tidak ditemukan.');
        exit();
    }
    $stmt->close();
} elseif (isset($_GET['action']) && $_GET['action'] !== 'add') {
    // Jika action ada tapi bukan 'add' atau 'edit' yang valid
    header('Location: index.php?error=Aksi tidak valid.');
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin | <?= $page_title ?></title>
<link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
<link rel="manifest" href="/assets/site.webmanifest">
<style>
body { font-family: sans-serif; padding: 20px; background-color: #f4f4f4; }
.container { max-width: 600px; margin: auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
h1 { color: #333; text-align: center; margin-bottom: 25px; }
.form-group { margin-bottom: 15px; }
label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
input[type="text"], input[type="date"], textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box; /* Penting agar padding tidak menambah lebar */
}
textarea { height: 150px; resize: vertical; } /* Biarkan tinggi textarea bisa diubah */
button {
    padding: 12px 20px;
    background-color: #4f46e5;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.2s;
}
button:hover { background-color: #4338ca; }
.back-link { display: inline-block; margin-top: 20px; color: #4f46e5; text-decoration: none; }
.back-link:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="container">
<h1><?= $page_title ?></h1>

<form action="<?= $form_action ?>" method="POST">
<div class="form-group">
<label for="version">Versi:</label>
<input type="text" id="version" name="version" value="<?= htmlspecialchars($version) ?>" placeholder="Contoh: v1.2.0" required>
</div>

<div class="form-group">
<label for="update_date">Tanggal Update:</label>
<input type="date" id="update_date" name="update_date" value="<?= htmlspecialchars($update_date) ?>" required>
</div>

<div class="form-group">
<label for="description">Deskripsi Perubahan:</label>
<textarea id="description" name="description" placeholder="Jelaskan perubahan apa saja yang ada di versi ini..." required><?= htmlspecialchars($description) ?></textarea>
</div>

<button type="submit"><?= ($changelog_id) ? 'Update Changelog' : 'Simpan Changelog' ?></button>
</form>

<a href="index.php" class="back-link">&larr; Kembali ke Daftar Changelogs</a>
</div>
</body>
</html>
