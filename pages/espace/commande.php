<?php

declare(strict_types=1);

use App\Auth;
use App\CommandeService;

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
// Le 2e paramètre vérifie que la commande appartient bien à l'utilisateur connecté
$commande = CommandeService::trouver($id, Auth::id());
if (!$commande) {
    abort(404, 'Commande introuvable.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'annuler') {
    try {
        CommandeService::annulerParClient($commande);
        flash('success', 'Votre commande ' . $commande['numero_commande'] . ' a été annulée.');
    } catch (DomainException $ex) {
        flash('danger', $ex->getMessage());
    }
    redirect('/espace/commande', ['id' => $id]);
}

render('espace/commande', [
    'title'    => 'Commande ' . $commande['numero_commande'],
    'c'        => $commande,
    'suivi'    => CommandeService::suivi($id),
    // Le suivi détaillé est visible une fois la commande acceptée
    'afficherSuivi' => !in_array($commande['statut'], ['en_attente', 'annulee'], true),
]);
