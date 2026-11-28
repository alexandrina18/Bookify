-- ========================================================
-- Database Export: Perpustakaan SMA GLITZ (Bookify)
-- Format: MySQL / MariaDB (Kompatibel phpMyAdmin)
-- Diekspor pada: 2026-11-28 13:54:44
-- ========================================================

CREATE DATABASE IF NOT EXISTS `bookify` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `bookify`;

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";

-- --------------------------------------------------------
-- Struktur tabel `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `member_id` VARCHAR(150) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(150),
  `gender` VARCHAR(150),
  `address` TEXT,
  `password` TEXT NOT NULL,
  `role` VARCHAR(150) NOT NULL DEFAULT 'member',
  `status` VARCHAR(150) NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `photo_path` VARCHAR(255),
  `nik` VARCHAR(150),
  `nis` VARCHAR(150),
  `kelas` VARCHAR(150)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data untuk tabel `users`
INSERT INTO `users` (`id`, `member_id`, `name`, `email`, `phone`, `gender`, `address`, `password`, `role`, `status`, `created_at`, `updated_at`, `photo_path`, `nik`, `nis`, `kelas`) VALUES
('1', 'ADMIN001', 'Petugas Perpustakaan SMA GLITZ', 'admin@smaglitz.sch.id', '081234567890', 'male', 'Perpustakaan SMA GLITZ, Jl. Glitz No. 1, Semarang', '$2y$10$UUPsV.hWlApV3bRFBNeoA.b1bnUFTqjJpVPJeJnGdtgkmZGp1mZv6', 'admin', 'active', '2026-11-23 00:28:25', '2026-11-23 00:28:25', NULL, NULL, NULL, NULL),
('2', 'MBR001', 'Jolanda Aurelia Susanto', 'jolanda@smaglitz.sch.id', '081122334455', 'female', 'Jl. Merdeka No. 15, Semarang', '$2y$10$I26b8GjGVTV96XrYyjLdx.pijq13rVTYqOXPtXFdo38rEyB9YYsP2', 'member', 'active', '2026-11-23 00:28:25', '2026-11-23 00:28:25', NULL, '3374012345670001', '20241001', 'XII MIPA 1'),
('3', 'MBR002', 'Theodora Trixie Sundari', 'theodora@smaglitz.sch.id', '082233445566', 'female', 'Jl. Pahlawan No. 28, Semarang', '$2y$10$nmwpK6LK1QzLsKbpt7en7.mSJVezF4X1LWRHYUqpGUI9aZ3DkHUUW', 'member', 'active', '2026-11-23 00:28:25', '2026-11-23 00:28:25', NULL, '3374012345670002', '20241002', 'XII IPS 1'),
('4', 'MBR003', 'Alexandrina Immanuela Sahar', 'alexandrina@smaglitz.sch.id', '083344556677', 'female', 'Jl. Pemuda No. 102, Semarang', '$2y$10$dlFX7c/SMMAlM5p2DLrH4.ebcQpd9oVW0BH0Ms0b8IId143qMU7PW', 'member', 'active', '2026-11-23 00:28:25', '2026-11-23 00:28:25', NULL, '3374012345670003', '20241003', 'XII IPS 2'),
('5', 'MBR004', 'Bonifasius Frith Farandy', 'bonifasius@smaglitz.sch.id', '084455667788', 'male', 'Jl. Diponegoro No. 44, Semarang', '$2y$10$nXlvWmxMmfn2c1IrvCjTGOEeLWK60qeZRGO1LgrUbv6wQlo9KJJ.C', 'member', 'active', '2026-11-23 00:28:25', '2026-11-23 00:28:25', NULL, '3374012345670004', '20241004', 'XI MIPA 2'),
('6', 'MBR005', 'Thomas Nova Wibisono', 'thomas@smaglitz.sch.id', '085566778899', 'male', 'Jl. Sudirman No. 89, Semarang', '$2y$10$btRqyS7T8yozttoFpWOF0eHjJq2lDgLREL5EbyAPAR7m2y.DCs31q', 'member', 'active', '2026-11-23 00:28:26', '2026-11-23 00:28:26', NULL, '3374012345670005', '20241005', 'XI IPS 1'),
('7', 'MBR006', 'Aditiya Pratama', 'aditiya@smaglitz.sch.id', '081234567801', 'male', 'Jl. Gajah Mada No. 17, Semarang', '$2y$10$wHaAjBgm0BYrc5eAKMzxk.EiyFwhw4X1gzzJrX2eRVQ5tKE2A6mV.', 'member', 'active', '2026-11-28 04:18:41', '2026-11-28 04:18:41', NULL, '3374012345670006', '20251006', 'X-A'),
('8', 'MBR007', 'Clarissa Putri Anindya', 'clarissa@smaglitz.sch.id', '081234567802', 'female', 'Jl. Pandanaran No. 56, Semarang', '$2y$10$wHaAjBgm0BYrc5eAKMzxk.EiyFwhw4X1gzzJrX2eRVQ5tKE2A6mV.', 'member', 'active', '2026-11-28 04:18:41', '2026-11-28 04:18:41', NULL, '3374012345670007', '20251007', 'X-B'),
('9', 'MBR008', 'Dimas Arya Wicaksana', 'dimas@smaglitz.sch.id', '081234567803', 'male', 'Jl. Erlangga Tengah No. 9, Semarang', '$2y$10$wHaAjBgm0BYrc5eAKMzxk.EiyFwhw4X1gzzJrX2eRVQ5tKE2A6mV.', 'member', 'active', '2026-11-28 04:18:41', '2026-11-28 04:18:41', NULL, '3374012345670008', '20241008', 'XI MIPA 3'),
('10', 'MBR009', 'Felicia Evelyn Hartanto', 'felicia@smaglitz.sch.id', '081234567804', 'female', 'Jl. Sumbing No. 14, Semarang', '$2y$10$wHaAjBgm0BYrc5eAKMzxk.EiyFwhw4X1gzzJrX2eRVQ5tKE2A6mV.', 'member', 'active', '2026-11-28 04:18:41', '2026-11-28 04:18:41', NULL, '3374012345670009', '20231009', 'XII MIPA 2'),
('11', 'MBR010', 'Kevin Jonathan Kusumo', 'kevin@smaglitz.sch.id', '081234567805', 'male', 'Jl. Siranda No. 3, Semarang', '$2y$10$wHaAjBgm0BYrc5eAKMzxk.EiyFwhw4X1gzzJrX2eRVQ5tKE2A6mV.', 'member', 'active', '2026-11-28 04:18:41', '2026-11-28 04:18:41', NULL, '3374012345670010', '20251010', 'X-C');

-- --------------------------------------------------------
-- Struktur tabel `publishers`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `publishers`;
CREATE TABLE `publishers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `location` VARCHAR(255),
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data untuk tabel `publishers`
INSERT INTO `publishers` (`id`, `name`, `location`, `created_at`, `updated_at`) VALUES
('1', 'Gramedia Pustaka Utama', 'Semarang', '2026-11-23 00:28:26', '2026-11-23 00:28:26'),
('2', 'Erlangga', 'Semarang', '2026-11-23 00:28:26', '2026-11-23 00:28:26'),
('3', 'Mizan', 'Semarang', '2026-11-23 00:28:26', '2026-11-23 00:28:26'),
('4', 'Kompas', 'Semarang', '2026-11-23 00:28:26', '2026-11-23 00:28:26');

