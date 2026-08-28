# 🚗 Gestion de Location de Voitures

Projet universitaire (L2) — Application web complète de gestion de location de véhicules, développée en PHP 8 / MySQL avec PDO.

---

## 1. Présentation du projet

**LocaAuto Madagascar** est une plateforme web permettant :

- à un **client** de consulter le catalogue de véhicules, réserver un véhicule, suivre ses réservations, consulter ses factures et annuler une réservation en attente ;
- à un **administrateur** de gérer la flotte de véhicules (ajout / modification / suppression), valider ou refuser les réservations, générer les factures, suivre les paiements et gérer les retours de véhicules.

Le parcours complet implémenté est :

```
Authentification → Catalogue → Réservation → Validation (admin)
→ Facturation → Paiement → Retour du véhicule → Disponibilité mise à jour
```

---

## 2. Technologies utilisées

| Côté | Technologies |
|------|--------------|
| Backend | PHP 8+, PDO (requêtes préparées) |
| Base de données | MySQL |
| Frontend | HTML5, CSS3, Bootstrap 5, JavaScript (vanilla) |
| Icônes | Font Awesome 6 |
| Sécurité | `password_hash()` / `password_verify()`, sessions PHP, jetons CSRF, `htmlspecialchars()` |

Aucun framework PHP lourd n'est utilisé : le projet reste simple et lisible pour un projet universitaire.

---

## 3. Prérequis

