<?php
session_start();
require "../config/Database.php"; // Memuat konfigurasi database

// Cek apakah pengguna sudah login dan memiliki role admin
if (!isset($_SESSION['role'])) {
    $_SESSION['message'] = "Anda harus login terlebih dahulu!";
    header("Location: index.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    $_SESSION['message'] = "Anda tidak memiliki akses untuk mengubah kategori!";
    header("Location: dashboard.php");
    exit();
}

// Pastikan koneksi database tersedia
if (!isset($pdo)) {
    die("Koneksi database tidak tersedia.");
}

// Ambil regex kategori dari database
$stmt = $pdo->query("SELECT regex_pattern FROM kategori LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$kategori_regex = isset($row['regex_pattern']) ? $row['regex_pattern'] : '';

// Ubah "|" menjadi ", " untuk placeholder input
$kategori_placeholder = str_replace('|', ', ', $kategori_regex);

// Proses jika form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['category_regex'])) {
        $category_regex = trim($_POST['category_regex']); // Ambil input admin

        // Cek apakah input sama dengan yang tersimpan
        if ($category_regex === $kategori_regex) {
            $_SESSION['message'] = "Kategori tidak berubah!";
            header("Location: admin.php");
            exit();
        }

        // Validasi format (hanya boleh huruf, angka, dan | sebagai pemisah)
        if (!preg_match('/^[A-Za-z0-9|]+$/', $category_regex)) {
            $_SESSION['message'] = "Format kategori tidak valid! Gunakan hanya huruf, angka, dan pemisah '|'.";
            header("Location: admin.php");
            exit();
        }

        // Jika valid, update database
        try {
            $stmt = $pdo->prepare("UPDATE kategori SET regex_pattern = ? LIMIT 1");
            $stmt->execute([$category_regex]);

            $_SESSION['message'] = "Kategori berhasil diperbarui!";
            header("Location: admin.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['message'] = "Gagal memperbarui kategori: " . $e->getMessage();
            header("Location: admin.php");
            exit();
        }
    } else {
        $_SESSION['message'] = "Input tidak boleh kosong!";
        header("Location: admin.php");
        exit();
    }
}
?>