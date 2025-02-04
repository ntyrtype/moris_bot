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

                handleUserMessage($user_id, $chat_id, $text, $username, $chat_type);
            }
        }
    }

    sendNotifications();

    sleep(2); // Delay untuk mencegah bot terlalu sering mengecek update
}

function handleUserMessage($user_id, $chat_id, $text, $username, $chat_type) {
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
            handleOrder($text, $chat_id);
        }
    }
}

function sendMessage($chat_id, $message) {
    $url = API_URL . "sendMessage?chat_id=$chat_id&text=" . urlencode($message);
    file_get_contents($url);
}

function handleOrder($text, $chat_id) {
    global $pdo;
    // Extract order details from the message
    preg_match("/#(\w+) #(.+) #(.+)/", $text, $matches);
    if (count($matches) === 4) {
        $order_id = $matches[1];
        $transaksi = $matches[2];
        $keterangan = $matches[3];
        $no_tiket = generateTicket();

        // Insert the order into the database
        $stmt = $pdo->prepare("INSERT INTO orders (Order_ID, Transaksi, Keterangan, No_Tiket, Status) VALUES (?, ?, ?, ?, 'Order')");
        $stmt->execute([$order_id, $transaksi, $keterangan, $no_tiket]);

        sendMessage($chat_id, "Permintaan Anda $order_id $transaksi sudah kami proses dengan no tiket $no_tiket, silahkan tunggu.");
    } else {
        sendMessage($chat_id, "Format order tidak valid. Pastikan formatnya adalah: /moban #Order_ID #Transaksi #Keterangan");
    }
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

        // Kirim pesan ke grup
        $message = "Permintaan Anda $no_tiket $order_id $transaksi sudah di PICK UP.";
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
    $stmt = $pdo->prepare("INSERT INTO temp_states (user_id, state) VALUES (?, ?) ON DUPLICATE KEY UPDATE state = ?");
    $stmt->execute([$user_id, $state, $state]);
}

function saveTempStateValue($user_id, $key, $value) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO temp_states (user_id, key, value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value = ?");
    $stmt->execute([$user_id, $key, $value, $value]);
}

function getTempState($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT state FROM temp_states WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

function getTempStateValue($user_id, $key) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT value FROM temp_states WHERE user_id = ? AND key = ?");
    $stmt->execute([$user_id, $key]);
    return $stmt->fetchColumn();
}

function clearTempState($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM temp_states WHERE user_id = ?");
    $stmt->execute([$user_id]);
}
?>
