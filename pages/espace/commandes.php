<?php

declare(strict_types=1);

use App\Auth;
use App\Database;

$commandes = Database::run(
    'SELECT c.commande_id, c.numero_commande, c.date_commande, c.date_prestation, c.nombre_personne,
            c.prix_total, c.statut, m.titre AS menu_titre
     FROM commande c JOIN menu m ON m.menu_id = c.menu_id
     WHERE c.utilisateur_id = :id
     ORDER BY c.date_commande DESC',
    ['id' => Auth::id()]
)->fetchAll();

render('espace/commandes', ['title' => 'Mes commandes', 'commandes' => $commandes]);
