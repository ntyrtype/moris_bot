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
</div>

<div class="sidebar" id="sidebar">
    <a class="menu"href="dashboard.php">Dashboard</a>
    <a href="order.php">Order</a>
    <a href="pickup.php">PickUp</a>
    <a href="close.php">Close</a>
</div>

<div class="content" id="content">
    <h1 class="headtitle">Close Menu</h1>
    <table id="closeTable">
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
            // Koneksi ke database
            $conn = new mysqli('localhost', 'root', '', 'moris_bot');

            // Periksa koneksi
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Query untuk mengambil data dengan status 'Close'
            $sql = "SELECT * FROM orders WHERE Status = 'Close' ORDER BY No DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $no = 1; // Nomor urut
                while ($row = $result->fetch_assoc()) {
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

            $conn->close();
            ?>
        </tbody>
    </table>
</div>

<script src="./js/sidebar.js"></script>

</body>
</html>