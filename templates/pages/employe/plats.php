<?php $types = ['entree' => 'Entrée', 'plat' => 'Plat', 'dessert' => 'Dessert']; ?>
<div class="container py-4">
    <h1 class="mb-3">Espace de gestion</h1>
    <?= render_partial('partials/nav-staff') ?>
    <div class="row g-4">
        <div class="col-lg-7">
            <h2 class="h4">Plats</h2>
            <div class="table-responsive">
                <table class="table align-middle">
                    <caption class="visually-hidden">Liste des plats</caption>
                    <thead><tr><th scope="col">Plat</th><th scope="col">Type</th><th scope="col">Allergènes</th><th scope="col">Menus</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody>
                    <?php foreach ($plats as $p): ?>
                        <tr>
                            <th scope="row"><?= e($p['titre_plat']) ?></th>
                            <td><?= e($types[$p['type_plat']]) ?></td>
                            <td class="small"><?= e($p['allergenes'] ?? '-') ?></td>
                            <td><?= (int) $p['nb_menus'] ?></td>
                            <td class="text-nowrap">
                                <a class="btn btn-sm btn-outline-bordeaux" href="/employe/plats?id=<?= (int) $p['plat_id'] ?>">Modifier<span class="visually-hidden"> <?= e($p['titre_plat']) ?></span></a>
                                <form method="post" action="/employe/plats" class="d-inline js-confirmer" data-confirmation="Supprimer le plat « <?= e($p['titre_plat']) ?> » ? Il sera retiré de <?= (int) $p['nb_menus'] ?> menu(s).">
                                    <?= App\Csrf::field() ?>
                                    <input type="hidden" name="action" value="supprimer">
                                    <input type="hidden" name="plat_id" value="<?= (int) $p['plat_id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer<span class="visually-hidden"> <?= e($p['titre_plat']) ?></span></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-lg-5">
            <form method="post" action="/employe/plats" class="card card-body" novalidate>
                <h2 class="h5"><?= $edition['plat_id'] ? 'Modifier le plat' : 'Ajouter un plat' ?></h2>
                <?= App\Csrf::field() ?>
                <input type="hidden" name="action" value="enregistrer">
                <input type="hidden" name="plat_id" value="<?= (int) $edition['plat_id'] ?>">
                <div class="mb-3">
                    <label for="titre_plat" class="form-label">Nom du plat <span aria-hidden="true">*</span></label>
                    <input type="text" class="form-control<?= aria_invalid($errors, 'titre_plat') ?>" id="titre_plat" name="titre_plat" value="<?= old($edition, 'titre_plat') ?>" required maxlength="150">
                    <?= field_error($errors, 'titre_plat') ?>
                </div>
                <div class="mb-3">
                    <label for="type_plat" class="form-label">Type <span aria-hidden="true">*</span></label>
                    <select class="form-select<?= aria_invalid($errors, 'type_plat') ?>" id="type_plat" name="type_plat" required>
                        <option value="">Choisir</option>
                        <?php foreach ($types as $cle => $libelle): ?>
                            <option value="<?= $cle ?>" <?= $edition['type_plat'] === $cle ? 'selected' : '' ?>><?= $libelle ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?= field_error($errors, 'type_plat') ?>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="2"><?= old($edition, 'description') ?></textarea>
                </div>
                <fieldset class="mb-3">
                    <legend class="form-label fs-6">Allergènes</legend>
                    <div class="row">
                        <?php foreach ($allergenes as $a): ?>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="allergenes[]" id="all-<?= (int) $a['allergene_id'] ?>" value="<?= (int) $a['allergene_id'] ?>"
                                        <?= in_array((int) $a['allergene_id'], $edition['allergenes'] ?? [], true) ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="all-<?= (int) $a['allergene_id'] ?>"><?= e($a['libelle']) ?></label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
                <button type="submit" class="btn btn-bordeaux">Enregistrer</button>
                <?php if ($edition['plat_id']): ?><a href="/employe/plats" class="btn btn-link">Annuler la modification</a><?php endif; ?>
            </form>
        </div>
    </div>
</div>
<script src="/assets/js/confirmation.js" defer></script>
