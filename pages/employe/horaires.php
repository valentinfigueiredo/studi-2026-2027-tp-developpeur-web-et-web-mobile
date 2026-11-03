<?php

declare(strict_types=1);

use App\Database;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $saisie = (array) ($_POST['horaires'] ?? []);
    $jours = array_column(horaires(), 'jour', 'horaire_id');
    $valeurs = [];

    foreach ($jours as $id => $jour) {
        $h = $saisie[$id] ?? [];
        $ferme = !empty($h['ferme']);
        $ouverture = $h['ouverture'] ?? '';
        $fermeture = $h['fermeture'] ?? '';
        if (!$ferme) {
            $format = '/^([01]\d|2[0-3]):[0-5]\d$/';
            if (!preg_match($format, $ouverture) || !preg_match($format, $fermeture)) {
                $errors[$id] = 'Indiquez une heure d\'ouverture et de fermeture pour le ' . $jour . ' (ou cochez « Fermé »).';
            } elseif ($ouverture >= $fermeture) {
                $errors[$id] = 'Le ' . $jour . ', l\'heure de fermeture doit être après l\'heure d\'ouverture.';
            }
        }
        $valeurs[$id] = [$ferme, $ferme ? null : $ouverture, $ferme ? null : $fermeture];
    }

    if (!$errors) {
        $stmt = Database::pdo()->prepare('UPDATE horaire SET ferme = :f, heure_ouverture = :o, heure_fermeture = :c WHERE horaire_id = :id');
        foreach ($valeurs as $id => [$ferme, $o, $c]) {
            $stmt->execute(['f' => (int) $ferme, 'o' => $o, 'c' => $c, 'id' => $id]);
        }
        flash('success', 'Les horaires ont été mis à jour.');
        redirect('/employe/horaires');
    }
}

render('employe/horaires', ['title' => 'Gestion des horaires', 'horaires' => horaires(), 'errors' => $errors]);
