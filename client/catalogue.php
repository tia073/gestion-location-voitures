<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../includes/functions.php';

// Filtres
$categorie = trim($_GET['categorie'] ?? '');
$prixMax = $_GET['prix_max'] ?? '';
$recherche = trim($_GET['recherche'] ?? '');

$sql = "SELECT * FROM vehicules WHERE statut = 'disponible'";
$params = [];

if ($categorie !== '') {
    $sql .= " AND categorie = :categorie";
    $params[':categorie'] = $categorie;
}
if ($prixMax !== '' && is_numeric($prixMax)) {
    $sql .= " AND prix_jour <= :prix_max";
    $params[':prix_max'] = $prixMax;
}
if ($recherche !== '') {
    $sql .= " AND (marque LIKE :recherche OR modele LIKE :recherche)";
    $params[':recherche'] = '%' . $recherche . '%';
}
$sql .= " ORDER BY marque, modele";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vehicules = $stmt->fetchAll();

// Liste des catégories pour le filtre
$categories = $pdo->query("SELECT DISTINCT categorie FROM vehicules ORDER BY categorie")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Catalogue des véhicules';
$assetsPath = '../assets/';
$basePath = '../';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fa-solid fa-car"></i> Catalogue des véhicules</h2>

    <form method="GET" action="catalogue.php" class="card shadow-sm p-3 mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Recherche</label>
                <input type="text" name="recherche" class="form-control" placeholder="Marque ou modèle" value="<?= e($recherche) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Catégorie</label>
                <select name="categorie" class="form-select">
                    <option value="">Toutes</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= e($cat) ?>" <?= $categorie === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Prix max / jour (Ar)</label>
                <input type="number" name="prix_max" class="form-control" value="<?= e($prixMax) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-filter"></i> Filtrer</button>
            </div>
        </div>
    </form>

    <?php if (empty($vehicules)): ?>
        <div class="alert alert-info">Aucun véhicule disponible ne correspond à votre recherche.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($vehicules as $v): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="card h-100 shadow-sm vehicule-card">
                        <img src="../assets/images/<?= e($v['image']) ?>" class="card-img-top" alt="<?= e($v['marque'] . ' ' . $v['modele']) ?>" onerror="this.src='../assets/images/default_car.jpg'">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= e($v['marque']) ?> <?= e($v['modele']) ?></h5>
                            <p class="text-muted small mb-1"><?= e($v['categorie']) ?> · <?= e($v['annee']) ?></p>
                            <p class="small mb-2">
                                <i class="fa-solid fa-users"></i> <?= e($v['nombre_places']) ?> places ·
                                <i class="fa-solid fa-gas-pump"></i> <?= e(ucfirst($v['carburant'])) ?> ·
                                <i class="fa-solid fa-gears"></i> <?= e(ucfirst($v['transmission'])) ?>
                            </p>
                            <p class="fw-bold text-primary mb-2"><?= formatMontant($v['prix_jour']) ?> / jour</p>
                            <div class="mt-auto">
                                <a href="reservation.php?vehicule_id=<?= (int)$v['id'] ?>" class="btn btn-primary w-100">
                                    <i class="fa-solid fa-calendar-check"></i> Réserver
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
