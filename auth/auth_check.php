<?php
/**
 * auth/auth_check.php
 * À inclure en haut de toute page nécessitant une connexion.
 * Usage :
 *   require_once __DIR__ . '/../auth/auth_check.php';                 // utilisateur connecté (client ou admin)
 *   require_once __DIR__ . '/../auth/auth_check.php'; requireAdmin(); // page réservée à l'admin
 *
 * Ce fichier suppose que config/db_config.php et includes/functions.php sont déjà chargés
 * (ils sont inclus automatiquement ici par sécurité s'ils ne le sont pas encore).
 */

if (!defined('DB_HOST')) {
    require_once __DIR__ . '/../config/db_config.php';
}
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/../includes/functions.php';
}

// Détermine le chemin relatif vers la racine pour la redirection (auth/ est à 1 niveau de la racine)
if (!isLoggedIn()) {
    setFlash('warning', 'Veuillez vous connecter pour accéder à cette page.');
    redirect('../auth/login.php');
}

/**
 * À appeler explicitement sur les pages réservées à l'administrateur.
 */
function requireAdmin() {
    if (!isAdmin()) {
        setFlash('danger', "Accès refusé : cette page est réservée à l'administrateur.");
        redirect('../index.php');
    }
}

/**
 * À appeler explicitement sur les pages réservées aux clients (non-admin).
 */
function requireClient() {
    if (isAdmin()) {
        setFlash('warning', 'Cette page est réservée aux clients.');
        redirect('../admin/admin_dashboard.php');
    }
}
