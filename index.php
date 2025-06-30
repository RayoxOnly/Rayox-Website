<?php
require_once 'config.php';
setSecurityHeaders();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Rayox.site - A modern community platform for gaming and social interaction. Join our growing community today!">
  <meta name="keywords" content="Rayox, community, gaming, social platform, online casino">
  <meta name="author" content="Rayox.site">
  <meta name="robots" content="index, follow">
  
  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= SITE_URL ?>">
  <meta property="og:title" content="Rayox.site - Community Platform">
  <meta property="og:description" content="A modern web application for community building and gaming">
  <meta property="og:image" content="<?= SITE_URL ?>/assets/logo.png">
  
  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="<?= SITE_URL ?>">
  <meta property="twitter:title" content="Rayox.site - Community Platform">
  <meta property="twitter:description" content="A modern web application for community building and gaming">
  <meta property="twitter:image" content="<?= SITE_URL ?>/assets/logo.png">
  
  <title>Rayox | Homepage</title>
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/LOGO/favicon32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/assets/LOGO/favicon16x16.png">
  <link rel="manifest" href="/assets/site.webmanifest">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
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
      flex-direction: column;
      padding: 2rem;
    }

    .container {
      max-width: 1200px;
      text-align: center;
      background-color: rgba(255, 255, 255, 0.9);
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      padding: 4rem 2rem;
      backdrop-filter: blur(5px);
    }

    .logo-container {
      margin-bottom: 3rem;
      position: relative;
      display: inline-block;
    }

    .logo {
      font-size: 5rem;
      font-weight: 800;
      background: linear-gradient(to right, #4f46e5, #818cf8);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 1rem;
      letter-spacing: -1px;
      position: relative;
    }

    .logo::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background: linear-gradient(to right, #4f46e5, #818cf8);
      border-radius: 2px;
    }

    .tagline {
      font-size: 1.2rem;
      color: #64748b;
      margin-bottom: 3rem;
    }

    .buttons {
      display: flex;
      gap: 1.5rem;
      justify-content: center;
      margin-top: 2rem;
    }

    .btn {
      padding: 1rem 2.5rem;
      font-size: 1.1rem;
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
    }

    .btn-login {
      background-color: #fff;
      color: #4f46e5;
      border: 2px solid #4f46e5;
    }

    .btn-login:hover {
      background-color: #f0f5ff;
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(79, 70, 229, 0.2);
    }

    .btn-register {
      background: linear-gradient(to right, #4f46e5, #818cf8);
      color: white;
    }

    .btn-register:hover {
      background: linear-gradient(to right, #4338ca, #6366f1);
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(79, 70, 229, 0.3);
    }

    .features {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
      margin-top: 4rem;
      text-align: left;
    }

    .feature {
      padding: 1.5rem;
      background: rgba(255, 255, 255, 0.8);
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s ease;
    }

    .feature:hover {
      transform: translateY(-5px);
    }

    .feature-icon {
      font-size: 2rem;
      color: #4f46e5;
      margin-bottom: 1rem;
    }

    .feature-title {
      font-size: 1.2rem;
      color: #334155;
      margin-bottom: 0.5rem;
    }

    .feature-desc {
      color: #64748b;
      font-size: 0.95rem;
      line-height: 1.5;
    }

    .footer {
      margin-top: 2rem;
      font-size: 0.9rem;
      color: #94a3b8;
    }

    @media (max-width: 768px) {
      .container {
        padding: 2rem 1rem;
      }

      .logo {
        font-size: 3.5rem;
      }

      .buttons {
        flex-direction: column;
        gap: 1rem;
      }

      .features {
        grid-template-columns: 1fr;
      }
    }

    .floating-shapes {
      position: absolute;
      width: 100%;
      height: 100%;
      overflow: hidden;
      z-index: -1;
    }

    .shape {
      position: absolute;
      opacity: 0.15;
      border-radius: 50%;
      background: linear-gradient(135deg, #4f46e5, #c3cfe2);
    }

    .shape:nth-child(1) {
      width: 150px;
      height: 150px;
      top: 10%;
      left: 15%;
      animation: float 15s infinite linear;
    }

    .shape:nth-child(2) {
      width: 80px;
      height: 80px;
      bottom: 20%;
      right: 10%;
      animation: float 12s infinite linear reverse;
    }

    .shape:nth-child(3) {
      width: 120px;
      height: 120px;
      bottom: 15%;
      left: 25%;
      animation: float 18s infinite linear;
    }

    .shape:nth-child(4) {
      width: 60px;
      height: 60px;
      top: 25%;
      right: 20%;
      animation: float 10s infinite linear reverse;
    }

    @keyframes float {
      0% {
        transform: translate(0, 0) rotate(0deg);
      }
      50% {
        transform: translate(20px, 20px) rotate(180deg);
      }
      100% {
        transform: translate(0, 0) rotate(360deg);
      }
    }
  </style>
</head>
<body>
  <div class="floating-shapes">
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
  </div>

  <div class="container">
    <div class="logo-container">
      <div class="logo">Rayox.site</div>
    </div>
    <p class="tagline">Together, we build the community</p>
    
    <div class="features">
      <div class="feature">
        <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
        <h3 class="feature-title">Secure Access</h3>
        <p class="feature-desc">Our platform prioritizes your security with advanced encryption and authentication methods.</p>
      </div>
      <div class="feature">
        <div class="feature-icon"><i class="fas fa-bolt"></i></div>
        <h3 class="feature-title">Lightning Fast</h3>
        <p class="feature-desc">Experience exceptional speed with our optimized infrastructure and responsive design.</p>
      </div>
      <div class="feature">
        <div class="feature-icon"><i class="fas fa-user-friends"></i></div>
        <h3 class="feature-title">Community</h3>
        <p class="feature-desc">Join our growing community of users and enjoy collaborative features.</p>
      </div>
    </div>
    
    <div class="buttons">
      <a href="/login" class="btn btn-login"><i class="fas fa-sign-in-alt"></i> Login</a>
      <a href="/register" class="btn btn-register"><i class="fas fa-user-plus"></i> Register</a>
    </div>
    
    <div class="footer">
      <p>© 2025 Rayox.site. All rights reserved.</p>
    </div>
  </div>
</body>
</html>