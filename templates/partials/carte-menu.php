<?php /** @var array $m */ ?>
<div class="col-md-6 col-lg-4">
    <article class="card h-100 carte-menu">
        <?php if ($m['image']): ?>
            <img src="/<?= e($m['image']) ?>" class="card-img-top" alt="<?= e($m['image_alt']) ?>" loading="lazy" width="600" height="400">
        <?php endif; ?>
        <div class="card-body d-flex flex-column">
            <p class="mb-2">
                <span class="badge badge-theme"><?= e($m['theme']) ?></span>
                <span class="badge badge-regime"><?= e($m['regime']) ?></span>
            </p>
            <h2 class="h4 card-title"><?= e($m['titre']) ?></h2>
            <p class="card-text"><?= e($m['description']) ?></p>
            <p class="mb-3 mt-auto">
                À partir de <strong><?= (int) $m['nombre_personne_minimum'] ?> personnes</strong><br>
                <span class="prix"><?= money($m['prix_minimum']) ?></span>
                <span class="small">pour <?= (int) $m['nombre_personne_minimum'] ?> pers.</span>
            </p>
            <?php if ((int) $m['quantite_restante'] < 1): ?>
                <p class="text-danger fw-semibold mb-2">Victime de son succès : plus disponible</p>
            <?php endif; ?>
            <a href="/menu?id=<?= (int) $m['menu_id'] ?>" class="btn btn-bordeaux">
                Voir le détail<span class="visually-hidden"> du menu <?= e($m['titre']) ?></span>
            </a>
        </div>
    </article>
</div>
