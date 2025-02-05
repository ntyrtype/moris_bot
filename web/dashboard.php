<?php
session_start();
require "../config/Database.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

if (isset($_GET['ajax']) && $_GET['ajax'] == "true") {
    header("Content-Type: application/json");

    $filter_date = $_GET['filter_date'] ?? '';

    $sql = "SELECT Status, COUNT(*) AS jumlah FROM orders WHERE 1=1";
    if (!empty($filter_date)) {
        $sql .= " AND DATE(tanggal) = :filter_date";
    }
    $sql .= " GROUP BY Status";

    $stmt = $pdo->prepare($sql);
    if (!empty($filter_date)) {
        $stmt->bindParam(':filter_date', $filter_date);
    }
    $stmt->execute();

    $orders_count = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $orders_count[$row['Status']] = $row['jumlah'];
    }

    echo json_encode($orders_count);
    exit();
}

// ** Query default untuk tampilan awal tanpa filter **
$sql = "SELECT Status, COUNT(*) AS jumlah FROM orders GROUP BY Status";
$stmt = $pdo->prepare($sql);
$stmt->execute();

$orders_count = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $orders_count[$row['Status']] = $row['jumlah'];
}

$order_count = $orders_count['Order'] ?? 0;
$pickup_count = $orders_count['Pickup'] ?? 0;
$close_count = $orders_count['Close'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="./js/dashboard.js"></script>
    <title>Dashboard</title>
</head>
<body>

<div class="navbar">
    <h1>MORIS BOT</h1>
    <button id="toggleSidebar">☰</button>
</div>

<div class="sidebar" id="sidebar">
    <a class="menu" href="dashboard.php">Dashboard</a>
    <a href="order.php">Order</a>
    <a href="pickup.php">PickUp</a>
    <a href="close.php">Close</a>
</div>

<div class="content" id="content">
    <h1 class="headtitle">Dashboard</h1>
    
    <div class="dashboard-container">
        <div class="card_order">
            <h2>Order</h2>
            <p id="order_count"><?= $order_count ?> Order</p>
        </div>
        <div class="card_pickup">
            <h2>Pickup</h2>
            <p id="pickup_count"><?= $pickup_count ?> Pickup</p>
        </div>
        <div class="card_close">
            <h2>Close</h2>
            <p id="close_count"><?= $close_count ?> Close</p>
        </div>
    </div>

    <div class="filter-container">
        <label for="filter_date">Filter Date:</label>
        <input type="date" id="filter_date">
        <button id="filter">Filter</button>
    </div>
</div>

<script src="./js/sidebar.js"></script>
<script src="./js/card.js"></script>

</body>
</html>
