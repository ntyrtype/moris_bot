<?php
session_start();
require "../config/Database.php";

// Memastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Ambil no_tiket dari parameter GET
$no_tiket = htmlspecialchars(trim($_GET['no_tiket'] ?? ''), ENT_QUOTES, 'UTF-8');

// Validasi no_tiket
if (empty($no_tiket)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'No Tiket tidak boleh kosong']);
    exit();
}

// Query untuk mengambil data log berdasarkan no_tiket
$query = "SELECT 
                tanggal,
                status,
                progress_order,
                keterangan,
                nama,
                role
            FROM 
                log_orders
            WHERE 
                No_Tiket = :no_tiket
            ORDER BY 
                tanggal DESC
        ";

try {
    // Eksekusi query
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":no_tiket", $no_tiket, PDO::PARAM_STR);
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Jika data kosong
    if (empty($logs)) {
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'Data log tidak ditemukan untuk No Tiket ini']);
        exit();
    }

    // Kembalikan data dalam format JSON
    header('Content-Type: application/json');
    echo json_encode($logs);
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Terjadi kesalahan pada server: ' . $e->getMessage()]);
    exit();
}