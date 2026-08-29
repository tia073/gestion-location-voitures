*database/insert_bdd.sql
-- =====================================================================
-- Projet : Gestion de Location de Voitures
-- Fichier : insert_bdd.sql
-- Description : Données de test réalistes
-- Mot de passe en clair pour TOUS les comptes de test : password123
-- Hash correspondant (bcrypt) : voir ci-dessous
-- =====================================================================

USE gestion_location_voitures;

-- ---------------------------------------------------------------------
-- Utilisateurs de test
-- Mot de passe pour tous : password123
-- ---------------------------------------------------------------------
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, adresse, role) VALUES
('Andrianina', 'Tiantsoa', 'admin@location.mg', '$2b$10$qyCbf3sfq2L0Jb37dc0iu.f3AuCzXl.43KLIaQG1ktie.EKN6ryca', '0341234567', 'Antananarivo, Madagascar', 'admin'),
('Rakoto', 'Jean', 'jean.rakoto@gmail.com', '$2b$10$qyCbf3sfq2L0Jb37dc0iu.f3AuCzXl.43KLIaQG1ktie.EKN6ryca', '0331234567', 'Antananarivo, Madagascar', 'client'),
('Rasoa', 'Marie', 'marie.rasoa@gmail.com', '$2b$10$qyCbf3sfq2L0Jb37dc0iu.f3AuCzXl.43KLIaQG1ktie.EKN6ryca', '0321234567', 'Antsirabe, Madagascar', 'client'),
('Randria', 'Paul', 'paul.randria@gmail.com', '$2b$10$qyCbf3sfq2L0Jb37dc0iu.f3AuCzXl.43KLIaQG1ktie.EKN6ryca', '0341112233', 'Toamasina, Madagascar', 'client');

-- ---------------------------------------------------------------------
-- Véhicules de test
-- ---------------------------------------------------------------------
INSERT INTO vehicules (marque, modele, annee, categorie, immatriculation, prix_jour, statut, image, description, nombre_places, transmission, carburant) VALUES
('Toyota', 'Corolla', 2022, 'Berline', '1234-AA-TA', 120000, 'disponible', 'toyota_corolla.jpg', 'Berline confortable et économique, idéale pour la ville et les longs trajets.', 5, 'automatique', 'essence'),
('Toyota', 'Hilux', 2021, 'Pick-up', '5678-BB-TA', 220000, 'disponible', 'toyota_hilux.jpg', 'Pick-up robuste, parfait pour les routes difficiles et le transport de charges.', 5, 'manuelle', 'diesel'),
('Hyundai', 'Tucson', 2023, 'SUV', '9012-CC-TA', 200000, 'disponible', 'hyundai_tucson.jpg', 'SUV moderne et spacieux avec toutes les options de confort.', 5, 'automatique', 'essence'),
('Renault', 'Clio', 2020, 'Citadine', '3456-DD-TA', 80000, 'disponible', 'renault_clio.jpg', 'Petite citadine maniable, idéale pour circuler en ville.', 5, 'manuelle', 'essence'),
('Kia', 'Sportage', 2022, 'SUV', '7890-EE-TA', 190000, 'reserve', 'kia_sportage.jpg', 'SUV familial fiable avec un grand coffre.', 5, 'automatique', 'diesel'),
('Nissan', 'Navara', 2021, 'Pick-up', '2468-FF-TA', 210000, 'loue', 'nissan_navara.jpg', 'Pick-up puissant adapté aux terrains accidentés.', 5, 'manuelle', 'diesel'),
('Suzuki', 'Swift', 2019, 'Citadine', '1357-GG-TA', 70000, 'disponible', 'suzuki_swift.jpg', 'Voiture compacte et économique en carburant.', 5, 'manuelle', 'essence'),
('Mercedes', 'Classe C', 2023, 'Berline de luxe', '9753-HH-TA', 350000, 'disponible', 'mercedes_classec.jpg', 'Berline de luxe pour vos déplacements professionnels.', 5, 'automatique', 'essence'),
('Toyota', 'Land Cruiser', 2020, '4x4', '8642-II-TA', 300000, 'maintenance', 'toyota_landcruiser.jpg', '4x4 tout-terrain robuste, en cours d\'entretien.', 7, 'automatique', 'diesel'),
('Honda', 'CR-V', 2022, 'SUV', '1122-JJ-TA', 180000, 'disponible', 'honda_crv.jpg', 'SUV confortable avec une excellente tenue de route.', 5, 'automatique', 'essence');

-- ---------------------------------------------------------------------
-- Réservations de test
-- ---------------------------------------------------------------------
INSERT INTO reservations (utilisateur_id, vehicule_id, date_debut, date_fin, nombre_jours, prix_total, statut, date_creation) VALUES
(2, 5, '2026-08-20', '2026-08-23', 3, 570000, 'validee', '2026-08-10 10:00:00'),
(3, 6, '2026-08-15', '2026-08-18', 3, 630000, 'validee', '2026-08-05 09:30:00'),
(2, 1, '2026-07-01', '2026-07-03', 2, 240000, 'terminee', '2026-06-25 14:00:00'),
(4, 3, '2026-08-25', '2026-08-28', 3, 600000, 'en_attente', '2026-08-13 08:00:00'),
(3, 4, '2026-06-10', '2026-06-12', 2, 160000, 'refusee', '2026-06-05 11:00:00');

-- ---------------------------------------------------------------------
-- Paiements de test
-- ---------------------------------------------------------------------
INSERT INTO paiements (reservation_id, montant, methode, statut, date_paiement) VALUES
(1, 570000, 'mobile_money', 'paye', '2026-08-10 10:15:00'),
(2, 630000, 'carte_bancaire', 'paye', '2026-08-05 09:45:00'),
(3, 240000, 'especes', 'paye', '2026-06-25 14:20:00');

-- ---------------------------------------------------------------------
-- Factures de test
-- ---------------------------------------------------------------------
INSERT INTO factures (reservation_id, numero_facture, montant_total, date_emission) VALUES
(1, 'FACT-2026-0001', 570000, '2026-08-10 10:20:00'),
(2, 'FACT-2026-0002', 630000, '2026-08-05 09:50:00'),
(3, 'FACT-2026-0003', 240000, '2026-06-25 14:25:00');

-- ---------------------------------------------------------------------
-- Retours de test
-- ---------------------------------------------------------------------
INSERT INTO retours (reservation_id, date_retour, etat_vehicule, kilometrage, remarques) VALUES
(3, '2026-07-03 09:00:00', 'bon', 45210, 'Véhicule rendu en bon état, plein d\'essence effectué.');
