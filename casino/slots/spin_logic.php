<?php
// casino/slots/spin_logic.php (MODIFIED VERSION)
session_start();
header('Content-Type: application/json');

// --- Konfigurasi & Variabel ---
$response = ['success' => false, 'message' => 'Terjadi kesalahan.'];
$servername = "localhost";
$db_username = "admin"; // GANTI SESUAI DATABASE ANDA
$db_password = "admin123"; // GANTI SESUAI DATABASE ANDA
$dbname = "login_app";

// Paytable (tetap sama, digunakan untuk detail kemenangan & visual)
$paytable = [
    // Format: 'emoji1emoji2emoji3' => ['title' => 'Nama', 'rate' => multiplier]
    '🦇🦆🍫' => ['title' => 'Pinata',        'rate' => 2],
'🦆🦆🔫' => ['title' => 'Duck Hunting',  'rate' => 3],
'🦆🦆🦆' => ['title' => 'Flock O\'Ducks', 'rate' => 3],
'🦆🦆🧑' => ['title' => 'Serious Duck Hunting', 'rate' => 4],
'🍫🍫🍫' => ['title' => 'Sweet Tooth',   'rate' => 4],
'🦆🚗🦆' => ['title' => 'Road Kill',     'rate' => 6],
'🍫☣️🍫' => ['title' => 'Melt Down',     'rate' => 7],
'☣️🍫☣️' => ['title' => 'Melt Down',     'rate' => 7], // Kombinasi kedua (tetap perlu untuk visual)
'🦇🚗🦇' => ['title' => 'Vandalism',     'rate' => 8],
'🚗🦇🚗' => ['title' => 'Vandalism',     'rate' => 8], // Kombinasi kedua (tetap perlu untuk visual)
'🦇🦇🦇' => ['title' => 'Home Run',      'rate' => 8],
'🧑🧑🔫' => ['title' => 'Gun Fight',     'rate' => 10],
'🧑🔫🔫' => ['title' => 'Gun Fight',     'rate' => 10], // Kombinasi kedua (tetap perlu untuk visual)
'🔫🔫🚗' => ['title' => 'Drive By',      'rate' => 20],
'🧑🧑🚗' => ['title' => 'Drive By',      'rate' => 20], // Kombinasi kedua (tetap perlu untuk visual)
'🔫🔫🔫' => ['title' => 'Firing Range',  'rate' => 40],
'🧑🧑🧑' => ['title' => 'Overkill',      'rate' => 200],
'🚗🚗🚗' => ['title' => 'Pile Up',       'rate' => 1000],
'☣️☣️☣️' => ['title' => 'Radioactive',   'rate' => 2000],
'7️⃣7️⃣7️⃣' => ['title' => 'Jackpot',       'rate' => 'jackpot'], // Rate khusus
];

// --- **BARU: Bobot untuk Setiap Hasil Paytable + Tidak Menang** ---
// **PENTING:** SESUAIKAN ANGKA BOBOT INI! Total bobot mempengaruhi probabilitas.
// Angka ini hanya contoh kasar. Anda perlu menyeimbangkannya.
// Pastikan semua KEY dari $paytable ada di sini (cukup salah satu jika ada duplikat visual)
$outcome_weights = [
    'NO_WIN' => 5000, // Bobot untuk tidak menang (PALING TINGGI)

    // Kemenangan Kecil (Bobot Sedang-Tinggi)
    '🦇🦆🍫' => 60,  // Pinata
'🦆🦆🔫' => 50,  // Duck Hunting (salah satu visual)
'🦆🦆🦆' => 45,  // Flock O'Ducks
'🦆🦆🧑' => 40,  // Serious Duck Hunting
'🍫🍫🍫' => 35,  // Sweet Tooth

// Kemenangan Sedang (Bobot Rendah-Sedang)
'🦆🚗🦆' => 25,  // Road Kill
'🍫☣️🍫' => 18,  // Melt Down (salah satu visual)
'🦇🚗🦇' => 15,  // Vandalism (salah satu visual)
'🦇🦇🦇' => 12,  // Home Run
'🧑🧑🔫' => 11,  // Gun Fight (salah satu visual)

// Kemenangan Besar (Bobot Rendah)
'🔫🔫🚗' => 8,   // Drive By (salah satu visual)
'🔫🔫🔫' => 5,   // Firing Range

// Kemenangan Sangat Besar (Bobot Sangat Rendah)
'🧑🧑🧑' => 3,   // Overkill
'🚗🚗🚗' => 2,   // Pile Up
'☣️☣️☣️' => 1,   // Radioactive

// Jackpot Trigger (Bobot Paling Rendah)
'7️⃣7️⃣7️⃣' => 1,   // Jackpot visual
];

