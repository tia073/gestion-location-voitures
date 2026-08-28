<?php
/**
 * includes/functions.php
 * Fonctions utilitaires réutilisées dans tout le projet
 */

/**
 * Échappe une chaîne pour un affichage sécurisé (protection XSS)
 */
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirige vers une URL et arrête l'exécution
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Vérifie si un utilisateur est connecté
 */
function isLoggedIn() {
    return isset($_SESSION['utilisateur_id']);
}

/**
 * Vérifie si l'utilisateur connecté est un administrateur
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Formate un montant en Ariary
 */
function formatMontant($montant) {
    return number_format((float)$montant, 0, ',', ' ') . ' Ar';
}

/**
 * Formate une date au format jj/mm/aaaa
 */
function formatDate($date) {
    if (empty($date)) return '';
    $d = new DateTime($date);
    return $d->format('d/m/Y');
}

/**
 * Formate une date + heure
 */
function formatDateHeure($date) {
    if (empty($date)) return '';
    $d = new DateTime($date);
    return $d->format('d/m/Y H:i');
}

/**
 * Calcule le nombre de jours entre deux dates (minimum 1)
 */
function calculerNombreJours($dateDebut, $dateFin) {
    $debut = new DateTime($dateDebut);
    $fin = new DateTime($dateFin);
    $diff = $debut->diff($fin)->days;
    return $diff > 0 ? $diff : 1;
}

/**
 * Retourne un badge HTML de statut de véhicule
 */
function badgeStatutVehicule($statut) {
    $classes = [
        'disponible' => 'badge-success',
        'reserve'    => 'badge-warning',
        'loue'       => 'badge-info',
        'maintenance'=> 'badge-danger',
    ];
    $labels = [
        'disponible' => 'Disponible',
        'reserve'    => 'Réservé',
        'loue'       => 'Loué',
        'maintenance'=> 'En maintenance',
    ];
    $class = $classes[$statut] ?? 'badge-secondary';
    $label = $labels[$statut] ?? e($statut);
    return '<span class="badge ' . $class . '">' . $label . '</span>';
}

/**
 * Retourne un badge HTML de statut de réservation
 */
function badgeStatutReservation($statut) {
    $classes = [
        'en_attente' => 'badge-warning',
        'validee'    => 'badge-success',
        'refusee'    => 'badge-danger',
        'annulee'    => 'badge-secondary',
        'terminee'   => 'badge-info',
    ];
    $labels = [
        'en_attente' => 'En attente',
        'validee'    => 'Validée',
        'refusee'    => 'Refusée',
        'annulee'    => 'Annulée',
        'terminee'   => 'Terminée',
    ];
    $class = $classes[$statut] ?? 'badge-secondary';
    $label = $labels[$statut] ?? e($statut);
    return '<span class="badge ' . $class . '">' . $label . '</span>';
}

/**
 * Retourne un badge HTML de statut de paiement
 */
function badgeStatutPaiement($statut) {
    $classes = [
        'en_attente' => 'badge-warning',
        'paye'       => 'badge-success',
        'annule'     => 'badge-danger',
    ];
    $labels = [
        'en_attente' => 'En attente',
        'paye'       => 'Payé',
        'annule'     => 'Annulé',
    ];
    $class = $classes[$statut] ?? 'badge-secondary';
    $label = $labels[$statut] ?? e($statut);
    return '<span class="badge ' . $class . '">' . $label . '</span>';
}

/**
 * Vérifie la disponibilité d'un véhicule sur une période donnée.
 * Retourne true si le véhicule est disponible (aucun chevauchement de réservation active).
 *
 * @param PDO $pdo
 * @param int $vehiculeId
 * @param string $dateDebut
 * @param string $dateFin
 * @param int|null $excludeReservationId Réservation à exclure du contrôle (utile en cas de modification)
 */
function vehiculeEstDisponible($pdo, $vehiculeId, $dateDebut, $dateFin, $excludeReservationId = null) {
    $sql = "SELECT COUNT(*) FROM reservations
            WHERE vehicule_id = :vehicule_id
            AND statut IN ('en_attente', 'validee')
            AND date_debut <= :date_fin
            AND date_fin >= :date_debut";

    $params = [
        ':vehicule_id' => $vehiculeId,
        ':date_debut'  => $dateDebut,
        ':date_fin'    => $dateFin,
    ];

    if ($excludeReservationId) {
        $sql .= " AND id != :exclude_id";
        $params[':exclude_id'] = $excludeReservationId;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchColumn() == 0;
}

/**
 * Génère un numéro de facture unique
 */
function genererNumeroFacture($pdo) {
    $annee = date('Y');
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM factures WHERE numero_facture LIKE :pattern");
    $stmt->execute([':pattern' => "FACT-$annee-%"]);
    $count = (int)$stmt->fetchColumn() + 1;
    return sprintf('FACT-%s-%04d', $annee, $count);
}

/**
 * Message flash (session) - définit un message
 */
function setFlash($type, $message) {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Récupère et vide les messages flash
 */
function getFlashMessages() {
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/**
 * Valide un token CSRF simple basé sur la session
 */
function genererCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifierCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}
