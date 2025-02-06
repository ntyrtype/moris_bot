<?php
session_start();

// Include file koneksi database
require_once '../config/database.php'; // Sesuaikan path jika direktori berbeda

// Proses login saat form dikirim
$error_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_username = trim($_POST['username']);
    $input_password = trim($_POST['password']);

    // Query untuk mencari user berdasarkan username
    try {
        $query = "SELECT * FROM users WHERE Username_Telegram = :username";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":username", $input_username, PDO::PARAM_STR);
        $stmt->execute();

        // Cek apakah user ditemukan
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verifikasi password
            if (password_verify($input_password, $user['Password'])) {
                // Update status menjadi 'active' setelah login berhasil
                $update_query = "UPDATE users SET status = 'active' WHERE ID = :user_id";
                $update_stmt = $pdo->prepare($update_query);
                $update_stmt->bindParam(":user_id", $user['ID'], PDO::PARAM_INT);
                $update_stmt->execute();

                // Set session untuk pengguna
                $_SESSION['user_id'] = $user['ID'];
                $_SESSION['username'] = $user['Username_Telegram'];
                $_SESSION['nama'] = $user['Nama']; 
                
                // Redirect ke dashboard
                header("Location: Dashboard.php");
                exit();
            } else {
                $error_message = "Password salah!";
            }
        } else {
            $error_message = "Username tidak ditemukan!";
        }
    } catch (PDOException $e) {
        $error_message = "Terjadi kesalahan saat menghubungkan ke database: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
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
      <h2 class="display-4 pt-3">Login</h2>
      <p class="text-center">Silahkan masukkan username dan password.</p>

      <?php if ($error_message): ?>
        <div class="alert alert-danger text-center"><?php echo $error_message; ?></div>
      <?php endif; ?>

      <form action="" method="POST"> 
        <div class="form-group">
          <input type="text" name="username" id="username" class="form-control" placeholder="Username" required >
        </div>

        <div class="form-group">
          <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
        </div>

        <div class="form-group">
          <input type="submit" class="btn btn-block btn-outline-primary btn-customs" value="Login">
        </div>
      </form>
    </section>
  </main>
</body>
</html>
