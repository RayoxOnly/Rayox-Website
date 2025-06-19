<?php
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Request tidak valid.', 'game_state' => null];
$default_pfp = '/assets/images/default_avatar.png'; // Path default PFP

// Cek Login & Method GET
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'GET') {
    $response['message'] = 'Akses ditolak atau method salah.';
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];
$game_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($game_id === false || $game_id <= 0) {
    $response['message'] = 'ID permainan tidak valid.';
    echo json_encode($response);
    exit();
}

try {
    // Ambil data game terbaru, tambahkan profile_picture_path
    $sql_get_state = "SELECT rg.id, rg.status, rg.current_turn_user_id, rg.chambers_fired, rg.winner_id, rg.max_chambers,
                             uc.username as creator_username, uo.username as opponent_username, uw.username as winner_username,
                             rg.creator_id, rg.opponent_id, rg.bet_amount,
                             -- Ambil path PFP, gunakan default jika NULL atau kosong
                             COALESCE(NULLIF(uc.profile_picture_path, ''), ?) AS creator_pfp,
                             COALESCE(NULLIF(uo.profile_picture_path, ''), ?) AS opponent_pfp
                      FROM roulette_games rg
                      JOIN users uc ON rg.creator_id = uc.id
                      LEFT JOIN users uo ON rg.opponent_id = uo.id
                      LEFT JOIN users uw ON rg.winner_id = uw.id
                      WHERE rg.id = ? AND (rg.creator_id = ? OR rg.opponent_id = ?)";

    $stmt_game = $conn->prepare($sql_get_state);
     if (!$stmt_game) throw new Exception("Gagal menyiapkan query get state: " . $conn->error);

    // Bind parameter: 2 string (default PFP), 3 integer (id, user_id, user_id)
    $stmt_game->bind_param("ssiii", $default_pfp, $default_pfp, $game_id, $user_id, $user_id);
    $stmt_game->execute();
    $result_game = $stmt_game->get_result();

    if ($result_game->num_rows === 1) {
        $game_state = $result_game->fetch_assoc();
        $response['success'] = true;
        $response['message'] = 'Data game berhasil diambil.';
        // Sertakan path PFP dalam response
        $response['game_state'] = [
            'id' => $game_state['id'],
            'status' => $game_state['status'],
            'current_turn_user_id' => $game_state['current_turn_user_id'],
            'chambers_fired' => $game_state['chambers_fired'],
            'winner_id' => $game_state['winner_id'],
            'max_chambers' => $game_state['max_chambers'],
             'winner_username' => $game_state['winner_username'],
             'pot' => $game_state['bet_amount'] * 2,
             'creator_username' => $game_state['creator_username'],
             'opponent_username' => $game_state['opponent_username'],
             'creator_id' => $game_state['creator_id'],
             'opponent_id' => $game_state['opponent_id'],
             'creator_pfp' => $game_state['creator_pfp'],   // <-- PFP Creator
             'opponent_pfp' => $game_state['opponent_pfp'], // <-- PFP Opponent
        ];
        // Tambahkan chamber_fired_live jika game sudah selesai
        if ($game_state['status'] === 'finished') {
            $fired = !empty($game_state['chambers_fired']) ? explode(',', $game_state['chambers_fired']) : [];
            $live_chamber = isset($game_state['live_chamber']) ? (int)$game_state['live_chamber'] : null;
            if ($live_chamber && in_array((string)$live_chamber, $fired)) {
                $response['game_state']['chamber_fired_live'] = $live_chamber;
            }
        }
    } else {
         $response['message'] = 'Permainan tidak ditemukan atau Anda tidak terlibat.';
         $response['game_state'] = ['status' => 'not_found']; // Tandai status khusus
    }
    $stmt_game->close();

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = "Error mengambil status: " . $e->getMessage();
    error_log("Error getting state for roulette game $game_id: " . $e->getMessage());
}

$conn->close();
echo json_encode($response);
exit();
?>