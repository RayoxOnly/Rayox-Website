<?php
// casino/slots/spin_logic.php
session_start();
header('Content-Type: application/json'); // Set header response ke JSON

// --- Konfigurasi & Variabel ---
$response = ['success' => false, 'message' => 'Terjadi kesalahan.'];
$servername = "localhost";
$db_username = "admin"; // Ganti
$db_password = "admin123"; // Ganti
$dbname = "login_app";

// Daftar Emoji & Paytable (HARUS SAMA DENGAN JS & DEFINISI AWAL)
$emojis = ['🦇', '🦆', '🍫', '🔫', '🧑', '🚗', '☣️', '7️⃣']; // Tambahkan 'filler' jika perlu lebih banyak simbol
$paytable = [
    // Format: [emoji1, emoji2, emoji3] => ['title' => 'Nama', 'rate' => multiplier]
    // Urutan dalam kombinasi PENTING jika tidak simetris
    '🦇🦆🍫' => ['title' => 'Pinata',        'rate' => 2],
'🦆🦆🔫' => ['title' => 'Duck Hunting',  'rate' => 3],
'🦆🦆🦆' => ['title' => 'Flock O\'Ducks', 'rate' => 3],
'🦆🦆🧑' => ['title' => 'Serious Duck Hunting', 'rate' => 4],
'🍫🍫🍫' => ['title' => 'Sweet Tooth',   'rate' => 4],
'🦆🚗🦆' => ['title' => 'Road Kill',     'rate' => 6],
'🍫☣️🍫' => ['title' => 'Melt Down',     'rate' => 7],
'☣️🍫☣️' => ['title' => 'Melt Down',     'rate' => 7], // Kombinasi kedua Melt Down
'🦇🚗🦇' => ['title' => 'Vandalism',     'rate' => 8],
'🚗🦇🚗' => ['title' => 'Vandalism',     'rate' => 8], // Kombinasi kedua Vandalism
'🦇🦇🦇' => ['title' => 'Home Run',      'rate' => 8],
'🧑🧑🔫' => ['title' => 'Gun Fight',     'rate' => 10],
'🧑🔫🔫' => ['title' => 'Gun Fight',     'rate' => 10], // Kombinasi kedua Gun Fight
'🔫🔫🚗' => ['title' => 'Drive By',      'rate' => 20],
'🧑🧑🚗' => ['title' => 'Drive By',      'rate' => 20], // Kombinasi kedua Drive By
'🔫🔫🔫' => ['title' => 'Firing Range',  'rate' => 40],
'🧑🧑🧑' => ['title' => 'Overkill',      'rate' => 200],
'🚗🚗🚗' => ['title' => 'Pile Up',       'rate' => 1000],
'☣️☣️☣️' => ['title' => 'Radioactive',   'rate' => 2000],
'7️⃣7️⃣7️⃣' => ['title' => 'Jackpot',       'rate' => 'jackpot'], // Rate khusus
];

// --- Pengecekan Awal ---
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Sesi tidak valid. Silakan login kembali.';
    echo json_encode($response);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['bet_amount'])) {
    $response['message'] = 'Request tidak valid.';
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];
$bet_amount = filter_input(INPUT_POST, 'bet_amount', FILTER_VALIDATE_INT);

if ($bet_amount === false || $bet_amount <= 0) {
    $response['message'] = 'Jumlah bet tidak valid.';
    echo json_encode($response);
    exit();
}

// --- Koneksi Database & Transaksi ---
$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    $response['message'] = 'Koneksi database gagal.';
    // Sebaiknya log error detail di server, jangan kirim ke client
    error_log("DB Connection Error: " . $conn->connect_error);
    echo json_encode($response);
    exit();
}

$conn->begin_transaction(); // Mulai transaksi

