-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 04 Feb 2025 pada 03.21
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `moris_bot`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `bot_notifications`
--

CREATE TABLE `bot_notifications` (
  `id` int(11) NOT NULL,
  `No_Tiket` varchar(50) DEFAULT NULL,
  `Order_ID` varchar(50) DEFAULT NULL,
  `Transaksi` varchar(255) DEFAULT NULL,
  `Chat_ID` bigint(20) DEFAULT NULL,
  `is_sent` tinyint(1) DEFAULT 0,
  `message_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `bot_notifications`
--

INSERT INTO `bot_notifications` (`id`, `No_Tiket`, `Order_ID`, `Transaksi`, `Chat_ID`, `is_sent`, `message_id`) VALUES
(1, 'TKT28124', 'WO', 'WO014356285', -4712566458, 1, NULL),
(2, 'TKT28124', 'WO', 'WO014356285', -4712566458, 1, NULL),
(3, 'TKT70117', 'AO', 'WO014356285', -4712566458, 1, NULL),
(4, 'TKT70117', 'AO', 'WO014356285', -4712566458, 1, NULL),
(5, 'TKT70117', 'AO', 'WO014356285', -4712566458, 1, NULL),
(7, 'TKT70117', 'AO', 'WO014356285', -4712566458, 1, NULL),
(8, 'TKT28124', 'WO', 'WO014356285', -4712566458, 1, NULL),
(9, 'TKT28124', 'WO', 'WO014356285', -4712566458, 1, NULL),
(10, 'TKT78969', 'PSB', 'WO014356285', -4712566458, 1, NULL),
(11, 'TKT78969', 'PSB', 'WO014356285', -4712566458, 1, NULL),
(12, 'TKT88815', 'PSB', 'WO014812917', -4712566458, 1, NULL),
(13, 'TKT88815', 'PSB', 'WO014812917', -4712566458, 1, NULL),
(14, 'TKT60324', 'PSB', 'WO014812917', -4712566458, 1, NULL),
(15, 'TKT60324', 'PSB', 'WO014812917', -4712566458, 1, NULL),
(16, 'TKT75484', 'PSB', 'WO014812917', -4712566458, 1, NULL),
(17, 'TKT75484', 'PSB', 'WO014812917', -4712566458, 1, NULL),
(18, 'TKT61918', 'AO', 'WO014812917', -4712566458, 1, NULL),
(19, 'TKT81905', 'AO', 'WO014812917', -4712566458, 1, NULL),
(20, 'TKT28008', 'WO', 'WO014812917', -4712566458, 1, NULL),
(21, 'TKT28008', 'WO', 'WO014812917', -4712566458, 1, NULL),
(22, 'TKT61918', 'AO', 'WO014812917', -4712566458, 1, NULL),
(23, 'TKT81905', 'AO', 'WO014812917', -4712566458, 1, NULL),
(24, 'TKT70660', 'WO', 'WO014812917', -4712566458, 1, NULL),
(25, 'TKT70660', 'WO', 'WO014812917', -4712566458, 1, NULL),
(26, 'TKT77609', 'PSB', 'WO014812917', -4712566458, 1, NULL),
(27, 'TKT77609', 'PSB', 'WO014812917', -4712566458, 1, NULL),
(28, 'TKT72804', 'PSB', 'WO014812917', -4712566458, 1, NULL),
(29, 'TKT72804', 'PSB', 'WO014812917', -4712566458, 1, NULL),
(30, 'TKT90370', 'PSB', 'WO014812917', -4712566458, 1, NULL),
(31, 'TKT90370', 'PSB', 'WO014812917', -4712566458, 1, NULL),
(32, 'TKT19585', 'WO', 'WO014812917', -4712566458, 1, NULL),
(33, 'TKT19585', 'WO', 'WO014812917', -4712566458, 1, NULL),
(34, 'TKT49926', 'PSB', 'WO014812917', -4712566458, 1, NULL),
(35, 'TKT49926', 'PSB', 'WO014812917', -4712566458, 1, NULL),
(36, 'TKT40806', 'PSB', 'WO014812917', -4712566458, 1, NULL),
(37, 'TKT40806', 'PSB', 'WO014812917', -4712566458, 1, NULL),
(38, 'TKT20802', 'WO', 'WO014812917', -4712566458, 1, NULL),
(39, 'TKT20802', 'WO', 'WO014812917', -4712566458, 1, NULL),
(40, 'TKT58326', 'MO', 'WO014812917', -4712566458, 1, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `No` int(11) DEFAULT NULL,
  `Order_ID` varchar(255) NOT NULL,
  `Transaksi` varchar(255) NOT NULL,
  `Keterangan` text DEFAULT NULL,
  `No_Tiket` varchar(10) NOT NULL,
  `ket_validasi` text DEFAULT NULL,
  `Status` enum('Order','Pickup','Close') NOT NULL DEFAULT 'Order',
  `id_telegram` bigint(20) DEFAULT NULL,
  `username_telegram` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`No`, `Order_ID`, `Transaksi`, `Keterangan`, `No_Tiket`, `ket_validasi`, `Status`, `id_telegram`, `username_telegram`) VALUES
(NULL, 'WO', 'WO014812917', 'DGPS250129211923012742835-AOi4250129091923067b57810_12748220-88080439~202501292334127286349~7286349~24114258~3~WSA  ODP-SWT-FAS/047 alasan : odp inputan jarak over  Valins ID: 28176483 Time: 2025-01-30 14:59:42 Summary ODP: ODP-SWT-FAS/047 IP OLT: 172.29.238.135 Slot: 1 Port: 9  SN: ZTEGD816A8E1', 'TKT19585', 'DONE', 'Close', 5675912025, 'fjrulll'),
(NULL, 'WO', 'WO014812917', 'DGPS250122190342073641958 DGPS250129211923012742835-AOi4250129091923067b57810_12748220-88080439~202501292334127286349~7286349~24114258~3~WSA  ODP-SWT-FAS/047 alasan : odp inputan jarak over  Valins ID: 28176483 Time: 2025-01-30 14:59:42 Summary ODP: ODP-SWT-FAS/047 IP OLT: 172.29.238.135 Slot: 1 Port: 9  SN: ZTEGD816A8E1', 'TKT20802', 'oke', 'Close', NULL, NULL),
(9, 'WO', 'WO014812917', 'DGPS250129211923012742835-AOi4250129091923067b57810_12748220-88080439~202501292334127286349~7286349~24114258~3~WSA  ODP-SWT-FAS/047 alasan : odp inputan jarak over  Valins ID: 28176483 Time: 2025-01-30 14:59:42 Summary ODP: ODP-SWT-FAS/047 IP OLT: 172.29.238.135 Slot: 1 Port: 9  SN: ZTEGD816A8E1', 'TKT28008', 'okey', 'Close', NULL, NULL),
(1, 'WO', 'WO014356285', 'DGPS250122190342073641958-AOi42501220703421256b4030_12074348-83746708~202501221950126430406~6430406~21407468~3~WS', 'TKT28124', 'sudah', 'Close', NULL, NULL),
(NULL, 'PSB', 'WO014812917', 'DGPS250122190342073641958 DGPS250129211923012742835-AOi4250129091923067b57810_12748220-88080439~202501292334127286349~7286349~24114258~3~WSA  ODP-SWT-FAS/047 alasan : odp inputan jarak over  Valins ID: 28176483 Time: 2025-01-30 14:59:42 Summary ODP: ODP-SWT-FAS/047 IP OLT: 172.29.238.135 Slot: 1 Port: 9  SN: ZTEGD816A8E1', 'TKT40806', 'sudah selasai', 'Close', NULL, NULL),
(NULL, 'PSB', 'WO014812917', 'DGPS250122190342073641958 DGPS250129211923012742835-AOi4250129091923067b57810_12748220-88080439~202501292334127286349~7286349~24114258~3~WSA  ODP-SWT-FAS/047 alasan : odp inputan jarak over  Valins ID: 28176483 Time: 2025-01-30 14:59:42 Summary ODP: ODP-SWT-FAS/047 IP OLT: 172.29.238.135 Slot: 1 Port: 9  SN: ZTEGD816A8E1', 'TKT49926', 'done', 'Close', 5675912025, 'fjrulll'),
(NULL, 'MO', 'WO014812917', 'DGPS250122190342073641958 DGPS250129211923012742835-AOi4250129091923067b57810_12748220-88080439~202501292334127286349~7286349~24114258~3~WSA  ODP-SWT-FAS/047 alasan : odp inputan jarak over  Valins ID: 28176483 Time: 2025-01-30 14:59:42 Summary ODP: ODP-SWT-FAS/047 IP OLT: 172.29.238.135 Slot: 1 Port: 9  SN: ZTEGD816A8E1', 'TKT58326', NULL, 'Pickup', 5675912025, 'fjrulll'),
(4, 'PSB', 'WO014812917', 'keterangan', 'TKT60324', 'sip', 'Close', NULL, NULL),
(8, 'AO', 'WO014812917', 'DGPS250129211923012742835-AOi4250129091923067b57810_12748220-88080439~202501292334127286349~7286349~24114258~3~WSA', 'TKT61918', 'okey', 'Close', NULL, NULL),
(2, 'AO', 'WO014356285', 'DGPS250122190342073641958-AOi42501220703421256b4030_12074348-83746708~202501221950126430406~6430406~21407468~3~WS', 'TKT70117', 'hallo', 'Close', NULL, NULL),
(10, 'WO', 'WO014812917', 'DGPS250129211923012742835-AOi4250129091923067b57810_12748220-88080439~202501292334127286349~7286349~24114258~3~WSA  ODP-SWT-FAS/047 alasan : odp inputan jarak over  Valins ID: 28176483 Time: 2025-01-30 14:59:42 Summary ODP: ODP-SWT-FAS/047 IP OLT: 172.29.238.135 Slot: 1 Port: 9  SN: ZTEGD816A8E1', 'TKT70660', 'done', 'Close', 5675912025, 'fjrulll'),
(12, 'PSB', 'WO014812917', 'DGPS250129211923012742835-AOi4250129091923067b57810_12748220-88080439~202501292334127286349~7286349~24114258~3~WSA  ODP-SWT-FAS/047 alasan : odp inputan jarak over  Valins ID: 28176483 Time: 2025-01-30 14:59:42 Summary ODP: ODP-SWT-FAS/047 IP OLT: 172.29.238.135 Slot: 1 Port: 9  SN: ZTEGD816A8E1', 'TKT72804', 'done', 'Close', 5675912025, 'fjrulll'),
(6, 'PSB', 'WO014812917', 'DGPS250129211923012742835-AOi4250129091923067b57810_12748220-88080439~202501292334127286349~7286349~24114258~3~WSA', 'TKT75484', 'okey', 'Close', NULL, NULL),
(11, 'PSB', 'WO014812917', 'DGPS250129211923012742835-AOi4250129091923067b57810_12748220-88080439~202501292334127286349~7286349~24114258~3~WSA  ODP-SWT-FAS/047 alasan : odp inputan jarak over  Valins ID: 28176483 Time: 2025-01-30 14:59:42 Summary ODP: ODP-SWT-FAS/047 IP OLT: 172.29.238.135 Slot: 1 Port: 9  SN: ZTEGD816A8E1', 'TKT77609', 'pp', 'Close', 5675912025, 'fjrulll'),
(3, 'PSB', 'WO014356285', 'DGPS250122190342073641958-AOi42501220703421256b4030_12074348-83746708~202501221950126430406~6430406~21407468~3~WS', 'TKT78969', 'selesai', 'Close', NULL, NULL),
(NULL, 'PSB', 'WO014812917', 'DGPS250122190342073641958 DGPS250129211923012742835-AOi4250129091923067b57810_12748220-88080439~202501292334127286349~7286349~24114258~3~WSA  ODP-SWT-FAS/047 alasan : odp inputan jarak over  Valins ID: 28176483 Time: 2025-01-30 14:59:42 Summary ODP: ODP-SWT-FAS/047 IP OLT: 172.29.238.135 Slot: 1 Port: 9  SN: ZTEGD816A8E1', 'TKT80156', NULL, 'Order', NULL, NULL),
(7, 'AO', 'WO014812917', 'DGPS250129211923012742835-AOi4250129091923067b57810_12748220-88080439~202501292334127286349~7286349~24114258~3~WSA', 'TKT81905', 'okey', 'Close', NULL, NULL),
(5, 'PSB', 'WO014812917', 'DGPS250129211923012742835-AOi4250129091923067b57810_12748220-88080439~202501292334127286349~7286349~24114258~3~WSA', 'TKT88815', 'silahkan lengkapi data', 'Close', NULL, NULL),
(NULL, 'PSB', 'WO014812917', 'DGPS250129211923012742835-AOi4250129091923067b57810_12748220-88080439~202501292334127286349~7286349~24114258~3~WSA  ODP-SWT-FAS/047 alasan : odp inputan jarak over  Valins ID: 28176483 Time: 2025-01-30 14:59:42 Summary ODP: ODP-SWT-FAS/047 IP OLT: 172.29.238.135 Slot: 1 Port: 9  SN: ZTEGD816A8E1', 'TKT90370', 'done', 'Close', 5675912025, 'fjrulll');

--
-- Trigger `orders`
--
DELIMITER $$
CREATE TRIGGER `after_status_update` AFTER UPDATE ON `orders` FOR EACH ROW BEGIN
    
    IF NEW.Status = 'Pickup' THEN
        INSERT INTO bot_notifications (No_Tiket, Order_ID, Transaksi, Chat_ID, is_sent)
        VALUES (NEW.No_Tiket, NEW.Order_ID, NEW.Transaksi, -4712566458, 0);
    
    
    ELSEIF NEW.Status = 'Close' THEN
        INSERT INTO bot_notifications (No_Tiket, Order_ID, Transaksi, Chat_ID, is_sent)
        VALUES (NEW.No_Tiket, NEW.Order_ID, NEW.Transaksi, -4712566458, 0);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_activity`
--

CREATE TABLE `order_activity` (
  `id` int(11) NOT NULL,
  `no_tiket` varchar(10) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `order_activity`
--

INSERT INTO `order_activity` (`id`, `no_tiket`, `user_id`, `activity_type`, `timestamp`) VALUES
(1, 'TKT28124', 2, 'Pickup', '2025-01-30 08:04:35'),
(2, 'TKT28124', 2, 'Close', '2025-01-30 08:04:43'),
(3, 'TKT70117', 3, 'Pickup', '2025-01-30 08:15:53'),
(4, 'TKT70117', 3, 'Close', '2025-01-30 08:16:03'),
(5, 'TKT70117', 3, 'Pickup', '2025-01-30 08:33:10'),
(6, 'TKT70117', 3, 'Close', '2025-01-30 08:42:45'),
(7, 'TKT28124', 3, 'Pickup', '2025-01-30 08:48:11'),
(8, 'TKT28124', 3, 'Close', '2025-01-30 08:48:24'),
(9, 'TKT78969', 3, 'Pickup', '2025-01-30 08:51:08'),
(10, 'TKT78969', 3, 'Close', '2025-01-30 08:51:35'),
(11, 'TKT88815', 3, 'Pickup', '2025-01-30 08:58:17'),
(12, 'TKT88815', 3, 'Close', '2025-01-30 08:58:39'),
(13, 'TKT60324', 3, 'Pickup', '2025-01-30 09:01:07'),
(14, 'TKT60324', 3, 'Close', '2025-01-30 09:10:16'),
(15, 'TKT75484', 3, 'Pickup', '2025-01-30 09:28:32'),
(16, 'TKT75484', 3, 'Close', '2025-01-30 09:28:40'),
(17, 'TKT61918', 3, 'Pickup', '2025-01-30 09:38:52'),
(18, 'TKT81905', 3, 'Pickup', '2025-01-30 09:38:53'),
(19, 'TKT28008', 3, 'Pickup', '2025-01-30 09:38:54'),
(20, 'TKT28008', 3, 'Close', '2025-01-30 09:39:29'),
(21, 'TKT61918', 3, 'Close', '2025-01-30 09:39:34'),
(22, 'TKT81905', 3, 'Close', '2025-01-30 09:39:40'),
(23, 'TKT70660', 2, 'Pickup', '2025-01-31 03:09:30'),
(24, 'TKT70660', 2, 'Close', '2025-01-31 03:09:43'),
(25, 'TKT77609', 4, 'Pickup', '2025-01-31 04:32:05'),
(26, 'TKT77609', 4, 'Close', '2025-01-31 04:32:21'),
(27, 'TKT72804', 2, 'Pickup', '2025-01-31 04:37:10'),
(28, 'TKT72804', 2, 'Close', '2025-01-31 04:37:30'),
(29, 'TKT90370', 2, 'Pickup', '2025-01-31 04:47:17'),
(30, 'TKT90370', 2, 'Close', '2025-01-31 04:47:33'),
(31, 'TKT19585', 5, 'Pickup', '2025-01-31 06:28:59'),
(32, 'TKT19585', 5, 'Close', '2025-01-31 06:29:11'),
(33, 'TKT49926', 2, 'Pickup', '2025-02-03 06:20:44'),
(34, 'TKT49926', 2, 'Close', '2025-02-03 06:21:10'),
(35, 'TKT40806', 2, 'Pickup', '2025-02-04 01:29:49'),
(36, 'TKT40806', 2, 'Close', '2025-02-04 01:32:01'),
(37, 'TKT20802', 3, 'Pickup', '2025-02-04 01:40:01'),
(38, 'TKT20802', 2, 'Close', '2025-02-04 01:40:36'),
(39, 'TKT58326', 3, 'Pickup', '2025-02-04 02:09:47');

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_details`
--

CREATE TABLE `order_details` (
  `No_Tiket` varchar(255) NOT NULL,
  `ODP` varchar(255) DEFAULT NULL,
  `Alasan` text DEFAULT NULL,
  `Valins_ID` int(11) DEFAULT NULL,
  `Time` datetime DEFAULT NULL,
  `Summary_ODP` text DEFAULT NULL,
  `IP_OLT` varchar(15) DEFAULT NULL,
  `Slot` int(11) DEFAULT NULL,
  `Port` int(11) DEFAULT NULL,
  `SN` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_messages`
--

CREATE TABLE `order_messages` (
  `id` int(11) NOT NULL,
  `no_tiket` varchar(50) DEFAULT NULL,
  `message_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `order_messages`
--

INSERT INTO `order_messages` (`id`, `no_tiket`, `message_id`) VALUES
(1, 'TKT28124', 198),
(2, 'TKT70117', 212),
(3, 'TKT78969', 222),
(4, 'TKT60324', 229),
(5, 'TKT88815', 231),
(6, 'TKT75484', 239),
(7, 'TKT81905', 243),
(8, 'TKT61918', 245),
(9, 'TKT28008', 247),
(10, 'TKT70660', 255),
(11, 'TKT77609', 259),
(12, 'TKT72804', 273),
(13, 'TKT90370', 277),
(14, 'TKT19585', 287),
(15, 'TKT49926', 291),
(16, 'TKT40806', 299),
(17, 'TKT20802', 306),
(18, 'TKT80156', 310),
(19, 'TKT58326', 312);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `Nama` varchar(255) NOT NULL,
  `ID_Telegram` bigint(20) NOT NULL,
  `Username_Telegram` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `status` enum('active','suspended','inactive') DEFAULT 'inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`ID`, `Nama`, `ID_Telegram`, `Username_Telegram`, `Password`, `status`) VALUES
(2, 'Fajrul', 5675912025, 'Fajrul123', '$2y$10$pdY1pXA4osR8CT0uGfT6x.RYvuSQdPnLz6F3IVCapx8PfU502OYdG', 'active'),
(3, 'atikapuspa', 1357144359, 'atikapuspaa', '$2y$10$ohscTdkHTxTb3bcl461wweuW4.vwk82PF4xY.tlDxaQdJAEbLsJYa', 'active'),
(4, 'putri', 1909036567, 'putri', '$2y$10$O00.gRaliNTw3Kn22N2Oie6hWnpqGxcH/Z.peEZ/P3erpc5Vo11GC', 'active'),
(5, 'M Gea Alrasyid', 151979848, '20920783', '$2y$10$O0Qt2wGjfQSDncVdJo8EwuiP8SdS1e7MN.2DETnwJDzDJxD18Dooi', 'active');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `bot_notifications`
--
ALTER TABLE `bot_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`No_Tiket`),
  ADD UNIQUE KEY `No_Tiket` (`No_Tiket`),
  ADD UNIQUE KEY `No` (`No`);

--
-- Indeks untuk tabel `order_activity`
--
ALTER TABLE `order_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `no_tiket` (`no_tiket`);

--
-- Indeks untuk tabel `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`No_Tiket`);

--
-- Indeks untuk tabel `order_messages`
--
ALTER TABLE `order_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `no_tiket` (`no_tiket`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `ID_Telegram` (`ID_Telegram`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `bot_notifications`
--
ALTER TABLE `bot_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT untuk tabel `order_activity`
--
ALTER TABLE `order_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT untuk tabel `order_messages`
--
ALTER TABLE `order_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `order_activity`
--
ALTER TABLE `order_activity`
  ADD CONSTRAINT `order_activity_ibfk_1` FOREIGN KEY (`no_tiket`) REFERENCES `orders` (`No_Tiket`);

--
-- Ketidakleluasaan untuk tabel `order_messages`
--
ALTER TABLE `order_messages`
  ADD CONSTRAINT `order_messages_ibfk_1` FOREIGN KEY (`no_tiket`) REFERENCES `orders` (`No_Tiket`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
