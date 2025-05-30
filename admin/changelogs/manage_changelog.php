<?php
// File: admin/changelogs/manage_changelog.php (Redesigned)
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
$action = $_GET['action'] ?? 'add'; // Default to add
$page_title = "Tambah Changelog Baru";
$form_action_url = "process_changelog.php?action=add";
$changelog_id = null;
$version = '';
$description = '';
$update_date = date('Y-m-d'); // Default ke hari ini
$submit_button_text = "Simpan Changelog";
$submit_button_icon = "fas fa-save";

// --- Cek Aksi Edit ---
if ($action === 'edit' && isset($_GET['id'])) {
    $changelog_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT version, description, update_date FROM changelogs WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $changelog_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $page_title = "Edit Changelog (ID: " . $changelog_id . ")";
            $form_action_url = "process_changelog.php?action=edit&id=" . $changelog_id;
            $version = $row['version'];
            $description = $row['description'];
            $update_date = $row['update_date'];
            $submit_button_text = "Update Changelog";
            $submit_button_icon = "fas fa-sync-alt";
        } else {
            header('Location: index.php?error=Changelog tidak ditemukan.');
            exit();
        }
        $stmt->close();
    } else {
         header('Location: index.php?error=Gagal menyiapkan query.');
         exit();
    }
} elseif ($action !== 'add') {
    // Jika action bukan add atau edit yang valid
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
<title>Admin | <?= $page_title ?> | Rayox</title>
<link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
<link rel="manifest" href="/assets/site.webmanifest">
<link rel="stylesheet" href="/assets/css/main.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="page-container" style="max-width: 700px;"> <div class="page-header no-border">
        <h1 class="gradient-text"><i class="fas <?= ($action === 'edit') ? 'fa-edit' : 'fa-plus-circle' ?>"></i> <?= $page_title ?></h1>
    </div>

    <?php if (isset($_GET['error'])): ?>
    <div class="message error fade-out"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <form action="<?= $form_action_url ?>" method="POST">
        <div class="form-group">
            <label for="version"><i class="fas fa-code-branch"></i> Versi:</label>
            <input type="text" id="version" name="version" value="<?= htmlspecialchars($version) ?>" placeholder="Contoh: v1.2.0 atau Build 20250430" required>
        </div>

        <div class="form-group">
            <label for="update_date"><i class="fas fa-calendar-alt"></i> Tanggal Update:</label>
            <input type="date" id="update_date" name="update_date" value="<?= htmlspecialchars($update_date) ?>" required>
        </div>

        <div class="form-group">
            <label for="description"><i class="fas fa-align-left"></i> Deskripsi Perubahan:</label>
            <textarea id="description" name="description" placeholder="Jelaskan perubahan apa saja yang ada di versi ini. Gunakan baris baru untuk setiap poin." required><?= htmlspecialchars($description) ?></textarea>
             <small style="color: #6b7280; display: block; margin-top: 5px;">Tips: Awali setiap poin perubahan dengan tanda '-' agar terlihat seperti daftar di halaman profile.</small>
        </div>

        <div class="btn-group" style="justify-content: flex-start;"> <button type="submit" class="btn btn-primary"><i class="<?= $submit_button_icon ?>"></i> <?= $submit_button_text ?></button>
             <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
        </div>
    </form>

    </div>

<script>
     // Script fade out message
    const messages = document.querySelectorAll('.message.fade-out');
    messages.forEach(msg => {
        // setTimeout(() => msg.remove(), 5000);
    });
 </script>
</body>
</html>