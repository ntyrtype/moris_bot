<?php
ob_start(); // Menyalakan output buffering
session_start();
require "../config/Database.php";

// Memastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style/style.css">
    <title>Close</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
</head>
<body>

<div class="navbar">
    <h1>MORIS BOT</h1>
    <button id="toggleSidebar">☰</button>
    <div class="profile-dropdown">
        <button id="profileButton"><?php echo htmlspecialchars($_SESSION['nama']); ?></button>
        <div class="profile-content" id="profileContent">
            <form action="logout.php" method="POST">
            <button type="submit" class="logout-btn" style="width: 100%; border: none; background: none; text-align: left;">Logout</button>
            </form>
        </div>
    </div>
</div>

<div class="sidebar" id="sidebar">
    <a class="menu"href="dashboard.php">Dashboard</a>
    <a href="order.php">Order</a>
    <a href="pickup.php">PickUp</a>
    <a href="close.php">Close</a>
</div>

<div class="content" id="content">
    <h1 class="headtitle">Close Menu</h1>
    <table id="dataTable">
        <thead>
            <tr>
                <th>No</th>
                <th>Order ID</th>
                <th>Transaksi</th>
                <th>Keterangan</th>
                <th>No Tiket</th>
                <th>Kontak</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Query untuk mengambil data dengan status 'Close'
            $sql = "SELECT * FROM orders WHERE Status = 'Close' ORDER BY No DESC";
            $stmt = $pdo->query($sql);

            if ($stmt->rowCount() > 0) {
                $no = 1;
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $telegram_username = $row['username_telegram'];
                    $telegram_link = "https://t.me/{$telegram_username}";
                    echo "<tr>
                        <td>{$no}</td>
                        <td>{$row['Order_ID']}</td>
                        <td>{$row['Transaksi']}</td>
                        <td>" . nl2br(htmlspecialchars($row['Keterangan'])) . "</td>
                        <td>{$row['No_Tiket']}</td>
                        <td><a href='{$telegram_link}' class='telegram-link' target='_blank'>Kontak</a></td>
                        <td>{$row['Status']}</td>
                    </tr>";
                    $no++;
                }
            } else {
                echo "<tr><td colspan='7'>No data available</td></tr>";
            }

            ?>
        </tbody>
    </table>
</div>

<script src="./js/sidebar.js"></script>
<script src="./js/datatable.js"></script>
<script src="./js/profile.js"></script>

</body>
</html>