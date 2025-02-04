<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order</title>
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
        .btn_PickUp {
            padding: 5px 10px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .btn_PickUp:hover {
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
    <p class="Dashboard">Dashboard</p>
    <a href="order.php">Order</a>
    <a href="pickup.php">Pick Up</a>
    <a href="close.php">Close</a>
</div>

<div class="content">
    <h1 class="headtitle">Order Menu</h1>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Order ID</th>
                <th>Transaksi</th>
                <th>Keterangan</th>
                <th>No Tiket</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $conn = new mysqli('localhost', 'root', '', 'moris_bot');

             // Periksa koneksi
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Periksa jika ada data POST untuk update status
            if (isset($_POST['pickup_tiket'])) {
                $pickup_tiket = $_POST['pickup_tiket'];

                // Query untuk memperbarui status menjadi 'Pick Up'
                $sql_update = "UPDATE orders SET Status = 'Pickup' WHERE No_Tiket = ?";
                $stmt = $conn->prepare($sql_update);
                $stmt->bind_param("s", $pickup_tiket); // 's' untuk string
                $stmt->execute();
                echo "<p>Status No Tiket $pickup_tiket berhasil diperbarui ke 'Pick Up'</p>";
            }

            // Query untuk mengambil data dengan status 'Order'
            $sql = "SELECT * FROM orders WHERE Status = 'Order'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $no = 1; // Nomor urut
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$no}</td>
                        <td>{$row['Order_ID']}</td>
                        <td>{$row['Transaksi']}</td>
                        <td>{$row['Keterangan']}</td>
                        <td>{$row['No_Tiket']}</td>
                        <td>
                            <form method='POST'>
                                <input type='hidden' name='pickup_tiket' value='{$row['No_Tiket']}'>
                                <button type='submit' class='btn_PickUp'>PICK UP</button>
                            </form>
                        </td>
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
