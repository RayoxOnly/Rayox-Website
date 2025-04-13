<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register | Rayox</title>
<link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
<link rel="manifest" href="/assets/site.webmanifest">
<style>
/* Gunakan style yang sama seperti index.php */
body {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}
.login-container {
    background: white;
    padding: 2.5rem;
    border-radius: 1rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    width: 100%;
    max-width: 400px;
    margin: 1rem;
}
.login-header {
    text-align: center;
    margin-bottom: 2rem;
}
.login-header h1 {
    color: #333;
    font-size: 1.75rem;
    font-weight: 600;
}
.input-group {
    margin-bottom: 1.5rem;
}
.input-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #333;
    font-size: 0.875rem;
}
.input-group input {
    width: 100%;
    padding: 0.75rem;
    border: 1.5px solid #e2e8f0;
    border-radius: 0.5rem;
    font-size: 0.875rem;
}
.login-button {
    width: 100%;
    padding: 0.75rem;
    background: #4f46e5;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
}
.login-button:hover {
    background: #4338ca;
}
.register-link {
    text-align: center;
    margin-top: 1.5rem;
    font-size: 0.875rem;
}
.register-link a {
    color: #4f46e5;
    text-decoration: none;
}
.error-message {
    background-color: #fee2e2;
    border: 1px solid #ef4444;
    color: #991b1b;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    font-size: 0.875rem;
}
.success-message {
    background-color: #dcfce7;
    border: 1px solid #22c55e;
    color: #166534;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    font-size: 0.875rem;
}
</style>
</head>
<body>

<div class="login-container">
<div class="login-header">
<h1>Daftar Akun</h1>
<p>Masukan Username & Password untuk Register</p>
</div>

<?php if (isset($_GET['error'])): ?>
<div class="error-message"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="success-message"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<form method="POST" action="register_process.php">
<div class="input-group">
<label for="username">Username</label>
<input type="text" id="username" name="username" required placeholder="Masukkan username">
</div>

<div class="input-group">
<label for="password">Password</label>
<input type="password" id="password" name="password" required placeholder="Masukkan password">
</div>

<button type="submit" class="login-button">Daftar</button>
</form>

<div class="register-link">
Sudah punya akun? <a href="/login">Login disini</a>
</div>
</div>

</body>
</html>
