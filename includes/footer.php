<?php
/**
 * includes/footer.php
 */
if (!isset($basePath)) {
    $basePath = '';
}
?>
<footer class="app-footer mt-5">
    <div class="container py-4">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5><i class="fa-solid fa-car-side"></i> <?= e(SITE_NAME) ?></h5>
                <p class="small mb-0">Votre partenaire de confiance pour la location de voitures à Madagascar.</p>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Liens rapides</h5>
                <ul class="list-unstyled small">
                    <li><a href="<?= $basePath ?>index.php">Accueil</a></li>
                    <li><a href="<?= $basePath ?>client/catalogue.php">Catalogue</a></li>
                    <li><a href="<?= $basePath ?>auth/login.php">Connexion</a></li>
                    <li><a href="<?= $basePath ?>auth/register.php">Inscription</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Contact</h5>
                <ul class="list-unstyled small">
                    <li><i class="fa-solid fa-location-dot"></i> Antananarivo, Madagascar</li>
                    <li><i class="fa-solid fa-phone"></i> +261 34 12 345 67</li>
                    <li><i class="fa-solid fa-envelope"></i> contact@locaauto.mg</li>
                </ul>
            </div>
        </div>
        <hr>
        <p class="text-center small mb-0">&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?> — Projet universitaire L2.</p>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $basePath ?>assets/js/script.js"></script>
</body>
</html>
