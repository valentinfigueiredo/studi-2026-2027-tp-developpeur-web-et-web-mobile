/**
 * Formulaire de commande :
 *  1. autocomplétion de l'adresse avec l'API Adresse du gouvernement (Base Adresse Nationale) ;
 *  2. calcul du prix en direct (mêmes règles que la classe PHP Pricing).
 * Le serveur recalcule TOUJOURS le prix : cet affichage n'est qu'une aide.
 */
(() => {
    const champMenu = document.getElementById('menu_id');
    const champNb = document.getElementById('nombre_personne');
    const champAdresse = document.getElementById('adresse_prestation');
    const champCp = document.getElementById('code_postal');
    const champVille = document.getElementById('ville_prestation');
    const champLat = document.getElementById('latitude');
    const champLon = document.getElementById('longitude');
    const listeSuggestions = document.getElementById('suggestions-adresse');
    if (!champMenu || !champNb) return;

    const euros = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });
    const DEPART = { lat: 44.8425, lon: -0.5745 }; // centre de Bordeaux, comme dans Geo.php

    // ---------- Calcul du prix ----------

    const menuChoisi = () => {
        // <select> (nouvelle commande) ou <input hidden> (modification, menu non modifiable)
        const source = champMenu.tagName === 'SELECT' ? champMenu.selectedOptions[0] : champMenu;
        if (!source || !source.dataset.prix) return null;
        // data-prix = prix PAR PERSONNE (champ prix_par_personne du MCD)
        return { prixParPersonne: parseFloat(source.dataset.prix), min: parseInt(source.dataset.min, 10) };
    };

    const estBordeaux = (ville) => ville.trim().toLowerCase().replace(/\s+cedex.*$/, '') === 'bordeaux';

    const distanceKm = (lat, lon) => {
        const rad = (d) => (d * Math.PI) / 180;
        const dLat = rad(lat - DEPART.lat);
        const dLon = rad(lon - DEPART.lon);
        const a = Math.sin(dLat / 2) ** 2 + Math.cos(rad(DEPART.lat)) * Math.cos(rad(lat)) * Math.sin(dLon / 2) ** 2;
        return Math.ceil(6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
    };

    const arrondi = (n) => Math.round(n * 100) / 100;
    const ecrire = (id, texte) => { document.getElementById(id).textContent = texte; };

    const majPrix = () => {
        const menu = menuChoisi();
        if (menu) {
            champNb.min = menu.min;
            if (champNb.value === '') champNb.value = menu.min;
        }
        const nb = parseInt(champNb.value, 10);
        if (!menu || !nb) {
            ['recap-menu', 'recap-reduction', 'recap-livraison', 'recap-total'].forEach((id) => ecrire(id, '-'));
            return;
        }

        const prixMenu = arrondi(menu.prixParPersonne * nb);
        const reduction = nb >= menu.min + 5 ? arrondi(prixMenu * 0.1) : 0;

        let livraison = null;
        let infoDistance = '';
        if (estBordeaux(champVille.value)) {
            livraison = 0;
            infoDistance = '(Bordeaux)';
        } else if (champLat.value && champLon.value) {
            const km = distanceKm(parseFloat(champLat.value), parseFloat(champLon.value));
            livraison = arrondi(5 + 0.59 * km);
            infoDistance = `(${km} km)`;
        }

        ecrire('recap-menu', euros.format(prixMenu));
        ecrire('recap-reduction', reduction > 0 ? '- ' + euros.format(reduction) : 'Non applicable');
        ecrire('recap-distance', infoDistance);
        ecrire('recap-livraison', livraison === null ? 'Choisissez une adresse' : euros.format(livraison));
        ecrire('recap-total', euros.format(prixMenu - reduction + (livraison ?? 0)) + (livraison === null ? ' + livraison' : ''));

        if (nb < menu.min) {
            ecrire('recap-total', `Minimum ${menu.min} personnes`);
        }
    };

    champMenu.addEventListener('change', () => { champNb.value = ''; majPrix(); });
    champNb.addEventListener('input', majPrix);
    champVille.addEventListener('input', () => { champLat.value = ''; champLon.value = ''; majPrix(); });

    // ---------- Autocomplétion de l'adresse (accessible au clavier) ----------

    let suggestions = [];
    let indexActif = -1;
    let minuteur;

    const fermer = () => {
        listeSuggestions.classList.add('d-none');
        listeSuggestions.replaceChildren();
        champAdresse.setAttribute('aria-expanded', 'false');
        champAdresse.removeAttribute('aria-activedescendant');
        indexActif = -1;
    };

    const choisir = (s) => {
        champAdresse.value = s.properties.name;
        champCp.value = s.properties.postcode;
        champVille.value = s.properties.city;
        champLon.value = s.geometry.coordinates[0];
        champLat.value = s.geometry.coordinates[1];
        fermer();
        majPrix();
    };

    const surligner = (index) => {
        [...listeSuggestions.children].forEach((li, i) => li.classList.toggle('active', i === index));
        indexActif = index;
        if (index >= 0) champAdresse.setAttribute('aria-activedescendant', 'suggestion-' + index);
    };

    const afficher = () => {
        listeSuggestions.replaceChildren(...suggestions.map((s, i) => {
            const li = document.createElement('li');
            li.className = 'list-group-item list-group-item-action';
            li.id = 'suggestion-' + i;
            li.setAttribute('role', 'option');
            li.textContent = s.properties.label;
            li.addEventListener('mousedown', (e) => { e.preventDefault(); choisir(s); });
            return li;
        }));
        listeSuggestions.classList.toggle('d-none', suggestions.length === 0);
        champAdresse.setAttribute('aria-expanded', suggestions.length ? 'true' : 'false');
    };

    champAdresse.addEventListener('input', () => {
        champLat.value = '';
        champLon.value = '';
        clearTimeout(minuteur);
        const q = champAdresse.value.trim();
        if (q.length < 4) { fermer(); return; }
        minuteur = setTimeout(async () => {
            try {
                const r = await fetch('https://api-adresse.data.gouv.fr/search/?limit=5&autocomplete=1&q=' + encodeURIComponent(q));
                suggestions = (await r.json()).features || [];
                afficher();
            } catch {
                fermer(); // API indisponible : la saisie manuelle reste possible
            }
        }, 300);
    });

    champAdresse.addEventListener('keydown', (e) => {
        if (listeSuggestions.classList.contains('d-none')) return;
        if (e.key === 'ArrowDown') { e.preventDefault(); surligner(Math.min(indexActif + 1, suggestions.length - 1)); }
        if (e.key === 'ArrowUp') { e.preventDefault(); surligner(Math.max(indexActif - 1, 0)); }
        if (e.key === 'Enter' && indexActif >= 0) { e.preventDefault(); choisir(suggestions[indexActif]); }
        if (e.key === 'Escape') fermer();
    });
    champAdresse.addEventListener('blur', () => setTimeout(fermer, 150));

    majPrix();
})();
