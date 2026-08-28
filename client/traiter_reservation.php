<?php
/**
 * client/traiter_reservation.php
 * Traite la soumission du formulaire de réservation.
 * Recalcule le prix côté serveur (ne fait jamais confiance au client) et vérifie la disponibilité.
 */
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../auth/auth_check.php';
requireClient();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('catalogue.php');
}

if (!verifierCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlash('danger', 'Requête invalide, veuillez réessayer.');
    redirect('catalogue.php');
}

$vehiculeId = (int)($_POST['vehicule_id'] ?? 0);
$dateDebut = $_POST['date_debut'] ?? '';
$dateFin = $_POST['date_fin'] ?? '';
$utilisateurId = $_SESSION['utilisateur_id'];

// Validation basique des dates
$today = new DateTime('today');
try {
    $dDebut = new DateTime($dateDebut);
    $dFin = new DateTime($dateFin);
} catch (Exception $e) {
    setFlash('danger', 'Dates invalides.');
    redirect('reservation.php?vehicule_id=' . $vehiculeId);
}

if ($dDebut < $today) {
    setFlash('danger', 'La date de départ ne peut pas être dans le passé.');
    redirect('reservation.php?vehicule_id=' . $vehiculeId);
}

if ($dFin <= $dDebut) {
    setFlash('danger', 'La date de retour doit être postérieure à la date de départ.');
    redirect('reservation.php?vehicule_id=' . $vehiculeId);
}

// Récupération du véhicule
$stmt = $pdo->prepare('SELECT * FROM vehicules WHERE id = :id');
$stmt->execute([':id' => $vehiculeId]);
$vehicule = $stmt->fetch();

if (!$vehicule) {
    setFlash('danger', "Ce véhicule n'existe pas.");
    redirect('catalogue.php');
}

if ($vehicule['statut'] !== 'disponible') {
    setFlash('warning', "Ce véhicule n'est plus disponible.");
    redirect('catalogue.php');
}

// Vérification de la disponibilité sur la période demandée (protège contre les doubles réservations)
if (!vehiculeEstDisponible($pdo, $vehiculeId, $dateDebut, $dateFin)) {
    setFlash('danger', 'Ce véhicule est déjà réservé sur la période sélectionnée. Merci de choisir d\'autres dates.');
    redirect('reservation.php?vehicule_id=' . $vehiculeId);
}

// Calcul du prix CÔTÉ SERVEUR (source de vérité, ignore tout prix envoyé par le client)
$nombreJours = calculerNombreJours($dateDebut, $dateFin);
$prixTotal = $nombreJours * (float)$vehicule['prix_jour'];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('INSERT INTO reservations (utilisateur_id, vehicule_id, date_debut, date_fin, nombre_jours, prix_total, statut)
                            VALUES (:uid, :vid, :debut, :fin, :jours, :prix, "en_attente")');
    $stmt->execute([
        ':uid'   => $utilisateurId,
        ':vid'   => $vehiculeId,
        ':debut' => $dateDebut,
        ':fin'   => $dateFin,
        ':jours' => $nombreJours,
        ':prix'  => $prixTotal,
    ]);

    // Le véhicule passe en statut "réservé" en attendant la validation de l'admin
    $stmt = $pdo->prepare('UPDATE vehicules SET statut = "reserve" WHERE id = :id');
    $stmt->execute([':id' => $vehiculeId]);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    setFlash('danger', 'Une erreur est survenue lors de la réservation. Veuillez réessayer.');
    redirect('reservation.php?vehicule_id=' . $vehiculeId);
}

setFlash('success', 'Votre réservation a été enregistrée avec succès ! Elle est en attente de validation par un administrateur.');
redirect('mes_reservations.php');