// --- Fungsi Helper untuk Weighted Random (Tetap Sama) ---
function getRandomWeightedElement(array $weightedValues) {
    $totalWeight = array_sum($weightedValues);
    if ($totalWeight <= 0) {
        return array_rand($weightedValues); // Fallback
    }
    $rand = mt_rand(1, (int) $totalWeight);
    $cumulativeWeight = 0;
    foreach ($weightedValues as $key => $value) {
        $cumulativeWeight += $value;
        if ($rand <= $cumulativeWeight) {
            return $key;
        }
    }
    // Fallback (seharusnya tidak mudah tercapai)
    return array_key_first($weightedValues);
}

// --- Fungsi Helper untuk Mengubah Key String ke Array Emoji (BARU) ---
function keyToEmojiArray(string $key): array {
    if (empty($key) || $key === 'NO_WIN') {
        // Jika tidak menang atau key kosong, kembalikan visual acak non-menang
        // (Anda bisa buat lebih canggih jika perlu)
        $basicEmojis = ['🦇', '🦆', '🍫', '🔫', '🧑', '🚗', '☣️']; // Tanpa 7
        return [
            $basicEmojis[array_rand($basicEmojis)],
            $basicEmojis[array_rand($basicEmojis)],
            $basicEmojis[array_rand($basicEmojis)]
        ];
        // Pastikan kombinasi acak ini TIDAK ADA di $paytable,
        // atau buat visual kalah yang pasti seperti ['❓','❓','❓']
        // return ['❓','❓','❓'];
    }
    // Pecah string key menjadi array karakter/emoji
    // Perlu penanganan khusus jika emoji multi-byte
    return preg_split('//u', $key, -1, PREG_SPLIT_NO_EMPTY);
}


// --- Pengecekan Awal (Tetap Sama) ---
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
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    $response['message'] = 'CSRF token tidak valid.';
    echo json_encode($response);
    exit();
}
if (isset($_SESSION['slots_last_spin']) && (time() - $_SESSION['slots_last_spin']) < 2) {
    $response['message'] = 'Tunggu sebentar sebelum spin berikutnya!';
    echo json_encode($response);
    exit();
}
$_SESSION['slots_last_spin'] = time();

$user_id = $_SESSION['user_id'];
$bet_amount = filter_input(INPUT_POST, 'bet_amount', FILTER_VALIDATE_INT);

if ($bet_amount === false || $bet_amount <= 0) {
    $response['message'] = 'Jumlah bet tidak valid.';
    echo json_encode($response);
    exit();
}

// --- Koneksi Database & Transaksi (Tetap Sama Awalnya) ---
$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    $response['message'] = 'Koneksi database gagal.';
    error_log("DB Connection Error: " . $conn->connect_error);
    echo json_encode($response);
    exit();
}

$conn->begin_transaction();

