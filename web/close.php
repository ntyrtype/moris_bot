<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Close</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .sidebar {
            width: 200px;
            background: #2c3e50;
            color: #fff;
            position: fixed;
            height: 100%;
            padding-top: 20px;
        }
        .sidebar a {
            display: block;
            color: #fff;
            padding: 15px;
            text-decoration: none;
        }
        .sidebar a:hover {
            background: #34495e;
        }
        .content {
            margin-left: 200px;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        table th {
            background: #34495e;
            color: white;
        }
        .btn {
            padding: 5px 10px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .btn:hover {
            background: #2980b9;
        }
        .Dashboard {
            margin-left: 10px;
            color: #fff;
            text-decoration: none;
        }
        .headtitle {
            color:#2c3e50
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2 style="text-align: center;">MORIS BOT</h2>
    <p class = "Dashboard" >Dashboard</p>
    <a href="order.php">Order</a>
    <a href="pickup.php">Pick Up</a>
    <a href="close.php">Close</a>
</div>

<div class="content">
    <h1 class="headtitle">Close Menu</h1>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Order ID</th>
                <th>Transaksi</th>
                <th class = "Keterangan">Keterangan</th>
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

            // Query untuk mengambil data dengan status 'Order'
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
                echo "<tr><td colspan='6'>No data available</td></tr>";
            }

            $conn->close();
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
