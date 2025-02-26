<?php
session_start();
require "../config/Database.php";

// Set response header agar terbaca sebagai JSON
header('Content-Type: application/json');
ob_clean(); // Membersihkan output buffer sebelum mengirim response JSON

// Memastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Anda harus login!"]);
    exit();
}

// Pastikan database terhubung
if (!isset($pdo)) {
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal!"]);
    exit();
}

// Fungsi generate No_Tiket (Aman & Unik)
function generateTicket($pdo) {
    do {
        $no_tiket = 'TKT' . strtoupper(substr(uniqid(rand(), true), -5));
        $query = "SELECT COUNT(*) FROM orders WHERE No_Tiket = :no_tiket";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':no_tiket', $no_tiket, PDO::PARAM_STR);
        $stmt->execute();
        $count = $stmt->fetchColumn();
    } while ($count > 0);
    return $no_tiket;
}

// Ambil data dari JavaScript (JSON)
$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !is_array($data)) {
    echo json_encode(["status" => "error", "message" => "Format data tidak sesuai!"]);
    exit();
}

// ENUM Validasi
$kategori_enum = ['INDIHOME', 'INDIBIZ', 'OLO', 'WIFIID', 'ASTINET', 'METRO', 'VPNIP'];
$status_enum = ['ORDER', 'PICKUP', 'CLOSE'];
$order_by_enum = ['TEKNISI', 'PLASA'];

// Query Insert Menggunakan Prepared Statement
$query = "INSERT INTO orders (Order_ID, Transaksi, Kategori, Keterangan, No_Tiket, Status, progress_order, order_by, tanggal) 
        VALUES (:order_id, :transaksi, :kategori, :keterangan, :no_tiket, :status, 'On Check', :order_by, CURDATE())";
$stmt = $pdo->prepare($query);

// Loop untuk Memasukkan Data
foreach ($data as $row) {
    $order_id  = isset($row["order_id"]) ? strtoupper(trim($row["order_id"])) : "";
    $transaksi = isset($row["transaksi"]) ? strtoupper(trim($row["transaksi"])) : "";
    $kategori  = isset($row["kategori"]) ? strtoupper(trim($row["kategori"])) : "";
    $keterangan = isset($row["keterangan"]) ? trim($row["keterangan"]) : "";
    $status    = isset($row["status"]) ? strtoupper(trim($row["status"])) : "";
    $order_by  = isset($row["order_by"]) ? strtoupper(trim($row["order_by"])) : "";

    // Validasi ENUM
    if (!in_array($kategori, $kategori_enum)) $kategori = 'INDIHOME';
    if (!in_array($status, $status_enum)) $status = 'ORDER';
    if (!in_array($order_by, $order_by_enum)) $order_by = 'TEKNISI';

    // Generate No_Tiket
    $no_tiket = generateTicket($pdo);

    // Eksekusi Query
    $stmt->execute([
        ':order_id'  => $order_id,
        ':transaksi' => $transaksi,
        ':kategori'  => $kategori,
        ':keterangan' => $keterangan,
        ':no_tiket'  => $no_tiket,
        ':status'    => $status,
        ':order_by'  => $order_by
    ]);
}

echo json_encode(["status" => "success", "message" => "Upload sukses!"]);
?>
