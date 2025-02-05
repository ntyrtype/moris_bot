<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pickup</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
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
            color:#2c3e50;
        }
        /* Styling the Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }
        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
        }
        .close {
            padding: 5px 10px;
            background: #B82132;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .close:hover {
            background: #D2665A;
        }
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        button {
            padding: 5px 10px;
            background: #B82132;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        button:hover {
            background: #D2665A;
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
                    session_start();
                    require "../config/Database.php";

                    // Memastikan pengguna sudah login
                    if (!isset($_SESSION['user_id'])) {
                        header("Location: login.php");
                        exit();
                    }

                    // Ambil ID pengguna yang sedang aktif
                    $user_id = $_SESSION['user_id'];

                    // Cek apakah form submit telah dilakukan untuk menutup tiket
                    if (isset($_POST['close_tiket'])) {
                        $pickup_tiket = $_POST['close_tiket'];
                        $keterangan = $_POST['keterangan'];  // Ambil keterangan yang diinput

                        if (empty($keterangan)) {
                            echo "<script>alert('Keterangan harus diisi!');</script>";
                        } else {
                            try {
                                // Memulai transaksi untuk memastikan atomicity
                                $pdo->beginTransaction();

                                // Query untuk memperbarui status menjadi 'Close' dan menyimpan keterangan
                                $sql_update = "UPDATE orders SET Status = 'Close', ket_validasi = ? WHERE No_Tiket = ?";
                                $stmt_update = $pdo->prepare($sql_update);
                                $stmt_update->bindParam(1, $keterangan, PDO::PARAM_STR);
                                $stmt_update->bindParam(2, $pickup_tiket, PDO::PARAM_STR);
                                $stmt_update->execute();

                                // Simpan aktivitas ke dalam tabel order_activity
                                $sql_activity = "INSERT INTO order_activity (no_tiket, user_id, activity_type) VALUES (?, ?, 'Close')";
                                $stmt_activity = $pdo->prepare($sql_activity);
                                $stmt_activity->bindParam(1, $pickup_tiket, PDO::PARAM_STR);
                                $stmt_activity->bindParam(2, $user_id, PDO::PARAM_INT);
                                $stmt_activity->execute();

                                // Commit transaksi
                                $pdo->commit();

                                echo "<p>Status No Tiket $pickup_tiket berhasil di Resolved dengan keterangan: $keterangan dan aktivitas tercatat.</p>";
                            } catch (PDOException $e) {
                                // Rollback jika terjadi error
                                $pdo->rollBack();
                                echo "Error: " . $e->getMessage();
                            }
                        }
                    }

                    // Query untuk mengambil data dengan status 'Pickup'
                    $sql = "SELECT * FROM orders WHERE Status = 'Pickup'";
                    $stmt = $pdo->query($sql);

                    if ($stmt->rowCount() > 0) {
                        $no = 1; // Nomor urut
                        foreach ($stmt as $row) {
                            $telegram_username = $row['username_telegram'];
                            $telegram_link = "https://t.me/{$telegram_username}";
                            echo "<tr>
                                    <td>{$no}</td>
                                    <td>{$row['Order_ID']}</td>
                                    <td>{$row['Transaksi']}</td>
                                    <td>{$row['Keterangan']}</td>
                                    <td>{$row['No_Tiket']}</td>
                                    <td><a href='{$telegram_link}' class='telegram-link' target='_blank'>Kontak</a></td>
                                    <td>
                                        <button onclick='openModal(\"{$row['No_Tiket']}\")'>Close</button>
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

    <!-- Modal for Keterangan -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Masukkan Keterangan</h2>
            <form method="POST">
                <textarea name="keterangan" rows="4" cols="50" required></textarea><br><br>
                <input type="hidden" id="pickup_tiket" name="close_tiket">
                <button type="submit" class="btn_close">Submit</button>
            </form>
        </div>
    </div>

    <script>
        // Open the modal
        function openModal(no_tiket) {
            document.getElementById('pickup_tiket').value = no_tiket;
            document.getElementById('modal').style.display = "block";
        }

        // Close the modal
        function closeModal() {
            document.getElementById('modal').style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == document.getElementById('modal')) {
                closeModal();
            }
        }

        $(document).ready(function() {
        $('#closeTable').DataTable();
        });
    </script>

</body>
</html>
