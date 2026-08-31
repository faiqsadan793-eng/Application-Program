-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 30, 2026 at 09:05 AM
-- Server version: 8.4.3
-- PHP Version: 8.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_eklinik_ta`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dokters`
--

CREATE TABLE `dokters` (
  `id_dokter` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `nip_sip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `poli` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dokters`
--

INSERT INTO `dokters` (`id_dokter`, `user_id`, `nip_sip`, `poli`, `created_at`, `updated_at`) VALUES
(1, 4, 'SIP. 446/002/DS/2026', 'Poli Anak', '2026-08-10 05:55:39', '2026-08-10 05:55:39'),
(2, 5, 'SIP. 447/003/DS/2026', 'Poli Gigi', '2026-08-12 17:46:26', '2026-08-12 17:46:26'),
(10, 33, 'SIP. 450/004/DS/2026', 'Poli Umum', '2026-08-28 04:57:51', '2026-08-28 04:57:51');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kunjungans`
--

CREATE TABLE `kunjungans` (
  `id_kunjungan` bigint UNSIGNED NOT NULL,
  `id_pasien` bigint UNSIGNED NOT NULL,
  `tgl_kunjungan` date NOT NULL,
  `active_tgl_kunjungan` date DEFAULT NULL,
  `status` enum('antre','menunggu_dokter','siap_bayar','selesai') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'antre',
  `poli_tujuan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kunjungans`
--

INSERT INTO `kunjungans` (`id_kunjungan`, `id_pasien`, `tgl_kunjungan`, `active_tgl_kunjungan`, `status`, `poli_tujuan`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-08-06', '2026-08-06', 'antre', 'Poli Umum', '2026-08-06 00:04:09', '2026-08-06 00:04:09'),
(2, 2, '2026-08-07', '2026-08-07', 'antre', 'Poli Gigi', '2026-08-07 04:50:34', '2026-08-07 04:50:34'),
(3, 3, '2026-08-07', '2026-08-07', 'antre', 'Poli Umum', '2026-08-07 04:56:52', '2026-08-07 04:56:52'),
(5, 1, '2026-08-07', '2026-08-07', 'antre', 'Poli Gigi', '2026-08-07 05:00:52', '2026-08-07 05:00:52'),
(6, 2, '2026-08-10', '2026-08-10', 'antre', 'Poli Umum', '2026-08-09 17:35:35', '2026-08-09 17:35:35'),
(7, 4, '2026-08-10', '2026-08-10', 'antre', 'Poli Umum', '2026-08-09 23:13:20', '2026-08-09 23:13:20'),
(8, 5, '2026-08-10', '2026-08-10', 'antre', 'Poli Umum', '2026-08-09 23:15:35', '2026-08-09 23:15:35'),
(9, 6, '2026-08-13', NULL, 'selesai', 'Poli Anak', '2026-08-12 17:36:08', '2026-08-13 05:29:31'),
(10, 7, '2026-08-13', '2026-08-13', 'antre', 'Poli Gigi', '2026-08-12 17:47:47', '2026-08-12 17:47:47'),
(11, 7, '2026-08-18', NULL, 'selesai', 'Poli Gigi', '2026-08-18 05:58:14', '2026-08-18 06:01:37'),
(12, 8, '2026-08-19', NULL, 'selesai', 'Poli Umum', '2026-08-19 06:19:58', '2026-08-19 06:21:48'),
(13, 8, '2026-08-20', NULL, 'selesai', 'Poli Anak', '2026-08-19 20:57:20', '2026-08-19 21:00:12'),
(24, 19, '2026-08-26', NULL, 'selesai', 'Poli Anak', '2026-08-26 00:07:57', '2026-08-26 00:11:39'),
(25, 19, '2026-08-26', NULL, 'selesai', 'Poli Anak', '2026-08-26 00:12:32', '2026-08-26 00:14:11'),
(26, 8, '2026-08-26', NULL, 'selesai', 'Poli Anak', '2026-08-26 00:12:40', '2026-08-26 00:14:30'),
(27, 2, '2026-08-28', NULL, 'selesai', 'Poli Anak', '2026-08-28 05:00:19', '2026-08-28 05:11:32'),
(28, 20, '2026-08-28', NULL, 'selesai', 'Poli Umum', '2026-08-28 05:02:28', '2026-08-28 05:08:43');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_07_23_123813_create_pasiens_table', 2),
(5, '2026_07_23_123814_create_kunjungans_table', 2),
(6, '2026_07_23_123815_create_rekam_medises_table', 2),
(7, '2026_07_23_123816_create_transaksis_table', 2),
(8, '2026_07_24_121802_add_role_to_users_table', 3),
(9, '2026_07_24_130000_add_columns_to_pasiens_table', 4),
(10, '2026_08_06_140000_add_jenis_kelamin_to_pasiens_table', 5),
(11, '2026_08_07_122713_add_unique_constraint_to_kunjungans_table', 6),
(12, '2026_08_10_000000_create_dokters_table', 7),
(13, '2026_08_13_122348_add_details_to_transaksis_table', 8),
(14, '2026_08_13_123428_add_metode_pembayaran_to_transaksis_table', 9),
(15, '2026_08_18_000000_strengthen_medical_records_integrity', 9),
(16, '2026_08_19_000000_allow_repeat_visits_after_completion', 10);

-- --------------------------------------------------------

--
-- Table structure for table `pasiens`
--

CREATE TABLE `pasiens` (
  `id_pasien` bigint UNSIGNED NOT NULL,
  `no_rm` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `jenis_kelamin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nik` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pekerjaan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pasiens`
--

INSERT INTO `pasiens` (`id_pasien`, `no_rm`, `nama`, `tanggal_lahir`, `jenis_kelamin`, `no_hp`, `nik`, `pekerjaan`, `alamat`, `created_at`, `updated_at`) VALUES
(1, 'RM-000001', 'Syahda contoh', '2006-06-06', 'Laki-laki', '089765432199', '0980986576355', 'Programmer', 'Jl. Swadaya', '2026-08-06 00:04:09', '2026-08-06 00:04:09'),
(2, 'RM-000002', 'ihsa', '2026-08-20', 'Laki-laki', '089765432199', '90192301923', 'Programmer', 'weded', '2026-08-07 04:50:34', '2026-08-07 04:50:34'),
(3, 'RM-000003', 'aasdw', '2026-08-08', 'Laki-laki', '08976543209', '0980986576355', 'Programmer', 'csdcsdc', '2026-08-07 04:56:52', '2026-08-07 04:56:52'),
(4, 'RM-000004', 'radit', '2023-06-09', 'Laki-laki', '0875757575757', '0924928493923942', 'Programmer', 'kebon kebonan', '2026-08-09 23:13:20', '2026-08-09 23:13:20'),
(5, 'RM-000005', 'djewd', '2026-08-06', 'Laki-laki', '0875757575757', '3242342434343434', 'Programmer', 'wewe3e', '2026-08-09 23:15:35', '2026-08-09 23:15:35'),
(6, 'RM-000006', 'Romeo Adit', '2022-07-14', 'Laki-laki', '085718545730', '0972663782348277', 'Karyawan Swasta', 'Jl.Serua Bojongsari', '2026-08-12 17:36:08', '2026-08-12 17:36:08'),
(7, 'RM-000007', 'Mahfud', '2026-08-01', 'Laki-laki', '0834657765432', '0923480928340293', 'Programmer', 'Jl.Kebon Rambutah', '2026-08-12 17:47:47', '2026-08-12 17:47:47'),
(8, 'RM-000008', 'Mila', '2014-04-04', 'Perempuan', '08976547788', '0980986572992173', 'Karyawan Swasta', 'Jl. Pendidikan', '2026-08-19 06:19:58', '2026-08-19 06:19:58'),
(19, 'RM-000019', 'Mafaza', '2026-08-01', 'Laki-laki', '089765438865', '0980986579987878', 'Karyawan Swasta', 'Jl. Bojongsari', '2026-08-26 00:07:57', '2026-08-26 00:07:57'),
(20, 'RM-000020', 'Irfan Gunawan', '2002-07-16', 'Laki-laki', '086788909087', '0980986579999999', 'Karyawan Swasta', 'Jl. Mandor Tajir, Serua, Bojongsari, Depok', '2026-08-28 05:02:28', '2026-08-28 05:02:28');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rekam_medises`
--

CREATE TABLE `rekam_medises` (
  `id_rm` bigint UNSIGNED NOT NULL,
  `id_kunjungan` bigint UNSIGNED NOT NULL,
  `id_dokter` bigint UNSIGNED DEFAULT NULL,
  `nama_dokter` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keluhan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `diagnosa` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `resep_obat` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rekam_medises`
--

INSERT INTO `rekam_medises` (`id_rm`, `id_kunjungan`, `id_dokter`, `nama_dokter`, `keluhan`, `diagnosa`, `resep_obat`, `created_at`, `updated_at`) VALUES
(1, 9, NULL, NULL, 'Kepala Pusing', 'Migran', 'Panadol', '2026-08-13 05:01:36', '2026-08-13 05:01:36'),
(2, 11, 2, 'dr. Rizki', 'Panas 40 derajat\r\nPusing\r\nMual', 'Demam Berdarah', 'wedwefd', '2026-08-18 06:00:18', '2026-08-18 06:00:18'),
(3, 12, NULL, 'dr. Nafeesa Azalia', 'kjbkj', 'iugiu', 'jhvjh', '2026-08-19 06:20:47', '2026-08-19 06:20:47'),
(4, 13, 1, 'dr. Hibrizi Arkan', 'hvnhvhj', 'gbcgb', 'vbcgvcb', '2026-08-19 20:59:06', '2026-08-19 20:59:06'),
(6, 24, 1, 'dr. Hibrizi Arkan', 'sjkas', 'dwqjk', 'dkwjndk', '2026-08-26 00:09:46', '2026-08-26 00:09:46'),
(7, 25, 1, 'dr. Hibrizi Arkan', 'erferf', 'wefewf', 'erferf', '2026-08-26 00:13:03', '2026-08-26 00:13:03'),
(8, 26, 1, 'dr. Hibrizi Arkan', 'feferf', 'eferf', 'sasdx', '2026-08-26 00:13:28', '2026-08-26 00:13:28'),
(9, 28, 10, 'Syahdan', 'Panas dan tenggorokan sakit', 'Radang dan Demam', 'Sanmol', '2026-08-28 05:07:28', '2026-08-28 05:07:28'),
(10, 27, 1, 'dr. Hibrizi Arkan', 'jasnk', 'asdxa', 'asd', '2026-08-28 05:10:59', '2026-08-28 05:10:59');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('5yrI0PyAm3SKHruKLGINYhSaXahVEyLOrtbJVpCC', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJ5RmtydGZvNGdBUXpGcm9iVFFqMERjd1M5NmhuRWk3ZHBrZWZCcXMwIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9yaXdheWF0LXRyYW5zYWtzaSIsInJvdXRlIjoicml3YXlhdC10cmFuc2Frc2kuaW5kZXgifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MX0=', 1787919101),
('oHMklgOK2yLem14rklb0Lhof1Dk0akyp7KKk5yJn', 4, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', 'eyJfdG9rZW4iOiJMTFlzR2dtTDR3bmlQZFo0eHdiTlFFdk90TEJOR2Jnd2E5bGJuRFpMIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL3Jla2FtLW1lZGlzLXBhc2llbiIsInJvdXRlIjoicmVrYW0tbWVkaXMtcGFzaWVuLmluZGV4In0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjo0fQ==', 1787919070);

-- --------------------------------------------------------

--
-- Table structure for table `transaksis`
--

CREATE TABLE `transaksis` (
  `id_transaksi` bigint UNSIGNED NOT NULL,
  `id_kunjungan` bigint UNSIGNED NOT NULL,
  `biaya_tindakan` decimal(12,2) NOT NULL DEFAULT '0.00',
  `biaya_obat` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_biaya` decimal(12,2) NOT NULL DEFAULT '0.00',
  `uang_dibayar` decimal(12,2) NOT NULL DEFAULT '0.00',
  `kembalian` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status_pembayaran` enum('lunas','belum') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'belum',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaksis`
--

INSERT INTO `transaksis` (`id_transaksi`, `id_kunjungan`, `biaya_tindakan`, `biaya_obat`, `total_biaya`, `uang_dibayar`, `kembalian`, `status_pembayaran`, `created_at`, `updated_at`) VALUES
(1, 9, 50000.00, 12000.00, 62000.00, 100000.00, 38000.00, 'lunas', '2026-08-13 05:01:36', '2026-08-13 05:29:31'),
(2, 11, 50000.00, 45000.00, 95000.00, 100000.00, 5000.00, 'lunas', '2026-08-18 06:00:18', '2026-08-18 06:01:37'),
(3, 12, 50000.00, 75000.00, 125000.00, 135000.00, 10000.00, 'lunas', '2026-08-19 06:20:47', '2026-08-19 06:21:48'),
(4, 13, 50000.00, 25000.00, 75000.00, 100000.00, 25000.00, 'lunas', '2026-08-19 20:59:06', '2026-08-19 21:00:12'),
(7, 24, 50000.00, 25000.00, 75000.00, 90000.00, 15000.00, 'lunas', '2026-08-26 00:09:46', '2026-08-26 00:11:39'),
(8, 25, 50000.00, 50000.00, 100000.00, 120000.00, 20000.00, 'lunas', '2026-08-26 00:13:03', '2026-08-26 00:14:11'),
(9, 26, 50000.00, 40000.00, 90000.00, 125000.00, 35000.00, 'lunas', '2026-08-26 00:13:28', '2026-08-26 00:14:30'),
(10, 28, 50000.00, 45000.00, 95000.00, 110000.00, 15000.00, 'lunas', '2026-08-28 05:07:28', '2026-08-28 05:08:43'),
(11, 27, 50000.00, 10000.00, 60000.00, 75000.00, 15000.00, 'lunas', '2026-08-28 05:10:59', '2026-08-28 05:11:32');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('dokter','staff') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Kasih', 'kasih@demo.com', 'staff', '2026-08-06 00:11:29', '$2y$12$OZa0QdqGSlzY6DETZbhdbekAADXI/xndCZWHvY.8Lg6FsRKvUIzi6', NULL, '2026-07-24 05:18:39', '2026-08-06 00:11:29'),
(2, 'dr. Budi Santoso', 'dokter@klinik.com', 'dokter', '2026-08-06 00:12:02', '$2y$12$zG/ifNzRCGPZm/GAFIx4MOk91yJ6XXL8koAlo8Jd/EA2F12BIdCNq', NULL, '2026-08-06 00:12:02', '2026-08-06 00:12:02'),
(3, 'Siti Rahma (Staff)', 'staff@klinik.com', 'staff', '2026-08-06 00:12:02', '$2y$12$N/6CMC32rGYKaUf8NzFhsOO73M4QqI92IyB4cYTIPTIShkUu8XCey', NULL, '2026-08-06 00:12:02', '2026-08-06 00:12:02'),
(4, 'dr. Hibrizi Arkan', 'arkan@klinik.com', 'dokter', NULL, '$2y$12$vwXYKNGYNSqMDX0Pqb6qyOZwZgF.9FknEaTaCIlVHng/7.6sFXAnK', NULL, '2026-08-10 05:55:39', '2026-08-10 05:55:39'),
(5, 'dr. Rizki', 'riski@klinik.com', 'dokter', NULL, '$2y$12$X1uhFYZMVIMm.0r6Q3WbLeGBMyasCYUUQwVRZ4zPKM2iL9B4wSg9C', NULL, '2026-08-12 17:46:26', '2026-08-12 17:46:26'),
(33, 'Syahdan', 'syahdan@klinik.com', 'dokter', NULL, '$2y$12$KGS48VMjnRFzc8G1vKQwLu/tNSO/X9j35Mt8NmU5IBl1eXP1LKrq2', NULL, '2026-08-28 04:57:50', '2026-08-28 04:57:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `dokters`
--
ALTER TABLE `dokters`
  ADD PRIMARY KEY (`id_dokter`),
  ADD UNIQUE KEY `dokters_user_id_unique` (`user_id`),
  ADD UNIQUE KEY `dokters_nip_sip_unique` (`nip_sip`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kunjungans`
--
ALTER TABLE `kunjungans`
  ADD PRIMARY KEY (`id_kunjungan`),
  ADD UNIQUE KEY `unique_pasien_kunjungan_aktif_per_hari` (`id_pasien`,`active_tgl_kunjungan`),
  ADD KEY `kunjungans_id_pasien_index` (`id_pasien`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pasiens`
--
ALTER TABLE `pasiens`
  ADD PRIMARY KEY (`id_pasien`),
  ADD UNIQUE KEY `pasiens_no_rm_unique` (`no_rm`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `rekam_medises`
--
ALTER TABLE `rekam_medises`
  ADD PRIMARY KEY (`id_rm`),
  ADD UNIQUE KEY `rekam_medises_id_kunjungan_unique` (`id_kunjungan`),
  ADD KEY `rekam_medises_id_dokter_foreign` (`id_dokter`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `transaksis`
--
ALTER TABLE `transaksis`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD UNIQUE KEY `transaksis_id_kunjungan_unique` (`id_kunjungan`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dokters`
--
ALTER TABLE `dokters`
  MODIFY `id_dokter` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kunjungans`
--
ALTER TABLE `kunjungans`
  MODIFY `id_kunjungan` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `pasiens`
--
ALTER TABLE `pasiens`
  MODIFY `id_pasien` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `rekam_medises`
--
ALTER TABLE `rekam_medises`
  MODIFY `id_rm` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `transaksis`
--
ALTER TABLE `transaksis`
  MODIFY `id_transaksi` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dokters`
--
ALTER TABLE `dokters`
  ADD CONSTRAINT `dokters_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kunjungans`
--
ALTER TABLE `kunjungans`
  ADD CONSTRAINT `kunjungans_id_pasien_foreign` FOREIGN KEY (`id_pasien`) REFERENCES `pasiens` (`id_pasien`) ON DELETE CASCADE;

--
-- Constraints for table `rekam_medises`
--
ALTER TABLE `rekam_medises`
  ADD CONSTRAINT `rekam_medises_id_dokter_foreign` FOREIGN KEY (`id_dokter`) REFERENCES `dokters` (`id_dokter`) ON DELETE SET NULL,
  ADD CONSTRAINT `rekam_medises_id_kunjungan_foreign` FOREIGN KEY (`id_kunjungan`) REFERENCES `kunjungans` (`id_kunjungan`) ON DELETE CASCADE;

--
-- Constraints for table `transaksis`
--
ALTER TABLE `transaksis`
  ADD CONSTRAINT `transaksis_id_kunjungan_foreign` FOREIGN KEY (`id_kunjungan`) REFERENCES `kunjungans` (`id_kunjungan`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
