<?php

declare(strict_types=1);

use App\Database;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $decision = $_POST['decision'] ?? '';
    if (in_array($decision, ['valide', 'refuse'], true)) {
        Database::run('UPDATE avis SET statut = :s WHERE avis_id = :id', ['s' => $decision, 'id' => (int) ($_POST['avis_id'] ?? 0)]);
        flash('success', $decision === 'valide' ? 'L\'avis est maintenant visible sur la page d\'accueil.' : 'L\'avis a été refusé.');
    }
    redirect('/employe/avis');
}

$avis = Database::run(
    "SELECT a.avis_id, a.note, a.commentaire, a.statut, a.date_avis, u.prenom, u.nom, c.numero_commande, m.titre AS menu
     FROM avis a
     JOIN utilisateur u ON u.utilisateur_id = a.utilisateur_id
     JOIN commande c ON c.commande_id = a.commande_id
     JOIN menu m ON m.menu_id = c.menu_id
     ORDER BY FIELD(a.statut, 'en_attente', 'valide', 'refuse'), a.date_avis DESC"
)->fetchAll();

render('employe/avis', ['title' => 'Modération des avis', 'avis' => $avis]);
