<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <h1 class="mb-3">Créer un compte</h1>
            <p>Les champs marqués d'un astérisque (<span aria-hidden="true">*</span><span class="visually-hidden">astérisque</span>) sont obligatoires.</p>

            <?php if ($errors): ?>
                <div class="alert alert-danger" role="alert" tabindex="-1" id="resume-erreurs">
                    Le formulaire contient <?= count($errors) ?> erreur<?= count($errors) > 1 ? 's' : '' ?>. Corrigez les champs signalés.
                </div>
            <?php endif; ?>

            <form method="post" action="/inscription" class="card card-body" novalidate>
                <?= App\Csrf::field() ?>
                <input type="hidden" name="redirect" value="<?= e($redirect) ?>">

                <fieldset>
                    <legend class="h5">Vos informations</legend>
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label for="prenom" class="form-label">Prénom <span aria-hidden="true">*</span></label>
                            <input type="text" class="form-control<?= aria_invalid($errors, 'prenom') ?>" id="prenom" name="prenom" value="<?= old($data, 'prenom') ?>" required autocomplete="given-name">
                            <?= field_error($errors, 'prenom') ?>
                        </div>
                        <div class="col-sm-6">
                            <label for="nom" class="form-label">Nom <span aria-hidden="true">*</span></label>
                            <input type="text" class="form-control<?= aria_invalid($errors, 'nom') ?>" id="nom" name="nom" value="<?= old($data, 'nom') ?>" required autocomplete="family-name">
                            <?= field_error($errors, 'nom') ?>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-sm-7">
                            <label for="email" class="form-label">Adresse email <span aria-hidden="true">*</span></label>
                            <input type="email" class="form-control<?= aria_invalid($errors, 'email') ?>" id="email" name="email" value="<?= old($data, 'email') ?>" required autocomplete="email">
                            <?= field_error($errors, 'email') ?>
                        </div>
                        <div class="col-sm-5">
                            <label for="telephone" class="form-label">Téléphone mobile <span aria-hidden="true">*</span></label>
                            <input type="tel" class="form-control<?= aria_invalid($errors, 'telephone') ?>" id="telephone" name="telephone" value="<?= old($data, 'telephone') ?>" required autocomplete="tel" placeholder="06 12 34 56 78">
                            <?= field_error($errors, 'telephone') ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="adresse_postale" class="form-label">Adresse postale <span aria-hidden="true">*</span></label>
                        <input type="text" class="form-control<?= aria_invalid($errors, 'adresse_postale') ?>" id="adresse_postale" name="adresse_postale" value="<?= old($data, 'adresse_postale') ?>" required autocomplete="street-address">
                        <?= field_error($errors, 'adresse_postale') ?>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-sm-4">
                            <label for="code_postal" class="form-label">Code postal <span aria-hidden="true">*</span></label>
                            <input type="text" inputmode="numeric" class="form-control<?= aria_invalid($errors, 'code_postal') ?>" id="code_postal" name="code_postal" value="<?= old($data, 'code_postal') ?>" required autocomplete="postal-code">
                            <?= field_error($errors, 'code_postal') ?>
                        </div>
                        <div class="col-sm-8">
                            <label for="ville" class="form-label">Ville <span aria-hidden="true">*</span></label>
                            <input type="text" class="form-control<?= aria_invalid($errors, 'ville') ?>" id="ville" name="ville" value="<?= old($data, 'ville') ?>" required autocomplete="address-level2">
                            <?= field_error($errors, 'ville') ?>
                        </div>
                        <div class="col-sm-6">
                            <label for="pays" class="form-label">Pays <span aria-hidden="true">*</span></label>
                            <input type="text" class="form-control<?= aria_invalid($errors, 'pays') ?>" id="pays" name="pays" value="<?= old($data, 'pays', 'France') ?>" required autocomplete="country-name">
                            <?= field_error($errors, 'pays') ?>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="h5">Sécurité</legend>
                    <?= render_partial('partials/champs-mot-de-passe', ['errors' => $errors]) ?>
                </fieldset>

                <div class="form-check mb-3">
                    <input class="form-check-input<?= aria_invalid($errors, 'rgpd') ?>" type="checkbox" id="rgpd" name="rgpd" value="1" required>
                    <label class="form-check-label" for="rgpd">
                        J'accepte que mes données soient utilisées pour gérer mes commandes, conformément à la
                        <a href="/mentions-legales#donnees">politique de confidentialité</a>. <span aria-hidden="true">*</span>
                    </label>
                    <?= field_error($errors, 'rgpd') ?>
                </div>

                <button type="submit" class="btn btn-bordeaux">Créer mon compte</button>
                <p class="mt-3 mb-0">Déjà inscrit ? <a href="/connexion<?= $redirect !== '/espace' ? '?redirect=' . e(urlencode($redirect)) : '' ?>">Connectez-vous</a></p>
            </form>
        </div>
    </div>
</div>
<script src="/assets/js/password.js" defer></script>
