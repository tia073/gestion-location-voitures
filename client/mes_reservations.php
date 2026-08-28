<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../auth/auth_check.php';
requireClient();

$utilisateurId = $_SESSION['utilisateur_id'];

$stmt = $pdo->prepare(
    'SELECT r.*, v.marque, v.modele, v.image, v.prix_jour,
            p.statut AS statut_paiement,
            f.numero_facture
     FROM reservations r
     JOIN vehicules v ON v.id = r.vehicule_id
     LEFT JOIN paiements p ON p.reservation_id = r.id
     LEFT JOIN factures f ON f.reservation_id = r.id
     WHERE r.utilisateur_id = :uid
     ORDER BY r.date_creation DESC'
);
$stmt->execute([':uid' => $utilisateurId]);
$reservations = $stmt->fetchAll();

$pageTitle = 'Mes réservations';
$assetsPath = '../assets/';
$basePath = '../';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fa-solid fa-list-check"></i> Mes réservations</h2>

    <?php if (empty($reservations)): ?>
        <div class="alert alert-info">
            Vous n'avez encore aucune réservation. <a href="catalogue.php">Parcourir le catalogue</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle bg-white shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th>Véhicule</th>
                        <th>Période</th>
                        <th>Jours</th>
                        <th>Prix total</th>
                        <th>Statut réservation</th>
                        <th>Paiement</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $r): ?>
                        <tr>
                            <td>
                                <img src="../assets/images/<?= e($r['image']) ?>" alt="" class="reservation-thumb" onerror="this.src='../assets/images/default_car.jpg'">
                                <?= e($r['marque']) ?> <?= e($r['modele']) ?>
                            </td>
                            <td><?= formatDate($r['date_debut']) ?> → <?= formatDate($r['date_fin']) ?></td>
                            <td><?= (int)$r['nombre_jours'] ?></td>
                            <td><?= formatMontant($r['prix_total']) ?></td>
                            <td><?= badgeStatutReservation($r['statut']) ?></td>
                            <td><?= $r['statut_paiement'] ? badgeStatutPaiement($r['statut_paiement']) : '<span class="text-muted">—</span>' ?></td>
                            <td>
                                <?php if ($r['numero_facture']): ?>
                                    <a href="../admin/facture.php?reservation_id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary mb-1">
                                        <i class="fa-solid fa-file-invoice"></i> Facture
                                    </a>
                                <?php endif; ?>
                                <?php if ($r['statut'] === 'en_attente'): ?>
                                    <form method="POST" action="annuler_reservation.php" onsubmit="return confirm('Confirmer l\'annulation de cette réservation ?');" class="d-inline">
                                        <input type="hidden" name="reservation_id" value="<?= (int)$r['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= e(genererCsrfToken()) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-xmark"></i> Annuler
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