try {
    // 1. Kunci dan Ambil Data User & Vault (FOR UPDATE) - Tetap Sama
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

    $current_vault = 0;
    if ($result_vault->num_rows === 1) {
        $vault_data = $result_vault->fetch_assoc();
        $current_vault = $vault_data['value'];
    } else {
        // Inisialisasi vault jika belum ada
        $stmt_init_vault = $conn->prepare("INSERT INTO game_state (key_name, value) VALUES ('vault_slots', 0)");
        if (!$stmt_init_vault->execute()) {
            throw new Exception("Gagal inisialisasi vault slots.");
        }
        $stmt_init_vault->close();
        // Ambil lagi setelah insert (opsional, bisa langsung pakai $current_vault=0)
        $stmt_vault->execute();
        $result_vault = $stmt_vault->get_result();
        if ($result_vault->num_rows === 1) {
            $vault_data = $result_vault->fetch_assoc();
            $current_vault = $vault_data['value'];
        }
    }

    $stmt_user->close();
    $stmt_vault->close();

    // 2. Validasi Saldo - Tetap Sama
    if ($current_balance < $bet_amount) {
        throw new Exception("Uang Anda tidak cukup untuk bet ini.");
    }

    // 3. Kurangi Saldo User, Tambah ke Vault - Tetap Sama
    $new_balance = $current_balance - $bet_amount;
    $new_vault = $current_vault + $bet_amount;

    $stmt_update_user = $conn->prepare("UPDATE users SET money = ? WHERE id = ?");
    $stmt_update_user->bind_param("ii", $new_balance, $user_id);
    if (!$stmt_update_user->execute()) throw new Exception("Gagal update saldo user.");
    $stmt_update_user->close();

    // Update vault dengan nilai baru (setelah ditambah bet)
    $stmt_update_vault = $conn->prepare("UPDATE game_state SET value = ? WHERE key_name = 'vault_slots'");
    $stmt_update_vault->bind_param("i", $new_vault);
    if (!$stmt_update_vault->execute()) throw new Exception("Gagal update vault slots setelah bet.");
    $stmt_update_vault->close();

    // --- RTP-based Dynamic Outcome Selection ---
    $RTP = 0.9; // 90% RTP
    $possible_outcomes = [];
    $total_probability = 0;
    foreach ($paytable as $key => $data) {
        if ($key === '7️⃣7️⃣7️⃣') continue; // Jackpot handled separately
        $rate = $data['rate'];
        $payout = is_numeric($rate) ? $bet_amount * $rate : 0;
        if ($payout > $new_vault) continue; // Skip if vault can't pay
        $prob = 1 / ($rate * 20); // Lower payout = higher chance, tune denominator for volatility
        $possible_outcomes[$key] = $prob;
        $total_probability += $prob;
    }
    // Add NO_WIN outcome to fill up to RTP
    $expected_payout = 0;
    foreach ($possible_outcomes as $key => $prob) {
        $rate = $paytable[$key]['rate'];
        $payout = is_numeric($rate) ? $bet_amount * $rate : 0;
        $expected_payout += $prob * $payout;
    }
    $prob_no_win = max(0, 1 - ($expected_payout / ($RTP * $bet_amount)));
    $possible_outcomes['NO_WIN'] = $prob_no_win;
    $total_probability += $prob_no_win;
    // Normalize probabilities
    foreach ($possible_outcomes as $key => &$prob) {
        $prob = $prob / $total_probability;
    }
    unset($prob);
    // --- Jackpot logic (rare, vault-based) ---
    $jackpot_chance = 0.00005; // 0.005% per spin (1 in 20,000)
    if ($new_vault > 0 && mt_rand() / mt_getrandmax() < $jackpot_chance) {
        $selected_outcome_key = '7️⃣7️⃣7️⃣';
    } else {
        // Weighted random selection
        $rand = mt_rand() / mt_getrandmax();
        $cumulative = 0;
        foreach ($possible_outcomes as $key => $prob) {
            $cumulative += $prob;
            if ($rand <= $cumulative) {
                $selected_outcome_key = $key;
                break;
            }
        }
        if (!isset($selected_outcome_key)) $selected_outcome_key = 'NO_WIN';
    }
    $reel_results = keyToEmojiArray($selected_outcome_key);

    // --- 5. Cek Hasil & Hitung Kemenangan (Logika Disederhanakan) ---
    $win_amount = 0;
    $win_title = null;
    $is_jackpot = false;

    if ($selected_outcome_key === 'NO_WIN') {
        // Tidak menang, tidak perlu lakukan apa-apa lagi
        $win_amount = 0;
        $win_title = null;
        // $reel_results sudah diatur oleh keyToEmojiArray

    } elseif ($selected_outcome_key === '7️⃣7️⃣7️⃣') {
        // Hasil adalah visual Jackpot, sekarang cek logic chance sebenarnya
        $base_jackpot_chance = 1 / 15000; // Sesuaikan base chance jika perlu
        $odds_multiplier = 1;
        // Logika odds scaling berdasarkan bet amount (tetap sama)
        if ($bet_amount >= 100) $odds_multiplier = 2;
        if ($bet_amount >= 1000) $odds_multiplier = 4;
        if ($bet_amount >= 10000) $odds_multiplier = 8;
        if ($bet_amount >= 100000) $odds_multiplier = 16;
        if ($bet_amount >= 1000000) $odds_multiplier = 32;
        if ($bet_amount >= 10000000) $odds_multiplier = 64;

        $final_jackpot_chance = $base_jackpot_chance * $odds_multiplier;
        $random_roll = mt_rand() / mt_getrandmax(); // Roll dadu digital

        if ($random_roll < $final_jackpot_chance) {
            // *** JACKPOT! ***
            $is_jackpot = true;
            $win_data = $paytable[$selected_outcome_key];
            $win_title = $win_data['title'];
            $win_amount = $new_vault; // Menangkan seluruh vault
            $new_vault = 0; // Vault dikosongkan setelah jackpot
            // $reel_results sudah diatur ke ['7️⃣', '7️⃣', '7️⃣']
        } else {
            // Mendapat visual 777 tapi gagal roll jackpot chance
            $win_amount = 0; // Tidak menang (atau beri hadiah hiburan kecil)
            $win_title = null; // atau 'Nyaris Jackpot!'
            $is_jackpot = false;
            // $reel_results sudah diatur ke ['7️⃣', '7️⃣', '7️⃣']
        }

    } elseif (isset($paytable[$selected_outcome_key])) {
        // Menang paytable biasa (selain Jackpot)
        $win_data = $paytable[$selected_outcome_key];
        $win_title = $win_data['title'];
        $win_rate = $win_data['rate'];
        $win_amount = $bet_amount * $win_rate;
        $is_jackpot = false; // Bukan jackpot
        // $reel_results sudah diatur ke visual yang sesuai

        // Cek apakah vault cukup untuk membayar
        if ($new_vault < $win_amount) {
            // Jika vault tidak cukup, bayar seadanya dari vault
            $win_amount = $new_vault;
            $response['partial_payout'] = true; // Tandai jika pembayaran tidak penuh
        }
        $new_vault -= $win_amount; // Kurangi vault sejumlah kemenangan

    } else {
        // Fallback jika $selected_outcome_key tidak dikenal (seharusnya tidak terjadi)
        error_log("Unknown selected outcome key: $selected_outcome_key");
        $win_amount = 0;
        $win_title = null;
        $reel_results = ['🚫','🚫','🚫']; // Visual error
    }


    // --- 6. Jika ada kemenangan, update saldo user & vault lagi (Logika tetap sama) ---
    if ($win_amount > 0) {
        $new_balance += $win_amount; // Tambahkan kemenangan ke saldo user

        // Update User Balance
        $stmt_win_user = $conn->prepare("UPDATE users SET money = ? WHERE id = ?");
        $stmt_win_user->bind_param("ii", $new_balance, $user_id);
        if (!$stmt_win_user->execute()) throw new Exception("Gagal menambah kemenangan ke saldo user.");
        $stmt_win_user->close();

        // Update Vault (setelah dikurangi kemenangan atau direset karena jackpot)
        $stmt_win_vault = $conn->prepare("UPDATE game_state SET value = ? WHERE key_name = 'vault_slots'");
        $stmt_win_vault->bind_param("i", $new_vault);
        if (!$stmt_win_vault->execute()) throw new Exception("Gagal update vault setelah pembayaran/jackpot.");
        $stmt_win_vault->close();
    }

    // --- 7. Commit Transaksi (Tetap Sama) ---
    $conn->commit();

    // --- 8. Siapkan Response Sukses (Tetap Sama) ---
    $response = [
        'success'     => true,
        'reels'       => $reel_results, // Kirim visual reel yang sudah ditentukan
        'win_amount'  => $win_amount,
        'win_title'   => $win_title,
        'is_jackpot'  => $is_jackpot,
        'new_balance' => $new_balance,
        'new_vault'   => $new_vault, // Kirim nilai vault terbaru
        'message'     => $is_jackpot ? 'JACKPOT!' : ($win_amount > 0 ? 'Anda Menang!' : 'Coba Lagi!')
    ];
    if (isset($response['partial_payout']) && $response['partial_payout']) {
        $response['message'] .= " (Vault tidak mencukupi untuk pembayaran penuh)";
    }


} catch (Exception $e) {
    // Rollback jika ada error (Tetap Sama)
    $conn->rollback();
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log("Slot Spin Error (User ID: $user_id): " . $e->getMessage());
    // Kembalikan state visual ke sebelum transaksi gagal jika perlu
    // Tapi biasanya lebih aman biarkan JS fetch state terbaru via get_state.php
    // $response['new_balance'] = $current_balance; // Saldo sebelum bet
    // $response['new_vault'] = $current_vault;    // Vault sebelum bet
    $response['reels'] = ['🔥','🔥','🔥']; // Visual error
}

$conn->close();
echo json_encode($response);
exit();

?>