-- --------------------------------------------------------
-- Struktur tabel `books`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `books`;
CREATE TABLE `books` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `book_id` VARCHAR(150) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `author` VARCHAR(255) NOT NULL,
  `year` INT,
  `pages` INT,
  `genre` TEXT,
  `stock` INT NOT NULL DEFAULT 1,
  `available` INT NOT NULL DEFAULT 1,
  `cover_url` VARCHAR(255),
  `publisher_id` INT,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `synopsis` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data untuk tabel `books`
INSERT INTO `books` (`id`, `book_id`, `title`, `author`, `year`, `pages`, `genre`, `stock`, `available`, `cover_url`, `publisher_id`, `created_at`, `updated_at`, `synopsis`) VALUES
('1', 'BK001', 'Laskar Pelangi', 'Andrea Hirata', '2005', '529', 'Novel', '3', '2', 'assets/covers/BK001.jpg', '3', '2026-11-23 00:28:26', '2026-11-23 00:28:26', NULL),
('2', 'BK002', 'Bumi Manusia', 'Pramoedya Ananta Toer', '1980', '535', 'Novel', '2', '2', 'assets/covers/BK002.jpg', '1', '2026-11-23 00:28:26', '2026-11-23 00:28:26', NULL),
('3', 'BK003', 'Pemrograman Web dengan Next.js', 'Ahmad Fauzi', '2023', '420', 'Teknologi', '4', '3', 'assets/covers/BK003.jpg', '2', '2026-11-23 00:28:26', '2026-11-23 00:28:26', NULL),
('4', 'BK004', 'Dasar-dasar Sistem Informasi', 'Budi Sutedjo', '2022', '310', 'Teknologi', '5', '4', 'assets/covers/BK004.jpg', '2', '2026-11-23 00:28:26', '2026-11-23 00:28:26', NULL),
('5', 'BK005', 'Filosofi Teras', 'Henry Manampiring', '2019', '316', 'Filsafat', '3', '0', 'assets/covers/BK005.jpg', '1', '2026-11-23 00:28:26', '2026-11-23 00:28:26', NULL),
('6', 'BK006', 'Atomic Habits', 'James Clear', '2018', '319', 'Pengembangan Diri', '4', '4', 'assets/covers/BK006.jpg', '1', '2026-11-23 00:28:26', '2026-11-23 00:28:26', NULL),
('7', 'BK007', 'Sapiens: Riwayat Singkat Umat Manusia', 'Yuval Noah Harari', '2011', '512', 'Sejarah', '2', '2', 'assets/covers/BK007.jpg', '1', '2026-11-23 00:28:26', '2026-11-23 00:28:26', NULL),
('8', 'BK008', 'The Alchemist', 'Paulo Coelho', '1988', '197', 'Novel', '3', '2', 'assets/covers/BK008.jpg', '3', '2026-11-23 00:28:26', '2026-11-23 00:28:26', NULL),
('9', 'BK009', 'Pengantar Basis Data', 'Raghu Ramakrishnan', '2020', '440', 'Teknologi', '3', '2', 'assets/covers/BK009.jpg', '2', '2026-11-23 00:28:26', '2026-11-23 00:28:26', NULL),
('10', 'BK010', 'Rich Dad Poor Dad', 'Robert T. Kiyosaki', '1997', '207', 'Keuangan', '2', '2', 'assets/covers/BK010.jpg', '4', '2026-11-23 00:28:26', '2026-11-23 00:28:26', NULL),
('11', 'BK011', 'Negeri 5 Menara', 'Ahmad Fuadi', '2009', '423', 'Novel', '2', '2', 'assets/covers/BK011.jpg', '1', '2026-11-23 00:28:26', '2026-11-23 00:28:26', NULL),
('12', 'BK012', 'Desain UI/UX Modern', 'Siti Rahma', '2024', '280', 'Teknologi', '3', '0', 'assets/covers/BK012.jpg', '2', '2026-11-23 00:28:26', '2026-11-23 00:28:26', NULL);

