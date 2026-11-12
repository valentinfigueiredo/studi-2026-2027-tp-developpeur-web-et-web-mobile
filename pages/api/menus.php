<?php

declare(strict_types=1);

use App\MenuRepository;

// API JSON utilisée par les filtres de la page « Nos menus » (sans rechargement de page)
$menus = MenuRepository::rechercher($_GET);

json_response(array_map(static fn (array $m) => [
    'id'          => (int) $m['menu_id'],
    'titre'       => $m['titre'],
    'description' => $m['description'],
    'personnes'   => (int) $m['nombre_personne_minimum'],
    'prix'        => (float) $m['prix_minimum'],
    'theme'       => $m['theme'],
    'regime'      => $m['regime'],
    'disponible'  => (int) $m['quantite_restante'] > 0,
    'image'       => $m['image'],
    'image_alt'   => $m['image_alt'],
], $menus));
