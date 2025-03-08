-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 17, 2024 at 11:07 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rekod_pengurusan_sistem`
--

-- --------------------------------------------------------

--
-- Table structure for table `deadlines`
--

CREATE TABLE `deadlines` (
  `deadline_id` int(11) NOT NULL,
  `serahan_no` tinyint(4) DEFAULT NULL,
  `deadline_date` date NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deadlines`
--

INSERT INTO `deadlines` (`deadline_id`, `serahan_no`, `deadline_date`, `description`) VALUES
(1, 1, '2024-11-28', 'Deadline for Serahan Pertama'),
(2, 2, '2024-12-09', 'Deadline for Serahan Kedua');

-- --------------------------------------------------------

--
-- Table structure for table `dokumen`
--

CREATE TABLE `dokumen` (
  `dokumen_id` int(11) NOT NULL,
  `kursus_id` int(11) NOT NULL,
  `serahan_no` int(11) NOT NULL,
  `nama_dokumen` varchar(255) NOT NULL,
  `status` enum('Checked','Not Checked') DEFAULT 'Not Checked',
  `comment` text DEFAULT NULL,
  `path_dokumen` varchar(100) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `checked_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dokumen`
--

INSERT INTO `dokumen` (`dokumen_id`, `kursus_id`, `serahan_no`, `nama_dokumen`, `status`, `comment`, `path_dokumen`, `uploaded_at`, `checked_at`) VALUES
(209, 19, 1, 'Ringkasan Maklumat Kursus', 'Not Checked', NULL, '', '2024-11-16 16:05:12', NULL),
(210, 19, 1, 'Perincian Kursus Mingguan', 'Not Checked', NULL, '', '2024-11-16 16:05:12', NULL),
(211, 19, 1, 'Borang Item Penilaian', 'Not Checked', NULL, '', '2024-11-16 16:05:12', NULL),
(212, 19, 1, 'Borang Pemetaan Pentaksiran dan COPO', 'Not Checked', NULL, '', '2024-11-16 16:05:12', NULL),
(213, 19, 1, 'Rekod Kehadiran Pelajar (Minggu 1-7 for diploma) (Minggu 1-4 for Asasi)', 'Not Checked', NULL, '', '2024-11-16 16:05:12', NULL),
(214, 19, 1, 'Jadual PdP Pensyarah', 'Not Checked', NULL, '', '2024-11-16 16:05:12', NULL),
(215, 19, 2, 'Rekod Kehadiran Pelajar (Minggu 1-14 for diploma) (Minggu 1-12 for Asasi)', 'Not Checked', NULL, '', '2024-11-16 16:05:12', NULL),
(216, 19, 2, 'Perincian Kursus Mingguan (lengkap)', 'Not Checked', NULL, '', '2024-11-16 16:05:12', NULL),
(217, 19, 2, 'Soalan Kemajuan 1', 'Not Checked', NULL, '', '2024-11-16 16:05:12', NULL),
(218, 19, 2, 'Soalan Kemajuan 2', 'Not Checked', NULL, '', '2024-11-16 16:05:12', NULL),
(219, 19, 2, 'Soalan Peperiksaan Akhir Beserta Skema Jawapan', 'Not Checked', NULL, '', '2024-11-16 16:05:12', NULL),
(220, 19, 2, 'Analisis Keputusan – Laporan CQI & Penilaian Pengajaran oleh Pelajar', 'Not Checked', NULL, '', '2024-11-16 16:05:12', NULL),
(221, 19, 2, 'Dokumen Tambahan', 'Not Checked', NULL, '', '2024-11-16 16:05:12', NULL),
(222, 20, 1, 'Ringkasan Maklumat Kursus', 'Not Checked', NULL, '', '2024-11-16 16:21:54', NULL),
(223, 20, 1, 'Perincian Kursus Mingguan', 'Not Checked', NULL, '', '2024-11-16 16:21:54', NULL),
(224, 20, 1, 'Borang Item Penilaian', 'Not Checked', NULL, '', '2024-11-16 16:21:54', NULL),
(225, 20, 1, 'Borang Pemetaan Pentaksiran dan COPO', 'Not Checked', NULL, '', '2024-11-16 16:21:54', NULL),
(226, 20, 1, 'Rekod Kehadiran Pelajar (Minggu 1-7 for diploma) (Minggu 1-4 for Asasi)', 'Not Checked', NULL, '', '2024-11-16 16:21:54', NULL),
(227, 20, 1, 'Jadual PdP Pensyarah', 'Not Checked', NULL, '', '2024-11-16 16:21:54', NULL),
(228, 20, 2, 'Rekod Kehadiran Pelajar (Minggu 1-14 for diploma) (Minggu 1-12 for Asasi)', 'Not Checked', NULL, '', '2024-11-16 16:21:54', NULL),
(229, 20, 2, 'Perincian Kursus Mingguan (lengkap)', 'Not Checked', NULL, '', '2024-11-16 16:21:54', NULL),
(230, 20, 2, 'Soalan Kemajuan 1', 'Not Checked', NULL, '', '2024-11-16 16:21:54', NULL),
(231, 20, 2, 'Soalan Kemajuan 2', 'Not Checked', NULL, '', '2024-11-16 16:21:54', NULL),
(232, 20, 2, 'Soalan Peperiksaan Akhir Beserta Skema Jawapan', 'Not Checked', NULL, '', '2024-11-16 16:21:54', NULL),
(233, 20, 2, 'Analisis Keputusan – Laporan CQI & Penilaian Pengajaran oleh Pelajar', 'Not Checked', NULL, '', '2024-11-16 16:21:54', NULL),
(234, 20, 2, 'Dokumen Tambahan', 'Not Checked', NULL, '', '2024-11-16 16:21:54', NULL),
(235, 21, 1, 'Ringkasan Maklumat Kursus', 'Checked', '', 'uploads/6739555e31ed2_Desktop - 2.pdf', '2024-11-17 02:30:54', '2024-11-17 09:58:36'),
(236, 21, 1, 'Perincian Kursus Mingguan', 'Not Checked', NULL, '', '2024-11-16 16:22:59', NULL),
(237, 21, 1, 'Borang Item Penilaian', 'Not Checked', NULL, '', '2024-11-16 16:22:59', NULL),
(238, 21, 1, 'Borang Pemetaan Pentaksiran dan COPO', 'Not Checked', NULL, '', '2024-11-16 16:22:59', NULL),
(239, 21, 1, 'Rekod Kehadiran Pelajar (Minggu 1-7 for diploma) (Minggu 1-4 for Asasi)', 'Not Checked', NULL, '', '2024-11-16 16:22:59', NULL),
(240, 21, 1, 'Jadual PdP Pensyarah', 'Not Checked', NULL, '', '2024-11-16 16:22:59', NULL),
(241, 21, 2, 'Rekod Kehadiran Pelajar (Minggu 1-14 for diploma) (Minggu 1-12 for Asasi)', 'Not Checked', NULL, 'uploads/6739bdd348a0f_6735fc176e911_JADUAL KULIAH - PELAJAR (TERBITAN KETIGA) 12 OGOS 2024.pdf', '2024-11-17 09:56:35', NULL),
(242, 21, 2, 'Perincian Kursus Mingguan (lengkap)', 'Not Checked', NULL, '', '2024-11-16 16:22:59', NULL),
(243, 21, 2, 'Soalan Kemajuan 1', 'Not Checked', NULL, '', '2024-11-16 16:22:59', NULL),
(244, 21, 2, 'Soalan Kemajuan 2', 'Not Checked', NULL, '', '2024-11-16 16:22:59', NULL),
(245, 21, 2, 'Soalan Peperiksaan Akhir Beserta Skema Jawapan', 'Not Checked', NULL, '', '2024-11-16 16:22:59', NULL),
(246, 21, 2, 'Analisis Keputusan – Laporan CQI & Penilaian Pengajaran oleh Pelajar', 'Not Checked', NULL, '', '2024-11-16 16:22:59', NULL),
(247, 21, 2, 'Dokumen Tambahan', 'Not Checked', NULL, '', '2024-11-16 16:22:59', NULL),
(261, 23, 1, 'Ringkasan Maklumat Kursus', 'Not Checked', NULL, '', '2024-11-17 04:32:21', NULL),
(262, 23, 1, 'Perincian Kursus Mingguan', 'Not Checked', NULL, '', '2024-11-17 04:32:21', NULL),
(263, 23, 1, 'Borang Item Penilaian', 'Not Checked', NULL, '', '2024-11-17 04:32:21', NULL),
(264, 23, 1, 'Borang Pemetaan Pentaksiran dan COPO', 'Not Checked', NULL, '', '2024-11-17 04:32:21', NULL),
(265, 23, 1, 'Rekod Kehadiran Pelajar (Minggu 1-7 for diploma) (Minggu 1-4 for Asasi)', 'Not Checked', NULL, '', '2024-11-17 04:32:21', NULL),
(266, 23, 1, 'Jadual PdP Pensyarah', 'Not Checked', NULL, '', '2024-11-17 04:32:21', NULL),
(267, 23, 2, 'Rekod Kehadiran Pelajar (Minggu 1-14 for diploma) (Minggu 1-12 for Asasi)', 'Not Checked', NULL, '', '2024-11-17 04:32:21', NULL),
(268, 23, 2, 'Perincian Kursus Mingguan (lengkap)', 'Not Checked', NULL, '', '2024-11-17 04:32:21', NULL),
(269, 23, 2, 'Soalan Kemajuan 1', 'Not Checked', NULL, '', '2024-11-17 04:32:21', NULL),
(270, 23, 2, 'Soalan Kemajuan 2', 'Not Checked', NULL, '', '2024-11-17 04:32:21', NULL),
(271, 23, 2, 'Soalan Peperiksaan Akhir Beserta Skema Jawapan', 'Not Checked', NULL, '', '2024-11-17 04:32:21', NULL),
(272, 23, 2, 'Analisis Keputusan – Laporan CQI & Penilaian Pengajaran oleh Pelajar', 'Not Checked', NULL, '', '2024-11-17 04:32:21', NULL),
(273, 23, 2, 'Dokumen Tambahan', 'Not Checked', NULL, '', '2024-11-17 04:32:21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `kursus`
--

CREATE TABLE `kursus` (
  `id` int(11) NOT NULL,
  `pensyarah_id` int(11) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `sesi` int(11) DEFAULT NULL,
  `kod_kursus` varchar(50) NOT NULL,
  `nama_kursus` varchar(255) NOT NULL,
  `program` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kursus`
--

INSERT INTO `kursus` (`id`, `pensyarah_id`, `semester`, `sesi`, `kod_kursus`, `nama_kursus`, `program`) VALUES
(19, 5, 1, 20232024, 'SK4022', 'Sistem Komputer', 'Diploma Pengajian Islam'),
(20, 2, 1, 20232024, 'SK5050', 'Pengaturcaraan Mobil', 'Diploma Pengajian Islam'),
(21, 5, 1, 20262027, 'SK4022', 'Sistem Komputer', 'Diploma Pengajian Islam'),
(23, 4, 1, 20262027, 'SK5050', 'Pengaturcaraan Mobil', 'Diploma Pengajian Islam');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expiry` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengesahan_kursus`
--

CREATE TABLE `pengesahan_kursus` (
  `id` int(11) NOT NULL,
  `kursus_id` int(11) NOT NULL,
  `no_serahan` tinyint(4) DEFAULT NULL,
  `pensyarah_id` int(11) NOT NULL,
  `ulasan` text DEFAULT NULL,
  `disahkan_oleh` int(11) NOT NULL,
  `waktu_pengesahan` timestamp NOT NULL DEFAULT current_timestamp(),
  `Status_pengesahan` enum('belum disahkan','telah disahkan') DEFAULT 'belum disahkan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengesahan_kursus`
--

INSERT INTO `pengesahan_kursus` (`id`, `kursus_id`, `no_serahan`, `pensyarah_id`, `ulasan`, `disahkan_oleh`, `waktu_pengesahan`, `Status_pengesahan`) VALUES
(23, 21, 1, 2, 'inhiuhybyy', 2, '2024-11-17 09:58:41', 'belum disahkan'),
(24, 21, 2, 2, '', 2, '2024-11-17 02:41:22', 'telah disahkan');

-- --------------------------------------------------------

--
-- Table structure for table `pensyarah`
--

CREATE TABLE `pensyarah` (
  `id` int(11) NOT NULL,
  `id_users` int(11) NOT NULL,
  `nama_pensyarah` varchar(255) NOT NULL,
  `ketua_jabatan` enum('yes','no') DEFAULT NULL,
  `jabatan` varchar(255) DEFAULT NULL,
  `fakulti` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pensyarah`
--

INSERT INTO `pensyarah` (`id`, `id_users`, `nama_pensyarah`, `ketua_jabatan`, `jabatan`, `fakulti`) VALUES
(2, 9, 'b', 'yes', 'Jabatan Bahasa Arab', 'Fakulti Pengajian Islam'),
(4, 11, 'Hisyamudin bin baharudin', 'yes', 'Jabatan Multimedia Kreatif & Komputeran', 'Fakulti Pengurusan & Informatik'),
(5, 12, 'Fazzly', 'no', 'Jabatan Bahasa Arab', 'Fakulti Pengajian Islam');

-- --------------------------------------------------------

--
-- Table structure for table `sesi`
--

CREATE TABLE `sesi` (
  `sesi_id` int(8) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sesi`
--

INSERT INTO `sesi` (`sesi_id`, `created_at`) VALUES
(20232024, '2024-11-16 16:00:50'),
(20242025, '2024-11-17 03:00:18'),
(20262027, '2024-11-14 05:44:59');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','ketua bahagian','pensyarah') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'a@a', '$2y$10$Xz0nZW2RMA4DTNtBX4kKR.9kGoh6SG.Y466D3h/S/i6ez3WW6JCTq', 'admin', '2024-11-14 04:57:56'),
(9, 'b', '', '$2y$10$PCODZZ0pyiQxwQmMWmrRD.vqdKI7aXPAhQLeHP/6SLeqRLyrs2jqG', 'ketua bahagian', '2024-11-14 05:09:20'),
(10, '000819060133', '', '$2y$10$6D6cKLHk.poWEv9ejUvMlOlxgSsIIlWLt1IOv9r4OeqiTclwVA6s.', 'ketua bahagian', '2024-11-14 05:49:16'),
(11, '000819060133', '', '$2y$10$fLucigCA0vp8TsdDOfOufu.POP8H2lT2xY.Xj0yy5/sxUhfRcFQcS', 'ketua bahagian', '2024-11-14 05:49:46'),
(12, '9806065074', '', '$2y$10$DeAKOjSoPZJQzOGEgEczQen9lOEs.IoN7okh7kZQGbAMhjR48s9Rq', 'pensyarah', '2024-11-14 13:06:43'),
(13, 'a', '', '$2y$10$0IPY/FiUyn1s1ZJnYnw3TOxN3IJmTfrfIlp3wUmU3wls0lFVuxj9C', 'ketua bahagian', '2024-11-16 15:16:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `deadlines`
--
ALTER TABLE `deadlines`
  ADD PRIMARY KEY (`deadline_id`);

--
-- Indexes for table `dokumen`
--
ALTER TABLE `dokumen`
  ADD PRIMARY KEY (`dokumen_id`),
  ADD KEY `kursus_id` (`kursus_id`);

--
-- Indexes for table `kursus`
--
ALTER TABLE `kursus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kod_kursus_semester_sesi` (`kod_kursus`,`semester`,`sesi`),
  ADD KEY `kursus_ibfk_1` (`pensyarah_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pengesahan_kursus`
--
ALTER TABLE `pengesahan_kursus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kursus_id` (`kursus_id`),
  ADD KEY `disahkan_oleh` (`disahkan_oleh`),
  ADD KEY `pengesahan_kursus_ibfk_3` (`pensyarah_id`),
  ADD KEY `idx_no_serahan` (`no_serahan`);

--
-- Indexes for table `pensyarah`
--
ALTER TABLE `pensyarah`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_users` (`id_users`);

--
-- Indexes for table `sesi`
--
ALTER TABLE `sesi`
  ADD PRIMARY KEY (`sesi_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `deadlines`
--
ALTER TABLE `deadlines`
  MODIFY `deadline_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `dokumen`
--
ALTER TABLE `dokumen`
  MODIFY `dokumen_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=274;

--
-- AUTO_INCREMENT for table `kursus`
--
ALTER TABLE `kursus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengesahan_kursus`
--
ALTER TABLE `pengesahan_kursus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `pensyarah`
--
ALTER TABLE `pensyarah`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dokumen`
--
ALTER TABLE `dokumen`
  ADD CONSTRAINT `dokumen_ibfk_1` FOREIGN KEY (`kursus_id`) REFERENCES `kursus` (`id`);

--
-- Constraints for table `kursus`
--
ALTER TABLE `kursus`
  ADD CONSTRAINT `kursus_ibfk_1` FOREIGN KEY (`pensyarah_id`) REFERENCES `pensyarah` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `pengesahan_kursus`
--
ALTER TABLE `pengesahan_kursus`
  ADD CONSTRAINT `pengesahan_kursus_ibfk_1` FOREIGN KEY (`kursus_id`) REFERENCES `kursus` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pengesahan_kursus_ibfk_2` FOREIGN KEY (`disahkan_oleh`) REFERENCES `pensyarah` (`id`),
  ADD CONSTRAINT `pengesahan_kursus_ibfk_3` FOREIGN KEY (`pensyarah_id`) REFERENCES `pensyarah` (`id`);

--
-- Constraints for table `pensyarah`
--
ALTER TABLE `pensyarah`
  ADD CONSTRAINT `pensyarah_ibfk_1` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
