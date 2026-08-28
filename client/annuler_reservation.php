<?php
/**
 * client/annuler_reservation.php
 * Permet à un client d'annuler l'une de SES réservations, uniquement si elle est encore "en_attente".
 */
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../auth/auth_check.php';
requireClient();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('mes_reservations.php');
}

if (!verifierCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlash('danger', 'Requête invalide, veuillez réessayer.');
    redirect('mes_reservations.php');
}

$reservationId = (int)($_POST['reservation_id'] ?? 0);
$utilisateurId = $_SESSION['utilisateur_id'];

// On vérifie que la réservation appartient bien à l'utilisateur connecté
$stmt = $pdo->prepare('SELECT * FROM reservations WHERE id = :id AND utilisateur_id = :uid');
$stmt->execute([':id' => $reservationId, ':uid' => $utilisateurId]);
$reservation = $stmt->fetch();

if (!$reservation) {
    setFlash('danger', 'Réservation introuvable.');
    redirect('mes_reservations.php');
}

if ($reservation['statut'] !== 'en_attente') {
    setFlash('warning', 'Seules les réservations en attente peuvent être annulées.');
    redirect('mes_reservations.php');
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('UPDATE reservations SET statut = "annulee" WHERE id = :id');
    $stmt->execute([':id' => $reservationId]);

    // Le véhicule redevient disponible s'il n'a pas d'autre réservation active
    $stmt = $pdo->prepare('UPDATE vehicules SET statut = "disponible" WHERE id = :id AND statut = "reserve"');
    $stmt->execute([':id' => $reservation['vehicule_id']]);

    $pdo->commit();
    setFlash('success', 'Votre réservation a été annulée.');
} catch (Exception $e) {
    $pdo->rollBack();
    setFlash('danger', "Erreur lors de l'annulation.");
}

redirect('mes_reservations.php');
