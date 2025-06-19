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
$default_pfp = '/assets/images/default_avatar.png'; // Path default PFP

// Ambil ID Game dari URL
$game_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($game_id === false || $game_id <= 0) {
    $_SESSION['error_message'] = "ID permainan tidak valid.";
    header('Location: index.php'); // Redirect ke lobi jika ID game salah
    exit();
}

// Ambil Detail Game Saat Ini dari DB (Termasuk PFP)
$game_data = null;

$sql_game = "SELECT rg.*,
                    uc.username as creator_username,
                    uo.username as opponent_username,
                    uw.username as winner_username,
                    -- Ambil path PFP, gunakan default jika NULL atau kosong
                    COALESCE(NULLIF(uc.profile_picture_path, ''), ?) AS creator_pfp,
                    COALESCE(NULLIF(uo.profile_picture_path, ''), ?) AS opponent_pfp
             FROM roulette_games rg
             JOIN users uc ON rg.creator_id = uc.id
             LEFT JOIN users uo ON rg.opponent_id = uo.id
             LEFT JOIN users uw ON rg.winner_id = uw.id
             WHERE rg.id = ? AND (rg.creator_id = ? OR rg.opponent_id = ?)"; // Pastikan user ini terlibat
$stmt_game = $conn->prepare($sql_game);

if (!$stmt_game) {
    $_SESSION['error_message'] = "Gagal menyiapkan query game: " . $conn->error;
    header('Location: index.php');
    exit();
}

// Bind parameter: 2 string (default PFP), 3 integer (id, user_id, user_id)
$stmt_game->bind_param("ssiii", $default_pfp, $default_pfp, $game_id, $user_id, $user_id);
$stmt_game->execute();
$result_game = $stmt_game->get_result();

if ($result_game->num_rows !== 1) {
    // Cek apakah game ada tapi user tidak terlibat (misal akses ID game orang lain)
    $stmt_check_exist = $conn->prepare("SELECT id FROM roulette_games WHERE id = ?");
    $stmt_check_exist->bind_param("i", $game_id);
    $stmt_check_exist->execute();
    if ($stmt_check_exist->get_result()->num_rows === 1) {
         $_SESSION['error_message'] = "Anda tidak terlibat dalam permainan ini.";
    } else {
        $_SESSION['error_message'] = "Permainan tidak ditemukan.";
    }
    $stmt_check_exist->close();
    header('Location: index.php'); // Redirect ke lobi
    exit();
}

$game_data = $result_game->fetch_assoc();
$stmt_game->close();
$conn->close(); // Tutup koneksi setelah data diambil

// Tentukan apakah user ini adalah kreator atau lawan
$is_creator = ($user_id === $game_data['creator_id']);
$opponent_id_for_js = $is_creator ? $game_data['opponent_id'] : $game_data['creator_id'];
$player1_username = $game_data['creator_username'];
$player2_username = $game_data['opponent_username'] ?? 'Menunggu...';
$player1_pfp = $game_data['creator_pfp'];
$player2_pfp = $game_data['opponent_pfp'];

