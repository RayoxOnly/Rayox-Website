<?php
// File: dashboard/saran.php (Modifikasi)
session_start(); // Mulai session

// --- Pengecekan Login ---
if (!isset($_SESSION['user'])) {
    header('Location: /login?error=Anda harus login untuk memberikan saran');
    exit();
}

// Ambil username dari session
$username = htmlspecialchars($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Saran | Rayox</title>
<link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/LOGO/favicon32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/LOGO/favicon16x16.png">
<link rel="manifest" href="/assets/site.webmanifest">
<style>
/* ... (style kamu sebelumnya atau sesuaikan) ... */
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 40px auto; /* Beri jarak atas bawah */
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
h1 {
    color: #333;
    text-align: center;
    margin-bottom: 10px;
}
.welcome-text { /* Class untuk text sambutan */
    text-align: center;
    font-size: 1.2em;
    margin-bottom: 20px;
    color: #555;
}
p.description { /* Class untuk deskripsi */
    color: #666;
    line-height: 1.6;
    margin-bottom: 30px;
    text-align: justify; /* Ratakan teks */
}
textarea {
    width: 100%; /* Lebar penuh */
    box-sizing: border-box; /* Include padding dan border dalam width */
    height: 150px; /* Tinggi lebih besar */
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-family: inherit; /* Gunakan font body */
    margin-bottom: 15px; /* Jarak bawah */
    font-size: 1rem; /* Ukuran font textarea */
}
label {
    display: block;
    margin-bottom: 8px;
    text-align: left;
    color: #444;
    font-weight: bold; /* Tebalkan label */
}
button { /* Styling untuk tombol submit */
    background-color: #4f46e5;
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.2s;
    display: block; /* Jadikan block agar bisa diatur margin auto */
    margin: 0 auto 20px auto; /* Pusatkan tombol dan beri jarak bawah */
}
button:hover {
    background-color: #4338ca;
}
.message { /* Styling untuk pesan sukses/error */
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 4px;
    text-align: center;
}
.success {
    background-color: #d1e7dd;
    color: #0f5132;
    border: 1px solid #badbcc;
}
.error {
    background-color: #f8d7da;
    color: #842029;
    border: 1px solid #f5c2c7;
}
.navigation-links { /* Wadah untuk link navigasi */
    text-align: center;
    margin-top: 20px;
}
.navigation-links a {
    margin: 0 10px;
    color: #4f46e5;
    text-decoration: none;
}
.navigation-links a:hover {
    text-decoration: underline;
}
</style>
</head>
<body>
<h1>Berikan Masukanmu!</h1>
<div class="welcome-text">Halo, <?php echo $username; ?>!</div>

<?php if (isset($_GET['status'])): ?>
<div class="message success"><?= htmlspecialchars($_GET['status']) ?></div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
<div class="message error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<p class="description">Punya saran untuk website ini? Silahkan isi pada kolom di bawah ini. Apapun! Selama itu tidak melanggar hukum dan aku bisa membuatnya, maka aku akan mempertimbangkan untuk membuatnya. Jadi, kreatiflah!</p>

<form action="submit_saran.php" method="POST">
<div>
<label for="suggestion">Masukkan saran Anda di bawah ini:</label>
<textarea id="suggestion" name="suggestion" placeholder="Ketik saran Anda di sini..." required></textarea>
</div>
<button type="submit">Kirim Saran</button>
</form>

<div class="navigation-links">
<a href="/dashboard">Kembali ke Dashboard</a>
<a href="/logout.php">Logout</a>
</div>
</body>
</html>
