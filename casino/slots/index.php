<?php
session_start();

// --- Pengecekan Login ---
if (!isset($_SESSION['user_id'])) {
    header('Location: /login?error=Anda harus login untuk bermain slot');
    exit();
}

$user_id = $_SESSION['user_id'];

// --- Koneksi Database (Ambil Saldo Awal & Vault Awal) ---
// Sebaiknya buat file koneksi terpisah (misal /config/db_connect.php) dan include di sini
$servername = "localhost";
$db_username = "admin"; // Ganti dengan username DB Anda
$db_password = "admin123"; // Ganti dengan password DB Anda
$dbname = "login_app";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Koneksi database gagal. Coba lagi nanti."); // Pesan error sederhana
}

// Ambil saldo user saat ini
$user_money = 0;
$stmt_user = $conn->prepare("SELECT money FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
if ($stmt_user->execute()) {
    $result_user = $stmt_user->get_result();
    if ($result_user->num_rows === 1) {
        $user_data = $result_user->fetch_assoc();
        $user_money = $user_data['money'];
    } else {
        // Handle jika user tidak ditemukan (meskipun seharusnya tidak terjadi jika session valid)
        session_destroy();
        header('Location: /login?error=User tidak ditemukan');
        exit();
    }
}
$stmt_user->close();

// Ambil nilai vault saat ini
$vault_amount = 0;
$stmt_vault = $conn->prepare("SELECT value FROM game_state WHERE key_name = 'vault_slots'");
if ($stmt_vault->execute()) {
    $result_vault = $stmt_vault->get_result();
    if ($result_vault->num_rows === 1) {
        $vault_data = $result_vault->fetch_assoc();
        $vault_amount = $vault_data['value'];
    }
    // Jika belum ada key 'vault_slots', vault_amount akan tetap 0 (default)
}
$stmt_vault->close();
$conn->close();

// Definisikan bet amounts
$bet_options = [10, 100, 1000, 10000, 100000, 1000000, 10000000];

// Definisikan emoji yang akan digunakan (sesuai paytable)
// Pastikan semua emoji di paytable ada di sini + emoji lain jika perlu
$emojis = ['🦇', '🦆', '🍫', '🔫', '🧑', '🚗', '☣️', '7️⃣']; // Tambahkan emoji lain jika ingin variasi

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
<title>Rayox | Slot</title>
<link rel="stylesheet" href="slots.css">
<link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
<link rel="manifest" href="/assets/site.webmanifest">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
<div class="slot-machine-container">
<h1>Rayox Slot</h1>

<div class="slot-display">
<div class="reel" id="reel1">❓</div>
<div class="reel" id="reel2">❓</div>
<div class="reel" id="reel3">❓</div>
</div>

<div class="controls">
<button id="spinButton" disabled>Pilih Bet</button>
<div class="bet-selector">
<?php foreach ($bet_options as $bet): ?>
<button class="bet-option" data-amount="<?php echo $bet; ?>" <?php echo ($user_money < $bet) ? 'disabled' : ''; ?>>
💰 $<?php echo number_format($bet); ?>
</button>
<?php endforeach; ?>
</div>
<p class="selected-bet-display">Bet Terpilih: <span id="selectedBetAmount">Tidak ada</span></p>
</div>

<div class="info-display">
<div class="paytable-title-display">
Menang: <span id="winTitle">-</span> (<span id="winAmount">$0</span>)
</div>
<div class="vault-display">
<div class="vault-label">🏦 Jackpot:</div>
<div class="vault-bar-container">
<div class="vault-bar" id="vaultBar"></div>
</div>
<div class="vault-amount" id="vaultAmountDisplay">$<?php echo number_format($vault_amount); ?></div>
</div>
<div class="user-balance-display">
Saldo Anda: <span id="userBalanceDisplay">$<?php echo number_format($user_money); ?></span>
</div>
</div>

<div class="message-area" id="messageArea"></div>

<a href="/casino" class="back-link">Kembali ke Casino</a>
<a href="/profile" class="back-link">Kembali ke Profile</a>

</div>

<script>
// Kirim data awal PHP ke JavaScript
const initialUserBalance = <?php echo $user_money; ?>;
const initialVaultAmount = <?php echo $vault_amount; ?>;
const availableEmojis = <?php echo json_encode($emojis); ?>;
const betOptions = <?php echo json_encode($bet_options); ?>;
const userId = <?php echo $user_id; ?>; // User ID jika diperlukan di JS
const csrfToken = <?php echo json_encode($csrf_token); ?>;
</script>
<script src="slots.js"></script>
</body>
</html>
