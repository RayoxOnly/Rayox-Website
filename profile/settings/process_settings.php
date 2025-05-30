<?php
// File: MyWebsite/profile/settings/process_settings.php (Updated)
session_start();

// --- Pengecekan Login ---
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?error=Sesi tidak valid. Silakan login.');
    exit();
}

// --- Hanya Proses Jika Method POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); // Redirect jika bukan POST
    exit();
}

// --- Identifikasi Aksi ---
$action = $_POST['action'] ?? '';

// --- Koneksi Database ---
$conn = new mysqli("localhost", "admin", "admin123", "login_app"); // Ganti kredensial
if ($conn->connect_error) {
    header('Location: index.php?error=' . urlencode('Koneksi database gagal: ' . $conn->connect_error));
    exit();
}

$user_id = $_SESSION['user_id'];
$default_picture_path = '/assets/images/default_avatar.png';

// --- Logika untuk Ganti Password ---
if ($action === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validasi Input Dasar
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        header('Location: index.php?error=' . urlencode('Semua field password wajib diisi.'));
        $conn->close();
        exit();
    }

    if ($new_password !== $confirm_password) {
        header('Location: index.php?error=' . urlencode('Password baru dan konfirmasi tidak cocok.'));
        $conn->close();
        exit();
    }

    // Validasi Kekuatan Password Baru
    if (strlen($new_password) < 8) {
        header('Location: index.php?error=' . urlencode('Password baru minimal harus 8 karakter.'));
        $conn->close();
        exit();
    }

    // Ambil Hashed Password Saat Ini dari Database
    $stmt_get = $conn->prepare("SELECT password FROM users WHERE id = ?");
    if (!$stmt_get) {
        header('Location: index.php?error=' . urlencode('Gagal menyiapkan query: ' . $conn->error));
        $conn->close(); exit();
    }
    $stmt_get->bind_param("i", $user_id);
    if (!$stmt_get->execute()) {
        header('Location: index.php?error=' . urlencode('Gagal mengeksekusi query: ' . $stmt_get->error));
        $stmt_get->close(); $conn->close(); exit();
    }
    $result = $stmt_get->get_result();

    if ($result->num_rows !== 1) {
        header('Location: index.php?error=' . urlencode('User tidak ditemukan.'));
        $stmt_get->close(); $conn->close(); exit();
    }

    $user_data = $result->fetch_assoc();
    $current_hashed_password = $user_data['password'];
    $stmt_get->close();

    // Verifikasi Password Saat Ini
    if (!password_verify($current_password, $current_hashed_password)) {
        header('Location: index.php?error=' . urlencode('Password saat ini salah.'));
        $conn->close(); exit();
    }

    // Hash Password Baru
    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update Password di Database
    $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    if (!$stmt_update) {
        header('Location: index.php?error=' . urlencode('Gagal menyiapkan update: ' . $conn->error));
        $conn->close(); exit();
    }
    $stmt_update->bind_param("si", $new_hashed_password, $user_id);

    if ($stmt_update->execute()) {
        header('Location: index.php?success=' . urlencode('Password berhasil diubah.'));
    } else {
        header('Location: index.php?error=' . urlencode('Gagal mengubah password: ' . $stmt_update->error));
    }
    $stmt_update->close();

} elseif ($action === 'change_profile_picture') {
    // --- Logika Upload Foto Profil ---

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];
        // Mendapatkan Document Root dari $_SERVER (lebih dinamis)
        $doc_root = rtrim($_SERVER['DOCUMENT_ROOT'], '/'); // Hapus slash di akhir jika ada
        $upload_dir = $doc_root . '/uploads/profile_pictures/'; // Path absolut server
        $web_path_dir = '/uploads/profile_pictures/'; // Path relatif untuk disimpan ke DB

        // Buat direktori jika belum ada
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                 header('Location: index.php?error=' . urlencode('Gagal membuat direktori upload. Periksa izin folder.'));
                 $conn->close(); exit();
            }
        }
        // Periksa izin tulis direktori
        if (!is_writable($upload_dir)) {
            header('Location: index.php?error=' . urlencode('Direktori upload tidak dapat ditulis. Periksa izin folder (writable by www-data).'));
            $conn->close(); exit();
        }


        // Validasi File
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB

        // Cek tipe MIME sebenarnya (lebih aman daripada hanya $file['type'])
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_types)) {
            header('Location: index.php?error=' . urlencode('Format file tidak didukung (hanya JPG, PNG, GIF).'));
            $conn->close(); exit();
        }
        if ($file['size'] > $max_size) {
            header('Location: index.php?error=' . urlencode('Ukuran file terlalu besar (maksimal 2MB).'));
            $conn->close(); exit();
        }

        // Generate Nama File Unik
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = 'user_' . $user_id . '_' . bin2hex(random_bytes(8)) . '.' . $file_extension; // Lebih unik
        $target_path_server = $upload_dir . $new_filename;
        $target_path_web = $web_path_dir . $new_filename;

        // --- Hapus File Lama (jika ada & BUKAN default) ---
        $stmt_get_old = $conn->prepare("SELECT profile_picture_path FROM users WHERE id = ?");
        if ($stmt_get_old) {
            $stmt_get_old->bind_param("i", $user_id);
            $stmt_get_old->execute();
            $result_old = $stmt_get_old->get_result();
            if($result_old->num_rows === 1) {
                $old_data = $result_old->fetch_assoc();
                $old_web_path = $old_data['profile_picture_path'];
                if (!empty($old_web_path) && $old_web_path !== $default_picture_path) {
                     $old_server_path = $doc_root . $old_web_path;
                     if (file_exists($old_server_path)) {
                         @unlink($old_server_path);
                     }
                }
            }
            $stmt_get_old->close();
        }
        // --- Akhir Hapus File Lama ---

        // Pindahkan File Upload
        if (move_uploaded_file($file['tmp_name'], $target_path_server)) {
            // Update Database
            $stmt_update_pic = $conn->prepare("UPDATE users SET profile_picture_path = ? WHERE id = ?");
            if ($stmt_update_pic) {
                $stmt_update_pic->bind_param("si", $target_path_web, $user_id);
                if ($stmt_update_pic->execute()) {
                    header('Location: index.php?success=' . urlencode('Foto profil berhasil diperbarui.'));
                } else {
                    // Jika gagal update DB, hapus file yang baru diupload
                    @unlink($target_path_server);
                    header('Location: index.php?error=' . urlencode('Gagal update database: ' . $stmt_update_pic->error));
                }
                $stmt_update_pic->close();
            } else {
                 header('Location: index.php?error=' . urlencode('Gagal menyiapkan update DB: ' . $conn->error));
            }
        } else {
            header('Location: index.php?error=' . urlencode('Gagal memindahkan file upload. Kode error: ' . $file['error']));
        }

    } else {
        // Handle error upload atau tidak ada file dipilih
         $upload_error_messages = [
             UPLOAD_ERR_INI_SIZE   => 'Ukuran file melebihi batas server (php.ini).',
             UPLOAD_ERR_FORM_SIZE  => 'Ukuran file melebihi batas form.',
             UPLOAD_ERR_PARTIAL    => 'File hanya terupload sebagian.',
             UPLOAD_ERR_NO_FILE    => 'Tidak ada file yang dipilih.',
             UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary server tidak ditemukan.',
             UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk server.',
             UPLOAD_ERR_EXTENSION  => 'Ekstensi PHP menghentikan upload file.',
         ];
         $error_code = $_FILES['profile_picture']['error'] ?? UPLOAD_ERR_NO_FILE;
         $error_message = $upload_error_messages[$error_code] ?? 'Terjadi error upload tidak diketahui.';
         // Jangan redirect jika tidak ada file dipilih, itu bukan error
         if ($error_code !== UPLOAD_ERR_NO_FILE) {
            header('Location: index.php?error=' . urlencode($error_message));
            $conn->close(); exit();
         } else {
            // Jika tidak ada file dipilih, anggap saja tidak ada perubahan foto
             header('Location: index.php'); // Kembali tanpa pesan error spesifik
             $conn->close(); exit();
         }
    }

} elseif ($action === 'remove_profile_picture') {
     // --- Logika Hapus Foto Profil Kustom ---
     $doc_root = rtrim($_SERVER['DOCUMENT_ROOT'], '/');

     // Ambil path saat ini untuk dihapus dari server
     $stmt_get_current = $conn->prepare("SELECT profile_picture_path FROM users WHERE id = ?");
     $current_web_path_to_delete = null;
     if ($stmt_get_current) {
         $stmt_get_current->bind_param("i", $user_id);
         $stmt_get_current->execute();
         $result_current = $stmt_get_current->get_result();
         if($result_current->num_rows === 1) {
             $current_data = $result_current->fetch_assoc();
             $current_web_path_to_delete = $current_data['profile_picture_path'];
         }
         $stmt_get_current->close();
     }

     // Set path ke default di database
     $stmt_remove = $conn->prepare("UPDATE users SET profile_picture_path = ? WHERE id = ?");
     if ($stmt_remove) {
         $stmt_remove->bind_param("si", $default_picture_path, $user_id);
         if ($stmt_remove->execute()) {
             // Jika update DB berhasil, hapus file lama (jika ada dan bukan default)
             if (!empty($current_web_path_to_delete) && $current_web_path_to_delete !== $default_picture_path) {
                 $server_path_to_delete = $doc_root . $current_web_path_to_delete;
                 if (file_exists($server_path_to_delete)) {
                     @unlink($server_path_to_delete);
                 }
             }
             header('Location: index.php?success=' . urlencode('Foto profil kustom berhasil dihapus.'));
         } else {
             header('Location: index.php?error=' . urlencode('Gagal mereset foto profil di database: ' . $stmt_remove->error));
         }
         $stmt_remove->close();
     } else {
         header('Location: index.php?error=' . urlencode('Gagal menyiapkan reset DB: ' . $conn->error));
     }

} else {
    // Jika aksi tidak dikenali
    header('Location: index.php?error=Aksi tidak valid.');
}

$conn->close();
exit();
?>