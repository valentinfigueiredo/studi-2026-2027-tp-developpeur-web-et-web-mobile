<?php

declare(strict_types=1);

use App\Auth;

if (Auth::check()) {
    redirect(accueil_selon_role());
}

$redirect = safe_redirect_target($_GET['redirect'] ?? $_POST['redirect'] ?? null, '');
$erreur = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $resultat = Auth::attempt($email, $_POST['password'] ?? '');

    if (is_string($resultat)) {
        $erreur = $resultat;
    } else {
        Auth::login($resultat);
        flash('success', 'Bonjour ' . $resultat['prenom'] . ', vous êtes connecté.');
        redirect($redirect !== '' ? $redirect : accueil_selon_role());
    }
}

render('auth/connexion', ['title' => 'Connexion', 'erreur' => $erreur, 'email' => $email, 'redirect' => $redirect]);