// --- Handler untuk Status Game Waiting, Finished, Cancelled (Sama seperti sebelumnya) ---
if ($game_data['status'] === 'finished' || $game_data['status'] === 'cancelled') {
    $result_message = $game_data['status'] === 'cancelled' ? "Permainan Dibatalkan." : "";
    if ($game_data['status'] === 'finished') {
        $winner = $game_data['winner_username'] ?? '???';
        $loser_username = ($game_data['winner_id'] === $game_data['creator_id']) ? $game_data['opponent_username'] : $game_data['creator_username'];
        $pot = $game_data['bet_amount'] * 2;
        $result_message = "Permainan Selesai! <strong>" . htmlspecialchars($winner) . "</strong> memenangkan $" . number_format($pot) . ".";
        if ($loser_username) { // Hanya tampilkan jika ada loser (game tidak dibatalkan karena error)
            $result_message .= "<br><small>(".htmlspecialchars($loser_username)." terkena peluru!)</small>";
        }
    }
     echo "<!DOCTYPE html><html><head><title>Hasil Game</title><link rel='stylesheet' href='/assets/css/main.css'><link rel='stylesheet' href='roulette.css'></head><body>";
     echo "<div class='page-container rr-result-container'>";
     echo "<h1>Hasil Permainan #${game_id}</h1>";
     echo "<div class='message info'>$result_message</div>";
     echo "<div class='rr-navigation'><a href='index.php' class='btn btn-primary'>Kembali ke Lobi</a></div>";
     echo "</div></body></html>";
     exit();
}
if ($game_data['status'] === 'waiting') {
     echo "<!DOCTYPE html><html><head><title>Menunggu Lawan</title><link rel='stylesheet' href='/assets/css/main.css'><link rel='stylesheet' href='roulette.css'><meta http-equiv='refresh' content='5'></head><body>"; // Auto refresh setiap 5 detik
     echo "<div class='page-container rr-result-container'>";
     echo "<h1>Permainan #${game_id}</h1>";
     echo "<div class='message info'><i class='fas fa-spinner fa-spin'></i> Menunggu lawan untuk bergabung... Taruhan: $" . number_format($game_data['bet_amount']) . "</div>";
     if ($is_creator) {
         echo "<form action='cancel_game.php' method='POST' onsubmit=\"return confirm('Yakin ingin membatalkan meja ini? Taruhan akan dikembalikan.');\" style='margin-top:1rem;'>";
         echo "<input type='hidden' name='csrf_token' value='" . htmlspecialchars($_SESSION['csrf_token'] ?? '') . "'>";
         echo "<input type='hidden' name='game_id' value='$game_id'>";
         echo "<button type='submit' class='btn btn-danger'>Batalkan Meja</button>";
         echo "</form>";
     }
      echo "<div class='rr-navigation' style='margin-top: 1rem;'><a href='index.php' class='btn btn-secondary'>Kembali ke Lobi</a></div>";
     echo "</div></body></html>";
     exit();
}
// --- Akhir Handler Status ---


