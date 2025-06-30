<?php
// File: profile/transfer/index.php (File Baru)
session_start();

// --- Pengecekan Login ---
if (!isset($_SESSION['user_id'])) {
    header('Location: /login?error=Anda harus login untuk mengakses halaman ini');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['user']; // Ambil username dari session

// Ambil saldo terbaru (opsional, tapi bagus untuk ditampilkan)
$conn = new mysqli("localhost", "admin", "admin123", "login_app");
$current_money = 'N/A'; // Default jika gagal ambil data
if (!$conn->connect_error) {
    $stmt_money = $conn->prepare("SELECT money FROM users WHERE id = ?");
    $stmt_money->bind_param("i", $user_id);
    if ($stmt_money->execute()) {
        $result_money = $stmt_money->get_result();
        if ($result_money->num_rows === 1) {
            $user_money_data = $result_money->fetch_assoc();
            $current_money = number_format($user_money_data['money']);
        }
    }
    $stmt_money->close();
    $conn->close(); // Tutup koneksi setelah selesai
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Transfer Uang | <?php echo htmlspecialchars($username); ?> | Rayox</title>
<link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/LOGO/favicon32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/LOGO/favicon16x16.png">
<link rel="manifest" href="/assets/site.webmanifest">
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f3f4f6;
    color: #4b5563;
    margin: 0;
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: flex-start; /* Align ke atas */
    min-height: 100vh;
}
.container {
    background: #fff;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    max-width: 500px;
    width: 90%;
    margin-top: 30px; /* Jarak dari atas */
}
h1 {
    font-size: 1.8rem;
    color: #4f46e5;
    margin-bottom: 1.5rem;
    text-align: center;
    border-bottom: 1px solid #eee;
    padding-bottom: 1rem;
}
.balance-info {
    text-align: center;
    margin-bottom: 1.5rem;
    font-size: 1.1rem;
}
.money {
    font-weight: bold;
    color: #16a34a;
}
.form-group {
    margin-bottom: 1rem;
}
.form-group label {
    display: block;
    margin-bottom: 0.3rem;
    font-weight: 600;
    color: #555;
    font-size: 0.9rem;
}
.form-group input[type="number"] {
    width: 100%;
    padding: 0.7rem;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 1rem;
}
.transfer-button {
    display: block; /* Agar bisa diatur margin auto */
    width: 100%;
    margin-top: 1.5rem;
    padding: 0.8rem 1.5rem;
    background-color: #4f46e5;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 500;
    transition: background-color 0.2s;
}
.transfer-button:hover {
    background-color: #4338ca;
}
.back-link {
    display: block;
    text-align: center;
    margin-top: 1.5rem;
    color: #4f46e5;
    text-decoration: none;
    font-size: 0.9rem;
}
.back-link:hover {
    text-decoration: underline;
}
/* Style untuk pesan status/error */
.message { padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; font-size: 0.9rem; }
.success { background-color: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
.error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
</style>
</head>
<body>
<div class="container">
<h1>Kirim Uang</h1>

<?php if (isset($_GET['transfer_status'])): ?>
<div class="message success"><?= htmlspecialchars($_GET['transfer_status']) ?></div>
<?php endif; ?>
<?php if (isset($_GET['transfer_error'])): ?>
<div class="message error"><?= htmlspecialchars($_GET['transfer_error']) ?></div>
<?php endif; ?>

<div class="balance-info">
Uang Anda Saat Ini: <span class="money">$<?php echo $current_money; ?></span>
</div>

<form action="/profile/transfer/transfer_money.php" method="POST">
<div class="form-group">
<label for="recipient_id">ID User Penerima:</label>
<input type="number" id="recipient_id" name="recipient_id" placeholder="Masukkan ID user tujuan" required min="1">
</div>
<div class="form-group">
<label for="amount">Jumlah Uang:</label>
<input type="number" id="amount" name="amount" placeholder="Jumlah yang akan dikirim" required min="1">
</div>
<button type="submit" class="transfer-button">Kirim Uang</button>
</form>

<a href="/profile" class="back-link">&larr; Kembali ke Profile</a>
</div>
</body>
</html>
