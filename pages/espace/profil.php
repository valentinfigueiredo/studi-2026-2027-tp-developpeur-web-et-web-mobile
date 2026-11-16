<?php

declare(strict_types=1);

use App\Auth;
use App\Database;
use App\Validator;

$id = Auth::id();
$errors = [];
$user = Database::run(
    'SELECT prenom, nom, email, telephone, adresse_postale, code_postal, ville, pays, password FROM utilisateur WHERE utilisateur_id = :id',
    ['id' => $id]
)->fetch();
$data = $user;
$estClient = Auth::hasRole(Auth::UTILISATEUR);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'infos') {
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
        $v = (new Validator())
            ->required('prenom', $data['prenom'], 'Prénom')
            ->required('nom', $data['nom'], 'Nom')
            ->required('email', $data['email'], 'Email')
            ->email('email', $data['email'])
            ->phone('telephone', $data['telephone'])
            ->maxLength('pays', $data['pays'], 100, 'Pays');
        if ($estClient) {
            $v->required('telephone', $data['telephone'], 'Téléphone')
              ->required('adresse_postale', $data['adresse_postale'], 'Adresse')
              ->custom('code_postal', (bool) preg_match('/^\d{5}$/', $data['code_postal']), 'Le code postal doit contenir 5 chiffres.')
              ->required('ville', $data['ville'], 'Ville');
        }
        $pris = Database::run('SELECT 1 FROM utilisateur WHERE email = :e AND utilisateur_id <> :id', ['e' => $data['email'], 'id' => $id])->fetchColumn();
        $v->custom('email', !$pris, 'Cette adresse email est déjà utilisée par un autre compte.');

        if ($v->fails()) {
            $errors = $v->errors();
        } else {
            Database::run(
                'UPDATE utilisateur SET prenom = :prenom, nom = :nom, email = :email, telephone = :tel,
                    adresse_postale = :adresse_postale, code_postal = :cp, ville = :ville, pays = :pays WHERE utilisateur_id = :id',
                [
                    'prenom' => $data['prenom'], 'nom' => $data['nom'], 'email' => $data['email'],
                    'tel' => $data['telephone'] ?: null, 'adresse_postale' => $data['adresse_postale'] ?: null,
                    'cp' => $data['code_postal'] ?: null, 'ville' => $data['ville'] ?: null, 'pays' => $data['pays'], 'id' => $id,
                ]
            );
            $_SESSION['user']['prenom'] = $data['prenom'];
            $_SESSION['user']['email'] = $data['email'];
            flash('success', 'Vos informations ont été mises à jour.');
            redirect('/espace/profil');
        }
    }

    if ($action === 'mot_de_passe') {
        $nouveau = $_POST['password'] ?? '';
        $v = (new Validator())
            ->custom('ancien', password_verify($_POST['ancien'] ?? '', $user['password']), 'Le mot de passe actuel est incorrect.')
            ->password('password', $nouveau)
            ->custom('password_confirmation', $nouveau === ($_POST['password_confirmation'] ?? ''), 'Les deux mots de passe ne correspondent pas.');
        if ($v->fails()) {
            $errors = $v->errors();
        } else {
            Database::run('UPDATE utilisateur SET password = :p WHERE utilisateur_id = :id', ['p' => Auth::hash($nouveau), 'id' => $id]);
            session_regenerate_id(true);
            flash('success', 'Votre mot de passe a été modifié.');
            redirect('/espace/profil');
        }
    }
}

render('espace/profil', ['title' => 'Mon profil', 'data' => $data, 'errors' => $errors, 'estClient' => $estClient]);
