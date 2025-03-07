<?php
session_start();
require "../config/Database.php";

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["success" => false, "message" => "Akses ditolak!"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['group_id'])) {
    try {
        $groupId = $_POST['group_id'];
        
        // Cek status saat ini
        $stmt = $pdo->prepare("SELECT is_active FROM groups WHERE group_id = ?");
        $stmt->execute([$groupId]);
        $currentStatus = $stmt->fetchColumn();

        if ($currentStatus === false) {
            echo json_encode(["success" => false, "message" => "Group tidak ditemukan!"]);
            exit;
        }

        // Toggle status
        $newStatus = $currentStatus ? 0 : 1;

        // Update status
        $stmt = $pdo->prepare("UPDATE groups SET is_active = ? WHERE group_id = ?");
        $stmt->execute([$newStatus, $groupId]);

        echo json_encode([
            "success" => true,
            "message" => "Status berhasil diperbarui!",
            "new_status" => $newStatus
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Gagal memperbarui status: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Permintaan tidak valid!"]);
}
?>
