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
</div>

<div class="content" id="content">
    <div class="navbar">
        <button id="toggleSidebar">☰</button>
        <a href="home.php" class="home-icon"><i class="fas fa-home"></i></a>
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
    <div class="container">
        <h1>📌 Petunjuk Penggunaan</h1>

        <section>
            <h2>1. Apa itu Moris Bot?</h2>
            <p>Moris Bot adalah sistem otomatisasi yang membantu pengguna dalam mengelola data, melacak order, dan menjalankan berbagai tugas administratif secara efisien.</p>
        </section>

        <section>
            <h2>2. Fitur Utama</h2>
            <ul>
                <li>✅ <strong>Manajemen Data</strong>: Menampilkan, mencari, dan memfilter data dengan mudah.</li>
                <li>✅ <strong>Tracking Order</strong>: Melacak dan mengelola status tiket atau pesanan.</li>
                <li>✅ <strong>Notifikasi & Reminder</strong>: Mengingatkan tugas atau jadwal penting.</li>
                <li>✅ <strong>Otomasi Tugas</strong>: Update status, generate laporan, atau pengolahan data otomatis.</li>
            </ul>
        </section>

        <section>
            <h2>3. Cara Menggunakan</h2>
            <p><strong>🔹 Navigasi Menu:</strong></p>
            <ul>
                <li><strong>Dashboard</strong>: Ringkasan informasi penting mengenai productivity dan progres order.</li>
                <li><strong>Order</strong>: Mengelola pesanan atau tiket layanan.</li>
                <li><strong>Pickup</strong>: Mengelola pesanan atau tiket layanan.</li>
                <li><strong>Close</strong>: Mengelola pesanan atau tiket layanan.</li>
                <li><strong>Laporan</strong>: Mengunduh laporan aktivitas atau transaksi.</li>
            </ul>
            <p><strong>🔹 Interaksi dengan Bot:</strong></p>
            <ul>
                <li>Ketik <code>"Cari Order #ID"</code> untuk melihat detail pesanan.</li>
                <li>Ketik <code>"Tampilkan laporan hari ini"</code> untuk melihat aktivitas terbaru.</li>
                <li>Gunakan tombol aksi seperti <button class="action-btn">Pickup</button>, <button class="action-btn">Cancel</button>, atau <button class="action-btn">Reply</button> untuk merespons tiket.</li>
            </ul>
        </section>

        <section>
            <h2>4. FAQ (Pertanyaan Umum)</h2>
            <p><strong>❓ Bagaimana cara login?</strong><br>➡ Gunakan akun yang sudah terdaftar di sistem.</p>
            <p><strong>❓ Bagaimana jika ada error saat input data?</strong><br>➡ Cek kembali format data, jika masih bermasalah hubungi admin.</p>
            <p><strong>❓ Apakah bisa diakses dari HP?</strong><br>➡ Ya, Moris Bot sudah mendukung tampilan responsif untuk mobile.</p>
        </section>

        <section>
            <h2>5. Hubungi Admin</h2>
            <p>Jika mengalami kendala, hubungi <strong>Admin IT</strong> melalui email atau WhatsApp.</p>
        </section>
    </div>
</div>

<script src="./js/sidebar.js"></script>
<script src="./js/profile.js"></script>

</body>
</html>