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
            sendMessage($chat_id, "Selamat datang! Ketik /daftar untuk registrasi");
        } elseif ($text == "/daftar") {
            // Mengecek apakah ID Telegram sudah terdaftar
            $stmt = $pdo->prepare("SELECT * FROM users WHERE ID_Telegram = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if ($user) {
                // Jika pengguna sudah terdaftar, tampilkan role-nya
                $role = $user['Role'] ?? 'Unknown Role'; // Ambil role dari database
                sendMessage($chat_id, "Telegram Anda sudah terdaftar sebagai $role.");
            } else {
                sendMessage($chat_id, "Masukkan nama Anda:");
                // Simpan status bahwa bot sedang menunggu input nama
                $_SESSION['state'] = 'awaiting_name';
            }
        } elseif (isset($_SESSION['state']) && $_SESSION['state'] == 'awaiting_name') {
            // Menyimpan nama yang diterima
            $_SESSION['name'] = $text;
            sendMessage($chat_id, "Masukkan role Anda (Teknisi/Plaza):");
            $_SESSION['state'] = 'awaiting_role';
        } elseif (isset($_SESSION['state']) && $_SESSION['state'] == 'awaiting_role') {
            // Validasi role yang dimasukkan
            $role = strtolower($text);
            if ($role === 'teknisi' || $role === 'plaza') {
                $_SESSION['role'] = ucfirst($role); // Simpan role dengan format kapital
                
                // Menyimpan data pengguna baru ke dalam database
                $stmt = $pdo->prepare("INSERT INTO users (Nama, Role, ID_Telegram) VALUES (?, ?, ?)");
                $stmt->execute([
                    $_SESSION['name'],
                    $_SESSION['role'],
                    $user_id
                ]);

                // Mengirim konfirmasi pendaftaran
                $message = "Anda telah terdaftar sebagai " . $_SESSION['role'] . "!\nNama: " . $_SESSION['name'] . "\nRole: " . $_SESSION['role'];
                sendMessage($chat_id, $message);

                // Menghapus session setelah pendaftaran selesai
                unset($_SESSION['state']);
                unset($_SESSION['name']);
                unset($_SESSION['role']);
            } else {
                sendMessage($chat_id, "Role tidak valid. Masukkan role 'Teknisi' atau 'Plaza'.");
            }
        }
    } elseif ($chat_type === 'group' || $chat_type === 'supergroup') {
        // Jika pesan berasal dari grup
        if ($chat_id == $target_group_id && strpos($text, "/moban") === 0) {
            handleOrder($text, $chat_id, $message_id, $user_id, $username);
        } elseif ($chat_id == $target_group_id && $text == "/help") {
            sendHelpMessage($chat_id, $message_id);
        }
    }
}

function sendHelpMessage($chat_id, $message_id) {
    $helpMessage = "Panduan Penggunaan Perintah `/moban`\n\n"
                . "Gunakan format berikut:\n"
                . "/moban #Kategori #Transaksi #Order_ID #Keterangan\n"
                . "\nKategori:"
                . "\n- INDIHOME"
                . "\n- INDIBIZ"
                . "\n- DATIN"
                . "\n\nJenis Transaksi:"
                . "\n- PDA"
                . "\n- MO"
                . "\n- ORBIT"
                . "\n- FFG"
                . "\n- UNSPEK"
                . "\n\n Untuk informasi lebih lanjut, hubungi admin.";

    replyMessage($chat_id, $helpMessage, $message_id);
}


function sendMessage($chat_id, $message) {
    $url = API_URL . "sendMessage?chat_id=$chat_id&text=" . urlencode($message);
    file_get_contents($url);
}

function handleOrder($text, $chat_id, $message_id, $user_id, $username) {
    global $pdo;

    // Perbaikan regex agar sesuai format
    preg_match("/^\/moban #(INDIHOME|INDIBIZ|DATIN|WMS|OLO) #([A-Z0-9]+) #([A-Z0-9]+) #([\s\S]+)/i", $text, $matches);

    if (count($matches) !== 5) {
        $message = "Format Order Tidak Valid!\n\n";
        $message .= "Pastikan formatnya sesuai dengan contoh berikut:\n";
        $message .= "/moban #Kategori #Transaksi #WONUM #Keterangan\n\n";
        $message .= "Contoh:\n";
        $message .= "/moban #INDIHOME #MO #WO123456789 #Permintaan layanan\n\n";
        $message .= "Untuk informasi lebih lanjut, ketik perintah `/help`.";
        
        replyMessage($chat_id, $message, $message_id,);
        return;
    }

    $kategori = strtoupper($matches[1]); // Kategori (INDIHOME, INDIBIZ, DATIN)
    $transaksi = strtoupper($matches[2]); // Transaksi
    $wonum = strtoupper($matches[3]); // WONUM
    $keterangan = trim($matches[4]); // Keterangan

    // Ambil role user berdasarkan user_id
    $stmt = $pdo->prepare("SELECT Nama, role FROM users WHERE id_telegram = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        replyMessage($chat_id, "User tidak ditemukan dalam database.", $message_id);
        return;
    }

    // Jika user bukan Teknisi/Plaza, tolak akses
    if (!in_array($user['role'], ['teknisi', 'plaza'])) {
        replyMessage($chat_id, "Maaf, fitur ini hanya bisa digunakan oleh Teknisi atau Plaza. Hubungi admin untuk mendapatkan akses.", $message_id);
        return;
    }

    $nama = $user['Nama']; // Ambil nama user dari database
    $order_by = strtolower($user['role']); // Role user sebagai order_by

    // Generate nomor tiket
    $no_tiket = generateTicket();

    // Cek apakah Order_ID atau No_Tiket sudah ada
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE Order_ID = ? OR No_Tiket = ?");
    $stmtCheck->execute([$wonum, $no_tiket]);
    $exists = $stmtCheck->fetchColumn();
    
    if ($exists > 0) {
        $stmtGet = $pdo->prepare("SELECT Order_ID, No_Tiket FROM orders WHERE Order_ID = ? OR No_Tiket = ?");
        $stmtGet->execute([$wonum, $no_tiket]);
        $existingOrder = $stmtGet->fetch(PDO::FETCH_ASSOC);
    
        replyMessage($chat_id, "Order sudah ada di sistem!\n\n Order_ID: {$existingOrder['Order_ID']}\n No_Tiket: {$existingOrder['No_Tiket']}", $message_id);
        return;
    }
    
    try {
        $pdo->beginTransaction();

        // Simpan ke tabel orders
        $stmt1 = $pdo->prepare("INSERT INTO orders (Order_ID, Transaksi, Kategori, Keterangan, No_Tiket, Status, id_telegram, username_telegram, order_by) 
        VALUES (?, ?, ?, ?, ?, 'Order', ?, ?, ?)");
        $stmt1->execute([$wonum, $transaksi, $kategori, $keterangan, $no_tiket, $user_id, $username, $order_by]);

        // Simpan ke tabel order_messages
        $stmt2 = $pdo->prepare("INSERT INTO order_messages (no_tiket, message_id) VALUES (?, ?)");
        $stmt2->execute([$no_tiket, $message_id]);

        $pdo->commit();

        replyMessage($chat_id, "Hallo $nama. Permintaan Anda $wonum $transaksi $kategori sudah kami rekap dengan no tiket $no_tiket.", $message_id);
    } catch (Exception $e) {
        $pdo->rollBack();
        replyMessage($chat_id, "Terjadi kesalahan saat menyimpan order. Coba lagi nanti.\n\nError: " . $e->getMessage(), $message_id);
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
