<?php
// Konfigurasi koneksi database
$host = 'localhost'; // Nama host database (biasanya 'localhost' untuk server lokal)
$dbname = 'moris_bot'; // Nama database yang digunakan
$username = 'root'; // Username database (default di XAMPP adalah 'root')
$password = ''; // Password database (kosong di XAMPP secara default)

try {
    // Membuat koneksi baru ke database menggunakan PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Mengatur mode error agar melempar exception jika terjadi kesalahan
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Menangani error koneksi dan menghentikan eksekusi dengan pesan error
    die("Connection failed: " . $e->getMessage());
}
?>
