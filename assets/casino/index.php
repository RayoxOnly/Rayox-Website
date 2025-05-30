<!DOCTYPE html>
<html>
<head>
<title>Casino</title>
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f0f0f0;
    margin: 0;
    padding: 20px;
}
.game-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* 3 kolom */
    grid-gap: 20px;
    max-width: 600px;
    margin: 20px auto;
}
.game-icon {
    background-color: #fff;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
.game-icon a {
    display: block;
    text-decoration: none;
    color: #333;
}
.game-icon img {
    max-width: 100%;
    height: auto;
    margin-bottom: 10px;
}
</style>
</head>
<body>

<h1>Selamat datang di Casino</h1>

<div class="game-grid">
<div class="game-icon">
<a href="/casino/slots">
<img src="/assets/slots_icon.png" alt="Slots">
Slots
</a>
</div>
<div class="game-icon">
<a href="/casino/roulette">
<img src="/assets/roulette_icon.png" alt="Russian Roulette">
Russian Roulette
</a>
</div>
<div class="game-icon">
</div>
<div class="game-icon">
</div>
<div class="game-icon">
</div>
<div class="game-icon">
</div>
<div class="game-icon">
</div>
<div class="game-icon">
</div>
<div class="game-icon">
</div>
</div>

</body>
</html>
