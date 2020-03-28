-- phpMyAdmin SQL Dump
-- version 4.9.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 28, 2020 at 12:14 AM
-- Server version: 5.6.47-cll-lve
-- PHP Version: 7.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cordova_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `authorization_grant`
--

CREATE TABLE `authorization_grant` (
  `id` int(32) NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `clientId` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `authorization_grant`
--

INSERT INTO `authorization_grant` (`id`, `username`, `password`, `clientId`) VALUES
(1, '7d96f69790319cf6c5feb25849eb4485', '0aabfdb107a0a0cbb4a5ea9724296cdc', '61fc89692eefa0b1a73f74a837b81a59');

-- --------------------------------------------------------

--
-- Table structure for table `pasien`
--

CREATE TABLE `pasien` (
  `id` int(32) NOT NULL,
  `namalengkap` varchar(64) NOT NULL,
  `ruangpasien` varchar(64) NOT NULL,
  `alamat` text NOT NULL,
  `umur` int(16) NOT NULL,
  `golongandarah` varchar(32) NOT NULL,
  `jeniskelamin` int(16) NOT NULL,
  `keluhan` varchar(64) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pasien`
--

INSERT INTO `pasien` (`id`, `namalengkap`, `ruangpasien`, `alamat`, `umur`, `golongandarah`, `jeniskelamin`, `keluhan`) VALUES
(4, 'Wokeh', 'Kamboja', 'Demak', 46, 'O', 1, 'Sakit Kepala'),
(3, 'Ucok', 'Ruang Wisma Bougenville', 'Manyaran, Semarang, Jawa Tengah', 26, 'AB', 1, 'Terkena Virus Corona'),
(6, 'Mantap', 'Wkkwkw', 'Wkwkw', 22, 'A', 3, 'ðŸ˜‚ðŸ˜‚ðŸ˜‚');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(32) NOT NULL,
  `namalengkap` varchar(64) NOT NULL,
  `username` varchar(64) NOT NULL,
  `password` varchar(64) NOT NULL,
  `user_token` text NOT NULL,
  `expires_at` varchar(64) NOT NULL,
  `authId` text NOT NULL,
  `authIdExpires` varchar(64) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `namalengkap`, `username`, `password`, `user_token`, `expires_at`, `authId`, `authIdExpires`) VALUES
(1, 'Wira Dwi Susanto', 'wiradwis', '123', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiIxOGY5ZWJhZDkwY2RmMmM3NGE4Y2IyMjA5OTYwMTI0ZSIsImlzcyI6Imh0dHBzOlwvXC9hcGkua21zcC1zdG9yZS5jb21cLyIsImF1ZCI6IjYxZmM4OTY5MmVlZmEwYjFhNzNmNzRhODM3YjgxYTU5IiwiaWF0IjoxNTg1Mzc4NzQ1LCJleHAiOjE1ODU2Mzc5NDUsInNlc3Npb25fZGF0YSI6IjJhZjQxNDdmYjhiZDE0OTNhZGU0OGRkN2YwY2VjNGUwYjdjNGVjOTliMDJhMTIwZjBkYzMwNGM4ZjMwZTk5NmIifQ.w0u5pelTBXr8dZSde5Kn8QISGWR2qab0bKvUfdo5iCk', '1585637945', '', ''),
(2, 'Wira Mantap', 'wiramantap123', '123', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJlMTQ4ZjhmNTA4ODFhMzQ0ODBkZjljNGUwMzFmYmJlOCIsImlzcyI6Imh0dHBzOlwvXC9hcGkua21zcC1zdG9yZS5jb21cLyIsImF1ZCI6IjYxZmM4OTY5MmVlZmEwYjFhNzNmNzRhODM3YjgxYTU5IiwiaWF0IjoxNTg1Mzc4Nzk5LCJleHAiOjE1ODU2Mzc5OTksInNlc3Npb25fZGF0YSI6ImFmZjNlN2ZmMTdkMTlkZjliZDZlNDRjYTAyOTE2YTZhN2U1OTdlMzZmM2M3ZjQxMjU3MzZhMWQ5YzIyOTcxZWMifQ.mob8Po9vn-54x01PU9z8jzaBOLPDOsLI1X11ZbLzi9E', '1585637999', '', ''),
(3, 'Wokeh', 'wokeh123', '123', '', '', '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `authorization_grant`
--
ALTER TABLE `authorization_grant`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pasien`
--
ALTER TABLE `pasien`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `authorization_grant`
--
ALTER TABLE `authorization_grant`
  MODIFY `id` int(32) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pasien`
--
ALTER TABLE `pasien`
  MODIFY `id` int(32) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(32) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
