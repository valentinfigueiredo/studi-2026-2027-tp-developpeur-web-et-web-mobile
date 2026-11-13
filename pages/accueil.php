<?php

declare(strict_types=1);

use App\Database;

// Uniquement les avis validés par un employé (RG17)
$avis = Database::run(
    "SELECT a.note, a.description, a.date_avis, u.prenom, LEFT(u.nom, 1) AS initiale, m.titre AS menu
     FROM avis a
     JOIN utilisateur u ON u.utilisateur_id = a.utilisateur_id
     JOIN commande c ON c.commande_id = a.commande_id
     JOIN menu m ON m.menu_id = c.menu_id
     WHERE a.statut = 'valide'
     ORDER BY a.date_avis DESC
     LIMIT 6"
)->fetchAll();

$noteMoyenne = Database::run("SELECT ROUND(AVG(note), 1) FROM avis WHERE statut = 'valide'")->fetchColumn();

render('accueil', [
    'title'       => 'Traiteur à Bordeaux depuis 25 ans',
    'avis'        => $avis,
    'noteMoyenne' => $noteMoyenne,
]);
