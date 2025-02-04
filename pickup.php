<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pickup</title>
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
        .btn_close {
            padding: 5px 10px;
            background: #B82132;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .btn_close:hover {
            background: #D2665A;
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
    <h1 class="headtitle">Pick UP Menu</h1>
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
            session_start();
            require "../config/Database.php";

            // Memastikan pengguna sudah login
            if (!isset($_SESSION['user_id'])) {
                header("Location: login.php");
                exit();
            }

            // Ambil ID pengguna yang sedang aktif
            $user_id = $_SESSION['user_id'];

            if (isset($_POST['close_tiket'])) {
                $pickup_tiket = $_POST['close_tiket'];

                try {
                    // Memulai transaksi untuk memastikan atomicity
                    $pdo->beginTransaction();

                    // Lock baris yang akan diupdate dengan `FOR UPDATE`
                    $sql_lock = "SELECT * FROM orders WHERE No_Tiket = ? FOR UPDATE";
                    $stmt_lock = $pdo->prepare($sql_lock);
                    $stmt_lock->bindParam(1, $pickup_tiket, PDO::PARAM_STR);
                    $stmt_lock->execute();

                    // Query untuk memperbarui status menjadi 'Close'
                    $sql_update = "UPDATE orders SET Status = 'Close' WHERE No_Tiket = ?";
                    $stmt_update = $pdo->prepare($sql_update);
                    $stmt_update->bindParam(1, $pickup_tiket, PDO::PARAM_STR);
                    $stmt_update->execute();

                    // Simpan aktivitas ke dalam tabel order_activity
                    $sql_activity = "INSERT INTO order_activity (no_tiket, user_id, activity_type) VALUES (?, ?, 'Close')";
                    $stmt_activity = $pdo->prepare($sql_activity);
                    $stmt_activity->bindParam(1, $pickup_tiket, PDO::PARAM_STR);
                    $stmt_activity->bindParam(2, $user_id, PDO::PARAM_INT);
                    $stmt_activity->execute();

                    // Commit transaksi
                    $pdo->commit();

                    echo "<p>Status No Tiket $pickup_tiket berhasil di Resolved dan aktivitas tercatat.</p>";
                } catch (PDOException $e) {
                    // Rollback jika terjadi error
                    $pdo->rollBack();
                    echo "Error: " . $e->getMessage();
                }
            }

            // Query untuk mengambil data dengan status 'Pickup'
            $sql = "SELECT * FROM orders WHERE Status = 'Pickup'";
            $stmt = $pdo->query($sql);

            if ($stmt->rowCount() > 0) {
                $no = 1; // Nomor urut
                foreach ($stmt as $row) {
                    echo "<tr>
                        <td>{$no}</td>
                        <td>{$row['Order_ID']}</td>
                        <td>{$row['Transaksi']}</td>
                        <td>{$row['Keterangan']}</td>
                        <td>{$row['No_Tiket']}</td>
                        <td>
                            <form method='POST'>
                                <input type='hidden' name='close_tiket' value='{$row['No_Tiket']}'>
                                <button type='submit' class='btn_close'>Close</button>
                            </form>
                        </td>
                    </tr>";
                    $no++;
                }
            } else {
                echo "<tr><td colspan='6'>No data available</td></tr>";
            }

            // Tidak perlu menutup koneksi secara eksplisit dengan $pdo karena PDO otomatis menutup koneksi saat script selesai dieksekusi.
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
