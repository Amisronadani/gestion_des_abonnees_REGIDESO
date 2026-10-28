-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2026 at 06:18 PM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.5.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gestion_abonnes`
--

-- --------------------------------------------------------

--
-- Table structure for table `abonne`
--

CREATE TABLE `abonne` (
  `idabonne_abonnes` int(11) NOT NULL,
  `nom_abonnes` varchar(40) NOT NULL,
  `prenom_abonnes` varchar(40) NOT NULL,
  `type_abonne_abonnes` varchar(40) DEFAULT NULL,
  `telephone_abonnes` varchar(30) DEFAULT NULL,
  `email_abonnes` varchar(40) DEFAULT NULL,
  `adresse_abonne` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `abonne`
--

INSERT INTO `abonne` (`idabonne_abonnes`, `nom_abonnes`, `prenom_abonnes`, `type_abonne_abonnes`, `telephone_abonnes`, `email_abonnes`, `adresse_abonne`) VALUES
(1, 'Dupont', 'Jean', 'Particulier', '0601020304', 'jean.dupont@email.fr', '12 Rue de Paris'),
(2, 'Martin', 'Marie', 'Particulier', '0605060708', 'marie.martin@email.fr', '45 Avenue Victor Hugo'),
(3, 'Soci??t?? ABC', 'Direction', 'Professionnel', '0147258369', 'contact@abc.fr', '8 Boulevard Haussmann'),
(4, 'Izere', 'Arlene', 'Professionnel', '4567890', 'arlene@gmail.com', 'buyenzi');

-- --------------------------------------------------------

--
-- Table structure for table `compteur`
--

CREATE TABLE `compteur` (
  `id_Compteur` int(11) NOT NULL,
  `numero_Compteur` varchar(30) NOT NULL,
  `Typecompteur_Compteur` varchar(30) DEFAULT NULL,
  `DatePose_Compteur` date DEFAULT NULL,
  `Etat_compteur_Compteur` varchar(30) DEFAULT 'Actif',
  `id_Point_de_service` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `compteur`
--

INSERT INTO `compteur` (`id_Compteur`, `numero_Compteur`, `Typecompteur_Compteur`, `DatePose_Compteur`, `Etat_compteur_Compteur`, `id_Point_de_service`) VALUES
(1, '7675494', 'Numérique', '2026-04-18', 'Actif', 1);

-- --------------------------------------------------------

--
-- Table structure for table `contrat`
--

CREATE TABLE `contrat` (
  `id_Contrat` int(11) NOT NULL,
  `reference_Contrat` varchar(40) NOT NULL,
  `dateDebut_Contrat` date DEFAULT NULL,
  `dateFin_Contrat` date DEFAULT NULL,
  `Statut_Contrat` varchar(30) DEFAULT 'Actif',
  `idabonne_abonnes` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contrat`
--

INSERT INTO `contrat` (`id_Contrat`, `reference_Contrat`, `dateDebut_Contrat`, `dateFin_Contrat`, `Statut_Contrat`, `idabonne_abonnes`) VALUES
(1, 'd-q', '2026-04-18', '2026-05-29', 'Actif', 4);

-- --------------------------------------------------------

--
-- Table structure for table `paiement`
--

