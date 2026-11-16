<?php

declare(strict_types=1);

use App\Auth;
use App\Database;
use App\Mailer;
use App\Validator;

if (Auth::check()) {
    redirect(accueil_selon_role());
}

$redirect = safe_redirect_target($_GET['redirect'] ?? $_POST['redirect'] ?? null, '/espace');
$errors = [];
$data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'prenom'      => trim($_POST['prenom'] ?? ''),
        'nom'         => trim($_POST['nom'] ?? ''),
        'email'       => mb_strtolower(trim($_POST['email'] ?? '')),
        'telephone'   => Validator::normalizePhone($_POST['telephone'] ?? ''),
        'adresse_postale'     => trim($_POST['adresse_postale'] ?? ''),
        'code_postal' => trim($_POST['code_postal'] ?? ''),
        'ville'       => trim($_POST['ville'] ?? ''),
        'pays'        => trim($_POST['pays'] ?? '') ?: 'France',
    ];
    $password = $_POST['password'] ?? '';

    $v = (new Validator())
        ->required('prenom', $data['prenom'], 'Prénom')
        ->required('nom', $data['nom'], 'Nom')
        ->required('email', $data['email'], 'Email')
        ->required('telephone', $data['telephone'], 'Téléphone')
        ->required('adresse_postale', $data['adresse_postale'], 'Adresse')
        ->required('code_postal', $data['code_postal'], 'Code postal')
        ->required('ville', $data['ville'], 'Ville')
        ->maxLength('prenom', $data['prenom'], 100, 'Prénom')
        ->maxLength('nom', $data['nom'], 100, 'Nom')
        ->maxLength('pays', $data['pays'], 100, 'Pays')
        ->email('email', $data['email'])
        ->phone('telephone', $data['telephone'])
        ->custom('code_postal', (bool) preg_match('/^\d{5}$/', $data['code_postal']), 'Le code postal doit contenir 5 chiffres.')
        ->password('password', $password)
        ->custom('password_confirmation', $password === ($_POST['password_confirmation'] ?? ''), 'Les deux mots de passe ne correspondent pas.')
        ->custom('rgpd', !empty($_POST['rgpd']), 'Vous devez accepter la politique de confidentialité.');

    if (!$v->fails()) {
        $existe = Database::run('SELECT 1 FROM utilisateur WHERE email = :e', ['e' => $data['email']])->fetchColumn();
        if ($existe) {
            $v->custom('email', false, 'Un compte existe déjà avec cette adresse email.');
        }
    }

    if ($v->fails()) {
        $errors = $v->errors();
    } else {
        // Le rôle est imposé côté serveur (RG3) : impossible de s'inscrire comme employé ou administrateur
        Database::run(
            "INSERT INTO utilisateur (email, password, prenom, nom, telephone, adresse_postale, code_postal, ville, pays, role_id)
             VALUES (:email, :password, :prenom, :nom, :telephone, :adresse_postale, :cp, :ville, :pays,
                     (SELECT role_id FROM role WHERE libelle = 'utilisateur'))",
            [
                'email'     => $data['email'],
                'password'  => Auth::hash($password),
                'prenom'    => $data['prenom'],
                'nom'       => $data['nom'],
                'telephone' => $data['telephone'],
                'adresse_postale'   => $data['adresse_postale'],
                'cp'        => $data['code_postal'],
                'ville'     => $data['ville'],
                'pays'      => $data['pays'],
            ]
        );

        Mailer::send($data['email'], 'Bienvenue chez Vite & Gourmand !', 'bienvenue', ['prenom' => $data['prenom']]);

        $user = Auth::attempt($data['email'], $password);
        Auth::login($user);
        flash('success', 'Votre compte a été créé. Bienvenue ' . $data['prenom'] . ' !');
        redirect($redirect);
    }
}

render('auth/inscription', ['title' => 'Créer un compte', 'errors' => $errors, 'data' => $data, 'redirect' => $redirect]);
