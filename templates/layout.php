<?php

use App\Auth;
use App\Csrf;

/** @var string $contenu */
$user = Auth::user();
$titrePage = isset($title) ? $title . ' | Vite & Gourmand' : 'Vite & Gourmand - Traiteur à Bordeaux depuis 25 ans';
$description = $metaDescription ?? 'Vite & Gourmand, traiteur bordelais depuis 25 ans : menus de fête, Noël, Pâques et événements, livrés chez vous à Bordeaux et alentours.';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titrePage) ?></title>
    <meta name="description" content="<?= e($description) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="/assets/css/style.css" rel="stylesheet">
    <link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
</head>
<body>
<!-- Lien d'évitement (RGAA 12.7) : permet aux utilisateurs au clavier d'aller directement au contenu -->
<a class="visually-hidden-focusable skip-link" href="#contenu">Aller au contenu principal</a>

<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-bordeaux" aria-label="Menu principal">
        <div class="container">
            <a class="navbar-brand" href="/">
                <img src="/assets/img/logo.svg" alt="" width="36" height="36" class="me-2">Vite &amp; Gourmand
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navPrincipale"
                    aria-controls="navPrincipale" aria-expanded="false" aria-label="Afficher le menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navPrincipale">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link<?= nav_active('/') ?>" href="/">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link<?= nav_active('/menus') ?>" href="/menus">Nos menus</a></li>
                    <li class="nav-item"><a class="nav-link<?= nav_active('/contact') ?>" href="/contact">Contact</a></li>
                </ul>
                <ul class="navbar-nav">
                    <?php if ($user === null): ?>
                        <li class="nav-item"><a class="nav-link<?= nav_active('/connexion') ?>" href="/connexion">Connexion</a></li>
                        <li class="nav-item"><a class="btn btn-or ms-lg-2" href="/inscription">Créer un compte</a></li>
                    <?php else: ?>
                        <?php if ($user['role'] === Auth::UTILISATEUR): ?>
                            <li class="nav-item"><a class="nav-link<?= nav_active('/espace', true) ?>" href="/espace">Mes commandes</a></li>
                        <?php endif; ?>
                        <?php if (Auth::isStaff()): ?>
                            <li class="nav-item"><a class="nav-link<?= nav_active('/employe') ?>" href="/employe">Espace employé</a></li>
                        <?php endif; ?>
                        <?php if ($user['role'] === Auth::ADMIN): ?>
                            <li class="nav-item"><a class="nav-link<?= nav_active('/admin/statistiques') ?>" href="/admin/statistiques">Administration</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link<?= nav_active('/espace/profil') ?>" href="/espace/profil">Mon profil</a></li>
                        <li class="nav-item">
                            <!-- Déconnexion en POST + jeton CSRF : un lien piégé ne peut pas déconnecter l'utilisateur -->
                            <form action="/deconnexion" method="post" class="d-inline">
                                <?= Csrf::field() ?>
                                <button type="submit" class="btn btn-outline-light ms-lg-2">Déconnexion</button>
                            </form>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<main id="contenu" tabindex="-1">
    <?php $messages = flashes(); if ($messages): ?>
        <div class="container mt-3">
            <?php foreach ($messages as $m): ?>
                <div class="alert alert-<?= e($m['type']) ?> alert-dismissible fade show" role="alert">
                    <?= e($m['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer le message"></button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?= $contenu ?>
</main>

<footer class="footer mt-5">
    <div class="container py-4">
        <div class="row g-4">
            <div class="col-md-4">
                <h2 class="h5">Vite &amp; Gourmand</h2>
                <p class="mb-1">Traiteur à Bordeaux depuis 25 ans.</p>
                <p class="mb-0">12 rue Sainte-Catherine, 33000 Bordeaux</p>
            </div>
            <div class="col-md-4">
                <h2 class="h5">Nos horaires</h2>
                <ul class="list-unstyled horaires mb-0">
                    <?php $horaires = horaires(); if (!$horaires): ?><li>Horaires momentanément indisponibles</li><?php endif; ?>
                    <?php foreach ($horaires as $h): ?>
                        <li>
                            <span class="jour"><?= e(ucfirst($h['jour'])) ?></span>
                            <span><?= $h['ferme'] ? 'Fermé' : e(heure_fr($h['heure_ouverture']) . ' - ' . heure_fr($h['heure_fermeture'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="col-md-4">
                <h2 class="h5">Informations</h2>
                <ul class="list-unstyled mb-0">
                    <li><a href="/mentions-legales">Mentions légales</a></li>
                    <li><a href="/cgv">Conditions générales de vente</a></li>
                    <li><a href="/contact">Nous contacter</a></li>
                </ul>
            </div>
        </div>
        <p class="text-center small mt-4 mb-0">&copy; <?= date('Y') ?> Vite &amp; Gourmand - Site réalisé par FastDev</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<?php foreach ($scripts ?? [] as $script): ?>
    <script src="/assets/js/<?= e($script) ?>.js" defer></script>
<?php endforeach; ?>
</body>
</html>
