<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    redirect(isAdmin() ? '../admin/admin_dashboard.php' : '../index.php');
}

$errors = [];
$emailOld = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailOld = trim($_POST['email'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';

    if ($emailOld === '' || $motDePasse === '') {
        $errors[] = 'Veuillez renseigner votre email et votre mot de passe.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE email = :email');
        $stmt->execute([':email' => $emailOld]);
        $utilisateur = $stmt->fetch();

        if ($utilisateur && password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
            // Régénère l'ID de session pour éviter la fixation de session
            session_regenerate_id(true);
            $_SESSION['utilisateur_id'] = $utilisateur['id'];
            $_SESSION['nom'] = $utilisateur['nom'];
            $_SESSION['prenom'] = $utilisateur['prenom'];
            $_SESSION['email'] = $utilisateur['email'];
            $_SESSION['role'] = $utilisateur['role'];

            setFlash('success', 'Bienvenue, ' . $utilisateur['prenom'] . ' !');

            if ($utilisateur['role'] === 'admin') {
                redirect('../admin/admin_dashboard.php');
            } else {
                redirect('../client/catalogue.php');
            }
        } else {
            $errors[] = 'Email ou mot de passe incorrect.';
        }
    }
}

$pageTitle = 'Connexion';
$assetsPath = '../assets/';
$basePath = '../';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm auth-card">
                <div class="card-body p-4">
                    <h2 class="mb-4 text-center"><i class="fa-solid fa-right-to-bracket"></i> Connexion</h2>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= e($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="login.php" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Adresse email</label>
                            <input type="email" name="email" class="form-control" required value="<?= e($emailOld) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="mot_de_passe" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                    </form>

                    <p class="text-center mt-3 mb-0">
                        Pas encore de compte ? <a href="register.php">Inscrivez-vous</a>
                    </p>

                    <div class="alert alert-light border mt-3 small mb-0">
                        <strong>Comptes de test</strong><br>
                        Admin : admin@location.mg / password123<br>
                        Client : jean.rakoto@gmail.com / password123
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
