<?php use App\CommandeService; ?>
<div class="container py-4">
    <h1 class="mb-3">Espace de gestion</h1>
    <?= render_partial('partials/nav-staff') ?>

    <h2 class="h4">Commandes</h2>
    <form method="get" action="/employe" class="row g-2 align-items-end mb-4" role="search" aria-label="Filtrer les commandes">
        <div class="col-sm-4">
            <label for="statut" class="form-label">Statut</label>
            <select class="form-select" id="statut" name="statut">
                <option value="">Tous les statuts</option>
                <?php foreach (CommandeService::STATUTS as $cle => $libelle): ?>
                    <option value="<?= e($cle) ?>" <?= $statut === $cle ? 'selected' : '' ?>><?= e($libelle) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-5">
            <label for="client" class="form-label">Client (nom, prénom ou email)</label>
            <input type="search" class="form-control" id="client" name="client" value="<?= e($client) ?>">
        </div>
        <div class="col-sm-3 d-flex gap-2">
            <button type="submit" class="btn btn-bordeaux">Filtrer</button>
            <a href="/employe" class="btn btn-link">Réinitialiser</a>
        </div>
    </form>

    <p><?= count($commandes) ?> commande<?= count($commandes) > 1 ? 's' : '' ?></p>
    <div class="table-responsive">
        <table class="table align-middle">
            <caption class="visually-hidden">Commandes triées par date de prestation</caption>
            <thead>
            <tr>
                <th scope="col">N°</th><th scope="col">Client</th><th scope="col">Menu</th>
                <th scope="col">Prestation</th><th scope="col">Pers.</th><th scope="col">Total</th>
                <th scope="col">Statut</th><th scope="col"><span class="visually-hidden">Actions</span></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($commandes as $c): ?>
                <tr>
                    <th scope="row"><?= e($c['numero_commande']) ?></th>
                    <td><?= e($c['prenom'] . ' ' . $c['nom']) ?></td>
                    <td><?= e($c['menu_titre']) ?></td>
                    <td><?= e(date_fr($c['date_prestation'])) ?> <?= e(heure_fr($c['heure_livraison'])) ?></td>
                    <td><?= (int) $c['nombre_personne'] ?></td>
                    <td><?= money($c['prix_total']) ?></td>
                    <td><?= render_partial('partials/badge-statut', ['statut' => $c['statut']]) ?></td>
                    <td><a class="btn btn-sm btn-outline-bordeaux" href="/employe/commande?id=<?= (int) $c['commande_id'] ?>">Gérer<span class="visually-hidden"> la commande <?= e($c['numero_commande']) ?></span></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
