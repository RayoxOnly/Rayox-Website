<?php
// File: MyWebsite/profile/changelogs/index.php (File Baru)
session_start();

// --- Pengecekan Login ---
if (!isset($_SESSION['user_id'])) {
    header('Location: /login?error=Anda harus login untuk melihat changelogs');
    exit();
}

// --- Koneksi Database ---
// Ganti dengan kredensial database Anda jika berbeda
$conn = new mysqli("localhost", "admin", "admin123", "login_app");
if ($conn->connect_error) {
    // Tampilkan pesan error sederhana daripada menghentikan skrip sepenuhnya
    $error_message = "Gagal terhubung ke database. Silakan coba lagi nanti.";
    $changelogs = []; // Tetapkan array kosong agar halaman tetap render
} else {
    // --- Ambil Data Changelogs ---
    $sql_changelog = "SELECT version, description, update_date FROM changelogs ORDER BY update_date DESC, id DESC";
    $result_changelog = $conn->query($sql_changelog);
    $changelogs = [];
    if ($result_changelog) {
        while ($row = $result_changelog->fetch_assoc()) {
            $changelogs[] = $row;
        }
    } else {
        $error_message = "Gagal mengambil data changelogs: " . $conn->error;
    }
    $conn->close(); // Tutup koneksi
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Changelogs | Rayox</title>
<link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/LOGO/favicon32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/LOGO/favicon16x16.png">
<link rel="manifest" href="/assets/site.webmanifest">
<link rel="stylesheet" href="/assets/css/main.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    /* Tambahkan style spesifik jika perlu, override main.css */
    body {
        /* Pastikan background sesuai dengan tema jika diperlukan */
         background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
         display: block; /* Override flex jika ada di main.css */
         min-height: 100vh;
         padding-top: 2rem; /* Beri jarak dari atas */
    }
    .changelog-container {
        /* Gunakan style .page-container dari main.css */
        background-color: rgba(255, 255, 255, 0.95);
        padding: 2rem;
        margin: 1rem auto; /* Center container */
        border-radius: 15px; /* Sesuaikan radius */
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        max-width: 800px; /* Sesuaikan lebar maksimum */
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }
     .changelog-header {
         text-align: center;
         margin-bottom: 2rem;
         padding-bottom: 1rem;
         border-bottom: 1px solid rgba(79, 70, 229, 0.1);
         position: relative;
     }
     .changelog-header::after {
         content: '';
         position: absolute;
         bottom: -1px;
         left: 50%;
         transform: translateX(-50%);
         width: 60px;
         height: 3px;
         background: linear-gradient(to right, #4f46e5, #818cf8);
         border-radius: 2px;
     }
     .changelog-header h1 {
         font-size: 2rem; /* Sesuaikan ukuran */
         margin-bottom: 0.5rem;
         /* Gunakan class gradient jika diinginkan */
         /* background: linear-gradient(to right, #4f46e5, #818cf8);
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
         background-clip: text; */
         color: #4f46e5; /* Atau warna solid */
     }

    .changelog-item {
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px dashed rgba(79, 70, 229, 0.15);
    }
    .changelog-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    .changelog-item h4 {
        font-size: 1.2rem; /* Sedikit lebih besar */
        color: #334155;
        margin-bottom: 0.5rem;
        /* Optional gradient text */
        /* background: linear-gradient(to right, #4f46e5, #818cf8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text; */
        display: inline-block;
    }
    .changelog-item .date {
        font-size: 0.85rem;
        color: #6b7280;
        margin-bottom: 0.8rem;
        display: block;
    }
    .changelog-item .date i {
        margin-right: 5px;
        color: #818cf8;
    }
    .changelog-item ul.changelog-list {
        font-size: 0.95rem; /* Sedikit lebih besar */
        line-height: 1.7;
        color: #4b5563;
        margin: 0;
        padding-left: 0; /* Hapus padding default */
        list-style-type: none; /* Hapus bullet default */
    }
     .changelog-item ul.changelog-list li {
        margin-bottom: 0.5rem;
        position: relative;
        padding-left: 20px; /* Ruang untuk ikon/bullet kustom */
    }
     .changelog-item ul.changelog-list li::before {
        /* Bullet kustom */
        content: "•";
        position: absolute;
        left: 0;
        top: 0px;
        color: #4f46e5;
        font-weight: bold;
        font-size: 1.2rem;
        line-height: 1.7;
     }
    .no-changelog {
        /* Reuse style from main.css or define here */
        font-size: 1rem;
        color: #6b7280;
        text-align: center;
        margin-top: 2rem;
        padding: 20px;
        background-color: rgba(79, 70, 229, 0.05);
        border-radius: 10px;
    }
    .back-link-container {
        text-align: center;
        margin-top: 2rem;
    }
</style>
</head>
<body>
<div class="changelog-container">
    <div class="changelog-header">
        <h1><i class="fas fa-history"></i> Changelogs</h1>
    </div>

    <?php if (isset($error_message)): ?>
        <div class="message error"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <?php if (!empty($changelogs)): ?>
        <?php foreach ($changelogs as $log): ?>
            <div class="changelog-item">
                <h4><?= htmlspecialchars($log['version']) ?></h4>
                <span class="date"><i class="far fa-calendar-alt"></i> <?= date("d F Y", strtotime($log['update_date'])) ?></span>
                <?php
                // Parsing deskripsi (sama seperti di profile/index.php)
                $raw_description = htmlspecialchars($log['description']);
                $lines = explode("\n", $raw_description);
                echo '<ul class="changelog-list">';
                foreach ($lines as $line) {
                    $trimmed_line = trim($line);
                    if (!empty($trimmed_line)) {
                        // Hapus tanda '-' di awal jika ada untuk styling list
                        $content = (strpos($trimmed_line, '-') === 0) ? ltrim(substr($trimmed_line, 1)) : $trimmed_line;
                        echo '<li>' . $content . '</li>';
                    }
                }
                echo '</ul>';
                ?>
            </div>
        <?php endforeach; ?>
    <?php elseif (!isset($error_message)): // Tampilkan pesan ini hanya jika tidak ada error dan tidak ada data ?>
        <p class="no-changelog"><i class="fas fa-info-circle"></i> Belum ada riwayat pembaruan yang tercatat.</p>
    <?php endif; ?>

    <div class="back-link-container">
         <a href="/profile" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Profile</a>
    </div>
</div>
</body>
</html>