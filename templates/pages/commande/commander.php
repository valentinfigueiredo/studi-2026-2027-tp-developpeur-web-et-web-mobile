<div class="container py-4">
    <h1 class="mb-3">Commander un menu</h1>
    <p>Les champs marqués d'un astérisque (<span aria-hidden="true">*</span><span class="visually-hidden">astérisque</span>) sont obligatoires.</p>

    <?php if ($errors): ?>
        <div class="alert alert-danger" role="alert">Le formulaire contient des erreurs. Corrigez les champs signalés.</div>
    <?php endif; ?>

    <?php if (!$menus): ?>
        <div class="alert alert-warning">Aucun menu n'est disponible à la commande pour le moment.</div>
    <?php else: ?>
    <form method="post" action="/commander" id="form-commande" novalidate>
        <?= App\Csrf::field() ?>
        <div class="row">
            <div class="col-lg-8">
                <fieldset class="mb-4">
                    <legend class="h5">1. Vos coordonnées</legend>
                    <p class="mb-1"><?= e($client['prenom'] . ' ' . $client['nom']) ?></p>
                    <p class="mb-1"><?= e($client['email']) ?> - <?= e($client['telephone']) ?></p>
                    <p class="small"><a href="/espace/profil">Modifier mes informations</a></p>
                    <label for="menu_id" class="form-label">Menu choisi <span aria-hidden="true">*</span></label>
                    <select class="form-select<?= aria_invalid($errors, 'menu_id') ?>" id="menu_id" name="menu_id" required>
                        <option value="">Choisissez un menu</option>
                        <?php foreach ($menus as $m): ?>
                            <option value="<?= (int) $m['menu_id'] ?>"
                                    data-prix="<?= e($m['prix_par_personne']) ?>"
                                    data-min="<?= (int) $m['nombre_personne_minimum'] ?>"
                                    <?= $menuId === (int) $m['menu_id'] ? 'selected' : '' ?>>
                                <?= e($m['titre']) ?> - <?= money($m['prix_minimum']) ?> pour <?= (int) $m['nombre_personne_minimum'] ?> pers.
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?= field_error($errors, 'menu_id') ?>
                </fieldset>

                <?= render_partial('partials/form-prestation', ['data' => $data, 'errors' => $errors]) ?>

                <button type="submit" class="btn btn-bordeaux btn-lg w-100">Valider ma commande</button>
                <p class="small mt-2">En validant, vous acceptez nos <a href="/cgv">conditions générales de vente</a>.</p>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>
