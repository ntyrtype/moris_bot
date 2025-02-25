<?php 
session_start();
require "../config/Database.php";

// Memastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Menangani aksi Pickup dan Cancel
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_tiket = htmlspecialchars(trim($_POST['no_tiket'] ?? ''), ENT_QUOTES, 'UTF-8');
    $action = htmlspecialchars(trim($_POST['action'] ?? ''), ENT_QUOTES, 'UTF-8');
    $keterangan = htmlspecialchars(trim($_POST['keterangan'] ?? ''), ENT_QUOTES, 'UTF-8');
    
    try {
        $pdo->beginTransaction();

        // Update status dan progress_order di tabel orders
        if ($action === 'pickup') {
            $new_status = 'Pickup';
            $new_progress = 'On Rekap';
            $_SESSION['message'] = "Order berhasil di pickup.";
        } elseif ($action === 'cancel') {
            $new_status = 'Close';
            $new_progress = 'Cancel';
            $_SESSION['message'] = "Order berhasil ke close.";
        }

        $sql_update_order = "
            UPDATE orders 
            SET Status = :status, progress_order = :progress_order 
            WHERE No_Tiket = :no_tiket
        ";
        $stmt_update_order = $pdo->prepare($sql_update_order);
        $stmt_update_order->bindParam(':status', $new_status, PDO::PARAM_STR);
        $stmt_update_order->bindParam(':progress_order', $new_progress, PDO::PARAM_STR);
        $stmt_update_order->bindParam(':no_tiket', $no_tiket, PDO::PARAM_STR);
        $stmt_update_order->execute();

        // Catat aktivitas di log_orders
        $sql_insert_log = "
            INSERT INTO log_orders (
                id_user, order_id, transaksi, Kategori, no_tiket, status, progress_order, keterangan, nama, role, order_by
            )
            SELECT 
                :id_user, o.Order_ID, o.Transaksi, o.Kategori, o.No_Tiket, :status, :progress_order, :keterangan, u.Nama, u.role, o.order_by
            FROM 
                orders o
            LEFT JOIN 
                users u ON u.ID = :id_user
            WHERE 
                o.No_Tiket = :no_tiket
        ";

        $stmt_insert_log = $pdo->prepare($sql_insert_log);
        $stmt_insert_log->bindParam(':id_user', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt_insert_log->bindParam(':status', $new_status, PDO::PARAM_STR);
        $stmt_insert_log->bindParam(':progress_order', $new_progress, PDO::PARAM_STR);
        $stmt_insert_log->bindParam(':keterangan', $keterangan, PDO::PARAM_STR);
        $stmt_insert_log->bindParam(':no_tiket', $no_tiket, PDO::PARAM_STR);
        $stmt_insert_log->execute();

        // Update id_telegram dan username_telegram di log_orders (trigger pertama)
        $sql_update_telegram = "
            UPDATE log_orders lo
            JOIN orders o ON lo.order_id = o.Order_ID
            SET lo.id_telegram = o.id_telegram, 
                lo.username_telegram = o.username_telegram
            WHERE lo.no_tiket = :no_tiket AND lo.id_telegram IS NULL;
        ";
        $stmt_update_telegram = $pdo->prepare($sql_update_telegram);
        $stmt_update_telegram->bindParam(':no_tiket', $no_tiket, PDO::PARAM_STR);
        $stmt_update_telegram->execute();

        // Update nama_order_by berdasarkan id_telegram (trigger kedua)
        $sql_update_nama_order_by = "
            UPDATE log_orders lo
            JOIN users u ON lo.id_telegram = u.id_telegram
            SET lo.nama_order_by = u.nama
            WHERE lo.no_tiket = :no_tiket;
        ";
        $stmt_update_nama_order_by = $pdo->prepare($sql_update_nama_order_by);
        $stmt_update_nama_order_by->bindParam(':no_tiket', $no_tiket, PDO::PARAM_STR);
        $stmt_update_nama_order_by->execute();


        $pdo->commit();
        header("Location: order.php"); // Redirect untuk menghindari resubmit form
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}

$transaksi = htmlspecialchars(trim($_GET['transaksi'] ?? ''), ENT_QUOTES, 'UTF-8');
$kategori = htmlspecialchars(trim($_GET['kategori'] ?? ''), ENT_QUOTES, 'UTF-8');
$start_date = htmlspecialchars(trim($_GET['start_date'] ?? ''), ENT_QUOTES, 'UTF-8');
$end_date = htmlspecialchars(trim($_GET['end_date'] ?? ''), ENT_QUOTES, 'UTF-8');
$order_by = htmlspecialchars(trim($_GET['order_by'] ?? ''), ENT_QUOTES, 'UTF-8');

// Query untuk mengambil data order
$query = "
    SELECT 
        o.Order_ID AS order_id,
        o.Kategori AS kategori,
        o.Transaksi AS transaksi,
        o.Keterangan AS Keterangan,
        o.No_Tiket AS no_tiket,
        o.id_telegram AS id_telegram,
        o.username_telegram AS username_telegram,
        u.Nama AS nama,
        o.tanggal AS tanggal,
        o.Status AS status,
        o.order_by AS order_by
    FROM 
        orders o
    LEFT JOIN 
        users u ON o.id_telegram = u.id_telegram
    WHERE 
        o.Status = 'Order'
";

// Tambahkan filter jika ada input order_by
if ($order_by) {
    $query .= " AND o.order_by = :order_by";
}

if ($transaksi) {
    $query .= " AND o.Transaksi = :transaksi";
}
if ($kategori) {
    $query .= " AND o.Kategori = :kategori";
}
if (!empty($start_date) && !empty($end_date)) {
    $query .= " AND o.tanggal BETWEEN :start_date AND :end_date";
} elseif (!empty($start_date)) {
    $query .= " AND o.tanggal >= :start_date";
} elseif (!empty($end_date)) {
    $query .= " AND o.tanggal <= :end_date";
}

// Eksekusi query
$stmt = $pdo->prepare($query);

// Bind parameter jika ada
if ($order_by) {
    $stmt->bindParam(":order_by", $order_by, PDO::PARAM_STR);
}
if ($transaksi) {
    $stmt->bindParam(":transaksi", $transaksi, PDO::PARAM_STR);
}
if ($kategori) {
    $stmt->bindParam(":kategori", $kategori, PDO::PARAM_STR);
}
if (!empty($start_date) && !empty($end_date)) {
    $stmt->bindParam(":start_date", $start_date, PDO::PARAM_STR);
    $stmt->bindParam(":end_date", $end_date, PDO::PARAM_STR);
} elseif (!empty($start_date)) {
    $stmt->bindParam(":start_date", $start_date, PDO::PARAM_STR);
} elseif (!empty($end_date)) {
    $stmt->bindParam(":end_date", $end_date, PDO::PARAM_STR);
}

$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="60">
    <link rel="stylesheet" href="./style/style.css">
    <title>Order</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
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
            <div class="profile-dropdown">
                <button id="profileButton"><?php echo htmlspecialchars($_SESSION['nama']); ?></button>
                <div class="profile-content" id="profileContent">
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="add_user.php">Tambah User</a>
                    <?php endif; ?>
                    <a href="reset_password.php">Reset Password</a>
                    <form action="logout.php" method="POST">
                        <button type="submit" class="logout-btn" style="width: 100%; border: none; background: none; text-align: left;">Logout</button>
                    </form>
                </div>
            </div>
        </div>
        <h1 class="headtitle">Order Menu</h1>
        <?php if (isset($_SESSION['message'])): ?>
        <div class="notification" style="margin: 20px 20px 20px 10px;">
            <?= htmlspecialchars($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message']); // Hapus pesan setelah ditampilkan ?>
        <?php endif; ?>
        <div class="filter">
            <form action="" method="GET">
                <select aria-label="order_by" name="order_by" id="order_by">
                    <option value="">All</option>
                    <option value="Plasa" <?= ($order_by === 'Plasa') ? 'selected' : '' ?>>PLASA</option>
                    <option value="Teknisi" <?= ($order_by === 'Teknisi') ? 'selected' : '' ?>>TEKNISI</option>
                </select>

                <select aria-label="transaksi" name="transaksi" id="transaksi">
                    <option value="">All Transaksi</option>
                    <option value="PDA" <?= ($transaksi === 'PDA') ? 'selected' : '' ?>>PDA</option>
                    <option value="MO" <?= ($transaksi === 'MO') ? 'selected' : '' ?>>MO</option>
                    <option value="ORBIT" <?= ($transaksi === 'ORBIT') ? 'selected' : '' ?>>ORBIT</option>
                    <option value="FFG" <?= ($transaksi === 'FFG') ? 'selected' : '' ?>>FFG</option>
                    <option value="UNSPEk" <?= ($transaksi === 'UNSPEk') ? 'selected' : '' ?>>UNSPEK</option>
                </select>

                <select aria-label="kategori" name="kategori" id="kategori">
                    <option value="">All Kategori</option>
                    <option value="Indibiz">Indibiz</option>
                    <option value="Indihome">Indihome</option>
                    <option value="Wifiid">Wifi.id</option>
                    <option value="Astinet">Astinet</option>
                    <option value="Metro">Metro</option>
                    <option value="VPNIP">VPNIP</option>
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
        <div class="table-responsive">
            <table id="dataTable" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Order ID</th>
                        <th>Kategori</th>
                        <th>Transaksi</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>No Tiket</th>
                        <th>Nama</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($orders)): ?>
                    <?php $no = 1; ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $no ?></td>
                            <td><?= htmlspecialchars($order['order_id']) ?></td>
                            <td><?= htmlspecialchars($order['kategori']) ?></td>
                            <td><?= htmlspecialchars($order['transaksi']) ?></td>
                            <td><?= htmlspecialchars($order['tanggal']) ?></td>
                            <td class="text-container">
                                <?php
                                    $text = nl2br(htmlspecialchars($order['Keterangan']));
                                    $shortText = substr($text, 0, 80); // Ambil 80 karakter pertama
                                ?>
                                <div class="short-text"><?= $shortText ?>...</div>
                                <div class="hidden-text" style="display: none;"><?= $text ?></div>
                                <button class="show-more">Show More</button>
                            </td>
                            <td><?= htmlspecialchars($order['no_tiket']) ?></td>
                            <td>
                                <a href="https://t.me/<?= htmlspecialchars($order['username_telegram']) ?>" target="_blank">
                                    <?= htmlspecialchars($order['nama']) ?>
                                </a>
                            </td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="no_tiket" value="<?= htmlspecialchars($order['no_tiket']) ?>">
                                    <button type="submit" name="action" value="pickup" class="btn_PickUp">Pickup</button>
                                </form>

                                <form method="POST" id="cancelForm" style="display:inline;">
                                    <input type="hidden" name="no_tiket" value="<?= htmlspecialchars($order['no_tiket']) ?>">
                                    <input type="text" id="keterangan_cancel" name="keterangan" placeholder="Keterangan Cancel" style="display:none;">
                                    <button type="button" id="showKeteranganButton" class="btn_Cancel">Cancel</button>
                                    <button type="submit" name="action" value="cancel" id="submit_cancel" style="display:none;">Submit Cancel</button>
                                </form>
                            </td>
                        </tr>
                        <?php $no++; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">Tidak ada data order.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <button id="downloadButton" class="download-btn">Download Excel</button>
    </div>

<script src="./js/sidebar.js"></script>
<script src="./js/profile.js"></script>
<script src="./js/datatable.js"></script>
<script src="./js/showmore.js"></script>
<script src="./js/cancel.js"></script>
<script src="./js/download.js"></script>

</body>
</html>