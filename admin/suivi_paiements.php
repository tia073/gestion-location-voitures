<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../auth/auth_check.php';
requireAdmin();

// Mise à jour du statut d'un paiement / de la méthode de paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifierCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Requête invalide, veuillez réessayer.');
        redirect('suivi_paiements.php');
    }

    $paiementId = (int)($_POST['paiement_id'] ?? 0);
    $nouveauStatut = $_POST['statut'] ?? '';
    $methode = $_POST['methode'] ?? '';

    $statutsValides = ['en_attente', 'paye', 'annule'];
    $methodesValides = ['especes', 'carte_bancaire', 'mobile_money', 'virement'];

    if (in_array($nouveauStatut, $statutsValides, true) && in_array($methode, $methodesValides, true)) {
        $stmt = $pdo->prepare('UPDATE paiements SET statut = :statut, methode = :methode WHERE id = :id');
        $stmt->execute([':statut' => $nouveauStatut, ':methode' => $methode, ':id' => $paiementId]);
        setFlash('success', 'Le paiement a été mis à jour.');
    } else {
        setFlash('danger', 'Données invalides.');
    }

    redirect('suivi_paiements.php');
}

$stmt = $pdo->query(
    'SELECT p.*, r.id AS reservation_id, r.prix_total, u.nom, u.prenom, v.marque, v.modele
     FROM paiements p
     JOIN reservations r ON r.id = p.reservation_id
     JOIN utilisateurs u ON u.id = r.utilisateur_id
     JOIN vehicules v ON v.id = r.vehicule_id
     ORDER BY p.date_paiement DESC'
);
$paiements = $stmt->fetchAll();

$totalPaye = 0;
$totalEnAttente = 0;
foreach ($paiements as $p) {
    if ($p['statut'] === 'paye') $totalPaye += $p['montant'];
    if ($p['statut'] === 'en_attente') $totalEnAttente += $p['montant'];
}

$pageTitle = 'Suivi des paiements';
$assetsPath = '../assets/';
$basePath = '../';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fa-solid fa-money-bill-wave"></i> Suivi des paiements</h2>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card stat-card shadow-sm">
                <div class="card-body">
                    <h3><?= formatMontant($totalPaye) ?></h3>
                    <p class="mb-0">Total encaissé</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stat-card stat-card-3 shadow-sm">
                <div class="card-body">
                    <h3><?= formatMontant($totalEnAttente) ?></h3>
                    <p class="mb-0">Total en attente</p>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($paiements)): ?>
        <div class="alert alert-info">Aucun paiement enregistré pour le moment.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover bg-white shadow-sm align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th><th>Client</th><th>Véhicule</th><th>Montant</th><th>Méthode</th><th>Statut</th><th>Date</th><th>Mettre à jour</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paiements as $p): ?>
                        <tr>
                            <td>#<?= (int)$p['reservation_id'] ?></td>
                            <td><?= e($p['prenom']) ?> <?= e($p['nom']) ?></td>
                            <td><?= e($p['marque']) ?> <?= e($p['modele']) ?></td>
                            <td><?= formatMontant($p['montant']) ?></td>
                            <td><?= e(ucfirst(str_replace('_', ' ', $p['methode']))) ?></td>
                            <td><?= badgeStatutPaiement($p['statut']) ?></td>
                            <td><?= formatDateHeure($p['date_paiement']) ?></td>
                            <td>
                                <form method="POST" action="suivi_paiements.php" class="d-flex gap-1 flex-wrap">
                                    <input type="hidden" name="paiement_id" value="<?= (int)$p['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= e(genererCsrfToken()) ?>">
                                    <select name="methode" class="form-select form-select-sm" style="width:auto;">
                                        <?php foreach (['especes', 'carte_bancaire', 'mobile_money', 'virement'] as $m): ?>
                                            <option value="<?= $m ?>" <?= $p['methode'] === $m ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $m)) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select name="statut" class="form-select form-select-sm" style="width:auto;">
                                        <?php foreach (['en_attente', 'paye', 'annule'] as $s): ?>
                                            <option value="<?= $s ?>" <?= $p['statut'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa-solid fa-check"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
