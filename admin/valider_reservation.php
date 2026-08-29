<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../auth/auth_check.php';
requireAdmin();

// Traitement de la validation / du refus
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifierCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Requête invalide, veuillez réessayer.');
        redirect('valider_reservation.php');
    }

    $reservationId = (int)($_POST['reservation_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM reservations WHERE id = :id');
    $stmt->execute([':id' => $reservationId]);
    $reservation = $stmt->fetch();

    if (!$reservation) {
        setFlash('danger', 'Réservation introuvable.');
        redirect('valider_reservation.php');
    }

    if ($reservation['statut'] !== 'en_attente') {
        setFlash('warning', 'Cette réservation a déjà été traitée.');
        redirect('valider_reservation.php');
    }

    try {
        $pdo->beginTransaction();

        if ($action === 'valider') {
            $stmt = $pdo->prepare('UPDATE reservations SET statut = "validee" WHERE id = :id');
            $stmt->execute([':id' => $reservationId]);

            // Le véhicule passe en "loué" pendant la durée de la location validée
            $stmt = $pdo->prepare('UPDATE vehicules SET statut = "loue" WHERE id = :id');
            $stmt->execute([':id' => $reservation['vehicule_id']]);

            // Création automatique du paiement en attente
            $stmt = $pdo->prepare('INSERT INTO paiements (reservation_id, montant, methode, statut) VALUES (:rid, :montant, "especes", "en_attente")');
            $stmt->execute([':rid' => $reservationId, ':montant' => $reservation['prix_total']]);

            setFlash('success', 'La réservation #' . $reservationId . ' a été validée.');
        } elseif ($action === 'refuser') {
            $stmt = $pdo->prepare('UPDATE reservations SET statut = "refusee" WHERE id = :id');
            $stmt->execute([':id' => $reservationId]);

            // Le véhicule redevient disponible
            $stmt = $pdo->prepare('UPDATE vehicules SET statut = "disponible" WHERE id = :id');
            $stmt->execute([':id' => $reservation['vehicule_id']]);

            setFlash('success', 'La réservation #' . $reservationId . ' a été refusée.');
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('danger', 'Une erreur est survenue.');
    }

    redirect('valider_reservation.php');
}

// Liste des réservations
$filtre = $_GET['statut'] ?? 'en_attente';
$statutsValides = ['en_attente', 'validee', 'refusee', 'annulee', 'terminee', 'toutes'];
if (!in_array($filtre, $statutsValides, true)) $filtre = 'en_attente';

$sql = 'SELECT r.*, u.nom, u.prenom, u.email, v.marque, v.modele, v.immatriculation
        FROM reservations r
        JOIN utilisateurs u ON u.id = r.utilisateur_id
        JOIN vehicules v ON v.id = r.vehicule_id';
$params = [];
if ($filtre !== 'toutes') {
    $sql .= ' WHERE r.statut = :statut';
    $params[':statut'] = $filtre;
}
$sql .= ' ORDER BY r.date_creation DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservations = $stmt->fetchAll();

$pageTitle = 'Gestion des réservations';
$assetsPath = '../assets/';
$basePath = '../';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fa-solid fa-clipboard-check"></i> Gestion des réservations</h2>

    <ul class="nav nav-pills mb-4">
        <?php
        $onglets = [
            'en_attente' => 'En attente', 'validee' => 'Validées', 'refusee' => 'Refusées',
            'annulee' => 'Annulées', 'terminee' => 'Terminées', 'toutes' => 'Toutes',
        ];
        foreach ($onglets as $key => $label): ?>
            <li class="nav-item">
                <a class="nav-link <?= $filtre === $key ? 'active' : '' ?>" href="valider_reservation.php?statut=<?= $key ?>"><?= $label ?></a>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if (empty($reservations)): ?>
        <div class="alert alert-info">Aucune réservation dans cette catégorie.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover bg-white shadow-sm align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th><th>Client</th><th>Véhicule</th><th>Période</th><th>Jours</th><th>Prix</th><th>Statut</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $r): ?>
                        <tr>
                            <td><?= (int)$r['id'] ?></td>
                            <td><?= e($r['prenom']) ?> <?= e($r['nom']) ?><br><span class="small text-muted"><?= e($r['email']) ?></span></td>
                            <td><?= e($r['marque']) ?> <?= e($r['modele']) ?><br><span class="small text-muted"><?= e($r['immatriculation']) ?></span></td>
                            <td><?= formatDate($r['date_debut']) ?> → <?= formatDate($r['date_fin']) ?></td>
                            <td><?= (int)$r['nombre_jours'] ?></td>
                            <td><?= formatMontant($r['prix_total']) ?></td>
                            <td><?= badgeStatutReservation($r['statut']) ?></td>
                            <td>
                                <?php if ($r['statut'] === 'en_attente'): ?>
                                    <form method="POST" action="valider_reservation.php" class="d-inline">
                                        <input type="hidden" name="reservation_id" value="<?= (int)$r['id'] ?>">
                                        <input type="hidden" name="action" value="valider">
                                        <input type="hidden" name="csrf_token" value="<?= e(genererCsrfToken()) ?>">
                                        <button type="submit" class="btn btn-sm btn-success"><i class="fa-solid fa-check"></i> Valider</button>
                                    </form>
                                    <form method="POST" action="valider_reservation.php" class="d-inline">
                                        <input type="hidden" name="reservation_id" value="<?= (int)$r['id'] ?>">
                                        <input type="hidden" name="action" value="refuser">
                                        <input type="hidden" name="csrf_token" value="<?= e(genererCsrfToken()) ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fa-solid fa-xmark"></i> Refuser</button>
                                    </form>
                                <?php elseif ($r['statut'] === 'validee'): ?>
                                    <a href="facture.php?reservation_id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-file-invoice"></i> Facture</a>
                                    <a href="retour_vehicule.php?reservation_id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-rotate-left"></i> Retour</a>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
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
