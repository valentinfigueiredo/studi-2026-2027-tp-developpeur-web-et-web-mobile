<div class="container py-4">
    <h1 class="mb-3">Espace de gestion</h1>
    <?= render_partial('partials/nav-staff') ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0">Menus</h2>
        <a href="/employe/menu" class="btn btn-bordeaux">Ajouter un menu</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <caption class="visually-hidden">Liste des menus</caption>
            <thead><tr>
                <th scope="col">Titre</th><th scope="col">Thème / régime</th><th scope="col">Prix</th>
                <th scope="col">Stock</th><th scope="col">État</th><th scope="col"><span class="visually-hidden">Actions</span></th>
            </tr></thead>
            <tbody>
            <?php foreach ($menus as $m): ?>
                <tr>
                    <th scope="row"><?= e($m['titre']) ?></th>
                    <td><?= e($m['theme']) ?> / <?= e($m['regime']) ?></td>
                    <td><?= money($m['prix_par_personne']) ?> / pers. (min. <?= (int) $m['nombre_personne_minimum'] ?>)</td>
                    <td><?= (int) $m['quantite_restante'] ?></td>
                    <td><?= $m['actif'] ? 'En vente' : 'Archivé' ?></td>
                    <td class="text-nowrap">
                        <a class="btn btn-sm btn-outline-bordeaux" href="/employe/menu?id=<?= (int) $m['menu_id'] ?>">Modifier<span class="visually-hidden"> <?= e($m['titre']) ?></span></a>
                        <form method="post" action="/employe/menus" class="d-inline<?= $m['actif'] ? ' js-confirmer' : '' ?>" data-confirmation="Supprimer le menu « <?= e($m['titre']) ?> » ?">
                            <?= App\Csrf::field() ?>
                            <input type="hidden" name="menu_id" value="<?= (int) $m['menu_id'] ?>">
                            <?php if ($m['actif']): ?>
                                <button type="submit" name="action" value="supprimer" class="btn btn-sm btn-outline-danger">Supprimer<span class="visually-hidden"> <?= e($m['titre']) ?></span></button>
                            <?php else: ?>
                                <button type="submit" name="action" value="reactiver" class="btn btn-sm btn-outline-success">Remettre en vente<span class="visually-hidden"> <?= e($m['titre']) ?></span></button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="/assets/js/confirmation.js" defer></script>
