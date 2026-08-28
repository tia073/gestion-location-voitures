<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../auth/auth_check.php';
requireAdmin();

// Statistiques générales
$totalVehicules = (int)$pdo->query('SELECT COUNT(*) FROM vehicules')->fetchColumn();
$totalDisponibles = (int)$pdo->query("SELECT COUNT(*) FROM vehicules WHERE statut = 'disponible'")->fetchColumn();
$totalUtilisateurs = (int)$pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'client'")->fetchColumn();
$totalReservationsEnAttente = (int)$pdo->query("SELECT COUNT(*) FROM reservations WHERE statut = 'en_attente'")->fetchColumn();
$totalReservations = (int)$pdo->query('SELECT COUNT(*) FROM reservations')->fetchColumn();
$revenuTotal = (float)$pdo->query("SELECT COALESCE(SUM(montant),0) FROM paiements WHERE statut = 'paye'")->fetchColumn();

// Dernières réservations
$stmt = $pdo->query(
    'SELECT r.*, u.nom, u.prenom, v.marque, v.modele
     FROM reservations r
     JOIN utilisateurs u ON u.id = r.utilisateur_id
     JOIN vehicules v ON v.id = r.vehicule_id
     ORDER BY r.date_creation DESC
     LIMIT 8'
);
$dernieresReservations = $stmt->fetchAll();

$pageTitle = 'Dashboard Administrateur';
$assetsPath = '../assets/';
$basePath = '../';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fa-solid fa-gauge"></i> Tableau de bord administrateur</h2>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card stat-card shadow-sm">
                <div class="card-body">
                    <i class="fa-solid fa-car fa-2x mb-2"></i>
                    <h3><?= $totalVehicules ?></h3>
                    <p class="mb-0">Véhicules (<?= $totalDisponibles ?> disponibles)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card stat-card stat-card-2 shadow-sm">
                <div class="card-body">
                    <i class="fa-solid fa-users fa-2x mb-2"></i>
                    <h3><?= $totalUtilisateurs ?></h3>
                    <p class="mb-0">Clients inscrits</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card stat-card stat-card-3 shadow-sm">
                <div class="card-body">
                    <i class="fa-solid fa-hourglass-half fa-2x mb-2"></i>
                    <h3><?= $totalReservationsEnAttente ?></h3>
                    <p class="mb-0">Réservations en attente</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card stat-card stat-card-4 shadow-sm">
                <div class="card-body">
                    <i class="fa-solid fa-sack-dollar fa-2x mb-2"></i>
                    <h3><?= formatMontant($revenuTotal) ?></h3>
                    <p class="mb-0">Revenu total encaissé</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <a href="ajouter_vehicule.php" class="btn btn-primary w-100 py-3"><i class="fa-solid fa-plus"></i> Ajouter un véhicule</a>
        </div>
        <div class="col-md-3">
            <a href="valider_reservation.php" class="btn btn-warning w-100 py-3"><i class="fa-solid fa-clipboard-check"></i> Gérer les réservations</a>
        </div>
        <div class="col-md-3">
            <a href="suivi_paiements.php" class="btn btn-info w-100 py-3 text-white"><i class="fa-solid fa-money-bill-wave"></i> Suivi des paiements</a>
        </div>
        <div class="col-md-3">
            <a href="retour_vehicule.php" class="btn btn-secondary w-100 py-3"><i class="fa-solid fa-rotate-left"></i> Retours véhicules</a>
        </div>
    </div>

    <h4 class="mb-3">Liste des véhicules</h4>
    <div class="table-responsive mb-5">
        <table class="table table-hover bg-white shadow-sm align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th><th>Véhicule</th><th>Immatriculation</th><th>Prix/jour</th><th>Statut</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $vehicules = $pdo->query('SELECT * FROM vehicules ORDER BY id DESC')->fetchAll();
                foreach ($vehicules as $v):
                ?>
                <tr>
                    <td><?= (int)$v['id'] ?></td>
                    <td><?= e($v['marque']) ?> <?= e($v['modele']) ?> (<?= e($v['annee']) ?>)</td>
                    <td><?= e($v['immatriculation']) ?></td>
                    <td><?= formatMontant($v['prix_jour']) ?></td>
                    <td><?= badgeStatutVehicule($v['statut']) ?></td>
                    <td>
                        <a href="modifier_vehicule.php?id=<?= (int)$v['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                        <form method="POST" action="supprimer_vehicule.php" class="d-inline" onsubmit="return confirm('Supprimer définitivement ce véhicule ?');">
                            <input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= e(genererCsrfToken()) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <h4 class="mb-3">Dernières réservations</h4>
    <div class="table-responsive">
        <table class="table table-hover bg-white shadow-sm align-middle">
            <thead class="table-dark">
                <tr><th>#</th><th>Client</th><th>Véhicule</th><th>Période</th><th>Prix</th><th>Statut</th></tr>
            </thead>
            <tbody>
                <?php foreach ($dernieresReservations as $r): ?>
                <tr>
                    <td><?= (int)$r['id'] ?></td>
                    <td><?= e($r['prenom']) ?> <?= e($r['nom']) ?></td>
                    <td><?= e($r['marque']) ?> <?= e($r['modele']) ?></td>
                    <td><?= formatDate($r['date_debut']) ?> → <?= formatDate($r['date_fin']) ?></td>
                    <td><?= formatMontant($r['prix_total']) ?></td>
                    <td><?= badgeStatutReservation($r['statut']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
