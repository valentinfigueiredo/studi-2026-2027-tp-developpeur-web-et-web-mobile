<?php

declare(strict_types=1);

use App\Auth;
use App\Database;
use App\Validator;

$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
$demande = $token === '' ? false : Database::run(
    'SELECT reinitialisation_id, utilisateur_id FROM reinitialisation_mdp
     WHERE token_hash = :h AND utilise = 0 AND date_expiration > NOW()',
    ['h' => hash('sha256', $token)]
)->fetch();

if (!$demande) {
    flash('danger', 'Ce lien de réinitialisation est invalide ou a expiré. Faites une nouvelle demande.');
    redirect('/mot-de-passe-oublie');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $v = (new Validator())
        ->password('password', $password)
        ->custom('password_confirmation', $password === ($_POST['password_confirmation'] ?? ''), 'Les deux mots de passe ne correspondent pas.');

    if ($v->fails()) {
        $errors = $v->errors();
    } else {
        Database::run('UPDATE utilisateur SET password = :p WHERE utilisateur_id = :id', ['p' => Auth::hash($password), 'id' => $demande['utilisateur_id']]);
        // Lien à usage unique
        Database::run('UPDATE reinitialisation_mdp SET utilise = 1 WHERE reinitialisation_id = :id', ['id' => $demande['reinitialisation_id']]);
        flash('success', 'Votre mot de passe a été modifié. Vous pouvez vous connecter.');
        redirect('/connexion');
    }
}

render('auth/reinitialiser', ['title' => 'Nouveau mot de passe', 'token' => $token, 'errors' => $errors]);
