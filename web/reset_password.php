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


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = $password = hash('sha256', $_POST['password']); // Enkripsi password
    $role = 'helpdesk'; // Default role
    $status = 'active'; // Default status

    try {
        $stmt = $pdo->prepare("INSERT INTO users (Nama, Username, Password, role, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nama, $username, $password, $role, $status]);

        echo "<script>alert('User berhasil ditambahkan!'); window.location.href='index.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Gagal menambahkan user: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ADD USER</title>
  <link href="https://stackpath.bootstrapcdn.com/bootswatch/4.4.1/cosmo/bootstrap.min.css" rel="stylesheet">
  <style>
    body{
      background-color: #2c3e50;
    }
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
      font-size: xxx-large;
      font-weight: bold;
      text-align: center;
    }
    .wrapper p {
      padding: 8px;
      margin-bottom: 2px;
    }
    .wrapper form .form-group span {color: red;}

    .form-group{
      margin-bottom: 20px;
      padding: 10px;
    }
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
      <button class="close-btn" onclick="window.history.back();">&times;</button>
      <h2 class="display-4 pt-3">Reset Password</h2>
      <p class="text-center">Isi inputan di bawah untuk mengganti password</p>
      
      <form action="" method="POST"> 

        <div class="form-group">
          <input type="password" name="password" id="password" class="form-control" placeholder="Password Lama" required>
        </div>

        <div class="form-group">
          <input type="password" name="password" id="password" class="form-control" placeholder="Password Baru" required>
        </div>

        <div class="form-group">
          <input type="password" name="password" id="password" class="form-control" placeholder="Konfirmasi Password Baru" required>
        </div>

        <div class="form-group">
          <input type="submit" class="btn btn-block btn-outline-primary btn-customs" value="Submit">
        </div>
      </form>
    </section>
  </main>
</body>
</html>