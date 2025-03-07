<?php
header('Content-Type: application/json');
require '../config/Database.php';

$stmt = $pdo->query("SELECT * FROM groups");
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($groups);
exit;
?>
