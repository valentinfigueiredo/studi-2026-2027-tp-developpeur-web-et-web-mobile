<div class="container py-5 text-center">
    <p class="atout-chiffre" aria-hidden="true"><?= (int) $code ?></p>
    <h1><?= e($title) ?></h1>
    <?php if ($message): ?><p><?= e($message) ?></p><?php endif; ?>
    <a href="/" class="btn btn-bordeaux mt-3">Retour à l'accueil</a>
</div>
