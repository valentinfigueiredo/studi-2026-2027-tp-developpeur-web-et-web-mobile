/**
 * Graphique de l'administrateur : nombre de commandes par menu (Chart.js),
 * avec filtres par menu et par période. Données : /api/statistiques (MongoDB).
 */
(() => {
    const form = document.getElementById('filtres-stats');
    if (!form) return;

    const euros = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });
    const tbody = document.getElementById('tableau-stats');
    let graphique = null;

    const cellule = (tag, texte, classe) => {
        const c = document.createElement(tag);
        c.textContent = texte;
        if (classe) c.className = classe;
        return c;
    };

    const charger = async () => {
        const params = new URLSearchParams(new FormData(form));
        const reponse = await fetch('/api/statistiques?' + params);
        const data = await reponse.json();

        if (!reponse.ok) {
            tbody.replaceChildren(Object.assign(document.createElement('tr'), {}));
            tbody.firstChild.append(Object.assign(cellule('td', data.erreur || 'Erreur'), { colSpan: 3 }));
            return;
        }

        document.getElementById('kpi-commandes').textContent = data.total_commandes;
        document.getElementById('kpi-ca').textContent = euros.format(data.chiffre_affaires);

        tbody.replaceChildren(...data.menus.map((m) => {
            const tr = document.createElement('tr');
            tr.append(cellule('th', m.menu_titre), cellule('td', m.nb_commandes, 'text-end'), cellule('td', euros.format(m.chiffre_affaires), 'text-end'));
            tr.firstChild.scope = 'row';
            return tr;
        }));
        if (data.menus.length === 0) {
            const tr = document.createElement('tr');
            tr.append(Object.assign(cellule('td', 'Aucune commande sur cette période.'), { colSpan: 3 }));
            tbody.append(tr);
        }

        const labels = data.menus.map((m) => m.menu_titre);
        const valeurs = data.menus.map((m) => m.nb_commandes);
        if (graphique) {
            graphique.data.labels = labels;
            graphique.data.datasets[0].data = valeurs;
            graphique.update();
        } else {
            graphique = new Chart(document.getElementById('graphique'), {
                type: 'bar',
                data: { labels, datasets: [{ label: 'Commandes', data: valeurs, backgroundColor: '#7A1F3D', borderRadius: 4 }] },
                options: {
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                },
            });
        }
    };

    form.addEventListener('submit', (e) => { e.preventDefault(); charger(); });
    // Chart.js est chargé en "defer" : on attend que la page soit prête
    window.addEventListener('load', charger);
})();