CREATE TABLE `paiement` (
  `id_paiement` int(11) NOT NULL,
  `date_paiement` date DEFAULT curdate(),
  `montant_paiement` decimal(10,2) NOT NULL,
  `mode_paiement` varchar(30) DEFAULT 'Esp??ces',
  `Statut_paiement` varchar(30) DEFAULT 'Pay??',
  `releve_id_releve` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `paiement`
--

INSERT INTO `paiement` (`id_paiement`, `date_paiement`, `montant_paiement`, `mode_paiement`, `Statut_paiement`, `releve_id_releve`) VALUES
(1, '2026-04-17', 700.00, 'Espèces', 'Payé', 1);

-- --------------------------------------------------------

--
-- Table structure for table `point_de_service`
--

CREATE TABLE `point_de_service` (
  `id_Point_de_service` int(11) NOT NULL,
  `libelle_Point_de_service` varchar(60) NOT NULL,
  `adresse_Point_de_service` varchar(60) DEFAULT NULL,
  `id_Contrat` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `point_de_service`
--

INSERT INTO `point_de_service` (`id_Point_de_service`, `libelle_Point_de_service`, `adresse_Point_de_service`, `id_Contrat`) VALUES
(1, 'down-town', 'buyenzi', 1);

-- --------------------------------------------------------

--
-- Table structure for table `releve`
--

CREATE TABLE `releve` (
  `id_releve` int(11) NOT NULL,
  `periode_releve` varchar(30) DEFAULT NULL,
  `date_releve` datetime DEFAULT current_timestamp(),
  `indexPrecedent_releve` decimal(10,2) DEFAULT 0.00,
  `indexSuivant_releve` decimal(10,2) DEFAULT 0.00,
  `ConsommationCalculee_releve` decimal(10,2) DEFAULT 0.00,
  `id_Compteur` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `releve`
--

INSERT INTO `releve` (`id_releve`, `periode_releve`, `date_releve`, `indexPrecedent_releve`, `indexSuivant_releve`, `ConsommationCalculee_releve`, `id_Compteur`) VALUES
(1, '04/2026', '2026-04-17 12:28:00', 30.00, 70.00, 40.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id_utilisateur` int(11) NOT NULL,
  `nom_utilisateur` varchar(50) NOT NULL,
  `prenom_utilisateur` varchar(50) NOT NULL,
  `email_utilisateur` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role_utilisateur` enum('Utilisateur','Agent','Administrateur') NOT NULL DEFAULT 'Utilisateur',
  `statut_utilisateur` enum('Actif','Inactif') NOT NULL DEFAULT 'Actif',
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id_utilisateur`, `nom_utilisateur`, `prenom_utilisateur`, `email_utilisateur`, `mot_de_passe`, `role_utilisateur`, `statut_utilisateur`, `date_creation`) VALUES
(1, 'Admin', 'RESIDESO', 'admin@resideso.com', '$2y$12$598I2DFy1uaeM3bgTEQyI.9ho85F5WZADRiYo3zBxZSq4zsM.sGBq', 'Administrateur', 'Actif', '2026-04-17 11:59:50'),
(2, 'hakizimana', 'kevin', 'khakizimana@gmail.com', '$2y$12$vEjQBf8j57AnwvSSeO5X6uKHtKnM8IAJeKq1bSc0tfL3DVdZQbPXq', 'Agent', 'Actif', '2026-04-17 12:10:07'),
(3, 'amiss', 'baijane', 'amiss@gmail.com', '$2y$12$SyAsZiOFMM0hRpgXJurjK.gHkTGIMWnsTebGq.g/fCQMx0fN6xIZi', 'Utilisateur', 'Actif', '2026-04-17 12:19:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `abonne`
--
ALTER TABLE `abonne`
  ADD PRIMARY KEY (`idabonne_abonnes`);

--
-- Indexes for table `compteur`
--
ALTER TABLE `compteur`
  ADD PRIMARY KEY (`id_Compteur`),
  ADD KEY `FK_Compteur_Point_de_service` (`id_Point_de_service`);

--
-- Indexes for table `contrat`
--
ALTER TABLE `contrat`
  ADD PRIMARY KEY (`id_Contrat`),
  ADD KEY `FK_Contrat_abonne` (`idabonne_abonnes`);

--
-- Indexes for table `paiement`
--
ALTER TABLE `paiement`
  ADD PRIMARY KEY (`id_paiement`),
  ADD KEY `FK_paiement_releve` (`releve_id_releve`);

--
-- Indexes for table `point_de_service`
--
ALTER TABLE `point_de_service`
  ADD PRIMARY KEY (`id_Point_de_service`),
  ADD KEY `FK_Point_de_service_Contrat` (`id_Contrat`);

--
-- Indexes for table `releve`
--
ALTER TABLE `releve`
  ADD PRIMARY KEY (`id_releve`),
  ADD KEY `FK_releve_Compteur` (`id_Compteur`);

--
-- Indexes for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD UNIQUE KEY `email_utilisateur` (`email_utilisateur`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `abonne`
--
ALTER TABLE `abonne`
  MODIFY `idabonne_abonnes` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `compteur`
--
ALTER TABLE `compteur`
  MODIFY `id_Compteur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contrat`
--
ALTER TABLE `contrat`
  MODIFY `id_Contrat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `paiement`
--
ALTER TABLE `paiement`
  MODIFY `id_paiement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `point_de_service`
--
ALTER TABLE `point_de_service`
  MODIFY `id_Point_de_service` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `releve`
--
ALTER TABLE `releve`
  MODIFY `id_releve` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `compteur`
--
ALTER TABLE `compteur`
  ADD CONSTRAINT `FK_Compteur_Point_de_service` FOREIGN KEY (`id_Point_de_service`) REFERENCES `point_de_service` (`id_Point_de_service`) ON DELETE CASCADE;

--
-- Constraints for table `contrat`
--
ALTER TABLE `contrat`
  ADD CONSTRAINT `FK_Contrat_abonne` FOREIGN KEY (`idabonne_abonnes`) REFERENCES `abonne` (`idabonne_abonnes`) ON DELETE CASCADE;

--
-- Constraints for table `paiement`
--
ALTER TABLE `paiement`
  ADD CONSTRAINT `FK_paiement_releve` FOREIGN KEY (`releve_id_releve`) REFERENCES `releve` (`id_releve`) ON DELETE CASCADE;

--
-- Constraints for table `point_de_service`
--
ALTER TABLE `point_de_service`
  ADD CONSTRAINT `FK_Point_de_service_Contrat` FOREIGN KEY (`id_Contrat`) REFERENCES `contrat` (`id_Contrat`) ON DELETE CASCADE;

--
-- Constraints for table `releve`
--
ALTER TABLE `releve`
  ADD CONSTRAINT `FK_releve_Compteur` FOREIGN KEY (`id_Compteur`) REFERENCES `compteur` (`id_Compteur`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
