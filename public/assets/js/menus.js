/**
 * Filtres dynamiques de la vue globale des menus.
 * À chaque modification d'un filtre, on interroge l'API /api/menus (fetch)
 * et on reconstruit la liste SANS recharger la page.
 *
 * Sécurité : les données sont insérées avec textContent (jamais innerHTML),
 * donc un titre de menu contenant du HTML ne peut pas être interprété (pas de XSS).
 */
(() => {
    const form = document.getElementById('filtres');
    const liste = document.getElementById('liste-menus');
    const compteur = document.getElementById('nb-resultats');
    if (!form || !liste) return;

    // Avec JavaScript, le bouton "Filtrer" devient inutile
    form.querySelectorAll('.js-masquer').forEach((el) => el.classList.add('d-none'));

    const prix = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });

    const el = (tag, className, text) => {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    };

    const carte = (m) => {
        const col = el('div', 'col-md-6 col-lg-4');
        const article = el('article', 'card h-100 carte-menu');

        if (m.image) {
            const img = el('img', 'card-img-top');
            img.src = '/' + m.image;
            img.alt = m.image_alt || '';
            img.loading = 'lazy';
            img.width = 600;
            img.height = 400;
            article.append(img);
        }

        const body = el('div', 'card-body d-flex flex-column');
        const badges = el('p', 'mb-2');
        badges.append(el('span', 'badge badge-theme', m.theme), ' ', el('span', 'badge badge-regime', m.regime));

        const infos = el('p', 'mb-3 mt-auto');
        infos.append('À partir de ', el('strong', '', m.personnes + ' personnes'), el('br'),
            el('span', 'prix', prix.format(m.prix)), ' ', el('span', 'small', 'pour ' + m.personnes + ' pers.'));

        body.append(badges, el('h2', 'h4 card-title', m.titre), el('p', 'card-text', m.description), infos);

        if (!m.disponible) {
            body.append(el('p', 'text-danger fw-semibold mb-2', 'Victime de son succès : plus disponible'));
        }

        const lien = el('a', 'btn btn-bordeaux', 'Voir le détail');
        lien.href = '/menu?id=' + encodeURIComponent(m.id);
        lien.append(el('span', 'visually-hidden', ' du menu ' + m.titre));
        body.append(lien);

        article.append(body);
        col.append(article);
        return col;
    };

    let controleur = null;

    const actualiser = async () => {
        const params = new URLSearchParams(new FormData(form));
        // On ne garde que les filtres remplis
        [...params.keys()].forEach((k) => { if (params.get(k) === '') params.delete(k); });

        // Annule la requête précédente si l'utilisateur tape vite
        controleur?.abort();
        controleur = new AbortController();

        liste.setAttribute('aria-busy', 'true');
        try {
            const reponse = await fetch('/api/menus?' + params.toString(), { signal: controleur.signal });
            if (!reponse.ok) throw new Error('HTTP ' + reponse.status);
            const menus = await reponse.json();

            liste.replaceChildren(...menus.map(carte));
            if (menus.length === 0) {
                liste.append(el('p', 'col-12', 'Aucun menu ne correspond à vos critères.'));
            }
            const n = menus.length;
            compteur.textContent = `${n} menu${n > 1 ? 's' : ''} trouvé${n > 1 ? 's' : ''}`;

            // L'URL reflète les filtres : on peut partager ou recharger la page sans les perdre
            history.replaceState(null, '', '/menus' + (params.toString() ? '?' + params : ''));
        } catch (e) {
            if (e.name !== 'AbortError') {
                compteur.textContent = 'Erreur lors du chargement des menus. Veuillez réessayer.';
            }
        } finally {
            liste.removeAttribute('aria-busy');
        }
    };

    // Petit délai (debounce) pour ne pas lancer une requête à chaque touche
    let minuteur;
    form.addEventListener('input', () => {
        clearTimeout(minuteur);
        minuteur = setTimeout(actualiser, 300);
    });
    form.addEventListener('submit', (e) => { e.preventDefault(); actualiser(); });
    form.addEventListener('reset', () => setTimeout(actualiser, 0));
})();
