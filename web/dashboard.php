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

    $transaksi = $_GET['transaksi'] ?? '';
    $kategori = $_GET['kategori'] ?? '';
    $start_date = $_GET['start_date'] ?? '';
    $end_date = $_GET['end_date'] ?? '';

    $sql = "SELECT Status, COUNT(*) AS jumlah FROM orders WHERE 1=1";

    if (!empty($transaksi)) {
        $sql .= " AND transaksi = :transaksi";
    }
    if (!empty($kategori)) {
        $sql .= " AND kategori = :kategori";
    }
    if (!empty($start_date) && !empty($end_date)) {
        $sql .= " AND DATE(tanggal) BETWEEN :start_date AND :end_date";
    }

    $sql .= " GROUP BY Status";

    $stmt = $pdo->prepare($sql);

    if (!empty($transaksi)) {
        $stmt->bindParam(':transaksi', $transaksi);
    }
    if (!empty($kategori)) {
        $stmt->bindParam(':kategori', $kategori);
    }
    if (!empty($start_date) && !empty($end_date)) {
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
    }

    $stmt->execute();

    $orders_count = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $orders_count[$row['Status']] = $row['jumlah'];
    }

    echo json_encode([
        'orders_count' => $orders_count
    ]);
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="60">
    <link rel="stylesheet" href="./style/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <a href="home.php" class="home-icon"><i class="fas fa-home"></i></a>
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
    <!-- Filter -->
    <div class="filter">
        <form action="" id="filterForm" method="GET">
        <input type="hidden" name="order_by" value="<?= htmlspecialchars($order_by) ?>">
            <select aria-label="transaksi" name="transaksi" id="transaksi">
                <option value="">All Transaksi</option>
                <option value="PDA">PDA</option>
                <option value="MO">MO</option>
                <option value="ORBIT">ORBIT</option>
                <option value="FFG">FFG</option>
                <option value="UNSPEk">UNSPEK</option>
            </select>

            <select aria-label="kategori" name="kategori" id="kategori">
                <option value="">All Kategori</option>
                <option value="Indibiz">Indibiz</option>
                <option value="Indihome">Indihome</option>
                <option value="Datin">Datin</option>
                <option value="WMS">WMS</option>
                <option value="OLO">OLO</option>
            </select>

            <!-- <div class="filter_date"> -->
            <label for="start_date">Date:</label>
            <input type="date" name="start_date" id="start_date" value="<?= isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : '' ?>">
            <label for="end_date">to:</label>
            <input type="date" name="end_date" id="end_date" value="<?= isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : '' ?>">
            <!-- </div> -->

            <button type="submit">Filter</button>
        </form>
    </div>
    <!-- Kartu Statistik -->
    <div class="stats">
        <div class="card_order">
            <h3>Total Order</h3>
            <p class="record-count" id="order_count">0</p>
        </div>
        <div class="card_pickup">
            <h3>Total Pickup</h3>
            <p class="record-count" id="pickup_count">0</p>
        </div>
        <div class="card_close">
            <h3>Total Close</h3>
            <p class="record-count" id="close_count">0</p>
        </div>
    </div>
    <!-- Tabel dan Grafik -->
    <div class="dashboard-content">
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <div class="table-container">
                <table id="productivityTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Record Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Grafik Progress by Tanggal -->
            <div class="chart-container">
                <canvas id="progressChart"></canvas>
                <canvas id="categoryChart"></canvas>
                <canvas id="progressTypeChart"></canvas>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="./js/sidebar.js"></script>
<script src="./js/card.js"></script>
<script src="./js/profile.js"></script>

</body>
</html>
