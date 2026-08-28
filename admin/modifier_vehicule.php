<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../auth/auth_check.php';
requireAdmin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM vehicules WHERE id = :id');
$stmt->execute([':id' => $id]);
$vehicule = $stmt->fetch();

if (!$vehicule) {
    setFlash('danger', 'Véhicule introuvable.');
    redirect('admin_dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifierCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Requête invalide, veuillez réessayer.';
    }

    $marque = trim($_POST['marque'] ?? '');
    $modele = trim($_POST['modele'] ?? '');
    $annee = trim($_POST['annee'] ?? '');
    $categorie = trim($_POST['categorie'] ?? '');
    $immatriculation = trim($_POST['immatriculation'] ?? '');
    $prixJour = trim($_POST['prix_jour'] ?? '');
    $statut = trim($_POST['statut'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $nombrePlaces = trim($_POST['nombre_places'] ?? '');
    $transmission = trim($_POST['transmission'] ?? '');
    $carburant = trim($_POST['carburant'] ?? '');

    $statutsValides = ['disponible', 'reserve', 'loue', 'maintenance'];

    if ($marque === '') $errors[] = 'La marque est obligatoire.';
    if ($modele === '') $errors[] = 'Le modèle est obligatoire.';
    if (!ctype_digit((string)$annee)) $errors[] = 'Année invalide.';
    if ($immatriculation === '') $errors[] = "L'immatriculation est obligatoire.";
    if (!is_numeric($prixJour) || $prixJour <= 0) $errors[] = 'Le prix journalier doit être un nombre positif.';
    if (!in_array($statut, $statutsValides, true)) $errors[] = 'Statut invalide.';

    if (empty($errors)) {
        // Unicité de l'immatriculation (hors véhicule courant)
        $stmt = $pdo->prepare('SELECT id FROM vehicules WHERE immatriculation = :imm AND id != :id');
        $stmt->execute([':imm' => $immatriculation, ':id' => $id]);
        if ($stmt->fetch()) {
            $errors[] = 'Cette immatriculation est déjà utilisée par un autre véhicule.';
        }
    }

    $imageName = $vehicule['image'];
    if (!empty($_FILES['image']['name'])) {
        $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $extensionsAutorisees, true)) {
            $errors[] = 'Format d\'image non autorisé.';
        } elseif ($_FILES['image']['size'] > 3 * 1024 * 1024) {
            $errors[] = "L'image ne doit pas dépasser 3 Mo.";
        } elseif (empty($errors)) {
            $imageName = uniqid('vehicule_', true) . '.' . $extension;
            move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../assets/images/' . $imageName);
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            'UPDATE vehicules SET marque=:marque, modele=:modele, annee=:annee, categorie=:categorie,
             immatriculation=:imm, prix_jour=:prix, statut=:statut, image=:image, description=:description,
             nombre_places=:places, transmission=:transmission, carburant=:carburant
             WHERE id=:id'
        );
        $stmt->execute([
            ':marque' => $marque, ':modele' => $modele, ':annee' => $annee, ':categorie' => $categorie,
            ':imm' => $immatriculation, ':prix' => $prixJour, ':statut' => $statut, ':image' => $imageName,
            ':description' => $description, ':places' => $nombrePlaces, ':transmission' => $transmission,
            ':carburant' => $carburant, ':id' => $id,
        ]);

        setFlash('success', 'Le véhicule a été mis à jour avec succès.');
        redirect('admin_dashboard.php');
    }

    // Recharge les données pour réafficher le formulaire avec les valeurs soumises
    $vehicule = array_merge($vehicule, [
        'marque' => $marque, 'modele' => $modele, 'annee' => $annee, 'categorie' => $categorie,
        'immatriculation' => $immatriculation, 'prix_jour' => $prixJour, 'statut' => $statut,
        'description' => $description, 'nombre_places' => $nombrePlaces, 'transmission' => $transmission,
        'carburant' => $carburant,
    ]);
}

$pageTitle = 'Modifier un véhicule';
$assetsPath = '../assets/';
$basePath = '../';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fa-solid fa-pen"></i> Modifier le véhicule</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="modifier_vehicule.php?id=<?= (int)$id ?>" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="id" value="<?= (int)$id ?>">
                <input type="hidden" name="csrf_token" value="<?= e(genererCsrfToken()) ?>">

                <div class="mb-3 text-center">
                    <img src="../assets/images/<?= e($vehicule['image']) ?>" class="modifier-image-preview" alt="" onerror="this.src='../assets/images/default_car.jpg'">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Marque *</label>
                        <input type="text" name="marque" class="form-control" required value="<?= e($vehicule['marque']) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Modèle *</label>
                        <input type="text" name="modele" class="form-control" required value="<?= e($vehicule['modele']) ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Année *</label>
                        <input type="number" name="annee" class="form-control" required value="<?= e($vehicule['annee']) ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Catégorie</label>
                        <input type="text" name="categorie" class="form-control" value="<?= e($vehicule['categorie']) ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Immatriculation *</label>
                        <input type="text" name="immatriculation" class="form-control" required value="<?= e($vehicule['immatriculation']) ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Prix / jour (Ar) *</label>
                        <input type="number" step="0.01" name="prix_jour" class="form-control" required value="<?= e($vehicule['prix_jour']) ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Statut *</label>
                        <select name="statut" class="form-select">
                            <?php foreach (['disponible', 'reserve', 'loue', 'maintenance'] as $s): ?>
                                <option value="<?= $s ?>" <?= $vehicule['statut'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Nombre de places</label>
                        <input type="number" name="nombre_places" class="form-control" value="<?= e($vehicule['nombre_places']) ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Transmission</label>
                        <select name="transmission" class="form-select">
                            <option value="manuelle" <?= $vehicule['transmission'] === 'manuelle' ? 'selected' : '' ?>>Manuelle</option>
                            <option value="automatique" <?= $vehicule['transmission'] === 'automatique' ? 'selected' : '' ?>>Automatique</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Carburant</label>
                        <select name="carburant" class="form-select">
                            <?php foreach (['essence', 'diesel', 'electrique', 'hybride'] as $c): ?>
                                <option value="<?= $c ?>" <?= $vehicule['carburant'] === $c ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nouvelle image (facultatif)</label>
                    <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= e($vehicule['description']) ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Enregistrer les modifications</button>
                <a href="admin_dashboard.php" class="btn btn-outline-secondary">Annuler</a>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
