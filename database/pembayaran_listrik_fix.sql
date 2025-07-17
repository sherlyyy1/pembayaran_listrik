-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 17, 2025 at 03:59 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.0.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pembayaran_listrik_fix`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `cekTagihanPelanggan` (IN `pid_pelanggan` INT, IN `pbulan` INT, IN `ptahun` INT)   BEGIN
    SELECT 
        t.id_tagihan,
        p.nama_pelanggan,
        t.bulan,
        t.tahun,
        t.jumlah_kwh,
        t.jumlah_tagihan,
        t.status_pembayaran
    FROM tagihan t
    JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
    WHERE t.id_pelanggan = pid_pelanggan
    AND t.bulan = pbulan
    AND t.tahun = ptahun;
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `totalPemakaianKWH` (`pid_pelanggan` INT, `pbulan` INT, `ptahun` INT) RETURNS INT(11) DETERMINISTIC BEGIN
    DECLARE kwh_sekarang INT;
    DECLARE kwh_lalu INT;
    DECLARE selisih INT;

    -- Ambil meteran bulan sekarang
    SELECT kwh_meter INTO kwh_sekarang
    FROM penggunaan
    WHERE id_pelanggan = pid_pelanggan
    AND bulan = pbulan AND tahun = ptahun;

    -- Ambil meteran bulan sebelumnya
    SELECT kwh_meter INTO kwh_lalu
    FROM penggunaan
    WHERE id_pelanggan = pid_pelanggan
    AND (
        (bulan = pbulan - 1 AND tahun = ptahun)
        OR (bulan = 12 AND pbulan = 1 AND tahun = ptahun - 1)
    )
    LIMIT 1;

    SET selisih = kwh_sekarang - IFNULL(kwh_lalu, 0);

    RETURN selisih;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `level`
--

CREATE TABLE `level` (
  `id_level` int(11) NOT NULL,
  `nama_level` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `level`
--

INSERT INTO `level` (`id_level`, `nama_level`) VALUES
(1, 'admin'),
(2, 'pelanggan');

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` int(11) NOT NULL,
  `nama_pelanggan` varchar(100) DEFAULT NULL,
  `alamat` varchar(100) DEFAULT NULL,
  `daya` int(11) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id_pelanggan`, `nama_pelanggan`, `alamat`, `daya`, `id_user`) VALUES
(100, 'Sherly Meilani', 'Jl. Mawar No.1', 900, 2),
(101, 'Yani Sari', 'Jl. Melati', 900, 4),
(102, 'Aditya Nugroho', NULL, NULL, 5),
(103, 'Brando Santoso', NULL, NULL, 6);

-- --------------------------------------------------------

--
-- Table structure for table `penggunaan`
--

CREATE TABLE `penggunaan` (
  `id_penggunaan` int(11) NOT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `bulan` int(11) DEFAULT NULL,
  `tahun` int(11) DEFAULT NULL,
  `kwh_meter` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penggunaan`
--

INSERT INTO `penggunaan` (`id_penggunaan`, `id_pelanggan`, `bulan`, `tahun`, `kwh_meter`) VALUES
(2, 1, 5, 2025, 12000),
(3, 1, 6, 2025, 12450),
(4, 1, 7, 2025, 12900),
(17, 101, 1, 2025, 500),
(18, 101, 2, 2025, 750),
(19, 101, 3, 2025, 1000),
(20, 101, 4, 2025, 1350),
(21, 101, 5, 2025, 1500),
(22, 101, 6, 2025, 1800),
(23, 101, 7, 2025, 2250),
(24, 101, 8, 2025, 2500),
(25, 101, 9, 2025, 2800),
(26, 101, 10, 2025, 3000),
(27, 101, 11, 2025, 3150),
(28, 102, 1, 2025, 500),
(29, 102, 2, 2025, 700),
(30, 102, 3, 2025, 1000);

