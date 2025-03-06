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
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"> -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
  <style>
    body {
      background-color: #2c3e50;
    }
    .wrapper {
      font-family: 'Poppins', sans-serif;
      width: 900px; /* Lebar wrapper disesuaikan */
      padding: 30px;
      margin: 50px auto 50px auto;
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
    /* tabel */
    .table-responsive {
      background: #fff;
      margin: 10px;
      padding: 15px;
      z-index: 1;
      max-width: calc(100% - 10px);
      overflow-x: auto;
      border-radius: 8px; 
      box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); 
    }

    table {
      width: 100%;
      table-layout: fixed;
      border-collapse: collapse;
      white-space: nowrap; 
    }

    table th {
      border: 1px solid #ddd;
      background: #2c3e50; 
      color: white;
      font-size: 18px; 
      font-weight: bold;
      padding: 10px; 
      text-align: center;
    }

    table td {
      border: 1px solid #ddd;
      padding: 10px;
      font-size: 14px; 
      text-align: left;
      background: #f9f9f9; 
      max-width: 100%
    }

    table td {
      border: 1px solid #ddd;
      padding: 10px;
      font-size: 14px; 
      text-align: left;
      background: #f9f9f9; 
      max-width: 100%;
    }
    .flex-group {
      display: flex;
      flex-wrap: nowrap; /* Mencegah turun ke bawah */
      gap: 20px; /* Jarak antar input */
      align-items: center; /* Menyelaraskan elemen sejajar */
    }

    .flex-group .form-group {
      flex: 1; /* Membuat input memiliki ukuran yang sama */
    }

    .flex-group input {
      width: 100%; /* Agar input mengisi form-group */
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
            <input type="text" name="category_regex" id="category_regex" class="form-control" placeholder="INDIHOME, INDIBIZ, Wifiid, Astinet, Metro, VPNIP, WMS, OLO" required>
            <label for="category_regex">
              Format Edit Kategori 
              <a href="#" id="regexInfo" class="info-icon">ℹ️</a>
            </label>
          </div>

          <!-- Modal atau Tooltip untuk Menampilkan Info Regex -->
          <div id="regexModal" style="display: none; position: absolute; background: white; border: 1px solid #ccc; padding: 10px;">
            <strong>Format:</strong>
            <p>INDIHOME|INDIBIZ|Wifiid|Astinet|Metro|VPNIP|WMS|OLO</p>
          </div>

          <div class="form-group">
            <input type="submit" class="btn btn-block btn-outline-primary btn-customs" value="Edit Kategori">
          </div>
        </form>
      </div>

      <!-- Section Manajemen Group -->
      <div class="admin-section">
        <h3>Manajemen Group</h3>
        <div class="table-responsive">
        <h4 style="margin-bottom: 20px;">Daftar Group</h4>
            <table id="dataTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Group</th>
                    <th>Id Group</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
            </table>
        </div>
        <h4 style="margin: 20px;">Tambah Group</h4>
        <form action="" method="POST">
          <div class="flex-group">
            <div class="form-group">
              <input type="text" name="group_name" id="group_name" class="form-control" placeholder="Nama Group" required>
            </div>
            <div class="form-group">
              <input type="text" name="group_description" id="group_description" class="form-control" placeholder="ID Group" required>
            </div>
            <div class="form-group">
              <input type="submit" class="btn btn-outline-primary btn-customs" value="Tambah Group">
            </div>
          </div>
        </form>
      </div>
    </section>
  </main>
  <script>
  document.getElementById("regexInfo").addEventListener("click", function(event) {
    event.preventDefault();
    let modal = document.getElementById("regexModal");
    modal.style.display = modal.style.display === "none" ? "block" : "none";
    modal.style.position = "absolute";
    modal.style.top = event.pageY + "px";
    modal.style.right = event.pageX  + "px";
  });

  document.addEventListener("click", function(event) {
    if (!event.target.matches("#regexInfo")) {
      document.getElementById("regexModal").style.display = "none";
    }
  });
  </script>
  <script src="./js/datatable.js"></script>
</body>
</html>