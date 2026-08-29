<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../auth/auth_check.php';
// Accessible aux clients (pour voir LEUR facture) et aux admins (pour toutes les factures)

$reservationId = (int)($_GET['reservation_id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT r.*, u.nom, u.prenom, u.email, u.telephone, u.adresse, v.marque, v.modele, v.immatriculation, v.prix_jour
     FROM reservations r
     JOIN utilisateurs u ON u.id = r.utilisateur_id
     JOIN vehicules v ON v.id = r.vehicule_id
     WHERE r.id = :id'
);
$stmt->execute([':id' => $reservationId]);
$reservation = $stmt->fetch();

if (!$reservation) {
    setFlash('danger', 'Réservation introuvable.');
    redirect(isAdmin() ? 'admin_dashboard.php' : '../client/mes_reservations.php');
}

// Sécurité : un client ne peut voir que SA PROPRE facture
if (!isAdmin() && $reservation['utilisateur_id'] != $_SESSION['utilisateur_id']) {
    setFlash('danger', "Vous n'avez pas accès à cette facture.");
    redirect('../client/mes_reservations.php');
}

if ($reservation['statut'] !== 'validee' && $reservation['statut'] !== 'terminee') {
    setFlash('warning', 'La facture ne peut être générée que pour une réservation validée.');
    redirect(isAdmin() ? 'valider_reservation.php' : '../client/mes_reservations.php');
}

// Récupère la facture existante, ou la génère si elle n'existe pas encore (uniquement l'admin peut générer)
$stmt = $pdo->prepare('SELECT * FROM factures WHERE reservation_id = :rid');
$stmt->execute([':rid' => $reservationId]);
$facture = $stmt->fetch();

if (!$facture && isAdmin()) {
    $numero = genererNumeroFacture($pdo);
    $stmt = $pdo->prepare('INSERT INTO factures (reservation_id, numero_facture, montant_total) VALUES (:rid, :numero, :montant)');
    $stmt->execute([':rid' => $reservationId, ':numero' => $numero, ':montant' => $reservation['prix_total']]);

    $stmt = $pdo->prepare('SELECT * FROM factures WHERE reservation_id = :rid');
    $stmt->execute([':rid' => $reservationId]);
    $facture = $stmt->fetch();
}

if (!$facture) {
    setFlash('info', "Aucune facture n'a encore été générée pour cette réservation.");
    redirect('../client/mes_reservations.php');
}

// Statut du paiement
$stmt = $pdo->prepare('SELECT * FROM paiements WHERE reservation_id = :rid ORDER BY id DESC LIMIT 1');
$stmt->execute([':rid' => $reservationId]);
$paiement = $stmt->fetch();

$pageTitle = 'Facture ' . $facture['numero_facture'];
$assetsPath = '../assets/';
$basePath = '../';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container my-5">
    <div class="card shadow-sm facture-card mx-auto">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h3 class="mb-0"><i class="fa-solid fa-car-side"></i> <?= e(SITE_NAME) ?></h3>
                    <p class="text-muted mb-0">Antananarivo, Madagascar</p>
                    <p class="text-muted">contact@locaauto.mg</p>
                </div>
                <div class="text-end">
                    <h4>FACTURE</h4>
                    <p class="mb-0"><strong><?= e($facture['numero_facture']) ?></strong></p>
                    <p class="text-muted"><?= formatDateHeure($facture['date_emission']) ?></p>
                </div>
            </div>
            <hr>
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Facturé à</h6>
                    <p class="mb-0"><?= e($reservation['prenom']) ?> <?= e($reservation['nom']) ?></p>
                    <p class="mb-0"><?= e($reservation['email']) ?></p>
                    <p class="mb-0"><?= e($reservation['telephone']) ?></p>
                    <p><?= e($reservation['adresse']) ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6>Statut du paiement</h6>
                    <?= $paiement ? badgeStatutPaiement($paiement['statut']) : badgeStatutPaiement('en_attente') ?>
                </div>
            </div>

            <table class="table table-bordered">
                <thead class="table-light">
                    <tr><th>Description</th><th>Période</th><th>Jours</th><th>Prix/jour</th><th class="text-end">Total</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= e($reservation['marque']) ?> <?= e($reservation['modele']) ?> (<?= e($reservation['immatriculation']) ?>)</td>
                        <td><?= formatDate($reservation['date_debut']) ?> → <?= formatDate($reservation['date_fin']) ?></td>
                        <td><?= (int)$reservation['nombre_jours'] ?></td>
                        <td><?= formatMontant($reservation['prix_jour']) ?></td>
                        <td class="text-end"><?= formatMontant($reservation['prix_total']) ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Total à payer</th>
                        <th class="text-end"><?= formatMontant($facture['montant_total']) ?></th>
                    </tr>
                </tfoot>
            </table>

            <div class="d-flex justify-content-between mt-4 no-print">
                <a href="<?= isAdmin() ? 'valider_reservation.php' : '../client/mes_reservations.php' ?>" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Retour
                </a>
                <button onclick="window.print()" class="btn btn-primary"><i class="fa-solid fa-print"></i> Imprimer</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
