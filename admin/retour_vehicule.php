<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../auth/auth_check.php';
requireAdmin();

// Enregistrement d'un retour de véhicule
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifierCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Requête invalide, veuillez réessayer.');
        redirect('retour_vehicule.php');
    }

    $reservationId = (int)($_POST['reservation_id'] ?? 0);
    $etatVehicule = $_POST['etat_vehicule'] ?? 'bon';
    $kilometrage = $_POST['kilometrage'] ?? null;
    $remarques = trim($_POST['remarques'] ?? '');

    $stmt = $pdo->prepare('SELECT * FROM reservations WHERE id = :id');
    $stmt->execute([':id' => $reservationId]);
    $reservation = $stmt->fetch();

    if (!$reservation) {
        setFlash('danger', 'Réservation introuvable.');
        redirect('retour_vehicule.php');
    }

    if ($reservation['statut'] !== 'validee') {
        setFlash('warning', 'Le retour ne peut être enregistré que pour une réservation validée (en cours de location).');
        redirect('retour_vehicule.php');
    }

    $etatsValides = ['bon', 'endommage', 'a_reparer'];
    if (!in_array($etatVehicule, $etatsValides, true)) $etatVehicule = 'bon';

    try {
        $pdo->beginTransaction();

        // Enregistrement du retour
        $stmt = $pdo->prepare('INSERT INTO retours (reservation_id, etat_vehicule, kilometrage, remarques) VALUES (:rid, :etat, :km, :remarques)');
        $stmt->execute([
            ':rid' => $reservationId, ':etat' => $etatVehicule,
            ':km' => $kilometrage !== '' ? $kilometrage : null, ':remarques' => $remarques,
        ]);

        // La réservation passe à "terminée"
        $stmt = $pdo->prepare('UPDATE reservations SET statut = "terminee" WHERE id = :id');
        $stmt->execute([':id' => $reservationId]);

        // Le véhicule redevient disponible, sauf s'il est endommagé (part en maintenance)
        $nouveauStatutVehicule = ($etatVehicule === 'bon') ? 'disponible' : 'maintenance';
        $stmt = $pdo->prepare('UPDATE vehicules SET statut = :statut WHERE id = :id');
        $stmt->execute([':statut' => $nouveauStatutVehicule, ':id' => $reservation['vehicule_id']]);

        $pdo->commit();
        setFlash('success', 'Le retour du véhicule a été enregistré. Le véhicule est maintenant "' . $nouveauStatutVehicule . '".');
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('danger', 'Une erreur est survenue.');
    }

    redirect('retour_vehicule.php');
}

// Réservations validées en attente de retour
$stmt = $pdo->query(
    "SELECT r.*, u.nom, u.prenom, v.marque, v.modele, v.immatriculation
     FROM reservations r
     JOIN utilisateurs u ON u.id = r.utilisateur_id
     JOIN vehicules v ON v.id = r.vehicule_id
     WHERE r.statut = 'validee'
     ORDER BY r.date_fin ASC"
);
$reservationsEnCours = $stmt->fetchAll();

// Historique des retours déjà effectués
$stmt = $pdo->query(
    "SELECT ret.*, r.date_debut, r.date_fin, u.nom, u.prenom, v.marque, v.modele
     FROM retours ret
     JOIN reservations r ON r.id = ret.reservation_id
     JOIN utilisateurs u ON u.id = r.utilisateur_id
     JOIN vehicules v ON v.id = r.vehicule_id
     ORDER BY ret.date_retour DESC
     LIMIT 15"
);
$historiqueRetours = $stmt->fetchAll();

$pageTitle = 'Gestion des retours';
$assetsPath = '../assets/';
$basePath = '../';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fa-solid fa-rotate-left"></i> Gestion des retours de véhicules</h2>

    <h4 class="mb-3">Locations en cours</h4>
    <?php if (empty($reservationsEnCours)): ?>
        <div class="alert alert-info mb-4">Aucune location en cours actuellement.</div>
    <?php else: ?>
        <div class="row g-4 mb-5">
            <?php foreach ($reservationsEnCours as $r): ?>
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?= e($r['marque']) ?> <?= e($r['modele']) ?> (<?= e($r['immatriculation']) ?>)</h5>
                            <p class="mb-1">Client : <?= e($r['prenom']) ?> <?= e($r['nom']) ?></p>
                            <p class="mb-3">Période : <?= formatDate($r['date_debut']) ?> → <?= formatDate($r['date_fin']) ?></p>

                            <form method="POST" action="retour_vehicule.php">
                                <input type="hidden" name="reservation_id" value="<?= (int)$r['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(genererCsrfToken()) ?>">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small">État du véhicule</label>
                                        <select name="etat_vehicule" class="form-select form-select-sm">
                                            <option value="bon">Bon état</option>
                                            <option value="endommage">Endommagé</option>
                                            <option value="a_reparer">À réparer</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Kilométrage</label>
                                        <input type="number" name="kilometrage" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small">Remarques</label>
                                        <textarea name="remarques" class="form-control form-control-sm" rows="2"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-sm btn-primary w-100 mt-2">
                                            <i class="fa-solid fa-check"></i> Enregistrer le retour
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h4 class="mb-3">Historique des retours</h4>
    <?php if (empty($historiqueRetours)): ?>
        <div class="alert alert-info">Aucun retour enregistré pour le moment.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover bg-white shadow-sm align-middle">
                <thead class="table-dark">
                    <tr><th>Client</th><th>Véhicule</th><th>Date de retour</th><th>État</th><th>Kilométrage</th><th>Remarques</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($historiqueRetours as $ret): ?>
                        <tr>
                            <td><?= e($ret['prenom']) ?> <?= e($ret['nom']) ?></td>
                            <td><?= e($ret['marque']) ?> <?= e($ret['modele']) ?></td>
                            <td><?= formatDateHeure($ret['date_retour']) ?></td>
                            <td><?= e(ucfirst(str_replace('_', ' ', $ret['etat_vehicule']))) ?></td>
                            <td><?= $ret['kilometrage'] ? number_format($ret['kilometrage'], 0, ',', ' ') . ' km' : '—' ?></td>
                            <td><?= e($ret['remarques']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
