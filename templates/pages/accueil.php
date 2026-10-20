<section class="hero" aria-labelledby="titre-accueil">
    <div class="container py-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <h1 id="titre-accueil" class="display-5">Vos repas de fête, préparés avec passion depuis 25 ans</h1>
                <p class="lead mt-3">
                    Noël, Pâques, anniversaire ou repas d'entreprise : Julie et José cuisinent pour vous
                    et livrent à Bordeaux et dans toute la métropole.
                </p>
                <a href="/menus" class="btn btn-bordeaux btn-lg mt-2">Découvrir nos menus</a>
            </div>
            <div class="col-lg-5">
                <img src="/assets/img/hero.svg" alt="" class="img-fluid">
            </div>
        </div>
    </div>
</section>

<section class="container py-5" aria-labelledby="titre-entreprise">
    <h2 id="titre-entreprise" class="text-center mb-4">Qui sommes-nous ?</h2>
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            <p>
                Depuis 25 ans, <strong>Vite &amp; Gourmand</strong> régale les Bordelais. Notre carte évolue au fil
                des saisons et des fêtes, avec des produits frais sélectionnés chez les producteurs de Nouvelle-Aquitaine.
                Chaque menu est cuisiné dans notre atelier du centre de Bordeaux, puis livré chez vous à l'heure prévue.
            </p>
        </div>
    </div>
</section>

<section class="bg-creme-fonce py-5" aria-labelledby="titre-pro">
    <div class="container">
        <h2 id="titre-pro" class="text-center mb-4">Une équipe de professionnels</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 text-center card-atout">
                    <div class="card-body">
                        <p class="atout-chiffre" aria-hidden="true">25</p>
                        <h3 class="h5">ans d'expérience</h3>
                        <p class="mb-0">Un savoir-faire reconnu pour les repas de fête comme pour les grands événements.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center card-atout">
                    <div class="card-body">
                        <p class="atout-chiffre" aria-hidden="true">100 %</p>
                        <h3 class="h5">fait maison</h3>
                        <p class="mb-0">Des plats cuisinés sur place, avec des allergènes clairement indiqués.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center card-atout">
                    <div class="card-body">
                        <p class="atout-chiffre" aria-hidden="true">7j/7</p>
                        <h3 class="h5">à votre écoute</h3>
                        <p class="mb-0">Un suivi de commande en ligne, de la validation jusqu'à la livraison.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container py-5" aria-labelledby="titre-avis">
    <h2 id="titre-avis" class="text-center mb-2">Ils nous ont fait confiance</h2>
    <?php if ($noteMoyenne): ?>
        <p class="text-center mb-4">Note moyenne : <strong><?= e(str_replace('.', ',', $noteMoyenne)) ?> / 5</strong></p>
    <?php endif; ?>
    <?php if (!$avis): ?>
        <p class="text-center">Aucun avis pour le moment.</p>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($avis as $a): ?>
                <div class="col-md-6 col-lg-4">
                    <figure class="card h-100 avis mb-0">
                        <div class="card-body">
                            <p class="etoiles mb-2" aria-label="Note : <?= (int) $a['note'] ?> sur 5">
                                <span aria-hidden="true"><?= etoiles((int) $a['note']) ?></span>
                            </p>
                            <blockquote class="mb-3"><p class="mb-0"><?= e($a['commentaire']) ?></p></blockquote>
                            <figcaption class="small text-muted">
                                <?= e($a['prenom'] . ' ' . $a['initiale'] . '.') ?>, menu « <?= e($a['menu']) ?> »
                            </figcaption>
                        </div>
                    </figure>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
