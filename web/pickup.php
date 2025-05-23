<?php 
// Inisialisasi session dan koneksi database
session_start();
require "../config/Database.php";

// Memastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Menangani aksi Reply
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_tiket = htmlspecialchars(trim($_POST['close_tiket'] ?? ''), ENT_QUOTES, 'UTF-8');
    $status = htmlspecialchars(trim($_POST['status'] ?? ''), ENT_QUOTES, 'UTF-8');
    $keterangan = htmlspecialchars(trim($_POST['keterangan'] ?? ''), ENT_QUOTES, 'UTF-8');
    
    try {
        $pdo->beginTransaction();

        // Tentukan status dan progress_order berdasarkan input
        if ($status === 'Sudah PS' || $status === 'CAINPUL') {
            $new_status = 'Close';
            $new_progress = $status;
        } else {
            $new_status = 'Pickup';
            $new_progress = $status;
        }

        // Update status dan progress_order di tabel orders
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

        $pdo->commit();
        // berikan notifikasi jika sukses
        $_SESSION['message'] = "Order berhasil diupdate.";
        header("Location: pickup.php"); // Redirect untuk menghindari resubmit form
        exit();

    } catch (PDOException $e) {
        // rollback jika error
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}

// Query untuk mengambil data order
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
        lo.nama AS Provi,
        o.tanggal AS tanggal,
        o.Status AS status,
        lo.Status AS log_status,
        o.order_by AS order_by,
        o.progress_order AS progress
    FROM
        orders o
    LEFT JOIN users u ON o.id_telegram = u.id_telegram
    LEFT JOIN (
        SELECT lo1.*
        FROM log_orders lo1
        INNER JOIN (
            SELECT Order_ID, MAX(no) AS max_no
            FROM log_orders
            GROUP BY Order_ID
        ) lo2 ON lo1.Order_ID = lo2.Order_ID AND lo1.no = lo2.max_no
    ) lo ON o.Order_ID = lo.Order_ID
    WHERE
        o.Status = 'Pickup';
";

// Tambahkan filter jika ada input order_by, transaksi, kategori, dan tanggal
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

// urutkan berdasarkan tanggal terlama
$query .= " ORDER BY o.tanggal DESC";

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
    <!-- <meta http-equiv="refresh" content="60"> -->
    <link rel="stylesheet" href="./style/style.css">
    <title>Pickup</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
