<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <h1 class="mb-3">Connexion</h1>
            <?php if ($erreur): ?>
                <div class="alert alert-danger" role="alert"><?= e($erreur) ?></div>
            <?php endif; ?>
            <form method="post" action="/connexion" class="card card-body">
                <?= App\Csrf::field() ?>
                <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
                <div class="mb-3">
                    <label for="email" class="form-label">Adresse email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= e($email) ?>" required autocomplete="username">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-bordeaux">Se connecter</button>
                <p class="mt-3 mb-1"><a href="/mot-de-passe-oublie">Mot de passe oublié ?</a></p>
                <p class="mb-0">Pas encore de compte ? <a href="/inscription<?= $redirect ? '?redirect=' . e(urlencode($redirect)) : '' ?>">Créez-en un</a></p>
            </form>
        </div>
    </div>
</div>