- [XAMPP](https://www.apachefriends.org/fr/index.html) (Apache + MySQL + PHP 8+)
- Un navigateur web récent
- (Optionnel) phpMyAdmin, inclus avec XAMPP

---

## 4. Installation de XAMPP

1. Téléchargez et installez XAMPP pour votre système d'exploitation.
2. Lancez le **XAMPP Control Panel**.
3. Démarrez les modules **Apache** et **MySQL**.

---

## 5. Création de la base de données

1. Ouvrez votre navigateur sur `http://localhost/phpmyadmin`.
2. Cliquez sur **Nouvelle base de données**.
3. Nommez-la `gestion_location_voitures` (ou laissez le script SQL la créer automatiquement, voir étape suivante).

---

## 6. Importation de `schema_bdd.sql`

1. Dans phpMyAdmin, sélectionnez la base `gestion_location_voitures` (ou la racine du serveur si le script ne l'a pas encore créée).
2. Allez dans l'onglet **Importer**.
3. Choisissez le fichier `database/schema_bdd.sql`.
4. Cliquez sur **Exécuter**.

Cela crée automatiquement la base et toutes les tables (`utilisateurs`, `vehicules`, `reservations`, `paiements`, `factures`, `retours`) avec leurs clés primaires, étrangères et index.

---

## 7. Importation de `insert_bdd.sql`

1. Toujours dans l'onglet **Importer** de phpMyAdmin (base `gestion_location_voitures` sélectionnée).
2. Choisissez le fichier `database/insert_bdd.sql`.
3. Cliquez sur **Exécuter**.

Cela insère les données de test : 4 utilisateurs, 10 véhicules, 5 réservations, paiements, factures et un retour.

---

## 8. Configuration de `db_config.php`

Ouvrez `config/db_config.php` et adaptez si nécessaire les identifiants de connexion (par défaut, ceux d'une installation XAMPP standard) :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_location_voitures');
define('DB_USER', 'root');
define('DB_PASS', ''); // vide par défaut sous XAMPP
```

---

## 9. Placement du projet dans `htdocs`

1. Copiez le dossier complet `gestion-location-voitures/` dans :
   - **Windows** : `C:\xampp\htdocs\`
   - **macOS** : `/Applications/XAMPP/htdocs/`
   - **Linux** : `/opt/lampp/htdocs/`

Le chemin final doit ressembler à :

```
C:\xampp\htdocs\gestion-location-voitures\
```

---

## 10. Démarrage d'Apache et MySQL

Dans le XAMPP Control Panel, vérifiez que les modules **Apache** et **MySQL** affichent bien le statut "Running" (fond vert).

---

## 11. URL pour accéder au projet

Ouvrez votre navigateur à l'adresse :

```
http://localhost/gestion-location-voitures/
```

---

## 12. Comptes de test

Tous les comptes de test utilisent le mot de passe : **`password123`**

| Rôle | Email | Mot de passe |
|------|-------|---------------|
| Administrateur | `admin@location.mg` | `password123` |
| Client | `jean.rakoto@gmail.com` | `password123` |
| Client | `marie.rasoa@gmail.com` | `password123` |
| Client | `paul.randria@gmail.com` | `password123` |

---

## 13. Rôles des utilisateurs

- **client** : peut consulter le catalogue, réserver un véhicule disponible, consulter/annuler ses réservations, consulter ses factures, modifier son profil.
- **admin** : accède au tableau de bord, gère les véhicules (CRUD), valide/refuse les réservations, gère les paiements, génère les factures, enregistre les retours de véhicules.

Les pages d'administration (`admin/`) sont protégées par `auth/auth_check.php` + `requireAdmin()` : un utilisateur non-admin qui tente d'y accéder directement est redirigé avec un message d'erreur.

---

## 14. Structure du projet

```
gestion-location-voitures/
│
├── index.php                     # Page d'accueil
│
├── config/
│   └── db_config.php              # Connexion PDO + session + constantes
│
├── includes/
│   ├── functions.php              # Fonctions utilitaires (sécurité, formatage, etc.)
│   ├── header.php                 # <head> commun
│   ├── navbar.php                 # Barre de navigation + messages flash
│   └── footer.php                 # Pied de page commun
│
├── auth/
│   ├── login.php                  # Connexion
│   ├── register.php               # Inscription
│   ├── logout.php                 # Déconnexion
│   ├── auth_check.php             # Protection des pages (session + rôles)
│   └── profil.php                 # Gestion du profil utilisateur
│
├── admin/
│   ├── admin_dashboard.php        # Tableau de bord + statistiques
│   ├── ajouter_vehicule.php       # Ajouter un véhicule
│   ├── modifier_vehicule.php      # Modifier un véhicule
│   ├── supprimer_vehicule.php     # Supprimer un véhicule
│   ├── valider_reservation.php    # Valider / refuser une réservation
│   ├── facture.php                # Génération et affichage de facture
│   ├── retour_vehicule.php        # Gestion des retours
│   └── suivi_paiements.php        # Suivi et mise à jour des paiements
│
├── client/
│   ├── catalogue.php              # Catalogue des véhicules (recherche/filtres)
│   ├── reservation.php            # Formulaire de réservation
│   ├── traiter_reservation.php    # Traitement backend de la réservation
│   ├── mes_reservations.php       # Historique des réservations du client
│   └── annuler_reservation.php    # Annulation d'une réservation en attente
│
├── assets/
│   ├── css/style.css              # Styles du site
│   ├── js/script.js               # Calcul dynamique du prix, validations
│   └── images/                    # Images des véhicules
│
├── database/
│   ├── schema_bdd.sql             # Script de création de la base
│   └── insert_bdd.sql             # Données de test
│
└── README.md
```

---

## 15. Répartition des tâches de l'équipe

| Membre | Rôle | Branche Git |
|--------|------|-------------|
| **Tiantsoa** | Chef de projet — Base de données & intégration | `main` |
| **Mandresy** | Backend authentification & sécurité | `feature/auth` |
| **Andry** | Backend gestion de la flotte | `feature/flotte` |
| **Ravaka** | Frontend catalogue & réservation | `feature/catalogue` |
| **Rotsy** | Backend facturation, retour & réservation | `feature/facturation` |

---

## 16. Fonctionnement du système (résumé)

1. Le client parcourt le catalogue et choisit un véhicule disponible.
2. Il se connecte (ou crée un compte) puis renseigne ses dates de location.
3. Le prix est calculé côté JavaScript (indicatif) **et recalculé côté PHP** avant l'enregistrement (sécurité anti-manipulation).
4. La réservation est enregistrée avec le statut `en_attente` ; le véhicule passe en `reserve`.
5. L'administrateur valide ou refuse la réservation depuis son espace.
   - **Validée** → le véhicule passe en `loue`, un paiement `en_attente` est créé automatiquement.
   - **Refusée** → le véhicule redevient `disponible`.
6. L'administrateur peut générer la facture et suivre le paiement (`en_attente`, `paye`, `annule`).
7. Lorsque le client rend le véhicule, l'administrateur enregistre le retour (état, kilométrage, remarques).
   - La réservation passe à `terminee`.
   - Le véhicule redevient `disponible` (ou `maintenance` s'il est endommagé).

---

## 17. Sécurité mise en œuvre

- Connexion à MySQL exclusivement via **PDO** avec requêtes préparées (protection contre les injections SQL).
- Mots de passe hashés avec `password_hash()` et vérifiés avec `password_verify()`.
- Sessions PHP sécurisées, régénération de l'identifiant de session à la connexion (`session_regenerate_id`).
- Vérification systématique des rôles (`isLoggedIn()`, `isAdmin()`, `requireAdmin()`, `requireClient()`).
- Jetons **CSRF** sur tous les formulaires sensibles (ajout, modification, suppression, validation, paiement, retour).
- Échappement systématique des données affichées avec `htmlspecialchars()` (fonction `e()`).
- Contrôle des fichiers uploadés (extension, taille) pour les images de véhicules.
- Double contrôle de disponibilité d'un véhicule (empêche la réservation d'un véhicule déjà réservé sur la même période).
- Recalcul systématique du prix côté serveur avant tout enregistrement.

---

## 18. Notes de développement

- Le projet est directement fonctionnel après importation des scripts SQL et configuration de `db_config.php`, sans étape de build supplémentaire.
- Le devise utilisée pour les prix est l'**Ariary (Ar)**, monnaie de Madagascar.
- Des images de véhicules d'exemple sont fournies dans `assets/images/`. Elles peuvent être remplacées par de vraies photos (même nom de fichier ou mise à jour du champ `image` en base).
