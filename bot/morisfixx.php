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

    // // ID grup yang ditargetkan
    // $target_group_id = -1002387652955;

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
            sendMessage($chat_id, "Masukkan role Anda (Teknisi/Plasa):");
            $_SESSION['state'] = 'awaiting_role';
        } elseif (isset($_SESSION['state']) && $_SESSION['state'] == 'awaiting_role') {
            // Validasi role yang dimasukkan
            $role = strtolower($text);
            if ($role === 'teknisi' || $role === 'plasa') {
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
                sendMessage($chat_id, "Role tidak valid. Masukkan role 'Teknisi' atau 'Plasa'.");
            }
        }
    } elseif ($chat_type === 'group' || $chat_type === 'supergroup') {
        // Hanya cek database jika pesan adalah /moban atau /help
        if (strpos($text, "/moban") === 0 || $text == "/help") {
            $stmt = $pdo->prepare("SELECT * FROM groups WHERE group_id = ?");
            $stmt->execute([$chat_id]);
            $group = $stmt->fetch();

            // Jika grup tidak terdaftar, kirim pesan dan hentikan eksekusi
            if (!$group) {
                sendMessage($chat_id, "Grup belum terdaftar di bot.\n\nHubungi admin untuk mendaftarkan grup ini.");
                return;
            }

            // Proses perintah yang valid
            if (strpos($text, "/moban") === 0) {
                handleOrder($text, $chat_id, $message_id, $user_id, $username);
            } elseif ($text == "/help") {
                sendHelpMessage($chat_id, $message_id);
            }
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
                . "\n- Wifiid"
                . "\n- Astinet"
                . "\n- Metro"
                . "\n- VPNIP"
                . "\n- OLO"
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

    // Ambil daftar kategori dari database
    $stmt = $pdo->query("SELECT regex_pattern FROM kategori LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $kategori_regex = $row['regex_pattern']; // Contoh: "INDIHOME|INDIBIZ|Wifiid|Astinet|Metro|VPNIP|WMS|OLO"

    // Perbaikan regex agar sesuai format dengan kategori yang diambil dari database
    $pattern = "/^\/moban #($kategori_regex) #([A-Z0-9]+) #([A-Z0-9]+) #([\s\S]+)/i";
    preg_match($pattern, $text, $matches);

    // Perbaikan regex agar sesuai format
    // preg_match("/^\/moban #(INDIHOME|INDIBIZ|Wifiid|Astinet|Metro|VPNIP|WMS|OLO) #([A-Z0-9]+) #([A-Z0-9]+) #([\s\S]+)/i", $text, $matches);

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
        replyMessage($chat_id, "User tidak ditemukan dalam database. Daftarkan anda terlebih dahulu", $message_id);
        return;
    }

    // Jika user bukan Teknisi/Plasa, tolak akses
    if (!in_array($user['role'], ['teknisi', 'plasa'])) {
        replyMessage($chat_id, "Maaf, fitur ini hanya bisa digunakan oleh Teknisi atau Plasa. Hubungi admin untuk mendapatkan akses.", $message_id);
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

        // Simpan ke tabel orders (tambahkan group_id)
        $stmt1 = $pdo->prepare("INSERT INTO orders (Order_ID, Transaksi, Kategori, Keterangan, No_Tiket, Status, id_telegram, username_telegram, order_by, group_id) 
        VALUES (?, ?, ?, ?, ?, 'Order', ?, ?, ?, ?)");
        $stmt1->execute([$wonum, $transaksi, $kategori, $keterangan, $no_tiket, $user_id, $username, $order_by, $chat_id]); // <-- $chat_id adalah group_id

        // Simpan ke tabel order_messages
        $stmt2 = $pdo->prepare("INSERT INTO order_messages (no_tiket, message_id, group_id) VALUES (?, ?, ?)");
        $stmt2->execute([$no_tiket, $message_id, $chat_id]); // <-- Simpan group_id

        $pdo->commit();

        // Balas pesan di grup asal
        replyMessage($chat_id, "Hallo $nama. Permintaan Anda $wonum $transaksi $kategori dengan no tiket $no_tiket sedang kami check, silahkan tunggu.", $message_id);
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
    $stmt = $pdo->query("
        SELECT lo.*, o.group_id 
        FROM log_orders lo
        JOIN orders o ON lo.No_Tiket = o.No_Tiket
        WHERE (lo.status = 'Pickup' OR lo.status = 'Close') 
        AND lo.is_sent = 0
    ");
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($notifications as $notification) {
        $chat_id = $notification['group_id']; // Ambil group_id dari order
        $no_tiket = $notification['No_Tiket'];
        $order_id = $notification['order_id'];
        $transaksi = $notification['transaksi'];
        $status = $notification['status'];
        $progress_order = $notification['progress_order'];
        $keterangan = !empty($notification['keterangan']) ? $notification['keterangan'] : "-"; // Jika NULL atau kosong, ubah jadi "-"
        $nama = $notification['nama']; // Nama yang menangani order
        $order_by = $notification['order_by']; // Teknisi atau Plasa

        // Format pesan berdasarkan status
        if ($status === 'Pickup') {
            if (in_array($progress_order, ['In Progress', 'Ada Kendala', 'On Eskalasi'])) {
                $message = " Order Pending\n\n No Tiket: $no_tiket\n Order ID: $order_id\n Transaksi: $transaksi\n Progress: $progress_order\n Ditangani oleh: $nama ($order_by)\n Keterangan: $keterangan";
            } elseif ($progress_order === 'On Rekap') {
                $message = " Order Proses\n\n No Tiket: $no_tiket\n Order ID: $order_id\n Transaksi: $transaksi\n Progress: $progress_order\n Ditangani oleh: $nama ($order_by)\n Keterangan: $keterangan";
            }
        } elseif ($status === 'Close') {
            if ($progress_order === 'Cancel') {
                $message = " Order Cancelled\n\n No Tiket: $no_tiket\n Order ID: $order_id\n Transaksi: $transaksi\n Progress Terakhir: $progress_order\n Ditangani oleh: $nama ($order_by)\n Keterangan: $keterangan";
            } elseif ($progress_order === 'Sudah PS') {
                $message = " Order Selesai\n\n No Tiket: $no_tiket\n Order ID: $order_id\n Transaksi: $transaksi\n Progress Terakhir: $progress_order\n Ditangani oleh: $nama ($order_by)\n Keterangan: $keterangan";
            }
        }
        

        // Ambil message_id dari order_messages yang sesuai dengan grup
        $stmtMessage = $pdo->prepare("
        SELECT message_id 
        FROM order_messages 
        WHERE no_tiket = ? AND group_id = ?
        ORDER BY id ASC 
        LIMIT 1
        ");
        $stmtMessage->execute([$no_tiket, $chat_id]);
        $orderMessage = $stmtMessage->fetch(PDO::FETCH_ASSOC);


        // Kirim pesan dengan atau tanpa reply
        if ($orderMessage) {
            replyMessage($chat_id, $message, $orderMessage['message_id']);
        } else {
            sendMessage($chat_id, $message);
        }

        // Update status is_sent menjadi 1 agar tidak dikirim ulang
        $stmtUpdate = $pdo->prepare("UPDATE log_orders SET is_sent = 1 WHERE no = ?");
        $stmtUpdate->execute([$notification['no']]);
    }
}


// function generateTicket() {
//     return 'TKT' . strtoupper(substr(uniqid(rand(), true), -5));
// }

function generateTicket() {
    global $pdo;
    do {
        $no_tiket = 'TKT' . strtoupper(substr(uniqid(rand(), true), -5));

        // Menggunakan prepared statement untuk keamanan
        $query = "SELECT COUNT(*) FROM orders WHERE No_Tiket = :no_tiket";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':no_tiket', $no_tiket, PDO::PARAM_STR);
        $stmt->execute();
        $count = $stmt->fetchColumn();
    } while ($count > 0); // Jika sudah ada di DB, ulangi generate

    return $no_tiket;
}


?>
