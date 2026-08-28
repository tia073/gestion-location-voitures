-- =====================================================================
-- Projet : Gestion de Location de Voitures
-- Fichier : schema_bdd.sql
-- Description : Script de création de la base de données complète
-- =====================================================================

CREATE DATABASE IF NOT EXISTS gestion_location_voitures
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE gestion_location_voitures;

-- ---------------------------------------------------------------------
-- Table : utilisateurs
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(30) DEFAULT NULL,
    adresse VARCHAR(255) DEFAULT NULL,
    role ENUM('client', 'admin') NOT NULL DEFAULT 'client',
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table : vehicules
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS vehicules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marque VARCHAR(100) NOT NULL,
    modele VARCHAR(100) NOT NULL,
    annee INT NOT NULL,
    categorie VARCHAR(50) DEFAULT 'Berline',
    immatriculation VARCHAR(50) NOT NULL UNIQUE,
    prix_jour DECIMAL(12,2) NOT NULL,
    statut ENUM('disponible', 'reserve', 'loue', 'maintenance') NOT NULL DEFAULT 'disponible',
    image VARCHAR(255) DEFAULT 'default_car.jpg',
    description TEXT,
    nombre_places INT DEFAULT 5,
    transmission ENUM('manuelle', 'automatique') DEFAULT 'manuelle',
    carburant ENUM('essence', 'diesel', 'electrique', 'hybride') DEFAULT 'essence',
    date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table : reservations
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    vehicule_id INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    nombre_jours INT NOT NULL,
    prix_total DECIMAL(12,2) NOT NULL,
    statut ENUM('en_attente', 'validee', 'refusee', 'annulee', 'terminee') NOT NULL DEFAULT 'en_attente',
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reservation_utilisateur FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateurs(id) ON DELETE CASCADE,
    CONSTRAINT fk_reservation_vehicule FOREIGN KEY (vehicule_id)
        REFERENCES vehicules(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table : paiements
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS paiements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    montant DECIMAL(12,2) NOT NULL,
    methode ENUM('especes', 'carte_bancaire', 'mobile_money', 'virement') NOT NULL DEFAULT 'especes',
    statut ENUM('en_attente', 'paye', 'annule') NOT NULL DEFAULT 'en_attente',
    date_paiement DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_paiement_reservation FOREIGN KEY (reservation_id)
        REFERENCES reservations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table : factures
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS factures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    numero_facture VARCHAR(50) NOT NULL UNIQUE,
    montant_total DECIMAL(12,2) NOT NULL,
    date_emission DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_facture_reservation FOREIGN KEY (reservation_id)
        REFERENCES reservations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table : retours
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS retours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    date_retour DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    etat_vehicule ENUM('bon', 'endommage', 'a_reparer') NOT NULL DEFAULT 'bon',
    kilometrage INT DEFAULT NULL,
    remarques TEXT,
    CONSTRAINT fk_retour_reservation FOREIGN KEY (reservation_id)
        REFERENCES reservations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Index utiles pour les performances
-- ---------------------------------------------------------------------
CREATE INDEX idx_vehicules_statut ON vehicules(statut);
CREATE INDEX idx_reservations_statut ON reservations(statut);
CREATE INDEX idx_reservations_dates ON reservations(date_debut, date_fin);
CREATE INDEX idx_reservations_vehicule ON reservations(vehicule_id);
