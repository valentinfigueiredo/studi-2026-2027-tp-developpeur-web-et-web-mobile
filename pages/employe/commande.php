<?php

declare(strict_types=1);

use App\Auth;
use App\CommandeService;
use App\Validator;

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$commande = CommandeService::trouver($id);
if (!$commande) {
    abort(404, 'Commande introuvable.');
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'statut') {
            CommandeService::changerStatut($commande, (string) ($_POST['statut'] ?? ''), Auth::id());
            flash('success', 'Le statut de la commande a été mis à jour.');
            redirect('/employe/commande', ['id' => $id]);
        }
        if ($action === 'annuler') {
            $motif = trim($_POST['motif'] ?? '');
            $mode = $_POST['mode_contact'] ?? '';
            $v = (new Validator())
                ->custom('contact_ok', !empty($_POST['contact_ok']), 'Vous devez avoir contacté le client avant d\'annuler.')
                ->in('mode_contact', $mode, ['telephone', 'mail'], 'Précisez comment le client a été contacté.')
                ->required('motif', $motif, 'Motif d\'annulation')
                ->maxLength('motif', $motif, 1000, 'Motif');
            if ($v->fails()) {
                $errors = $v->errors();
            } else {
                CommandeService::annulerParEmploye($commande, $motif, $mode, Auth::id());
                flash('success', 'La commande a été annulée et le client a été prévenu par email.');
                redirect('/employe/commande', ['id' => $id]);
            }
        }
    } catch (DomainException $ex) {
        flash('danger', $ex->getMessage());
        redirect('/employe/commande', ['id' => $id]);
    }
}

render('employe/commande', [
    'title'  => 'Commande ' . $commande['numero_commande'],
    'c'      => $commande,
    'suivi'  => CommandeService::suivi($id),
    'errors' => $errors,
]);
