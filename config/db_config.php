*database/db_config.php
<?php
/**
 * config/db_config.php
 * Connexion à la base de données MySQL via PDO
 * + démarrage de la session + constantes globales du projet
 */

// ---------------------------------------------------------------------
// Paramètres de connexion (à adapter si nécessaire)
// ---------------------------------------------------------------------
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3307');
define('DB_NAME', 'gestion_location_voitures');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ---------------------------------------------------------------------
// Connexion PDO
// ---------------------------------------------------------------------
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die('Erreur de connexion à la base de données : ' . htmlspecialchars($e->getMessage()));
}

// ---------------------------------------------------------------------
// Démarrage de la session (une seule fois)
// ---------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------------------------------------------------------------------
// Constantes globales du projet
// ---------------------------------------------------------------------
define('SITE_NAME', 'LocaAuto Madagascar');
define('DEVISE', 'Ar');
