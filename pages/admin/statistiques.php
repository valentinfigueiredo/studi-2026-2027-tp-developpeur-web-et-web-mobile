<?php

declare(strict_types=1);

use App\Database;

$menus = Database::run('SELECT menu_id, titre FROM menu ORDER BY titre')->fetchAll();

render('admin/statistiques', [
    'title'   => 'Statistiques',
    'menus'   => $menus,
    'scripts' => ['statistiques'],
]);