--
-- Triggers `penggunaan`
--
DELIMITER $$
CREATE TRIGGER `after_insert_penggunaan` AFTER INSERT ON `penggunaan` FOR EACH ROW BEGIN
    DECLARE kwh_sebelumnya INT;
    DECLARE jumlah_kwh INT;
    DECLARE total_tagihan INT;

    -- Ambil KWH bulan sebelumnya
    SELECT kwh_meter INTO kwh_sebelumnya
    FROM penggunaan
    WHERE id_pelanggan = NEW.id_pelanggan
    AND (
        (bulan = NEW.bulan - 1 AND tahun = NEW.tahun)
        OR (bulan = 12 AND NEW.bulan = 1 AND tahun = NEW.tahun - 1)
    )
    LIMIT 1;

    -- Hitung jumlah_kwh & tagihan
    SET jumlah_kwh = NEW.kwh_meter - IFNULL(kwh_sebelumnya, 0);
    SET total_tagihan = jumlah_kwh * 1500;

    -- Insert ke tagihan
    INSERT INTO tagihan (
        id_tagihan,
        id_pelanggan,
        bulan,
        tahun,
        jumlah_kwh,
        jumlah_tagihan,
        status_pembayaran
    ) VALUES (
        UNIX_TIMESTAMP(),  -- auto ID unik dari waktu
        NEW.id_pelanggan,
        NEW.bulan,
        NEW.tahun,
        jumlah_kwh,
        total_tagihan,
        'Belum Bayar'
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tagihan`
--

CREATE TABLE `tagihan` (
  `id_tagihan` int(11) NOT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `bulan` int(11) DEFAULT NULL,
  `tahun` int(11) DEFAULT NULL,
  `jumlah_kwh` int(11) DEFAULT NULL,
  `jumlah_tagihan` int(11) DEFAULT NULL,
  `status_pembayaran` enum('Belum Bayar','Menunggu Konfirmasi','Lunas') DEFAULT 'Belum Bayar',
  `bukti_transfer` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tagihan`
--

INSERT INTO `tagihan` (`id_tagihan`, `id_pelanggan`, `bulan`, `tahun`, `jumlah_kwh`, `jumlah_tagihan`, `status_pembayaran`, `bukti_transfer`) VALUES
(1, 1, 6, 2025, 450, 675000, 'Lunas', NULL),
(1752313000, 101, 1, 2025, 500, 750000, 'Lunas', NULL),
(1752314874, 101, 2, 2025, 250, 375000, 'Lunas', NULL),
(1752316265, 101, 3, 2025, 250, 375000, 'Lunas', NULL),
(1752316480, 101, 4, 2025, 350, 525000, 'Lunas', NULL),
(1752317156, 101, 5, 2025, 150, 225000, 'Lunas', 'bukti_1752318392.jpg'),
(1752319138, 101, 6, 2025, 300, 450000, 'Lunas', 'bukti_1752320106.jpg'),
(1752320517, 101, 7, 2025, 450, 675000, 'Lunas', 'bukti_1752320824.jpg'),
(1752321365, 101, 8, 2025, 250, 375000, 'Lunas', 'bukti_1752321407.jpg'),
(1752322246, 101, 9, 2025, 300, 450000, 'Lunas', 'bukti_1752322336.jpg'),
(1752323564, 101, 10, 2025, 200, 300000, 'Lunas', 'bukti_1752323660.jpg'),
(1752602348, 101, 11, 2025, 150, 225000, 'Belum Bayar', NULL),
(1752626869, 102, 1, 2025, 500, 750000, 'Lunas', 'bukti_6876f702aab68.jpg'),
(1752627048, 102, 2, 2025, 200, 300000, 'Lunas', 'bukti_6876fed518425.jpg'),
(1752628895, 102, 3, 2025, 300, 450000, 'Belum Bayar', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tarif`
--

CREATE TABLE `tarif` (
  `id_tarif` int(11) NOT NULL,
  `daya` int(11) DEFAULT NULL,
  `harga_per_kwh` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tarif`
--

INSERT INTO `tarif` (`id_tarif`, `daya`, `harga_per_kwh`) VALUES
(1, 900, 1500),
(99, 2000, 1600);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `id_level` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `username`, `password`, `id_level`) VALUES
(2, 'user123', '$2y$10$BWBlbTIDakxCtuDk./HGe.97Np94d74HxtgmOEW7xePExqvokumIK', 2),
(3, 'admin', '$2y$10$TlqtZ6cS9.WdZW5knLG1zeUVE5P0ikdi8yuLLqH1jbeS04LqBezzG', 1),
(4, 'yani123', '$2y$10$/Wjx4EAYmPGu0jyXGL5rQ.vF9vYvcCcDznK6meSGSfN/sDENJJSCy', 2),
(5, 'aditya71', '$2y$10$bIwO84LIpM7EvpqQ.FACEOzu0rVv4VBuA8QIxBm5VGuNa74CsPez6', 2),
(6, 'brando23', '$2y$10$bcQT9ALiLu0fUXqSkVgikeIyvD3GHhnJHS.oqC8GCCFlJkbj0KOd2', 2);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_tagihan`
-- (See below for the actual view)
--
CREATE TABLE `view_tagihan` (
`id_tagihan` int(11)
,`id_pelanggan` int(11)
,`nama_pelanggan` varchar(100)
,`bulan` int(11)
,`tahun` int(11)
,`jumlah_kwh` int(11)
,`jumlah_tagihan` int(11)
,`status_pembayaran` enum('Belum Bayar','Menunggu Konfirmasi','Lunas')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_tagihan_lengkap`
-- (See below for the actual view)
--
CREATE TABLE `view_tagihan_lengkap` (
`nama_pelanggan` varchar(100)
,`alamat` varchar(100)
,`bulan` int(11)
,`tahun` int(11)
,`kwh_meter` int(11)
,`jumlah_kwh` int(11)
,`jumlah_tagihan` int(11)
,`status_pembayaran` enum('Belum Bayar','Menunggu Konfirmasi','Lunas')
);

-- --------------------------------------------------------

--
-- Structure for view `view_tagihan`
--
DROP TABLE IF EXISTS `view_tagihan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_tagihan`  AS SELECT `t`.`id_tagihan` AS `id_tagihan`, `p`.`id_pelanggan` AS `id_pelanggan`, `p`.`nama_pelanggan` AS `nama_pelanggan`, `t`.`bulan` AS `bulan`, `t`.`tahun` AS `tahun`, `t`.`jumlah_kwh` AS `jumlah_kwh`, `t`.`jumlah_tagihan` AS `jumlah_tagihan`, `t`.`status_pembayaran` AS `status_pembayaran` FROM (`tagihan` `t` join `pelanggan` `p` on(`t`.`id_pelanggan` = `p`.`id_pelanggan`))  ;

-- --------------------------------------------------------

--
-- Structure for view `view_tagihan_lengkap`
--
DROP TABLE IF EXISTS `view_tagihan_lengkap`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_tagihan_lengkap`  AS SELECT `p`.`nama_pelanggan` AS `nama_pelanggan`, `p`.`alamat` AS `alamat`, `pg`.`bulan` AS `bulan`, `pg`.`tahun` AS `tahun`, `pg`.`kwh_meter` AS `kwh_meter`, `t`.`jumlah_kwh` AS `jumlah_kwh`, `t`.`jumlah_tagihan` AS `jumlah_tagihan`, `t`.`status_pembayaran` AS `status_pembayaran` FROM ((`pelanggan` `p` join `penggunaan` `pg` on(`p`.`id_pelanggan` = `pg`.`id_pelanggan`)) left join `tagihan` `t` on(`t`.`id_pelanggan` = `p`.`id_pelanggan` and `t`.`bulan` = `pg`.`bulan` and `t`.`tahun` = `pg`.`tahun`))  ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `level`
--
ALTER TABLE `level`
  ADD PRIMARY KEY (`id_level`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `idx_id_pelanggan` (`id_pelanggan`);

--
-- Indexes for table `penggunaan`
--
ALTER TABLE `penggunaan`
  ADD PRIMARY KEY (`id_penggunaan`),
  ADD UNIQUE KEY `unik_penggunaan` (`id_pelanggan`,`bulan`,`tahun`),
  ADD KEY `idx_id_penggunaan` (`id_pelanggan`);

--
-- Indexes for table `tagihan`
--
ALTER TABLE `tagihan`
  ADD PRIMARY KEY (`id_tagihan`),
  ADD KEY `id_pelanggan` (`id_pelanggan`);

--
-- Indexes for table `tarif`
--
ALTER TABLE `tarif`
  ADD PRIMARY KEY (`id_tarif`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD KEY `id_level` (`id_level`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `penggunaan`
--
ALTER TABLE `penggunaan`
  MODIFY `id_penggunaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `tagihan`
--
ALTER TABLE `tagihan`
  MODIFY `id_tagihan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2000000012;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`id_level`) REFERENCES `level` (`id_level`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
