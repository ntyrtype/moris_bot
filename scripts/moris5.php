<?php
require "../config/Database.php";
require "../config/bot_config.php";

// File untuk menyimpan offset terakhir
$offsetFile = 'last_offset.txt';

// Membaca offset terakhir dari file (jika ada)
$offset = file_exists($offsetFile) ? (int)file_get_contents($offsetFile) : 0;

while (true) {
    // Ambil update dari Telegram
    $updates = json_decode(file_get_contents(API_URL . "getUpdates?offset=$offset"), true);

    if ($updates["ok"] && count($updates["result"]) > 0) {
        foreach ($updates["result"] as $update) {
            $offset = $update["update_id"] + 1; // Update offset ke ID update terakhir + 1

            // Menyimpan offset terakhir ke file
            file_put_contents($offsetFile, $offset);

            $message = $update["message"] ?? null;

            if ($message) {
                $chat_id = $message["chat"]["id"];
                $chat_type = $message["chat"]["type"] ?? '';
                $text = $message["text"] ?? "";
                $user_id = $message["from"]["id"];
                $username = $message["from"]["username"] ?? "Unknown";
                $message_id = $message["message_id"]; // Extract message_id

                handleUserMessage($user_id, $chat_id, $text, $username, $chat_type, $message_id);
            }
        }
    }

    sendNotifications();

    sleep(2); // Delay untuk mencegah bot terlalu sering mengecek update
}

function handleUserMessage($user_id, $chat_id, $text, $username, $chat_type, $message_id) {
    global $pdo;

    // ID grup yang ditargetkan
    $target_group_id = -4712566458;

    if ($chat_type === 'private') {
        // Jika pesan berasal dari chat pribadi
        if ($text == "/start") {
            sendMessage($chat_id, "Selamat datang! Ketik /daftar untuk registrasi.");
        } elseif ($text == "/daftar") {
            sendMessage($chat_id, "Masukkan nama Anda:");
            saveTempState($user_id, 'awaiting_name');
        } elseif (getTempState($user_id) == 'awaiting_name') {
            saveTempStateValue($user_id, 'name', $text);
            saveTempState($user_id, 'awaiting_username');
            sendMessage($chat_id, "Masukkan username Anda:");
        } elseif (getTempState($user_id) == 'awaiting_username') {
            saveTempStateValue($user_id, 'username', $text);
            saveTempState($user_id, 'awaiting_password');
            sendMessage($chat_id, "Masukkan password Anda:");
        } elseif (getTempState($user_id) == 'awaiting_password') {
            $password = password_hash($text, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (Nama, ID_Telegram, Username_Telegram, Password) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                getTempStateValue($user_id, 'name'),
                $user_id,
                getTempStateValue($user_id, 'username'),
                $password
            ]);
            sendMessage($chat_id, "Anda telah terdaftar!\nNama: " . getTempStateValue($user_id, 'name') . "\nUsername: " . getTempStateValue($user_id, 'username'));
            clearTempState($user_id);
        }
    } elseif ($chat_type === 'group' || $chat_type === 'supergroup') {
        // Jika pesan berasal dari grup
        if ($chat_id == $target_group_id && strpos($text, "/moban") === 0) {
            handleOrder($text, $chat_id, $message_id);
        }
    }
}

function sendMessage($chat_id, $message) {
    $url = API_URL . "sendMessage?chat_id=$chat_id&text=" . urlencode($message);
    file_get_contents($url);
}

function handleOrder($text, $chat_id, $message_id) {
    global $pdo;

    // Extract order details from the message
    preg_match("/#(\w+) #(.+) #(.+)/", $text, $matches);
    if (count($matches) === 4) {
        $order_id = $matches[1];
        $transaksi = $matches[2];
        $keterangan = $matches[3];
        $no_tiket = generateTicket();

        try {
            $pdo->beginTransaction();

            // Simpan data ke tabel orders
            $stmt1 = $pdo->prepare("INSERT INTO orders (Order_ID, Transaksi, Keterangan, No_Tiket, Status) VALUES (?, ?, ?, ?, 'Order')");
            $stmt1->execute([$order_id, $transaksi, $keterangan, $no_tiket]);

            // Simpan data ke tabel order_messages
            $stmt2 = $pdo->prepare("INSERT INTO order_messages (no_tiket, message_id) VALUES (?, ?)");
            $stmt2->execute([$no_tiket, $message_id]);

            $pdo->commit();

            replyMessage($chat_id, "Permintaan Anda $order_id $transaksi sudah kami proses dengan no tiket $no_tiket, silakan tunggu.", $message_id);
        } catch (Exception $e) {
            $pdo->rollBack();
            replyMessage($chat_id, "Terjadi kesalahan saat menyimpan order. Coba lagi nanti.", $message_id);
        }
    } else {
        replyMessage($chat_id, "Format order tidak valid. Pastikan formatnya adalah: /moban #Order_ID #Transaksi #Keterangan", $message_id);
    }
}

