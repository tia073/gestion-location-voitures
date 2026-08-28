<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    redirect(isAdmin() ? '../admin/admin_dashboard.php' : '../index.php');
}

$errors = [];
$old = ['nom' => '', 'prenom' => '', 'email' => '', 'telephone' => '', 'adresse' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['nom'] = trim($_POST['nom'] ?? '');
    $old['prenom'] = trim($_POST['prenom'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['telephone'] = trim($_POST['telephone'] ?? '');
    $old['adresse'] = trim($_POST['adresse'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';

    // Validation côté serveur
    if ($old['nom'] === '') $errors[] = 'Le nom est obligatoire.';
    if ($old['prenom'] === '') $errors[] = 'Le prénom est obligatoire.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = "L'adresse email n'est pas valide.";
    if (strlen($motDePasse) < 6) $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
    if ($motDePasse !== $confirmation) $errors[] = 'Les mots de passe ne correspondent pas.';

    if (empty($errors)) {
        // Vérifier l'unicité de l'email
        $stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = :email');
        $stmt->execute([':email' => $old['email']]);
        if ($stmt->fetch()) {
            $errors[] = 'Cette adresse email est déjà utilisée.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($motDePasse, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, adresse, role)
                                VALUES (:nom, :prenom, :email, :mdp, :tel, :adresse, "client")');
        $stmt->execute([
            ':nom'     => $old['nom'],
            ':prenom'  => $old['prenom'],
            ':email'   => $old['email'],
            ':mdp'     => $hash,
            ':tel'     => $old['telephone'],
            ':adresse' => $old['adresse'],
        ]);

        setFlash('success', 'Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.');
        redirect('login.php');
    }
}

$pageTitle = 'Inscription';
$assetsPath = '../assets/';
$basePath = '../';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow-sm auth-card">
                <div class="card-body p-4">
                    <h2 class="mb-4 text-center"><i class="fa-solid fa-user-plus"></i> Créer un compte</h2>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= e($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="register.php" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Prénom *</label>
                                <input type="text" name="prenom" class="form-control" required value="<?= e($old['prenom']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nom *</label>
                                <input type="text" name="nom" class="form-control" required value="<?= e($old['nom']) ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Adresse email *</label>
                            <input type="email" name="email" class="form-control" required value="<?= e($old['email']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="telephone" class="form-control" value="<?= e($old['telephone']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Adresse</label>
                            <input type="text" name="adresse" class="form-control" value="<?= e($old['adresse']) ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mot de passe *</label>
                                <input type="password" name="mot_de_passe" class="form-control" required minlength="6">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirmation *</label>
                                <input type="password" name="confirmation" class="form-control" required minlength="6">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mt-2">Créer mon compte</button>
                    </form>

                    <p class="text-center mt-3 mb-0">
                        Déjà un compte ? <a href="login.php">Connectez-vous</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
