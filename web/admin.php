<?php
session_start();
require "../config/Database.php"; // Memuat konfigurasi database

// Cek apakah pengguna sudah login dan memiliki role admin
if (!isset($_SESSION['role'])) {
  echo "<script>alert('Anda harus login terlebih dahulu!'); window.location.href='index.php';</script>";
  exit();
}

if ($_SESSION['role'] !== 'admin') {
  echo "<script>alert('Anda tidak memiliki akses untuk menambahkan user!'); window.location.href='dashboard.php';</script>";
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Tools</title>
  <link href="https://stackpath.bootstrapcdn.com/bootswatch/4.4.1/cosmo/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #2c3e50;
    }
    .wrapper {
      font-family: 'Poppins', sans-serif;
      width: 500px; /* Lebar wrapper disesuaikan */
      padding: 30px;
      margin: auto;
      margin-top: 50px;
      display: flex;
      flex-direction: column; /* Mengatur tata letak menjadi atas-bawah */
      background-color: #EEEEEE;
      border-radius: 0.5rem;
      position: relative;
    }
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
    .wrapper h2 {
      padding: 10px;
      margin-bottom: 10px;
      font-weight: bold;
      text-align: center;
    }
    .wrapper p {
      padding: 8px;
      margin-bottom: 2px;
    }
    .wrapper form .form-group span {
      color: red;
    }
    .form-group {
      margin-bottom: 20px;
      padding: 10px;
    }
    .btn {
      border-color: #2c3e50;
    }
    .btn-customs {
      color: #2c3e50;
    }
    .btn:hover {
      background: #2c3e50;
      border-color: #2c3e50;
    }
    .admin-section {
      width: 100%; /* Lebar penuh untuk setiap section */
      margin-bottom: 30px; /* Jarak antara dua section */
      background-color: #fff;
      padding: 20px;
      border-radius: 0.5rem;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .admin-section h3 {
      margin-bottom: 20px;
      text-align: center;
    }
  </style>
</head>
<body>
  <main>
    <section class="container wrapper">
      <button class="close-btn" onclick="window.history.back();">&times;</button>
      <h2 class="display-4 pt-3">Admin Tools</h2>
      
      <!-- Section Manajemen Kategori -->
      <div class="admin-section">
        <h3>Manajemen Kategori</h3>
        <form action="" method="POST">
          <div class="form-group">
            <input type="text" name="category_name" id="category_name" class="form-control" placeholder="Nama Kategori" required>
          </div>
          <div class="form-group">
            <input type="text" name="category_regex" id="category_regex" class="form-control" placeholder="Regex Pattern" required>
          </div>
          <div class="form-group">
            <input type="submit" class="btn btn-block btn-outline-primary btn-customs" value="Tambah Kategori">
          </div>
        </form>
      </div>

      <!-- Section Manajemen Group -->
      <div class="admin-section">
        <h3>Manajemen Group</h3>
        <form action="" method="POST">
          <div class="form-group">
            <input type="text" name="group_name" id="group_name" class="form-control" placeholder="Nama Group" required>
          </div>
          <div class="form-group">
            <input type="text" name="group_description" id="group_description" class="form-control" placeholder="Deskripsi Group" required>
          </div>
          <div class="form-group">
            <input type="submit" class="btn btn-block btn-outline-primary btn-customs" value="Tambah Group">
          </div>
        </form>
      </div>
    </section>
  </main>
</body>
</html>