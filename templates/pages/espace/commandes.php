<div class="container py-4">
    <h1 class="mb-4">Mes commandes</h1>
    <?php if (!$commandes): ?>
        <p>Vous n'avez pas encore passé de commande.</p>
        <a href="/menus" class="btn btn-bordeaux">Découvrir nos menus</a>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <caption class="visually-hidden">Liste de vos commandes, de la plus récente à la plus ancienne</caption>
                <thead>
                <tr>
                    <th scope="col">N° de commande</th>
                    <th scope="col">Menu</th>
                    <th scope="col">Prestation</th>
                    <th scope="col">Personnes</th>
                    <th scope="col">Total</th>
                    <th scope="col">Statut</th>
                    <th scope="col"><span class="visually-hidden">Actions</span></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($commandes as $c): ?>
                    <tr>
                        <th scope="row"><?= e($c['numero_commande']) ?></th>
                        <td><?= e($c['menu_titre']) ?></td>
                        <td><?= e(date_fr($c['date_prestation'])) ?></td>
                        <td><?= (int) $c['nombre_personne'] ?></td>
                        <td><?= money($c['prix_total']) ?></td>
                        <td><?= render_partial('partials/badge-statut', ['statut' => $c['statut']]) ?></td>
                        <td>
                            <a href="/espace/commande?id=<?= (int) $c['commande_id'] ?>" class="btn btn-sm btn-outline-bordeaux">
                                Détail<span class="visually-hidden"> de la commande <?= e($c['numero_commande']) ?></span>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
