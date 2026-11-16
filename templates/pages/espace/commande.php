<?php use App\CommandeService; ?>
<div class="container py-4">
    <p><a href="/espace">&larr; Retour à mes commandes</a></p>
    <h1 class="mb-1">Commande <?= e($c['numero_commande']) ?></h1>
    <p class="mb-4"><?= render_partial('partials/badge-statut', ['statut' => $c['statut']]) ?>
        <span class="ms-2 small">Passée le <?= e(date_fr($c['date_commande'], true)) ?></span></p>

    <div class="row g-4">
        <div class="col-lg-7">
            <section class="card mb-4" aria-labelledby="titre-detail">
                <div class="card-body">
                    <h2 id="titre-detail" class="h5">Détail de la prestation</h2>
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Menu</dt><dd class="col-sm-7"><?= e($c['menu_titre']) ?></dd>
                        <dt class="col-sm-5">Date et heure</dt><dd class="col-sm-7"><?= e(date_fr($c['date_prestation'])) ?> à <?= e(heure_fr($c['heure_livraison'])) ?></dd>
                        <dt class="col-sm-5">Adresse</dt><dd class="col-sm-7"><?= e($c['adresse_prestation']) ?>, <?= e($c['code_postal'] . ' ' . $c['ville_prestation']) ?></dd>
                        <dt class="col-sm-5">Nombre de personnes</dt><dd class="col-sm-7"><?= (int) $c['nombre_personne'] ?></dd>
                        <dt class="col-sm-5">Prêt de matériel</dt><dd class="col-sm-7"><?= $c['pret_materiel'] ? 'Oui' . ($c['restitution_materiel'] ? ' (matériel restitué)' : ' (matériel non restitué)') : 'Non' ?></dd>
                        <dt class="col-sm-5">Prix du menu</dt><dd class="col-sm-7"><?= money($c['prix_menu']) ?></dd>
                        <?php if ((float) $c['montant_reduction'] > 0): ?>
                            <dt class="col-sm-5">Réduction (10 %)</dt><dd class="col-sm-7">- <?= money($c['montant_reduction']) ?></dd>
                        <?php endif; ?>
                        <dt class="col-sm-5">Livraison</dt><dd class="col-sm-7"><?= money($c['prix_livraison']) ?><?= (float) $c['distance_km'] > 0 ? ' (' . e((float) $c['distance_km']) . ' km)' : '' ?></dd>
                        <dt class="col-sm-5">Total</dt><dd class="col-sm-7 fw-bold"><?= money($c['prix_total']) ?></dd>
                        <?php if ($c['statut'] === 'annulee'): ?>
                            <dt class="col-sm-5">Motif d'annulation</dt><dd class="col-sm-7"><?= e($c['motif_annulation']) ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </section>

            <?php if ($c['statut'] === 'en_attente'): ?>
                <section class="card mb-4" aria-labelledby="titre-actions">
                    <div class="card-body">
                        <h2 id="titre-actions" class="h5">Modifier ou annuler</h2>
                        <p>Votre commande n'a pas encore été acceptée par notre équipe : vous pouvez encore la modifier (sauf le menu) ou l'annuler.</p>
                        <a href="/espace/commande/modifier?id=<?= (int) $c['commande_id'] ?>" class="btn btn-outline-bordeaux">Modifier la commande</a>
                        <form method="post" action="/espace/commande" class="d-inline js-confirmer" data-confirmation="Voulez-vous vraiment annuler cette commande ?">
                            <?= App\Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int) $c['commande_id'] ?>">
                            <input type="hidden" name="action" value="annuler">
                            <button type="submit" class="btn btn-outline-danger">Annuler la commande</button>
                        </form>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($c['statut'] === 'terminee'): ?>
                <section class="card mb-4" aria-labelledby="titre-avis">
                    <div class="card-body">
                        <h2 id="titre-avis" class="h5">Votre avis</h2>
                        <?php if ($c['avis_id']): ?>
                            <p class="mb-0">Merci ! Vous avez donné la note de <?= (int) $c['avis_note'] ?>/5.
                                <?= $c['avis_statut'] === 'valide' ? 'Votre avis est publié sur notre site.' : 'Votre avis est en cours de modération.' ?></p>
                        <?php else: ?>
                            <form method="post" action="/espace/avis">
                                <?= App\Csrf::field() ?>
                                <input type="hidden" name="commande_id" value="<?= (int) $c['commande_id'] ?>">
                                <fieldset class="mb-3">
                                    <legend class="form-label fs-6">Votre note <span aria-hidden="true">*</span></legend>
                                    <div class="notation">
                                        <?php for ($n = 5; $n >= 1; $n--): ?>
                                            <input type="radio" class="btn-check" name="note" id="note-<?= $n ?>" value="<?= $n ?>" required>
                                            <label class="btn btn-outline-or" for="note-<?= $n ?>"><?= $n ?><span class="visually-hidden"> sur 5</span> <span aria-hidden="true">★</span></label>
                                        <?php endfor; ?>
                                    </div>
                                </fieldset>
                                <div class="mb-3">
                                    <label for="commentaire" class="form-label">Votre commentaire <span aria-hidden="true">*</span></label>
                                    <textarea class="form-control" id="commentaire" name="commentaire" rows="4" required maxlength="1000"></textarea>
                                </div>
                                <button type="submit" class="btn btn-bordeaux">Envoyer mon avis</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>

        <div class="col-lg-5">
            <section class="card" aria-labelledby="titre-suivi">
                <div class="card-body">
                    <h2 id="titre-suivi" class="h5">Suivi de la commande</h2>
                    <?php if (!$afficherSuivi): ?>
                        <p class="mb-0"><?= $c['statut'] === 'annulee' ? 'Cette commande a été annulée.' : 'Le suivi détaillé sera disponible dès que notre équipe aura accepté votre commande.' ?></p>
                    <?php else: ?>
                        <ol class="suivi list-unstyled mb-0">
                            <?php foreach ($suivi as $etape): ?>
                                <li>
                                    <strong><?= e(CommandeService::libelle($etape['statut'])) ?></strong><br>
                                    <span class="small"><time datetime="<?= e($etape['date_modification']) ?>"><?= e(date_fr($etape['date_modification'], true)) ?></time></span>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</div>
<script src="/assets/js/confirmation.js" defer></script>