try {
    // 1. Kunci dan Ambil Data User & Vault (FOR UPDATE mencegah race condition)
    $stmt_user = $conn->prepare("SELECT money FROM users WHERE id = ? FOR UPDATE");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    $stmt_vault = $conn->prepare("SELECT value FROM game_state WHERE key_name = 'vault_slots' FOR UPDATE");
    $stmt_vault->execute();
    $result_vault = $stmt_vault->get_result();

    if ($result_user->num_rows !== 1) {
        throw new Exception("User tidak ditemukan.");
    }
    $user_data = $result_user->fetch_assoc();
    $current_balance = $user_data['money'];

    $current_vault = 0; // Default jika belum ada
    if ($result_vault->num_rows === 1) {
        $vault_data = $result_vault->fetch_assoc();
        $current_vault = $vault_data['value'];
    }

    $stmt_user->close();
    $stmt_vault->close();

    // 2. Validasi Saldo
    if ($current_balance < $bet_amount) {
        throw new Exception("Uang Anda tidak cukup untuk bet ini.");
    }

    // 3. Kurangi Saldo User, Tambah ke Vault
    $new_balance = $current_balance - $bet_amount;
    $new_vault = $current_vault + $bet_amount;

    $stmt_update_user = $conn->prepare("UPDATE users SET money = ? WHERE id = ?");
    $stmt_update_user->bind_param("ii", $new_balance, $user_id);
    if (!$stmt_update_user->execute()) throw new Exception("Gagal update saldo user.");
    $stmt_update_user->close();

    // Update atau Insert Vault
    $stmt_update_vault = $conn->prepare("INSERT INTO game_state (key_name, value) VALUES ('vault_slots', ?) ON DUPLICATE KEY UPDATE value = ?");
    $stmt_update_vault->bind_param("ii", $new_vault, $new_vault);
    if (!$stmt_update_vault->execute()) throw new Exception("Gagal update vault slots.");
    $stmt_update_vault->close();


    // 4. Generate Hasil Slot (3 reel)
    $reel_results = [];
    for ($i = 0; $i < 3; $i++) {
        $randomIndex = array_rand($emojis);
        $reel_results[] = $emojis[$randomIndex];
    }
    $result_key = implode('', $reel_results); // Buat key string dari hasil

    // 5. Cek Paytable & Hitung Kemenangan
    $win_amount = 0;
    $win_title = null;
    $is_jackpot = false;

    // Cek Jackpot 777 dengan Odds Scaling
    if ($result_key === '7️⃣7️⃣7️⃣') {
        // Tentukan base chance (misal 1 banding 10000)
        $base_jackpot_chance = 1 / 10000; // Peluang dasar

        // Hitung multiplier berdasarkan bet
        $odds_multiplier = 1;
        if ($bet_amount >= 100) $odds_multiplier = 2;
        if ($bet_amount >= 1000) $odds_multiplier = 4;
        if ($bet_amount >= 10000) $odds_multiplier = 8;
        if ($bet_amount >= 100000) $odds_multiplier = 16;
        if ($bet_amount >= 1000000) $odds_multiplier = 32;
        if ($bet_amount >= 10000000) $odds_multiplier = 64;

        $final_jackpot_chance = $base_jackpot_chance * $odds_multiplier;

        // Simulasi lemparan "dadu" untuk jackpot
        $random_roll = mt_rand() / mt_getrandmax(); // Angka acak antara 0 dan 1
        if ($random_roll < $final_jackpot_chance) {
            $is_jackpot = true;
            $win_title = $paytable[$result_key]['title'];
            $win_amount = $new_vault; // Menangkan seluruh vault
            $new_vault = 0; // Vault dikosongkan
        } else {
            // 777 muncul tapi tidak menang jackpot (karena chance roll gagal)
            // Bisa beri hadiah kecil atau anggap tidak menang
            $win_amount = 0; // Atau beri hadiah hiburan, misal $win_amount = $bet_amount * 5;
            $win_title = null; // atau 'Nyaris Jackpot!'
        }

    } else if (isset($paytable[$result_key])) {
        // Menang paytable biasa
        $win_data = $paytable[$result_key];
        $win_title = $win_data['title'];
        $win_amount = $bet_amount * $win_data['rate'];

        // Pastikan vault cukup untuk membayar
        if ($new_vault < $win_amount) {
            // Jika vault tidak cukup, bayar seadanya dari vault
            $win_amount = $new_vault;
            // Bisa tambahkan pesan khusus di response bahwa vault tidak cukup
            $response['partial_payout'] = true;
        }
        $new_vault -= $win_amount; // Kurangi vault
    }

    // 6. Jika ada kemenangan (termasuk jackpot), update saldo user & vault lagi
    if ($win_amount > 0) {
        $new_balance += $win_amount; // Tambah kemenangan ke saldo

        // Update User Balance
        $stmt_win_user = $conn->prepare("UPDATE users SET money = ? WHERE id = ?");
        $stmt_win_user->bind_param("ii", $new_balance, $user_id);
        if (!$stmt_win_user->execute()) throw new Exception("Gagal menambah kemenangan ke saldo user.");
        $stmt_win_user->close();

        // Update Vault (setelah dikurangi atau direset karena jackpot)
        $stmt_win_vault = $conn->prepare("UPDATE game_state SET value = ? WHERE key_name = 'vault_slots'");
        $stmt_win_vault->bind_param("i", $new_vault);
        if (!$stmt_win_vault->execute()) throw new Exception("Gagal update vault setelah pembayaran.");
        $stmt_win_vault->close();
    }

    // 7. Commit Transaksi
    $conn->commit();

    // 8. Siapkan Response Sukses
    $response = [
        'success'     => true,
        'reels'       => $reel_results,
        'win_amount'  => $win_amount,
        'win_title'   => $win_title,
        'is_jackpot'  => $is_jackpot,
        'new_balance' => $new_balance,
        'new_vault'   => $new_vault,
        'message'     => $is_jackpot ? 'JACKPOT!' : ($win_amount > 0 ? 'Anda Menang!' : 'Coba Lagi!')
    ];

} catch (Exception $e) {
    // Jika terjadi error, rollback semua perubahan DB
    $conn->rollback();
    $response['success'] = false;
    $response['message'] = $e->getMessage(); // Kirim pesan error
    // Log error detail di server
    error_log("Slot Spin Error (User ID: $user_id): " . $e->getMessage());

    // Ambil state terakhir (sebelum rollback) untuk konsistensi display jika perlu
    // Tapi lebih aman biarkan JS fetch state terbaru via get_state.php
    // $response['new_balance'] = $current_balance; // Kembalikan ke saldo sebelum bet
    // $response['new_vault'] = $current_vault;
}

$conn->close();
echo json_encode($response);
exit();

?>
