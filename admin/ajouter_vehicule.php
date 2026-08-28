<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../auth/auth_check.php';
requireAdmin();

$errors = [];
$old = [
    'marque' => '', 'modele' => '', 'annee' => date('Y'), 'categorie' => 'Berline',
    'immatriculation' => '', 'prix_jour' => '', 'description' => '',
    'nombre_places' => 5, 'transmission' => 'manuelle', 'carburant' => 'essence',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifierCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Requête invalide, veuillez réessayer.';
    }

    foreach ($old as $key => $default) {
        $old[$key] = trim($_POST[$key] ?? $default);
    }

    // Validation
    if ($old['marque'] === '') $errors[] = 'La marque est obligatoire.';
    if ($old['modele'] === '') $errors[] = 'Le modèle est obligatoire.';
    if (!ctype_digit((string)$old['annee']) || $old['annee'] < 1990 || $old['annee'] > (int)date('Y') + 1) {
        $errors[] = 'Année invalide.';
    }
    if ($old['immatriculation'] === '') $errors[] = "L'immatriculation est obligatoire.";
    if (!is_numeric($old['prix_jour']) || $old['prix_jour'] <= 0) $errors[] = 'Le prix journalier doit être un nombre positif.';

    // Gestion de l'upload d'image (facultatif)
    $imageName = 'default_car.jpg';
    if (!empty($_FILES['image']['name'])) {
        $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $extensionsAutorisees, true)) {
            $errors[] = 'Format d\'image non autorisé (jpg, jpeg, png, webp uniquement).';
        } elseif ($_FILES['image']['size'] > 3 * 1024 * 1024) {
            $errors[] = "L'image ne doit pas dépasser 3 Mo.";
        } elseif (empty($errors)) {
            $imageName = uniqid('vehicule_', true) . '.' . $extension;
            $destination = __DIR__ . '/../assets/images/' . $imageName;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $errors[] = "Erreur lors de l'envoi de l'image.";
                $imageName = 'default_car.jpg';
            }
        }
    }

    if (empty($errors)) {
        // Vérifier l'unicité de l'immatriculation
        $stmt = $pdo->prepare('SELECT id FROM vehicules WHERE immatriculation = :imm');
        $stmt->execute([':imm' => $old['immatriculation']]);
        if ($stmt->fetch()) {
            $errors[] = 'Cette immatriculation existe déjà.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            'INSERT INTO vehicules (marque, modele, annee, categorie, immatriculation, prix_jour, statut, image, description, nombre_places, transmission, carburant)
             VALUES (:marque, :modele, :annee, :categorie, :imm, :prix, "disponible", :image, :description, :places, :transmission, :carburant)'
        );
        $stmt->execute([
            ':marque' => $old['marque'], ':modele' => $old['modele'], ':annee' => $old['annee'],
            ':categorie' => $old['categorie'], ':imm' => $old['immatriculation'], ':prix' => $old['prix_jour'],
            ':image' => $imageName, ':description' => $old['description'], ':places' => $old['nombre_places'],
            ':transmission' => $old['transmission'], ':carburant' => $old['carburant'],
        ]);

        setFlash('success', 'Le véhicule a été ajouté avec succès.');
        redirect('admin_dashboard.php');
    }
}

$pageTitle = 'Ajouter un véhicule';
$assetsPath = '../assets/';
$basePath = '../';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fa-solid fa-plus"></i> Ajouter un véhicule</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="ajouter_vehicule.php" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e(genererCsrfToken()) ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Marque *</label>
                        <input type="text" name="marque" class="form-control" required value="<?= e($old['marque']) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Modèle *</label>
                        <input type="text" name="modele" class="form-control" required value="<?= e($old['modele']) ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Année *</label>
                        <input type="number" name="annee" class="form-control" required value="<?= e($old['annee']) ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Catégorie</label>
                        <select name="categorie" class="form-select">
                            <?php foreach (['Berline', 'SUV', 'Citadine', 'Pick-up', '4x4', 'Berline de luxe', 'Minibus'] as $cat): ?>
                                <option value="<?= $cat ?>" <?= $old['categorie'] === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Immatriculation *</label>
                        <input type="text" name="immatriculation" class="form-control" required value="<?= e($old['immatriculation']) ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Prix / jour (Ar) *</label>
                        <input type="number" step="0.01" name="prix_jour" class="form-control" required value="<?= e($old['prix_jour']) ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nombre de places</label>
                        <input type="number" name="nombre_places" class="form-control" value="<?= e($old['nombre_places']) ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Transmission</label>
                        <select name="transmission" class="form-select">
                            <option value="manuelle" <?= $old['transmission'] === 'manuelle' ? 'selected' : '' ?>>Manuelle</option>
                            <option value="automatique" <?= $old['transmission'] === 'automatique' ? 'selected' : '' ?>>Automatique</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Carburant</label>
                        <select name="carburant" class="form-select">
                            <?php foreach (['essence', 'diesel', 'electrique', 'hybride'] as $c): ?>
                                <option value="<?= $c ?>" <?= $old['carburant'] === $c ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Image du véhicule</label>
                        <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= e($old['description']) ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Ajouter le véhicule</button>
                <a href="admin_dashboard.php" class="btn btn-outline-secondary">Annuler</a>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
