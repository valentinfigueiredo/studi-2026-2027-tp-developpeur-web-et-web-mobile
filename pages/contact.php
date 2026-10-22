<?php

declare(strict_types=1);

use App\Auth;
use App\Mailer;
use App\Validator;

$errors = [];
$data = ['titre' => '', 'message' => '', 'email' => Auth::user()['email'] ?? ''];
$envoye = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'titre'   => trim($_POST['titre'] ?? ''),
        'message' => trim($_POST['message'] ?? ''),
        'email'   => trim($_POST['email'] ?? ''),
    ];
    // Champ "pot de miel" invisible pour les humains : s'il est rempli, c'est un robot spammeur
    $robot = ($_POST['site_web'] ?? '') !== '';

    $v = (new Validator())
        ->required('titre', $data['titre'], 'Titre')
        ->maxLength('titre', $data['titre'], 150, 'Titre')
        ->required('message', $data['message'], 'Description')
        ->maxLength('message', $data['message'], 3000, 'Description')
        ->required('email', $data['email'], 'Email')
        ->email('email', $data['email']);

    if ($v->fails()) {
        $errors = $v->errors();
    } else {
        if (!$robot) {
            // L'email du visiteur est en "Répondre à" : l'entreprise lui répond directement
            Mailer::send(Mailer::entreprise(), '[Contact site] ' . $data['titre'], 'contact', $data, $data['email']);
        }
        $envoye = true;
    }
}

render('contact', ['title' => 'Contact', 'errors' => $errors, 'data' => $data, 'envoye' => $envoye]);
