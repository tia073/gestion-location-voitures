<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../auth/auth_check.php';
requireClient();

$vehiculeId = (int)($_GET['vehicule_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM vehicules WHERE id = :id');
$stmt->execute([':id' => $vehiculeId]);
$vehicule = $stmt->fetch();

if (!$vehicule) {
    setFlash('danger', "Ce véhicule n'existe pas.");
    redirect('catalogue.php');
}

if ($vehicule['statut'] !== 'disponible') {
    setFlash('warning', "Ce véhicule n'est actuellement pas disponible à la réservation.");
    redirect('catalogue.php');
}

$pageTitle = 'Réserver un véhicule';
$assetsPath = '../assets/';
$basePath = '../';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fa-solid fa-calendar-check"></i> Réserver ce véhicule</h2>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <img src="../assets/images/<?= e($vehicule['image']) ?>" class="card-img-top" alt="" onerror="this.src='../assets/images/default_car.jpg'">
                <div class="card-body">
                    <h4><?= e($vehicule['marque']) ?> <?= e($vehicule['modele']) ?> (<?= e($vehicule['annee']) ?>)</h4>
                    <p class="text-muted"><?= e($vehicule['categorie']) ?></p>
                    <p><?= e($vehicule['description']) ?></p>
                    <p class="fw-bold fs-5 text-primary" id="prix-jour" data-prix="<?= e($vehicule['prix_jour']) ?>">
                        <?= formatMontant($vehicule['prix_jour']) ?> / jour
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="traiter_reservation.php" id="form-reservation" novalidate>
                        <input type="hidden" name="vehicule_id" value="<?= (int)$vehicule['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= e(genererCsrfToken()) ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date de départ *</label>
                                <input type="date" name="date_debut" id="date_debut" class="form-control"
                                       min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date de retour *</label>
                                <input type="date" name="date_fin" id="date_fin" class="form-control"
                                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                            </div>
                        </div>

                        <div class="alert alert-light border" id="recap-prix" style="display:none;">
                            <p class="mb-1">Nombre de jours : <strong id="recap-jours">0</strong></p>
                            <p class="mb-0 fs-5">Prix total estimé : <strong id="recap-total" class="text-primary">0 Ar</strong></p>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa-solid fa-check"></i> Confirmer la réservation
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
