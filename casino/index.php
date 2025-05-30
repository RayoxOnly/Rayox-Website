<?php
session_start(); // Mulai session jika perlu cek login atau ambil data user

// Opsional: Cek jika user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: /login?error=Anda harus login untuk mengakses casino');
    exit();
}
$username = $_SESSION['user'] ?? 'Tamu'; // Ambil username jika ada
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Casino | Rayox</title>
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
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        color: #4b5563;
        display: flex;
        justify-content: center; /* Center the main container */
        align-items: flex-start; /* Align container to top */
        padding: 3rem 2rem; /* Add padding around */
    }

     /* Optional floating shapes like homepage */
    .floating-shapes {
        position: fixed; /* Use fixed to keep behind content */
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: -1;
    }

    .shape {
        position: absolute;
        opacity: 0.1;
        border-radius: 50%;
        background: linear-gradient(135deg, #a8bfff, #e0e7ff); /* Lighter gradient */
        animation: float 15s infinite linear alternate; /* Add alternate direction */
    }
     .shape:nth-child(1) { width: 180px; height: 180px; top: 15%; left: 10%; animation-duration: 18s; }
     .shape:nth-child(2) { width: 90px; height: 90px; bottom: 10%; right: 15%; animation-duration: 14s; animation-delay: -5s; }
     .shape:nth-child(3) { width: 130px; height: 130px; top: 50%; left: 30%; animation-duration: 22s; }
     .shape:nth-child(4) { width: 70px; height: 70px; top: 20%; right: 25%; animation-duration: 12s; animation-delay: -8s;}

    @keyframes float {
        0% { transform: translate(0, 0) rotate(0deg); }
        100% { transform: translate(30px, -30px) rotate(180deg); }
    }


    .container {
        background-color: rgba(255, 255, 255, 0.9);
        padding: 2.5rem 3rem;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        max-width: 900px; /* Wider container */
        width: 100%;
        text-align: center;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }

    .header h1 {
        font-size: 2.5rem;
        font-weight: 800;
        background: linear-gradient(to right, #4f46e5, #818cf8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 0.75rem;
        letter-spacing: -1px;
    }
     .header h1::after { /* Optional underline */
        content: '';
        display: block;
        margin: 5px auto 0;
        width: 70px;
        height: 3px;
        background: linear-gradient(to right, #4f46e5, #818cf8);
        border-radius: 2px;
    }


    .welcome-text {
        margin-bottom: 3rem;
        font-size: 1.15rem;
        color: #64748b;
    }

    .game-grid {
        display: grid;
        /* Adjust columns for responsiveness */
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }

    .game-card { /* Changed from game-icon to game-card */
        background: rgba(255, 255, 255, 0.8);
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden; /* Ensure content stays within rounded corners */
        border: 1px solid rgba(255, 255, 255, 0.1);
        display: flex; /* Use flex for content */
        flex-direction: column;
    }

    .game-card:hover {
        transform: translateY(-8px); /* More pronounced hover effect */
        box-shadow: 0 8px 25px rgba(79, 70, 229, 0.15); /* Adjusted shadow */
    }

    .game-card a {
        text-decoration: none;
        color: inherit; /* Inherit text color */
        display: flex;
        flex-direction: column;
        flex-grow: 1; /* Make link fill the card */
    }

    .game-thumbnail {
        background-color: #e0e7ff; /* Placeholder background */
        height: 140px; /* Fixed height for thumbnail area */
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .game-thumbnail img {
        max-width: 80px; /* Control icon size within thumbnail */
        height: auto;
        filter: drop-shadow(0 3px 5px rgba(0,0,0,0.1));
    }

    .game-info {
        padding: 1.5rem;
        text-align: center;
        flex-grow: 1;
        background-color: rgba(255, 255, 255, 0.9); /* Slightly different bg */
    }

    .game-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #334155;
        margin-bottom: 0.5rem;
    }

    .game-description {
        font-size: 0.9rem;
        color: #64748b;
        line-height: 1.5;
        /* Add min-height if descriptions vary a lot */
    }

    /* Placeholder Styling */
    .game-card.placeholder {
         background: rgba(229, 231, 235, 0.7); /* More subtle placeholder */
         box-shadow: none;
         cursor: default;
         opacity: 0.7;
         border: 1px dashed #9ca3af;
         justify-content: center; /* Center placeholder text */
         align-items: center;
         min-height: 250px; /* Give placeholder a decent size */
    }
     .game-card.placeholder:hover {
        transform: none;
        box-shadow: none;
    }
     .game-card.placeholder .game-info {
         background: transparent;
         padding: 0;
     }
    .game-card.placeholder .game-title {
         color: #6b7280;
         font-style: italic;
         font-size: 1rem;
     }

    .back-link {
        display: inline-block;
        margin-top: 1rem;
        padding: 0.8rem 2rem;
        font-size: 1rem;
        border: 2px solid #4f46e5;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
        text-decoration: none;
        color: #4f46e5;
        background-color: #fff;
    }

    .back-link:hover {
        background-color: #f0f5ff;
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.2);
    }


    @media (max-width: 768px) {
        body { padding: 2rem 1rem; }
        .container { padding: 2rem 1.5rem; }
        .header h1 { font-size: 2rem; }
        .game-grid { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; }
        .game-card.placeholder { min-height: 200px; }
    }
     @media (max-width: 480px) {
        .game-grid { grid-template-columns: 1fr; } /* Single column on very small screens */
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
    <div class="header">
        <h1><i class="fas fa-dice-d20"></i> Rayox Casino</h1>
        <p class="welcome-text">Selamat datang, <?php echo htmlspecialchars($username); ?>! Pilih permainan Anda.</p>
    </div>

    <div class="game-grid">
        <div class="game-card">
            <a href="/casino/slots">
                <div class="game-thumbnail">
                     <img src="/assets/slots_icon.png" alt="Slots Icon">
                 </div>
                <div class="game-info">
                    <h3 class="game-title">Mesin Slot</h3>
                    <p class="game-description">Uji keberuntungan Anda dengan kombinasi emoji!</p>
                </div>
            </a>
        </div>

        <div class="game-card">
             <a href="/casino/russianroulette">
                 <div class="game-thumbnail">
                      <img src="/assets/roulette_icon.png" alt="Russian Roulette Icon">
                  </div>
                 <div class="game-info">
                     <h3 class="game-title">Russian Roulette</h3>
                     <p class="game-description">Permainan PvP klasik dengan risiko tinggi.</p>
                 </div>
             </a>
         </div>

        <div class="game-card placeholder">
             <div class="game-info">
                 <h3 class="game-title">Segera Hadir</h3>
             </div>
         </div>
         <div class="game-card placeholder">
             <div class="game-info">
                 <h3 class="game-title">Segera Hadir</h3>
             </div>
         </div>
         </div>

    <a href="/profile" class="back-link"><i class="fas fa-arrow-left"></i> Kembali ke Profile</a>
</div>

</body>
</html>