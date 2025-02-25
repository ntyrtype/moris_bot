<?php
session_start();
require "../config/Database.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Unauthorized"]);
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Home</title>
</head>
<body>

<div class="sidebar" id="sidebar">
    <h1>MORIS BOT</h1>
    <a href="dashboard.php">Dashboard</a>
    <a href="order.php">Order</a>
    <a href="pickup.php">PickUp</a>
    <a href="close.php">Close</a>
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
    <div class="container">
        <h1>📌 Petunjuk Penggunaan</h1>

        <section>
            <h2>1. Apa itu Moris Bot?</h2>
            <p>Moris Bot adalah sistem otomatisasi yang membantu pengguna dalam mengelola data, melacak order, dan menjalankan berbagai tugas administratif secara efisien.</p>
        </section>

        <section>
            <h2>2. Fitur Utama</h2>
            <ul>
                <li>✅ <strong>Manajemen Pengguna</strong>: Role Admin, Helpdesk, Teknis, Plasa.</li>
                <li>✅ <strong>Manajemen Order</strong>: Tracking Pembaruan Status.</li>
                <li>✅ <strong>Log Aktivitas</strong>: Merekam perubahan status setiap pesanan.</li>
                <li>✅ <strong>Integrasi Telegram</strong>: Notifikasi otomatis untuk perubahan status.</li>
            </ul>
        </section>

        <section>
            <h2>3. Cara Menggunakan</h2>
            <p><strong>🔹 Navigasi Menu:</strong></p>
            <ul>
                <li><strong>Dashboard</strong>: Ringkasan informasi penting mengenai productivity dan progres order.</li>
                <li><strong>Order</strong>: Melihat dan mengelola data pesanan dari bot telegram yang dapat memantau status setiap order dan mengambil tindakan seperti memproses atau membatalkan pesanan.</li>
                <li><strong>Pickup</strong>: Menampilkan data pesanan yang sudah di pickup dari order yang sedang dalam proses pengerjaan dan memerlukan pembaruan status untuk memantau perkembangan.</li>
                <li><strong>Close</strong>: Menampilkan daftar pesanan yang sudah diselesaikan. Semua order yang ada di halaman ini memiliki status final, artinya tidak memerlukan tindakan lanjutan selain pencatatan dan monitoring.</li>
                <li><strong>Nama Pengguna</strong>: Pengguna yang sedang login jika di klik akan menampilkan fitur tambah user hanya untuk admin dan logout untuk semua pengguna.</li>
            </ul>
            <p><strong>🔹 Interaksi dengan Bot:</strong></p>
            <ul>
                <li>Ketik <code>"/help"</code> untuk melihat template /moban.</li>
                <li>Ketik <code>"/moban"</code> untuk mengorder.</li>
                <li>Gunakan tombol aksi seperti <button class="action-btn">Pickup</button>, <button class="action-btn">Cancel</button>, atau <button class="action-btn">Reply</button> untuk merespons tiket.</li>
            </ul>
        </section>
    </div>
</div>

<script src="./js/sidebar.js"></script>
<script src="./js/profile.js"></script>

</body>
</html>