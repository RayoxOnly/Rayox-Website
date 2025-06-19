<?php
// File: /casino/russianroulette/db_config.php
// Konfigurasi koneksi database terpusat

$db_host = "localhost";
$db_user = "admin";
$db_pass = "admin123";
$db_name = "login_app";

// Membuat koneksi
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Cek koneksi
if ($conn->connect_error) {
    // Hentikan skrip atau tangani error dengan cara lain
    // Di production, sebaiknya log error dan tampilkan pesan umum
    error_log("Koneksi database gagal: " . $conn->connect_error);
    die("Tidak dapat terhubung ke database. Silakan coba lagi nanti.");
}

// Set charset ke utf8mb4 untuk dukungan emoji dan karakter internasional
if (!$conn->set_charset("utf8mb4")) {
     error_log("Error loading character set utf8mb4: " . $conn->error);
     // Mungkin tidak fatal, tapi bagus untuk dicatat
}

// Fungsi helper untuk log aksi (jika tabel roulette_log ada)
function log_roulette_action($game_id, $user_id, $action) {
    global $conn; // Gunakan koneksi global
    // Cek apakah koneksi valid sebelum mencoba query
    if ($conn && !$conn->connect_error) {
         $stmt_log = $conn->prepare("INSERT INTO roulette_log (game_id, user_id, action) VALUES (?, ?, ?)");
        if ($stmt_log) {
            $stmt_log->bind_param("iis", $game_id, $user_id, $action);
            $stmt_log->execute();
            $stmt_log->close();
        } else {
            error_log("Gagal menyiapkan statement log: " . $conn->error);
        }
    } else {
        error_log("Koneksi DB tidak valid saat mencoba log action.");
    }
}

// Helper function to check if user has active or waiting game
function check_user_has_active_or_waiting_game($conn, $user_id) {
    $stmt = $conn->prepare("SELECT id FROM roulette_games WHERE (creator_id = ? OR opponent_id = ?) AND status IN ('waiting', 'active') LIMIT 1");
    if (!$stmt) return false;
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['id'];
    }
    $stmt->close();
    return false;
}

// Jangan tutup koneksi di sini ($conn->close();)
// Biarkan file yang meng-include file ini yang menutup koneksi setelah selesai.
?>