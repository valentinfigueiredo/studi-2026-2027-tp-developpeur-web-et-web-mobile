<?php

declare(strict_types=1);

use App\Database;
use App\Env;
use App\Mailer;

$envoye = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mb_strtolower(trim($_POST['email'] ?? ''));
    $user = Database::run('SELECT utilisateur_id, prenom FROM utilisateur WHERE email = :e AND actif = 1', ['e' => $email])->fetch();

    if ($user) {
        // Jeton aléatoire cryptographiquement sûr ; seul son hash SHA-256 est stocké en base
        $token = bin2hex(random_bytes(32));
        Database::run('UPDATE reinitialisation_mdp SET utilise = 1 WHERE utilisateur_id = :id', ['id' => $user['utilisateur_id']]);
        Database::run(
            'INSERT INTO reinitialisation_mdp (utilisateur_id, token_hash, date_expiration)
             VALUES (:id, :hash, NOW() + INTERVAL 1 HOUR)',
            ['id' => $user['utilisateur_id'], 'hash' => hash('sha256', $token)]
        );
        $lien = rtrim(Env::get('APP_URL', 'http://localhost:8000'), '/') . '/reinitialiser?token=' . $token;
        Mailer::send($email, 'Réinitialisation de votre mot de passe', 'reinitialisation', ['prenom' => $user['prenom'], 'lien' => $lien]);
    }
    // Même message que l'email existe ou non : on ne révèle pas qui a un compte
    $envoye = true;
}

render('auth/mot-de-passe-oublie', ['title' => 'Mot de passe oublié', 'envoye' => $envoye]);
