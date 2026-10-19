<?php

declare(strict_types=1);

use App\MenuRepository;

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$menu = $id ? MenuRepository::trouver($id) : null;
if (!$menu) {
    abort(404, 'Ce menu n\'existe pas ou n\'est plus proposé.');
}

// Regroupe les plats par catégorie pour l'affichage
$plats = ['entree' => [], 'plat' => [], 'dessert' => []];
foreach ($menu['plats'] as $p) {
    $plats[$p['type_plat']][] = $p;
}

render('menu', [
    'title'           => $menu['titre'],
    'metaDescription' => mb_substr($menu['description'], 0, 155),
    'menu'            => $menu,
    'plats'           => $plats,
]);
