<?php
// Mengatur header response sebagai JSON    
header('Content-Type: application/json');
// Memuat konfigurasi database dan koneksi PDO
require '../config/Database.php';

// Membuat query untuk mengambil semua data groups
$stmt = $pdo->query("SELECT * FROM groups");
// Mengambil hasil query dalam bentuk array asosiatif
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mengkonversi hasil ke format JSON dan mengirim response
echo json_encode($groups);
exit;
?>
