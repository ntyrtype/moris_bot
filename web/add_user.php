<?php
// Memulai sesi untuk menyimpan data login pengguna
session_start();

require "../config/Database.php"; // Memuat konfigurasi database

// Cek apakah pengguna sudah login dan memiliki role admin
if (!isset($_SESSION['role'])) {
  // Jika belum login, arahkan kembali ke halaman login
  echo "<script>alert('Anda harus login terlebih dahulu!'); window.location.href='index.php';</script>";
  exit();
}

// Cek apakah role pengguna adalah 'admin'
if ($_SESSION['role'] !== 'admin') {
  // Jika bukan admin, tolak akses ke halaman ini
  echo "<script>alert('Anda tidak memiliki akses untuk menambahkan user!'); window.location.href='dashboard.php';</script>";
  exit();
}

// Proses form ketika data dikirim dengan metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form dan hilangkan spasi berlebih
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = hash('sha256', $_POST['password']); // Enkripsi password
    $role = $_POST['role']; // Role dari dropdown
    $status = 'active'; // Default status

    try {
        // Query untuk menambahkan user baru ke tabel users
        $stmt = $pdo->prepare("INSERT INTO users (Nama, Username, Password, role, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nama, $username, $password, $role, $status]);

         // Tampilkan pesan sukses dan arahkan ke halaman login/index
        echo "<script>alert('User berhasil ditambahkan!'); window.location.href='index.php';</script>";
    } catch (PDOException $e) {
        // Jika terjadi error saat insert, tampilkan pesan error
        echo "<script>alert('Gagal menambahkan user: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ADD USER</title>
  <!-- Memuat tema bootstrap dari Bootswatch -->
  <link href="https://stackpath.bootstrapcdn.com/bootswatch/4.4.1/cosmo/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Gaya latar belakang halaman */
    body{
      background-color: #2c3e50;
    }
    /* Gaya kontainer utama */
    .wrapper { 
      font-family:'Poppins', sans-serif;
      width: 500px; 
      padding: 30px; 
      margin: auto;
      margin-top: 50px;
      display: flex;
      flex-direction: column;
      text-align: center;
      background-color: #EEEEEE;
      border-radius: 0.5rem;
      position: relative;
    }
    /* Tombol close untuk kembali ke halaman sebelumnya */
    .close-btn {
      position: absolute;
      top: 5px;
      right: 10px;
      font-size: 30px;
      color: #333;
      cursor: pointer;
      background: none;
      border: none;
    }
    /* Gaya judul */
    .wrapper h2 {
      padding: 10px;
      margin-bottom: 10px;
      font-weight: bold;
      text-align: center;
    }
    /* Gaya paragraf */
    .wrapper p {
      padding: 8px;
      margin-bottom: 2px;
    }
     /* Gaya untuk pesan error (jika nanti ingin ditambahkan validasi) */
    .wrapper form .form-group span {color: red;}
    .form-group{
      margin-bottom: 20px;
      padding: 10px;
    }
    /* Gaya tombol */
    .btn{
      border-color: #2c3e50;
    }
    .btn-customs{
      color: #2c3e50;
    }
    .btn:hover{
      background: #2c3e50;
      border-color: #2c3e50;
    }
  </style>
</head>
<body>
  <main>
    <section class="container wrapper">
      <!-- Tombol close untuk kembali ke halaman sebelumnya -->
      <button class="close-btn" onclick="window.history.back();">&times;</button>

      <!-- Judul halaman -->
      <h2 class="display-4 pt-3">Tambah User</h2>
      <p class="text-center">Isi inputan di bawah untuk menambahkan user baru</p>
      
      <!-- Form tambah user -->
      <form action="" method="POST"> 
        <!-- Input nama -->
        <div class="form-group">
          <input type="text" name="nama" id="name" class="form-control" placeholder="Name" required >
        </div>

        <!-- Input username -->
        <div class="form-group">
          <input type="text" name="username" id="username" class="form-control" placeholder="Username" required >
        </div>

        <!-- Input password -->
        <div class="form-group">
          <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
        </div>

        <!-- Dropdown pilihan role -->
        <div class="form-group">
          <select name="role" class="form-control" required>
            <option value="helpdesk">Helpdesk</option>
            <option value="admin">Admin</option>
          </select>
        </div>

        <!-- Tombol submit form -->
        <div class="form-group">
          <input type="submit" class="btn btn-block btn-outline-primary btn-customs" value="Tambah User">
        </div>
      </form>
    </section>
  </main>
</body>
</html>
