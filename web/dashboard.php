<?php
session_start();
require "../config/Database.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Unauthorized"]);
    header("Location: index.php");
    exit();
}
$order_count = 0;
$pickup_count = 0;
$close_count = 0;

if (isset($_GET['ajax']) && $_GET['ajax'] == "true") {
    header("Content-Type: application/json");

    $filter_date = $_GET['filter_date'] ?? '';

    $sql = "SELECT Status, COUNT(*) AS jumlah FROM orders WHERE 1=1";
    $sql_transaksi = "SELECT transaksi, Status, COUNT(*) AS jumlah FROM orders WHERE 1=1";

    if (!empty($filter_date)) {
        $sql .= " AND DATE(tanggal) = :filter_date";
        $sql_transaksi .= " AND DATE(tanggal) = :filter_date";
    }

    $sql .= " GROUP BY Status";
    $sql_transaksi .= " GROUP BY transaksi, Status";

    $stmt = $pdo->prepare($sql);
    $stmt_transaksi = $pdo->prepare($sql_transaksi);

    if (!empty($filter_date)) {
        $stmt->bindParam(':filter_date', $filter_date);
        $stmt_transaksi->bindParam(':filter_date', $filter_date);
    }

    $stmt->execute();
    $stmt_transaksi->execute();

    $orders_count = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $orders_count[$row['Status']] = $row['jumlah'];
    }

    $transaksi_count = [];
    while ($row = $stmt_transaksi->fetch(PDO::FETCH_ASSOC)) {
        $transaksi_count[$row['transaksi']][$row['Status']] = $row['jumlah'];
    }

    echo json_encode([
        'orders_count' => $orders_count,
        'transaksi_count' => $transaksi_count
    ]);
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Dashboard</title>
</head>
<body>

<div class="sidebar" id="sidebar">
    <h1>MORIS BOT</h1>
    <a href="dashboard.php">Dashboard</a>
    <a href="order.php">Order</a>
    <a href="pickup.php">PickUp</a>
    <a href="close.php">Close</a>
</div>

<div class="content" id="content">
    <div class="navbar">
        <button id="toggleSidebar">☰</button>
        <a href="">Plasa</a>
        <p>|</p>
        <a href="">Teknisi</a>
        <div class="profile-dropdown">
            <button id="profileButton"><?php echo htmlspecialchars($_SESSION['nama']); ?></button>
            <div class="profile-content" id="profileContent">
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="add_user.php">Tambah User</a>
                <?php endif; ?>
                <form action="logout.php" method="POST">
                    <button type="submit" class="logout-btn" style="width: 100%; border: none; background: none; text-align: left;">Logout</button>
                </form>
            </div>
        </div>
    </div>
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
        <input type="date" id="filter_date" value="<?php echo date('Y-m-d'); ?>">
        <button id="filter">Filter</button>
    </div>

    <div id="transaksi_table">
        <!-- Table for transaksi data will be inserted here by JavaScript -->
    </div>
</div>

<script src="./js/sidebar.js"></script>
<script src="./js/card.js"></script>
<script src="./js/profile.js"></script>

</body>
</html>
