<?php

declare(strict_types=1);

use App\MenuRepository;

// Premier affichage rendu côté serveur (fonctionne même sans JavaScript),
// puis les filtres mettent la liste à jour dynamiquement via /api/menus (menus.js).
$filtres = [
    'prix_min'  => $_GET['prix_min'] ?? '',
    'prix_max'  => $_GET['prix_max'] ?? '',
    'theme'     => $_GET['theme'] ?? '',
    'regime'    => $_GET['regime'] ?? '',
    'personnes' => $_GET['personnes'] ?? '',
];

render('menus', [
    'title'           => 'Nos menus',
    'metaDescription' => 'Tous les menus traiteur de Vite & Gourmand : Noël, Pâques, classiques, végétariens et vegan. Filtrez par prix, thème et régime.',
    'menus'           => MenuRepository::rechercher($filtres),
    'themes'          => MenuRepository::themes(),
    'regimes'         => MenuRepository::regimes(),
    'filtres'         => $filtres,
    'scripts'         => ['menus'],
]);
