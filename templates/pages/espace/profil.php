<div class="container py-4">
    <h1 class="mb-4">Mon profil</h1>
    <div class="row g-4">
        <div class="col-lg-7">
            <form method="post" action="/espace/profil" class="card card-body" novalidate>
                <?= App\Csrf::field() ?>
                <input type="hidden" name="action" value="infos">
                <h2 class="h5">Mes informations personnelles</h2>
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label for="prenom" class="form-label">Prénom</label>
                        <input type="text" class="form-control<?= aria_invalid($errors, 'prenom') ?>" id="prenom" name="prenom" value="<?= old($data, 'prenom') ?>" required autocomplete="given-name">
                        <?= field_error($errors, 'prenom') ?>
                    </div>
                    <div class="col-sm-6">
                        <label for="nom" class="form-label">Nom</label>
                        <input type="text" class="form-control<?= aria_invalid($errors, 'nom') ?>" id="nom" name="nom" value="<?= old($data, 'nom') ?>" required autocomplete="family-name">
                        <?= field_error($errors, 'nom') ?>
                    </div>
                    <div class="col-sm-7">
                        <label for="email" class="form-label">Email (identifiant de connexion)</label>
                        <input type="email" class="form-control<?= aria_invalid($errors, 'email') ?>" id="email" name="email" value="<?= old($data, 'email') ?>" required autocomplete="email">
                        <?= field_error($errors, 'email') ?>
                    </div>
                    <div class="col-sm-5">
                        <label for="telephone" class="form-label">Téléphone mobile</label>
                        <input type="tel" class="form-control<?= aria_invalid($errors, 'telephone') ?>" id="telephone" name="telephone" value="<?= old($data, 'telephone') ?>" autocomplete="tel">
                        <?= field_error($errors, 'telephone') ?>
                    </div>
                    <?php if ($estClient): ?>
                        <div class="col-12">
                            <label for="adresse_postale" class="form-label">Adresse postale</label>
                            <input type="text" class="form-control<?= aria_invalid($errors, 'adresse_postale') ?>" id="adresse_postale" name="adresse_postale" value="<?= old($data, 'adresse_postale') ?>" autocomplete="street-address">
                            <?= field_error($errors, 'adresse_postale') ?>
                        </div>
                        <div class="col-sm-4">
                            <label for="code_postal" class="form-label">Code postal</label>
                            <input type="text" inputmode="numeric" class="form-control<?= aria_invalid($errors, 'code_postal') ?>" id="code_postal" name="code_postal" value="<?= old($data, 'code_postal') ?>" autocomplete="postal-code">
                            <?= field_error($errors, 'code_postal') ?>
                        </div>
                        <div class="col-sm-8">
                            <label for="ville" class="form-label">Ville</label>
                            <input type="text" class="form-control<?= aria_invalid($errors, 'ville') ?>" id="ville" name="ville" value="<?= old($data, 'ville') ?>" autocomplete="address-level2">
                            <?= field_error($errors, 'ville') ?>
                        </div>
                        <div class="col-sm-6">
                            <label for="pays" class="form-label">Pays</label>
                            <input type="text" class="form-control<?= aria_invalid($errors, 'pays') ?>" id="pays" name="pays" value="<?= old($data, 'pays', 'France') ?>" autocomplete="country-name">
                            <?= field_error($errors, 'pays') ?>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-bordeaux">Enregistrer</button>
            </form>
        </div>
        <div class="col-lg-5">
            <form method="post" action="/espace/profil" class="card card-body" novalidate>
                <?= App\Csrf::field() ?>
                <input type="hidden" name="action" value="mot_de_passe">
                <h2 class="h5">Changer mon mot de passe</h2>
                <div class="mb-3">
                    <label for="ancien" class="form-label">Mot de passe actuel</label>
                    <input type="password" class="form-control<?= aria_invalid($errors, 'ancien') ?>" id="ancien" name="ancien" required autocomplete="current-password">
                    <?= field_error($errors, 'ancien') ?>
                </div>
                <?= render_partial('partials/champs-mot-de-passe', ['errors' => $errors]) ?>
                <button type="submit" class="btn btn-outline-bordeaux">Modifier le mot de passe</button>
            </form>
        </div>
    </div>
</div>
<script src="/assets/js/password.js" defer></script>
