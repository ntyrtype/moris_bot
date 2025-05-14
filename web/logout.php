<?php
// Memulai session untuk mengakses data session yang ada
session_start();
// Menghapus semua data session yang tersimpan di server
session_destroy(); // Destroy all session data
header("Location: index.php"); // Redirect to login page
exit();
?>