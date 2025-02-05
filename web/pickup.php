<?php
ob_start(); // Menyalakan output buffering
session_start();
require "../config/Database.php";

// Memastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style/style.css">
    <title>Pickup</title>
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
        <h1 class="headtitle">PickUP Menu</h1>
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
                                        <td>" . nl2br(htmlspecialchars($row['Keterangan'])) . "</td>
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

        <script src="./js/keterangan.js"></script>
        <script src="./js/sidebar.js"></script>
        <script src="./js/datatable.js"></script>
    </body>
    </html>
