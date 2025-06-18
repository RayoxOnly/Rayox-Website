<?php
require_once '../config.php';
setSecurityHeaders();
secureSession();
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register | Rayox</title>
<link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
<link rel="manifest" href="/assets/site.webmanifest">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
<style>
    /* Salin CSS dari halaman Login yang dimodifikasi */
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

    .register-container { /* Ganti nama kelas agar spesifik */
        background-color: rgba(255, 255, 255, 0.95);
        padding: 3rem 2.5rem;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 420px;
        margin: 1rem;
        backdrop-filter: blur(5px);
    }

    .register-header { /* Ganti nama kelas agar spesifik */
        text-align: center;
        margin-bottom: 2.5rem;
    }

     .register-header .logo { /* Ganti nama kelas agar spesifik */
        font-size: 2.5rem;
        font-weight: 800;
        background: linear-gradient(to right, #4f46e5, #818cf8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 0.75rem;
        letter-spacing: -1px;
        display: inline-block;
    }

     .register-header .logo::after { /* Ganti nama kelas agar spesifik */
        content: '';
        display: block;
        margin: 5px auto 0;
        width: 60px;
        height: 3px;
        background: linear-gradient(to right, #4f46e5, #818cf8);
        border-radius: 2px;
    }

    .register-header p { /* Ganti nama kelas agar spesifik */
        color: #64748b;
        font-size: 1rem;
    }

    .input-group {
        margin-bottom: 1.5rem;
    }

    .input-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #4b5563;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .input-group input {
        width: 100%;
        padding: 0.9rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        font-size: 1rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        background-color: rgba(255, 255, 255, 0.8);
    }

    .input-group input:focus {
        outline: none;
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
    }

    .btn-register { /* Ganti nama kelas agar spesifik */
        width: 100%;
        padding: 1rem 2rem;
        font-size: 1.05rem;
        border: none;
        border-radius: 50px;
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
        margin-top: 1rem;
    }

    .btn-register:hover { /* Ganti nama kelas agar spesifik */
      background: linear-gradient(to right, #4338ca, #6366f1);
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(79, 70, 229, 0.3);
    }

    .login-link { /* Ganti nama kelas agar spesifik */
        text-align: center;
        margin-top: 2rem;
        font-size: 0.95rem;
        color: #64748b;
    }

    .login-link a { /* Ganti nama kelas agar spesifik */
        color: #4f46e5;
        text-decoration: none;
        font-weight: 600;
    }
    .login-link a:hover { /* Ganti nama kelas agar spesifik */
        text-decoration: underline;
     }

    /* Message Styling - Sama seperti Login */
    .message {
        padding: 12px 15px;
        border-radius: 10px;
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
    .success-message {
        background-color: #dcfce7;
        border-color: #86efac;
        color: #166534;
    }

    /* Fade out animation - Sama seperti Login */
    @keyframes fadeOut {
        from {opacity: 1;}
        to {opacity: 0; display: none;}
    }

    .fade-out {
        animation: fadeOut 3s ease-out 3s forwards;
    }

     /* Responsive - Sama seperti Login */
     @media (max-width: 480px) {
        .register-container { /* Ganti nama kelas */
            padding: 2rem 1.5rem;
        }
        .register-header .logo { /* Ganti nama kelas */
            font-size: 2rem;
        }
         .register-header p { /* Ganti nama kelas */
            font-size: 0.9rem;
        }
        .btn-register { /* Ganti nama kelas */
            padding: 0.9rem 1.5rem;
            font-size: 1rem;
        }
     }
</style>
</head>
<body>

<div class="register-container"> <div class="register-header"> <div class="logo">Rayox.site</div>
        <p>Buat akun baru Anda</p>
    </div>

    <?php if (isset($_GET['error'])): ?>
    <div id="errorMessage" class="message error-message"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
    <div id="successMessage" class="message success-message"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <form method="POST" action="register_process.php">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <div class="input-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required placeholder="Enter new username" autocomplete="username" pattern="[a-zA-Z0-9_]{3,20}" title="Username must be 3-20 characters long and contain only letters, numbers, and underscores">
        </div>

        <div class="input-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Enter new password" autocomplete="new-password" minlength="8" title="Password must be at least 8 characters long">
        </div>

        <button type="submit" class="btn btn-register"><i class="fas fa-user-plus"></i> Register</button>
    </form>

    <div class="login-link">
        Already have an account? <a href="/login">Login here</a>
    </div>
</div>

<script>
    // Auto-hide messages after 5 seconds
    const messages = document.querySelectorAll('.message');
    messages.forEach(message => {
        setTimeout(() => {
            message.classList.add('fade-out');
        }, 2000);
    });
    
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        
        if (!username || !password) {
            e.preventDefault();
            alert('Please fill in all fields');
            return false;
        }
        
        // Validate username pattern
        const usernamePattern = /^[a-zA-Z0-9_]{3,20}$/;
        if (!usernamePattern.test(username)) {
            e.preventDefault();
            alert('Username must be 3-20 characters long and contain only letters, numbers, and underscores');
            return false;
        }
        
        // Validate password length
        if (password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long');
            return false;
        }
    });
</script>

</body>
</html>