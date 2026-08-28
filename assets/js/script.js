/**
 * assets/js/script.js
 * Comportements JavaScript côté client : calcul dynamique du prix de réservation,
 * validation basique des formulaires. Le calcul du prix ici est purement indicatif :
 * le montant final est TOUJOURS recalculé et vérifié côté serveur en PHP.
 */

document.addEventListener('DOMContentLoaded', function () {
    initCalculPrixReservation();
    initValidationConfirmationMotDePasse();
});

/**
 * Calcule dynamiquement le nombre de jours et le prix total
 * sur la page de réservation (client/reservation.php).
 */
function initCalculPrixReservation() {
    const dateDebutInput = document.getElementById('date_debut');
    const dateFinInput = document.getElementById('date_fin');
    const prixJourEl = document.getElementById('prix-jour');
    const recapBox = document.getElementById('recap-prix');
    const recapJours = document.getElementById('recap-jours');
    const recapTotal = document.getElementById('recap-total');

    if (!dateDebutInput || !dateFinInput || !prixJourEl) {
        return; // On n'est pas sur la page de réservation
    }

    const prixJour = parseFloat(prixJourEl.dataset.prix) || 0;

    function formatMontant(valeur) {
        return Math.round(valeur).toLocaleString('fr-FR').replace(/,/g, ' ') + ' Ar';
    }

    function recalculer() {
        const debut = dateDebutInput.value;
        const fin = dateFinInput.value;

        if (!debut || !fin) {
            recapBox.style.display = 'none';
            return;
        }

        const dDebut = new Date(debut);
        const dFin = new Date(fin);
        const diffTemps = dFin - dDebut;
        let jours = Math.ceil(diffTemps / (1000 * 60 * 60 * 24));

        if (jours <= 0) {
            recapBox.style.display = 'none';
            return;
        }

        const total = jours * prixJour;

        recapJours.textContent = jours;
        recapTotal.textContent = formatMontant(total);
        recapBox.style.display = 'block';

        // Ajuste automatiquement la date minimale de retour
        const lendemain = new Date(dDebut);
        lendemain.setDate(lendemain.getDate() + 1);
        dateFinInput.min = lendemain.toISOString().split('T')[0];
    }

    dateDebutInput.addEventListener('change', recalculer);
    dateFinInput.addEventListener('change', recalculer);
}

/**
 * Vérifie côté client que le mot de passe et sa confirmation correspondent
 * (double vérification en plus du contrôle serveur obligatoire).
 */
function initValidationConfirmationMotDePasse() {
    const form = document.getElementById('form-reservation');
    // Recherche générique de paires mot de passe / confirmation sur toute page
    const mdp = document.querySelector('input[name="mot_de_passe"]');
    const confirmation = document.querySelector('input[name="confirmation"]');

    if (mdp && confirmation) {
        const verifier = function () {
            if (confirmation.value && mdp.value !== confirmation.value) {
                confirmation.setCustomValidity('Les mots de passe ne correspondent pas.');
            } else {
                confirmation.setCustomValidity('');
            }
        };
        mdp.addEventListener('input', verifier);
        confirmation.addEventListener('input', verifier);
    }
}
