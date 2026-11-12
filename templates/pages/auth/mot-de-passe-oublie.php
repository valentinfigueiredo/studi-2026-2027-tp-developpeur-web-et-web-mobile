<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <h1 class="mb-3">Mot de passe oublié</h1>
            <?php if ($envoye): ?>
                <div class="alert alert-success" role="status">
                    Si un compte est associé à cette adresse, vous allez recevoir un email contenant un lien
                    pour choisir un nouveau mot de passe. Ce lien est valable 1 heure.
                </div>
                <a href="/connexion">Retour à la connexion</a>
            <?php else: ?>
                <p>Indiquez l'adresse email de votre compte : nous vous enverrons un lien de réinitialisation.</p>
                <form method="post" action="/mot-de-passe-oublie" class="card card-body">
                    <?= App\Csrf::field() ?>
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse email</label>
                        <input type="email" class="form-control" id="email" name="email" required autocomplete="email">
                    </div>
                    <button type="submit" class="btn btn-bordeaux">Envoyer le lien</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
