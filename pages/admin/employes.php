<?php

declare(strict_types=1);

use App\Auth;
use App\Database;
use App\Mailer;
use App\Validator;

$errors = [];
$data = ['prenom' => '', 'nom' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'creer') {
        $data = [
            'prenom' => trim($_POST['prenom'] ?? ''),
            'nom'    => trim($_POST['nom'] ?? ''),
            'email'  => mb_strtolower(trim($_POST['email'] ?? '')),
        ];
        $password = $_POST['password'] ?? '';
        $v = (new Validator())
            ->required('prenom', $data['prenom'], 'Prénom')
            ->required('nom', $data['nom'], 'Nom')
            ->required('email', $data['email'], 'Email')
            ->email('email', $data['email'])
            ->password('password', $password)
            ->custom('password_confirmation', $password === ($_POST['password_confirmation'] ?? ''), 'Les deux mots de passe ne correspondent pas.');
        $existe = Database::run('SELECT 1 FROM utilisateur WHERE email = :e', ['e' => $data['email']])->fetchColumn();
        $v->custom('email', !$existe, 'Un compte existe déjà avec cette adresse email.');

        if ($v->fails()) {
            $errors = $v->errors();
        } else {
            // Rôle "employe" imposé : aucun formulaire ne permet de créer un administrateur (RG4)
            Database::run(
                "INSERT INTO utilisateur (email, password, prenom, nom, role_id)
                 VALUES (:e, :p, :prenom, :nom, (SELECT role_id FROM role WHERE libelle = 'employe'))",
                ['e' => $data['email'], 'p' => Auth::hash($password), 'prenom' => $data['prenom'], 'nom' => $data['nom']]
            );
            // Le mot de passe n'est PAS envoyé par mail : l'employé doit le demander à l'administrateur
            Mailer::send($data['email'], 'Votre compte employé Vite & Gourmand', 'compte-employe', ['prenom' => $data['prenom'], 'email' => $data['email']]);
            flash('success', 'Le compte de ' . $data['prenom'] . ' a été créé. Communiquez-lui son mot de passe de vive voix.');
            redirect('/admin/employes');
        }
    }

    if (in_array($action, ['desactiver', 'reactiver'], true)) {
        // La condition sur le rôle empêche de désactiver un administrateur via une requête modifiée
        Database::run(
            "UPDATE utilisateur SET actif = :a WHERE utilisateur_id = :id
             AND role_id = (SELECT role_id FROM role WHERE libelle = 'employe')",
            ['a' => $action === 'reactiver' ? 1 : 0, 'id' => (int) ($_POST['utilisateur_id'] ?? 0)]
        );
        flash('success', $action === 'reactiver' ? 'Le compte a été réactivé.' : 'Le compte a été désactivé : l\'employé ne peut plus se connecter.');
        redirect('/admin/employes');
    }
}

$employes = Database::run(
    "SELECT u.utilisateur_id, u.prenom, u.nom, u.email, u.actif, u.date_creation
     FROM utilisateur u JOIN role r ON r.role_id = u.role_id
     WHERE r.libelle = 'employe' ORDER BY u.actif DESC, u.nom"
)->fetchAll();

render('admin/employes', ['title' => 'Gestion des employés', 'employes' => $employes, 'errors' => $errors, 'data' => $data]);
