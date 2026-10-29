/**
 * Demande une confirmation avant une action irréversible (annulation, suppression...).
 * Remplace l'attribut onsubmit="..." qui serait bloqué par notre Content-Security-Policy.
 */
document.querySelectorAll('form.js-confirmer').forEach((form) => {
    form.addEventListener('submit', (e) => {
        if (!window.confirm(form.dataset.confirmation || 'Confirmez-vous cette action ?')) {
            e.preventDefault();
        }
    });
});
