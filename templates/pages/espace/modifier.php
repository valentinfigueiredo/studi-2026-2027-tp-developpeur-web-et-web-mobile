<div class="container py-4">
    <p><a href="/espace/commande?id=<?= (int) $c['commande_id'] ?>">&larr; Retour à la commande</a></p>
    <h1 class="mb-3">Modifier la commande <?= e($c['numero_commande']) ?></h1>
    <?php if ($errors): ?>
        <div class="alert alert-danger" role="alert">Le formulaire contient des erreurs. Corrigez les champs signalés.</div>
    <?php endif; ?>
    <form method="post" action="/espace/commande/modifier" novalidate>
        <?= App\Csrf::field() ?>
        <input type="hidden" name="id" value="<?= (int) $c['commande_id'] ?>">
        <!-- Menu non modifiable : simple champ caché qui porte les données de prix pour commande.js -->
        <input type="hidden" id="menu_id" data-prix="<?= e($c['prix_minimum']) ?>" data-min="<?= (int) $c['nombre_personne_minimum'] ?>">
        <fieldset class="mb-4">
            <legend class="h5">1. Menu</legend>
            <p class="mb-0"><strong><?= e($c['menu_titre']) ?></strong> <span class="small text-muted">(le menu ne peut pas être modifié)</span></p>
        </fieldset>
        <div class="row">
            <div class="col-lg-8">
                <?= render_partial('partials/form-prestation', ['data' => $data, 'errors' => $errors]) ?>
                <button type="submit" class="btn btn-bordeaux btn-lg">Enregistrer les modifications</button>
            </div>
        </div>
    </form>
</div>
