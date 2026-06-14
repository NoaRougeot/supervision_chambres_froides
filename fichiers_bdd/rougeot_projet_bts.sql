-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql-rougeot.alwaysdata.net
-- Generation Time: Jun 14, 2026 at 09:57 AM
-- Server version: 10.11.18-MariaDB
-- PHP Version: 8.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rougeot_projet_bts`
--

-- --------------------------------------------------------

--
-- Table structure for table `Alerte`
--

CREATE TABLE `Alerte` (
  `id_alerte` int(9) NOT NULL,
  `type_alerte` tinyint(1) NOT NULL,
  `horodatage_alerte` int(11) NOT NULL,
  `date_ack_alarme` int(11) NOT NULL,
  `id_chambre` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Chambre`
--

CREATE TABLE `Chambre` (
  `id_chambre` tinyint(4) NOT NULL,
  `nom_chambre` varchar(100) NOT NULL,
  `type_chambre` tinyint(1) NOT NULL,
  `plage_min` tinyint(4) NOT NULL,
  `plage_max` tinyint(4) NOT NULL,
  `adresse_mac` binary(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Chambre`
--

INSERT INTO `Chambre` (`id_chambre`, `nom_chambre`, `type_chambre`, `plage_min`, `plage_max`, `adresse_mac`) VALUES
(1, 'Chambre 1', 1, 2, 4, 0x000000000000),
(2, 'Chambre 2', 0, -60, -20, 0x000000000000);

-- --------------------------------------------------------

--
-- Table structure for table `Porte`
--

CREATE TABLE `Porte` (
  `id_enregistrement_porte` int(9) NOT NULL,
  `etat_porte` tinyint(1) NOT NULL,
  `horodatage_porte` int(20) NOT NULL,
  `id_chambre` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Temperature`
--

CREATE TABLE `Temperature` (
  `id_enregistrement_temperature` int(9) NOT NULL,
  `temperature` smallint(6) NOT NULL,
  `horodatage_temperature` int(11) NOT NULL,
  `id_chambre` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Utilisateur`
--

CREATE TABLE `Utilisateur` (
  `id_utilisateur` tinyint(4) NOT NULL,
  `pseudo` varchar(32) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `date_inscription` int(11) NOT NULL,
  `droits` varchar(10) NOT NULL,
  `hash_mdp` varchar(255) NOT NULL,
  `email` varchar(320) NOT NULL,
  `cle_secrete` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Utilisateur`
--

INSERT INTO `Utilisateur` (`id_utilisateur`, `pseudo`, `nom`, `prenom`, `date_inscription`, `droits`, `hash_mdp`, `email`, `cle_secrete`) VALUES
(1, 'noa', 'Rougeot', 'Noa', 1779779001, 'admin', '$2y$10$IAC8bOdERXJ36WIZFJ3.uurjC6XyqkhfDzZepqA4Lo4VdGjqQ0a0C', 'jury@gmail.com', '7LKYWOQWZG64IUHFVJHOT3YANC7SJUBF');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Alerte`
--
ALTER TABLE `Alerte`
  ADD PRIMARY KEY (`id_alerte`),
  ADD KEY `id_chambre_alerte` (`id_chambre`,`id_alerte`) USING BTREE;

--
-- Indexes for table `Chambre`
--
ALTER TABLE `Chambre`
  ADD PRIMARY KEY (`id_chambre`);

--
-- Indexes for table `Porte`
--
ALTER TABLE `Porte`
  ADD PRIMARY KEY (`id_enregistrement_porte`),
  ADD KEY `id_chambre` (`id_chambre`,`id_enregistrement_porte`) USING BTREE;

--
-- Indexes for table `Temperature`
--
ALTER TABLE `Temperature`
  ADD PRIMARY KEY (`id_enregistrement_temperature`),
  ADD KEY `id_enregistrement_temperature` (`id_chambre`,`id_enregistrement_temperature`) USING BTREE;

--
-- Indexes for table `Utilisateur`
--
ALTER TABLE `Utilisateur`
  ADD PRIMARY KEY (`id_utilisateur`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Alerte`
--
ALTER TABLE `Alerte`
  MODIFY `id_alerte` int(9) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Chambre`
--
ALTER TABLE `Chambre`
  MODIFY `id_chambre` tinyint(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `Porte`
--
ALTER TABLE `Porte`
  MODIFY `id_enregistrement_porte` int(9) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Temperature`
--
ALTER TABLE `Temperature`
  MODIFY `id_enregistrement_temperature` int(9) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Utilisateur`
--
ALTER TABLE `Utilisateur`
  MODIFY `id_utilisateur` tinyint(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Alerte`
--
ALTER TABLE `Alerte`
  ADD CONSTRAINT `Alerte_ibfk_1` FOREIGN KEY (`id_chambre`) REFERENCES `Chambre` (`id_chambre`);

--
-- Constraints for table `Porte`
--
ALTER TABLE `Porte`
  ADD CONSTRAINT `Porte_ibfk_1` FOREIGN KEY (`id_chambre`) REFERENCES `Chambre` (`id_chambre`);

--
-- Constraints for table `Temperature`
--
ALTER TABLE `Temperature`
  ADD CONSTRAINT `Temperature_ibfk_1` FOREIGN KEY (`id_chambre`) REFERENCES `Chambre` (`id_chambre`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
