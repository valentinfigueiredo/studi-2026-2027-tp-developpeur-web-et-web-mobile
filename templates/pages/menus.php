<div class="container py-4">
    <h1 class="mb-4">Nos menus</h1>

    <div class="row g-4">
        <aside class="col-lg-3">
            <form id="filtres" class="card card-body filtres" action="/menus" method="get" aria-labelledby="titre-filtres">
                <h2 id="titre-filtres" class="h5">Filtrer les menus</h2>

                <fieldset class="mb-3">
                    <legend class="form-label fs-6">Fourchette de prix (€)</legend>
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="prix_min" class="form-label small">Prix minimum</label>
                            <input type="number" min="0" step="10" class="form-control" id="prix_min" name="prix_min" value="<?= old($filtres, 'prix_min') ?>">
                        </div>
                        <div class="col-6">
                            <label for="prix_max" class="form-label small">Prix maximum</label>
                            <input type="number" min="0" step="10" class="form-control" id="prix_max" name="prix_max" value="<?= old($filtres, 'prix_max') ?>">
                        </div>
                    </div>
                </fieldset>

                <div class="mb-3">
                    <label for="theme" class="form-label">Thème</label>
                    <select class="form-select" id="theme" name="theme">
                        <option value="">Tous les thèmes</option>
                        <?php foreach ($themes as $t): ?>
                            <option value="<?= (int) $t['theme_id'] ?>" <?= (string) $filtres['theme'] === (string) $t['theme_id'] ? 'selected' : '' ?>><?= e($t['libelle']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="regime" class="form-label">Régime</label>
                    <select class="form-select" id="regime" name="regime">
                        <option value="">Tous les régimes</option>
                        <?php foreach ($regimes as $r): ?>
                            <option value="<?= (int) $r['regime_id'] ?>" <?= (string) $filtres['regime'] === (string) $r['regime_id'] ? 'selected' : '' ?>><?= e($r['libelle']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="personnes" class="form-label">Nombre de convives</label>
                    <input type="number" min="1" class="form-control" id="personnes" name="personnes" value="<?= old($filtres, 'personnes') ?>" aria-describedby="aide-personnes">
                    <div id="aide-personnes" class="form-text">Affiche les menus commandables pour ce nombre de personnes.</div>
                </div>

                <!-- Le bouton sert si JavaScript est désactivé ; sinon les résultats se mettent à jour automatiquement -->
                <button type="submit" class="btn btn-bordeaux w-100 js-masquer">Filtrer</button>
                <button type="reset" class="btn btn-link w-100 mt-1" id="reinitialiser">Réinitialiser les filtres</button>
            </form>
        </aside>

        <section class="col-lg-9" aria-labelledby="titre-resultats">
            <h2 id="titre-resultats" class="visually-hidden">Résultats</h2>
            <!-- aria-live : le lecteur d'écran annonce le nombre de résultats après chaque filtrage -->
            <p id="nb-resultats" class="mb-3" aria-live="polite"><?= count($menus) ?> menu<?= count($menus) > 1 ? 's' : '' ?> trouvé<?= count($menus) > 1 ? 's' : '' ?></p>
            <div class="row g-4" id="liste-menus">
                <?php foreach ($menus as $m): ?>
                    <?= render_partial('partials/carte-menu', ['m' => $m]) ?>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</div>