</head>
<body>

    <!-- sidebar -->
    <div class="sidebar" id="sidebar">
        <h1>MORIS BOT</h1>
        <a href="dashboard.php">Dashboard</a>
        <a href="order.php">Order</a>
        <a href="pickup.php">PickUp</a>
        <a href="close.php">Close</a>
        <!-- jika admin tampilkan log -->
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="log.php">Log</a>
        <?php endif; ?>
    </div>

    <div class="content" id="content">
        <div class="navbar">
            <button id="toggleSidebar">☰</button>
            <a href="home.php" class="home-icon"><i class="fas fa-home"></i></a>
            <div class="profile-dropdown">
                <button id="profileButton"><?php echo htmlspecialchars($_SESSION['nama']); ?></button>
                <div class="profile-content" id="profileContent">
                    <!-- jika admin ampilkan tools admin dan tambah user -->
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="add_user.php">Tambah User</a>
                        <a href="admin.php">Tools</a>
                    <?php endif; ?>
                    <a href="reset_password.php">Reset Password</a>
                    <form action="logout.php" method="POST">
                        <button type="submit" class="logout-btn" style="width: 100%; border: none; background: none; text-align: left;">Logout</button>
                    </form>
                </div>
            </div>
        </div>
        <h1 class="headtitle">Pickup Menu</h1>
        <!-- seasson jika berhasil pickup maka akan memberi notif -->
        <?php if (isset($_SESSION['message'])): ?>
        <div class="notification" style="margin: 20px 20px 20px 10px;">
            <?= htmlspecialchars($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message']); // Hapus pesan setelah ditampilkan ?>
        <?php endif; ?>
        <div class="filter">
            <form action="" method="GET">
                <!-- filter by order -->
                <select aria-label="order_by" name="order_by" id="order_by">
                    <option value="">All</option>
                    <option value="Plasa" <?= ($order_by === 'Plasa') ? 'selected' : '' ?>>PLASA</option>
                    <option value="Teknisi" <?= ($order_by === 'Teknisi') ? 'selected' : '' ?>>TEKNISI</option>
                </select>

                <!-- filter by transaksi -->
                <select aria-label="transaksi" name="transaksi" id="transaksi">
                    <option value="">All Transaksi</option>
                    <option value="PDA" <?= ($transaksi === 'PDA') ? 'selected' : '' ?>>PDA</option>
                    <option value="MO" <?= ($transaksi === 'MO') ? 'selected' : '' ?>>MO</option>
                    <option value="ORBIT" <?= ($transaksi === 'ORBIT') ? 'selected' : '' ?>>ORBIT</option>
                    <option value="FFG" <?= ($transaksi === 'FFG') ? 'selected' : '' ?>>FFG</option>
                    <option value="UNSPEk" <?= ($transaksi === 'UNSPEk') ? 'selected' : '' ?>>UNSPEK</option>
                    <option value="PSB" <?= ($transaksi === 'PSB') ? 'selected' : '' ?>>PSB</option>
                    <option value="RO" <?= ($transaksi === 'RO') ? 'selected' : '' ?>>RO</option>
                    <option value="SO" <?= ($transaksi === 'SO') ? 'selected' : '' ?>>SO</option>
                    <option value="DO" <?= ($transaksi === 'DO') ? 'selected' : '' ?>>DO</option>
                </select>

                <!-- filter by kategori -->
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

                <!-- filter by date -->
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
                    <!-- kolom tabel -->
                    <tr>
                        <th>No</th>
                        <th>Order ID</th>
                        <th>Kategori</th>
                        <th>Transaksi</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>No Tiket</th>
                        <th>Nama</th>
                        <th>Provi</th>
                        <th>Progress</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- looping untuk menampilkan data -->
                    <?php if (!empty($orders)): ?>
                        <?php $no = 1; ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= $no ?></td>
                                <td><?= htmlspecialchars($order['order_id']) ?></td>
                                <td><?= htmlspecialchars($order['kategori']) ?></td>
                                <td><?= htmlspecialchars($order['transaksi']) ?></td>
                                <td><?= htmlspecialchars($order['tanggal']) ?></td>
                                <!-- jika keteranngan panjang maka akan di showmore -->
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
                                    <!-- linked telegram by id telegram di database -->
                                    <a href="https://t.me/<?= htmlspecialchars($order['username_telegram']) ?>" target="_blank">
                                        <?= htmlspecialchars($order['nama']) ?>
                                    </a>
                                <td><?= htmlspecialchars($order['Provi']) ?></td>
                                <td><?= htmlspecialchars($order['progress']) ?></td>
                                <td>
                                    <!-- memmbuat reply jika close -->
                                    <button onclick="openModal('<?php echo htmlspecialchars($order['no_tiket'], ENT_QUOTES, 'UTF-8'); ?>')">Reply</button>
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
        <!-- btn download -->
        <button id="downloadButton" class="download-btn">Download Excel</button>
    </div>

    <!-- modal ketika close -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Masukkan Keterangan</h2>
            <form method="POST">
                <label for="status">Status:</label>
                <select name="status" id="status" required>
                    <option value="" disabled selected>Pilih Status</option>
                    <option value="Sudah PS">Sudah PS</option>
                    <option value="CAINPUL">CAINPUL</option>
                    <option value="On Eskalasi">On Eskalasi</option>
                </select>
                <br><br>

                <label for="keterangan">Keterangan:</label>
                <textarea name="keterangan" rows="4" cols="50" required></textarea><br><br>
                <input type="hidden" id="pickup_tiket" name="close_tiket">
                <button type="submit" class="btn_close">Submit</button>
            </form>
        </div>
    </div>

<!-- memanggil scripts js -->
<script src="./js/sidebar.js"></script> <!-- memanggil sidebar -->
<script src="./js/profile.js"></script> <!-- memanggil profile -->
<script src="./js/datatable.js"></script> <!-- memanggil datatable -->
<script src="./js/showmore.js"></script> <!-- memanggil showmore -->
<script src="./js/keterangan.js"></script> <!-- memanggil keterangan -->
<!-- <script src="./js/download.js"></script> -->

</body>
</html>