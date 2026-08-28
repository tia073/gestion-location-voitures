<?php
require_once __DIR__ . '/config/db_config.php';
require_once __DIR__ . '/includes/functions.php';

// Véhicules mis en avant sur la page d'accueil
$stmt = $pdo->query("SELECT * FROM vehicules WHERE statut = 'disponible' ORDER BY date_ajout DESC LIMIT 6");
$vehiculesVedette = $stmt->fetchAll();

$pageTitle = 'Accueil';
$assetsPath = 'assets/';
$basePath = '';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<header class="hero-section">
    <div class="container text-center">
        <h1><i class="fa-solid fa-car-side"></i> <?= e(SITE_NAME) ?></h1>
        <p class="lead">Louez la voiture qu'il vous faut, quand vous en avez besoin, partout à Madagascar.</p>
        <a href="client/catalogue.php" class="btn btn-light btn-lg mt-3">
            <i class="fa-solid fa-magnifying-glass"></i> Voir le catalogue
        </a>
        <?php if (!isLoggedIn()): ?>
            <a href="auth/register.php" class="btn btn-outline-light btn-lg mt-3 ms-2">
                <i class="fa-solid fa-user-plus"></i> Créer un compte
            </a>
        <?php endif; ?>
    </div>
</header>

<section class="container my-5">
    <h2 class="text-center mb-5">Comment ça marche ?</h2>
    <div class="row g-4 text-center">
        <div class="col-md-3">
            <div class="how-it-works-step">
                <i class="fa-solid fa-magnifying-glass fa-2x mb-3"></i>
                <h5>1. Choisissez</h5>
                <p class="small">Parcourez notre catalogue et trouvez le véhicule idéal.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="how-it-works-step">
                <i class="fa-solid fa-calendar-check fa-2x mb-3"></i>
                <h5>2. Réservez</h5>
                <p class="small">Sélectionnez vos dates et confirmez votre réservation.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="how-it-works-step">
                <i class="fa-solid fa-file-invoice-dollar fa-2x mb-3"></i>
                <h5>3. Payez</h5>
                <p class="small">Réglez en toute sécurité après validation par notre équipe.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="how-it-works-step">
                <i class="fa-solid fa-road fa-2x mb-3"></i>
                <h5>4. Roulez</h5>
                <p class="small">Récupérez votre véhicule et profitez de votre trajet.</p>
            </div>
        </div>
    </div>
</section>

<section class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Véhicules disponibles</h2>
        <a href="client/catalogue.php" class="btn btn-outline-primary">Voir tout le catalogue</a>
    </div>

    <?php if (empty($vehiculesVedette)): ?>
        <div class="alert alert-info">Aucun véhicule disponible pour le moment.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($vehiculesVedette as $v): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm vehicule-card">
                        <img src="assets/images/<?= e($v['image']) ?>" class="card-img-top" alt="<?= e($v['marque'] . ' ' . $v['modele']) ?>" onerror="this.src='assets/images/default_car.jpg'">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= e($v['marque']) ?> <?= e($v['modele']) ?></h5>
                            <p class="text-muted small mb-2"><?= e($v['categorie']) ?> · <?= e($v['annee']) ?></p>
                            <p class="fw-bold text-primary mb-2"><?= formatMontant($v['prix_jour']) ?> / jour</p>
                            <div class="mt-auto">
                                <a href="client/reservation.php?vehicule_id=<?= (int)$v['id'] ?>" class="btn btn-primary w-100">
                                    <i class="fa-solid fa-calendar-check"></i> Réserver
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