function replyMessage($chat_id, $message, $reply_to_message_id) {
    $url = API_URL . "sendMessage?chat_id=$chat_id&text=" . urlencode($message) . "&reply_to_message_id=$reply_to_message_id";
    file_get_contents($url);
}

function sendNotifications() {
    global $pdo;

    // Ambil notifikasi yang belum terkirim
    $stmt = $pdo->query("SELECT * FROM bot_notifications WHERE is_sent = 0");
    $notifications = $stmt->fetchAll();

    foreach ($notifications as $notification) {
        $chat_id = $notification['Chat_ID'];
        $no_tiket = $notification['No_Tiket'];
        $order_id = $notification['Order_ID'];
        $transaksi = $notification['Transaksi'];

        // Cek status order di tabel 'orders'
        $stmtStatus = $pdo->prepare("SELECT Status FROM orders WHERE No_Tiket = ?");
        $stmtStatus->execute([$no_tiket]);
        $status = $stmtStatus->fetchColumn();

        // Ambil user yang melakukan "Pick Up" dari tabel order_activity
        $stmtPickUp = $pdo->prepare("SELECT user_id FROM order_activity WHERE no_tiket = ? AND activity_type = 'Pickup' ORDER BY timestamp DESC LIMIT 1");
        $stmtPickUp->execute([$no_tiket]);
        $pickUpUser = $stmtPickUp->fetch(PDO::FETCH_ASSOC);

        // Ambil user yang melakukan "Close" dari tabel order_activity
        $stmtClose = $pdo->prepare("SELECT user_id FROM order_activity WHERE no_tiket = ? AND activity_type = 'Close' ORDER BY timestamp DESC LIMIT 1");
        $stmtClose->execute([$no_tiket]);
        $closeUser = $stmtClose->fetch(PDO::FETCH_ASSOC);

        // Ambil nama pengguna yang melakukan Pick Up
        if ($pickUpUser) {
            $stmtUser = $pdo->prepare("SELECT Nama FROM users WHERE ID = ?");
            $stmtUser->execute([$pickUpUser['user_id']]);
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
            $pickUpUserName = $user ? $user['Nama'] : 'Unknown User';
        } else {
            $pickUpUserName = 'Unknown User'; // Jika tidak ada pengguna yang ditemukan
        }

        // Ambil nama pengguna yang melakukan Close
        if ($closeUser) {
            $stmtUser = $pdo->prepare("SELECT Nama FROM users WHERE ID = ?");
            $stmtUser->execute([$closeUser['user_id']]);
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
            $closeUserName = $user ? $user['Nama'] : 'Unknown User';
        } else {
            $closeUserName = 'Unknown User'; // Jika tidak ada pengguna yang ditemukan
        }

        // Tentukan pesan berdasarkan status
        if ($status === 'Pickup') {
            $message = "Permintaan Anda $no_tiket $order_id $transaksi sudah di PICK UP oleh $pickUpUserName.";
        } elseif ($status === 'Close') {
            $message = "Permintaan Anda $no_tiket $order_id $transaksi sudah di RESOLVED oleh $closeUserName.";
        } else {
            continue; // Jika bukan Pickup atau Close, lewati
        }

        // Kirim pesan
        sendMessage($chat_id, $message);

        // Tandai sebagai terkirim
        $stmtUpdate = $pdo->prepare("UPDATE bot_notifications SET is_sent = 1 WHERE id = ?");
        $stmtUpdate->execute([$notification['id']]);
    }
}

function generateTicket() {
    return 'TKT' . strtoupper(substr(uniqid(rand(), true), -5));
}

function saveTempState($user_id, $state) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO temp_user_state (user_id, state) VALUES (?, ?) ON DUPLICATE KEY UPDATE state = ?");
    $stmt->execute([$user_id, $state, $state]);
}

function getTempState($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT state FROM temp_user_state WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

function saveTempStateValue($user_id, $key, $value) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO temp_user_data (user_id, `key`, value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value = ?");
    $stmt->execute([$user_id, $key, $value, $value]);
}

function getTempStateValue($user_id, $key) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT value FROM temp_user_data WHERE user_id = ? AND `key` = ?");
    $stmt->execute([$user_id, $key]);
    return $stmt->fetchColumn();
}

function clearTempState($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM temp_user_state WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $stmt = $pdo->prepare("DELETE FROM temp_user_data WHERE user_id = ?");
    $stmt->execute([$user_id]);
}
?>
