<?php

use App\Auth;

$libelles = ['entree' => 'Entrées', 'plat' => 'Plats', 'dessert' => 'Desserts'];
$disponible = (int) $menu['quantite_restante'] > 0;
?>
<div class="container py-4">
    <nav aria-label="Fil d'Ariane">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Accueil</a></li>
            <li class="breadcrumb-item"><a href="/menus">Nos menus</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e($menu['titre']) ?></li>
        </ol>
    </nav>

    <div class="row g-5">
        <div class="col-lg-6">
            <?php if ($menu['images']): ?>
                <div id="galerie" class="carousel slide" aria-label="Galerie photos du menu">
                    <div class="carousel-inner rounded">
                        <?php foreach ($menu['images'] as $i => $img): ?>
                            <div class="carousel-item<?= $i === 0 ? ' active' : '' ?>">
                                <img src="/<?= e($img['chemin']) ?>" class="d-block w-100" alt="<?= e($img['texte_alt']) ?>" width="600" height="400">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($menu['images']) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#galerie" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Photo précédente</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#galerie" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Photo suivante</span>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-6">
            <p class="mb-2">
                <span class="badge badge-theme"><?= e($menu['theme']) ?></span>
                <span class="badge badge-regime"><?= e($menu['regime']) ?></span>
            </p>
            <h1><?= e($menu['titre']) ?></h1>
            <p class="lead"><?= e($menu['description']) ?></p>

            <p class="fs-5">
                <span class="prix"><?= money($menu['prix_minimum']) ?></span>
                pour <?= (int) $menu['nombre_personne_minimum'] ?> personnes minimum
                <span class="d-block small text-muted">
                    soit <?= money($menu['prix_par_personne']) ?> par personne.
                    -10 % dès <?= (int) $menu['nombre_personne_minimum'] + 5 ?> personnes.
                </span>
            </p>

            <p>
                <?php if ($disponible): ?>
                    <strong><?= (int) $menu['quantite_restante'] ?></strong> commande<?= $menu['quantite_restante'] > 1 ? 's' : '' ?> encore possible<?= $menu['quantite_restante'] > 1 ? 's' : '' ?> pour ce menu.
                <?php else: ?>
                    <strong class="text-danger">Ce menu n'est plus disponible à la commande.</strong>
                <?php endif; ?>
            </p>

            <!-- Conditions mises en évidence (exigence du client) : encadré + rôle "note" -->
            <div class="alert alert-conditions" role="note" aria-labelledby="titre-conditions">
                <h2 id="titre-conditions" class="h5"><span aria-hidden="true">⚠️</span> Conditions de ce menu</h2>
                <p class="mb-0"><?= nl2br(e($menu['conditions'])) ?></p>
            </div>

            <?php if ($disponible): ?>
                <?php if (Auth::hasRole(Auth::UTILISATEUR)): ?>
                    <a href="/commander?menu=<?= (int) $menu['menu_id'] ?>" class="btn btn-bordeaux btn-lg">Commander ce menu</a>
                <?php elseif (!Auth::check()): ?>
                    <a href="/connexion?redirect=<?= e(urlencode('/commander?menu=' . $menu['menu_id'])) ?>" class="btn btn-bordeaux btn-lg">Commander ce menu</a>
                    <p class="small mt-2">Vous devrez vous <a href="/connexion?redirect=<?= e(urlencode('/commander?menu=' . $menu['menu_id'])) ?>">connecter</a>
                        ou <a href="/inscription?redirect=<?= e(urlencode('/commander?menu=' . $menu['menu_id'])) ?>">créer un compte</a> pour commander.</p>
                <?php else: ?>
                    <p class="small text-muted">La commande est réservée aux comptes clients.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <section class="mt-5" aria-labelledby="titre-composition">
        <h2 id="titre-composition">Composition du menu</h2>
        <div class="row g-4 mt-1">
            <?php foreach ($plats as $type => $liste): if (!$liste) continue; ?>
                <div class="col-md-4">
                    <h3 class="h5 titre-categorie"><?= e($libelles[$type]) ?></h3>
                    <ul class="list-unstyled">
                        <?php foreach ($liste as $p): ?>
                            <li class="mb-3">
                                <strong><?= e($p['titre_plat']) ?></strong>
                                <?php if ($p['description']): ?><br><span><?= e($p['description']) ?></span><?php endif; ?>
                                <br><span class="small allergenes">
                                    Allergènes : <?= $p['allergenes'] ? e($p['allergenes']) : 'aucun allergène majeur' ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>