-- --------------------------------------------------------
-- Struktur tabel `borrowings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `borrowings`;
CREATE TABLE `borrowings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `book_id` INT NOT NULL,
  `borrowed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `due_date` DATETIME NOT NULL,
  `returned_at` DATETIME,
  `status` VARCHAR(150) NOT NULL DEFAULT 'active',
  `renew_count` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fulfillment_method` VARCHAR(150),
  `pickup_deadline` TEXT,
  `shipping_fee` INT NOT NULL DEFAULT 0,
  `receipt_no` TEXT,
  `estimated_delivery` TEXT,
  `payment_method` VARCHAR(150),
  `paid_at` DATETIME,
  `delivery_address` TEXT,
  `payment_deadline` TEXT,
  `history_visible` INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data untuk tabel `borrowings`
INSERT INTO `borrowings` (`id`, `user_id`, `book_id`, `borrowed_at`, `due_date`, `returned_at`, `status`, `renew_count`, `created_at`, `updated_at`, `fulfillment_method`, `pickup_deadline`, `shipping_fee`, `receipt_no`, `estimated_delivery`, `payment_method`, `paid_at`, `delivery_address`, `payment_deadline`, `history_visible`) VALUES
('1', '2', '1', '2026-11-13 00:28:26', '2026-11-20 00:28:26', NULL, 'overdue', '0', '2026-11-23 00:28:26', '2026-11-23 00:28:26', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '1'),
('2', '3', '5', '2026-11-20 00:28:26', '2026-11-27 00:28:26', NULL, 'overdue', '1', '2026-11-23 00:28:26', '2026-11-23 00:28:26', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '1'),
('3', '4', '9', '2026-11-09 00:28:26', '2026-11-16 00:28:26', '2026-11-16 00:28:26', 'returned', '0', '2026-11-23 00:28:26', '2026-11-23 00:28:26', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '1'),
('4', '3', '5', '2026-11-23 03:16:23', '2026-11-30 03:16:23', NULL, 'active', '0', '2026-11-23 03:16:23', '2026-11-23 03:16:23', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '1'),
('5', '2', '2', '2026-11-27 09:22:55', '2026-11-27 17:00:00', NULL, 'cancelled', '0', '2026-11-27 02:22:55', '2026-11-27 14:18:22', 'pickup', '2026-11-27 17:00:00', '0', NULL, NULL, NULL, NULL, NULL, NULL, '1'),
('6', '2', '12', '2026-11-27 09:24:55', '2026-12-06 17:00:00', NULL, 'in_transit', '0', '2026-11-27 02:24:55', '2026-11-27 02:24:55', 'delivery', NULL, '15000', 'BKP2611271F3CF9', '2026-11-29 17:00:00', 'qris', '2026-11-27 09:25:00', 'Jl. Merdeka No. 1', NULL, '1'),
('7', '2', '8', '2026-11-27 09:25:39', '2026-12-06 17:00:00', NULL, 'awaiting_payment', '0', '2026-11-27 02:25:39', '2026-11-27 02:25:39', 'delivery', NULL, '15000', 'BKP2611275664A4', '2026-11-29 17:00:00', NULL, NULL, 'Jl. Merdeka No. 1', NULL, '1'),
('8', '2', '4', '2026-11-25 10:12:50', '2026-12-02 10:12:50', NULL, 'active', '0', '2026-11-27 03:12:50', '2026-11-27 03:12:50', 'pickup', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('9', '2', '2', '2026-11-27 13:47:11', '2026-11-27 17:00:00', NULL, 'cancelled', '0', '2026-11-27 06:47:11', '2026-11-27 14:18:22', 'pickup', '2026-11-27 17:00:00', '0', NULL, NULL, NULL, NULL, NULL, NULL, '1'),
('10', '2', '4', '2026-11-27 21:28:13', '2026-11-28 13:00:00', NULL, 'cancelled', '0', '2026-11-27 14:28:13', '2026-11-28 06:49:04', 'pickup', '2026-11-28 13:00:00', '0', NULL, NULL, NULL, NULL, NULL, NULL, '1'),
('11', '2', '12', '2026-11-27 21:28:27', '2026-12-06 17:00:00', NULL, 'in_transit', '0', '2026-11-27 14:28:27', '2026-11-27 14:28:27', 'delivery', NULL, '15000', 'BKP261127734A19', '2026-11-29 17:00:00', 'virtual_account', '2026-11-27 21:28:39', 'Jl. Merdeka No. 1, Semarang', '2026-11-28 00:28:27', '1'),
('12', '2', '12', '2026-11-27 21:37:17', '2026-12-06 17:00:00', NULL, 'in_transit', '0', '2026-11-27 14:37:17', '2026-11-27 14:37:17', 'delivery', NULL, '15000', 'BKP261127A277E3', '2026-11-29 17:00:00', 'virtual_account', '2026-11-27 21:39:32', 'jl.merdeka, candisari, semarang', '2026-11-28 00:37:17', '1'),
('13', '2', '6', '2026-11-27 21:42:55', '2026-12-06 17:00:00', NULL, 'cancelled', '0', '2026-11-27 14:42:55', '2026-11-28 04:44:56', 'delivery', NULL, '15000', 'BKP261127E71161', '2026-11-29 17:00:00', NULL, NULL, 'Jl. Merdeka No. 1, candisari, semarang', '2026-11-28 00:42:55', '1'),
('14', '2', '6', '2026-11-27 21:55:31', '2026-11-28 13:00:00', NULL, 'cancelled', '0', '2026-11-27 14:55:31', '2026-11-28 06:49:04', 'pickup', '2026-11-28 13:00:00', '0', NULL, NULL, NULL, NULL, NULL, NULL, '1'),
('15', '2', '2', '2026-11-27 21:55:58', '2026-11-28 13:00:00', NULL, 'cancelled', '0', '2026-11-27 14:55:58', '2026-11-28 06:49:04', 'pickup', '2026-11-28 13:00:00', '0', NULL, NULL, NULL, NULL, NULL, NULL, '1');

-- --------------------------------------------------------
-- Struktur tabel `fines`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `fines`;
CREATE TABLE `fines` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `borrowing_id` INT,
  `days_late` INT NOT NULL DEFAULT 0,
  `amount` INT NOT NULL,
  `book_price` INT,
  `status` VARCHAR(150) NOT NULL DEFAULT 'unpaid',
  `type` VARCHAR(150) NOT NULL DEFAULT 'late',
  `notes` TEXT,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data untuk tabel `fines`
INSERT INTO `fines` (`id`, `user_id`, `borrowing_id`, `days_late`, `amount`, `book_price`, `status`, `type`, `notes`, `created_at`, `updated_at`) VALUES
('1', '2', '1', '3', '3000', NULL, 'unpaid', 'late', NULL, '2026-11-23 00:28:26', '2026-11-23 00:28:26');

-- --------------------------------------------------------
-- Struktur tabel `reviews`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `book_id` INT NOT NULL,
  `rating` INT NOT NULL,
  `review` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Struktur tabel `budgets`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `budgets`;
CREATE TABLE `budgets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `amount` INT NOT NULL,
  `type` VARCHAR(150) NOT NULL,
  `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data untuk tabel `budgets`
INSERT INTO `budgets` (`id`, `category`, `description`, `amount`, `type`, `date`, `created_at`, `updated_at`) VALUES
('1', 'salary', 'Gaji Project Manager (2 bulan)', '16000000', 'expense', '2026-11-23 00:28:26', '2026-11-23 00:28:26', '2026-11-23 00:28:26'),
('2', 'salary', 'Gaji System Analyst & Designer (2 bulan)', '14000000', 'expense', '2026-11-23 00:28:26', '2026-11-23 00:28:26', '2026-11-23 00:28:26'),
('3', 'salary', 'Gaji Developer Backend (2 bulan)', '16000000', 'expense', '2026-11-23 00:28:26', '2026-11-23 00:28:26', '2026-11-23 00:28:26'),
('4', 'salary', 'Gaji Developer Frontend (2 bulan)', '14000000', 'expense', '2026-11-23 00:28:26', '2026-11-23 00:28:26', '2026-11-23 00:28:26'),
('5', 'salary', 'Gaji Tester (1 bulan)', '5000000', 'expense', '2026-11-23 00:28:26', '2026-11-23 00:28:26', '2026-11-23 00:28:26'),
('6', 'infrastructure', 'Hosting/VPS 1 tahun, domain, email', '5000000', 'expense', '2026-11-23 00:28:26', '2026-11-23 00:28:26', '2026-11-23 00:28:26'),
('7', 'equipment', '2 scanner barcode, 2 webcam, printer, stiker', '5500000', 'expense', '2026-11-23 00:28:26', '2026-11-23 00:28:26', '2026-11-23 00:28:26'),
('8', 'books', 'Pengadaan ~200 buku awal', '15000000', 'expense', '2026-11-23 00:28:26', '2026-11-23 00:28:26', '2026-11-23 00:28:26'),
('9', 'operations', 'Biaya operasional 3 bulan awal', '7500000', 'expense', '2026-11-23 00:28:26', '2026-11-23 00:28:26', '2026-11-23 00:28:26');

-- --------------------------------------------------------
-- Struktur tabel `extensions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `extensions`;
CREATE TABLE `extensions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `borrowing_id` INT NOT NULL,
  `requested_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `new_due_date` DATETIME,
  `status` VARCHAR(150) NOT NULL DEFAULT 'pending',
  `admin_note` TEXT,
  `reviewed_at` DATETIME,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reason` TEXT,
  `duration_days` INT NOT NULL DEFAULT 7
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data untuk tabel `extensions`
INSERT INTO `extensions` (`id`, `user_id`, `borrowing_id`, `requested_at`, `new_due_date`, `status`, `admin_note`, `reviewed_at`, `created_at`, `updated_at`, `reason`, `duration_days`) VALUES
('2', '2', '8', '2026-11-27 10:19:43', NULL, 'pending', NULL, NULL, '2026-11-27 03:19:43', '2026-11-27 03:19:43', 'belum selesai membaca', '3');

-- --------------------------------------------------------
-- Struktur tabel `complaints`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `complaints`;
CREATE TABLE `complaints` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `book_id` INT NOT NULL,
  `type` VARCHAR(150) NOT NULL,
  `description` TEXT,
  `status` VARCHAR(150) NOT NULL DEFAULT 'pending',
  `admin_note` TEXT,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `photo_path` VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data untuk tabel `complaints`
INSERT INTO `complaints` (`id`, `user_id`, `book_id`, `type`, `description`, `status`, `admin_note`, `created_at`, `updated_at`, `photo_path`) VALUES
('1', '2', '1', 'lainnya', 'coba', 'pending', NULL, '2026-11-27 01:45:10', '2026-11-27 01:45:10', NULL);

-- --------------------------------------------------------
-- Struktur tabel `attendances`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `attendances`;
CREATE TABLE `attendances` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `check_in` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `check_out` DATETIME,
  `duration` INT,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data untuk tabel `attendances`
INSERT INTO `attendances` (`id`, `user_id`, `check_in`, `check_out`, `duration`, `created_at`) VALUES
('1', '2', '2026-11-22 22:28:26', NULL, NULL, '2026-11-23 00:28:26'),
('2', '3', '2026-11-27 07:47:37', NULL, NULL, '2026-11-27 07:47:37');

SET FOREIGN_KEY_CHECKS = 1;
