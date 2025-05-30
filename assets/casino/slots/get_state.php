<?php
// casino/slots/get_state.php
session_start();
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Gagal mengambil data.'];
$servername = "localhost";
$db_username = "admin"; // Ganti
$db_password = "admin123"; // Ganti
$dbname = "login_app";

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Sesi tidak valid.';
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    $response['message'] = 'Koneksi database gagal.';
    // Log error
    error_log("Get State DB Connection Error: " . $conn->connect_error);
    echo json_encode($response);
    exit();
}

try {
    // Ambil saldo user
    $stmt_user = $conn->prepare("SELECT money FROM users WHERE id = ?");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    $user_balance = 0;
    if ($result_user->num_rows === 1) {
        $user_data = $result_user->fetch_assoc();
        $user_balance = $user_data['money'];
    }
    $stmt_user->close();

    // Ambil vault
    $stmt_vault = $conn->prepare("SELECT value FROM game_state WHERE key_name = 'vault_slots'");
    $stmt_vault->execute();
    $result_vault = $stmt_vault->get_result();
    $vault_amount = 0;
    if ($result_vault->num_rows === 1) {
        $vault_data = $result_vault->fetch_assoc();
        $vault_amount = $vault_data['value'];
    }
    $stmt_vault->close();

    $response = [
        'success' => true,
        'current_balance' => (int)$user_balance, // Cast ke integer
        'current_vault' => (int)$vault_amount    // Cast ke integer
    ];

} catch (Exception $e) {
    $response['message'] = "Error mengambil data: " . $e->getMessage();
    error_log("Get State Error (User ID: $user_id): " . $e->getMessage());
}

$conn->close();
echo json_encode($response);
exit();
?>
