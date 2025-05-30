<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Rayox</title>
<link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
<link rel="manifest" href="/assets/site.webmanifest">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', 'Roboto', 'Arial', sans-serif;
    }

    body {
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        padding: 2rem;
    }

    .login-container {
        background-color: rgba(255, 255, 255, 0.95);
        padding: 3rem 2.5rem;
        border-radius: 20px; /* Increased radius */
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 420px;
        margin: 1rem;
        backdrop-filter: blur(5px);
    }

    .login-header {
        text-align: center;
        margin-bottom: 2.5rem;
    }

    .login-header .logo {
        font-size: 2.5rem; /* Adjust size as needed */
        font-weight: 800;
        background: linear-gradient(to right, #4f46e5, #818cf8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 0.75rem;
        letter-spacing: -1px;
        display: inline-block; /* Needed for gradient */
    }

     .login-header .logo::after { /* Optional underline like homepage */
        content: '';
        display: block;
        margin: 5px auto 0;
        width: 60px;
        height: 3px;
        background: linear-gradient(to right, #4f46e5, #818cf8);
        border-radius: 2px;
    }

    .login-header p {
        color: #64748b; /* Adjusted color */
        font-size: 1rem;
    }

    .input-group {
        margin-bottom: 1.5rem;
    }

    .input-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #4b5563; /* Adjusted color */
        font-size: 0.9rem;
        font-weight: 600;
    }

    .input-group input {
        width: 100%;
        padding: 0.9rem 1rem; /* Slightly larger padding */
        border: 1px solid #d1d5db; /* Adjusted border color */
        border-radius: 10px; /* Increased radius */
        font-size: 1rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        background-color: rgba(255, 255, 255, 0.8);
    }

    .input-group input:focus {
        outline: none;
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2); /* Focus ring */
    }

    .btn-login {
        width: 100%;
        padding: 1rem 2rem;
        font-size: 1.05rem;
        border: none;
        border-radius: 50px; /* Pill shape */
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        background: linear-gradient(to right, #4f46e5, #818cf8);
        color: white;
        margin-top: 1rem; /* Add some space above button */
    }

    .btn-login:hover {
      background: linear-gradient(to right, #4338ca, #6366f1);
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(79, 70, 229, 0.3);
    }

    .register-link {
        text-align: center;
        margin-top: 2rem;
        font-size: 0.95rem;
        color: #64748b;
    }

    .register-link a {
        color: #4f46e5;
        text-decoration: none;
        font-weight: 600;
    }
     .register-link a:hover {
        text-decoration: underline;
     }

    /* Message Styling */
    .message {
        padding: 12px 15px;
        border-radius: 10px; /* Match input radius */
        margin-bottom: 1.5rem;
        text-align: center;
        font-size: 0.95rem;
        border: 1px solid transparent;
    }
    .error-message {
        background-color: #fee2e2;
        border-color: #fca5a5;
        color: #b91c1c;
    }
    .success-message { /* If you add success messages */
        background-color: #dcfce7;
        border-color: #86efac;
        color: #166534;
    }

    /* Fade out animation */
    @keyframes fadeOut {
        from {opacity: 1;}
        to {opacity: 0; display: none;} /* Added display none */
    }

    .fade-out {
        animation: fadeOut 3s ease-out 3s forwards; /* Start fade after 3s */
    }

    /* Responsive */
     @media (max-width: 480px) {
        .login-container {
            padding: 2rem 1.5rem;
        }
        .login-header .logo {
            font-size: 2rem;
        }
         .login-header p {
            font-size: 0.9rem;
        }
        .btn-login {
            padding: 0.9rem 1.5rem;
            font-size: 1rem;
        }
     }

</style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <div class="logo">Rayox.site</div>
        <p>Masuk untuk melanjutkan</p>
    </div>

    <?php if (isset($_GET['error'])): ?>
    <div id="errorMessage" class="message error-message"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['success'])): /* Display success message from registration */ ?>
    <div id="successMessage" class="message success-message"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>


    <form method="POST" action="login_process.php">
        <div class="input-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Masukkan username Anda" required>
        </div>

        <div class="input-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Masukkan password Anda" required>
        </div>

        <button type="submit" class="btn btn-login"><i class="fas fa-sign-in-alt"></i> Login</button>

    </form>
    <div class="register-link">
        Belum punya akun? <a href="/register">Daftar disini</a>
    </div>
</div>

<script>
    const errorMessage = document.getElementById('errorMessage');
    if (errorMessage) {
        errorMessage.classList.add('fade-out');
    }
    const successMessage = document.getElementById('successMessage');
     if (successMessage) {
        successMessage.classList.add('fade-out');
    }
</script>

</body>
</html>