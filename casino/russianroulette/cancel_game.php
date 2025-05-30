<?php
session_start();
require_once 'db_config.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Anda harus login untuk membatalkan.";
    header('Location: index.php');
    exit();
}

// Cek Method POST dan game_id
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['game_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$game_id = filter_input(INPUT_POST, 'game_id', FILTER_VALIDATE_INT);

if ($game_id === false || $game_id <= 0) {
    $_SESSION['error_message'] = "ID permainan tidak valid.";
    header('Location: index.php');
    exit();
}

// Mulai Transaksi
$conn->begin_transaction();

try {
    // 1. Kunci dan Ambil Detail Game (FOR UPDATE)
    $stmt_game = $conn->prepare("SELECT id, creator_id, bet_amount, status FROM roulette_games WHERE id = ? FOR UPDATE");
    if (!$stmt_game) throw new Exception("Gagal menyiapkan query game: " . $conn->error);
    $stmt_game->bind_param("i", $game_id);
    $stmt_game->execute();
    $result_game = $stmt_game->get_result();

    if ($result_game->num_rows !== 1) {
        throw new Exception("Permainan tidak ditemukan.");
    }
    $game_data = $result_game->fetch_assoc();
    $stmt_game->close();

    // 2. Validasi Kepemilikan dan Status
    if ($game_data['creator_id'] !== $user_id) {
        // Hanya kreator yang bisa batal, atau admin (belum diimplementasi)
        // Atau bisa juga user yang terlibat jika game sudah 'active' tapi error? (Perlu aturan jelas)
        throw new Exception("Anda bukan pembuat permainan ini.");
    }
    if ($game_data['status'] !== 'waiting') { // Hanya bisa batal jika masih waiting
        // Kecuali ada logika admin override atau kondisi khusus
        throw new Exception("Permainan ini sudah dimulai atau selesai, tidak bisa dibatalkan.");
    }

    $bet_amount = $game_data['bet_amount'];

    // 3. Update Status Game menjadi Cancelled
    $stmt_cancel = $conn->prepare("UPDATE roulette_games SET status = 'cancelled', finished_at = CURRENT_TIMESTAMP WHERE id = ? AND status = 'waiting'");
    if (!$stmt_cancel) throw new Exception("Gagal menyiapkan update cancel: " . $conn->error);
    $stmt_cancel->bind_param("i", $game_id);
    if (!$stmt_cancel->execute() || $stmt_cancel->affected_rows !== 1) {
        throw new Exception("Gagal membatalkan permainan (mungkin sudah dimulai?).");
    }
    $stmt_cancel->close();

    // 4. Kembalikan Uang ke Kreator (Kunci baris user)
    $stmt_user_lock = $conn->prepare("SELECT id FROM users WHERE id = ? FOR UPDATE");
     if (!$stmt_user_lock) throw new Exception("Gagal kunci user: " . $conn->error);
     $stmt_user_lock->bind_param("i", $user_id);
     $stmt_user_lock->execute();
     $stmt_user_lock->close(); // Kunci saja

    $stmt_refund = $conn->prepare("UPDATE users SET money = money + ? WHERE id = ?");
    if (!$stmt_refund) throw new Exception("Gagal menyiapkan refund: " . $conn->error);
    $stmt_refund->bind_param("ii", $bet_amount, $user_id);
    if (!$stmt_refund->execute()) {
        throw new Exception("Gagal mengembalikan taruhan.");
    }
    $stmt_refund->close();

    // 5. Log Aksi
    log_roulette_action($game_id, $user_id, "Cancelled waiting game. Bet $" . number_format($bet_amount) . " refunded.");

    // 6. Commit Transaksi
    $conn->commit();

    $_SESSION['status_message'] = "Meja permainan #${game_id} berhasil dibatalkan dan taruhan dikembalikan.";
    header('Location: index.php'); // Kembali ke lobi

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error_message'] = "Error Batal: " . $e->getMessage();
    error_log("Error cancelling roulette game $game_id for user $user_id: " . $e->getMessage());
    header('Location: index.php'); // Kembali ke lobi
}

$conn->close();
exit();
?>