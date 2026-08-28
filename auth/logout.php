<?php
/**
 * auth/logout.php
 * Déconnecte l'utilisateur en détruisant proprement la session.
 */
require_once __DIR__ . '/../config/db_config.php';

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']
    );
}

session_destroy();

session_start();
$_SESSION['flash'][] = ['type' => 'success', 'message' => 'Vous avez été déconnecté avec succès.'];

header('Location: ../index.php');
exit;
