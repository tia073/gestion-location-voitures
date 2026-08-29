<?php

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../auth/auth_check.php';

requireAdmin();

// Nombre total de véhicules
$requete = $pdo->query("SELECT COUNT(*) FROM vehicules");
$totalVehicules = $requete->fetchColumn();

// Nombre de véhicules disponibles
$requete = $pdo->query("
    SELECT COUNT(*) 
    FROM vehicules 
    WHERE statut = 'disponible'
");
$totalDisponibles = $requete->fetchColumn();

// Nombre de clients
$requete = $pdo->query("
    SELECT COUNT(*) 
    FROM utilisateurs 
    WHERE role = 'client'
");
$totalUtilisateurs = $requete->fetchColumn();

// Réservations en attente
$requete = $pdo->query("
    SELECT COUNT(*) 
    FROM reservations 
    WHERE statut = 'en_attente'
");
$totalReservationsEnAttente = $requete->fetchColumn();

// Revenu total
$requete = $pdo->query("
    SELECT COALESCE(SUM(montant), 0)
    FROM paiements
    WHERE statut = 'paye'
");
$revenuTotal = $requete->fetchColumn();

// LISTE DES VEHICULES

$requete = $pdo->query("
    SELECT id, marque, modele, annee, immatriculation,
           prix_jour, statut
    FROM vehicules
    ORDER BY id DESC
");

$vehicules = $requete->fetchAll();

// DERNIERES RESERVATIONS

$requete = $pdo->query("
    SELECT r.id,
           r.date_debut,
           r.date_fin,
           r.prix_total,
           r.statut,
           u.nom,
           u.prenom,
           v.marque,
           v.modele
    FROM reservations r
    JOIN utilisateurs u ON u.id = r.utilisateur_id
    JOIN vehicules v ON v.id = r.vehicule_id
    ORDER BY r.date_creation DESC
    LIMIT 8
");

$dernieresReservations = $requete->fetchAll();

$pageTitle = 'Dashboard Administrateur';
$assetsPath = '../assets/';
$basePath = '../';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

?>

<div class="container my-5">

    <h2 class="mb-4">
        <i class="fa-solid fa-gauge"></i>
        Tableau de bord administrateur
    </h2>

    <!-- STATISTIQUES -->

    <div class="row g-3 mb-4">

        <!-- Véhicules -->
        <div class="col-md-3 col-6">
            <div class="card stat-card shadow-sm">
                <div class="card-body">

                    <i class="fa-solid fa-car fa-2x mb-2"></i>

                    <h3>
                        <?= $totalVehicules ?>
                    </h3>

                    <p class="mb-0">
                        Véhicules
                        (<?= $totalDisponibles ?> disponibles)
                    </p>

                </div>
            </div>
        </div>

        <!-- Clients -->
        <div class="col-md-3 col-6">
            <div class="card stat-card stat-card-2 shadow-sm">
                <div class="card-body">

                    <i class="fa-solid fa-users fa-2x mb-2"></i>
                    <h3>
                        <?= $totalUtilisateurs ?>
                    </h3>
                    <p class="mb-0">
                        Clients inscrits
                    </p>
                </div>
            </div>
        </div>

        <!-- Réservations -->
        <div class="col-md-3 col-6">
            <div class="card stat-card stat-card-3 shadow-sm">
                <div class="card-body">

                    <i class="fa-solid fa-hourglass-half fa-2x mb-2"></i>

                    <h3>
                        <?= $totalReservationsEnAttente ?>
                    </h3>

                    <p class="mb-0">
                        Réservations en attente
                    </p>

                </div>
            </div>
        </div>

        <!-- Revenus -->
        <div class="col-md-3 col-6">
            <div class="card stat-card stat-card-4 shadow-sm">
                <div class="card-body">

                    <i class="fa-solid fa-sack-dollar fa-2x mb-2"></i>

                    <h3>
                        <?= formatMontant($revenuTotal) ?>
                    </h3>

                    <p class="mb-0">
                        Revenu total encaissé
                    </p>

                </div>
            </div>
        </div>

    </div>

    <!--BOUTONS RAPIDES -->

    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <a href="ajouter_vehicule.php"
               class="btn btn-primary w-100 py-3">

                <i class="fa-solid fa-plus"></i>
                Ajouter un véhicule

            </a>
        </div>

        <div class="col-md-3">
            <a href="valider_reservation.php"
               class="btn btn-warning w-100 py-3">

                <i class="fa-solid fa-clipboard-check"></i>
                Gérer les réservations

            </a>
        </div>

        <div class="col-md-3">
            <a href="suivi_paiements.php"
               class="btn btn-info w-100 py-3 text-white">

                <i class="fa-solid fa-money-bill-wave"></i>
                Suivi des paiements

            </a>
        </div>

        <div class="col-md-3">
            <a href="retour_vehicule.php"
               class="btn btn-secondary w-100 py-3">

                <i class="fa-solid fa-rotate-left"></i>
                Retours véhicules

            </a>
        </div>

    </div>

    <!-- LISTE DES VEHICULES -->

    <h4 class="mb-3">
        Liste des véhicules
    </h4>

    <div class="table-responsive mb-5">

        <table class="table table-hover bg-white shadow-sm align-middle">

            <thead class="table-dark">

                <tr>
                    <th>#</th>
                    <th>Véhicule</th>
                    <th>Immatriculation</th>
                    <th>Prix/jour</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($vehicules as $vehicule): ?>

                    <tr>
                        <td>
                            <?= $vehicule['id'] ?>
                        </td>

                        <td>
                            <?= e($vehicule['marque']) ?>
                            <?= e($vehicule['modele']) ?>
                            (<?= e($vehicule['annee']) ?>)
                        </td>

                        <td>
                            <?= e($vehicule['immatriculation']) ?>
                        </td>

                        <td>
                            <?= formatMontant($vehicule['prix_jour']) ?>
                        </td>

                        <td>
                            <?= badgeStatutVehicule($vehicule['statut']) ?>
                        </td>

                        <td>

                            <!-- Modifier -->
                            <a href="modifier_vehicule.php?id=<?= $vehicule['id'] ?>"
                               class="btn btn-sm btn-outline-primary">

                                <i class="fa-solid fa-pen"></i>

                            </a>

                            <!-- Supprimer -->
                            <form method="POST"
                                  action="supprimer_vehicule.php"
                                  class="d-inline"
                                  onsubmit="return confirm('Supprimer définitivement ce véhicule ?');">

                                <input type="hidden"
                                       name="id"
                                       value="<?= $vehicule['id'] ?>">

                                <input type="hidden"
                                       name="csrf_token"
                                       value="<?= e(genererCsrfToken()) ?>">

                                <button type="submit"
                                        class="btn btn-sm btn-outline-danger">

                                    <i class="fa-solid fa-trash"></i>

                                </button>
                            </form>
                        </td>
                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

    <!--DERNIERES RESERVATIONS -->

    <h4 class="mb-3">
        Dernières réservations
    </h4>

    <div class="table-responsive">
        <table class="table table-hover bg-white shadow-sm align-middle">
            <thead class="table-dark">

                <tr>
                    <th>#</th>
                    <th>Client</th>
                    <th>Véhicule</th>
                    <th>Période</th>
                    <th>Prix</th>
                    <th>Statut</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($dernieresReservations as $reservation): ?>
                    <tr>

                        <td>
                            <?= $reservation['id'] ?>
                        </td>

                        <td>
                            <?= e($reservation['prenom']) ?>
                            <?= e($reservation['nom']) ?>
                        </td>

                        <td>
                            <?= e($reservation['marque']) ?>
                            <?= e($reservation['modele']) ?>
                        </td>

                        <td>
                            <?= formatDate($reservation['date_debut']) ?>
                            →
                            <?= formatDate($reservation['date_fin']) ?>
                        </td>

                        <td>
                            <?= formatMontant($reservation['prix_total']) ?>
                        </td>

                        <td>
                            <?= badgeStatutReservation($reservation['statut']) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
