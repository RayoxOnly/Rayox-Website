<?php
session_start();
require_once 'db_config.php'; // Include konfigurasi DB

// Cek Login
if (!isset($_SESSION['user_id'])) {
    header('Location: /login?error=Anda harus login untuk bermain');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['user'];

// Ambil Saldo User Saat Ini
$user_money = 0;
$stmt_money = $conn->prepare("SELECT money FROM users WHERE id = ?");
if ($stmt_money) {
    $stmt_money->bind_param("i", $user_id);
    $stmt_money->execute();
    $result_money = $stmt_money->get_result();
    if ($result_money->num_rows === 1) {
        $user_money = $result_money->fetch_assoc()['money'];
    }
    $stmt_money->close();
} else {
    // Handle error prepare statement
     $_SESSION['error_message'] = "Gagal mengambil data saldo.";
}


// Ambil Daftar Game yang Menunggu (Waiting Games)
$waiting_games = [];
// Pilih kolom yang dibutuhkan, join dengan users untuk nama creator
$sql_waiting = "SELECT rg.id, rg.bet_amount, rg.max_chambers, u.username as creator_username
                FROM roulette_games rg
                JOIN users u ON rg.creator_id = u.id
                WHERE rg.status = 'waiting' AND rg.creator_id != ?
                ORDER BY rg.created_at DESC LIMIT 20"; // Limit hasil
$stmt_waiting = $conn->prepare($sql_waiting);
if ($stmt_waiting) {
    $stmt_waiting->bind_param("i", $user_id); // Jangan tampilkan game sendiri
    $stmt_waiting->execute();
    $result_waiting = $stmt_waiting->get_result();
    while ($row = $result_waiting->fetch_assoc()) {
        $waiting_games[] = $row;
    }
    $stmt_waiting->close();
} else {
     $_SESSION['error_message'] = "Gagal mengambil daftar permainan.";
}


// Ambil Game Aktif User (jika ada)
$active_game_id = null;
$sql_active = "SELECT id FROM roulette_games
               WHERE (creator_id = ? OR opponent_id = ?) AND status = 'active'
               LIMIT 1";
$stmt_active = $conn->prepare($sql_active);
if($stmt_active) {
    $stmt_active->bind_param("ii", $user_id, $user_id);
    $stmt_active->execute();
    $result_active = $stmt_active->get_result();
    if ($result_active->num_rows === 1) {
        $active_game_id = $result_active->fetch_assoc()['id'];
    }
    $stmt_active->close();
}

// Jika user sudah ada di game aktif, redirect ke halaman game
if ($active_game_id) {
    header('Location: game.php?id=' . $active_game_id);
    exit();
}

$conn->close(); // Tutup koneksi setelah semua query selesai

// Ambil pesan status/error dari session (jika ada dari redirect)
$status_message = $_SESSION['status_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['status_message'], $_SESSION['error_message']); // Hapus pesan setelah dibaca

// CSRF token setup
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Russian Roulette - Lobi | Rayox</title>
    <link rel="stylesheet" href="/assets/css/main.css"> <link rel="stylesheet" href="roulette.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
    <link rel="manifest" href="/assets/site.webmanifest">
</head>
<body>
    <div class="page-container rr-lobby-container">
        <div class="page-header">
            <h1 class="gradient-text"><i class="fas fa-skull-crossbones"></i> Russian Roulette</h1>
            <p class="subtitle">Selamat datang, <?php echo htmlspecialchars($username); ?>! Saldo Anda: <span class="money">$<?php echo number_format($user_money); ?></span></p>
        </div>

        <?php if ($status_message): ?>
            <div class="message success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($status_message); ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="message error"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <section class="rr-section">
            <h2><i class="fas fa-plus-circle"></i> Buat Game Baru</h2>
            <form action="create_game.php" method="POST" class="rr-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="form-group">
                    <label for="bet_amount"><i class="fas fa-coins"></i> Jumlah Taruhan:</label>
                    <input type="number" id="bet_amount" name="bet_amount" min="100" placeholder="Minimal $100" required>
                </div>
                <div class="form-group">
                    <label for="max_chambers"><i class="fas fa-circle"></i> Jumlah Chamber:</label>
                    <select id="max_chambers" name="max_chambers" required>
                        <option value="6" selected>6 Chambers (Standar)</option>
                        <option value="5">5 Chambers</option>
                        <option value="7">7 Chambers</option>
                        <option value="8">8 Chambers</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Buat Meja</button>
            </form>
        </section>

        <section class="rr-section">
            <h2><i class="fas fa-list-ul"></i> Daftar Meja Tersedia</h2>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Pembuat</th>
                            <th>Taruhan</th>
                            <th>Chambers</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($waiting_games)): ?>
                            <?php foreach ($waiting_games as $game): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($game['creator_username']); ?></td>
                                <td class="money">$<?php echo number_format($game['bet_amount']); ?></td>
                                <td><?php echo htmlspecialchars($game['max_chambers']); ?></td>
                                <td class="actions">
                                    <?php if ($user_money >= $game['bet_amount']): ?>
                                    <form action="join_game.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                        <input type="hidden" name="game_id" value="<?php echo $game['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-sign-in-alt"></i> Join</button>
                                    </form>
                                    <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled title="Saldo tidak cukup"><i class="fas fa-times"></i> Join</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="no-data">Belum ada meja tersedia. Buat meja baru!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
             <p class="rr-note">Meja akan otomatis hilang jika dibatalkan atau permainan dimulai.</p>
        </section>

        <div class="rr-navigation">
            <a href="/casino" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali ke Casino</a>
            <a href="/profile" class="btn btn-secondary btn-sm"><i class="fas fa-user"></i> Kembali ke Profile</a>
        </div>
    </div>
    </body>
</html>