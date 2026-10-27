<?php

declare(strict_types=1);

use App\Auth;
use App\CommandeService;
use App\Database;
use App\MenuRepository;

$client = Database::run(
    'SELECT prenom, nom, email, telephone, adresse, code_postal, ville FROM utilisateur WHERE utilisateur_id = :id',
    ['id' => Auth::id()]
)->fetch();

$menus = MenuRepository::commandables();
$menuId = (int) ($_POST['menu_id'] ?? $_GET['menu'] ?? 0);
$errors = [];

// Valeurs par défaut : l'adresse du compte (modifiable pour livrer ailleurs)
$data = [
    'adresse_prestation' => $client['adresse'],
    'code_postal'        => $client['code_postal'],
    'ville_prestation'   => $client['ville'],
    'nombre_personne'    => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menu = $menuId ? MenuRepository::trouver($menuId) : null;
    if (!$menu || (int) $menu['quantite_restante'] < 1) {
        $errors['menu_id'] = 'Veuillez choisir un menu disponible.';
        $data = $_POST + $data;
    } else {
        [$errors, $data] = CommandeService::valider($_POST, $menu);
        if (!$errors) {
            try {
                $id = CommandeService::creer(Auth::id(), $menuId, $data);
                flash('success', 'Merci ! Votre commande a bien été enregistrée. Un email de confirmation vous a été envoyé.');
                redirect('/espace/commande', ['id' => $id]);
            } catch (DomainException $ex) {
                $errors['menu_id'] = $ex->getMessage();
            }
        }
    }
}

render('commande/commander', [
    'title'   => 'Commander un menu',
    'client'  => $client,
    'menus'   => $menus,
    'menuId'  => $menuId,
    'data'    => $data,
    'errors'  => $errors,
    'scripts' => ['commande'],
]);
