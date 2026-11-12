<?php

declare(strict_types=1);

use App\Auth;
use App\CommandeService;

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$commande = CommandeService::trouver($id, Auth::id());
if (!$commande) {
    abort(404, 'Commande introuvable.');
}
if ($commande['statut'] !== 'en_attente') {
    flash('warning', 'Cette commande a déjà été acceptée : elle ne peut plus être modifiée.');
    redirect('/espace/commande', ['id' => $id]);
}

$errors = [];
$data = $commande;
$data['heure_livraison'] = substr($commande['heure_livraison'], 0, 5);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Le menu ne peut pas être changé (RG11) : on reprend celui de la commande, pas celui du formulaire
    [$errors, $nouvelles] = CommandeService::valider($_POST, $commande);
    if (!$errors) {
        try {
            CommandeService::modifierParClient($commande, $nouvelles);
            flash('success', 'Votre commande a été modifiée.');
            redirect('/espace/commande', ['id' => $id]);
        } catch (DomainException $ex) {
            flash('danger', $ex->getMessage());
            redirect('/espace/commande', ['id' => $id]);
        }
    }
    $data = $nouvelles + $data;
}

render('espace/modifier', [
    'title'   => 'Modifier la commande ' . $commande['numero_commande'],
    'c'       => $commande,
    'data'    => $data,
    'errors'  => $errors,
    'scripts' => ['commande'],
]);
