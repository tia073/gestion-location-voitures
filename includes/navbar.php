<?php
/**
 * includes/navbar.php
 * Barre de navigation commune.
 * Variables attendues : $basePath (chemin relatif vers la racine du projet, ex: "" ou "../")
 */
if (!isset($basePath)) {
    $basePath = '';
}
$flashMessages = getFlashMessages();
?>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
    <div class="container">
        <a class="navbar-brand" href="<?= $basePath ?>index.php">
            <i class="fa-solid fa-car-side"></i> <?= e(SITE_NAME) ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>index.php">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>client/catalogue.php">Catalogue</a></li>
                <?php if (isLoggedIn() && !isAdmin()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>client/mes_reservations.php">Mes réservations</a></li>
                <?php endif; ?>
                <?php if (isAdmin()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>admin/admin_dashboard.php">Dashboard Admin</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>auth/profil.php">
                        <i class="fa-solid fa-user"></i> <?= e($_SESSION['prenom'] ?? '') ?>
                    </a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>auth/logout.php">
                        <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
                    </a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>auth/login.php">Connexion</a></li>
                    <li class="nav-item">
                        <a class="btn btn-light btn-sm ms-2 mt-1" href="<?= $basePath ?>auth/register.php">Inscription</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if (!empty($flashMessages)): ?>
    <div class="container mt-3">
        <?php foreach ($flashMessages as $msg): ?>
            <div class="alert alert-<?= e($msg['type']) ?> alert-dismissible fade show" role="alert">
                <?= e($msg['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
