<?php
session_start();
require_once 'db_config.php'; // Include konfigurasi DB
header('Content-Type: application/json'); // Response selalu JSON

$response = ['success' => false, 'message' => 'Request tidak valid.'];

// Cek Login & Method POST
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Akses ditolak atau method salah.';
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];
$game_id = filter_input(INPUT_POST, 'game_id', FILTER_VALIDATE_INT);

if ($game_id === false || $game_id <= 0) {
    $response['message'] = 'ID permainan tidak valid.';
    echo json_encode($response);
    exit();
}

// Mulai Transaksi
$conn->begin_transaction();

try {
    // 1. Kunci dan Ambil Data Game (FOR UPDATE)
    $stmt_game = $conn->prepare("SELECT * FROM roulette_games WHERE id = ? FOR UPDATE");
    if (!$stmt_game) throw new Exception("Gagal menyiapkan query game: " . $conn->error);
    $stmt_game->bind_param("i", $game_id);
    $stmt_game->execute();
    $result_game = $stmt_game->get_result();

    if ($result_game->num_rows !== 1) {
        throw new Exception("Permainan tidak ditemukan.");
    }
    $game = $result_game->fetch_assoc();
    $stmt_game->close();

    // 2. Validasi Status dan Giliran
    if ($game['status'] !== 'active') {
        throw new Exception("Permainan tidak aktif.");
    }
    if ($game['current_turn_user_id'] !== $user_id) {
        throw new Exception("Bukan giliran Anda.");
    }

    // 3. Tentukan Chamber yang Akan Ditembak
    $fired_chambers = !empty($game['chambers_fired']) ? explode(',', $game['chambers_fired']) : [];
    $chamber_to_fire = 0;
    for ($i = 1; $i <= $game['max_chambers']; $i++) {
        if (!in_array((string)$i, $fired_chambers)) { // Pastikan perbandingan string
            $chamber_to_fire = $i;
            break;
        }
    }
    if ($chamber_to_fire === 0) { // Seharusnya tidak terjadi jika status 'active'
        throw new Exception("Error: Tidak ada chamber tersisa untuk ditembak?");
    }

    // 4. Cek Hasil Tembakan
    $hit_live_round = ($chamber_to_fire === (int)$game['live_chamber']);
    $new_fired_chambers_str = implode(',', array_merge($fired_chambers, [(string)$chamber_to_fire]));
    $next_turn_user_id = ($user_id === $game['creator_id']) ? $game['opponent_id'] : $game['creator_id'];

    $log_message = "User ID $user_id fired chamber #$chamber_to_fire. ";

    if ($hit_live_round) {
        // ---- KENA PELURU ----
        $winner_id = $next_turn_user_id; // Lawan adalah pemenang
        $loser_id = $user_id;
        $pot = $game['bet_amount'] * 2;
        $log_message .= "Hit LIVE round! User ID $winner_id wins $$pot.";

        // Update status game
        $stmt_finish = $conn->prepare("UPDATE roulette_games SET status = 'finished', winner_id = ?, chambers_fired = ?, finished_at = CURRENT_TIMESTAMP, current_turn_user_id = NULL WHERE id = ?");
        if (!$stmt_finish) throw new Exception("Gagal menyiapkan update finish: " . $conn->error);
        $stmt_finish->bind_param("isi", $winner_id, $new_fired_chambers_str, $game_id);
        if (!$stmt_finish->execute()) throw new Exception("Gagal mengakhiri permainan.");
        $stmt_finish->close();

        // Tambahkan uang ke pemenang (kunci baris pemenang)
        $stmt_winner_bal = $conn->prepare("SELECT id FROM users WHERE id = ? FOR UPDATE");
        if (!$stmt_winner_bal) throw new Exception("Gagal kunci pemenang: " . $conn->error);
        $stmt_winner_bal->bind_param("i", $winner_id);
        $stmt_winner_bal->execute();
        $stmt_winner_bal->close(); // Hanya perlu kunci, tidak perlu fetch

        $stmt_payout = $conn->prepare("UPDATE users SET money = money + ? WHERE id = ?");
        if (!$stmt_payout) throw new Exception("Gagal menyiapkan payout: " . $conn->error);
        $stmt_payout->bind_param("ii", $pot, $winner_id);
        if (!$stmt_payout->execute()) throw new Exception("Gagal memberikan hadiah ke pemenang.");
        $stmt_payout->close();

        // Siapkan response untuk game berakhir
        $response = [
            'success' => true,
            'game_over' => true,
            'hit' => true,
            'chamber_fired' => $chamber_to_fire,
            'chamber_fired_live' => $chamber_to_fire,
            'winner_id' => $winner_id,
            'loser_id' => $loser_id,
            'payout' => $pot,
            'message' => "BANG! Anda terkena peluru di chamber #$chamber_to_fire! Lawan memenangkan $" . number_format($pot) . "."
        ];

    } else {
        // ---- PELURU KOSONG ----
        $log_message .= "Hit BLANK. Next turn: User ID $next_turn_user_id.";

        // Update giliran dan chamber yang ditembak
        $stmt_next_turn = $conn->prepare("UPDATE roulette_games SET chambers_fired = ?, current_turn_user_id = ? WHERE id = ?");
        if (!$stmt_next_turn) throw new Exception("Gagal menyiapkan update giliran: " . $conn->error);
        $stmt_next_turn->bind_param("sii", $new_fired_chambers_str, $next_turn_user_id, $game_id);
        if (!$stmt_next_turn->execute()) throw new Exception("Gagal update giliran.");
        $stmt_next_turn->close();

        // Siapkan response untuk giliran berikutnya
        $response = [
            'success' => true,
            'game_over' => false,
            'hit' => false,
            'chamber_fired' => $chamber_to_fire,
            'next_turn_user_id' => $next_turn_user_id,
            'chambers_fired_list' => $new_fired_chambers_str,
            'message' => "KLIK! Peluru kosong di chamber #$chamber_to_fire. Giliran lawan."
        ];
    }

    // Log aksi
    log_roulette_action($game_id, $user_id, $log_message);

    // Commit Transaksi
    $conn->commit();

} catch (Exception $e) {
    $conn->rollback();
    $response['success'] = false;
    $response['message'] = "Error: " . $e->getMessage();
    error_log("Error taking turn in roulette game $game_id for user $user_id: " . $e->getMessage());
}

$conn->close();
echo json_encode($response);
exit();
?>