<?php
// memulai session
session_start();
require "../config/Database.php"; // Menggunakan koneksi database dari sini

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Anda harus login terlebih dahulu!'); window.location.href='index.php';</script>";
    exit();
}

// berdasar user_id yang tertngkap di session
$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // Validasi input tidak boleh kosong
    if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
        echo "<script>alert('Semua input harus diisi!'); window.history.back();</script>";
        exit();
    }

    // Cek apakah password baru dan konfirmasi password cocok
    if ($password_baru !== $konfirmasi_password) {
        echo "<script>alert('Konfirmasi password tidak sesuai!'); window.history.back();</script>";
        exit();
    }

    // Gunakan koneksi database dari Database.php
    global $pdo; // Pastikan $pdo dari Database.php tersedia

    try {
        // Ambil password lama dari database
        $sql = "SELECT password FROM users WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // jika pengguna tidak ditemukan
        if (!$user) {
            echo "<script>alert('Pengguna tidak ditemukan!'); window.history.back();</script>";
            exit();
        }

        // Hash password lama yang dimasukkan user (hash menggunaan sha256)
        $hashed_input = hash("sha256", $password_lama);

        // Verifikasi apakah password lama cocok
        if ($hashed_input !== $user['password']) {
            echo "<script>alert('Password lama salah!'); window.history.back();</script>";
            exit();
        }

        // Hash password baru dengan SHA-256
        $password_baru_hashed = hash("sha256", $password_baru);

        // Update password di database
        $update_sql = "UPDATE users SET password = :password WHERE id = :id";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->bindParam(':password', $password_baru_hashed, PDO::PARAM_STR);
        $update_stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        
        if ($update_stmt->execute()) {
            echo "<script>alert('Password berhasil diubah! Silakan login kembali.'); window.location.href='logout.php';</script>";
        } else {
            echo "<script>alert('Gagal mengupdate password!'); window.history.back();</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Terjadi kesalahan: " . $e->getMessage() . "'); window.history.back();</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ADD USER</title>
  <!-- memanggil bootstrap -->
  <link href="https://stackpath.bootstrapcdn.com/bootswatch/4.4.1/cosmo/bootstrap.min.css" rel="stylesheet">
  <!-- styling -->
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
      <form action="reset_password.php" method="POST"> 
        <!-- memasukkan password lama -->
          <div class="form-group">
              <input type="password" name="password_lama" id="password_lama" class="form-control" placeholder="Password Lama" required>
          </div>
        <!-- memasukkan password baru -->
          <div class="form-group">
              <input type="password" name="password_baru" id="password_baru" class="form-control" placeholder="Password Baru" required>
          </div>
        <!-- mengkonfirmasi password baru -->
          <div class="form-group">
              <input type="password" name="konfirmasi_password" id="konfirmasi_password" class="form-control" placeholder="Konfirmasi Password Baru" required>
          </div>
        <!-- btn submit -->
          <div class="form-group">
              <input type="submit" class="btn btn-block btn-outline-primary btn-customs" value="Submit">
          </div>
      </form>
    </section>
  </main>
</body>
</html>