<?php

declare(strict_types=1);

use App\CommandeService;
use App\Database;

$statut = $_GET['statut'] ?? '';
$client = trim($_GET['client'] ?? '');

$where = [];
$params = [];
if ($statut !== '' && isset(CommandeService::STATUTS[$statut])) {
    $where[] = 'c.statut = :statut';
    $params['statut'] = $statut;
}
if ($client !== '') {
    // Recherche partielle sur le nom, le prénom ou l'email du client
    // Avec de vraies requêtes préparées, un paramètre nommé ne peut servir qu'une fois : q1, q2, q3, q4
    $where[] = '(u.nom LIKE :q1 OR u.prenom LIKE :q2 OR u.email LIKE :q3 OR CONCAT(u.prenom, \' \', u.nom) LIKE :q4)';
    $motif = '%' . addcslashes($client, '%_') . '%';
    $params += ['q1' => $motif, 'q2' => $motif, 'q3' => $motif, 'q4' => $motif];
}

$commandes = Database::run(
    'SELECT c.commande_id, c.numero_commande, c.date_prestation, c.heure_livraison, c.nombre_personne, c.prix_total, c.statut,
            u.prenom, u.nom, m.titre AS menu_titre
     FROM commande c
     JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
     JOIN menu m ON m.menu_id = c.menu_id'
    . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
    . ' ORDER BY c.date_prestation ASC, c.heure_livraison ASC',
    $params
)->fetchAll();

render('employe/commandes', [
    'title'     => 'Gestion des commandes',
    'commandes' => $commandes,
    'statut'    => $statut,
    'client'    => $client,
]);
