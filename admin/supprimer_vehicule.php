<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../auth/auth_check.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin_dashboard.php');
}

if (!verifierCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlash('danger', 'Requête invalide, veuillez réessayer.');
    redirect('admin_dashboard.php');
}

$id = (int)($_POST['id'] ?? 0);

// Empêche la suppression d'un véhicule ayant des réservations actives (en_attente ou validee)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE vehicule_id = :id AND statut IN ('en_attente', 'validee')");
$stmt->execute([':id' => $id]);

if ($stmt->fetchColumn() > 0) {
    setFlash('danger', 'Impossible de supprimer ce véhicule : il possède des réservations actives.');
    redirect('admin_dashboard.php');
}

$stmt = $pdo->prepare('DELETE FROM vehicules WHERE id = :id');
$stmt->execute([':id' => $id]);

setFlash('success', 'Le véhicule a été supprimé.');
redirect('admin_dashboard.php');
