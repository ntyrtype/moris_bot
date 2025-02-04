<?php
session_start();
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
            // Mengecek apakah ID Telegram sudah terdaftar
            $stmt = $pdo->prepare("SELECT * FROM users WHERE ID_Telegram = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if ($user) {
                sendMessage($chat_id, "Telegram Anda sudah terdaftar.");
            } else {
                sendMessage($chat_id, "Masukkan nama Anda:");
                // Simpan status bahwa bot sedang menunggu input nama
                $_SESSION['state'] = 'awaiting_name';
            }
        } elseif (isset($_SESSION['state']) && $_SESSION['state'] == 'awaiting_name') {
            // Menyimpan nama yang diterima
            $_SESSION['name'] = $text;
            sendMessage($chat_id, "Masukkan username Anda:");
            $_SESSION['state'] = 'awaiting_username';
        } elseif (isset($_SESSION['state']) && $_SESSION['state'] == 'awaiting_username') {
            // Mengecek apakah username sudah terdaftar
            $stmt = $pdo->prepare("SELECT * FROM users WHERE Username_Telegram = ?");
            $stmt->execute([strtolower($text)]);
            $existing_user = $stmt->fetch();

            if ($existing_user) {
                sendMessage($chat_id, "Username Anda sudah terdaftar. Coba lagi dengan username yang berbeda.");
            } else {
                $_SESSION['username'] = $text;
                sendMessage($chat_id, "Masukkan password Anda:");
                $_SESSION['state'] = 'awaiting_password';
            }
        } elseif (isset($_SESSION['state']) && $_SESSION['state'] == 'awaiting_password') {
            // Meng-hash password untuk penyimpanan aman
            $password = password_hash($text, PASSWORD_DEFAULT);

            // Menyimpan data pengguna baru ke dalam database
            $stmt = $pdo->prepare("INSERT INTO users (Nama, ID_Telegram, Username_Telegram, Password) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['name'],
                $user_id,
                $_SESSION['username'],
                $password
            ]);

            // Mengirim konfirmasi pendaftaran
            sendMessage($chat_id, "Anda telah terdaftar!\nNama: " . $_SESSION['name'] . "\nUsername: " . $_SESSION['username']);

            // Menghapus session setelah pendaftaran selesai
            unset($_SESSION['state']);
            unset($_SESSION['name']);
            unset($_SESSION['username']);
        }
    } elseif ($chat_type === 'group' || $chat_type === 'supergroup') {
        // Jika pesan berasal dari grup
        if ($chat_id == $target_group_id && strpos($text, "/moban") === 0) {
            handleOrder($text, $chat_id, $message_id, $user_id, $username);
        }
    }
}


function sendMessage($chat_id, $message) {
    $url = API_URL . "sendMessage?chat_id=$chat_id&text=" . urlencode($message);
    file_get_contents($url);
}

function handleOrder($text, $chat_id, $message_id, $user_id, $username) {
    global $pdo;

    // Extract order details from the message using regex that allows line breaks
    preg_match("/#(\w+) #(\w+) #(.+)/s", $text, $matches);
    if (count($matches) === 4) {
        $order_id = $matches[1];
        $transaksi = $matches[2];
        $keterangan = trim($matches[3]);

        // Replace newline characters with a space or some other placeholder if needed
        $keterangan = str_replace("\n", " ", $keterangan); // Mengganti baris baru dengan spasi
        $keterangan = str_replace("\r", " ", $keterangan); // Mengganti carriage return dengan spasi

        // Generate ticket number
        $no_tiket = generateTicket();

        try {
            $pdo->beginTransaction();

            // Save data into the orders table
            $stmt1 = $pdo->prepare("INSERT INTO orders (Order_ID, Transaksi, Keterangan, No_Tiket, Status, id_telegram, username_telegram) VALUES (?, ?, ?, ?, 'Order', ?, ?)");
            $stmt1->execute([$order_id, $transaksi, $keterangan, $no_tiket, $user_id, $username]);

            // Save data into the order_messages table (jika diperlukan)
            $stmt2 = $pdo->prepare("INSERT INTO order_messages (no_tiket, message_id) VALUES (?, ?)");
            $stmt2->execute([$no_tiket, $message_id]);

            $pdo->commit();

            replyMessage($chat_id, "Permintaan Anda $order_id $transaksi sudah kami proses dengan no tiket $no_tiket, silakan tunggu.", $message_id);
        } catch (Exception $e) {
            $pdo->rollBack();
            replyMessage($chat_id, "Terjadi kesalahan saat menyimpan order. Coba lagi nanti.", $message_id);
        }
    } else {
        replyMessage($chat_id, "Format order tidak valid. Pastikan formatnya sesuai dengan template berikut:\n\n/moban #Order_ID #Transaksi #Keterangan\n\nContoh pengisian yang benar:\n/moban #PSB #WO014812917 #DGPS250129211923012742835-AOi4250129091923067b57810_12748220-88080439~202501292334127286349~7286349~24114258~3~WSA\n\nODP-SWT-FAS/047\nalasan : odp inputan jarak over\nValins ID: 28176483\nTime: 2025-01-30 14:59:42\nSummary ODP: ODP-SWT-FAS/047\nIP OLT: 172.29.238.135\nSlot: 1\nPort: 9\nSN: ZTEGD816A8E1", $message_id);
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
        $stmtStatus = $pdo->prepare("SELECT Status, ket_validasi FROM orders WHERE No_Tiket = ?");
        $stmtStatus->execute([$no_tiket]);
        $order = $stmtStatus->fetch(PDO::FETCH_ASSOC);
        $status = $order['Status'];
        $ket_validasi = $order['ket_validasi']; // Ambil ket_validasi

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
            // Tambahkan ket_validasi ke pesan
            $message = "Permintaan Anda $no_tiket $order_id $transaksi sudah di RESOLVED oleh $closeUserName. Keterangan: $ket_validasi";
        } else {
            continue; // Jika bukan Pickup atau Close, lewati
        }

        // Ambil message_id dari tabel order_messages untuk digunakan sebagai reply_to_message_id
        $stmtMessage = $pdo->prepare("SELECT message_id FROM order_messages WHERE no_tiket = ? ORDER BY id ASC LIMIT 1");
        $stmtMessage->execute([$no_tiket]);
        $orderMessage = $stmtMessage->fetch(PDO::FETCH_ASSOC);

        // Jika ditemukan message_id, kirim reply
        if ($orderMessage) {
            replyMessage($chat_id, $message, $orderMessage['message_id']);
        } else {
            // Jika tidak ada message_id yang ditemukan, kirim pesan tanpa reply
            sendMessage($chat_id, $message);
        }

        // Tandai sebagai terkirim
        $stmtUpdate = $pdo->prepare("UPDATE bot_notifications SET is_sent = 1 WHERE id = ?");
        $stmtUpdate->execute([$notification['id']]);
    }
}


function generateTicket() {
    return 'TKT' . strtoupper(substr(uniqid(rand(), true), -5));
}

?>
