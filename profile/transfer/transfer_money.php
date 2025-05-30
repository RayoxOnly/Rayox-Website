<?php
// File: profile/transfer/transfer_money.php (Modifikasi path redirect)
session_start();

// --- Pengecekan Login ---
if (!isset($_SESSION['user_id'])) {
    // Redirect ke login jika belum login
    header('Location: /login?error=Anda harus login untuk melakukan transfer');
    exit();
}

// --- Hanya Proses Jika Method POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /profile/transfer'); // <<--- UBAH INI
    exit();
}

// --- Ambil Data Input ---
$sender_id = $_SESSION['user_id'];
$recipient_id = filter_input(INPUT_POST, 'recipient_id', FILTER_VALIDATE_INT);
$amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_INT);

// --- Validasi Input ---
if ($recipient_id === false || $recipient_id <= 0) {
    header('Location: /profile/transfer?transfer_error=ID User Penerima tidak valid.'); // <<--- UBAH INI
    exit();
}
if ($amount === false || $amount <= 0) {
    header('Location: /profile/transfer?transfer_error=Jumlah uang tidak valid.'); // <<--- UBAH INI
    exit();
}
if ($sender_id === $recipient_id) {
    header('Location: /profile/transfer?transfer_error=Tidak bisa mengirim uang ke diri sendiri.'); // <<--- UBAH INI
    exit();
}

// --- Koneksi Database ---
$conn = new mysqli("localhost", "admin", "admin123", "login_app");
if ($conn->connect_error) {
    header('Location: /profile/transfer?transfer_error=Koneksi database gagal.'); // <<--- UBAH INI
    exit();
}

// --- Mulai Transaksi Database (PENTING!) ---
$conn->begin_transaction();

try {
    // 1. Kunci baris pengirim dan ambil saldo (FOR UPDATE)
    $stmt_sender = $conn->prepare("SELECT money FROM users WHERE id = ? FOR UPDATE");
    $stmt_sender->bind_param("i", $sender_id);
    $stmt_sender->execute();
    $result_sender = $stmt_sender->get_result();

    if ($result_sender->num_rows !== 1) {
        throw new Exception("User pengirim tidak ditemukan.");
    }
    $sender_data = $result_sender->fetch_assoc();
    $sender_balance = $sender_data['money'];
    $stmt_sender->close();

    // 2. Cek Saldo Pengirim
    if ($sender_balance < $amount) {
        throw new Exception("Uang Anda tidak cukup untuk melakukan transfer ini.");
    }

    // 3. Kunci baris penerima dan cek keberadaan (FOR UPDATE)
    $stmt_recipient = $conn->prepare("SELECT id FROM users WHERE id = ? FOR UPDATE");
    $stmt_recipient->bind_param("i", $recipient_id);
    $stmt_recipient->execute();
    $result_recipient = $stmt_recipient->get_result();

    if ($result_recipient->num_rows !== 1) {
        throw new Exception("User penerima dengan ID " . htmlspecialchars($recipient_id) . " tidak ditemukan.");
    }
    $stmt_recipient->close();

    // 4. Kurangi Saldo Pengirim
    $stmt_update_sender = $conn->prepare("UPDATE users SET money = money - ? WHERE id = ?");
    $stmt_update_sender->bind_param("ii", $amount, $sender_id);
    if (!$stmt_update_sender->execute()) {
        throw new Exception("Gagal mengurangi saldo pengirim.");
    }
    $stmt_update_sender->close();

    // 5. Tambah Saldo Penerima
    $stmt_update_recipient = $conn->prepare("UPDATE users SET money = money + ? WHERE id = ?");
    $stmt_update_recipient->bind_param("ii", $amount, $recipient_id);
    if (!$stmt_update_recipient->execute()) {
        throw new Exception("Gagal menambah saldo penerima.");
    }
    $stmt_update_recipient->close();

    // 6. Jika semua berhasil, commit transaksi
    $conn->commit();
    header('Location: /profile/transfer?transfer_status=Berhasil mengirim $' . number_format($amount) . ' ke user ID ' . $recipient_id); // <<--- UBAH INI

} catch (Exception $e) {
    // Jika ada error, rollback semua perubahan
    $conn->rollback();
    header('Location: /profile/transfer?transfer_error=' . urlencode("Error: " . $e->getMessage())); // <<--- UBAH INI (tambah prefix "Error:")
}

// Tutup koneksi
$conn->close();
exit();
?>
