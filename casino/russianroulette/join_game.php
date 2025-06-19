<?php
session_start();
require_once 'db_config.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Anda harus login untuk bergabung.";
    header('Location: index.php');
    exit();
}

// Cek Method POST dan game_id
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['game_id'])) {
    header('Location: index.php');
    exit();
}

// CSRF check
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    $_SESSION['error_message'] = 'CSRF token tidak valid.';
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
    $stmt_game = $conn->prepare("SELECT id, creator_id, opponent_id, bet_amount, status FROM roulette_games WHERE id = ? FOR UPDATE");
    if (!$stmt_game) throw new Exception("Gagal menyiapkan query game: " . $conn->error);
    $stmt_game->bind_param("i", $game_id);
    $stmt_game->execute();
    $result_game = $stmt_game->get_result();

    if ($result_game->num_rows !== 1) {
        throw new Exception("Permainan tidak ditemukan.");
    }
    $game_data = $result_game->fetch_assoc();
    $stmt_game->close();

    // 2. Validasi Status Game dan Opponent
    if ($game_data['status'] !== 'waiting') {
        throw new Exception("Permainan ini tidak lagi menunggu lawan.");
    }
    if ($game_data['opponent_id'] !== null) {
        throw new Exception("Permainan ini sudah ada yang bergabung.");
    }
    if ($game_data['creator_id'] === $user_id) {
        throw new Exception("Anda tidak bisa bergabung dengan permainan Anda sendiri.");
    }

    $bet_amount = $game_data['bet_amount'];

    // 3. Kunci dan Ambil Saldo User (FOR UPDATE)
    $stmt_user = $conn->prepare("SELECT money FROM users WHERE id = ? FOR UPDATE");
    if (!$stmt_user) throw new Exception("Gagal menyiapkan query user: " . $conn->error);
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($result_user->num_rows !== 1) throw new Exception("User Anda tidak ditemukan.");
    $user_balance = $result_user->fetch_assoc()['money'];
    $stmt_user->close();

    // 4. Cek Saldo Cukup
    if ($user_balance < $bet_amount) {
        throw new Exception("Saldo Anda tidak cukup ($" . number_format($user_balance) . ") untuk bergabung dengan taruhan $" . number_format($bet_amount) . ".");
    }

    // 5. Cek apakah user sudah punya game aktif atau waiting lain
    $existing_game_id = check_user_has_active_or_waiting_game($conn, $user_id);
    if ($existing_game_id) {
        throw new Exception("Anda sudah memiliki permainan yang sedang menunggu atau aktif (ID: ".$existing_game_id."). Selesaikan atau batalkan dulu.");
    }

    // 6. Kurangi Saldo User yang Bergabung
    $stmt_update_user = $conn->prepare("UPDATE users SET money = money - ? WHERE id = ?");
    if (!$stmt_update_user) throw new Exception("Gagal menyiapkan update saldo: " . $conn->error);
    $stmt_update_user->bind_param("ii", $bet_amount, $user_id);
    if (!$stmt_update_user->execute()) throw new Exception("Gagal mengurangi saldo.");
    $stmt_update_user->close();

    // 7. Tentukan Giliran Pertama (misal: acak)
    $first_turn_user_id = (rand(0, 1) === 0) ? $game_data['creator_id'] : $user_id;

    // 8. Update Status Game
    $stmt_update_game = $conn->prepare("UPDATE roulette_games SET opponent_id = ?, status = 'active', started_at = CURRENT_TIMESTAMP, current_turn_user_id = ? WHERE id = ? AND status = 'waiting'");
    if (!$stmt_update_game) throw new Exception("Gagal menyiapkan update game: " . $conn->error);
    $stmt_update_game->bind_param("iii", $user_id, $first_turn_user_id, $game_id);
    if (!$stmt_update_game->execute() || $stmt_update_game->affected_rows !== 1) {
         // Jika affected_rows 0, berarti game sudah diambil orang lain (race condition)
        throw new Exception("Gagal memulai permainan. Mungkin sudah diambil pemain lain.");
    }
    $stmt_update_game->close();

    // 9. Log Aksi
    log_roulette_action($game_id, $user_id, "Joined game. First turn: user ID $first_turn_user_id.");

    // 10. Commit Transaksi
    $conn->commit();

    // Redirect ke halaman permainan
    header('Location: game.php?id=' . $game_id);

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error_message'] = "Error Bergabung: " . $e->getMessage();
    error_log("Error joining roulette game $game_id for user $user_id: " . $e->getMessage());
    header('Location: index.php');
}

$conn->close();
exit();
?>