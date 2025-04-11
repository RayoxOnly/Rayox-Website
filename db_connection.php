<?php
$host = "localhost"; // Ganti dengan host database Anda
$username = "rayhanadmin";  // Ganti dengan username database Anda
$password = "RayhanZirfa";      // Ganti dengan password database Anda
$database = "database"; // Ganti dengan nama database Anda

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
