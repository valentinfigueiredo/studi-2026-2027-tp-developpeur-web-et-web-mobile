<?php

declare(strict_types=1);

use App\Auth;
use App\CommandeService;
use App\Database;
use App\Validator;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/espace');
}

$id = (int) ($_POST['commande_id'] ?? 0);
$commande = CommandeService::trouver($id, Auth::id());
if (!$commande) {
    abort(404, 'Commande introuvable.');
}

$commentaire = trim($_POST['commentaire'] ?? '');
$v = (new Validator())
    ->custom('commande', $commande['statut'] === 'terminee', 'Vous pourrez donner votre avis une fois la commande terminée.')
    ->custom('commande', $commande['avis_id'] === null, 'Vous avez déjà donné votre avis sur cette commande.')
    ->intRange('note', $_POST['note'] ?? null, 1, 5, 'La note doit être comprise entre 1 et 5.')
    ->required('commentaire', $commentaire, 'Commentaire')
    ->maxLength('commentaire', $commentaire, 1000, 'Commentaire');

if ($v->fails()) {
    flash('danger', implode(' ', $v->errors()));
} else {
    // Statut "en_attente" : l'avis ne sera visible qu'après validation par un employé (RG17)
    Database::run(
        'INSERT INTO avis (commande_id, utilisateur_id, note, description) VALUES (:c, :u, :n, :t)',
        ['c' => $id, 'u' => Auth::id(), 'n' => (int) $_POST['note'], 't' => $commentaire]
    );
    flash('success', 'Merci pour votre avis ! Il sera publié après validation par notre équipe.');
}
redirect('/espace/commande', ['id' => $id]);
