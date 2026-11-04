<?php

use App\CommandeService;

$suivants = CommandeService::TRANSITIONS[$c['statut']] ?? [];
// Après "livrée", une seule suite possible selon le prêt de matériel
if ($c['statut'] === 'livre') {
    $suivants = [$c['pret_materiel'] ? 'en_attente_retour_materiel' : 'terminee'];
}
?>
<div class="container py-4">
    <h1 class="mb-3">Espace de gestion</h1>
    <?= render_partial('partials/nav-staff') ?>
    <p><a href="/employe">&larr; Retour aux commandes</a></p>

    <h2 class="h4">Commande <?= e($c['numero_commande']) ?> <?= render_partial('partials/badge-statut', ['statut' => $c['statut']]) ?></h2>

    <div class="row g-4 mt-1">
        <div class="col-lg-7">
            <section class="card mb-4" aria-labelledby="titre-client">
                <div class="card-body">
                    <h3 id="titre-client" class="h5">Client</h3>
                    <p class="mb-0"><?= e($c['prenom'] . ' ' . $c['nom']) ?><br>
                        <a href="mailto:<?= e($c['email']) ?>"><?= e($c['email']) ?></a><br>
                        <a href="tel:<?= e($c['telephone']) ?>"><?= e($c['telephone']) ?></a></p>
                </div>
            </section>
            <section class="card mb-4" aria-labelledby="titre-prestation">
                <div class="card-body">
                    <h3 id="titre-prestation" class="h5">Prestation</h3>
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Menu</dt><dd class="col-sm-7"><?= e($c['menu_titre']) ?></dd>
                        <dt class="col-sm-5">Date et heure</dt><dd class="col-sm-7"><?= e(date_fr($c['date_prestation'])) ?> à <?= e(heure_fr($c['heure_livraison'])) ?></dd>
                        <dt class="col-sm-5">Adresse</dt><dd class="col-sm-7"><?= e($c['adresse_prestation'] . ', ' . $c['code_postal'] . ' ' . $c['ville_prestation']) ?></dd>
                        <dt class="col-sm-5">Personnes</dt><dd class="col-sm-7"><?= (int) $c['nombre_personne'] ?></dd>
                        <dt class="col-sm-5">Prêt de matériel</dt><dd class="col-sm-7"><?= $c['pret_materiel'] ? 'Oui' : 'Non' ?></dd>
                        <dt class="col-sm-5">Total</dt><dd class="col-sm-7"><?= money($c['prix_total']) ?> (dont livraison <?= money($c['prix_livraison']) ?>)</dd>
                        <?php if ($c['statut'] === 'annulee'): ?>
                            <dt class="col-sm-5">Annulation</dt>
                            <dd class="col-sm-7"><?= e($c['motif_annulation']) ?><?= $c['mode_contact'] ? ' (client contacté par ' . e($c['mode_contact'] === 'mail' ? 'mail' : 'téléphone') . ')' : ' (par le client)' ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </section>

            <?php if ($suivants): ?>
                <section class="card mb-4" aria-labelledby="titre-statut">
                    <div class="card-body">
                        <h3 id="titre-statut" class="h5">Faire avancer la commande</h3>
                        <form method="post" action="/employe/commande" class="d-flex flex-wrap gap-2">
                            <?= App\Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int) $c['commande_id'] ?>">
                            <input type="hidden" name="action" value="statut">
                            <?php foreach ($suivants as $s): ?>
                                <button type="submit" name="statut" value="<?= e($s) ?>" class="btn btn-bordeaux">
                                    Passer à « <?= e(CommandeService::libelle($s)) ?> »
                                </button>
                            <?php endforeach; ?>
                        </form>
                    </div>
                </section>
            <?php endif; ?>

            <?php if (!in_array($c['statut'], ['terminee', 'annulee'], true)): ?>
                <section class="card border-danger mb-4" aria-labelledby="titre-annulation">
                    <div class="card-body">
                        <h3 id="titre-annulation" class="h5">Annuler la commande</h3>
                        <p class="small">Le client doit avoir été contacté au préalable (par téléphone ou par mail).</p>
                        <?php if ($errors): ?><div class="alert alert-danger" role="alert">Le formulaire d'annulation est incomplet.</div><?php endif; ?>
                        <form method="post" action="/employe/commande" class="js-confirmer" data-confirmation="Confirmez-vous l'annulation de cette commande ?" novalidate>
                            <?= App\Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int) $c['commande_id'] ?>">
                            <input type="hidden" name="action" value="annuler">
                            <fieldset class="mb-3">
                                <legend class="form-label fs-6">Mode de contact du client <span aria-hidden="true">*</span></legend>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="mode_contact" id="contact-tel" value="telephone" <?= ($_POST['mode_contact'] ?? '') === 'telephone' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="contact-tel">Appel téléphonique</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="mode_contact" id="contact-mail" value="mail" <?= ($_POST['mode_contact'] ?? '') === 'mail' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="contact-mail">Mail</label>
                                </div>
                                <?= field_error($errors, 'mode_contact') ?>
                            </fieldset>
                            <div class="mb-3">
                                <label for="motif" class="form-label">Motif de l'annulation <span aria-hidden="true">*</span></label>
                                <textarea class="form-control<?= aria_invalid($errors, 'motif') ?>" id="motif" name="motif" rows="3" required><?= old($_POST, 'motif') ?></textarea>
                                <?= field_error($errors, 'motif') ?>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input<?= aria_invalid($errors, 'contact_ok') ?>" type="checkbox" id="contact_ok" name="contact_ok" value="1">
                                <label class="form-check-label" for="contact_ok">Je confirme avoir contacté le client et l'avoir informé de l'annulation <span aria-hidden="true">*</span></label>
                                <?= field_error($errors, 'contact_ok') ?>
                            </div>
                            <button type="submit" class="btn btn-danger">Annuler la commande</button>
                        </form>
                    </div>
                </section>
            <?php endif; ?>
        </div>
        <div class="col-lg-5">
            <section class="card" aria-labelledby="titre-historique">
                <div class="card-body">
                    <h3 id="titre-historique" class="h5">Historique</h3>
                    <ol class="suivi list-unstyled mb-0">
                        <?php foreach ($suivi as $etape): ?>
                            <li><strong><?= e(CommandeService::libelle($etape['statut'])) ?></strong><br>
                                <span class="small"><?= e(date_fr($etape['date_modification'], true)) ?></span></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </section>
        </div>
    </div>
</div>
<script src="/assets/js/confirmation.js" defer></script>
