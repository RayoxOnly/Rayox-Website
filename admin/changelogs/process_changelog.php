<?php
session_start();

// --- Pengecekan Login DAN Role Admin ---
if (!isset($_SESSION['user']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak."; // Lebih baik redirect atau tampilkan pesan error
    exit();
}

// --- Koneksi Database ---
$conn = new mysqli("localhost", "admin", "admin123", "login_app");
if ($conn->connect_error) {
    // Sebaiknya log error ini, jangan tampilkan detail ke user
    header('Location: index.php?error=Koneksi database gagal.');
    exit();
}

// --- Tentukan Aksi ---
$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? intval($_GET['id']) : null; // Untuk edit dan delete

// --- Proses Hapus ---
if ($action === 'delete' && $id !== null) {
    $stmt = $conn->prepare("DELETE FROM changelogs WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header('Location: index.php?status=Changelog berhasil dihapus.');
    } else {
        header('Location: index.php?error=Gagal menghapus changelog: ' . $stmt->error); // Tampilkan error jika perlu
    }
    $stmt->close();
    $conn->close();
    exit();
}

// --- Proses Tambah atau Edit (harus POST request) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $version = trim($_POST['version'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $update_date = $_POST['update_date'] ?? '';

    // Validasi dasar
    if (empty($version) || empty($description) || empty($update_date)) {
        $error_msg = "Semua field wajib diisi.";
        // Redirect kembali ke form dengan pesan error
        $redirect_url = ($action === 'edit' && $id) ? "manage_changelog.php?action=edit&id=$id" : "manage_changelog.php?action=add";
        header("Location: $redirect_url&error=" . urlencode($error_msg));
        exit();
    }

    // Validasi format tanggal (opsional tapi bagus)
    if (DateTime::createFromFormat('Y-m-d', $update_date) === false) {
        $error_msg = "Format tanggal tidak valid (gunakan YYYY-MM-DD).";
        $redirect_url = ($action === 'edit' && $id) ? "manage_changelog.php?action=edit&id=$id" : "manage_changelog.php?action=add";
        header("Location: $redirect_url&error=" . urlencode($error_msg));
        exit();
    }


    if ($action === 'add') {
        // --- Proses Tambah ---
        $stmt = $conn->prepare("INSERT INTO changelogs (version, description, update_date) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $version, $description, $update_date);

        if ($stmt->execute()) {
            header('Location: index.php?status=Changelog baru berhasil ditambahkan.');
        } else {
            header('Location: manage_changelog.php?action=add&error=Gagal menyimpan changelog: ' . urlencode($stmt->error));
        }
        $stmt->close();

    } elseif ($action === 'edit' && $id !== null) {
        // --- Proses Edit ---
        $stmt = $conn->prepare("UPDATE changelogs SET version = ?, description = ?, update_date = ? WHERE id = ?");
        $stmt->bind_param("sssi", $version, $description, $update_date, $id);

        if ($stmt->execute()) {
            header('Location: index.php?status=Changelog berhasil diperbarui.');
        } else {
            header('Location: manage_changelog.php?action=edit&id=' . $id . '&error=Gagal memperbarui changelog: ' . urlencode($stmt->error));
        }
        $stmt->close();

    } else {
        // Aksi tidak valid jika bukan add/edit POST
        header('Location: index.php?error=Aksi tidak valid.');
    }

} else {
    // Jika bukan POST request (kecuali untuk delete yg sudah ditangani di atas)
    header('Location: index.php');
}

$conn->close();
exit();
?>
