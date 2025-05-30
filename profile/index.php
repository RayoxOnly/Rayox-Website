<?php
// File: MyWebsite/profile/index.php (REVISED - Fixed code leak potential)
session_start();

// --- Pengecekan Login ---
if (!isset($_SESSION['user_id'])) {
    header('Location: /login?error=Anda harus login untuk mengakses halaman ini');
    exit();
}

$user_id = $_SESSION['user_id'];
$db_error_message = null; // Inisialisasi variabel pesan error DB
$user_data = null; // Inisialisasi data user
$changelogs = []; // Inisialisasi changelogs

// --- Koneksi Database ---
$conn = new mysqli("localhost", "admin", "admin123", "login_app");
if ($conn->connect_error) {
    // Catat error tapi jangan hentikan script
    // error_log("Profile page DB connection failed: " . $conn->connect_error);
    $db_error_message = "Koneksi database gagal. Data mungkin tidak lengkap.";
    // Sediakan data fallback dari session jika koneksi gagal
    $user_data = [
        'id' => $user_id,
        'username' => $_SESSION['user'] ?? 'Error',
        'money' => 'N/A',
        'profile_picture_path' => null, // Tidak bisa ambil gambar jika DB error
        'role' => $_SESSION['role'] ?? 'user'
    ];
} else {
    // --- Ambil Data User Saat Ini (Termasuk profile_picture_path & role) ---
    $stmt_user = $conn->prepare("SELECT id, username, money, profile_picture_path, role FROM users WHERE id = ?");
    if ($stmt_user) {
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();
        if ($result_user->num_rows === 1) {
            $user_data = $result_user->fetch_assoc();
            // Simpan/Update role di session jika belum ada atau berbeda
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== $user_data['role']) {
                $_SESSION['role'] = $user_data['role'];
            }
        } else {
            // User ID di session tapi tidak ada di DB -> sesi tidak valid
            session_unset(); // Hapus variabel session
            session_destroy(); // Hancurkan session
            header('Location: /login?error=Sesi tidak valid, silakan login kembali');
            exit();
        }
        $stmt_user->close();
    } else {
        // error_log("Failed to prepare statement to get user data: " . $conn->error);
        $db_error_message = "Gagal menyiapkan query data user.";
        // Sediakan data fallback jika query gagal
        $user_data = [
            'id' => $user_id,
            'username' => $_SESSION['user'] ?? 'Error',
            'money' => 'N/A',
            'profile_picture_path' => null,
            'role' => $_SESSION['role'] ?? 'user'
        ];
    }

    // --- Ambil Data Changelogs ---
    // Hanya jika tidak ada error sebelumnya dalam mengambil data user
    if (!$db_error_message) {
        $sql_changelog = "SELECT version, description, update_date FROM changelogs ORDER BY update_date DESC, id DESC LIMIT 5"; // Limit to 5 for sidebar
        $result_changelog = $conn->query($sql_changelog);
        if ($result_changelog) {
            while ($row = $result_changelog->fetch_assoc()) {
                $changelogs[] = $row;
            }
            $result_changelog->free(); // Bebaskan memori hasil query
        } else {
             // error_log("Failed to fetch changelogs: " . $conn->error);
            $db_error_message = ($db_error_message ? $db_error_message . ' ' : '') . "Gagal mengambil data changelogs.";
        }
    }
    $conn->close(); // Koneksi ditutup di sini
}

// Tentukan path gambar default dan path yang akan ditampilkan
$default_picture_path = '/assets/images/default_avatar.png';
// Pastikan $user_data tidak null sebelum mengaksesnya
$display_picture_path = $default_picture_path; // Default awal
if ($user_data && !empty($user_data['profile_picture_path'])) {
    $display_picture_path = $user_data['profile_picture_path'];
}

