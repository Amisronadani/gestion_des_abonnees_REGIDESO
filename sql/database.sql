-- ============================================================
-- RESIDESO - Système de Gestion des Abonnés
-- Script SQL complet - Base de données
-- ============================================================

CREATE DATABASE IF NOT EXISTS gestion_abonnes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_abonnes;

-- ============================================================
-- Table des utilisateurs de l'application
-- ============================================================
DROP TABLE IF EXISTS utilisateurs;
CREATE TABLE utilisateurs (
    id_utilisateur INT AUTO_INCREMENT NOT NULL,
    nom_utilisateur VARCHAR(50) NOT NULL,
    prenom_utilisateur VARCHAR(50) NOT NULL,
    email_utilisateur VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role_utilisateur ENUM('Utilisateur', 'Agent', 'Administrateur') NOT NULL DEFAULT 'Utilisateur',
    statut_utilisateur ENUM('Actif', 'Inactif') NOT NULL DEFAULT 'Actif',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_utilisateur)
) ENGINE=InnoDB;

-- ============================================================
-- Table des abonnés
-- ============================================================
DROP TABLE IF EXISTS paiement;
DROP TABLE IF EXISTS releve;
DROP TABLE IF EXISTS compteur;
DROP TABLE IF EXISTS point_de_service;
DROP TABLE IF EXISTS contrat;
DROP TABLE IF EXISTS abonne;

CREATE TABLE abonne (
    idabonne_abonnes INT AUTO_INCREMENT NOT NULL,
    nom_abonnes VARCHAR(40) NOT NULL,
    prenom_abonnes VARCHAR(40) NOT NULL,
    type_abonne_abonnes VARCHAR(40),
    telephone_abonnes VARCHAR(30),
    email_abonnes VARCHAR(40),
    adresse_abonne VARCHAR(40),
    PRIMARY KEY (idabonne_abonnes)
) ENGINE=InnoDB;

-- ============================================================
-- Table des contrats
-- ============================================================
CREATE TABLE contrat (
    id_Contrat INT AUTO_INCREMENT NOT NULL,
    reference_Contrat VARCHAR(40) NOT NULL,
    dateDebut_Contrat DATE,
    dateFin_Contrat DATE,
    Statut_Contrat VARCHAR(30) DEFAULT 'Actif',
    idabonne_abonnes INT NOT NULL,
    PRIMARY KEY (id_Contrat),
    CONSTRAINT FK_Contrat_abonne FOREIGN KEY (idabonne_abonnes) REFERENCES abonne (idabonne_abonnes) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- Table des points de service
-- ============================================================
CREATE TABLE point_de_service (
    id_Point_de_service INT AUTO_INCREMENT NOT NULL,
    libelle_Point_de_service VARCHAR(60) NOT NULL,
    adresse_Point_de_service VARCHAR(60),
    id_Contrat INT NOT NULL,
    PRIMARY KEY (id_Point_de_service),
    CONSTRAINT FK_Point_de_service_Contrat FOREIGN KEY (id_Contrat) REFERENCES contrat (id_Contrat) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- Table des compteurs
-- ============================================================
CREATE TABLE compteur (
    id_Compteur INT AUTO_INCREMENT NOT NULL,
    numero_Compteur VARCHAR(30) NOT NULL,
    Typecompteur_Compteur VARCHAR(30),
    DatePose_Compteur DATE,
    Etat_compteur_Compteur VARCHAR(30) DEFAULT 'Actif',
    id_Point_de_service INT NOT NULL,
    PRIMARY KEY (id_Compteur),
    CONSTRAINT FK_Compteur_Point_de_service FOREIGN KEY (id_Point_de_service) REFERENCES point_de_service (id_Point_de_service) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- Table des relevés
-- ============================================================
CREATE TABLE releve (
    id_releve INT AUTO_INCREMENT NOT NULL,
    periode_releve VARCHAR(30),
    date_releve DATETIME DEFAULT CURRENT_TIMESTAMP,
    indexPrecedent_releve DECIMAL(10,2) DEFAULT 0,
    indexSuivant_releve DECIMAL(10,2) DEFAULT 0,
    ConsommationCalculee_releve DECIMAL(10,2) DEFAULT 0,
    id_Compteur INT NOT NULL,
    PRIMARY KEY (id_releve),
    CONSTRAINT FK_releve_Compteur FOREIGN KEY (id_Compteur) REFERENCES compteur (id_Compteur) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- Table des paiements
-- ============================================================
CREATE TABLE paiement (
    id_paiement INT AUTO_INCREMENT NOT NULL,
    date_paiement DATE DEFAULT (CURRENT_DATE),
    montant_paiement DECIMAL(10,2) NOT NULL,
    mode_paiement VARCHAR(30) DEFAULT 'Espèces',
    Statut_paiement VARCHAR(30) DEFAULT 'Payé',
    releve_id_releve INT NOT NULL,
    PRIMARY KEY (id_paiement),
    CONSTRAINT FK_paiement_releve FOREIGN KEY (releve_id_releve) REFERENCES releve (id_releve) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- Insertion d'un administrateur par défaut
-- Mot de passe: admin123 (hashé avec password_hash)
-- ============================================================
INSERT INTO utilisateurs (nom_utilisateur, prenom_utilisateur, email_utilisateur, mot_de_passe, role_utilisateur, statut_utilisateur)
VALUES ('Admin', 'RESIDESO', 'admin@resideso.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrateur', 'Actif');

-- ============================================================
-- Données de démonstration
-- ============================================================
INSERT INTO abonne (nom_abonnes, prenom_abonnes, type_abonne_abonnes, telephone_abonnes, email_abonnes, adresse_abonne) VALUES
('Dupont', 'Jean', 'Particulier', '0601020304', 'jean.dupont@email.fr', '12 Rue de Paris'),
('Martin', 'Marie', 'Particulier', '0605060708', 'marie.martin@email.fr', '45 Avenue Victor Hugo'),
('Société ABC', 'Direction', 'Professionnel', '0147258369', 'contact@abc.fr', '8 Boulevard Haussmann');
