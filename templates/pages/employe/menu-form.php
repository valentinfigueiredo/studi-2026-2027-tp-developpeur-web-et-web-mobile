<?php $libelles = ['entree' => 'Entrées', 'plat' => 'Plats', 'dessert' => 'Desserts']; ?>
<div class="container py-4">
    <h1 class="mb-3">Espace de gestion</h1>
    <?= render_partial('partials/nav-staff') ?>
    <p><a href="/employe/menus">&larr; Retour aux menus</a></p>
    <h2 class="h4"><?= $menu ? 'Modifier « ' . e($menu['titre']) . ' »' : 'Nouveau menu' ?></h2>

    <?php if ($errors): ?>
        <div class="alert alert-danger" role="alert">Le formulaire contient des erreurs. Corrigez les champs signalés.</div>
    <?php endif; ?>

    <form method="post" action="/employe/menu<?= $menu ? '?id=' . (int) $menu['menu_id'] : '' ?>" enctype="multipart/form-data" class="card card-body mb-4" novalidate>
        <?= App\Csrf::field() ?>
        <input type="hidden" name="action" value="enregistrer">
        <?php if ($menu): ?><input type="hidden" name="menu_id" value="<?= (int) $menu['menu_id'] ?>"><?php endif; ?>

        <div class="row g-3">
            <div class="col-md-8">
                <label for="titre" class="form-label">Titre <span aria-hidden="true">*</span></label>
                <input type="text" class="form-control<?= aria_invalid($errors, 'titre') ?>" id="titre" name="titre" value="<?= old($data, 'titre') ?>" required maxlength="150">
                <?= field_error($errors, 'titre') ?>
            </div>
            <div class="col-md-2">
                <label for="theme_id" class="form-label">Thème <span aria-hidden="true">*</span></label>
                <select class="form-select<?= aria_invalid($errors, 'theme_id') ?>" id="theme_id" name="theme_id" required>
                    <option value="">Choisir</option>
                    <?php foreach ($themes as $t): ?>
                        <option value="<?= (int) $t['theme_id'] ?>" <?= (int) $data['theme_id'] === (int) $t['theme_id'] ? 'selected' : '' ?>><?= e($t['libelle']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= field_error($errors, 'theme_id') ?>
            </div>
            <div class="col-md-2">
                <label for="regime_id" class="form-label">Régime <span aria-hidden="true">*</span></label>
                <select class="form-select<?= aria_invalid($errors, 'regime_id') ?>" id="regime_id" name="regime_id" required>
                    <option value="">Choisir</option>
                    <?php foreach ($regimes as $r): ?>
                        <option value="<?= (int) $r['regime_id'] ?>" <?= (int) $data['regime_id'] === (int) $r['regime_id'] ? 'selected' : '' ?>><?= e($r['libelle']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= field_error($errors, 'regime_id') ?>
            </div>
            <div class="col-12">
                <label for="description" class="form-label">Description <span aria-hidden="true">*</span></label>
                <textarea class="form-control<?= aria_invalid($errors, 'description') ?>" id="description" name="description" rows="3" required><?= old($data, 'description') ?></textarea>
                <?= field_error($errors, 'description') ?>
            </div>
            <div class="col-sm-4">
                <label for="nombre_personne_minimum" class="form-label">Nombre de personnes minimum <span aria-hidden="true">*</span></label>
                <input type="number" min="1" class="form-control<?= aria_invalid($errors, 'nombre_personne_minimum') ?>" id="nombre_personne_minimum" name="nombre_personne_minimum" value="<?= old($data, 'nombre_personne_minimum') ?>" required>
                <?= field_error($errors, 'nombre_personne_minimum') ?>
            </div>
            <div class="col-sm-4">
                <label for="prix_par_personne" class="form-label">Prix par personne (€) <span aria-hidden="true">*</span></label>
                <input type="number" min="0.01" step="0.01" class="form-control<?= aria_invalid($errors, 'prix_par_personne') ?>" id="prix_par_personne" name="prix_par_personne" value="<?= old($data, 'prix_par_personne') ?>" required>
                <?= field_error($errors, 'prix_par_personne') ?>
            </div>
            <div class="col-sm-4">
                <label for="quantite_restante" class="form-label">Stock (commandes possibles) <span aria-hidden="true">*</span></label>
                <input type="number" min="0" class="form-control<?= aria_invalid($errors, 'quantite_restante') ?>" id="quantite_restante" name="quantite_restante" value="<?= old($data, 'quantite_restante') ?>" required>
                <?= field_error($errors, 'quantite_restante') ?>
            </div>
            <div class="col-12">
                <label for="conditions" class="form-label">Conditions (délai de commande, conservation...) <span aria-hidden="true">*</span></label>
                <textarea class="form-control<?= aria_invalid($errors, 'conditions') ?>" id="conditions" name="conditions" rows="2" required><?= old($data, 'conditions') ?></textarea>
                <?= field_error($errors, 'conditions') ?>
            </div>
        </div>

        <fieldset class="mt-4">
            <legend class="h5">Composition <span aria-hidden="true">*</span></legend>
            <?= field_error($errors, 'plats') ?>
            <div class="row">
                <?php foreach ($libelles as $type => $libelle): ?>
                    <div class="col-md-4">
                        <p class="fw-semibold mb-1"><?= e($libelle) ?></p>
                        <?php foreach ($plats as $p): if ($p['type_plat'] !== $type) continue; ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="plats[]" id="plat-<?= (int) $p['plat_id'] ?>" value="<?= (int) $p['plat_id'] ?>"
                                    <?= in_array((int) $p['plat_id'], array_map('intval', $platsChoisis), true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="plat-<?= (int) $p['plat_id'] ?>"><?= e($p['titre_plat']) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="small mt-2"><a href="/employe/plats">Créer un nouveau plat</a></p>
        </fieldset>

        <fieldset class="mt-3">
            <legend class="h5">Ajouter une photo</legend>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="image" class="form-label">Image (JPG, PNG ou WebP, 2 Mo maximum)</label>
                    <input type="file" class="form-control<?= aria_invalid($errors, 'image') ?>" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                    <?= field_error($errors, 'image') ?>
                </div>
                <div class="col-md-6">
                    <label for="texte_alt" class="form-label">Description de l'image (pour les personnes malvoyantes)</label>
                    <input type="text" class="form-control<?= aria_invalid($errors, 'texte_alt') ?>" id="texte_alt" name="texte_alt" maxlength="255" value="<?= old($_POST, 'texte_alt') ?>">
                    <?= field_error($errors, 'texte_alt') ?>
                </div>
            </div>
        </fieldset>

        <button type="submit" class="btn btn-bordeaux mt-4">Enregistrer le menu</button>
    </form>

    <?php if ($menu && $menu['images']): ?>
        <section aria-labelledby="titre-galerie">
            <h3 id="titre-galerie" class="h5">Galerie actuelle</h3>
            <div class="row g-3">
                <?php foreach ($menu['images'] as $img): ?>
                    <div class="col-6 col-md-3">
                        <img src="/<?= e($img['chemin']) ?>" alt="<?= e($img['texte_alt']) ?>" class="img-fluid rounded mb-2">
                        <form method="post" action="/employe/menu?id=<?= (int) $menu['menu_id'] ?>" class="js-confirmer" data-confirmation="Supprimer cette image ?">
                            <?= App\Csrf::field() ?>
                            <input type="hidden" name="action" value="supprimer_image">
                            <input type="hidden" name="image_id" value="<?= (int) $img['image_id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer<span class="visually-hidden"> l'image : <?= e($img['texte_alt']) ?></span></button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>
<script src="/assets/js/confirmation.js" defer></script>