// Pastikan username ada untuk judul halaman, ambil dari session jika DB error
$page_title_username = htmlspecialchars($user_data['username'] ?? ($_SESSION['user'] ?? 'User'));

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
    <link rel="manifest" href="/assets/site.webmanifest">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
     <link rel="stylesheet" href="/assets/css/main.css">
    <title>Profile | <?php echo $page_title_username; ?></title>
     <style>
         /* Tambahan spesifik HANYA jika diperlukan untuk override main.css */
         .profile-avatar img.profile-avatar-image {
             width: 100%;
             height: 100%;
             object-fit: cover;
             border-radius: 50%;
         }
         body {
             display: flex; /* Pastikan flex aktif untuk layout sidebar */
             position: relative; /* Untuk background shapes */
             min-height: 100vh;
         }
          /* Pastikan main-content dan sidebar memiliki order yang benar */
         .main-content { order: 1; flex-grow: 1; }
         #changelog-panel { order: 2; width: 320px; /* Atur lebar sesuai main.css */ }

          /* Sembunyikan sidebar kanan di layar kecil */
         @media (max-width: 992px) {
            #changelog-panel { display: none; }
            .main-content { padding-right: 30px; } /* Sesuaikan padding */
         }
          /* Penyesuaian untuk menu mobile aktif */
          @media (max-width: 768px) {
             body { display: block; } /* Kembali ke block untuk mobile menu */
             .main-content { padding-left: 20px; padding-top: 90px; order: unset; } /* Reset padding kiri, tambah padding atas */
         }
     </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <button id="hamburger-btn"><i class="fas fa-bars"></i></button>

    <nav id="mobile-menu">
        <div id="logo-sidebar">Rayox.site</div>
        <ul>
            <li><a href="/profile"><i class="fas fa-user"></i> Profile Utama</a></li>
            <li><a href="/profile/transfer"><i class="fas fa-exchange-alt"></i> Transfer Uang</a></li>
            <li><a href="/profile/saran"><i class="fas fa-comment-dots"></i> Beri Saran</a></li>
            <li><a href="/profile/settings"><i class="fas fa-cog"></i> Pengaturan</a></li>
            <li><a href="/casino"><i class="fas fa-dice"></i> Casino</a></li>
            <?php // Cek role dari data user yang diambil atau fallback ke session
            $user_role = $user_data['role'] ?? ($_SESSION['role'] ?? 'user');
            if ($user_role === 'admin'): ?>
            <li><hr></li>
            <li><a href="/admin"><i class="fas fa-shield-alt"></i> Panel Admin</a></li>
            <?php endif; ?>
            <li><hr></li>
            <li><a href="/profile/changelogs"><i class="fas fa-history"></i> Changelogs</a></li>
            <li><a href="/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <main class="main-content">
         <div class="profile-container">
             <div class="page-header">
                 <div class="profile-avatar">
                     <img src="<?php echo htmlspecialchars($display_picture_path); ?>" alt="Foto Profil <?php echo $page_title_username; ?>" class="profile-avatar-image">
                 </div>
                 <h1 class="gradient-text">Profil <?php echo $page_title_username; ?></h1>
             </div>

             <?php if (isset($db_error_message)): ?>
                <div class="message error"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($db_error_message); ?></div>
             <?php endif; ?>

             <?php if ($user_data): // Hanya tampilkan jika $user_data berhasil diambil ?>
             <div class="profile-info-card">
                 <div class="info-row">
                     <i class="fas fa-id-card"></i>
                     <strong>User ID:</strong>
                     <span class="value"><?php echo htmlspecialchars($user_data['id']); ?></span>
                 </div>

                 <div class="info-row">
                     <i class="fas fa-user-tag"></i>
                     <strong>Username:</strong>
                     <span class="value"><?php echo htmlspecialchars($user_data['username']); ?></span>
                 </div>

                 <div class="info-row">
                     <i class="fas fa-coins"></i>
                     <strong>Uang:</strong>
                     <span class="value money">$<?php echo ($user_data['money'] === 'N/A' ? 'N/A' : number_format($user_data['money'])); ?></span>
                 </div>
             </div>

             <div class="profile-actions btn-group">
                 <a href="/profile/settings" class="btn btn-primary"><i class="fas fa-user-edit"></i> Edit Profile</a>
                 <a href="/logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
             </div>
             <?php else: // Tampilkan pesan jika data user gagal diambil total ?>
                 <div class="message error"><i class="fas fa-exclamation-circle"></i> Tidak dapat memuat data profil lengkap saat ini.</div>
                 <div class="profile-actions btn-group">
                     <a href="/logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                 </div>
             <?php endif; ?>
         </div>
    </main>

    <aside id="changelog-panel">
        <h2><i class="fas fa-history"></i> Changelogs</h2>
        <?php if (!empty($changelogs)): ?>
            <?php foreach ($changelogs as $log): ?>
                <div class="changelog-item">
                    <h4><?php echo htmlspecialchars($log['version']); ?></h4>
                    <span class="date"><i class="far fa-calendar-alt"></i> <?php echo date("d F Y", strtotime($log['update_date'])); ?></span>
                    <?php
                    // Membersihkan dan memformat deskripsi changelog
                    $cleaned_description = htmlspecialchars($log['description'], ENT_QUOTES, 'UTF-8');
                    $lines = preg_split('/\r\n|\r|\n/', $cleaned_description); // Handle berbagai jenis line ending
                    echo '<ul class="changelog-list">';
                    foreach ($lines as $line) {
                        $trimmed_line = trim($line);
                        if (!empty($trimmed_line)) {
                            // Hapus tanda '-' di awal jika ada (untuk styling CSS)
                            $content = (strpos($trimmed_line, '-') === 0 && strlen($trimmed_line) > 1) ? ltrim(substr($trimmed_line, 1)) : $trimmed_line;
                            echo '<li>' . $content . '</li>'; // Output sudah di-escape
                        }
                    }
                    echo '</ul>';
                    ?>
                </div>
            <?php endforeach; ?>
             <div style="text-align:center; margin-top:1.5rem;">
                 <a href="/profile/changelogs" class="btn btn-secondary btn-sm">Lihat Semua Changelogs</a>
             </div>
        <?php // Tampilkan pesan error spesifik jika gagal load changelogs
        elseif(isset($db_error_message) && strpos(strtolower($db_error_message), 'changelogs') !== false): ?>
             <p class="no-changelog"><i class="fas fa-exclamation-triangle"></i> Gagal memuat changelogs.</p>
        <?php // Tampilkan pesan "belum ada" jika koneksi aman tapi array kosong
        elseif(empty($db_error_message)): ?>
            <p class="no-changelog"><i class="fas fa-info-circle"></i> Belum ada riwayat pembaruan.</p>
        <?php // Jangan tampilkan apa-apa jika ada error DB umum (sudah ada pesan di main content)
              endif; ?>
    </aside>

    <script>
        const hamburgerBtn = document.getElementById('hamburger-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const body = document.body;
        const mainContent = document.querySelector('.main-content');

        hamburgerBtn.addEventListener('click', (event) => {
            event.stopPropagation();
            mobileMenu.classList.toggle('open');
        });

        document.addEventListener('click', (event) => {
            if (mobileMenu.classList.contains('open') && !mobileMenu.contains(event.target) && !hamburgerBtn.contains(event.target)) {
                mobileMenu.classList.remove('open');
            }
        });
    </script>
</body>
</html>