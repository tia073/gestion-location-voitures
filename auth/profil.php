<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../auth/auth_check.php';

$userId = $_SESSION['utilisateur_id'];
$errors = [];
$success = false;

$stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE id = :id');
$stmt->execute([':id' => $userId]);
$utilisateur = $stmt->fetch();

if (!$utilisateur) {
    redirect('logout.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_info') {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $adresse = trim($_POST['adresse'] ?? '');

        if ($nom === '' || $prenom === '') {
            $errors[] = 'Le nom et le prénom sont obligatoires.';
        } else {
            $stmt = $pdo->prepare('UPDATE utilisateurs SET nom = :nom, prenom = :prenom, telephone = :tel, adresse = :adresse WHERE id = :id');
            $stmt->execute([
                ':nom' => $nom, ':prenom' => $prenom, ':tel' => $telephone,
                ':adresse' => $adresse, ':id' => $userId,
            ]);
            $_SESSION['nom'] = $nom;
            $_SESSION['prenom'] = $prenom;
            setFlash('success', 'Vos informations ont été mises à jour.');
            redirect('profil.php');
        }
    } elseif ($action === 'update_password') {
        $ancien = $_POST['ancien_mdp'] ?? '';
        $nouveau = $_POST['nouveau_mdp'] ?? '';
        $confirmation = $_POST['confirmation_mdp'] ?? '';

        if (!password_verify($ancien, $utilisateur['mot_de_passe'])) {
            $errors[] = 'Ancien mot de passe incorrect.';
        } elseif (strlen($nouveau) < 6) {
            $errors[] = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
        } elseif ($nouveau !== $confirmation) {
            $errors[] = 'Les nouveaux mots de passe ne correspondent pas.';
        } else {
            $hash = password_hash($nouveau, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE utilisateurs SET mot_de_passe = :mdp WHERE id = :id');
            $stmt->execute([':mdp' => $hash, ':id' => $userId]);
            setFlash('success', 'Votre mot de passe a été modifié avec succès.');
            redirect('profil.php');
        }
    }

    // Recharger les données à jour
    $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE id = :id');
    $stmt->execute([':id' => $userId]);
    $utilisateur = $stmt->fetch();
}

$pageTitle = 'Mon profil';
$assetsPath = '../assets/';
$basePath = '../';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fa-solid fa-user-gear"></i> Mon profil</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Informations personnelles</h5>
                    <form method="POST" action="profil.php">
                        <input type="hidden" name="action" value="update_info">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?= e($utilisateur['email']) ?>" disabled>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Prénom</label>
                                <input type="text" name="prenom" class="form-control" required value="<?= e($utilisateur['prenom']) ?>">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Nom</label>
                                <input type="text" name="nom" class="form-control" required value="<?= e($utilisateur['nom']) ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="telephone" class="form-control" value="<?= e($utilisateur['telephone']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Adresse</label>
                            <input type="text" name="adresse" class="form-control" value="<?= e($utilisateur['adresse']) ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Changer le mot de passe</h5>
                    <form method="POST" action="profil.php">
                        <input type="hidden" name="action" value="update_password">
                        <div class="mb-3">
                            <label class="form-label">Ancien mot de passe</label>
                            <input type="password" name="ancien_mdp" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nouveau mot de passe</label>
                            <input type="password" name="nouveau_mdp" class="form-control" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmation</label>
                            <input type="password" name="confirmation_mdp" class="form-control" required minlength="6">
                        </div>
                        <button type="submit" class="btn btn-secondary">Modifier le mot de passe</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
