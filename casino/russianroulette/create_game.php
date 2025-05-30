<?php
session_start();
require_once 'db_config.php'; // Include konfigurasi DB

// Cek Login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Anda harus login untuk membuat game.";
    header('Location: index.php');
    exit();
}

// Cek Method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$bet_amount = filter_input(INPUT_POST, 'bet_amount', FILTER_VALIDATE_INT);
$max_chambers = filter_input(INPUT_POST, 'max_chambers', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 5, 'max_range' => 8] // Validasi jumlah chamber
]);

// Validasi Input
if ($bet_amount === false || $bet_amount < 100) { // Minimal bet 100
    $_SESSION['error_message'] = "Jumlah taruhan tidak valid (minimal $100).";
    header('Location: index.php');
    exit();
}
if ($max_chambers === false) {
    $_SESSION['error_message'] = "Jumlah chamber tidak valid (5-8).";
    header('Location: index.php');
    exit();
}

// Mulai Transaksi
$conn->begin_transaction();

try {
    // 1. Kunci dan Ambil Saldo User (FOR UPDATE)
    $stmt_user = $conn->prepare("SELECT money FROM users WHERE id = ? FOR UPDATE");
    if (!$stmt_user) throw new Exception("Gagal menyiapkan query user: " . $conn->error);
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    if ($result_user->num_rows !== 1) {
        throw new Exception("User tidak ditemukan.");
    }
    $user_data = $result_user->fetch_assoc();
    $current_balance = $user_data['money'];
    $stmt_user->close();

    // 2. Cek Saldo Cukup
    if ($current_balance < $bet_amount) {
        throw new Exception("Saldo Anda tidak cukup ($" . number_format($current_balance) . ") untuk membuat meja dengan taruhan $" . number_format($bet_amount) . ".");
    }

     // 3. Cek apakah user sudah punya game aktif atau waiting
     $stmt_check_game = $conn->prepare("SELECT id FROM roulette_games WHERE (creator_id = ? OR opponent_id = ?) AND status IN ('waiting', 'active') LIMIT 1");
     if (!$stmt_check_game) throw new Exception("Gagal menyiapkan cek game: " . $conn->error);
     $stmt_check_game->bind_param("ii", $user_id, $user_id);
     $stmt_check_game->execute();
     $result_check_game = $stmt_check_game->get_result();
     if ($result_check_game->num_rows > 0) {
         $existing_game = $result_check_game->fetch_assoc();
         if ($existing_game) { // Tambah cek $existing_game
             throw new Exception("Anda sudah memiliki permainan yang sedang menunggu atau aktif (ID: ".$existing_game['id']."). Selesaikan atau batalkan dulu.");
         }
     }
     $stmt_check_game->close();


    // 4. Kurangi Saldo User
    $stmt_update_user = $conn->prepare("UPDATE users SET money = money - ? WHERE id = ?");
    if (!$stmt_update_user) throw new Exception("Gagal menyiapkan update saldo: " . $conn->error);
    $stmt_update_user->bind_param("ii", $bet_amount, $user_id);
    if (!$stmt_update_user->execute()) {
        throw new Exception("Gagal mengurangi saldo.");
    }
    $stmt_update_user->close();

    // 5. Acak Live Chamber
    $live_chamber = rand(1, $max_chambers);

    // 6. Buat Game Baru di DB
    $stmt_create_game = $conn->prepare("INSERT INTO roulette_games (creator_id, bet_amount, max_chambers, live_chamber, status) VALUES (?, ?, ?, ?, 'waiting')");
    if (!$stmt_create_game) throw new Exception("Gagal menyiapkan insert game: " . $conn->error);
    $stmt_create_game->bind_param("iiii", $user_id, $bet_amount, $max_chambers, $live_chamber);
    if (!$stmt_create_game->execute()) {
        throw new Exception("Gagal membuat permainan di database.");
    }
    $new_game_id = $conn->insert_id; // Ambil ID game baru
    $stmt_create_game->close();

    // 7. Log Aksi
    log_roulette_action($new_game_id, $user_id, "Created game with bet $" . number_format($bet_amount) . " and $max_chambers chambers. Live chamber is $live_chamber.");

    // 8. Commit Transaksi
    $conn->commit();

    // --- PERUBAHAN DI SINI ---
    // Redirect langsung ke halaman game, bukan ke lobi
    header('Location: game.php?id=' . $new_game_id);

} catch (Exception $e) {
    $conn->rollback(); // Batalkan semua perubahan jika ada error
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    error_log("Error creating roulette game for user $user_id: " . $e->getMessage()); // Log error detail
    header('Location: index.php'); // Kembali ke lobi dengan pesan error
}

$conn->close();
exit();
?>