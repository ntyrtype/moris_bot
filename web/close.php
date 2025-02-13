<?php 
session_start();
require "../config/Database.php";

// Memastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$transaksi = filter_input(INPUT_GET, 'transaksi', FILTER_SANITIZE_STRING) ?? '';
$kategori = filter_input(INPUT_GET, 'kategori', FILTER_SANITIZE_STRING) ?? '';
$filter_date = filter_input(INPUT_GET, 'filter_date', FILTER_SANITIZE_STRING) ?? '';

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
        o.Status AS status
    FROM 
        orders o
    LEFT JOIN 
        users u ON o.id_telegram = u.id_telegram
    WHERE 
        o.Status = 'Close'
";

// Tambahkan kondisi filter ke query
if ($transaksi) {
    $query .= " AND o.Transaksi = :transaksi";
}
if ($kategori) {
    $query .= " AND o.Kategori = :kategori";
}
if ($filter_date) {
    $query .= " AND o.tanggal = :filter_date";
}

// Eksekusi query
$stmt = $pdo->prepare($query);

if ($transaksi) {
    $stmt->bindParam(":transaksi", $transaksi, PDO::PARAM_STR);
}
if ($kategori) {
    $stmt->bindParam(":kategori", $kategori, PDO::PARAM_STR);
}
if ($filter_date) {
    $stmt->bindParam(":filter_date", $filter_date, PDO::PARAM_STR);
}

$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style/style.css">
    <title>Order</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
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
        <a href="">Plasa</a>
        <p>|</p>
        <a href="">Teknisi</a>
        <div class="profile-dropdown">
            <button id="profileButton"><?php echo htmlspecialchars($_SESSION['nama']); ?></button>
            <div class="profile-content" id="profileContent">
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="add_user.php">Tambah User</a>
                <?php endif; ?>
                <form action="logout.php" method="POST">
                    <button type="submit" class="logout-btn" style="width: 100%; border: none; background: none; text-align: left;">Logout</button>
                </form>
            </div>
        </div>
    </div>
    <h1 class="headtitle">Close Menu</h1>
    <div class="filter">
        <form action="" method="GET">
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
                <option value="Indibiz" <?= ($kategori === 'Indibiz') ? 'selected' : '' ?>>Indibiz</option>
                <option value="Indihome" <?= ($kategori === 'Indihome') ? 'selected' : '' ?>>Indihome</option>
                <option value="Datin" <?= ($kategori === 'Datin') ? 'selected' : '' ?>>Datin</option>
                <option value="WMS" <?= ($kategori === 'WMS') ? 'selected' : '' ?>>WMS</option>
                <option value="OLO" <?= ($kategori === 'OLO') ? 'selected' : '' ?>>OLO</option>
            </select>

            <input type="date" name="filter_date" id="filter_date" value="<?= htmlspecialchars($filter_date) ?>">
            <button type="submit">Filter</button>
        </form>
    </div>
    <table id="dataTable">
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
                <th>Status</th>
                <th>Log</th>
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
                    <td>
                        <div class="text-container">
                            <?php
                            $text = nl2br(htmlspecialchars($order['Keterangan']));
                            $shortText = substr($text, 0, 80); // Ambil 80 karakter pertama
                            ?>
                            <span class="short-text"><?= $shortText ?>...</span>
                            <span class="hidden-text" style="display: none;"><?= $text ?></span>
                            <button class="show-more">Show More</button>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($order['no_tiket']) ?></td>
                    <td>
                        <a href="https://t.me/<?= htmlspecialchars($order['username_telegram']) ?>" target="_blank">
                            <?= htmlspecialchars($order['nama']) ?>
                        </a>
                    </td>
                    <td>
                    <?= htmlspecialchars($order['status']) ?>
                    </td>
                    <td>link</td>
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

<script src="./js/sidebar.js"></script>
<script src="./js/profile.js"></script>
<script src="./js/datatable.js"></script>
<script src="./js/showmore.js"></script>
<script src="./js/cancel.js"></script>

</body>
</html>