<?php

declare(strict_types=1);

use App\Database;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer') {
    $id = (int) ($_POST['menu_id'] ?? 0);
    $aDesCommandes = Database::run('SELECT COUNT(*) FROM commande WHERE menu_id = :id', ['id' => $id])->fetchColumn() > 0;
    if ($aDesCommandes) {
        // Un menu déjà commandé est retiré de la vente mais conservé (historique et chiffre d'affaires)
        Database::run('UPDATE menu SET actif = 0 WHERE menu_id = :id', ['id' => $id]);
        flash('success', 'Ce menu a déjà été commandé : il a été retiré de la vente et archivé.');
    } else {
        Database::run('DELETE FROM menu WHERE menu_id = :id', ['id' => $id]);
        flash('success', 'Le menu a été supprimé.');
    }
    redirect('/employe/menus');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reactiver') {
    Database::run('UPDATE menu SET actif = 1 WHERE menu_id = :id', ['id' => (int) ($_POST['menu_id'] ?? 0)]);
    flash('success', 'Le menu est de nouveau en vente.');
    redirect('/employe/menus');
}

$menus = Database::run(
    'SELECT m.menu_id, m.titre, m.prix_par_personne, m.prix_par_personne * m.nombre_personne_minimum AS prix_minimum, m.nombre_personne_minimum, m.quantite_restante, m.actif,
            t.libelle AS theme, r.libelle AS regime
     FROM menu m JOIN theme t USING (theme_id) JOIN regime r USING (regime_id)
     ORDER BY m.actif DESC, m.titre'
)->fetchAll();

render('employe/menus', ['title' => 'Gestion des menus', 'menus' => $menus]);