// ---- Data untuk Javascript (Sertakan PFP) ----
$js_game_data = [
    'game_id' => $game_data['id'],
    'max_chambers' => $game_data['max_chambers'],
    'my_user_id' => $user_id,
    'creator_id' => $game_data['creator_id'],
    'opponent_id' => $opponent_id_for_js, // Lawan dari user saat ini
    'player1_username' => $player1_username,
    'player2_username' => $player2_username,
    'player1_pfp' => $player1_pfp, // <-- Tambahkan PFP
    'player2_pfp' => $player2_pfp  // <-- Tambahkan PFP
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Russian Roulette Game #<?php echo $game_id; ?> | Rayox</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="roulette.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
    <link rel="manifest" href="/assets/site.webmanifest">
    <style>
        /* Styling untuk PFP di kartu pemain */
        .rr-player-card .profile-pic {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin: 0 auto 10px auto; /* Tengah dan beri jarak bawah */
            display: block;
        }
        .rr-player-card.active-turn .profile-pic {
             border-color: #a5b4fc; /* Warna border saat aktif */
        }
         .rr-player-card h3 {
            margin-bottom: 0.3rem; /* Kurangi jarak bawah nama */
            font-size: 1rem; /* Kecilkan sedikit nama */
        }

        /* Style lain dari sebelumnya */
        .rr-players { display: flex; justify-content: space-around; margin-bottom: 2rem; text-align: center; gap: 1rem; }
        .rr-player-card { padding: 1rem 1.5rem; border: 2px solid #e5e7eb; border-radius: 10px; background-color: #f9fafb; flex: 1; transition: all 0.3s ease; }
        .rr-player-card.active-turn { border-color: #4f46e5; background-color: #eef2ff; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.15); transform: scale(1.03); }
        .rr-player-card .player-status { font-size: 0.9rem; color: #6b7280; font-weight: 500; min-height: 1.2em; display: block; }
        .rr-player-card.active-turn .player-status { color: #4f46e5; font-weight: bold; }

        .rr-chambers { text-align: center; margin-bottom: 2.5rem; background-color: rgba(0,0,0,0.05); padding: 1rem; border-radius: 10px; }
        .rr-chamber { display: inline-block; width: 45px; height: 45px; line-height: 45px; text-align: center; border: 2px solid #9ca3af; border-radius: 50%; margin: 6px; font-weight: bold; background-color: #e5e7eb; font-size: 1.2rem; color: #4b5563; cursor: default; transition: all 0.3s ease; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1); position: relative; }
        .rr-chamber.fired-blank { background-color: #dcfce7; border-color: #86efac; color: #166534; font-size: 0; }
        .rr-chamber.fired-blank::before { content: '✔'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 1.5rem; line-height: 1; }
        .rr-chamber.fired-live { background-color: #fee2e2; border-color: #fca5a5; color: #b91c1c; font-size: 0; }
        .rr-chamber.fired-live::before { content: '💀'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 1.5rem; line-height: 1; }
        .rr-chamber.current { border-color: #4f46e5; background-color: #c7d2fe;}

        .rr-actions { text-align: center; margin-bottom: 1.5rem; }
        .rr-actions button { min-width: 120px; padding: 0.9rem 2rem; }
        .rr-actions button i { margin-right: 8px; }
        .rr-actions button:disabled { opacity: 0.6; cursor: not-allowed; }

        #gameMessage { min-height: 60px; margin-top: 1rem; padding: 1rem; font-size: 1.05rem; font-weight: 500; text-align: center; }
        #gameMessage.loading { color: #6b7280; font-style: italic; }
        #gameMessage .spinner { font-size: 1.2rem; animation: spin 1s linear infinite; display: inline-block; margin-left: 10px; color: #4f46e5; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="page-container rr-game-container">
        <div class="page-header">
            <h1 class="gradient-text">Game #<?php echo $game_id; ?></h1>
            <p class="subtitle">Total Taruhan: <span class="money">$<?php echo number_format($game_data['bet_amount'] * 2); ?></span></p>
        </div>

        <div class="rr-players">
            <div class="rr-player-card" id="playerCard_<?php echo $game_data['creator_id']; ?>">
                <img src="<?php echo htmlspecialchars($player1_pfp); ?>" alt="PFP <?php echo htmlspecialchars($player1_username); ?>" class="profile-pic" id="pfp_<?php echo $game_data['creator_id']; ?>">
                <h3><?php echo htmlspecialchars($player1_username); ?></h3>
                <span class="player-status"></span>
            </div>
            <div style="align-self: center; font-size: 1.5rem; color: #777;">VS</div>
            <div class="rr-player-card" id="playerCard_<?php echo $game_data['opponent_id']; ?>">
                <img src="<?php echo htmlspecialchars($player2_pfp); ?>" alt="PFP <?php echo htmlspecialchars($player2_username); ?>" class="profile-pic" id="pfp_<?php echo $game_data['opponent_id']; ?>">
                <h3><?php echo htmlspecialchars($player2_username); ?></h3>
                <span class="player-status"></span>
            </div>
        </div>

        <div class="rr-chambers" id="chamberDisplay">
             <?php for ($i = 1; $i <= $game_data['max_chambers']; $i++): ?>
                <div class="rr-chamber" data-chamber="<?php echo $i; ?>"><?php echo $i; ?></div>
            <?php endfor; ?>
        </div>

        <div class="rr-actions">
            <button id="shootButton" class="btn btn-danger" disabled><i class="fas fa-crosshairs"></i> Tembak</button>
        </div>

        <div id="gameMessage" class="message info" style="min-height: 60px;">Memuat status permainan...</div>

         <div class="rr-navigation">
            <a href="index.php" class="btn btn-secondary btn-sm"><i class="fas fa-list-ul"></i> Kembali ke Lobi</a>
             <?php if ($game_data['status'] === 'active' && $is_creator): // Tombol batal hanya jika kreator & game aktif ?>
                <form action="cancel_game.php" method="POST" onsubmit="return confirm('Yakin ingin membatalkan permainan ini? Taruhan mungkin tidak kembali.');" style="display:inline; margin-left: 10px;">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                    <input type="hidden" name="game_id" value="<?php echo $game_id; ?>">
                    <button type="submit" class="btn btn-warning btn-sm"><i class="fas fa-times"></i> Batalkan</button>
                </form>
             <?php endif; ?>
        </div>

    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        // Kirim data PHP ke Javascript
        const GAME_DATA = <?php echo json_encode($js_game_data); ?>;
        const DEFAULT_PFP = '<?php echo $default_pfp; ?>'; // Kirim path default PFP
    </script>
    <script src="roulette.js"></script> </body>
</html>