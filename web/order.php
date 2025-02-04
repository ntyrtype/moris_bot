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
                <th>Kontak</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            session_start();
            require "../config/Database.php";

            // Memastikan pengguna sudah login
            if (!isset($_SESSION['user_id'])) {
                header("Location: login.php");
                exit();
            }

            // Ambil ID pengguna yang sedang aktif
            $user_id = $_SESSION['user_id'];

            if (isset($_POST['pickup_tiket'])) {
                $pickup_tiket = $_POST['pickup_tiket'];

                try {
                    // Memulai transaksi untuk memastikan atomicity
                    $pdo->beginTransaction();

                    // Lock baris yang akan diupdate dengan `FOR UPDATE`
                    $sql_lock = "SELECT * FROM orders WHERE No_Tiket = ? FOR UPDATE";
                    $stmt_lock = $pdo->prepare($sql_lock);
                    $stmt_lock->bindParam(1, $pickup_tiket, PDO::PARAM_STR);
                    $stmt_lock->execute();

                    // Query untuk memperbarui status menjadi 'Pick Up'
                    $sql_update = "UPDATE orders SET Status = 'Pickup' WHERE No_Tiket = ?";
                    $stmt_update = $pdo->prepare($sql_update);
                    $stmt_update->bindParam(1, $pickup_tiket, PDO::PARAM_STR);
                    $stmt_update->execute();

                    // Simpan aktivitas ke dalam tabel order_activity
                    $sql_activity = "INSERT INTO order_activity (no_tiket, user_id, activity_type) VALUES (?, ?, 'Pickup')";
                    $stmt_activity = $pdo->prepare($sql_activity);
                    $stmt_activity->bindParam(1, $pickup_tiket, PDO::PARAM_STR);
                    $stmt_activity->bindParam(2, $user_id, PDO::PARAM_INT);
                    $stmt_activity->execute();

                    // Commit transaksi
                    $pdo->commit();

                    echo "<p>Status No Tiket $pickup_tiket berhasil diperbarui ke 'Pick Up' dan aktivitas tercatat.</p>";
                } catch (PDOException $e) {
                    // Rollback jika terjadi error
                    $pdo->rollBack();
                    echo "Error: " . $e->getMessage();
                }
            }

            // Query untuk mengambil data dengan status 'Order'
            $sql = "SELECT * FROM orders WHERE Status = 'Order'";
            $stmt = $pdo->query($sql);

            // Menampilkan data order
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
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
