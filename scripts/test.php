<?php 
require "../config/Database.php";
require "../config/bot_config.php";


function sendGroupMessage($message) {
    $groupChatId = '-4712566458';  // Ganti dengan ID grup Anda
    $url = API_URL . "sendMessage?chat_id=$groupChatId&text=" . urlencode($message);
    $response = file_get_contents($url);

    // Debug response
    if ($response === FALSE) {
        error_log("Gagal mengirim pesan ke grup: $message");
    } else {
        error_log("Pesan terkirim ke grup: $message");
    }
}
?>