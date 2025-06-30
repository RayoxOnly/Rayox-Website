<?php
// File: MyWebsite/profile/settings/index.php (REVISED AGAIN - Double Check)
ini_set('display_errors', 0); // Pastikan error tidak tampil di production
ini_set('log_errors', 1);   // Pastikan error dicatat di log
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED); // Laporkan semua kecuali notice/deprecated

session_start();

// --- Pengecekan Login ---
if (!isset($_SESSION['user_id'])) {
    // Tambahkan exit setelah header
    header('Location: /login?error=Anda harus login untuk mengakses halaman ini');
    exit();
}

$user_id = $_SESSION['user_id'];
// Ambil username dari session, beri default jika tidak ada
$username = $_SESSION['user'] ?? 'User';

// --- Koneksi Database untuk Ambil Path Gambar Saat Ini ---
// Definisikan variabel koneksi di luar blok try-catch agar bisa dicek nanti
$conn = null;
$current_pic_path = '/assets/images/default_avatar.png'; // Default
$db_connection_error = null;

// Gunakan try-catch untuk menangani potensi error saat koneksi/query
try {
    // Sembunyikan error koneksi bawaan PHP, tangani manual
    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = new mysqli("localhost", "admin", "admin123", "login_app");

    // Periksa error koneksi manual
    if ($conn->connect_error) {
        // error_log("Database connection failed: " . $conn->connect_errno . " - " . $conn->connect_error);
        throw new Exception("Gagal terhubung ke DB untuk mengambil foto profil.");
    }

    // Set character set (penting)
    if (!$conn->set_charset("utf8mb4")) {
        // error_log("Error loading character set utf8mb4: " . $conn->error);
        // Tidak perlu throw exception, tapi bagus untuk dicatat
    }

    $stmt_pic = $conn->prepare("SELECT profile_picture_path FROM users WHERE id = ?");
    if (!$stmt_pic) {
        // error_log("Failed to prepare statement to get picture path: " . $conn->error);
        throw new Exception("Gagal menyiapkan query ambil foto profil.");
    }

    $stmt_pic->bind_param("i", $user_id);
    $stmt_pic->execute();
    $result_pic = $stmt_pic->get_result();

    if ($result_pic->num_rows === 1) {
        $pic_data = $result_pic->fetch_assoc();
        // Gunakan path dari DB jika ada dan tidak kosong
        if (!empty($pic_data['profile_picture_path'])) {
            $current_pic_path = $pic_data['profile_picture_path'];
        }
    }
    // Tidak perlu else di sini, jika user tidak ada, biarkan default path

    $stmt_pic->close();

} catch (Exception $e) {
    $db_connection_error = $e->getMessage();
} finally {
    // Pastikan koneksi ditutup jika berhasil dibuka
    if ($conn && !$conn->connect_error) {
        $conn->close();
    }
}
// --- Akhir Koneksi Database ---

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengaturan Akun | <?php echo htmlspecialchars($username); ?> | Rayox</title>
<link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/LOGO/favicon32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/LOGO/favicon16x16.png">
<link rel="manifest" href="/assets/site.webmanifest">
<link rel="stylesheet" href="/assets/css/main.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    /* (Salin CSS tambahan dari respons sebelumnya jika diperlukan) */
    .form-group input[type="file"] { padding: 0.5rem; border: 1px dashed #ccc; background-color: #f9f9f9; border-radius: 8px; transition: border-color 0.2s ease; }
    .form-group input[type="file"]:hover { border-color: #4f46e5; }
    .form-group input[type="file"]::file-selector-button { padding: 0.5rem 1rem; border: none; background: #e0e7ff; color: #4f46e5; border-radius: 4px; cursor: pointer; margin-right: 10px; transition: background-color 0.2s; }
    .form-group input[type="file"]::file-selector-button:hover { background-color: #c7d2fe; }
    .current-profile-picture img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #e0e7ff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: block; margin: 10px auto 0; }
    .current-profile-picture p { margin-bottom: 5px; font-weight: 500; color: #4b5563; }
     .section { background-color: rgba(255, 255, 255, 0.6); padding: 1.5rem; margin-bottom: 2rem; border-radius: 15px; border: 1px solid rgba(0, 0, 0, 0.05); box-shadow: 0 3px 10px rgba(0,0,0,0.03); }
     .section h2 { margin-top: 0; margin-bottom: 1.5rem; padding-bottom: 0.8rem; border-bottom: 1px solid rgba(79, 70, 229, 0.1); font-size: 1.5rem; color: #4f46e5; }
      .section h2 i { margin-right: 10px; }
</style>
</head>
<body>
<div class="floating-shapes"> <div class="shape"></div><div class="shape"></div><div class="shape"></div><div class="shape"></div>
</div>

<div class="page-container"> <div class="page-header">
        <h1 class="gradient-text"><i class="fas fa-cog"></i> Pengaturan Akun</h1>
        <p class="subtitle">Kelola informasi dan keamanan akun Anda.</p>
    </div>

    <?php if (isset($_GET['success'])): ?>
    <div class="message success fade-out"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
    <div class="message error fade-out"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>
     <?php if ($db_connection_error): ?>
     <div class="message error"><i class="fas fa-database"></i> <?php echo htmlspecialchars($db_connection_error); ?></div>
     <?php endif; ?>

    <section class="section">
        <h2><i class="fas fa-user-circle"></i> Foto Profil</h2>
        <form action="process_settings.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="change_profile_picture">

            <div class="current-profile-picture" style="margin-bottom: 1.5rem; text-align:center;">
                 <p>Foto Profil Saat Ini:</p>
                 <img src="<?php echo htmlspecialchars($current_pic_path); ?>" alt="Current Profile Picture" >
            </div>

            <div class="form-group">
                <label for="profile_picture"><i class="fas fa-upload"></i> Pilih Gambar Baru (Max: 2MB, JPG/PNG/GIF):</label>
                <input type="file" id="profile_picture" name="profile_picture" accept="image/jpeg, image/png, image/gif">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Upload Foto Baru</button>
        </form>

        <?php // Tambahkan form Hapus Foto jika path bukan default dan tidak ada error koneksi DB awal
        if (!$db_connection_error && basename($current_pic_path) !== 'default_avatar.png'): ?>
        <form action="process_settings.php" method="POST" style="margin-top: 1rem; text-align:center;">
            <input type="hidden" name="action" value="remove_profile_picture">
            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus foto profil kustom Anda?')">
                <i class="fas fa-trash"></i> Hapus Foto Kustom
            </button>
        </form>
        <?php endif; ?>
    </section>

    <section class="section">
        <h2><i class="fas fa-key"></i> Ganti Password</h2>
        <form action="process_settings.php" method="POST" id="changePasswordForm">
            <input type="hidden" name="action" value="change_password">
            <div class="form-group">
                <label for="current_password">Password Saat Ini:</label>
                <input type="password" id="current_password" name="current_password" required placeholder="Masukkan password lama Anda">
            </div>
            <div class="form-group">
                <label for="new_password">Password Baru (min. 8 karakter):</label>
                <input type="password" id="new_password" name="new_password" required placeholder="Masukkan password baru" minlength="8">
            </div>
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password Baru:</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="Ulangi password baru" minlength="8">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Ubah Password</button>
        </form>
    </section>

    <section class="section">
        <h2><i class="fas fa-info-circle"></i> Informasi Umum</h2>
        <div class="form-group">
            <label for="username_display"><i class="fas fa-user"></i> Username:</label>
            <input type="text" id="username_display" name="username_display" value="<?php echo htmlspecialchars($username); ?>" disabled>
        </div>
        <div class="form-group">
            <label for="userid_display"><i class="fas fa-id-badge"></i> User ID:</label>
            <input type="text" id="userid_display" name="userid_display" value="<?php echo htmlspecialchars($user_id); ?>" disabled>
        </div>
     </section>

    <div style="text-align: center; margin-top: 2rem;">
     <a href="/profile" class="back-link btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Profile</a>
    </div>
</div>

<script>
    // Client-side validation untuk cek kecocokan password baru
    const passwordForm = document.getElementById('changePasswordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(event) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const newPasswordInput = document.getElementById('new_password');
            const confirmPasswordInput = document.getElementById('confirm_password');

            // Hapus indikator error sebelumnya
            newPasswordInput.style.borderColor = '';
            confirmPasswordInput.style.borderColor = '';
            const oldError = passwordForm.querySelector('.message.error.validation-error'); // Target specific validation error
            if (oldError) oldError.remove();


            if (newPassword !== confirmPassword) {
                 confirmPasswordInput.style.borderColor = 'red';
                 const errorDiv = document.createElement('div');
                 errorDiv.className = 'message error validation-error'; // Tambah class validation-error
                 errorDiv.style.marginTop = '1rem';
                 errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Password baru dan konfirmasi tidak cocok!';

                 passwordForm.insertBefore(errorDiv, passwordForm.querySelector('button[type="submit"]'));

                 // Hapus pesan error setelah beberapa detik
                 setTimeout(() => {
                     if (errorDiv.parentNode) {
                        errorDiv.remove();
                     }
                     confirmPasswordInput.style.borderColor = '';
                    }, 5000);

                 event.preventDefault();
             }
        });
    }

     // Script fade out message notifikasi sukses/error dari server
    const messages = document.querySelectorAll('.message.fade-out');
    messages.forEach(msg => {
        setTimeout(() => {
             msg.style.transition = 'opacity 0.5s ease-out';
             msg.style.opacity = '0';
             setTimeout(() => msg.remove(), 500);
         }, 4500);
    });
</script>

</body>
</html>