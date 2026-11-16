<?php

declare(strict_types=1);

use App\Database;
use App\MenuRepository;
use App\Upload;
use App\Validator;

$id = (int) ($_GET['id'] ?? $_POST['menu_id'] ?? 0);
$menu = $id ? MenuRepository::trouver($id, true) : null;
if ($id && !$menu) {
    abort(404, 'Menu introuvable.');
}

$errors = [];
$data = $menu ?? [
    'titre' => '', 'description' => '', 'theme_id' => '', 'regime_id' => '', 'nombre_personne_minimum' => '',
    'prix_par_personne' => '', 'conditions' => '', 'quantite_restante' => '', 'images' => [], 'plats' => [],
];
$platsChoisis = array_column($data['plats'], 'plat_id');

// Suppression d'une image de la galerie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer_image' && $menu) {
    $img = Database::run('SELECT chemin FROM menu_image WHERE image_id = :i AND menu_id = :m', ['i' => (int) $_POST['image_id'], 'm' => $id])->fetch();
    if ($img) {
        Database::run('DELETE FROM menu_image WHERE image_id = :i', ['i' => (int) $_POST['image_id']]);
        Upload::supprimer($img['chemin']);
        flash('success', 'L\'image a été supprimée.');
    }
    redirect('/employe/menu', ['id' => $id]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'enregistrer') {
    $data = array_merge($data, [
        'titre'                   => trim($_POST['titre'] ?? ''),
        'description'             => trim($_POST['description'] ?? ''),
        'theme_id'                => (int) ($_POST['theme_id'] ?? 0),
        'regime_id'               => (int) ($_POST['regime_id'] ?? 0),
        'nombre_personne_minimum' => $_POST['nombre_personne_minimum'] ?? '',
        'prix_par_personne'            => str_replace(',', '.', (string) ($_POST['prix_par_personne'] ?? '')),
        'conditions'              => trim($_POST['conditions'] ?? ''),
        'quantite_restante'       => $_POST['quantite_restante'] ?? '',
    ]);
    // On ne garde que des identifiants de plats qui existent vraiment
    $platsExistants = array_map('intval', Database::run('SELECT plat_id FROM plat')->fetchAll(PDO::FETCH_COLUMN));
    $platsChoisis = array_values(array_intersect(array_map('intval', (array) ($_POST['plats'] ?? [])), $platsExistants));
    $themes = array_map('intval', array_column(MenuRepository::themes(), 'theme_id'));
    $regimes = array_map('intval', array_column(MenuRepository::regimes(), 'regime_id'));

    $v = (new Validator())
        ->required('titre', $data['titre'], 'Titre')
        ->maxLength('titre', $data['titre'], 150, 'Titre')
        ->required('description', $data['description'], 'Description')
        ->required('conditions', $data['conditions'], 'Conditions')
        ->in('theme_id', $data['theme_id'], $themes, 'Choisissez un thème.')
        ->in('regime_id', $data['regime_id'], $regimes, 'Choisissez un régime.')
        ->intRange('nombre_personne_minimum', $data['nombre_personne_minimum'], 1, 500, 'Le nombre de personnes minimum doit être un entier positif.')
        ->intRange('quantite_restante', $data['quantite_restante'], 0, 10000, 'Le stock doit être un entier positif ou nul.')
        ->custom('prix_par_personne', is_numeric($data['prix_par_personne']) && (float) $data['prix_par_personne'] > 0, 'Le prix doit être un nombre positif.')
        ->custom('plats', $platsChoisis !== [], 'Sélectionnez au moins un plat.');

    $nouvelleImage = ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    $texteAlt = trim($_POST['texte_alt'] ?? '');
    if ($nouvelleImage) {
        // RGAA : toute image porteuse d'information doit avoir une alternative textuelle
        $v->required('texte_alt', $texteAlt, 'Description de l\'image');
    }

    if ($v->fails()) {
        $errors = $v->errors();
    } else {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $params = [
                'titre' => $data['titre'], 'description' => $data['description'], 'theme' => $data['theme_id'],
                'regime' => $data['regime_id'], 'min' => (int) $data['nombre_personne_minimum'],
                'prix' => round((float) $data['prix_par_personne'], 2), 'conditions' => $data['conditions'],
                'stock' => (int) $data['quantite_restante'],
            ];
            if ($id) {
                Database::run(
                    'UPDATE menu SET titre = :titre, description = :description, theme_id = :theme, regime_id = :regime,
                        nombre_personne_minimum = :min, prix_par_personne = :prix, conditions = :conditions, quantite_restante = :stock
                     WHERE menu_id = :id',
                    $params + ['id' => $id]
                );
            } else {
                Database::run(
                    'INSERT INTO menu (titre, description, theme_id, regime_id, nombre_personne_minimum, prix_par_personne, conditions, quantite_restante)
                     VALUES (:titre, :description, :theme, :regime, :min, :prix, :conditions, :stock)',
                    $params
                );
                $id = (int) $pdo->lastInsertId();
            }

            // Composition : on remplace la liste des plats du menu
            Database::run('DELETE FROM menu_plat WHERE menu_id = :id', ['id' => $id]);
            $insert = $pdo->prepare('INSERT INTO menu_plat (menu_id, plat_id) VALUES (:m, :p)');
            foreach (array_unique($platsChoisis) as $platId) {
                $insert->execute(['m' => $id, 'p' => $platId]);
            }

            if ($nouvelleImage) {
                $chemin = Upload::image($_FILES['image']);
                $ordre = (int) Database::run('SELECT COALESCE(MAX(ordre), 0) + 1 FROM menu_image WHERE menu_id = :m', ['m' => $id])->fetchColumn();
                Database::run(
                    'INSERT INTO menu_image (menu_id, chemin, texte_alt, ordre) VALUES (:m, :c, :a, :o)',
                    ['m' => $id, 'c' => $chemin, 'a' => $texteAlt, 'o' => $ordre]
                );
            }
            $pdo->commit();
            flash('success', 'Le menu a été enregistré.');
            redirect('/employe/menu', ['id' => $id]);
        } catch (DomainException $ex) {
            $pdo->rollBack();
            $errors['image'] = $ex->getMessage();
        } catch (Throwable $ex) {
            $pdo->rollBack();
            throw $ex;
        }
    }
}

$plats = Database::run("SELECT plat_id, titre_plat, type_plat FROM plat ORDER BY FIELD(type_plat, 'entree', 'plat', 'dessert'), titre_plat")->fetchAll();

render('employe/menu-form', [
    'title'        => $menu ? 'Modifier le menu' : 'Nouveau menu',
    'menu'         => $menu,
    'data'         => $data,
    'errors'       => $errors,
    'themes'       => MenuRepository::themes(),
    'regimes'      => MenuRepository::regimes(),
    'plats'        => $plats,
    'platsChoisis' => $platsChoisis,
]);
