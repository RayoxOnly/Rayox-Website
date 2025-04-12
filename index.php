<!DOCTYPE html>
<html>
<head>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
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
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #666;
            font-size: 0.875rem;
        }

        .input-group {
            margin-bottom: 1.5rem;
        }

        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .input-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: border-color 0.15s ease;
        }

        .input-group input:focus {
            outline: none;
            border-color: #4f46e5;
        }

        .forgot-password {
            text-align: right;
            margin-bottom: 1.5rem;
        }

        .forgot-password a {
            color: #4f46e5;
            font-size: 0.875rem;
            text-decoration: none;
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
            transition: background-color 0.15s ease;
        }

        .login-button:hover {
            background: #4338ca;
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.875rem;
            color: #666;
        }

        .register-link a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 500;
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

    /* Animasi fade out */
    @keyframes fadeOut {
        from {opacity: 1;}
        to {opacity: 0;}
    }

    .fade-out {
        animation: fadeOut 3s ease-out forwards;
    }

    </style>
    <title>Login</title>
</head>
<body>

    <div class="login-container">
        <div class="login-header">
            <h1>Selamat Datang</h1>
            <p>Masukan Username & Password untuk login</p>
        </div>

    <!-- Setelah login-header dan sebelum form -->
<?php if (isset($_GET['error'])): ?>
    <div id="errorMessage" class="error-message"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>


        <form method="POST" action="login.php">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Masukan username anda" required>
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukan password anda" required>
            </div>

            <button type="submit" class="login-button">Login</button>

        </form>
        <div class="register-link">
        Belum punya akun? <a href="register.php">Daftar disini</a>
        </div>
    </div>

    <script>
    // Cek jika ada pesan error
    const errorMessage = document.getElementById('errorMessage');
    if (errorMessage) {
        // Tambahkan class fade-out setelah 3 detik
        setTimeout(() => {
            errorMessage.classList.add('fade-out');
        }, 3000);

        // Hapus element setelah animasi selesai
        setTimeout(() => {
            errorMessage.style.display = 'none';
        }, 6000);
    }
</script>

</body>
</html>
