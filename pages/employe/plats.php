<?php

declare(strict_types=1);

use App\Database;
use App\Validator;

$errors = [];
$edition = null;
$allergenes = Database::run('SELECT allergene_id, libelle FROM allergene ORDER BY libelle')->fetchAll();
$idsAllergenes = array_map('intval', array_column($allergenes, 'allergene_id'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $platId = (int) ($_POST['plat_id'] ?? 0);

    if ($action === 'supprimer') {
        Database::run('DELETE FROM plat WHERE plat_id = :id', ['id' => $platId]);
        flash('success', 'Le plat a été supprimé (et retiré des menus qui le contenaient).');
        redirect('/employe/plats');
    }

    if ($action === 'enregistrer') {
        $titre = trim($_POST['titre_plat'] ?? '');
        $type = $_POST['type_plat'] ?? '';
        $description = trim($_POST['description'] ?? '');
        $choix = array_values(array_intersect(array_map('intval', (array) ($_POST['allergenes'] ?? [])), $idsAllergenes));

        $v = (new Validator())
            ->required('titre_plat', $titre, 'Nom du plat')
            ->maxLength('titre_plat', $titre, 150, 'Nom du plat')
            ->in('type_plat', $type, ['entree', 'plat', 'dessert'], 'Choisissez le type de plat.');

        if ($v->fails()) {
            $errors = $v->errors();
            $edition = ['plat_id' => $platId, 'titre_plat' => $titre, 'type_plat' => $type, 'description' => $description, 'allergenes' => $choix];
        } else {
            $pdo = Database::pdo();
            $pdo->beginTransaction();
            if ($platId) {
                Database::run('UPDATE plat SET titre_plat = :n, type_plat = :t, description = :d WHERE plat_id = :id',
                    ['n' => $titre, 't' => $type, 'd' => $description ?: null, 'id' => $platId]);
            } else {
                Database::run('INSERT INTO plat (titre_plat, type_plat, description) VALUES (:n, :t, :d)',
                    ['n' => $titre, 't' => $type, 'd' => $description ?: null]);
                $platId = (int) $pdo->lastInsertId();
            }
            Database::run('DELETE FROM plat_allergene WHERE plat_id = :id', ['id' => $platId]);
            $insert = $pdo->prepare('INSERT INTO plat_allergene (plat_id, allergene_id) VALUES (:p, :a)');
            foreach ($choix as $a) {
                $insert->execute(['p' => $platId, 'a' => $a]);
            }
            $pdo->commit();
            flash('success', 'Le plat « ' . $titre . ' » a été enregistré.');
            redirect('/employe/plats');
        }
    }
}

if ($edition === null && isset($_GET['id'])) {
    $edition = Database::run('SELECT * FROM plat WHERE plat_id = :id', ['id' => (int) $_GET['id']])->fetch() ?: null;
    if ($edition) {
        $edition['allergenes'] = array_map('intval', Database::run(
            'SELECT allergene_id FROM plat_allergene WHERE plat_id = :id', ['id' => $edition['plat_id']]
        )->fetchAll(PDO::FETCH_COLUMN));
    }
}

$plats = Database::run(
    "SELECT p.plat_id, p.titre_plat, p.type_plat,
            GROUP_CONCAT(DISTINCT a.libelle ORDER BY a.libelle SEPARATOR ', ') AS allergenes,
            COUNT(DISTINCT mp.menu_id) AS nb_menus
     FROM plat p
     LEFT JOIN plat_allergene pa ON pa.plat_id = p.plat_id
     LEFT JOIN allergene a ON a.allergene_id = pa.allergene_id
     LEFT JOIN menu_plat mp ON mp.plat_id = p.plat_id
     GROUP BY p.plat_id
     ORDER BY FIELD(p.type_plat, 'entree', 'plat', 'dessert'), p.titre_plat"
)->fetchAll();

render('employe/plats', [
    'title'      => 'Gestion des plats',
    'plats'      => $plats,
    'allergenes' => $allergenes,
    'edition'    => $edition ?? ['plat_id' => 0, 'titre_plat' => '', 'type_plat' => '', 'description' => '', 'allergenes' => []],
    'errors'     => $errors,
]);
