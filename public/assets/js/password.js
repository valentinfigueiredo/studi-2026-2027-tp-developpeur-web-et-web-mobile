/**
 * Indique en direct quelles règles du mot de passe sont respectées (RG1).
 * Simple aide à la saisie : la vérification qui fait foi est faite côté serveur.
 */
(() => {
    const champ = document.getElementById('password');
    const liste = document.getElementById('regles-mdp');
    if (!champ || !liste) return;

    const regles = {
        longueur: (v) => v.length >= 10,
        majuscule: (v) => /[A-Z]/.test(v),
        minuscule: (v) => /[a-z]/.test(v),
        chiffre: (v) => /\d/.test(v),
        special: (v) => /[^A-Za-z\d]/.test(v),
    };

    champ.addEventListener('input', () => {
        Object.entries(regles).forEach(([nom, test]) => {
            const item = liste.querySelector(`[data-regle="${nom}"]`);
            const ok = test(champ.value);
            item.classList.toggle('ok', ok);
            // Information non portée uniquement par la couleur (RGAA 3.1)
            item.dataset.etat = ok ? 'respectée' : 'non respectée';
        });
    });
})();
